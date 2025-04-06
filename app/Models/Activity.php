<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
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
        'action_type',
        'entity_type',
        'entity_id',
        'description',
        'icon',
        'metadata',
        'ip_address',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];
    
    /**
     * Get the pageant that owns the activity.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }
    
    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get a humanized version of the action type.
     */
    public function getHumanizedActionAttribute(): string
    {
        $actionMap = [
            'CONTESTANT_ADDED' => 'Added contestant',
            'CONTESTANT_UPDATED' => 'Updated contestant',
            'CONTESTANT_REMOVED' => 'Removed contestant',
            'EVENT_CREATED' => 'Created event',
            'EVENT_UPDATED' => 'Updated event',
            'EVENT_COMPLETED' => 'Completed event',
            'SEGMENT_CREATED' => 'Created segment',
            'SEGMENT_UPDATED' => 'Updated segment',
            'SCORE_UPDATED' => 'Updated scores',
            'JUDGE_ASSIGNED' => 'Assigned judge',
            'JUDGE_REMOVED' => 'Removed judge',
            'CATEGORY_CREATED' => 'Created category',
            'CATEGORY_UPDATED' => 'Updated category',
        ];
        
        return $actionMap[$this->action_type] ?? $this->action_type;
    }
    
    /**
     * Get the formatted timestamp for display.
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
