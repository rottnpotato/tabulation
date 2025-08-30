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
        'weight',
        'display_order',
        'is_active',
        'scoring_config',
        'is_locked',
        'locked_at',
        'locked_by',
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
}
