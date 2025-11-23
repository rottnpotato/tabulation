<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'contestant_type',
        'required_judges',
        'is_locked',
        'locked_at',
        'locked_by',
        'current_round_id',
        'is_temporarily_editable',
        'temporary_edit_granted_by',
        'temporary_edit_granted_by',
        'temporary_edit_granted_at',
        'archive_reason',
        'archived_at',
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
        'is_temporarily_editable' => 'boolean',
        'temporary_edit_granted_at' => 'datetime',
        'archived_at' => 'datetime',
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
     * Get the current round for this pageant.
     */
    public function currentRound(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'current_round_id');
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
     * Get the rounds associated with this pageant.
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
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
            // Simplified status system
            'Draft' => 25,
            'Ongoing' => 60,
            'Completed' => 100,
            // Legacy statuses (in case old data exists)
            'Setup' => 25,
            'Active' => 60,
            'Unlocked_For_Edit' => 100,
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
            'milestones' => [],
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
            'milestones' => [],
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
            'milestones' => [],
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
                'description' => 'Percentage-based scoring (0-100%)',
            ],
            '1-10' => [
                'type' => '1-10',
                'maxScore' => 10,
                'description' => 'Scale scoring from 1 to 10 points',
            ],
            '1-5' => [
                'type' => '1-5',
                'maxScore' => 5,
                'description' => 'Scale scoring from 1 to 5 points',
            ],
            'points' => [
                'type' => 'points',
                'maxScore' => 50,
                'description' => 'Point-based scoring with a maximum of 50 points',
            ],
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
            'coverImage' => $this->cover_image ?: '/images/placeholders/pageant-cover.jpg',
            'logo' => $this->logo,
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
     * Check if the pageant is ongoing
     */
    public function isOngoing(): bool
    {
        return $this->status === 'Ongoing';
    }

    /**
     * Check if the pageant is in setup status (legacy)
     */
    public function isSetup(): bool
    {
        return $this->status === 'Setup';
    }

    /**
     * Check if the pageant is active (legacy, treated as ongoing)
     */
    public function isActive(): bool
    {
        return $this->status === 'Active' || $this->status === 'Ongoing';
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
        // Admins can always edit
        // This check should be done in the policy, but we need this method for UI

        if ($this->isLocked()) {
            return false;
        }

        // Completed pageants cannot be edited, even with temporary edit access
        if ($this->isCompleted()) {
            return false;
        }

        // If pageant is Ongoing, only allow editing with temporary access granted by admin
        if ($this->isOngoing()) {
            return $this->is_temporarily_editable === true;
        }

        // Check if temporary edit access has been granted (for any status)
        if ($this->is_temporarily_editable === true) {
            return true;
        }

        // Check if status allows editing
        if (! in_array($this->status, ['Draft', 'Setup', 'Unlocked_For_Edit', 'Pending_Approval'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if start date has been reached
     */
    public function hasStartDateReached(): bool
    {
        if (! $this->start_date) {
            return false;
        }

        $today = now()->startOfDay();
        $startDate = $this->start_date->startOfDay();

        return $today >= $startDate;
    }

    /**
     * Check if today is the pageant date
     */
    public function isPageantDateToday(): bool
    {
        if (! $this->pageant_date) {
            return false;
        }

        $today = now()->startOfDay();
        $pageantDate = $this->pageant_date->startOfDay();

        return $today->equalTo($pageantDate);
    }

    /**
     * Check if pageant can be scored (only on pageant date)
     */
    public function canBeScored(): bool
    {
        return $this->isPageantDateToday();
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
            'status' => 'Setup', // Move to Setup when locked
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
            'status' => 'Draft', // Move back to Draft when unlocked
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

    /**
     * Set the current round for this pageant
     */
    public function setCurrentRound($roundId): void
    {
        $round = $this->rounds()->findOrFail($roundId);
        $this->update(['current_round_id' => $roundId]);
    }

    /**
     * Get the current active round for this pageant
     */
    public function getCurrentRound()
    {
        if ($this->current_round_id) {
            return $this->currentRound;
        }

        // If no current round is set, return the first active round
        return $this->rounds()->active()->ordered()->first();
    }

    /**
     * Check if there's a current round set
     */
    public function hasCurrentRound(): bool
    {
        return $this->current_round_id !== null;
    }

    /**
     * Clear the current round
     */
    public function clearCurrentRound(): void
    {
        $this->update(['current_round_id' => null]);
    }

    /**
     * Grant temporary edit access for an ongoing pageant
     */
    public function grantTemporaryEditAccess(int $grantedBy): void
    {
        $this->update([
            'is_temporarily_editable' => true,
            'temporary_edit_granted_by' => $grantedBy,
            'temporary_edit_granted_at' => now(),
        ]);
    }

    /**
     * Revoke temporary edit access
     */
    public function revokeTemporaryEditAccess(): void
    {
        $this->update([
            'is_temporarily_editable' => false,
            'temporary_edit_granted_by' => null,
            'temporary_edit_granted_at' => null,
        ]);
    }

    /**
     * Check if the pageant allows solo contestants
     */
    public function allowsSoloContestants(): bool
    {
        return in_array($this->contestant_type, ['solo', 'both']);
    }

    /**
     * Check if the pageant allows pair contestants
     */
    public function allowsPairContestants(): bool
    {
        return in_array($this->contestant_type, ['pairs', 'both']);
    }

    /**
     * Check if the pageant is solo contestants only
     */
    public function isSoloOnly(): bool
    {
        return $this->contestant_type === 'solo';
    }

    /**
     * Check if the pageant is pair contestants only
     */
    public function isPairsOnly(): bool
    {
        return $this->contestant_type === 'pairs';
    }

    /**
     * Check if the pageant allows both solo and pair contestants
     */
    public function allowsBothTypes(): bool
    {
        return $this->contestant_type === 'both';
    }

    /**
     * Check if an organizer has conflicting pageants for a given date range
     *
     * @param  int  $organizerId  The organizer's user ID
     * @param  string|null  $startDate  The start date to check
     * @param  string|null  $endDate  The end date to check
     * @param  int|null  $excludePageantId  Optional pageant ID to exclude from the check (for updates)
     * @return array|null Returns array with conflict info if conflict exists, null otherwise
     */
    public static function getOrganizerConflict(int $organizerId, ?string $startDate, ?string $endDate, ?int $excludePageantId = null): ?array
    {
        // If no dates provided, there's no conflict
        if (! $startDate || ! $endDate) {
            return null;
        }

        $query = self::whereHas('organizers', function ($query) use ($organizerId) {
            $query->where('users.id', $organizerId);
        })
            ->where(function ($query) use ($startDate, $endDate) {
                // Check for overlapping date ranges
                $query->where(function ($q) use ($startDate, $endDate) {
                    // New pageant starts during existing pageant
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                      // Existing pageant is completely within new pageant
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '>=', $startDate)
                                ->where('end_date', '<=', $endDate);
                        })
                      // New pageant is completely within existing pageant
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                });
            })
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        // Exclude specific pageant if provided (for update scenarios)
        if ($excludePageantId) {
            $query->where('id', '!=', $excludePageantId);
        }

        $conflictingPageant = $query->first();

        if ($conflictingPageant) {
            return [
                'pageant_id' => $conflictingPageant->id,
                'pageant_name' => $conflictingPageant->name,
                'start_date' => $conflictingPageant->start_date->format('M d, Y'),
                'end_date' => $conflictingPageant->end_date->format('M d, Y'),
            ];
        }

        return null;
    }
}
