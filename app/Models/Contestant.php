<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'name',
        'number',
        'origin',
        'age',
        'photo',
        'bio',
        'scores',
        'metadata',
        'active',
        'rank',
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
    ];
    
    /**
     * Get the pageant that owns the contestant.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
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
