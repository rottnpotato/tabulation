<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Models\Contestant;
use App\Models\Criteria;
use App\Models\Pageant;
use App\Models\Round;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class JudgeController extends Controller
{
    /**
     * Show judge dashboard with assigned pageants
     */
    public function dashboard()
    {
        $judge = Auth::user();

        // Get pageants assigned to this judge
        $pageants = Pageant::whereHas('judges', function ($query) use ($judge) {
            $query->where('user_id', $judge->id)
                ->where('active', true);
        })
            ->with(['rounds.criteria', 'contestants', 'currentRound'])
            ->get()
            ->map(function ($pageant) use ($judge) {
                // Calculate scoring progress for this judge
                $totalPossibleScores = 0;
                $submittedScores = 0;

                foreach ($pageant->rounds as $round) {
                    $criteria = $round->criteria;
                    $contestants = $pageant->contestants;
                    $totalPossibleScores += $criteria->count() * $contestants->count();

                    $submittedScores += Score::where('judge_id', $judge->id)
                        ->where('pageant_id', $pageant->id)
                        ->where('round_id', $round->id)
                        ->count();
                }

                $progressPercentage = $totalPossibleScores > 0
                    ? round(($submittedScores / $totalPossibleScores) * 100)
                    : 0;

                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'description' => $pageant->description,
                    'status' => $pageant->status,
                    'pageant_date' => $pageant->pageant_date,
                    'venue' => $pageant->venue,
                    'location' => $pageant->location,
                    'contestants_count' => $pageant->contestants->count(),
                    'rounds_count' => $pageant->rounds->count(),
                    'scoring_progress' => $progressPercentage,
                    'total_scores_needed' => $totalPossibleScores,
                    'scores_submitted' => $submittedScores,
                    'current_round' => $pageant->currentRound ? [
                        'id' => $pageant->currentRound->id,
                        'name' => $pageant->currentRound->name,
                        'is_locked' => $pageant->currentRound->is_locked ?? false,
                    ] : null,
                ];
            });

        return Inertia::render('Judge/Dashboard', [
            'pageants' => $pageants,
            'judge' => [
                'id' => $judge->id,
                'name' => $judge->name,
                'email' => $judge->email,
            ],
        ]);
    }

    /**
     * Show scoring interface for a specific pageant and round
     */
    public function scoring($pageantId, $roundId = null)
    {
        $judge = Auth::user();

        // Validate judge has access to this pageant
        $pageant = $this->getPageantForJudge($pageantId, $judge->id);

        // Get available rounds for this pageant with locking status
        $rounds = $pageant->rounds()->active()->ordered()->with('lockedBy')->get();

        if ($rounds->isEmpty()) {
            return Inertia::render('Judge/Scoring', [
                'pageant' => null,
                'error' => 'No rounds available for scoring in this pageant.',
            ]);
        }

        // If no round specified, use the current round set by tabulator, or first available round
        if (! $roundId) {
            $preferredRound = $pageant->getCurrentRound();
            $roundId = $preferredRound ? $preferredRound->id : $rounds->first()->id;
        }

        $currentRound = $rounds->find($roundId);

        if (! $currentRound) {
            $preferredRound = $pageant->getCurrentRound();
            $fallbackRoundId = $preferredRound ? $preferredRound->id : $rounds->first()->id;

            return redirect()->route('judge.scoring', ['pageantId' => $pageantId, 'roundId' => $fallbackRoundId]);
        }

        // Check if current round can be edited
        $canEditCurrentRound = $currentRound->canBeEdited();

        // Get criteria for this round
        $criteria = $currentRound->criteria()->orderBy('display_order')->get();

        // Get contestants for this pageant
        $contestants = $pageant->contestants()->orderBy('number')->get();

        // Get existing scores for this judge in this round
        $existingScores = Score::where('judge_id', $judge->id)
            ->where('pageant_id', $pageantId)
            ->where('round_id', $roundId)
            ->get()
            ->keyBy(function ($score) {
                return $score->contestant_id.'-'.$score->criteria_id;
            });

        // Format data for frontend
        $formattedContestants = $contestants->map(function ($contestant) {
            return [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->name,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                'origin' => $contestant->origin,
            ];
        });

        $formattedCriteria = $criteria->map(function ($criterion) {
            return [
                'id' => $criterion->id,
                'name' => $criterion->name,
                'description' => $criterion->description,
                'weight' => $criterion->weight,
                'min_score' => $criterion->min_score,
                'max_score' => $criterion->max_score,
            ];
        });

        $formattedScores = [];
        $formattedNotes = [];

        foreach ($existingScores as $key => $score) {
            $formattedScores[$key] = $score->score;
            if ($score->notes) {
                $formattedNotes[$score->contestant_id] = $score->notes;
            }
        }

        return Inertia::render('Judge/Scoring', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'scoring_system' => $pageant->scoring_system,
                'current_round_id' => $pageant->current_round_id,
                'has_current_round' => $pageant->hasCurrentRound(),
            ],
            'rounds' => $rounds->map(function ($round) {
                return [
                    'id' => $round->id,
                    'name' => $round->name,
                    'description' => $round->description,
                    'is_locked' => $round->is_locked,
                    'can_be_edited' => $round->canBeEdited(),
                    'locked_by' => $round->lockedBy ? [
                        'id' => $round->lockedBy->id,
                        'name' => $round->lockedBy->name,
                    ] : null,
                ];
            }),
            'currentRound' => [
                'id' => $currentRound->id,
                'name' => $currentRound->name,
                'description' => $currentRound->description,
                'is_locked' => $currentRound->is_locked,
                'can_be_edited' => $canEditCurrentRound,
                'locked_by' => $currentRound->lockedBy ? [
                    'id' => $currentRound->lockedBy->id,
                    'name' => $currentRound->lockedBy->name,
                ] : null,
            ],
            'contestants' => $formattedContestants,
            'criteria' => $formattedCriteria,
            'existingScores' => $formattedScores,
            'existingNotes' => $formattedNotes,
            'canEditScores' => $canEditCurrentRound,
        ]);
    }

    /**
     * Submit scores for a contestant
     */
    public function submitScores(Request $request, $pageantId, $roundId)
    {
        $judge = Auth::user();

        // Validate judge has access to this pageant
        $pageant = $this->getPageantForJudge($pageantId, $judge->id);
        $round = $pageant->rounds()->findOrFail($roundId);

        // Check if round is locked for editing
        if ($round->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'This round has been locked for editing by the tabulator.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'contestant_id' => 'required|exists:contestants,id',
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $contestantId = $request->contestant_id;
        $scores = $request->scores;
        $notes = $request->notes;

        // Verify contestant belongs to this pageant
        $contestant = $pageant->contestants()->findOrFail($contestantId);

        // Verify all criteria belong to this round
        $criteria = $round->criteria()->whereIn('id', array_keys($scores))->get();

        if ($criteria->count() !== count($scores)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid criteria provided.',
            ], 422);
        }

        DB::transaction(function () use ($judge, $pageantId, $roundId, $contestantId, $scores, $notes, $criteria) {
            foreach ($scores as $criteriaId => $score) {
                $criterion = $criteria->find($criteriaId);

                // Validate score is within criteria range
                if ($score < $criterion->min_score || $score > $criterion->max_score) {
                    throw new \Exception("Score for {$criterion->name} must be between {$criterion->min_score} and {$criterion->max_score}");
                }

                $newScore = Score::updateOrCreate(
                    [
                        'judge_id' => $judge->id,
                        'pageant_id' => $pageantId,
                        'round_id' => $roundId,
                        'criteria_id' => $criteriaId,
                        'contestant_id' => $contestantId,
                    ],
                    [
                        'score' => $score,
                        'notes' => $notes,
                        'submitted_at' => now(),
                    ]
                );

                ScoreUpdated::dispatch($newScore);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Scores submitted successfully.',
            'contestant_name' => $contestant->name,
        ]);
    }

    /**
     * Get scores summary for a judge
     */
    public function scoresSummary($pageantId)
    {
        $judge = Auth::user();
        $pageant = $this->getPageantForJudge($pageantId, $judge->id);

        $summary = [];

        foreach ($pageant->rounds as $round) {
            $criteria = $round->criteria;
            $contestants = $pageant->contestants;

            $totalPossible = $criteria->count() * $contestants->count();
            $submitted = Score::where('judge_id', $judge->id)
                ->where('pageant_id', $pageantId)
                ->where('round_id', $round->id)
                ->count();

            $summary[$round->id] = [
                'round_name' => $round->name,
                'total_possible' => $totalPossible,
                'submitted' => $submitted,
                'percentage' => $totalPossible > 0 ? round(($submitted / $totalPossible) * 100) : 0,
            ];
        }

        return response()->json($summary);
    }

    /**
     * Get pageant data with judge access validation
     */
    private function getPageantForJudge($pageantId, $judgeId)
    {
        $pageant = Pageant::with([
            'contestants',
            'rounds.criteria' => function ($query) {
                $query->orderBy('display_order');
            },
        ])->whereHas('judges', function ($query) use ($judgeId) {
            $query->where('user_id', $judgeId)
                ->where('active', true);
        })->findOrFail($pageantId);

        return $pageant;
    }
}
