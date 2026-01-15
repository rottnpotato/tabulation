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

        // Check if pageant has reached required judges limit
        if ($pageant->required_judges && $pageant->judges()->count() >= $pageant->required_judges) {
            return back()->withErrors(['message' => 'This pageant has already reached the maximum number of judges.']);
        }

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
                'pageant' => [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'contestant_type' => $pageant->contestant_type ?? 'solo',
                    'ranking_method' => $pageant->ranking_method ?? 'score_average',
                    'tie_handling' => $pageant->tie_handling ?? 'average',
                ],
                'rounds' => $rounds,
                'contestants' => [],
                'judges' => [],
                'currentRound' => null,
                'scores' => [],
                'totalScores' => [],
            ]);
        }

        $contestants = $pageant->contestants()->with('members:id,name,gender')->get()->map(function ($contestant) {
            return [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->is_pair ? ($contestant->display_name ?? $contestant->name) : $contestant->name,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                'is_pair' => (bool) $contestant->is_pair,
                'gender' => $contestant->gender,
                'members_text' => $contestant->is_pair ? $contestant->members->pluck('name')->implode(' & ') : null,
                'members' => $contestant->is_pair ? $contestant->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'gender' => $member->gender,
                    ];
                })->values()->all() : null,
                'backed_out' => (bool) $contestant->backed_out,
                'backed_out_reason' => $contestant->backed_out_reason,
            ];
        });

        $judges = $pageant->judges->map(function ($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
            ];
        });

        // Get aggregated scores per judge per contestant per round
        // Calculate sum of all criteria scores for each judge-contestant pair
        $scoresQuery = Score::where('pageant_id', $pageantId)
            ->where('round_id', $roundId)
            ->with(['criteria'])
            ->get()
            ->groupBy(['contestant_id', 'judge_id']);

        $scores = [];
        $totalScores = [];
        $weightedScores = [];

        foreach ($scoresQuery as $contestantId => $judgeScores) {
            foreach ($judgeScores as $judgeId => $judgeScoreRecords) {
                // Calculate simple sum and weighted sum of all criteria scores
                $judgeTotal = 0;
                $weightedTotal = 0;

                foreach ($judgeScoreRecords as $scoreRecord) {
                    $judgeTotal += $scoreRecord->score;
                    // Calculate weighted score: score * weight / 100
                    $weight = $scoreRecord->criteria->weight ?? 0;
                    $weightedTotal += ($scoreRecord->score * $weight / 100);
                }

                $key = $contestantId.'-'.$judgeId.'-'.$roundId;
                // Store the simple sum of scores for this judge (raw display)
                $scores[$key] = round($judgeTotal, 2);
                $totalScores[$key] = round($judgeTotal, 2);
                // Store the weighted score (for ranking and tie explanation)
                $weightedScores[$key] = round($weightedTotal, 2);
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
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'contestant_type' => $pageant->contestant_type ?? 'solo',
                'ranking_method' => $pageant->ranking_method ?? 'score_average',
                'tie_handling' => $pageant->tie_handling ?? 'average',
            ],
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
            'totalScores' => $totalScores,
            'weightedScores' => $weightedScores,
            'criteria' => $criteria,
            'detailedScores' => $detailedScores,
        ]);
    }

    /**
     * Show results for a pageant
    /**
     * Show comprehensive results for tabulator
     *
     * For Results page:
     * - "Overall Tally" shows ONLY the final round results (not cumulative)
     * - Individual round views show scores up to that specific round
     * - Qualification badges indicate who advanced to the next round
     * - This matches the print page behavior for consistency
     */
    public function results($pageantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        // Sort rounds by display_order
        $sortedRounds = $pageant->rounds->sortBy('display_order');

        // Group rounds by type to find the last round of each type and get top_n_proceed per stage
        $roundsByType = $sortedRounds->groupBy('type');
        $lastRoundIdsByType = [];
        $topNByType = [];

        foreach ($roundsByType as $type => $rounds) {
            $lastRound = $rounds->sortByDesc('display_order')->first();
            if ($lastRound) {
                $lastRoundIdsByType[$type] = $lastRound->id;

                // Find top_n_proceed for this stage type (from any round of this type that has it set)
                $topN = $rounds->whereNotNull('top_n_proceed')
                    ->where('top_n_proceed', '>', 0)
                    ->sortByDesc('display_order')
                    ->first()?->top_n_proceed;

                $topNByType[$type] = $topN;
            }
        }

        $rounds = $sortedRounds->map(function ($round) use ($lastRoundIdsByType, $topNByType) {
            $isLastOfType = ($lastRoundIdsByType[$round->type] ?? null) === $round->id;

            // Attach top_n_proceed to the last round of each type for badge display
            $topNProceed = $isLastOfType ? ($topNByType[$round->type] ?? null) : null;

            return [
                'id' => $round->id,
                'name' => $round->name,
                'type' => $round->type,
                'weight' => $round->weight,
                'top_n_proceed' => $topNProceed,
                'display_order' => $round->display_order,
                'is_last_of_type' => $isLastOfType,
            ];
        })->values();

        // Calculate overall results with all round scores displayed
        // But rank by ONLY the final round score (not cumulative)
        // Force fresh calculation by disabling cache to ensure latest scores are shown
        $rankingMethod = $pageant->ranking_method ?? 'score_average';

        // For ordinal ranking, we need to get ALL contestants with scores first,
        // then apply ordinal ranking only to finalists - otherwise we'd cut non-finalists
        if ($rankingMethod === 'ordinal') {
            // Get all contestants with all their scores (like score_average does)
            $contestants = $this->scoreCalculationService->calculateAllContestantsWithScores($pageant, false);

            // Find the last final round for ordinal ranking
            $lastFinalRound = $pageant->rounds
                ->filter(fn ($round) => strtolower($round->type) === 'final')
                ->sortByDesc('display_order')
                ->first();

            if ($lastFinalRound) {
                // Get ordinal-ranked finalists for the last round
                $ordinalFinalists = $this->scoreCalculationService->calculateOrdinalForRound($pageant, $lastFinalRound, false);
                $finalistRanks = collect($ordinalFinalists)->keyBy('id');

                // Merge ordinal ranks into all contestants
                $contestants = collect($contestants)->map(function ($contestant) use ($lastFinalRound, $finalistRanks) {
                    $hasFinalScore = isset($contestant['scores'][$lastFinalRound->name]) && $contestant['scores'][$lastFinalRound->name] !== null;
                    $finalRoundScore = $hasFinalScore ? $contestant['scores'][$lastFinalRound->name] : null;

                    // Get ordinal rank if this contestant is a finalist
                    $ordinalRank = null;
                    $totalRankSum = $contestant['totalRankSum'] ?? 0;
                    if ($finalistRanks->has($contestant['id'])) {
                        $finalist = $finalistRanks->get($contestant['id']);
                        $ordinalRank = $finalist['rank'] ?? null;
                        $totalRankSum = $finalist['totalRankSum'] ?? $totalRankSum;
                    }

                    return array_merge($contestant, [
                        'finalScore' => $finalRoundScore,
                        'totalScore' => $finalRoundScore,
                        'hasQualifiedForFinal' => $hasFinalScore,
                        'rank' => $ordinalRank ?? 0,
                        'totalRankSum' => $totalRankSum,
                    ]);
                })->toArray();

                // Sort: finalists first (by rank), then non-finalists
                usort($contestants, function ($a, $b) {
                    $aIsFinalist = ($a['rank'] ?? 0) > 0;
                    $bIsFinalist = ($b['rank'] ?? 0) > 0;

                    // Finalists come first
                    if ($aIsFinalist && ! $bIsFinalist) {
                        return -1;
                    }
                    if (! $aIsFinalist && $bIsFinalist) {
                        return 1;
                    }

                    // Both finalists: sort by rank (ascending)
                    if ($aIsFinalist && $bIsFinalist) {
                        return ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999);
                    }

                    // Both non-finalists: sort by number
                    return ($a['number'] ?? 0) <=> ($b['number'] ?? 0);
                });
            }
        } else {
            // Standard path for score_average and rank_sum methods
            $contestants = $this->scoreCalculationService->calculatePageantFinalScores($pageant, false);

            // Find the last final round to use for ranking
            $lastFinalRound = $pageant->rounds
                ->filter(fn ($round) => strtolower($round->type) === 'final')
                ->sortByDesc('display_order')
                ->first();

            if ($lastFinalRound) {
                $rankingMethod = $pageant->ranking_method ?? 'score_average';
                $tieHandling = $pageant->tie_handling ?? 'average';

                if ($rankingMethod === 'rank_sum') {
                    $finalScoreMode = $pageant->final_score_mode ?? 'fresh';

                    // Calculate rank sums based on final_score_mode
                    $contestants = collect($contestants)->map(function ($contestant) use ($lastFinalRound, $finalScoreMode, $pageant) {
                        $hasFinalScore = isset($contestant['scores'][$lastFinalRound->name]) && $contestant['scores'][$lastFinalRound->name] !== null;

                        if ($finalScoreMode === 'inherit') {
                            // Inherit mode: calculate weighted rank sums per stage using inheritance percentages
                            $finalScoreInheritance = $pageant->final_score_inheritance ?? [];
                            $totalRankSum = 0;
                            $rawScoreSum = 0;
                            $weightedRawTotal = 0; // Sum of (stage raw total × stage percentage)
                            $inheritanceBreakdown = [];
                            $stageRankSums = [];
                            $stageScoreSums = [];

                            if (isset($contestant['judgeRanks']) && is_array($contestant['judgeRanks'])) {
                                foreach ($contestant['judgeRanks'] as $roundName => $roundData) {
                                    if (isset($roundData['ranks']) && is_array($roundData['ranks'])) {
                                        $roundRankSum = array_sum($roundData['ranks']);
                                        $totalRankSum += $roundRankSum;

                                        // Find the round type for this round
                                        $round = $pageant->rounds->firstWhere('name', $roundName);
                                        $roundType = strtolower($round->type ?? 'preliminary');

                                        if (! isset($stageRankSums[$roundType])) {
                                            $stageRankSums[$roundType] = 0;
                                        }
                                        $stageRankSums[$roundType] += $roundRankSum;
                                    }
                                }
                            }
                            // Sum all scores for display
                            if (isset($contestant['scores']) && is_array($contestant['scores'])) {
                                foreach ($contestant['scores'] as $roundName => $score) {
                                    if ($score !== null) {
                                        $rawScoreSum += $score;

                                        // Find the round type
                                        $round = $pageant->rounds->firstWhere('name', $roundName);
                                        $roundType = strtolower($round->type ?? 'preliminary');

                                        if (! isset($stageScoreSums[$roundType])) {
                                            $stageScoreSums[$roundType] = 0;
                                        }
                                        $stageScoreSums[$roundType] += $score;
                                    }
                                }
                            }

                            // Build inheritance breakdown and calculate weightedRawTotal
                            foreach ($stageScoreSums as $stageType => $stageScore) {
                                $percentage = $finalScoreInheritance[$stageType] ?? 0;
                                $inheritancePercentage = $percentage / 100;
                                $rankSum = $stageRankSums[$stageType] ?? 0;

                                // Calculate weighted raw total: stage raw score × percentage
                                $rawContribution = $stageScore * $inheritancePercentage;
                                $weightedRawTotal += $rawContribution;

                                $inheritanceBreakdown[$stageType] = [
                                    'stageType' => ucwords(str_replace('-', ' ', $stageType)),
                                    'percentage' => $percentage,
                                    'stageRawTotal' => round($stageScore, 2),
                                    'rawContribution' => round($rawContribution, 2),
                                    'stageAverage' => round($stageScore, 2), // Raw score for display
                                    'contribution' => round($rankSum, 2), // Rank sum per stage
                                ];
                            }

                            return array_merge($contestant, [
                                'finalScore' => $hasFinalScore ? $totalRankSum : null,
                                'totalScore' => $hasFinalScore ? $totalRankSum : null,
                                'totalRankSum' => $hasFinalScore ? $totalRankSum : 0,
                                'rawScore' => $rawScoreSum,
                                'weightedRawTotal' => $hasFinalScore ? round($weightedRawTotal, 2) : null,
                                'displayScore' => $rawScoreSum, // Shows cumulative score for Inherit mode
                                'hasQualifiedForFinal' => $hasFinalScore,
                                'inheritanceBreakdown' => ! empty($inheritanceBreakdown) ? $inheritanceBreakdown : null,
                            ]);
                        } else {
                            // Fresh Start mode: only final round's rank sum
                            $finalRoundRankSum = 0;
                            $rawScore = 0;
                            if ($hasFinalScore) {
                                if (isset($contestant['judgeRanks'][$lastFinalRound->name]['ranks'])) {
                                    $finalRoundRankSum = array_sum($contestant['judgeRanks'][$lastFinalRound->name]['ranks']);
                                }
                                $rawScore = $contestant['scores'][$lastFinalRound->name];
                            }

                            return array_merge($contestant, [
                                'finalScore' => $hasFinalScore ? $finalRoundRankSum : null,
                                'totalScore' => $hasFinalScore ? $finalRoundRankSum : null,
                                'totalRankSum' => $hasFinalScore ? $finalRoundRankSum : 0,
                                'rawScore' => $rawScore,
                                'displayScore' => $rawScore, // Shows only final round score for Fresh Start
                                'hasQualifiedForFinal' => $hasFinalScore,
                            ]);
                        }
                    })->toArray();

                    // Sort and detect ties for tie-breaking info
                    usort($contestants, function ($a, $b) {
                        $rankSumA = $a['totalRankSum'] ?? PHP_INT_MAX;
                        $rankSumB = $b['totalRankSum'] ?? PHP_INT_MAX;

                        if ($rankSumA !== $rankSumB) {
                            return $rankSumA <=> $rankSumB;
                        }

                        $rawScoreA = $a['rawScore'] ?? 0;
                        $rawScoreB = $b['rawScore'] ?? 0;

                        return $rawScoreB <=> $rawScoreA;
                    });

                    // Add tie-break info for contestants with same rank sum
                    $prevRankSum = null;
                    $prevRawScore = null;
                    $prevIndex = null;
                    foreach ($contestants as $i => &$contestant) {
                        $currentRankSum = $contestant['totalRankSum'] ?? 0;
                        $currentRawScore = $contestant['rawScore'] ?? 0;

                        if ($prevRankSum !== null && $currentRankSum == $prevRankSum && $currentRawScore != $prevRawScore) {
                            // This contestant was in a tie that was resolved by raw score
                            $contestant['tieBreakInfo'] = "Tie resolved by score ({$currentRawScore} vs {$prevRawScore})";
                            if ($prevIndex !== null) {
                                $contestants[$prevIndex]['tieBreakInfo'] = "Tie resolved by score ({$prevRawScore} vs {$currentRawScore})";
                            }
                        }

                        $prevRankSum = $currentRankSum;
                        $prevRawScore = $currentRawScore;
                        $prevIndex = $i;
                    }
                    unset($contestant);

                    // Re-rank contestants
                    $contestants = $this->scoreCalculationService->applyGenderSeparatedRanking(
                        $contestants,
                        $pageant,
                        'totalRankSum',
                        'asc',
                        $tieHandling
                    );
                } else {
                    // For score_average: check if inherit mode with percentages
                    $finalScoreMode = $pageant->final_score_mode ?? 'fresh';
                    $finalScoreInheritance = $pageant->final_score_inheritance ?? [];

                    if ($finalScoreMode === 'inherit' && ! empty($finalScoreInheritance)) {
                        // Inherit mode with percentages: calculate weighted average per stage, then apply inheritance percentages
                        $contestants = collect($contestants)->map(function ($contestant) use ($pageant, $lastFinalRound, $finalScoreInheritance) {
                            $hasFinalScore = isset($contestant['scores'][$lastFinalRound->name]) && $contestant['scores'][$lastFinalRound->name] !== null;

                            // Calculate score per stage type
                            $stageScores = [];
                            $stageWeights = [];
                            $stageRawTotals = []; // Sum of raw scores per stage (for weightedRawTotal calculation)
                            $inheritanceBreakdown = [];

                            foreach ($pageant->rounds as $round) {
                                $roundType = strtolower($round->type ?? 'preliminary');
                                $roundScore = $contestant['scores'][$round->name] ?? null;

                                if ($roundScore !== null && isset($finalScoreInheritance[$roundType])) {
                                    if (! isset($stageScores[$roundType])) {
                                        $stageScores[$roundType] = 0;
                                        $stageWeights[$roundType] = 0;
                                        $stageRawTotals[$roundType] = 0;
                                    }
                                    $roundWeight = $round->weight ?? 1;
                                    if ($roundWeight <= 0) {
                                        $roundWeight = 1;
                                    }
                                    $weightedRoundScore = $roundScore * $roundWeight;
                                    $stageScores[$roundType] += $weightedRoundScore;
                                    $stageWeights[$roundType] += $roundWeight;
                                    $stageRawTotals[$roundType] += $roundScore; // Sum raw scores per stage
                                }
                            }

                            // Apply inheritance percentages to stage averages
                            $finalScore = 0;
                            $weightedRawTotal = 0; // Sum of (stage raw total × stage percentage)
                            foreach ($stageScores as $stageType => $weightedSum) {
                                if ($stageWeights[$stageType] > 0) {
                                    $stageAverage = $weightedSum / $stageWeights[$stageType];
                                    $inheritancePercentage = ($finalScoreInheritance[$stageType] ?? 0) / 100;
                                    $contribution = $stageAverage * $inheritancePercentage;
                                    $finalScore += $contribution;

                                    // Calculate weighted raw total: stage raw score × percentage
                                    $stageRawTotal = $stageRawTotals[$stageType] ?? 0;
                                    $rawContribution = $stageRawTotal * $inheritancePercentage;
                                    $weightedRawTotal += $rawContribution;

                                    // Store breakdown for display
                                    $inheritanceBreakdown[$stageType] = [
                                        'stageType' => ucwords(str_replace('-', ' ', $stageType)),
                                        'percentage' => $finalScoreInheritance[$stageType] ?? 0,
                                        'stageAverage' => round($stageAverage, 2),
                                        'stageRawTotal' => round($stageRawTotal, 2),
                                        'rawContribution' => round($rawContribution, 2),
                                        'contribution' => round($contribution, 2),
                                    ];
                                }
                            }

                            return array_merge($contestant, [
                                'finalScore' => $hasFinalScore ? round($finalScore, 2) : null,
                                'totalScore' => $hasFinalScore ? round($finalScore, 2) : null,
                                'weightedRawTotal' => $hasFinalScore ? round($weightedRawTotal, 2) : null,
                                'hasQualifiedForFinal' => $hasFinalScore,
                                'inheritanceBreakdown' => $inheritanceBreakdown,
                            ]);
                        })->toArray();
                    } else {
                        // Fresh mode: use only the final round score for ranking
                        $contestants = collect($contestants)->map(function ($contestant) use ($lastFinalRound) {
                            $hasFinalScore = isset($contestant['scores'][$lastFinalRound->name]) && $contestant['scores'][$lastFinalRound->name] !== null;
                            $finalRoundScore = $hasFinalScore ? $contestant['scores'][$lastFinalRound->name] : null;

                            return array_merge($contestant, [
                                'finalScore' => $finalRoundScore,
                                'totalScore' => $finalRoundScore,
                                'hasQualifiedForFinal' => $hasFinalScore,
                            ]);
                        })->toArray();
                    }

                    // Re-rank contestants based on final score (descending - higher is better)
                    $contestants = $this->scoreCalculationService->applyGenderSeparatedRanking(
                        $contestants,
                        $pageant,
                        'finalScore',
                        'desc',
                        $tieHandling
                    );
                }
            }
        }

        // Calculate results for each round/stage using unified calculation method
        $roundResults = [];
        $orderedRounds = $pageant->rounds->sortBy('display_order');

        foreach ($orderedRounds as $currentRound) {
            // Force fresh calculation by disabling cache to ensure latest scores are shown
            $roundContestants = $this->scoreCalculationService->calculateRoundViewScores($pageant, $currentRound, false);

            // For round results, use the round's actual top_n_proceed value from the database
            // This allows advancement badges to show for any round that has top_n_proceed set
            $roundTopN = $currentRound->top_n_proceed;

            $roundResults['round_'.$currentRound->id] = [
                'contestants' => $roundContestants,
                'top_n_proceed' => $roundTopN,
            ];
        }

        // Helper function to format contestant data consistently
        $formatContestantData = function ($contestant, $topNProceed = null) use ($pageant) {
            $contestantModel = $pageant->contestants->firstWhere('id', $contestant['id']);
            $memberNames = [];
            $memberGenders = [];

            if ($contestantModel && $contestantModel->is_pair && $contestantModel->members->isNotEmpty()) {
                foreach ($contestantModel->members as $member) {
                    $memberNames[] = $member->name;
                    $memberGenders[] = $member->gender;
                }
            }

            // Determine qualified status based on rank and top_n_proceed
            $rank = $contestant['rank'] ?? 0;
            $qualified = true; // Default to qualified if no cutoff
            $qualificationCutoff = null;

            if ($topNProceed !== null && $topNProceed > 0) {
                $qualificationCutoff = $topNProceed;
                $qualified = $rank > 0 && $rank <= $topNProceed;
            }

            // Unified score: use finalScore if available, fallback to totalScore
            $totalScore = $contestant['finalScore'] ?? $contestant['totalScore'] ?? 0;

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
                'displayScores' => $contestant['displayScores'] ?? [],
                'displayTotal' => $contestant['displayTotal'] ?? 0,
                'totalScore' => $totalScore,
                'finalScore' => $totalScore,
                'totalRankSum' => $contestant['totalRankSum'] ?? 0,
                'judgeRanks' => $contestant['judgeRanks'] ?? [],
                'rank' => $rank,
                'qualified' => $qualified,
                'qualification_cutoff' => $qualificationCutoff,
                'inheritanceBreakdown' => $contestant['inheritanceBreakdown'] ?? null,
            ];
        };

        // Get the number of winners for overall results
        $numberOfWinners = $pageant->getNumberOfWinners();

        // Calculate raw display scores (sum of all judge totals per round) for frontend display only
        // This does NOT affect the actual ranking calculation which uses weighted averages
        $displayScoresPerContestant = $this->calculateDisplayScores($pageant);

        // Find the last final round for Fresh Start mode
        $lastFinalRound = $pageant->rounds
            ->filter(fn ($round) => strtolower($round->type) === 'final')
            ->sortByDesc('display_order')
            ->first();
        $finalScoreMode = $pageant->final_score_mode ?? 'fresh';
        $rankingMethod = $pageant->ranking_method ?? 'score_average';

        // Merge display scores into contestant data
        $contestants = collect($contestants)->map(function ($contestant) use ($displayScoresPerContestant, $lastFinalRound, $finalScoreMode, $rankingMethod) {
            $contestantId = $contestant['id'];
            $displayScores = $displayScoresPerContestant[$contestantId] ?? [];

            // Calculate display total based on fresh_start vs inherit mode (only for rank_sum)
            if ($rankingMethod === 'rank_sum' && $lastFinalRound) {
                $hasFinalScore = $contestant['hasQualifiedForFinal'] ?? false;

                if ($finalScoreMode === 'inherit') {
                    // Inherit mode: sum of all round raw scores
                    $displayTotal = $hasFinalScore ? array_sum($displayScores) : 0;
                } else {
                    // Fresh Start mode: only final round's raw score
                    $displayTotal = $hasFinalScore
                        ? ($displayScores[$lastFinalRound->name] ?? 0)
                        : 0;
                }
            } else {
                // For score_average or no final round: sum all rounds
                $displayTotal = array_sum($displayScores);
            }

            return array_merge($contestant, [
                'displayScores' => $displayScores,
                'displayTotal' => round($displayTotal, 2),
            ]);
        })->toArray();

        // Format overall contestants with qualification based on number of winners
        $contestants = collect($contestants)->map(function ($contestant) use ($formatContestantData, $numberOfWinners) {
            return $formatContestantData($contestant, $numberOfWinners);
        });

        // Format round-specific results with their respective top_n_proceed
        $formattedRoundResults = [];
        foreach ($roundResults as $key => $result) {
            $topN = $result['top_n_proceed'];
            $formattedRoundResults[$key] = [
                'contestants' => collect($result['contestants'])->map(function ($contestant) use ($formatContestantData, $topN, $displayScoresPerContestant) {
                    // Merge display scores into round contestant data
                    $contestantId = $contestant['id'];
                    $displayScores = $displayScoresPerContestant[$contestantId] ?? [];
                    $displayTotal = array_sum($displayScores);

                    $contestantWithDisplay = array_merge($contestant, [
                        'displayScores' => $displayScores,
                        'displayTotal' => round($displayTotal, 2),
                    ]);

                    return $formatContestantData($contestantWithDisplay, $topN);
                })->values()->all(),
                'top_n_proceed' => $topN,
            ];
        }

        return Inertia::render('Tabulator/Results', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'contestant_type' => $pageant->contestant_type,
                'number_of_winners' => $pageant->getNumberOfWinners(),
                'ranking_method' => $pageant->ranking_method ?? 'score_average',
                'tie_handling' => $pageant->tie_handling ?? 'average',
                'final_score_mode' => $pageant->final_score_mode ?? 'fresh',
                'final_score_inheritance' => $pageant->final_score_inheritance ?? [],
            ],
            'contestants' => $contestants,
            'rounds' => $rounds,
            'roundResults' => $formattedRoundResults,
        ]);
    }

    /**
     * Show printable results for a pageant
     *
     * For Print page:
     * - "Overall" shows ONLY the final round results (not cumulative)
     * - This is what gets printed as the official final results
     * - Semi-final and other round types show stage-specific results
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

        // For Print page, "overall" means the FINAL ROUND only (not cumulative)
        // We'll compute this later after we have the final round results
        $overallResults = collect();

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

        // Get unique round types from pageant with labels and top_n_proceed info
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
                    'top_n_proceed' => $lastRound->top_n_proceed,
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
                    'displayScores' => $contestant['displayScores'] ?? $contestant['scores'] ?? [],
                    'displayTotal' => $contestant['displayTotal'] ?? $contestant['finalScore'] ?? 0,
                    'final_score' => $contestant['finalScore'] ?? 0,
                    'totalScore' => $contestant['finalScore'] ?? 0,
                    'totalRankSum' => $contestant['totalRankSum'] ?? 0,
                    'rank' => $contestant['rank'] ?? 0,
                ];
            });

            // Filter out contestants who have no scores for this stage (didn't qualify/compete)
            $stageResults = $stageResults->filter(function ($contestant) {
                // Only include contestants who have at least one score for this stage
                $scores = $contestant['scores'] ?? [];
                $hasScores = ! empty($scores) && array_filter($scores, fn ($score) => $score !== null && $score > 0);

                return ! empty($hasScores);
            });

            $stageResults = $markQualifiedContestants($stageResults, $roundType)->values();

            // Apply top_n_proceed filter if defined for this round type
            if (isset($roundTypeInfo['top_n_proceed']) && $roundTypeInfo['top_n_proceed'] > 0) {
                $topN = $roundTypeInfo['top_n_proceed'];

                // For pair pageants, filter each gender separately
                if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                    $maleResults = $stageResults->filter(fn ($c) => ($c['gender'] ?? '') === 'male')->take($topN);
                    $femaleResults = $stageResults->filter(fn ($c) => ($c['gender'] ?? '') === 'female')->take($topN);
                    $stageResults = $maleResults->merge($femaleResults)->values();
                } else {
                    $stageResults = $stageResults->take($topN);
                }
            }

            $resultsByRoundType[$roundType] = $stageResults;
        }

        // Get the last final round and use it for "overall" - Print page shows ONLY final round results
        $lastFinalRound = $pageant->rounds
            ->filter(fn ($round) => strtolower($round->type) === 'final')
            ->sortByDesc('display_order')
            ->first();

        // Calculate Overall Tally - same as Results page
        // Shows ALL contestants with ALL round scores, ranked by final round score
        $overallTallyContestants = $this->scoreCalculationService->calculatePageantFinalScores($pageant);

        $rankingMethod = $pageant->ranking_method ?? 'score_average';
        $finalScoreMode = $pageant->final_score_mode ?? 'fresh';

        if ($lastFinalRound) {
            // Replace the cumulative finalScore with just the final round score for ranking
            $overallTallyContestants = collect($overallTallyContestants)->map(function ($contestant) use ($lastFinalRound, $rankingMethod) {
                $hasFinalScore = isset($contestant['scores'][$lastFinalRound->name]) && $contestant['scores'][$lastFinalRound->name] !== null;
                $finalRoundScore = $hasFinalScore ? $contestant['scores'][$lastFinalRound->name] : null;

                $updates = [
                    'finalScore' => $finalRoundScore,
                    'totalScore' => $finalRoundScore,
                    'hasQualifiedForFinal' => $hasFinalScore,
                ];

                // For rank_sum method with fresh mode, update totalRankSum to only the final round's rank sum
                if ($rankingMethod === 'rank_sum' && $hasFinalScore) {
                    // Get the rank sum from the final round only (sum of judge ranks for this contestant in final round)
                    $finalRoundRankSum = 0;
                    if (isset($contestant['judgeRanks'][$lastFinalRound->name]['ranks'])) {
                        $finalRoundRankSum = array_sum($contestant['judgeRanks'][$lastFinalRound->name]['ranks']);
                    }
                    $updates['totalRankSum'] = $finalRoundRankSum;
                } elseif ($rankingMethod === 'rank_sum' && ! $hasFinalScore) {
                    // Non-finalists get a very high rank sum so they sort last
                    $updates['totalRankSum'] = 999999;
                }

                return array_merge($contestant, $updates);
            })->toArray();

            // Re-rank contestants based on ranking method
            if ($rankingMethod === 'rank_sum') {
                // For rank_sum: lower totalRankSum is better (asc)
                $overallTallyContestants = $this->scoreCalculationService->applyGenderSeparatedRanking(
                    $overallTallyContestants,
                    $pageant,
                    'totalRankSum',
                    'asc',
                    $pageant->tie_handling ?? 'average'
                );
            } else {
                // For score_average: higher score is better (desc)
                $overallTallyContestants = $this->scoreCalculationService->applyGenderSeparatedRanking(
                    $overallTallyContestants,
                    $pageant,
                    'finalScore',
                    'desc',
                    $pageant->tie_handling ?? 'average'
                );
            }
        }

        // Calculate raw display scores (sum of all judge totals per round) for frontend display only
        // Same as Results page for consistent score display
        $displayScoresPerContestant = $this->calculateDisplayScores($pageant);

        // Format overall tally results for frontend with displayScores
        $overallTally = collect($overallTallyContestants)->map(function ($contestant) use ($pageant, $displayScoresPerContestant, $lastFinalRound, $rankingMethod, $finalScoreMode) {
            $contestantModel = $pageant->contestants->firstWhere('id', $contestant['id']);
            $memberNames = [];
            $memberGenders = [];

            if ($contestantModel && $contestantModel->is_pair && $contestantModel->members->isNotEmpty()) {
                foreach ($contestantModel->members as $member) {
                    $memberNames[] = $member->name;
                    $memberGenders[] = $member->gender;
                }
            }

            // Get display scores for this contestant
            $contestantId = $contestant['id'];
            $displayScores = $displayScoresPerContestant[$contestantId] ?? [];

            // Calculate display total based on fresh_start vs inherit mode
            if ($rankingMethod === 'rank_sum' && $lastFinalRound) {
                $hasFinalScore = $contestant['hasQualifiedForFinal'] ?? false;

                if ($finalScoreMode === 'inherit') {
                    $displayTotal = $hasFinalScore ? array_sum($displayScores) : 0;
                } else {
                    $displayTotal = $hasFinalScore
                        ? ($displayScores[$lastFinalRound->name] ?? 0)
                        : 0;
                }
            } else {
                $displayTotal = array_sum($displayScores);
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
                'displayScores' => $displayScores,
                'displayTotal' => round($displayTotal, 2),
                'final_score' => $contestant['finalScore'] ?? 0,
                'totalScore' => $contestant['finalScore'] ?? 0,
                'totalRankSum' => $contestant['totalRankSum'] ?? 0,
                'judgeRanks' => $contestant['judgeRanks'] ?? [],
                'rank' => $contestant['rank'] ?? 0,
                'hasQualifiedForFinal' => $contestant['hasQualifiedForFinal'] ?? false,
            ];
        })->values();

        // Final Result (Top N) - Only contestants who competed in the final round
        // Filter to only those with hasQualifiedForFinal = true
        $finalTopN = $overallTally->filter(fn ($c) => $c['hasQualifiedForFinal'] === true);

        // Apply top_n_proceed filter if set
        if ($lastFinalRound && $lastFinalRound->top_n_proceed !== null && $lastFinalRound->top_n_proceed > 0) {
            $topN = $lastFinalRound->top_n_proceed;

            if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                $maleFinalists = $finalTopN->filter(fn ($c) => ($c['gender'] ?? '') === 'male')->take($topN);
                $femaleFinalists = $finalTopN->filter(fn ($c) => ($c['gender'] ?? '') === 'female')->take($topN);
                $finalTopN = $maleFinalists->merge($femaleFinalists)->values();
            } else {
                $finalTopN = $finalTopN->take($topN);
            }
        }

        // Legacy overallResults - keep for backward compatibility (uses final stage results only)
        $overallResults = isset($resultsByRoundType['final']) ? $resultsByRoundType['final'] : $overallTally;

        // Compute Minor Awards data
        $minorAwards = $this->scoreCalculationService->calculateMinorAwardsByStage($pageant, 'semi-final');

        // Get rounds ordered for display in Overall Tally table
        $sortedRounds = $pageant->rounds->sortBy('display_order');

        // Group rounds by type to find advancement info
        $roundsByType = $sortedRounds->groupBy('type');
        $lastRoundIdsByType = [];
        $topNByType = [];

        foreach ($roundsByType as $type => $roundsOfType) {
            $lastRound = $roundsOfType->sortByDesc('display_order')->first();
            if ($lastRound) {
                $lastRoundIdsByType[$type] = $lastRound->id;

                // Find top_n_proceed for this stage type
                $topN = $roundsOfType->whereNotNull('top_n_proceed')
                    ->where('top_n_proceed', '>', 0)
                    ->sortByDesc('display_order')
                    ->first()?->top_n_proceed;

                $topNByType[$type] = $topN;
            }
        }

        $rounds = $sortedRounds->map(function ($round) use ($lastRoundIdsByType, $topNByType) {
            $isLastOfType = ($lastRoundIdsByType[$round->type] ?? null) === $round->id;

            // Attach top_n_proceed to the last round of each type for badge display
            $topNProceed = $isLastOfType ? ($topNByType[$round->type] ?? null) : null;

            return [
                'id' => $round->id,
                'name' => $round->name,
                'type' => $round->type,
                'weight' => $round->weight,
                'top_n_proceed' => $topNProceed,
                'display_order' => $round->display_order,
                'is_last_of_type' => $isLastOfType,
            ];
        })->values();

        // Calculate results for each individual round (same as Results page)
        $roundResults = [];
        $orderedRounds = $pageant->rounds->sortBy('display_order');

        foreach ($orderedRounds as $currentRound) {
            $roundContestants = $this->scoreCalculationService->calculateRoundViewScores($pageant, $currentRound);

            // Format contestants for this round
            $formattedRoundContestants = collect($roundContestants)->map(function ($contestant) use ($pageant) {
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
                    'displayScores' => $contestant['displayScores'] ?? $contestant['scores'] ?? [],
                    'displayTotal' => $contestant['displayTotal'] ?? $contestant['totalScore'] ?? $contestant['finalScore'] ?? 0,
                    'totalScore' => $contestant['totalScore'] ?? $contestant['finalScore'] ?? 0,
                    'final_score' => $contestant['totalScore'] ?? $contestant['finalScore'] ?? 0,
                    'totalRankSum' => $contestant['totalRankSum'] ?? 0,
                    'judgeRanks' => $contestant['judgeRanks'] ?? [],
                    'rank' => $contestant['rank'] ?? 0,
                ];
            })->values();

            $roundResults['round_'.$currentRound->id] = [
                'contestants' => $formattedRoundContestants,
                'top_n_proceed' => $currentRound->top_n_proceed,
            ];
        }

        return Inertia::render('Tabulator/Print', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'contestant_type' => $pageant->contestant_type,
                'date' => $pageant->pageant_date?->format('F j, Y'),
                'venue' => $pageant->venue,
                'location' => $pageant->location,
                'number_of_winners' => $pageant->getNumberOfWinners(),
                'ranking_method' => $pageant->ranking_method ?? 'score_average',
                'final_score_mode' => $pageant->final_score_mode ?? 'fresh',
                'final_score_inheritance' => $pageant->final_score_inheritance ?? [],
            ],
            'rounds' => $rounds,
            'roundTypes' => $uniqueRoundTypes,
            'roundResults' => $roundResults,
            'resultsByRoundType' => $resultsByRoundType,
            'resultsOverall' => $overallResults,
            'overallTally' => $overallTally,
            'finalTopN' => $finalTopN,
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
     * Mark a contestant as backed out
     */
    public function markBackedOut(Request $request, $pageantId, $contestantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $contestant = Contestant::where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        // Update contestant backed out status
        $contestant->update([
            'backed_out' => true,
            'backed_out_at' => now(),
            'backed_out_by' => $tabulator->id,
            'backed_out_reason' => $request->reason,
        ]);

        // Log the action
        AuditLog::create([
            'user_id' => $tabulator->id,
            'user_role' => $tabulator->role,
            'action_type' => 'CONTESTANT_BACKED_OUT',
            'target_entity' => 'Contestant',
            'target_id' => $contestant->id,
            'details' => json_encode([
                'contestant_name' => $contestant->name,
                'contestant_number' => $contestant->number,
                'pageant_id' => $pageantId,
                'pageant_name' => $pageant->name,
                'reason' => $request->reason,
                'backed_out_by' => $tabulator->name,
            ]),
            'ip_address' => $request->ip(),
        ]);

        // Broadcast real-time update to all listeners on the pageant channel
        event(new \App\Events\ContestantBackedOut(
            $contestant,
            $pageant,
            'backed_out',
            $request->reason,
            $tabulator->name
        ));

        // Notify all judges assigned to this pageant
        foreach ($pageant->judges as $judge) {
            event(new \App\Events\JudgeNotified(
                $judge->id,
                $pageantId,
                "Contestant #{$contestant->number} ({$contestant->name}) has been marked as backed out. Reason: {$request->reason}",
                'Contestant Backed Out',
                null,
                'contestant_backed_out'
            ));
        }

        return response()->json([
            'success' => true,
            'message' => "Contestant #{$contestant->number} has been marked as backed out.",
        ]);
    }

    /**
     * Undo backed out status for a contestant
     */
    public function undoBackedOut(Request $request, $pageantId, $contestantId)
    {
        $tabulator = Auth::user();
        $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);

        $contestant = Contestant::where('pageant_id', $pageantId)
            ->findOrFail($contestantId);

        if (! $contestant->backed_out) {
            return response()->json([
                'success' => false,
                'message' => 'This contestant is not marked as backed out.',
            ], 400);
        }

        $previousReason = $contestant->backed_out_reason;

        // Restore contestant status
        $contestant->update([
            'backed_out' => false,
            'backed_out_at' => null,
            'backed_out_by' => null,
            'backed_out_reason' => null,
        ]);

        // Log the action
        AuditLog::create([
            'user_id' => $tabulator->id,
            'user_role' => $tabulator->role,
            'action_type' => 'CONTESTANT_RESTORED',
            'target_entity' => 'Contestant',
            'target_id' => $contestant->id,
            'details' => json_encode([
                'contestant_name' => $contestant->name,
                'contestant_number' => $contestant->number,
                'pageant_id' => $pageantId,
                'pageant_name' => $pageant->name,
                'previous_reason' => $previousReason,
                'restored_by' => $tabulator->name,
            ]),
            'ip_address' => $request->ip(),
        ]);

        // Broadcast real-time update
        event(new \App\Events\ContestantBackedOut(
            $contestant,
            $pageant,
            'restored',
            null,
            $tabulator->name
        ));

        // Notify all judges assigned to this pageant
        foreach ($pageant->judges as $judge) {
            event(new \App\Events\JudgeNotified(
                $judge->id,
                $pageantId,
                "Contestant #{$contestant->number} ({$contestant->name}) has been restored and can now be scored.",
                'Contestant Restored',
                null,
                'contestant_restored'
            ));
        }

        return response()->json([
            'success' => true,
            'message' => "Contestant #{$contestant->number} has been restored.",
        ]);
    }

    /**
     * Calculate display scores (sum of all judge totals per round) for frontend display only
     * This does NOT affect ranking calculations - it's purely for visual display
     */
    private function calculateDisplayScores(Pageant $pageant): array
    {
        $displayScores = [];

        foreach ($pageant->contestants as $contestant) {
            $contestantScores = [];

            foreach ($pageant->rounds as $round) {
                // Get all scores for this contestant in this round
                $scores = Score::where('pageant_id', $pageant->id)
                    ->where('round_id', $round->id)
                    ->where('contestant_id', $contestant->id)
                    ->get();

                if ($scores->isEmpty()) {
                    continue;
                }

                // Group by judge and sum each judge's criteria scores
                $judgeScores = $scores->groupBy('judge_id');
                $totalForRound = 0;

                foreach ($judgeScores as $judgeId => $judgeScoreRecords) {
                    $judgeTotal = 0;
                    foreach ($judgeScoreRecords as $scoreRecord) {
                        $judgeTotal += $scoreRecord->score;
                    }
                    $totalForRound += $judgeTotal;
                }

                $contestantScores[$round->name] = round($totalForRound, 2);
            }

            $displayScores[$contestant->id] = $contestantScores;
        }

        return $displayScores;
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
