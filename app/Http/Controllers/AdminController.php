<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Pageant;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
                // Calculate progress based on real data, not placeholders
                $eventsCount = $pageant->events()->count();
                $eventsCompletedCount = $pageant->events()->where('status', 'Completed')->count();
                $progress = $eventsCount > 0 ? ($eventsCompletedCount / $eventsCount) * 100 : 0;
                
                // No longer use status-based progress if no events exist
                // Remove dummy placeholder values
                
                // Get current phase or event from real data only
                $currentEvent = null;
                
                if ($pageant->status === 'Active') {
                    $currentEvent = $pageant->events()
                        ->where('status', 'In Progress')
                        ->orderBy('start_datetime', 'desc')
                        ->first();
                        
                    if (!$currentEvent) {
                        $currentEvent = $pageant->events()
                            ->where('status', 'Pending')
                            ->where('start_datetime', '>=', now())
                            ->orderBy('start_datetime', 'asc')
                            ->first();
                    }
                }
                
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
            ->where(function($query) {
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
        // Get all organizers for assigning to the pageant
        $organizers = User::where('role', 'organizer')->get()->map(function ($organizer) {
            return [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'email' => $organizer->email,
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
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'venue' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'status' => 'required|in:Draft,Setup',
                'organizer_ids' => 'array',
                'organizer_ids.*' => 'exists:users,id',
                'scoring_system' => 'required|string|in:percentage,1-10,1-5,points',
            ]);
            
            // Create pageant
            $pageant = Pageant::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'venue' => $validated['venue'],
                'location' => $validated['location'],
                'status' => $validated['status'],
                'created_by' => Auth::id(),
                'scoring_system' => $validated['scoring_system'],
            ]);
            
            // Attach organizers
            if (isset($validated['organizer_ids'])) {
                $pageant->organizers()->attach($validated['organizer_ids']);
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
            \Illuminate\Support\Facades\Log::error('Error creating pageant: ' . $e->getMessage());
            
            // Return with error message
            return back()
                ->withInput()
                ->with('error', 'An error occurred while creating the pageant. Please try again.');
        }
    }
    
    public function ongoingPageants()
    {
        return redirect()->route('admin.pageants.index');
    }
    
    public function ongoingPageantDetail($id)
    {
        $pageant = Pageant::with([
            'organizers', 
            'judges',
            'contestants',
            'events',
            'segments',
            'categories',
            'activities' => function ($query) {
                $query->latest()->limit(10);
            }
        ])->findOrFail($id);
        
        // Get all organizers for reassignment
        $allOrganizers = User::where('role', 'organizer')->get();
        
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
        ]);
        
        $pageant = Pageant::findOrFail($id);
        $oldStatus = $pageant->status;
        
        $pageant->update([
            'status' => $validated['status'],
        ]);
        
        // Log the action
        $this->auditLogService->log(
            'PAGEANT_STATUS_CHANGED',
            'Pageant',
            $pageant->id,
            "Changed pageant '{$pageant->name}' status from '{$oldStatus}' to '{$pageant->status}'"
        );
        
        return back()->with('success', "Pageant status has been changed from '{$oldStatus}' to '{$pageant->status}'.");
    }
    
    public function updatePageant(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'venue' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'organizer_ids' => 'array',
            'organizer_ids.*' => 'exists:users,id',
            'scoring_system' => 'nullable|string|in:percentage,1-10,1-5,points',
        ]);
        
        $pageant = Pageant::findOrFail($id);
        
        // Only allow editing of setup/draft pageants
        if (!$pageant->isDraft() && !$pageant->isSetup()) {
            return back()->with('error', 'Only pageants in Draft or Setup status can be edited.');
        }
        
        $pageant->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
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
            'events',
            'segments',
            'categories',
            'activities' => function ($query) {
                $query->latest()->limit(10);
            }
        ])->findOrFail($id);
        
        // Get all organizers for reassignment
        $allOrganizers = User::where('role', 'organizer')->get();
        
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
            'id' => $id
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
            'reason' => 'Cancelled', // This would be a real field in the database
            'archived_at' => $pageant->updated_at->format('Y-m-d'),
            'archive_note' => $pageant->description ?: 'No notes provided about the archival reason.',
            'category' => $pageant->category ?? 'General',
            'budget' => $pageant->budget ?? 0,
            'organizer' => $pageant->organizers->isNotEmpty() ? $pageant->organizers->first()->name : null,
            'contestants' => $pageant->contestants_count ?? 0,
            'replacement_pageant' => null, // This would be a real field in the database
            'archived_by' => 'Admin' // This would be a real field linking to the user who archived
        ];
        
        return Inertia::render('Admin/Pageants/ArchivedDetail', [
            'pageant' => $pageantData
        ]);
    }
    
    public function auditLog(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');
        
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
            ],
            'actionTypes' => $actionTypes,
            'targetEntities' => $targetEntities,
            'users' => $users,
        ]);
    }
} 