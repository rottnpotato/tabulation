<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Pageant;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function dashboard()
    {
        // Count pageants by status
        $pageantCounts = [
            'pending_approval' => Pageant::where('status', 'Pending_Approval')->count(),
            'draft' => Pageant::where('status', 'Draft')->count(),
            'setup' => Pageant::where('status', 'Setup')->count(),
            'active' => Pageant::where('status', 'Active')->count(),
            'completed' => Pageant::where('status', 'Completed')->count(),
            'unlocked_for_edit' => Pageant::where('status', 'Unlocked_For_Edit')->count(),
            'archived' => Pageant::where('status', 'Archived')->count(),
            'cancelled' => Pageant::where('status', 'Cancelled')->count(),
            'total' => Pageant::count(),
        ];

        // Count users by role
        $userCounts = [
            'organizers' => User::where('role', 'organizer')->count(),
            'tabulators' => User::where('role', 'tabulator')->count(),
            'judges' => User::where('role', 'judge')->count(),
            'total' => User::count(),
        ];

        // Get recent pageant activity
        $recentPageantActivity = AuditLog::where('target_entity', 'Pageant')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent user activity
        $recentUserActivity = AuditLog::where('target_entity', 'User')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent pageants
        $recentPageants = Pageant::with('organizers')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                    'end_date' => $pageant->end_date?->format('M d, Y'),
                    'location' => $pageant->location,
                    'organizers' => $pageant->organizers->map(function ($organizer) {
                        return [
                            'id' => $organizer->id,
                            'name' => $organizer->name,
                        ];
                    }),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'pageantCounts' => $pageantCounts,
            'userCounts' => $userCounts,
            'recentPageantActivity' => $recentPageantActivity,
            'recentUserActivity' => $recentUserActivity,
            'recentPageants' => $recentPageants,
        ]);
    }

    public function allPageants()
    {
        // Get all pageants with organizers and permission information
        $pageants = Pageant::with(['organizers', 'editorWithPermission'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pageant) {
                // Calculate progress using the pageant's built-in progress calculation
                $progress = $pageant->calculateProgress();

                // Events functionality has been removed
                $currentEvent = null;

                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'description' => $pageant->description,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                    'end_date' => $pageant->end_date?->format('M d, Y'),
                    'venue' => $pageant->venue,
                    'location' => $pageant->location,
                    'created_at' => $pageant->created_at->format('Y-m-d H:i:s'),
                    'organizers' => $pageant->organizers->map(function ($organizer) {
                        return [
                            'id' => $organizer->id,
                            'name' => $organizer->name,
                        ];
                    }),
                    'is_edit_permission_granted' => $pageant->is_edit_permission_granted,
                    'edit_permission_granted_to' => $pageant->editorWithPermission ? [
                        'id' => $pageant->editorWithPermission->id,
                        'name' => $pageant->editorWithPermission->name,
                    ] : null,
                    'edit_permission_expires_at' => $pageant->edit_permission_expires_at?->format('M d, Y H:i'),
                    'contestants_count' => $pageant->contestants()->count(),
                    'progress' => round($progress),
                    'currentRound' => $currentEvent ? $currentEvent->name : null,
                    'currentEvent' => $currentEvent ? [
                        'id' => $currentEvent->id,
                        'name' => $currentEvent->name,
                        'type' => $currentEvent->type,
                        'status' => $currentEvent->status,
                    ] : null,
                    'coverImage' => null, // No placeholder image
                ];
            });

        // Count pageants by status for filters
        $pageantCounts = [
            'draft' => Pageant::where('status', 'Draft')->count(),
            'setup' => Pageant::where('status', 'Setup')->count(),
            'active' => Pageant::where('status', 'Active')->count(),
            'completed' => Pageant::where('status', 'Completed')->count(),
            'unlocked_for_edit' => Pageant::where('status', 'Unlocked_For_Edit')->count(),
            'archived' => Pageant::where('status', 'Archived')->count(),
            'cancelled' => Pageant::where('status', 'Cancelled')->count(),
            'total' => Pageant::count(),
        ];

        // Get all organizers for filtering
        $organizers = User::where('role', 'organizer')->get();

        return Inertia::render('Admin/Pageants/Index', [
            'pageants' => $pageants,
            'pageantCounts' => $pageantCounts,
            'organizers' => $organizers,
        ]);
    }

    public function reports()
    {
        // Fetch pageant statistics
        $pageantsByMonth = DB::table('pageants')
            ->select(DB::raw('EXTRACT(MONTH FROM created_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereRaw('EXTRACT(YEAR FROM created_at) = ?', [date('Y')])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Create a complete months array (1-12) with counts
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $pageantMonthlyData = [];

        for ($i = 1; $i <= 12; $i++) {
            $pageantMonthlyData[] = $pageantsByMonth[$i] ?? 0;
        }

        // Calculate completion rate
        $totalPageants = Pageant::count();
        $completedPageants = Pageant::where('status', 'Completed')->orWhere('status', 'Archived')->count();
        $completionRate = $totalPageants > 0 ? round(($completedPageants / $totalPageants) * 100) : 0;

        // Get average pageant duration (for completed pageants with valid dates)
        $durationData = Pageant::whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where(function ($query) {
                $query->where('status', 'Completed')
                    ->orWhere('status', 'Archived');
            })
            ->select(DB::raw('AVG(EXTRACT(EPOCH FROM (end_date::timestamp - start_date::timestamp))/86400) as avg_duration'))
            ->first();

        $avgDuration = $durationData ? round($durationData->avg_duration, 1) : 0;

        // Get user statistics
        $usersByRole = User::select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        // Get system activity by day of week
        $activityByDay = DB::table('audit_logs')
            ->select(DB::raw('EXTRACT(DOW FROM created_at) as day'), DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('EXTRACT(DOW FROM created_at)'))
            ->get()
            ->pluck('count', 'day')
            ->toArray();

        // Create a complete day array (0-6, Sunday-Saturday) with counts
        $dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $activityData = [];

        for ($i = 0; $i <= 6; $i++) {
            $activityData[] = $activityByDay[$i] ?? 0;
        }

        // Get scoring distribution data
        $scoringDistribution = DB::table('pageants')
            ->select(DB::raw('COALESCE(scoring_system, \'percentage\') as scoring_system'), DB::raw('COUNT(*) as count'))
            ->groupBy('scoring_system')
            ->get()
            ->pluck('count', 'scoring_system')
            ->toArray();

        return Inertia::render('Admin/Reports', [
            'pageantStats' => [
                'monthlyCreation' => $pageantMonthlyData,
                'monthLabels' => $monthLabels,
                'completionRate' => $completionRate,
                'avgDuration' => $avgDuration,
            ],
            'userStats' => [
                'byRole' => $usersByRole,
            ],
            'systemStats' => [
                'activityByDay' => $activityData,
                'dayLabels' => $dayLabels,
                'scoringDistribution' => $scoringDistribution,
            ],
        ]);
    }

    // Pageant methods
    public function createPageant()
    {
        // Get only verified organizers for assigning to the pageant with their pageants
        $organizers = User::where('role', 'organizer')
            ->where('is_verified', true)
            ->with(['pageants' => function ($query) {
                $query->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->whereNotIn('status', ['Cancelled', 'Archived'])
                    ->select('pageants.id', 'pageants.name', 'pageants.start_date', 'pageants.end_date');
            }])
            ->get()
            ->map(function ($organizer) {
                return [
                    'id' => $organizer->id,
                    'name' => $organizer->name,
                    'email' => $organizer->email,
                    'pageants' => $organizer->pageants->map(function ($pageant) {
                        return [
                            'id' => $pageant->id,
                            'name' => $pageant->name,
                            'start_date' => $pageant->start_date->format('Y-m-d'),
                            'end_date' => $pageant->end_date->format('Y-m-d'),
                            'start_date_formatted' => $pageant->start_date->format('M d, Y'),
                            'end_date_formatted' => $pageant->end_date->format('M d, Y'),
                        ];
                    }),
                ];
            });

        return Inertia::render('Admin/Pageants/Create', [
            'organizers' => $organizers,
        ]);
    }

    public function storePageant(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'end_time' => 'nullable|date_format:H:i',
                'venue' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'status' => 'required|in:Draft,Setup',
                'organizer_ids' => 'array',
                'organizer_ids.*' => 'exists:users,id',
                'scoring_system' => 'required|string|in:percentage,1-10,1-5,points',
                'contestant_type' => 'required|string|in:solo,pairs,both',
            ]);

            // Check for organizer conflicts if dates are provided
            if (isset($validated['organizer_ids']) && $validated['start_date'] && $validated['end_date']) {
                $conflicts = [];
                foreach ($validated['organizer_ids'] as $organizerId) {
                    // First check if organizer is verified
                    $organizer = User::find($organizerId);
                    if ($organizer && ! $organizer->is_verified) {
                        return back()
                            ->withInput()
                            ->withErrors(['organizer_ids' => "Organizer '{$organizer->name}' has not verified their email yet and cannot be assigned to pageants."]);
                    }

                    $conflict = Pageant::getOrganizerConflict(
                        $organizerId,
                        $validated['start_date'],
                        $validated['end_date']
                    );
                    if ($conflict) {
                        $conflicts[] = "{$organizer->name} is already assigned to '{$conflict['pageant_name']}' ({$conflict['start_date']} - {$conflict['end_date']})";
                    }
                }

                if (! empty($conflicts)) {
                    return back()
                        ->withInput()
                        ->withErrors(['organizer_ids' => 'The following organizers have scheduling conflicts: '.implode(', ', $conflicts)]);
                }
            }

            // Create pageant
            $pageant = Pageant::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_date' => $validated['end_date'],
                'end_time' => $validated['end_time'] ?? null,
                'venue' => $validated['venue'],
                'location' => $validated['location'],
                'status' => $validated['status'],
                'created_by' => Auth::id(),
                'scoring_system' => $validated['scoring_system'],
                'contestant_type' => $validated['contestant_type'],
            ]);

            // Attach organizers - only one organizer per pageant
            if (isset($validated['organizer_ids'])) {
                // Take only the first organizer if multiple are provided
                $organizerId = is_array($validated['organizer_ids'])
                    ? reset($validated['organizer_ids'])
                    : $validated['organizer_ids'];
                $pageant->organizers()->attach($organizerId);
            }

            // Log the action
            $this->auditLogService->log(
                'PAGEANT_CREATED',
                'Pageant',
                $pageant->id,
                "Created pageant '{$pageant->name}'"
            );

            // Set success flash message and redirect
            return redirect()
                ->route('admin.pageants.index')
                ->with('success', "Pageant '{$pageant->name}' has been created successfully.");
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error creating pageant: '.$e->getMessage());

            // Return with error message
            return back()
                ->withInput()
                ->with('error', 'An error occurred while creating the pageant. Please try again.');
        }
    }

    public function ongoingPageants()
    {
        // Get pageants that are currently active or in progress
        $ongoingPageants = Pageant::with(['organizers', 'contestants'])
            ->whereIn('status', ['Active', 'Setup'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'description' => $pageant->description,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                    'end_date' => $pageant->end_date?->format('M d, Y'),
                    'pageant_date' => $pageant->pageant_date?->format('M d, Y'),
                    'venue' => $pageant->venue,
                    'location' => $pageant->location,
                    'progress' => $pageant->calculateProgress(),
                    'contestants_count' => $pageant->contestants()->count(),
                    'organizers' => $pageant->organizers->map(function ($organizer) {
                        return [
                            'id' => $organizer->id,
                            'name' => $organizer->name,
                        ];
                    }),
                    'coverImage' => $pageant->cover_image ?: '/images/placeholders/pageant-cover.jpg',
                    'created_at' => $pageant->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Admin/Pageants/Ongoing', [
            'pageants' => $ongoingPageants,
        ]);
    }

    public function ongoingPageantDetail($id)
    {
        $pageant = Pageant::with([
            'organizers',
            'judges',
            'contestants',
            'segments',
            'categories',
            'rounds',
            'activities' => function ($query) {
                $query->latest()->limit(10);
            },
        ])->findOrFail($id);

        // Get only verified organizers for reassignment
        $allOrganizers = User::where('role', 'organizer')
            ->where('is_verified', true)
            ->get();

        // Get pageant details data from the model
        $pageantData = $pageant->getPageantDetailsData();

        // Add organizers data for the edit form
        $pageantData['organizers'] = $pageant->organizers->map(function ($organizer) {
            return [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'email' => $organizer->email,
            ];
        });

        // Add rounds data
        $pageantData['rounds'] = $pageant->rounds->map(function ($round) {
            return [
                'id' => $round->id,
                'name' => $round->name,
                'type' => $round->type,
                'is_active' => $round->is_active ?? false,
                'order' => $round->order ?? 0,
                'status' => $round->status ?? 'pending',
            ];
        });

        return Inertia::render('Admin/Pageants/PageantDetails', [
            'pageant' => $pageantData,
            'contestants' => $pageantData['contestants'],
            'allOrganizers' => $allOrganizers,
        ]);
    }

    public function updatePageantStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Draft,Setup,Active,Completed,Unlocked_For_Edit,Archived,Cancelled',
            'reason' => 'nullable|string|max:500',
        ]);

        $pageant = Pageant::findOrFail($id);
        $oldStatus = $pageant->status;
        $reason = $validated['reason'] ?? null;

        // Prevent direct status changes from Pending_Approval
        if ($pageant->isPendingApproval()) {
            return back()->with('error', 'Pageants pending approval must be approved or rejected through the approval system.');
        }

        $pageant->update([
            'status' => $validated['status'],
            'archive_reason' => $pageant->status === 'Archived' ? null : ($validated['status'] === 'Archived' ? $reason : null),
            'archived_at' => $pageant->status === 'Archived' ? null : ($validated['status'] === 'Archived' ? now() : null),
        ]);

        // Log the action
        $logMessage = "Changed pageant '{$pageant->name}' status from '{$oldStatus}' to '{$pageant->status}'";
        if ($pageant->status === 'Archived' && $reason) {
            $logMessage .= ". Reason: {$reason}";
        }

        $this->auditLogService->log(
            'PAGEANT_STATUS_CHANGED',
            'Pageant',
            $pageant->id,
            $logMessage
        );

        return back()->with('success', "Pageant status has been changed from '{$oldStatus}' to '{$pageant->status}'.");
    }

    public function updatePageant(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'venue' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'organizer_ids' => 'array',
            'organizer_ids.*' => 'exists:users,id',
            'scoring_system' => 'nullable|string|in:percentage,1-10,1-5,points',
        ]);

        $pageant = Pageant::findOrFail($id);

        // Only allow editing of setup/draft pageants
        if (! $pageant->isDraft() && ! $pageant->isSetup()) {
            return back()->with('error', 'Only pageants in Draft or Setup status can be edited.');
        }

        // Validate that organizers are verified if provided
        if (isset($validated['organizer_ids'])) {
            foreach ($validated['organizer_ids'] as $organizerId) {
                $organizer = User::find($organizerId);
                if ($organizer && ! $organizer->is_verified) {
                    return back()
                        ->withInput()
                        ->withErrors(['organizer_ids' => "Organizer '{$organizer->name}' has not verified their email yet and cannot be assigned to pageants."]);
                }
            }
        }

        $pageant->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_date' => $validated['end_date'],
            'end_time' => $validated['end_time'] ?? null,
            'venue' => $validated['venue'],
            'location' => $validated['location'],
            'scoring_system' => $validated['scoring_system'] ?? $pageant->scoring_system,
        ]);

        // Update organizers if provided
        if (isset($validated['organizer_ids'])) {
            $pageant->organizers()->sync($validated['organizer_ids']);
        }

        // Log the action
        $this->auditLogService->log(
            'PAGEANT_UPDATED',
            'Pageant',
            $pageant->id,
            "Updated pageant '{$pageant->name}'"
        );

        return back()->with('success', "Pageant '{$pageant->name}' has been updated successfully.");
    }

    public function grantEditPermission(Request $request, $id)
    {
        $validated = $request->validate([
            'organizer_id' => 'required|exists:users,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $pageant = Pageant::findOrFail($id);
        $organizer = User::findOrFail($validated['organizer_id']);

        // Only completed pageants can have edit permissions granted
        if ($pageant->status !== 'Completed') {
            return back()->with('error', 'Only Completed pageants can have edit permissions granted.');
        }

        // Update pageant status and set edit permission
        $pageant->update([
            'status' => 'Unlocked_For_Edit',
            'edit_permission_granted_to' => $validated['organizer_id'],
            'edit_permission_granted_at' => now(),
            'edit_permission_expires_at' => $validated['expires_at'] ?? now()->addDays(7),
        ]);

        // Log the action
        $this->auditLogService->log(
            'PAGEANT_EDIT_PERMISSION_GRANTED',
            'Pageant',
            $pageant->id,
            "Granted edit permission for pageant '{$pageant->name}' to organizer '{$organizer->name}'"
        );

        return back()->with('success', "Edit permission granted to {$organizer->name} for pageant '{$pageant->name}'.");
    }

    public function revokeEditPermission($id)
    {
        $pageant = Pageant::findOrFail($id);

        // Only pageants with edit permissions can have them revoked
        if ($pageant->status !== 'Unlocked_For_Edit') {
            return back()->with('error', 'This pageant does not have edit permissions to revoke.');
        }

        // Get organizer name before clearing the permission
        $organizer = User::find($pageant->edit_permission_granted_to);
        $organizerName = $organizer ? $organizer->name : 'Unknown Organizer';

        // Update pageant status and clear edit permission
        $pageant->update([
            'status' => 'Completed',
            'edit_permission_granted_to' => null,
            'edit_permission_granted_at' => null,
            'edit_permission_expires_at' => null,
        ]);

        // Log the action
        $this->auditLogService->log(
            'PAGEANT_EDIT_PERMISSION_REVOKED',
            'Pageant',
            $pageant->id,
            "Revoked edit permission for pageant '{$pageant->name}' from organizer '{$organizerName}'"
        );

        return back()->with('success', "Edit permission revoked from {$organizerName} for pageant '{$pageant->name}'.");
    }

    public function previousPageants()
    {
        $pageants = Pageant::whereIn('status', ['Completed', 'Unlocked_For_Edit'])
            ->with('organizers', 'editorWithPermission')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                    'end_date' => $pageant->end_date?->format('M d, Y'),
                    'location' => $pageant->location,
                    'organizers' => $pageant->organizers->map(function ($organizer) {
                        return [
                            'id' => $organizer->id,
                            'name' => $organizer->name,
                        ];
                    }),
                    'is_edit_permission_granted' => $pageant->is_edit_permission_granted,
                    'edit_permission_granted_to' => $pageant->editorWithPermission ? [
                        'id' => $pageant->editorWithPermission->id,
                        'name' => $pageant->editorWithPermission->name,
                    ] : null,
                    'edit_permission_expires_at' => $pageant->edit_permission_expires_at?->format('M d, Y H:i'),
                ];
            });

        return Inertia::render('Admin/Pageants/Previous', [
            'pageants' => $pageants,
        ]);
    }

    public function previousPageantDetail($id)
    {
        $pageant = Pageant::with([
            'organizers',
            'judges',
            'contestants',
            'segments',
            'categories',
            'activities' => function ($query) {
                $query->latest()->limit(10);
            },
        ])->findOrFail($id);

        // Get only verified organizers for reassignment
        $allOrganizers = User::where('role', 'organizer')
            ->where('is_verified', true)
            ->get();

        // Get pageant details data from the model
        $pageantData = $pageant->getPageantDetailsData();

        // Add organizers data for the edit form
        $pageantData['organizers'] = $pageant->organizers->map(function ($organizer) {
            return [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'email' => $organizer->email,
            ];
        });

        return Inertia::render('Admin/Pageants/PreviousDetail', [
            'pageant' => $pageantData,
            'contestants' => $pageantData['contestants'],
            'allOrganizers' => $allOrganizers,
            'id' => $id,
        ]);
    }

    public function archivedPageants()
    {
        $pageants = Pageant::whereIn('status', ['Archived', 'Cancelled'])
            ->with('organizers')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                    'end_date' => $pageant->end_date?->format('M d, Y'),
                    'location' => $pageant->location,
                    'reason' => $pageant->archive_reason,
                    'archived_at' => $pageant->archived_at?->format('Y-m-d'),
                    'archive_note' => $pageant->description,
                    'category' => $pageant->categories->first()?->name ?? 'General',
                    'contestants' => $pageant->contestants()->count(),
                    'venue' => $pageant->venue,
                    'organizers' => $pageant->organizers->map(function ($organizer) {
                        return [
                            'id' => $organizer->id,
                            'name' => $organizer->name,
                        ];
                    }),
                ];
            });

        return Inertia::render('Admin/Pageants/Archived', [
            'pageants' => $pageants,
        ]);
    }

    public function archivedPageantDetail($id)
    {
        // Find the pageant by ID
        $pageant = Pageant::with(['organizers'])->findOrFail($id);

        // Map the pageant data to a format suitable for the ArchivedDetail component
        $pageantData = [
            'id' => $pageant->id,
            'title' => $pageant->name,
            'date' => $pageant->start_date?->format('Y-m-d'),
            'venue' => $pageant->venue ?: $pageant->location,
            'reason' => $pageant->archive_reason,
            'archived_at' => $pageant->archived_at?->format('Y-m-d'),
            'archive_note' => $pageant->description ?: 'No notes provided about the archival reason.',
            'category' => $pageant->categories->first()?->name ?? 'General',
            'budget' => 0, // Placeholder as budget is not in model
            'organizer' => $pageant->organizers->isNotEmpty() ? $pageant->organizers->first()->name : null,
            'contestants' => $pageant->contestants()->count(),
            'replacement_pageant' => null,
            'archived_by' => 'Admin',
        ];

        return Inertia::render('Admin/Pageants/ArchivedDetail', [
            'pageant' => $pageantData,
        ]);
    }

    public function auditLog(Request $request)
    {
        $query = AuditLog::with('user');

        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action_type') && $request->action_type) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->has('target_entity') && $request->target_entity) {
            $query->where('target_entity', $request->target_entity);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['created_at', 'user_id', 'action_type', 'target_entity', 'ip_address'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $query->orderBy($sortBy, $sortDirection);

        $logs = $query->paginate(15);

        // Get distinct action types for filter dropdown
        $actionTypes = AuditLog::distinct('action_type')->pluck('action_type');

        // Get distinct target entities for filter dropdown
        $targetEntities = AuditLog::distinct('target_entity')->pluck('target_entity');

        // Get users for filter dropdown
        $users = User::all();

        return Inertia::render('Admin/AuditLog', [
            'logs' => $logs,
            'filters' => [
                'user_id' => $request->user_id,
                'action_type' => $request->action_type,
                'target_entity' => $request->target_entity,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
            'actionTypes' => $actionTypes,
            'targetEntities' => $targetEntities,
            'users' => $users,
        ]);
    }

    /**
     * Show pending pageants awaiting approval
     */
    public function pendingApprovals()
    {
        $pendingPageants = Pageant::where('status', 'Pending_Approval')
            ->with(['creator', 'organizers'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Pageants/PendingApprovals', [
            'pendingPageants' => $pendingPageants,
        ]);
    }

    /**
     * Approve a pending pageant
     */
    public function approvePageant($id)
    {
        try {
            $pageant = Pageant::findOrFail($id);

            if (! $pageant->canBeApproved()) {
                return redirect()->route('admin.pageants.pending-approvals')
                    ->with('error', 'This pageant cannot be approved in its current status.');
            }

            $pageant->approve();

            // Log the action
            $this->auditLogService->log(
                'PAGEANT_APPROVED',
                'Pageant',
                $pageant->id,
                "Approved pageant '{$pageant->name}'"
            );

            return redirect()->route('admin.pageants.pending-approvals')
                ->with('success', "Pageant '{$pageant->name}' has been approved successfully!");

        } catch (\Exception $e) {
            Log::error('Error approving pageant: '.$e->getMessage());

            return redirect()->route('admin.pageants.pending-approvals')
                ->with('error', 'Failed to approve pageant. Please try again.');
        }
    }

    /**
     * Reject a pending pageant
     */
    public function rejectPageant($id)
    {
        try {
            $pageant = Pageant::findOrFail($id);

            if (! $pageant->canBeApproved()) {
                return redirect()->route('admin.pageants.pending-approvals')
                    ->with('error', 'This pageant cannot be rejected in its current status.');
            }

            $pageant->reject();

            // Log the action
            $this->auditLogService->log(
                'PAGEANT_REJECTED',
                'Pageant',
                $pageant->id,
                "Rejected pageant '{$pageant->name}'"
            );

            return redirect()->route('admin.pageants.pending-approvals')
                ->with('success', "Pageant '{$pageant->name}' has been rejected.");

        } catch (\Exception $e) {
            Log::error('Error rejecting pageant: '.$e->getMessage());

            return redirect()->route('admin.pageants.pending-approvals')
                ->with('error', 'Failed to reject pageant. Please try again.');
        }
    }

    /**
     * View all edit access requests
     */
    public function editAccessRequests()
    {
        $requests = DB::table('edit_access_requests')
            ->join('pageants', 'edit_access_requests.pageant_id', '=', 'pageants.id')
            ->join('users as organizers', 'edit_access_requests.organizer_id', '=', 'organizers.id')
            ->leftJoin('users as reviewers', 'edit_access_requests.reviewed_by', '=', 'reviewers.id')
            ->select(
                'edit_access_requests.*',
                'pageants.name as pageant_name',
                'pageants.start_date as pageant_start_date',
                'organizers.name as organizer_name',
                'organizers.email as organizer_email',
                'reviewers.name as reviewer_name'
            )
            ->orderByRaw("CASE 
                WHEN edit_access_requests.status = 'pending' THEN 1 
                WHEN edit_access_requests.status = 'approved' THEN 2 
                WHEN edit_access_requests.status = 'rejected' THEN 3 
                ELSE 4 
            END")
            ->orderBy('edit_access_requests.created_at', 'desc')
            ->get();

        return Inertia::render('Admin/EditAccessRequests', [
            'requests' => $requests,
        ]);
    }

    /**
     * Approve an edit access request
     */
    public function approveEditAccessRequest(Request $request, $requestId)
    {
        $admin = Auth::user();

        try {
            $editRequest = DB::table('edit_access_requests')
                ->where('id', $requestId)
                ->first();

            if (! $editRequest) {
                return redirect()->route('admin.pageants.edit-access-requests')
                    ->with('error', 'Edit access request not found.');
            }

            if ($editRequest->status !== 'pending') {
                return redirect()->route('admin.pageants.edit-access-requests')
                    ->with('error', 'This request has already been processed.');
            }

            $pageant = Pageant::findOrFail($editRequest->pageant_id);

            // Grant edit permission by setting a temporary unlock flag
            DB::table('pageants')
                ->where('id', $editRequest->pageant_id)
                ->update([
                    'is_temporarily_editable' => true,
                    'temporary_edit_granted_by' => $admin->id,
                    'temporary_edit_granted_at' => now(),
                    'updated_at' => now(),
                ]);

            // Update the request status
            DB::table('edit_access_requests')
                ->where('id', $requestId)
                ->update([
                    'status' => 'approved',
                    'reviewed_by' => $admin->id,
                    'admin_notes' => $request->input('notes'),
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);

            // Log the action
            $this->auditLogService->log(
                'EDIT_ACCESS_GRANTED',
                'Pageant',
                $pageant->id,
                "Admin {$admin->name} granted edit access for pageant '{$pageant->name}' to organizer ID {$editRequest->organizer_id}"
            );

            return redirect()->route('admin.pageants.edit-access-requests')
                ->with('success', 'Edit access has been granted for this pageant.');

        } catch (\Exception $e) {
            Log::error('Error approving edit access request: '.$e->getMessage());

            return redirect()->route('admin.pageants.edit-access-requests')
                ->with('error', 'Failed to approve edit access request. Please try again.');
        }
    }

    /**
     * Reject an edit access request
     */
    public function rejectEditAccessRequest(Request $request, $requestId)
    {
        $admin = Auth::user();

        try {
            $editRequest = DB::table('edit_access_requests')
                ->where('id', $requestId)
                ->first();

            if (! $editRequest) {
                return redirect()->route('admin.pageants.edit-access-requests')
                    ->with('error', 'Edit access request not found.');
            }

            if ($editRequest->status !== 'pending') {
                return redirect()->route('admin.pageants.edit-access-requests')
                    ->with('error', 'This request has already been processed.');
            }

            $pageant = Pageant::findOrFail($editRequest->pageant_id);

            // Update the request status
            DB::table('edit_access_requests')
                ->where('id', $requestId)
                ->update([
                    'status' => 'rejected',
                    'reviewed_by' => $admin->id,
                    'admin_notes' => $request->input('notes'),
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);

            // Log the action
            $this->auditLogService->log(
                'EDIT_ACCESS_REJECTED',
                'Pageant',
                $pageant->id,
                "Admin {$admin->name} rejected edit access request for pageant '{$pageant->name}' from organizer ID {$editRequest->organizer_id}"
            );

            return redirect()->route('admin.pageants.edit-access-requests')
                ->with('success', 'Edit access request has been rejected.');

        } catch (\Exception $e) {
            Log::error('Error rejecting edit access request: '.$e->getMessage());

            return redirect()->route('admin.pageants.edit-access-requests')
                ->with('error', 'Failed to reject edit access request. Please try again.');
        }
    }
}
