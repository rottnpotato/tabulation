<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
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
        'weight',
        'max_score',
        'scoring_type',
        'display_order',
        'criteria',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'criteria' => 'array',
        'active' => 'boolean',
        'weight' => 'integer',
        'max_score' => 'decimal:2',
        'display_order' => 'integer',
    ];

    /**
     * Get the pageant that owns the category.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }

    /**
     * Get the formatted name with weight.
     */
    public function getNameWithWeightAttribute(): string
    {
        return "{$this->name} ({$this->weight}%)";
    }
}
