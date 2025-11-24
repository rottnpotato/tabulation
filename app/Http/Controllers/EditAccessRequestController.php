<?php

namespace App\Http\Controllers;

use App\Mail\EditAccessRequested;
use App\Models\EditAccessRequest;
use App\Models\Pageant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class EditAccessRequestController extends Controller
{
    /**
     * Request edit access for a locked pageant.
     */
    public function store(Request $request, Pageant $pageant)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        // Check if user is authorized organizer for this pageant
        if (! $pageant->organizers()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'You are not authorized to request edit access for this pageant.');
        }

        // Check if there's already a pending request
        $existingRequest = EditAccessRequest::where('pageant_id', $pageant->id)
            ->where('organizer_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending edit access request for this pageant.');
        }

        // Create the request
        $editAccessRequest = EditAccessRequest::create([
            'pageant_id' => $pageant->id,
            'organizer_id' => $request->user()->id,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        // Send email notification to all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new EditAccessRequested(
                $request->user(),
                $pageant,
                $validated['reason']
            ));
        }

        return back()->with('success', 'Edit access request submitted successfully. An admin will review your request.');
    }

    /**
     * Show all edit access requests (admin only).
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $requests = EditAccessRequest::with(['pageant', 'organizer', 'reviewer'])
            ->orderByRaw("
                CASE 
                    WHEN status = 'pending' THEN 1 
                    WHEN status = 'approved' THEN 2 
                    WHEN status = 'rejected' THEN 3 
                    ELSE 4 
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/EditAccessRequests', [
            'requests' => $requests,
        ]);
    }

    /**
     * Approve an edit access request.
     */
    public function approve(Request $request, EditAccessRequest $editAccessRequest)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if (! $editAccessRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $editAccessRequest->approve(
            $request->user()->id,
            $validated['admin_notes'] ?? null
        );

        return back()->with('success', 'Edit access request approved. The organizer can now edit the pageant.');
    }

    /**
     * Reject an edit access request.
     */
    public function reject(Request $request, EditAccessRequest $editAccessRequest)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if (! $editAccessRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|min:10|max:500',
        ]);

        $editAccessRequest->reject(
            $request->user()->id,
            $validated['admin_notes']
        );

        return back()->with('success', 'Edit access request rejected.');
    }

    /**
     * Revoke temporary edit access for a pageant.
     */
    public function revoke(Request $request, Pageant $pageant)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $pageant->revokeTemporaryEditAccess();

        return back()->with('success', 'Temporary edit access revoked for this pageant.');
    }
}
