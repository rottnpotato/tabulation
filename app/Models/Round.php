<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pageant_id',
        'name',
        'description',
        'type',
        'identifier',
        'weight',
        'display_order',
        'is_active',
        'scoring_config',
        'is_locked',
        'locked_at',
        'locked_by',
        'top_n_proceed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weight' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'scoring_config' => 'array',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'top_n_proceed' => 'integer',
    ];

    /**
     * Get the pageant that owns the round.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }

    /**
     * Get the user who locked this round.
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Get the criteria associated with this round.
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(Criteria::class);
    }

    /**
     * Scope to get only active rounds.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get rounds ordered by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Check if the round is locked for editing
     */
    public function isLocked(): bool
    {
        return $this->is_locked === true;
    }

    /**
     * Check if the round can be edited (not locked)
     */
    public function canBeEdited(): bool
    {
        return ! $this->isLocked();
    }

    /**
     * Lock the round for editing
     */
    public function lock($userId = null): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);
    }

    /**
     * Unlock the round for editing
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    /**
     * Generate a default identifier based on type if not provided
     */
    public function generateIdentifier(): string
    {
        $typePrefix = $this->type === 'final' ? 'F' : 'SF';
        $count = self::where('pageant_id', $this->pageant_id)
            ->where('type', $this->type)
            ->count();

        return $typePrefix.($count > 0 ? $count : '');
    }

    /**
     * Check if this round is a semi-final round
     */
    public function isSemiFinal(): bool
    {
        return $this->type === 'semi-final';
    }

    /**
     * Check if this round is a final round
     */
    public function isFinal(): bool
    {
        return $this->type === 'final';
    }

    /**
     * Check if this round is a custom elimination round
     */
    public function isCustomElimination(): bool
    {
        return $this->type === 'custom' && $this->top_n_proceed !== null;
    }

    /**
     * Get the round type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            'semi-final' => 'Semi-Final',
            'final' => 'Final',
            'custom' => $this->top_n_proceed ? "Top {$this->top_n_proceed}" : 'Custom',
            default => ucfirst($this->type)
        };
    }

    /**
     * Check if a judge has completed scoring all contestants for all criteria in this round
     */
    public function isJudgeScoringComplete(int $judgeId): bool
    {
        $criteriaCount = $this->criteria()->count();
        $contestantsCount = $this->pageant->contestants()->count();
        $expectedScores = $criteriaCount * $contestantsCount;

        if ($expectedScores === 0) {
            return false;
        }

        $submittedScores = Score::where('round_id', $this->id)
            ->where('judge_id', $judgeId)
            ->where('pageant_id', $this->pageant_id)
            ->count();

        return $submittedScores >= $expectedScores;
    }

    /**
     * Get scoring completion percentage for a judge in this round
     */
    public function getJudgeScoringProgress(int $judgeId): float
    {
        $criteriaCount = $this->criteria()->count();
        $contestantsCount = $this->pageant->contestants()->count();
        $expectedScores = $criteriaCount * $contestantsCount;

        if ($expectedScores === 0) {
            return 0;
        }

        $submittedScores = Score::where('round_id', $this->id)
            ->where('judge_id', $judgeId)
            ->where('pageant_id', $this->pageant_id)
            ->count();

        return round(($submittedScores / $expectedScores) * 100, 1);
    }
}
