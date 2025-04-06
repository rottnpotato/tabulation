<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
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
        'start_datetime',
        'end_datetime',
        'venue',
        'location',
        'status',
        'metadata',
        'is_milestone',
        'display_order',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'metadata' => 'array',
        'is_milestone' => 'boolean',
        'display_order' => 'integer',
    ];
    
    /**
     * Get the pageant that owns the event.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }
    
    /**
     * Check if the event is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }
    
    /**
     * Check if the event is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }
    
    /**
     * Check if the event is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'In Progress';
    }
    
    /**
     * Check if the event is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }
    
    /**
     * Get the duration of the event in hours
     */
    public function getDurationHours()
    {
        if (!$this->start_datetime || !$this->end_datetime) {
            return null;
        }
        
        return $this->end_datetime->diffInHours($this->start_datetime);
    }
}
