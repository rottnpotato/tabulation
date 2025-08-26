<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Criteria extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criteria';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pageant_id',
        'round_id',
        'segment_id',
        'category_id',
        'name',
        'description',
        'weight',
        'min_score',
        'max_score',
        'allow_decimals',
        'decimal_places',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weight' => 'integer',
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'allow_decimals' => 'boolean',
        'decimal_places' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the pageant that owns the criteria.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }

    /**
     * Get the segment that owns the criteria.
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    /**
     * Get the category that owns the criteria.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the round that owns the criteria.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }
} 