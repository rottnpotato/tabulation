<?php

namespace App\Http\Controllers;

use App\Models\Pageant;
use App\Models\User;
use App\Models\Round;
use App\Models\Contestant;
use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TabulatorController extends Controller
{
    /**
     * Show tabulator dashboard for a specific pageant
     */
    public function dashboard($pageantId = null)
    {
        $tabulator = Auth::user();
        
        // If no pageant specified, get all pageants this tabulator is assigned to
        if (!$pageantId) {
            $pageants = Pageant::whereHas('tabulators', function($query) use ($tabulator) {
                $query->where('user_id', $tabulator->id);
            })->with(['contestants', 'judges', 'rounds.criteria'])->get();
            
            return Inertia::render('Tabulator/Dashboard', [
                'pageants' => $pageants->map(function($pageant) {
                    return [
                        'id' => $pageant->id,
                        'name' => $pageant->name,
                        'status' => $pageant->status,
                        'contestants_count' => $pageant->contestants->count(),
                        'judges_count' => $pageant->judges->count(),
                        'rounds_count' => $pageant->rounds->count(),
                        'criteria_count' => $pageant->rounds->sum(function($round) {
                            return $round->criteria->count();
                        }),
                    ];
                })
            ]);
        }
        
        // Get specific pageant data
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        // Calculate summary statistics
        $totalContestants = $pageant->contestants->count();
        $totalJudges = $pageant->judges->count();
        $totalRounds = $pageant->rounds->count();
        $totalCriteria = $pageant->rounds->sum(function($round) {
            return $round->criteria->count();
        });
        
        // Get recent activity (this would be from actual scoring activity)
        $recentActivity = collect([]);
        
        return Inertia::render('Tabulator/Dashboard', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'status' => $pageant->status,
                'scoring_system' => $pageant->scoring_system,
            ],
            'summary' => [
                'contestants' => $totalContestants,
                'judges' => $totalJudges,
                'rounds' => $totalRounds,
                'criteria' => $totalCriteria,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Show judges for a pageant
     */
    public function judges($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        $judges = $pageant->judges->map(function($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
                'email' => $judge->email,
                'title' => $judge->pivot->role ?? 'Judge',
                'isActive' => $judge->pivot->active ?? true,
                'scoresSubmitted' => 0, // TODO: Calculate actual scores submitted
            ];
        });
        
        return Inertia::render('Tabulator/Judges', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
            ],
            'judges' => $judges,
        ]);
    }

    /**
     * Show scores for a specific pageant and round
     */
    public function scores($pageantId, $roundId = null)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        // Get available rounds
        $rounds = $pageant->rounds->map(function($round) {
            return [
                'id' => $round->id,
                'name' => $round->name,
                'type' => $round->type,
                'weight' => $round->weight,
            ];
        });
        
        // If no round specified, use first round
        if (!$roundId && $rounds->count() > 0) {
            $roundId = $rounds->first()['id'];
        }
        
        $currentRound = $pageant->rounds->find($roundId);
        
        if (!$currentRound) {
            return Inertia::render('Tabulator/Scores', [
                'pageant' => ['id' => $pageant->id, 'name' => $pageant->name],
                'rounds' => $rounds,
                'contestants' => [],
                'judges' => [],
                'currentRound' => null,
                'scores' => [],
            ]);
        }
        
        $contestants = $pageant->contestants->map(function($contestant) {
            return [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->name,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
            ];
        });
        
        $judges = $pageant->judges->map(function($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
            ];
        });
        
        // TODO: Implement actual scoring data retrieval
        $scores = collect([]);
        
        return Inertia::render('Tabulator/Scores', [
            'pageant' => ['id' => $pageant->id, 'name' => $pageant->name],
            'rounds' => $rounds,
            'currentRound' => [
                'id' => $currentRound->id,
                'name' => $currentRound->name,
            ],
            'contestants' => $contestants,
            'judges' => $judges,
            'scores' => $scores,
        ]);
    }

    /**
     * Show results for a pageant
     */
    public function results($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        $rounds = $pageant->rounds->map(function($round) {
            return [
                'id' => $round->id,
                'name' => $round->name,
                'weight' => $round->weight,
            ];
        });
        
        $contestants = $pageant->contestants->map(function($contestant) {
            // TODO: Calculate actual scores from scoring data
            $mockScores = [];
            foreach ($pageant->rounds as $round) {
                $mockScores[$round->name] = rand(85, 98) + (rand(0, 99) / 100);
            }
            
            return [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->name,
                'region' => $contestant->origin,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                'scores' => $mockScores,
                'totalScore' => collect($mockScores)->avg(),
            ];
        });
        
        // Sort contestants by total score (descending)
        $contestants = $contestants->sortByDesc('totalScore')->values();
        
        return Inertia::render('Tabulator/Results', [
            'pageant' => ['id' => $pageant->id, 'name' => $pageant->name],
            'contestants' => $contestants,
            'rounds' => $rounds,
        ]);
    }

    /**
     * Show printable results for a pageant
     */
    public function print($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        // Get top contestants for printing
        $contestants = $pageant->contestants->map(function($contestant) {
            // TODO: Calculate actual scores
            $mockScores = [];
            foreach ($pageant->rounds as $round) {
                $mockScores[$round->name] = rand(85, 98);
            }
            
            return [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->name,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                'scores' => $mockScores,
                'final_score' => collect($mockScores)->avg(),
            ];
        })->sortByDesc('final_score')->take(10)->values();
        
        return Inertia::render('Tabulator/Print', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'date' => $pageant->pageant_date?->format('F j, Y'),
                'venue' => $pageant->venue,
                'location' => $pageant->location,
            ],
            'results' => $contestants,
        ]);
    }

    /**
     * Get pageant data with tabulator access validation
     */
    private function getPageantForTabulator($pageantId, $tabulatorId)
    {
        $pageant = Pageant::with([
            'contestants',
            'judges',
            'rounds.criteria',
        ])->whereHas('tabulators', function($query) use ($tabulatorId) {
            $query->where('user_id', $tabulatorId);
        })->findOrFail($pageantId);
        
        return $pageant;
    }
} 