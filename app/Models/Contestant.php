<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contestant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pageant_id',
        'user_id',
        'name',
        'number',
        'gender',
        'origin',
        'age',
        'photo',
        'bio',
        'scores',
        'metadata',
        'active',
        'rank',
        'is_pair',
        'pair_id',
        'backed_out',
        'backed_out_at',
        'backed_out_by',
        'backed_out_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scores' => 'array',
        'metadata' => 'array',
        'active' => 'boolean',
        'age' => 'integer',
        'number' => 'integer',
        'rank' => 'integer',
        'is_pair' => 'boolean',
        'backed_out' => 'boolean',
        'backed_out_at' => 'datetime',
    ];

    /**
     * Members of this pair (if this contestant represents a pair)
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            Contestant::class,
            'contestant_members',
            'pair_contestant_id',
            'member_contestant_id'
        )->withTimestamps();
    }

    /**
     * Pairs that this contestant is a member of.
     */
    public function pairs(): BelongsToMany
    {
        return $this->belongsToMany(
            Contestant::class,
            'contestant_members',
            'member_contestant_id',
            'pair_contestant_id'
        )->withTimestamps();
    }

    /**
     * Get the paired contestant (partner) if this contestant is part of a pair.
     */
    public function pairPartner()
    {
        if (! $this->pair_id) {
            return null;
        }

        return static::where('pair_id', $this->pair_id)
            ->where('id', '!=', $this->id)
            ->first();
    }

    /**
     * Check if this contestant is part of a pair.
     */
    public function isPaired(): bool
    {
        return ! empty($this->pair_id);
    }

    /**
     * Accessor for a nicely formatted display name.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_pair) {
            $names = $this->members->pluck('name')->filter()->values();
            if ($names->count() === 2) {
                return $names[0].' & '.$names[1];
            }
        }

        return (string) $this->name;
    }

    /**
     * Get the pageant that owns the contestant.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }

    /**
     * Get the user associated with this contestant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who marked this contestant as backed out.
     */
    public function backedOutByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'backed_out_by');
    }

    /**
     * Get the images associated with this contestant.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ContestantImage::class)->orderBy('display_order');
    }

    /**
     * Get the primary image for this contestant.
     */
    public function primaryImage()
    {
        return $this->hasMany(ContestantImage::class)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Calculate the contestant's average score across all categories.
     */
    public function calculateAverageScore()
    {
        if (empty($this->scores)) {
            return null;
        }

        $scores = collect($this->scores);

        if ($scores->isEmpty()) {
            return null;
        }

        return $scores->avg();
    }
}
