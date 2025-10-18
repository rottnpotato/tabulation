<?php

namespace App\Events;

use App\Models\Pageant;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PageantCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Pageant $pageant;

    public User $organizer;

    /**
     * Create a new event instance.
     */
    public function __construct(Pageant $pageant, User $organizer)
    {
        $this->pageant = $pageant;
        $this->organizer = $organizer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'pageant.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'pageant_id' => $this->pageant->id,
            'pageant_name' => $this->pageant->name,
            'organizer_id' => $this->organizer->id,
            'organizer_name' => $this->organizer->name,
            'pageant_date' => $this->pageant->pageant_date,
            'venue' => $this->pageant->venue,
            'location' => $this->pageant->location,
            'status' => $this->pageant->status,
            'created_at' => $this->pageant->created_at->toISOString(),
        ];
    }
}
