<?php

namespace App\Events;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class JudgeNotified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $judgeId;

    public $pageantId;

    public $message;

    public $title;

    public $roundName;

    public $action;

    /**
     * Create a new event instance.
     */
    public function __construct($judgeId, $pageantId, $message, $title = 'Notification', $roundName = null, $action = 'general')
    {
        $this->judgeId = $judgeId;
        $this->pageantId = $pageantId;
        $this->message = $message;
        $this->title = $title;
        $this->roundName = $roundName;
        $this->action = $action;

        // Log the notification to audit_logs
        $this->logNotification();
    }

    /**
     * Log the notification to audit logs
     */
    protected function logNotification(): void
    {
        $sender = Auth::user();
        $judge = User::find($this->judgeId);

        $details = [
            'judge_id' => $this->judgeId,
            'judge_name' => $judge?->name ?? 'Unknown',
            'judge_email' => $judge?->email ?? 'Unknown',
            'pageant_id' => $this->pageantId,
            'title' => $this->title,
            'message' => $this->message,
            'round_name' => $this->roundName,
            'action' => $this->action,
            'sent_at' => now()->toDateTimeString(),
        ];

        AuditLog::create([
            'user_id' => $sender?->id,
            'user_role' => $sender?->role ?? 'system',
            'action_type' => 'judge_notification_sent',
            'target_entity' => 'judge',
            'target_id' => $this->judgeId,
            'details' => json_encode($details),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("judge.{$this->judgeId}"),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'pageant_id' => $this->pageantId,
            'message' => $this->message,
            'title' => $this->title,
            'round_name' => $this->roundName,
            'action' => $this->action,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
