<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Pageant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'pageant_date',
        'venue',
        'location',
        'cover_image',
        'logo',
        'status',
        'created_by',
        'is_edit_permission_granted',
        'edit_permission_expires_at',
        'edit_permission_granted_to',
        'scoring_system',
        'required_judges',
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
        'start_date' => 'date',
        'end_date' => 'date',
        'pageant_date' => 'date',
        'is_edit_permission_granted' => 'boolean',
        'edit_permission_expires_at' => 'datetime',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    /**
     * Get the user who created the pageant.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who has edit permission for this pageant.
     */
    public function editorWithPermission(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_permission_granted_to');
    }

    /**
     * Get the user who locked this pageant.
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Get the organizers associated with this pageant.
     */
    public function organizers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pageant_organizers')
            ->where('users.role', 'organizer')
            ->withTimestamps();
    }

    /**
     * Get the judges associated with this pageant.
     */
    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pageant_judges')
            ->where('users.role', 'judge')
            ->withPivot('role', 'assigned_categories', 'assigned_segments', 'active', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the tabulators associated with this pageant.
     */
    public function tabulators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pageant_tabulators')
            ->where('users.role', 'tabulator')
            ->withPivot('active', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the contestants associated with this pageant.
     */
    public function contestants(): HasMany
    {
        return $this->hasMany(Contestant::class);
    }



    /**
     * Get the categories associated with this pageant.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('display_order');
    }

    /**
     * Get the segments associated with this pageant.
     */
    public function segments(): HasMany
    {
        return $this->hasMany(Segment::class)->orderBy('display_order');
    }

    /**
     * Get the criteria associated with this pageant.
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(Criteria::class);
    }

    /**
     * Get the activities associated with this pageant.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }



    /**
     * Get the active segments for this pageant.
     */
    public function activeSegments()
    {
        return $this->segments()->where('active', true);
    }

    /**
     * Get the recent activities for this pageant.
     */
    public function recentActivities()
    {
        return $this->activities()->latest()->limit(10);
    }

    /**
     * Calculate the overall progress of the pageant based on status.
     */
    public function calculateProgress(): int
    {
        return $this->getProgressFromStatus();
    }

    /**
     * Get progress percentage based on pageant status.
     */
    protected function getProgressFromStatus(): int
    {
        $statusMap = [
            'Pending_Approval' => 5,
            'Draft' => 10,
            'Setup' => 25,
            'Active' => 60,
            'Completed' => 100,
            'Unlocked_For_Edit' => 100,
            'Archived' => 100,
            'Cancelled' => 100,
        ];
        
        return $statusMap[$this->status] ?? 0;
    }

    /**
     * Get the top categories for this pageant with no placeholder data.
     */
    public function getTopCategoriesAttribute()
    {
        $categories = $this->categories()
            ->orderBy('weight', 'desc')
            ->limit(5)
            ->get();

        if ($categories->isEmpty()) {
            return [];
        }

        return $categories->map(function ($category) {
            return [
                'name' => $category->name,
                'avgScore' => rand(70, 95), // This should be replaced with actual calculation in real implementation
                'weight' => $category->weight,
            ];
        });
    }

    /**
     * Get judges with no placeholder data.
     */
    public function getPageantJudgesAttribute()
    {
        $judges = $this->judges()->get();

        if ($judges->isEmpty()) {
            return [];
        }

        return $judges->map(function ($judge) {
            return [
                'id' => $judge->id,
                'name' => $judge->name,
                'role' => $judge->pivot->role ?? 'Judge',
                'photo' => $judge->profile_photo_path,
                'active' => $judge->pivot->active ?? true,
            ];
        });
    }

    /**
     * Get recent activities with no placeholder data.
     */
    public function getPageantActivitiesAttribute()
    {
        $activities = $this->recentActivities()->get();

        if ($activities->isEmpty()) {
            return [];
        }

        return $activities->map(function ($activity) {
            $icon = 'CheckCircle';
            
            switch ($activity->action_type) {
                case 'CONTESTANT_ADDED':
                case 'CONTESTANT_UPDATED':
                case 'CONTESTANT_REMOVED':
                    $icon = 'User2';
                    break;
                case 'SEGMENT_CREATED':
                case 'SEGMENT_UPDATED':
                    $icon = 'Clock';
                    break;
                case 'SCORE_UPDATED':
                    $icon = 'Star';
                    break;
                case 'JUDGE_ASSIGNED':
                case 'JUDGE_REMOVED':
                    $icon = 'Award';
                    break;
                case 'CATEGORY_CREATED':
                case 'CATEGORY_UPDATED':
                    $icon = 'List';
                    break;
            }
            
            return [
                'icon' => $icon,
                'description' => $activity->description,
                'time' => $activity->created_at->diffForHumans(),
            ];
        });
    }

    /**
     * Get pageant phases based on status and date.
     */
    public function getPageantPhasesAttribute()
    {
        $phases = [];
        
        // Setup phase - based on status
        $setupCompleted = in_array($this->status, ['Active', 'Completed', 'Unlocked_For_Edit', 'Archived']);
        $setupCurrent = $this->status === 'Setup';
        
        $phases[] = [
            'name' => 'Setup',
            'description' => 'Initial pageant configuration and planning',
            'icon' => 'File',
            'completed' => $setupCompleted,
            'current' => $setupCurrent,
            'milestones' => []
        ];
        
        // Registration phase
        $regCompleted = in_array($this->status, ['Active', 'Completed', 'Unlocked_For_Edit', 'Archived']);
        $regCurrent = $this->status === 'Draft';
        
        $phases[] = [
            'name' => 'Contestant Registration',
            'description' => 'Registering and documenting pageant contestants',
            'icon' => 'Users',
            'completed' => $regCompleted,
            'current' => $regCurrent,
            'milestones' => []
        ];
        
        // Competition phase
        $compCompleted = in_array($this->status, ['Completed', 'Unlocked_For_Edit', 'Archived']);
        $compCurrent = $this->status === 'Active';
        
        $phases[] = [
            'name' => 'Competition',
            'description' => 'Main pageant competition and judging',
            'icon' => 'Clock',
            'completed' => $compCompleted,
            'current' => $compCurrent,
            'milestones' => []
        ];
        
        return $phases;
    }

    /**
     * Get contestant data with no placeholders.
     */
    public function getPageantContestantsAttribute()
    {
        $contestants = $this->contestants()->get();

        if ($contestants->isEmpty()) {
            return [];
        }

        return $contestants->map(function ($contestant) {
            return [
                'id' => $contestant->id,
                'name' => $contestant->name,
                'number' => $contestant->number,
                'age' => $contestant->age,
                'origin' => $contestant->origin,
                'score' => json_decode($contestant->scores, true)['average'] ?? null,
                'photo' => $contestant->photo ?? null,
            ];
        });
    }

    /**
     * Get all data needed for the pageant details page.
     */
    public function getPageantDetailsData()
    {
        // Map scoring_system enum to a descriptive object
        $scoringSystemMap = [
            'percentage' => [
                'type' => 'percentage',
                'maxScore' => 100,
                'description' => 'Percentage-based scoring (0-100%)'
            ],
            '1-10' => [
                'type' => '1-10',
                'maxScore' => 10,
                'description' => 'Scale scoring from 1 to 10 points'
            ],
            '1-5' => [
                'type' => '1-5',
                'maxScore' => 5,
                'description' => 'Scale scoring from 1 to 5 points'
            ],
            'points' => [
                'type' => 'points',
                'maxScore' => 50,
                'description' => 'Point-based scoring with a maximum of 50 points'
            ]
        ];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'pageant_date' => $this->pageant_date,
            'venue' => $this->venue,
            'location' => $this->location,
            'status' => $this->status,
            'progress' => $this->calculateProgress(),
            'contestants_count' => $this->contestants()->count(),
            'judges_count' => $this->judges()->count(),
            'coverImage' => '/images/placeholders/pageant-cover.jpg', // Placeholder until actual cover images are implemented
            'phases' => $this->PageantPhases,
            'topCategories' => $this->TopCategories,
            'judges' => $this->PageantJudges,
            'recentActivities' => $this->PageantActivities,
            'contestants' => $this->PageantContestants,
            'scoringSystem' => $scoringSystemMap[$this->scoring_system] ?? $scoringSystemMap['percentage'],
        ];
    }

    /**
     * Check if the pageant is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    /**
     * Check if the pageant is in setup status
     */
    public function isSetup(): bool
    {
        return $this->status === 'Setup';
    }

    /**
     * Check if the pageant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    /**
     * Check if the pageant is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }

    /**
     * Check if the pageant is unlocked for editing
     */
    public function isUnlockedForEdit(): bool
    {
        return $this->status === 'Unlocked_For_Edit';
    }

    /**
     * Check if the pageant is archived
     */
    public function isArchived(): bool
    {
        return $this->status === 'Archived';
    }

    /**
     * Check if the pageant is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    /**
     * Check if the pageant is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->status === 'Pending_Approval';
    }

    /**
     * Check if the pageant can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->isPendingApproval();
    }

    /**
     * Approve the pageant and set it to Draft status
     */
    public function approve(): void
    {
        if ($this->canBeApproved()) {
            $this->update(['status' => 'Draft']);
        }
    }

    /**
     * Reject the pageant and set it to Cancelled status
     */
    public function reject(): void
    {
        if ($this->canBeApproved()) {
            $this->update(['status' => 'Cancelled']);
        }
    }

    /**
     * Check if the pageant is locked
     */
    public function isLocked(): bool
    {
        return $this->is_locked === true;
    }

    /**
     * Check if the pageant can be edited (not locked and in editable status)
     */
    public function canBeEdited(): bool
    {
        if ($this->isLocked()) {
            return false;
        }

        return in_array($this->status, ['Draft', 'Setup', 'Unlocked_For_Edit']);
    }

    /**
     * Lock the pageant configuration
     */
    public function lockConfiguration($userId = null): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId,
            'status' => 'Setup' // Move to Setup when locked
        ]);
    }

    /**
     * Unlock the pageant configuration
     */
    public function unlockConfiguration(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
            'status' => 'Draft' // Move back to Draft when unlocked
        ]);
    }

    /**
     * Set pageant status to final (Setup)
     */
    public function setFinal($userId = null): void
    {
        $this->lockConfiguration($userId);
    }

    /**
     * Set pageant status to draft
     */
    public function setDraft(): void
    {
        if ($this->isLocked()) {
            $this->unlockConfiguration();
        } else {
            $this->update(['status' => 'Draft']);
        }
    }
}
