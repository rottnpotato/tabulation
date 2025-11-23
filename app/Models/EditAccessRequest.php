<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditAccessRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pageant_id',
        'organizer_id',
        'reason',
        'status',
        'reviewed_by',
        'admin_notes',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the pageant that this request is for.
     */
    public function pageant(): BelongsTo
    {
        return $this->belongsTo(Pageant::class);
    }

    /**
     * Get the organizer who requested access.
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Get the admin who reviewed the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the request.
     */
    public function approve(int $reviewerId, ?string $adminNotes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'admin_notes' => $adminNotes,
            'reviewed_at' => now(),
        ]);

        // Grant temporary edit access to the pageant
        $this->pageant->update([
            'is_temporarily_editable' => true,
            'temporary_edit_granted_by' => $reviewerId,
            'temporary_edit_granted_at' => now(),
        ]);
    }

    /**
     * Reject the request.
     */
    public function reject(int $reviewerId, ?string $adminNotes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'admin_notes' => $adminNotes,
            'reviewed_at' => now(),
        ]);
    }
}
