<?php

namespace App\Events;

use App\Models\Score;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Score $score
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
            new PrivateChannel('pageant.'.$this->score->pageant_id),
            new PrivateChannel('pageant.'.$this->score->pageant_id.'.round.'.$this->score->round_id),
            new PrivateChannel('judge.'.$this->score->judge_id),
            new PrivateChannel('tabulator.pageant.'.$this->score->pageant_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $this->score->load(['contestant', 'criteria', 'judge']);

        return [
            'score_id' => $this->score->id,
            'score' => $this->score->score,
            'contestant_id' => $this->score->contestant_id,
            'contestant_name' => $this->score->contestant->name,
            'contestant_number' => $this->score->contestant->number,
            'criteria_id' => $this->score->criteria_id,
            'criteria_name' => $this->score->criteria->name,
            'round_id' => $this->score->round_id,
            'judge_id' => $this->score->judge_id,
            'judge_name' => $this->score->judge->name,
            'pageant_id' => $this->score->pageant_id,
            'submitted_at' => $this->score->submitted_at?->toISOString(),
            'notes' => $this->score->notes,
        ];
    }
}
