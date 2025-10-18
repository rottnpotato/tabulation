<?php

namespace App\Events;

use App\Models\Activity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Activity $activity
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pageant.'.$this->activity->pageant_id),
            new PrivateChannel('organizer.pageant.'.$this->activity->pageant_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $this->activity->load('user');

        return [
            'id' => $this->activity->id,
            'pageant_id' => $this->activity->pageant_id,
            'user_id' => $this->activity->user_id,
            'user_name' => $this->activity->user?->name ?? 'System',
            'user_role' => $this->activity->user?->role ?? 'system',
            'action_type' => $this->activity->action_type,
            'entity_type' => $this->activity->entity_type,
            'entity_id' => $this->activity->entity_id,
            'description' => $this->activity->description,
            'icon' => $this->activity->icon,
            'metadata' => $this->activity->metadata,
            'created_at' => $this->activity->created_at->toISOString(),
            'formatted_time' => $this->activity->created_at->diffForHumans(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'activity.created';
    }
}
