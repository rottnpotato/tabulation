<?php

namespace App\Http\Controllers;

use App\Events\RoundUpdated;
use App\Http\Requests\AssignJudgeRequest;
use App\Http\Requests\StoreJudgeRequest;
use App\Http\Requests\UpdateJudgeRequest;
use App\Models\AuditLog;
use App\Models\Contestant;
use App\Models\Criteria;
use App\Models\Pageant;
use App\Models\Round;
use App\Models\Score;
use App\Models\User;
use App\Services\ScoreCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TabulatorController extends Controller
{
    protected ScoreCalculationService $scoreCalculationService;

    public function __construct(ScoreCalculationService $scoreCalculationService)
    {
        $this->scoreCalculationService = $scoreCalculationService;
    }

    /**
     * Show tabulator dashboard for a specific pageant
     */
    public function dashboard($pageantId = null)
    {
        $tabulator = Auth::user();

        // If no pageant specified, get all pageants this tabulator is assigned to
        if (! $pageantId) {
            $pageants = Pageant::whereHas('tabulators', function ($query) use ($tabulator) {
                $query->where('user_id', $tabulator->id);
            })->with(['contestants', 'judges', 'rounds.criteria'])->get();

            // parse pageant start_date to string format
            return Inertia::render('Tabulator/Dashboard', [
                'pageants' => $pageants->map(function ($pageant) {
                    return [
                        'id' => $pageant->id,
                        'name' => $pageant->name,
                        'status' => $pageant->status,
                        'start_date' => $pageant->start_date ? $pageant->start_date->toDateString() : null,
                        'contestants_count' => $pageant->contestants->count(),
                        'judges_count' => $pageant->judges->count(),
                        'rounds_count' => $pageant->rounds->count(),
                        'criteria_count' => $pageant->rounds->sum(function ($round) {
                            return $round->criteria->count();
                        }),
                    ];
                }),
            ]);
        }

        // Get specific pageant data
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        // Calculate summary statistics
        $totalContestants = $pageant->contestants->count();
        $totalJudges = $pageant->judges->count();
        $totalRounds = $pageant->rounds->count();
        $totalCriteria = $pageant->rounds->sum(function ($round) {
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
                'start_date' => $pageant->start_date ? $pageant->start_date->toDateString() : null,
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
        if (! Auth::user()->hasPermission('tabulator_view_judges')) {
            return redirect()->back()->with('error', 'You do not have permission to view judge information.');
        }

        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $judges = $pageant->judges->map(function ($judge) {
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
                'status' => $pageant->status,
                'is_completed' => $pageant->isCompleted(),
            ],
            'judges' => $judges,
            'availableJudges' => $availableJudges,
        ]);
    }

    /**
     * Assign a judge to the pageant
     */
    public function assignJudge(AssignJudgeRequest $request, $pageantId)
    {
        $validated = $request->validated();

        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        // Check if pageant has reached required judges limit
        if ($pageant->required_judges && $pageant->judges()->count() >= $pageant->required_judges) {
            return back()->withErrors(['message' => 'This pageant has already reached the maximum number of judges.']);
        }

        // Check if judge is already assigned
        if ($pageant->judges()->where('user_id', $validated['judge_id'])->exists()) {
            return back()->withErrors(['message' => 'This judge is already assigned to this pageant.']);
        }

        // Assign the judge
        $pageant->judges()->attach($validated['judge_id'], [
            'role' => $validated['role'] ?? 'judge',
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
            $newStatus = ! $judge->pivot->active;
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
            $judge->update(['password' => $newPassword]);

            // TODO: Send email with new password
            // Mail::to($judge->email)->send(new PasswordReset($judge, $newPassword));

            return back()->with('success', "Judge password reset. New password: {$newPassword}");
        }

        return back()->withErrors(['message' => 'Judge not found.']);
    }

    /**
     * Create a new judge account for a specific pageant
     */
    public function createJudge(StoreJudgeRequest $request, $pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $validated = $request->validated();

        // Create the judge account
        $judge = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'role' => 'judge',
            'pageant_id' => $pageantId,
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Assign the judge to this pageant
        $pageant->judges()->attach($judge->id, [
            'role' => $validated['role_title'] ?? 'Judge',
            'active' => true,
        ]);

        return back()->with('success', 'Judge account created successfully.');
    }

    /**
     * Update judge account
     */
    public function updateJudge(UpdateJudgeRequest $request, $pageantId, $judgeId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $judge = User::findOrFail($judgeId);

        // Verify judge is assigned to this pageant
        if (! $pageant->judges()->where('user_id', $judgeId)->exists()) {
            return back()->withErrors(['message' => 'Judge not found for this pageant.']);
        }

        $validated = $request->validated();

        // Update judge account
        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $judge->update($updateData);

        // Update pivot data if role title changed
        if (isset($validated['role_title'])) {
            $pageant->judges()->updateExistingPivot($judgeId, [
                'role' => $validated['role_title'] ?? 'Judge',
            ]);
        }

        return back()->with('success', 'Judge account updated successfully.');
    }

    /**
     * Show scores for a specific pageant and round
     */
    public function scores($pageantId, $roundId = null)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        // Get available rounds
        $rounds = $pageant->rounds->map(function ($round) {
            return [
                'id' => $round->id,
                'name' => $round->name,
                'type' => $round->type,
                'weight' => $round->weight,
                'top_n_proceed' => $round->top_n_proceed,
            ];
        });

        // If no round specified, use first round
        if (! $roundId && $rounds->count() > 0) {
            $roundId = $rounds->first()['id'];
        }

        $currentRound = $pageant->rounds->find($roundId);

        if (! $currentRound) {
            return Inertia::render('Tabulator/Scores', [
                'pageant' => ['id' => $pageant->id, 'name' => $pageant->name],
                'rounds' => $rounds,
                'contestants' => [],
                'judges' => [],
                'currentRound' => null,
                'scores' => [],
            ]);
        }

        $contestants = $pageant->contestants()->with('members:id,name')->get()->map(function ($contestant) {
            return [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->is_pair ? ($contestant->display_name ?? $contestant->name) : $contestant->name,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                'is_pair' => (bool) $contestant->is_pair,
                'members_text' => $contestant->is_pair ? $contestant->members->pluck('name')->implode(' & ') : null,
            ];
        });

        $judges = $pageant->judges->map(function ($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
            ];
        });

        // Get aggregated scores per judge per contestant per round
        // Calculate weighted average of all criteria scores for each judge-contestant pair
        $scoresQuery = Score::where('pageant_id', $pageantId)
            ->where('round_id', $roundId)
            ->with(['criteria'])
            ->get()
            ->groupBy(['contestant_id', 'judge_id']);

        $scores = [];
        $scoringSystem = $pageant->scoring_system ?? 'percentage';

        foreach ($scoresQuery as $contestantId => $judgeScores) {
            foreach ($judgeScores as $judgeId => $judgeScoreRecords) {
                // Calculate weighted average for this judge's scores on this contestant
                $totalWeightedScore = 0;
                $totalWeight = 0;

                foreach ($judgeScoreRecords as $scoreRecord) {
                    $weight = $scoreRecord->criteria->weight ?? 1; // Default weight to 1 if not set
                    $totalWeightedScore += $scoreRecord->score * $weight;
                    $totalWeight += $weight;
                }

                if ($totalWeight > 0) {
                    $averageScore = $totalWeightedScore / $totalWeight;

                    // Normalize score based on scoring system
                    $normalizedScore = $this->normalizeScore($averageScore, $scoringSystem);

                    $key = $contestantId.'-'.$judgeId.'-'.$roundId;
                    $scores[$key] = round($normalizedScore, 2);
                }
            }
        }

        // Get criteria for this round with their details
        $criteria = $currentRound->criteria()->orderBy('display_order')->get()->map(function ($criterion) {
            return [
                'id' => $criterion->id,
                'name' => $criterion->name,
                'description' => $criterion->description,
                'weight' => $criterion->weight,
                'min_score' => (float) $criterion->min_score,
                'max_score' => (float) $criterion->max_score,
            ];
        });

        // Get individual criterion scores for detailed view
        $detailedScores = Score::where('pageant_id', $pageantId)
            ->where('round_id', $roundId)
            ->get()
            ->map(function ($score) {
                return [
                    'key' => "{$score->contestant_id}-{$score->judge_id}-{$score->criteria_id}",
                    'contestant_id' => $score->contestant_id,
                    'judge_id' => $score->judge_id,
                    'criteria_id' => $score->criteria_id,
                    'score' => (float) $score->score,
                    'notes' => $score->notes,
                    'submitted_at' => $score->submitted_at?->format('M d, Y h:i A'),
                ];
            })
            ->keyBy('key');

        return Inertia::render('Tabulator/Scores', [
            'pageant' => ['id' => $pageant->id, 'name' => $pageant->name],
            'rounds' => $rounds,
            'currentRound' => [
                'id' => $currentRound->id,
                'name' => $currentRound->name,
                'type' => $currentRound->type,
                'top_n_proceed' => $currentRound->top_n_proceed,
            ],
            'contestants' => $contestants,
            'judges' => $judges,
            'scores' => $scores,
            'criteria' => $criteria,
            'detailedScores' => $detailedScores,
        ]);
    }

    /**
     * Show results for a pageant
     */
    public function results($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $rounds = $pageant->rounds->sortBy('display_order')->map(function ($round) {
            return [
                'id' => $round->id,
                'name' => $round->name,
                'type' => $round->type,
                'weight' => $round->weight,
                'top_n_proceed' => $round->top_n_proceed,
                'display_order' => $round->display_order,
            ];
        })->values();

        // Calculate overall final scores using the service
        $contestants = $this->scoreCalculationService->calculatePageantFinalScores($pageant);

        // Calculate results for each round grouping (up to that round)
        $roundResults = [];
        $orderedRounds = $pageant->rounds->sortBy('display_order');

        foreach ($orderedRounds as $index => $currentRound) {
            // Get all rounds up to and including the current round
            $roundsUpToCurrent = $orderedRounds->filter(function ($r) use ($currentRound) {
                return $r->display_order <= $currentRound->display_order;
            });

            // Calculate scores using only these rounds
            $roundContestants = [];
            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;

                foreach ($roundsUpToCurrent as $round) {
                    $roundScore = $this->scoreCalculationService->calculateContestantRoundScore($contestant, $round, $pageant);

                    if ($roundScore !== null) {
                        $roundScores[$round->name] = $roundScore;
                        $roundWeight = $round->weight ?? 1;
                        if ($roundWeight <= 0) {
                            $roundWeight = 1;
                        }
                        $totalWeightedScore += $roundScore * $roundWeight;
                        $totalRoundWeight += $roundWeight;
                    }
                }

                $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

                $roundContestants[] = [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'gender' => $contestant->gender,
                    'region' => $contestant->origin,
                    'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                    'scores' => $roundScores,
                    'totalScore' => round($finalScore, 2),
                ];
            }

            // Sort by score
            usort($roundContestants, fn ($a, $b) => $b['totalScore'] <=> $a['totalScore']);

            // Add ranks
            foreach ($roundContestants as $rankIndex => &$contestant) {
                $contestant['rank'] = $rankIndex + 1;
            }

            // Apply top_n_proceed filtering from PREVIOUS round
            if ($index > 0) {
                $previousRound = $orderedRounds->values()[$index - 1];
                if ($previousRound->top_n_proceed !== null && $previousRound->top_n_proceed > 0) {
                    // Get the IDs of contestants who advanced
                    $previousRoundKey = 'round_'.$previousRound->id;
                    $advancedContestants = [];

                    if (isset($roundResults[$previousRoundKey])) {
                        $prevResults = $roundResults[$previousRoundKey]['contestants'];

                        // For pair pageants, take top N from each gender
                        if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                            $maleResults = array_filter($prevResults, fn ($c) => ($c['gender'] ?? '') === 'male');
                            $femaleResults = array_filter($prevResults, fn ($c) => ($c['gender'] ?? '') === 'female');

                            $maleResults = array_slice($maleResults, 0, $previousRound->top_n_proceed);
                            $femaleResults = array_slice($femaleResults, 0, $previousRound->top_n_proceed);

                            $advancedContestants = array_merge(
                                array_column($maleResults, 'id'),
                                array_column($femaleResults, 'id')
                            );
                        } else {
                            // Take top N overall
                            $topN = array_slice($prevResults, 0, $previousRound->top_n_proceed);
                            $advancedContestants = array_column($topN, 'id');
                        }
                    }

                    // Filter contestants to only those who advanced
                    if (! empty($advancedContestants)) {
                        $roundContestants = array_filter($roundContestants, function ($c) use ($advancedContestants) {
                            return in_array($c['id'], $advancedContestants);
                        });
                        $roundContestants = array_values($roundContestants);

                        // Re-rank after filtering
                        foreach ($roundContestants as $rankIndex => &$contestant) {
                            $contestant['rank'] = $rankIndex + 1;
                        }
                    }
                }
            }

            $roundResults['round_'.$currentRound->id] = [
                'contestants' => $roundContestants,
                'top_n_proceed' => $currentRound->top_n_proceed,
            ];
        }

        // Helper function to format contestant data consistently
        $formatContestantData = function ($contestant) use ($pageant) {
            $contestantModel = $pageant->contestants->firstWhere('id', $contestant['id']);
            $memberNames = [];
            $memberGenders = [];

            if ($contestantModel && $contestantModel->is_pair && $contestantModel->members->isNotEmpty()) {
                foreach ($contestantModel->members as $member) {
                    $memberNames[] = $member->name;
                    $memberGenders[] = $member->gender;
                }
            }

            return [
                'id' => $contestant['id'],
                'number' => $contestant['number'],
                'name' => $contestant['name'],
                'gender' => $contestantModel->gender ?? null,
                'is_pair' => $contestantModel->is_pair ?? false,
                'member_names' => $memberNames,
                'member_genders' => $memberGenders,
                'region' => $contestant['region'] ?? null,
                'image' => $contestant['image'] ?? '/images/placeholders/contestant-placeholder.jpg',
                'scores' => $contestant['scores'] ?? [],
                'totalScore' => $contestant['finalScore'] ?? $contestant['totalScore'] ?? 0,
                'rank' => $contestant['rank'] ?? 0,
            ];
        };

        // Format overall contestants
        $contestants = collect($contestants)->map($formatContestantData);

        // Format round-specific results
        $formattedRoundResults = [];
        foreach ($roundResults as $key => $result) {
            $formattedRoundResults[$key] = [
                'contestants' => collect($result['contestants'])->map($formatContestantData)->values()->all(),
                'top_n_proceed' => $result['top_n_proceed'],
            ];
        }

        return Inertia::render('Tabulator/Results', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'contestant_type' => $pageant->contestant_type,
                'number_of_winners' => $pageant->getNumberOfWinners(),
            ],
            'contestants' => $contestants,
            'rounds' => $rounds,
            'roundResults' => $formattedRoundResults,
        ]);
    }

    /**
     * Show printable results for a pageant
     */
    public function print($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        // Check if all rounds are locked
        $allRoundsLocked = $pageant->rounds->count() > 0 && $pageant->rounds->every(fn ($round) => $round->is_locked);

        // Get list of unlocked rounds for the warning message
        $unlockedRounds = $pageant->rounds->filter(fn ($round) => ! $round->is_locked)->map(fn ($round) => [
            'id' => $round->id,
            'name' => $round->name,
            'type' => $round->type,
        ])->values();

        // Helper function to mark qualified contestants based on top_n_proceed from previous round
        $markQualifiedContestants = function ($results, $stageType) use ($pageant) {
            // Find the last round before this stage that has top_n_proceed set
            $orderedRounds = $pageant->rounds->sortBy('display_order');
            $topN = null;

            foreach ($orderedRounds as $round) {
                if ($round->type === $stageType) {
                    // Found the target stage, stop looking
                    break;
                }

                // Check if this round has advancement rules
                if ($round->top_n_proceed !== null && $round->top_n_proceed > 0) {
                    $topN = $round->top_n_proceed;
                }
            }

            // Mark contestants as qualified based on their position
            return $results->map(function ($contestant, $index) use ($topN) {
                $contestant['qualified'] = $topN === null || ($index + 1) <= $topN;
                $contestant['qualification_cutoff'] = $topN;

                return $contestant;
            });
        };

        // Compute results for each stage
        $overallResults = collect($this->scoreCalculationService->calculatePageantFinalScores($pageant))->map(function ($contestant) use ($pageant) {
            $contestantModel = $pageant->contestants->firstWhere('id', $contestant['id']);
            $memberNames = [];
            $memberGenders = [];

            if ($contestantModel && $contestantModel->is_pair && $contestantModel->members->isNotEmpty()) {
                foreach ($contestantModel->members as $member) {
                    $memberNames[] = $member->name;
                    $memberGenders[] = $member->gender;
                }
            }

            return [
                'id' => $contestant['id'],
                'number' => $contestant['number'],
                'name' => $contestant['name'],
                'gender' => $contestantModel->gender ?? null,
                'is_pair' => $contestantModel->is_pair ?? false,
                'member_names' => $memberNames,
                'member_genders' => $memberGenders,
                'image' => $contestant['image'] ?? '/images/placeholders/contestant-placeholder.jpg',
                'scores' => $contestant['scores'] ?? [],
                'final_score' => $contestant['finalScore'] ?? 0,
                'rank' => $contestant['rank'] ?? 0,
            ];
        })->values();

        $semiResults = collect($this->scoreCalculationService->calculatePageantStageScores($pageant, 'semi-final'))->map(function ($contestant) use ($pageant) {
            $contestantModel = $pageant->contestants->firstWhere('id', $contestant['id']);
            $memberNames = [];
            $memberGenders = [];

            if ($contestantModel && $contestantModel->is_pair && $contestantModel->members->isNotEmpty()) {
                foreach ($contestantModel->members as $member) {
                    $memberNames[] = $member->name;
                    $memberGenders[] = $member->gender;
                }
            }

            return [
                'id' => $contestant['id'],
                'number' => $contestant['number'],
                'name' => $contestant['name'],
                'gender' => $contestantModel->gender ?? null,
                'is_pair' => $contestantModel->is_pair ?? false,
                'member_names' => $memberNames,
                'member_genders' => $memberGenders,
                'image' => $contestant['image'] ?? '/images/placeholders/contestant-placeholder.jpg',
                'scores' => $contestant['scores'] ?? [],
                'final_score' => $contestant['finalScore'] ?? 0,
                'rank' => $contestant['rank'] ?? 0,
            ];
        });
        $semiResults = $markQualifiedContestants($semiResults, 'semi-final')->values();

        $finalResults = collect($this->scoreCalculationService->calculatePageantStageScores($pageant, 'final'))->map(function ($contestant) use ($pageant) {
            $contestantModel = $pageant->contestants->firstWhere('id', $contestant['id']);
            $memberNames = [];
            $memberGenders = [];

            if ($contestantModel && $contestantModel->is_pair && $contestantModel->members->isNotEmpty()) {
                foreach ($contestantModel->members as $member) {
                    $memberNames[] = $member->name;
                    $memberGenders[] = $member->gender;
                }
            }

            return [
                'id' => $contestant['id'],
                'number' => $contestant['number'],
                'name' => $contestant['name'],
                'gender' => $contestantModel->gender ?? null,
                'is_pair' => $contestantModel->is_pair ?? false,
                'member_names' => $memberNames,
                'member_genders' => $memberGenders,
                'image' => $contestant['image'] ?? '/images/placeholders/contestant-placeholder.jpg',
                'scores' => $contestant['scores'] ?? [],
                'final_score' => $contestant['finalScore'] ?? 0,
                'rank' => $contestant['rank'] ?? 0,
            ];
        });
        $finalResults = $markQualifiedContestants($finalResults, 'final')->values();

        // Filter final results based on the last final round's top_n_proceed
        $lastFinalRound = $pageant->rounds
            ->filter(fn ($round) => $round->type === 'final')
            ->sortByDesc('display_order')
            ->first();

        if ($lastFinalRound && $lastFinalRound->top_n_proceed !== null && $lastFinalRound->top_n_proceed > 0) {
            $topN = $lastFinalRound->top_n_proceed;

            // For pair pageants, filter each gender separately
            if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                $maleFinalists = $finalResults->filter(fn ($c) => ($c['gender'] ?? '') === 'male')->take($topN);
                $femaleFinalists = $finalResults->filter(fn ($c) => ($c['gender'] ?? '') === 'female')->take($topN);
                $finalResults = $maleFinalists->merge($femaleFinalists)->values();
            } else {
                $finalResults = $finalResults->take($topN);
            }
        }

        // Get judges for this pageant
        $judges = $pageant->judges->map(function ($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
                'role' => $judge->pivot->role ?? 'Judge',
            ];
        });

        // Get unique round types from pageant with labels
        $uniqueRoundTypes = $pageant->rounds
            ->sortBy('display_order')
            ->groupBy('type')
            ->map(function ($roundsOfType) {
                $firstRound = $roundsOfType->first();
                $lastRound = $roundsOfType->sortByDesc('display_order')->first();

                return [
                    'key' => $firstRound->type,
                    'label' => ucwords(str_replace(['-', '_'], ' ', $firstRound->type)),
                    'display_order' => $firstRound->display_order,
                    'last_display_order' => $lastRound->display_order,
                ];
            })
            ->values();

        // Calculate results for each unique round type dynamically
        $resultsByRoundType = [];
        foreach ($uniqueRoundTypes as $roundTypeInfo) {
            $roundType = $roundTypeInfo['key'];
            $stageResults = collect($this->scoreCalculationService->calculatePageantStageScores($pageant, $roundType))->map(function ($contestant) use ($pageant) {
                $contestantModel = $pageant->contestants->firstWhere('id', $contestant['id']);
                $memberNames = [];
                $memberGenders = [];

                if ($contestantModel && $contestantModel->is_pair && $contestantModel->members->isNotEmpty()) {
                    foreach ($contestantModel->members as $member) {
                        $memberNames[] = $member->name;
                        $memberGenders[] = $member->gender;
                    }
                }

                return [
                    'id' => $contestant['id'],
                    'number' => $contestant['number'],
                    'name' => $contestant['name'],
                    'gender' => $contestantModel->gender ?? null,
                    'is_pair' => $contestantModel->is_pair ?? false,
                    'member_names' => $memberNames,
                    'member_genders' => $memberGenders,
                    'image' => $contestant['image'] ?? '/images/placeholders/contestant-placeholder.jpg',
                    'scores' => $contestant['scores'] ?? [],
                    'final_score' => $contestant['finalScore'] ?? 0,
                    'rank' => $contestant['rank'] ?? 0,
                ];
            });

            $stageResults = $markQualifiedContestants($stageResults, $roundType)->values();
            $resultsByRoundType[$roundType] = $stageResults;
        }

        // Get the last final round and use it for "overall" if it exists
        $lastFinalRound = $pageant->rounds
            ->filter(fn ($round) => strtolower($round->type) === 'final')
            ->sortByDesc('display_order')
            ->first();

        // Overall results should be the last final round if it exists, otherwise use full calculation
        if ($lastFinalRound && isset($resultsByRoundType['final'])) {
            $overallResults = $resultsByRoundType['final'];

            // Apply top_n_proceed filter from last final round
            if ($lastFinalRound->top_n_proceed !== null && $lastFinalRound->top_n_proceed > 0) {
                $topN = $lastFinalRound->top_n_proceed;

                if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                    $maleFinalists = $overallResults->filter(fn ($c) => ($c['gender'] ?? '') === 'male')->take($topN);
                    $femaleFinalists = $overallResults->filter(fn ($c) => ($c['gender'] ?? '') === 'female')->take($topN);
                    $overallResults = $maleFinalists->merge($femaleFinalists)->values();
                } else {
                    $overallResults = $overallResults->take($topN);
                }
            }
        }

        // Compute Minor Awards data
        $minorAwards = $this->scoreCalculationService->calculateMinorAwardsByStage($pageant, 'semi-final');

        return Inertia::render('Tabulator/Print', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'contestant_type' => $pageant->contestant_type,
                'date' => $pageant->pageant_date?->format('F j, Y'),
                'venue' => $pageant->venue,
                'location' => $pageant->location,
                'number_of_winners' => $pageant->getNumberOfWinners(),
            ],
            'roundTypes' => $uniqueRoundTypes,
            'resultsByRoundType' => $resultsByRoundType,
            'resultsOverall' => $overallResults,
            'resultsSemiFinal' => $semiResults,
            'resultsFinal' => $finalResults,
            'minorAwards' => $minorAwards,
            'judges' => $judges,
            'tabulator' => [
                'id' => $tabulator->id,
                'name' => $tabulator->name,
            ],
            'allRoundsLocked' => $allRoundsLocked,
            'unlockedRounds' => $unlockedRounds,
        ]);
    }

    /**
     * Show Minor Awards (best per semi-final round) page
     */
    public function minorAwards($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $awardsByRound = $this->scoreCalculationService->calculateMinorAwardsByStage($pageant, 'semi-final');

        // Judges for attestations
        $judges = $pageant->judges->map(function ($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
                'role' => $judge->pivot->role ?? 'Judge',
            ];
        });

        return Inertia::render('Tabulator/MinorAwards', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'contestant_type' => $pageant->contestant_type,
                'logo' => $pageant->logo,
                'date' => $pageant->pageant_date?->format('F j, Y'),
                'venue' => $pageant->venue,
                'location' => $pageant->location,
            ],
            'awardsByRound' => $awardsByRound,
            'judges' => $judges,
        ]);
    }

    /**
     * Printable Minor Awards (semi-final)
     */
    public function minorAwardsPrint($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $awardsByRound = $this->scoreCalculationService->calculateMinorAwardsByStage($pageant, 'semi-final');

        $judges = $pageant->judges->map(function ($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
                'role' => $judge->pivot->role ?? 'Judge',
            ];
        });

        return Inertia::render('Tabulator/MinorAwardsPrint', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'contestant_type' => $pageant->contestant_type,
                'logo' => $pageant->logo,
                'date' => $pageant->pageant_date?->format('F j, Y'),
                'venue' => $pageant->venue,
                'location' => $pageant->location,
            ],
            'awardsByRound' => $awardsByRound,
            'judges' => $judges,
        ]);
    }

    /**
     * Set the current round for a pageant
     */
    public function setCurrentRound(Request $request, $pageantId)
    {
        $request->validate([
            'round_id' => 'required|exists:rounds,id',
        ]);

        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        // Verify the round belongs to this pageant
        $round = $pageant->rounds()->findOrFail($request->round_id);

        $pageant->setCurrentRound($request->round_id);

        // Broadcast round change
        RoundUpdated::dispatch(
            $round,
            $pageant,
            'set_current',
            "Round '{$round->name}' is now the current round"
        );

        return back()->with('success', 'Current round set to: '.$round->name);
    }

    /**
     * Lock a round for editing
     */
    public function lockRound($pageantId, $roundId)
    {
        if (! Auth::user()->hasPermission('tabulator_tabulate_results')) {
            return redirect()->back()->with('error', 'You do not have permission to lock rounds.');
        }

        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $round = $pageant->rounds()->findOrFail($roundId);
        $round->lock($tabulator->id);

        // Broadcast round lock
        RoundUpdated::dispatch(
            $round,
            $pageant,
            'locked',
            "Round '{$round->name}' has been locked for editing"
        );

        return back()->with('success', 'Round "'.$round->name.'" has been locked for editing.');
    }

    /**
     * Unlock a round for editing
     */
    public function unlockRound($pageantId, $roundId)
    {
        if (! Auth::user()->hasPermission('tabulator_tabulate_results')) {
            return redirect()->back()->with('error', 'You do not have permission to unlock rounds.');
        }

        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $round = $pageant->rounds()->findOrFail($roundId);
        $round->unlock();

        // Broadcast round unlock
        RoundUpdated::dispatch(
            $round,
            $pageant,
            'unlocked',
            "Round '{$round->name}' has been unlocked for editing"
        );

        return back()->with('success', 'Round "'.$round->name.'" has been unlocked for editing.');
    }

    /**
     * Get round management data for tabulator
     */
    public function roundManagement($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $rounds = $pageant->rounds()->with('lockedBy')->get()->map(function ($round) {
            return [
                'id' => $round->id,
                'name' => $round->name,
                'description' => $round->description,
                'identifier' => $round->identifier,
                'type' => $round->type,
                'top_n_proceed' => $round->top_n_proceed,
                'weight' => $round->weight,
                'display_order' => $round->display_order,
                'is_active' => $round->is_active,
                'is_locked' => $round->is_locked,
                'locked_at' => $round->locked_at,
                'locked_by' => $round->lockedBy ? [
                    'id' => $round->lockedBy->id,
                    'name' => $round->lockedBy->name,
                ] : null,
            ];
        });

        // Get all judges assigned to this pageant
        $judges = $pageant->judges()->get()->map(function ($judge) use ($pageant, $rounds) {
            $judgeData = [
                'id' => $judge->id,
                'name' => $judge->name,
                'email' => $judge->email,
                'rounds_progress' => [],
            ];

            // Calculate progress for each round
            foreach ($rounds as $round) {
                $roundId = $round['id'];

                // Get total criteria count for this round
                $totalCriteria = $pageant->rounds()
                    ->where('id', $roundId)
                    ->first()
                    ->criteria()
                    ->count();

                // Get total contestants for this pageant
                $totalContestants = $pageant->contestants()->count();

                // Total scores needed = criteria count × contestant count
                $totalScoresNeeded = $totalCriteria * $totalContestants;

                // Get scores submitted by this judge for this round
                $scoresSubmitted = Score::where('judge_id', $judge->id)
                    ->where('round_id', $roundId)
                    ->where('pageant_id', $pageant->id)
                    ->count();

                // Calculate percentage
                $percentage = $totalScoresNeeded > 0
                    ? round(($scoresSubmitted / $totalScoresNeeded) * 100, 1)
                    : 0;

                $judgeData['rounds_progress'][$roundId] = [
                    'submitted' => $scoresSubmitted,
                    'total' => $totalScoresNeeded,
                    'percentage' => $percentage,
                ];
            }

            return $judgeData;
        });

        return Inertia::render('Tabulator/RoundManagement', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'current_round_id' => $pageant->current_round_id,
                'current_round' => $pageant->getCurrentRound() ? [
                    'id' => $pageant->getCurrentRound()->id,
                    'name' => $pageant->getCurrentRound()->name,
                ] : null,
                'status' => $pageant->status,
                'is_completed' => $pageant->isCompleted(),
            ],
            'rounds' => $rounds,
            'judges' => $judges,
        ]);
    }

    /**
     * Get aggregated score for a specific judge-contestant-round combination
     */
    public function getAggregatedScore($pageantId, $roundId, Request $request)
    {
        $tabulator = Auth::user();

        // Validate tabulator has access to this pageant
        $this->getPageantForTabulator($pageantId, $tabulator->id);

        $request->validate([
            'judge_id' => 'required|exists:users,id',
            'contestant_id' => 'required|exists:contestants,id',
        ]);

        $judgeId = $request->judge_id;
        $contestantId = $request->contestant_id;

        // Get all scores for this judge-contestant-round combination
        $scores = Score::where('pageant_id', $pageantId)
            ->where('round_id', $roundId)
            ->where('judge_id', $judgeId)
            ->where('contestant_id', $contestantId)
            ->with('criteria')
            ->get();

        if ($scores->isEmpty()) {
            return response()->json(['aggregated_score' => null]);
        }

        // Calculate weighted average using the service
        $aggregatedScore = $this->scoreCalculationService->calculateJudgeContestantScore(
            $judgeId,
            $contestantId,
            $roundId,
            $pageantId
        );

        if ($aggregatedScore !== null) {
            $aggregatedScore = round($aggregatedScore, 2);
        }

        return response()->json(['aggregated_score' => $aggregatedScore]);
    }

    /**
     * Get audit logs for score edits in a specific round
     */
    public function getScoreAuditLogs($pageantId, $roundId, Request $request)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
        $round = $pageant->rounds()->findOrFail($roundId);

        // Get all score IDs for this round
        $scoreIds = Score::where('pageant_id', $pageantId)
            ->where('round_id', $roundId)
            ->pluck('id');

        // Build query for audit logs
        $query = AuditLog::whereIn('target_entity', ['Score'])
            ->whereIn('action_type', ['SCORE_UPDATED', 'SCORE_CREATED'])
            ->whereIn('target_id', $scoreIds);

        // Apply action type filter
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Apply user filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply search filter (search in details)
        if ($request->filled('search')) {
            $query->where('details', 'like', '%'.$request->search.'%');
        }

        // Get pagination parameters
        $perPage = $request->input('per_page', 20);
        $perPage = min(max((int) $perPage, 5), 100); // Limit between 5 and 100

        // Fetch paginated audit logs
        $auditLogs = $query->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                    ] : null,
                    'user_role' => $log->user_role,
                    'action_type' => $log->action_type,
                    'details' => $log->details,
                    'created_at' => $log->created_at->format('M d, Y h:i A'),
                    'timestamp' => $log->created_at->timestamp,
                ];
            });

        // Get unique users who have created audit logs for filtering
        $users = AuditLog::whereIn('target_entity', ['Score'])
            ->whereIn('action_type', ['SCORE_UPDATED', 'SCORE_CREATED'])
            ->whereIn('target_id', $scoreIds)
            ->with('user:id,name')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ]);

        return response()->json([
            'audit_logs' => $auditLogs->items(),
            'pagination' => [
                'current_page' => $auditLogs->currentPage(),
                'last_page' => $auditLogs->lastPage(),
                'per_page' => $auditLogs->perPage(),
                'total' => $auditLogs->total(),
                'from' => $auditLogs->firstItem(),
                'to' => $auditLogs->lastItem(),
            ],
            'filters' => [
                'users' => $users,
            ],
            'round_name' => $round->name,
        ]);
    }

    /**
     * Normalize score based on the pageant's scoring system
     */
    private function normalizeScore($score, $scoringSystem)
    {
        switch ($scoringSystem) {
            case 'percentage':
                // Percentage system: ensure score is between 0-100
                return max(0, min(100, $score));

            case '1-10':
                // 1-10 scale: ensure score is between 1-10
                return max(1, min(10, $score));

            case '1-5':
                // 1-5 scale: ensure score is between 1-5
                return max(1, min(5, $score));

            case 'points':
                // Points system: ensure score doesn't exceed 50
                return max(0, min(50, $score));

            default:
                // Default to percentage system
                return max(0, min(100, $score));
        }
    }

    /**
     * Notify judges to start scoring
     */
    public function notifyJudges(Request $request, $pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $request->validate([
            'judge_ids' => 'required|array',
            'judge_ids.*' => 'exists:users,id',
            'message' => 'required|string|max:500',
            'round_id' => 'nullable|exists:rounds,id',
        ]);

        $roundName = null;
        if ($request->round_id) {
            $round = Round::findOrFail($request->round_id);
            $roundName = $round->name;
        }

        $title = $request->round_id
            ? "Time to Score: {$roundName}"
            : 'Scoring Notification';

        $notifiedCount = 0;
        foreach ($request->judge_ids as $judgeId) {
            // Verify judge is assigned to this pageant
            if ($pageant->judges()->where('user_id', $judgeId)->exists()) {
                event(new \App\Events\JudgeNotified(
                    $judgeId,
                    $pageantId,
                    $request->message,
                    $title,
                    $roundName,
                    'score_request'
                ));
                $notifiedCount++;
            }
        }

        return redirect()->back()->with('success', "Successfully notified {$notifiedCount} judge(s).");
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
        ])->whereHas('tabulators', function ($query) use ($tabulatorId) {
            $query->where('user_id', $tabulatorId);
        })->findOrFail($pageantId);

        return $pageant;
    }
}
