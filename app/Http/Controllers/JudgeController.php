<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Models\Contestant;
use App\Models\Criteria;
use App\Models\Pageant;
use App\Models\Round;
use App\Models\Score;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class JudgeController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

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
                    'can_be_scored' => $pageant->canBeScored(),
                    'venue' => $pageant->venue,
                    'location' => $pageant->location,
                    'cover_image' => $pageant->cover_image,
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

        // Check if pageant can be scored (must be within start and end date in Philippine time)
        if (! $pageant->canBeScored()) {
            $startDateFormatted = $pageant->start_date
                ? $pageant->start_date->setTimezone('Asia/Manila')->format('F j, Y g:i A')
                : 'not set';

            $endDateFormatted = $pageant->end_date
                ? $pageant->end_date->setTimezone('Asia/Manila')->format('F j, Y g:i A')
                : 'not set';

            $currentPHTime = now()->setTimezone('Asia/Manila')->format('F j, Y g:i A');

            $dateRange = $pageant->end_date
                ? "between {$startDateFormatted} and {$endDateFormatted}"
                : "starting from {$startDateFormatted}";

            return Inertia::render('Judge/Scoring', [
                'pageant' => [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'start_date' => $startDateFormatted,
                    'end_date' => $endDateFormatted,
                    'current_time' => $currentPHTime,
                ],
                'error' => "Scoring is only allowed {$dateRange} (Philippine Time).\n\nCurrent Time: {$currentPHTime}\n\nPlease return during the pageant period.",
            ]);
        }

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

        // Check if current round can be edited (also considers pageant completion status)
        $canEditCurrentRound = $currentRound->canBeEdited() && ! $pageant->isCompleted() && ! $pageant->isArchived();

        // Get criteria for this round
        $criteria = $currentRound->criteria()->orderBy('display_order')->get();

        // Get contestants for this pageant
        $contestants = $pageant->contestants()->with('members:id,name')->orderBy('number')->get();

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
                'name' => $contestant->is_pair ? ($contestant->display_name ?? $contestant->name) : $contestant->name,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                'origin' => $contestant->origin,
                'is_pair' => (bool) $contestant->is_pair,
                'members_text' => $contestant->is_pair ? $contestant->members->pluck('name')->implode(' & ') : null,
            ];
        });

        $formattedCriteria = $criteria->map(function ($criterion) {
            return [
                'id' => $criterion->id,
                'name' => $criterion->name,
                'description' => $criterion->description,
                'weight' => $criterion->weight,
                'min_score' => (float) $criterion->min_score,
                'max_score' => (float) $criterion->max_score,
                'allow_decimals' => (bool) ($criterion->allow_decimals ?? false),
                'decimal_places' => (int) ($criterion->decimal_places ?? 0),
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

        // Get all scores for all contestants in this round for reference
        $allContestantScores = [];
        foreach ($contestants as $contestant) {
            $contestantScores = Score::where('judge_id', $judge->id)
                ->where('pageant_id', $pageantId)
                ->where('round_id', $roundId)
                ->where('contestant_id', $contestant->id)
                ->get()
                ->keyBy('criteria_id');

            if ($contestantScores->isNotEmpty()) {
                // Calculate weighted average
                $weightedSum = 0;
                $weightTotal = 0;
                $hasAllScores = true;

                foreach ($criteria as $criterion) {
                    $score = $contestantScores->get($criterion->id);
                    if ($score) {
                        $weight = $criterion->weight ?? 1;
                        $weightedSum += $score->score * $weight;
                        $weightTotal += $weight;
                    } else {
                        $hasAllScores = false;
                    }
                }

                $average = $weightTotal > 0 ? round($weightedSum / $weightTotal, 2) : null;

                $allContestantScores[$contestant->id] = [
                    'contestant_id' => $contestant->id,
                    'contestant_name' => $contestant->is_pair ? ($contestant->display_name ?? $contestant->name) : $contestant->name,
                    'contestant_number' => $contestant->number,
                    'scores' => $contestantScores->map(fn ($s) => [
                        'criteria_id' => $s->criteria_id,
                        'score' => $s->score,
                    ])->values(),
                    'average' => $average,
                    'is_complete' => $hasAllScores,
                ];
            }
        }

        return Inertia::render('Judge/Scoring', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'scoring_system' => $pageant->scoring_system,
                'current_round_id' => $pageant->current_round_id,
                'has_current_round' => $pageant->hasCurrentRound(),
                'status' => $pageant->status,
                'is_completed' => $pageant->isCompleted(),
                'can_be_scored' => $pageant->canBeScored(),
                'start_date' => $pageant->start_date?->format('F j, Y'),
                'start_time' => $pageant->start_time,
            ],
            'rounds' => $rounds->map(function ($round) use ($judge) {
                return [
                    'id' => $round->id,
                    'name' => $round->name,
                    'description' => $round->description,
                    'type' => $round->type,
                    'top_n_proceed' => $round->top_n_proceed,
                    'is_locked' => $round->is_locked,
                    'can_be_edited' => $round->canBeEdited(),
                    'scoring_progress' => $round->getJudgeScoringProgress($judge->id),
                    'is_complete' => $round->isJudgeScoringComplete($judge->id),
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
                'type' => $currentRound->type,
                'top_n_proceed' => $currentRound->top_n_proceed,
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
            'allContestantScores' => array_values($allContestantScores),
        ]);
    }

    /**
     * Submit scores for a contestant
     */
    public function submitScores(Request $request, $pageantId, $roundId)
    {
        if (! Auth::user()->hasPermission('judge_submit_scores')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to submit scores.',
            ], 403);
        }

        $judge = Auth::user();

        // Validate judge has access to this pageant
        $pageant = $this->getPageantForJudge($pageantId, $judge->id);
        $round = $pageant->rounds()->findOrFail($roundId);

        // Check if pageant can be scored (must be within start and end date in Philippine time)
        if (! $pageant->canBeScored()) {
            $startDateFormatted = $pageant->start_date
                ? $pageant->start_date->setTimezone('Asia/Manila')->format('F j, Y g:i A')
                : 'not set';

            $endDateFormatted = $pageant->end_date
                ? $pageant->end_date->setTimezone('Asia/Manila')->format('F j, Y g:i A')
                : 'not set';

            $dateRange = $pageant->end_date
                ? "between {$startDateFormatted} and {$endDateFormatted}"
                : "starting from {$startDateFormatted}";

            return response()->json([
                'success' => false,
                'message' => "Scoring is only allowed {$dateRange} (Philippine Time). Please try again during the pageant period.",
            ], 403);
        }

        // Check if round is locked for editing
        if ($round->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'This round has been locked for editing by the tabulator.',
            ], 403);
        }

        // Basic validation first
        $validator = Validator::make($request->all(), [
            'contestant_id' => 'required|exists:contestants,id',
            'scores' => 'required|array',
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

        // Get criteria for detailed validation
        $criteria = $round->criteria()->whereIn('id', array_keys($scores))->get();

        if ($criteria->count() !== count($scores)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid criteria provided.',
            ], 422);
        }

        // Validate each score against its criteria
        foreach ($scores as $criteriaId => $score) {
            $criterion = $criteria->find($criteriaId);

            // Validate data type
            if (! is_numeric($score)) {
                return response()->json([
                    'success' => false,
                    'message' => "Score for '{$criterion->name}' must be a number.",
                    'field' => "scores.{$criteriaId}",
                ], 422);
            }

            $score = (float) $score;

            // Validate range
            if ($score < $criterion->min_score || $score > $criterion->max_score) {
                return response()->json([
                    'success' => false,
                    'message' => "Score for '{$criterion->name}' must be between {$criterion->min_score} and {$criterion->max_score}.",
                    'field' => "scores.{$criteriaId}",
                    'expected_range' => [
                        'min' => $criterion->min_score,
                        'max' => $criterion->max_score,
                    ],
                ], 422);
            }

            // Validate decimals
            if (! $criterion->allow_decimals && floor($score) != $score) {
                return response()->json([
                    'success' => false,
                    'message' => "Score for '{$criterion->name}' must be a whole number (no decimals allowed).",
                    'field' => "scores.{$criteriaId}",
                ], 422);
            }

            // Validate decimal places
            if ($criterion->allow_decimals && $criterion->decimal_places > 0) {
                $scoreStr = (string) $score;
                if (strpos($scoreStr, '.') !== false) {
                    $decimalPart = substr($scoreStr, strpos($scoreStr, '.') + 1);
                    if (strlen($decimalPart) > $criterion->decimal_places) {
                        return response()->json([
                            'success' => false,
                            'message' => "Score for '{$criterion->name}' can have maximum {$criterion->decimal_places} decimal places.",
                            'field' => "scores.{$criteriaId}",
                            'max_decimal_places' => $criterion->decimal_places,
                        ], 422);
                    }
                }
            }
        }

        // Continue with transaction for saving scores
        DB::transaction(function () use ($judge, $pageantId, $roundId, $contestantId, $scores, $notes, $contestant, $round) {
            foreach ($scores as $criteriaId => $score) {
                // Check if score already exists
                $existingScore = Score::where([
                    'judge_id' => $judge->id,
                    'pageant_id' => $pageantId,
                    'round_id' => $roundId,
                    'criteria_id' => $criteriaId,
                    'contestant_id' => $contestantId,
                ])->first();

                $isUpdate = $existingScore !== null;
                $oldScore = $isUpdate ? $existingScore->score : null;

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

                // Log the action in audit log
                if ($isUpdate && $oldScore != $score) {
                    $criteria = Criteria::find($criteriaId);
                    $this->auditLogService->log(
                        'SCORE_UPDATED',
                        'Score',
                        $newScore->id,
                        "Judge '{$judge->name}' updated score for contestant '{$contestant->name}' in round '{$round->name}' - Criteria: '{$criteria->name}' - Old: {$oldScore}, New: {$score}"
                    );
                } elseif (! $isUpdate) {
                    $criteria = Criteria::find($criteriaId);
                    $this->auditLogService->log(
                        'SCORE_CREATED',
                        'Score',
                        $newScore->id,
                        "Judge '{$judge->name}' submitted score for contestant '{$contestant->name}' in round '{$round->name}' - Criteria: '{$criteria->name}' - Score: {$score}"
                    );
                }

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
     * Get detailed contestant information for judges, including images and basic metadata.
     */
    public function contestantDetails(int $pageantId, int $contestantId)
    {
        $judge = Auth::user();
        $pageant = $this->getPageantForJudge($pageantId, $judge->id);

        $contestant = $pageant->contestants()->with(['images', 'members:id,name,number,photo'])->findOrFail($contestantId);

        $images = $contestant->images->map(function ($image) {
            return [
                'id' => $image->id,
                'path' => asset($image->image_path),
                'caption' => $image->caption,
                'is_primary' => (bool) $image->is_primary,
            ];
        });

        return response()->json([
            'id' => $contestant->id,
            'number' => $contestant->number,
            'name' => $contestant->is_pair ? ($contestant->display_name ?? $contestant->name) : $contestant->name,
            'origin' => $contestant->origin,
            'age' => $contestant->age,
            'bio' => $contestant->bio,
            'rank' => $contestant->rank,
            'metadata' => $contestant->metadata,
            'image' => $contestant->photo ? asset($contestant->photo) : asset('/images/placeholders/contestant.jpg'),
            'images' => $images,
            'scores' => $contestant->scores ?? [],
            'is_pair' => (bool) $contestant->is_pair,
            'members' => $contestant->is_pair ? $contestant->members->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'number' => $m->number,
                    'photo' => $m->photo ? asset($m->photo) : null,
                ];
            })->values() : [],
            'members_text' => $contestant->is_pair ? $contestant->members->pluck('name')->implode(' & ') : null,
        ]);
    }

    /**
     * Compare judge's weighted scores for all contestants in the current and previous rounds.
     */
    public function roundComparison(Request $request, int $pageantId, int $roundId)
    {
        $judge = Auth::user();
        $pageant = $this->getPageantForJudge($pageantId, $judge->id);

        $request->validate([
            'contestant_id' => 'required|exists:contestants,id',
        ]);

        $subjectContestantId = (int) $request->get('contestant_id');

        $round = $pageant->rounds()->with('criteria')->findOrFail($roundId);
        $previousRound = $pageant->rounds()
            ->where('display_order', '<', $round->display_order)
            ->orderByDesc('display_order')
            ->with('criteria')
            ->first();

        $contestants = $pageant->contestants()->select(['id', 'number', 'name', 'photo'])->orderBy('number')->get();

        $formatContestant = function ($c) {
            return [
                'id' => $c->id,
                'number' => $c->number,
                'name' => $c->name,
                'image' => $c->photo ? asset($c->photo) : asset('/images/placeholders/contestant.jpg'),
            ];
        };

        $calculateWeightedByContestant = function ($criteriaCollection, $targetRoundId) use ($judge, $pageantId) {
            $criteriaById = $criteriaCollection->keyBy('id');

            $scores = Score::where('judge_id', $judge->id)
                ->where('pageant_id', $pageantId)
                ->where('round_id', $targetRoundId)
                ->get();

            $grouped = $scores->groupBy('contestant_id');

            $results = [];
            foreach ($grouped as $contestantId => $scoresForContestant) {
                $weightedSum = 0.0;
                $weightTotal = 0.0;

                foreach ($scoresForContestant as $score) {
                    $criterion = $criteriaById->get($score->criteria_id);
                    if (! $criterion) {
                        continue; // skip scores not belonging to this round (safety)
                    }
                    $weight = (float) ($criterion->weight ?? 1);
                    $weightedSum += ((float) $score->score) * $weight;
                    $weightTotal += $weight;
                }

                $results[$contestantId] = $weightTotal > 0 ? round($weightedSum / $weightTotal, 2) : null;
            }

            return $results;
        };

        $currentScores = $calculateWeightedByContestant($round->criteria, $round->id);
        $previousScores = $previousRound ? $calculateWeightedByContestant($previousRound->criteria, $previousRound->id) : [];

        $buildComparison = function ($roundModel, $scoresMap) use ($contestants, $formatContestant, $subjectContestantId) {
            if (! $roundModel) {
                return null;
            }

            $items = $contestants->map(function ($c) use ($formatContestant, $scoresMap) {
                $base = $formatContestant($c);
                $base['judgeWeightedScore'] = $scoresMap[$c->id] ?? null;

                return $base;
            })->sortByDesc(function ($row) {
                return $row['judgeWeightedScore'] ?? -INF;
            })->values();

            $position = null;
            foreach ($items as $index => $row) {
                if ($row['id'] === $subjectContestantId) {
                    $position = $index + 1;
                    break;
                }
            }

            return [
                'round' => [
                    'id' => $roundModel->id,
                    'name' => $roundModel->name,
                ],
                'subject' => [
                    'contestant_id' => $subjectContestantId,
                    'position' => $position,
                    'score' => $scoresMap[$subjectContestantId] ?? null,
                ],
                'contestants' => $items,
            ];
        };

        return response()->json([
            'current' => $buildComparison($round, $currentScores),
            'previous' => $previousRound ? $buildComparison($previousRound, $previousScores) : null,
        ]);
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
