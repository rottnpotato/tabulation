<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Segment extends Model
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
        'start_datetime',
        'end_datetime',
        'type',
        'weight',
        'max_score',
        'scoring_type',
        'status',
        'display_order',
        'rules',
        'scoring_criteria',
        'active',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'rules' => 'array',
        'scoring_criteria' => 'array',
        'active' => 'boolean',
        'weight' => 'integer',
        'max_score' => 'decimal:2',
        'display_order' => 'integer',
    ];
    
    /**
     * Get the pageant that owns the segment.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }
    
    /**
     * Check if the segment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }
    
    /**
     * Check if the segment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }
    
    /**
     * Check if the segment is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'In Progress';
    }
    
    /**
     * Check if the segment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }
}
