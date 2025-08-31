<?php

namespace App\Events;

use App\Models\Pageant;
use App\Models\Round;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Round $round,
        public Pageant $pageant,
        public string $action, // 'locked', 'unlocked', 'set_current'
        public ?string $message = null
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pageant.'.$this->pageant->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'round_id' => $this->round->id,
            'round_name' => $this->round->name,
            'pageant_id' => $this->pageant->id,
            'action' => $this->action,
            'is_locked' => $this->round->is_locked,
            'is_current' => $this->pageant->current_round_id === $this->round->id,
            'message' => $this->message,
            'timestamp' => now()->toISOString(),
        ];
    }
}
