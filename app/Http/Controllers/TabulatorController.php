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
use Illuminate\Support\Str;
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
        
        // Get available judges not yet assigned to this pageant
        $assignedJudgeIds = $pageant->judges->pluck('id')->toArray();
        $availableJudges = User::where('role', 'judge')
            ->where('is_active', true)
            ->whereNotIn('id', $assignedJudgeIds)
            ->select('id', 'name', 'email', 'username')
            ->orderBy('name')
            ->get();
        
        return Inertia::render('Tabulator/Judges', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'required_judges' => $pageant->required_judges,
                'current_judges_count' => $judges->count(),
            ],
            'judges' => $judges,
            'availableJudges' => $availableJudges,
        ]);
    }

    /**
     * Assign a judge to the pageant
     */
    public function assignJudge(Request $request, $pageantId)
    {
        $request->validate([
            'judge_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:50',
        ]);

        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        // Check if pageant has reached required judges limit
        if ($pageant->required_judges && $pageant->judges()->count() >= $pageant->required_judges) {
            return back()->withErrors(['message' => 'This pageant has already reached the maximum number of judges.']);
        }
        
        // Check if judge is already assigned
        if ($pageant->judges()->where('user_id', $request->judge_id)->exists()) {
            return back()->withErrors(['message' => 'This judge is already assigned to this pageant.']);
        }
        
        // Assign the judge
        $pageant->judges()->attach($request->judge_id, [
            'role' => $request->role ?? 'judge',
            'active' => true,
        ]);
        
        return back()->with('success', 'Judge assigned successfully.');
    }

    /**
     * Remove a judge from the pageant
     */
    public function removeJudge($pageantId, $judgeId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        $pageant->judges()->detach($judgeId);
        
        return back()->with('success', 'Judge removed successfully.');
    }

    /**
     * Toggle judge active status
     */
    public function toggleJudgeStatus($pageantId, $judgeId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        $judge = $pageant->judges()->where('user_id', $judgeId)->first();
        if ($judge) {
            $newStatus = !$judge->pivot->active;
            $pageant->judges()->updateExistingPivot($judgeId, ['active' => $newStatus]);
            
            return back()->with('success', 'Judge status updated successfully.');
        }
        
        return back()->withErrors(['message' => 'Judge not found.']);
    }

    /**
     * Reset judge password
     */
    public function resetJudgePassword($pageantId, $judgeId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        
        $judge = User::find($judgeId);
        if ($judge && $pageant->judges()->where('user_id', $judgeId)->exists()) {
            // Generate a new password
            $newPassword = Str::random(8);
            $judge->update(['password' => bcrypt($newPassword)]);
            
            // TODO: Send email with new password
            // Mail::to($judge->email)->send(new PasswordReset($judge, $newPassword));
            
            return back()->with('success', "Judge password reset. New password: {$newPassword}");
        }
        
        return back()->withErrors(['message' => 'Judge not found.']);
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