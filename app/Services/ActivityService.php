<?php

namespace App\Services;

use App\Events\ActivityCreated;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityService
{
    /**
     * Log an activity for a pageant
     *
     * @param  int  $pageantId  The pageant ID
     * @param  string  $actionType  The type of action (e.g., "SCORE_SUBMITTED", "CONTESTANT_ADDED")
     * @param  string  $description  Human-readable description
     * @param  string|null  $entityType  The type of entity (e.g., "Score", "Contestant")
     * @param  int|null  $entityId  The ID of the entity
     * @param  int|null  $userId  The user who performed the action (defaults to authenticated user)
     * @param  array|null  $metadata  Additional metadata
     * @param  string|null  $icon  Icon name for UI display
     * @return Activity The created activity entry
     */
    public function log(
        int $pageantId,
        string $actionType,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?int $userId = null,
        ?array $metadata = null,
        ?string $icon = null
    ): Activity {
        $user = $userId ? \App\Models\User::find($userId) : Auth::user();

        $activity = Activity::create([
            'pageant_id' => $pageantId,
            'user_id' => $user?->id,
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'icon' => $icon,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
        ]);

        // Broadcast the activity to connected clients
        broadcast(new ActivityCreated($activity))->toOthers();

        return $activity;
    }

    /**
     * Get recent activities for a pageant
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentActivities(int $pageantId, int $limit = 10)
    {
        return Activity::where('pageant_id', $pageantId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_name' => $activity->user?->name ?? 'System',
                    'user_role' => $activity->user?->role ?? 'system',
                    'action_type' => $activity->action_type,
                    'description' => $activity->description,
                    'icon' => $activity->icon ?? $this->getDefaultIcon($activity->action_type),
                    'entity_type' => $activity->entity_type,
                    'entity_id' => $activity->entity_id,
                    'metadata' => $activity->metadata,
                    'created_at' => $activity->created_at->toISOString(),
                    'formatted_time' => $activity->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get recent activities for an organizer (across all their pageants)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrganizerActivities(int $organizerId, int $limit = 20)
    {
        // Get pageant IDs for this organizer
        $pageantIds = \Illuminate\Support\Facades\DB::table('pageant_organizers')
            ->where('user_id', $organizerId)
            ->pluck('pageant_id');

        return Activity::whereIn('pageant_id', $pageantIds)
            ->with(['user', 'pageant'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'pageant_id' => $activity->pageant_id,
                    'pageant_name' => $activity->pageant?->name ?? 'Unknown Pageant',
                    'user_name' => $activity->user?->name ?? 'System',
                    'user_role' => $activity->user?->role ?? 'system',
                    'action_type' => $activity->action_type,
                    'description' => $activity->description,
                    'icon' => $activity->icon ?? $this->getDefaultIcon($activity->action_type),
                    'entity_type' => $activity->entity_type,
                    'entity_id' => $activity->entity_id,
                    'metadata' => $activity->metadata,
                    'created_at' => $activity->created_at->toISOString(),
                    'formatted_time' => $activity->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get default icon based on action type
     */
    private function getDefaultIcon(string $actionType): string
    {
        $iconMap = [
            'SCORE_SUBMITTED' => 'star',
            'SCORE_UPDATED' => 'edit',
            'CONTESTANT_ADDED' => 'user-plus',
            'CONTESTANT_UPDATED' => 'user-check',
            'CONTESTANT_REMOVED' => 'user-minus',
            'JUDGE_ASSIGNED' => 'gavel',
            'JUDGE_REMOVED' => 'user-x',
            'TABULATOR_ASSIGNED' => 'calculator',
            'TABULATOR_REMOVED' => 'x-circle',
            'ROUND_STARTED' => 'play-circle',
            'ROUND_COMPLETED' => 'check-circle',
            'CRITERIA_CREATED' => 'list-checks',
            'CRITERIA_UPDATED' => 'edit-3',
            'PAGEANT_UPDATED' => 'crown',
            'STATUS_CHANGED' => 'refresh-cw',
        ];

        return $iconMap[$actionType] ?? 'activity';
    }
}
