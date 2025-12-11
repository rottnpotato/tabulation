<?php

namespace App\Events;

use App\Models\Contestant;
use App\Models\Pageant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContestantBackedOut implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Contestant $contestant;

    public Pageant $pageant;

    public string $action; // 'backed_out' or 'restored'

    public ?string $reason;

    public ?string $backedOutByName;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Contestant $contestant,
        Pageant $pageant,
        string $action,
        ?string $reason = null,
        ?string $backedOutByName = null
    ) {
        $this->contestant = $contestant;
        $this->pageant = $pageant;
        $this->action = $action;
        $this->reason = $reason;
        $this->backedOutByName = $backedOutByName;
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
            'contestant_id' => $this->contestant->id,
            'contestant_name' => $this->contestant->name,
            'contestant_number' => $this->contestant->number,
            'pageant_id' => $this->pageant->id,
            'action' => $this->action,
            'backed_out' => $this->contestant->backed_out,
            'reason' => $this->reason,
            'backed_out_by' => $this->backedOutByName,
            'timestamp' => now()->toISOString(),
        ];
    }
}
