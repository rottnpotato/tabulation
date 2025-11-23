<?php

namespace App\Mail;

use App\Models\Pageant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EditAccessRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $organizer;

    public $pageant;

    public $reason;

    public $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $organizer, Pageant $pageant, string $reason)
    {
        $this->organizer = $organizer;
        $this->pageant = $pageant;
        $this->reason = $reason;
        $this->actionUrl = route('admin.pageants.edit-access-requests');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Edit Access Request: '.$this->pageant->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.edit-access-requested',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
