<?php

namespace App\Models;

use App\Services\ScoreCalculationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

class Score extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pageant_id',
        'round_id',
        'criteria_id',
        'contestant_id',
        'judge_id',
        'score',
        'notes',
        'submitted_at',
    ];

    /**
     * Boot the model and add event listeners for cache invalidation
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($score) {
            $scoreService = App::make(ScoreCalculationService::class);
            $scoreService->invalidateContestantCache($score->pageant_id, $score->contestant_id);
        });

        static::deleted(function ($score) {
            $scoreService = App::make(ScoreCalculationService::class);
            $scoreService->invalidateContestantCache($score->pageant_id, $score->contestant_id);
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the pageant this score belongs to.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }

    /**
     * Get the round this score belongs to.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Get the criteria this score belongs to.
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class);
    }

    /**
     * Get the contestant this score is for.
     */
    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class);
    }

    /**
     * Get the judge who submitted this score.
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    /**
     * Get scores for a specific judge and pageant.
     */
    public static function getJudgeScores($judgeId, $pageantId, $roundId = null)
    {
        $query = static::where('judge_id', $judgeId)
            ->where('pageant_id', $pageantId);

        if ($roundId) {
            $query->where('round_id', $roundId);
        }

        return $query->with(['criteria', 'contestant', 'round'])->get();
    }

    /**
     * Check if a judge has scored a specific contestant for a criteria in a round.
     */
    public static function hasJudgeScored($judgeId, $pageantId, $roundId, $criteriaId, $contestantId)
    {
        return static::where([
            'judge_id' => $judgeId,
            'pageant_id' => $pageantId,
            'round_id' => $roundId,
            'criteria_id' => $criteriaId,
            'contestant_id' => $contestantId,
        ])->exists();
    }

    /**
     * Get average score for a contestant in a specific round.
     */
    public static function getContestantAverageScore($contestantId, $roundId)
    {
        return static::where('contestant_id', $contestantId)
            ->where('round_id', $roundId)
            ->avg('score');
    }

    /**
     * Get all scores for a specific round grouped by contestant.
     */
    public static function getRoundScoresByContestant($roundId)
    {
        return static::where('round_id', $roundId)
            ->with(['contestant', 'judge', 'criteria'])
            ->get()
            ->groupBy('contestant_id');
    }
}
