<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pageant;
use App\Models\Event;
use App\Events\EventUpdated;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrganizerController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Check if a username already exists
     */
    public function checkUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string'
        ]);

        $exists = User::where('username', $request->username)->exists();

        return response()->json([
            'usernameExists' => $exists
        ]);
    }

    /**
     * Create a new organizer user with pending verification
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|min:3|max:30|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Generate verification token
        $verificationToken = Str::random(64);
        $expiresAt = now()->addHours(48);

        // Create the user with pending verification
        $organizer = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'organizer',
            'is_verified' => false,
            'verification_token' => $verificationToken,
            'verification_expires_at' => $expiresAt,
        ]);

        // Send verification email
        $this->sendVerificationEmail($organizer, $verificationToken);

        // Log the action
        $this->auditLogService->log(
            'ORGANIZER_CREATED',
            'User',
            $organizer->id,
            "Created organizer account for '{$organizer->name}' ({$organizer->email})"
        );

        return response()->json([
            'success' => true,
            'message' => 'Organizer created successfully! A verification email has been sent.',
            'organizer' => [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'email' => $organizer->email,
            ]
        ]);
    }

    /**
     * Handle email verification
     */
    public function verify($token)
    {
        // Find user with this token
        $organizer = User::where('verification_token', $token)
            ->where('is_verified', false)
            ->where('verification_expires_at', '>', now())
            ->first();

        if (!$organizer) {
            return Inertia::render('VerificationFailed', [
                'message' => 'Verification link is invalid or has expired.',
            ]);
        }

        // Update user as verified
        $organizer->update([
            'is_verified' => true,
            'verification_token' => null,
            'verification_expires_at' => null,
            'email_verified_at' => now(),
        ]);

        // Log the action
        $this->auditLogService->log(
            'ORGANIZER_VERIFIED',
            'User',
            $organizer->id,
            "Verified organizer account for '{$organizer->name}' ({$organizer->email})"
        );

        // Show verification success page with link to set new password
        return Inertia::render('VerificationSuccess', [
            'user' => [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'email' => $organizer->email,
            ],
            'resetPasswordUrl' => route('password.reset', ['token' => $this->generatePasswordResetToken($organizer)]),
        ]);
    }

    /**
     * Generate a password reset token for the user
     */
    private function generatePasswordResetToken(User $user)
    {
        $token = Str::random(64);
        
        // Store the token in the password reset table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
        
        return $token;
    }

    /**
     * Send verification email to the organizer
     */
    private function sendVerificationEmail(User $organizer, $token)
    {
        $verificationUrl = url("/verify-organizer/{$token}");
        
        $mailData = [
            'name' => $organizer->name,
            'verificationUrl' => $verificationUrl,
            'expiresAt' => $organizer->verification_expires_at->format('M d, Y H:i:s'),
        ];
        
        Mail::send('emails.organizer-verification', $mailData, function($message) use ($organizer) {
            $message->to($organizer->email, $organizer->name)
                    ->subject('Verify Your Organizer Account');
        });
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request, $id)
    {
        $organizer = User::findOrFail($id);
        
        // Only allow resending if not verified and is an organizer
        if ($organizer->is_verified || $organizer->role !== 'organizer') {
            return back()->with('error', 'Cannot resend verification for this account.');
        }
        
        // Generate new token
        $verificationToken = Str::random(64);
        $expiresAt = now()->addHours(48);
        
        $organizer->update([
            'verification_token' => $verificationToken,
            'verification_expires_at' => $expiresAt,
        ]);
        
        // Send verification email
        $this->sendVerificationEmail($organizer, $verificationToken);
        
        // Log the action
        $this->auditLogService->log(
            'VERIFICATION_EMAIL_RESENT',
            'User',
            $organizer->id,
            "Resent verification email to organizer '{$organizer->name}' ({$organizer->email})"
        );
        
        return back()->with('success', 'Verification email has been resent.');
    }

    public function dashboard()
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Get pageant IDs assigned to this organizer
        $pageantIds = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->pluck('pageant_id');
        
        // Get counts by status
        $pageantsByStatus = [
            'active' => Pageant::whereIn('id', $pageantIds)->where('status', 'Active')->count(),
            'draft' => Pageant::whereIn('id', $pageantIds)->where('status', 'Draft')->count(),
            'setup' => Pageant::whereIn('id', $pageantIds)->where('status', 'Setup')->count(),
            'completed' => Pageant::whereIn('id', $pageantIds)->where('status', 'Completed')->count(),
            'unlocked_for_edit' => Pageant::whereIn('id', $pageantIds)->where('status', 'Unlocked_For_Edit')->count(),
        ];
        
        // Get recent pageants
        $recentPageants = Pageant::whereIn('id', $pageantIds)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                    'end_date' => $pageant->end_date?->format('M d, Y'),
                    'venue' => $pageant->venue,
                    'location' => $pageant->location,
                ];
            });
            
        // Get upcoming events
        $upcomingEvents = Event::whereIn('pageant_id', $pageantIds)
            ->where('status', '!=', 'Completed')
            ->where('status', '!=', 'Cancelled')
            ->where('start_datetime', '>', now())
            ->orderBy('start_datetime')
            ->limit(5)
            ->get()
            ->map(function ($event) {
                // Get the associated pageant name
                $pageantName = $event->pageant ? $event->pageant->name : 'Unknown Pageant';
                
                // Format dates to a consistent string format
                $startDateTime = $event->start_datetime ? $event->start_datetime->format('Y-m-d H:i:s') : null;
                $endDateTime = $event->end_datetime ? $event->end_datetime->format('Y-m-d H:i:s') : null;
                
                // Format for display
                $startDateTimeFormatted = $startDateTime ? date('M d, Y h:i A', strtotime($startDateTime)) : null;
                $endDateTimeFormatted = $endDateTime ? date('M d, Y h:i A', strtotime($endDateTime)) : null;
                
                return [
                    'id' => $event->id,
                    'pageant_id' => $event->pageant_id,
                    'pageant_name' => $pageantName,
                    'name' => $event->name,
                    'description' => $event->description,
                    'type' => $event->type ?? 'General',
                    'start_datetime' => $startDateTimeFormatted,
                    'end_datetime' => $endDateTimeFormatted,
                    'raw_start_datetime' => $startDateTime,
                    'raw_end_datetime' => $endDateTime,
                    'venue' => $event->venue,
                    'location' => $event->location,
                    'status' => $event->status ?? 'Pending',
                    'is_milestone' => $event->is_milestone ?? false,
                ];
            });

        return Inertia::render('Organizer/Dashboard', [
            'pageantCounts' => $pageantsByStatus,
            'recentPageants' => $recentPageants,
            'totalPageants' => count($pageantIds),
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function criteria()
    {
        return Inertia::render('Organizer/Criteria');
    }

    public function contestants()
    {
        return Inertia::render('Organizer/Contestants');
    }

    public function scoring()
    {
        return Inertia::render('Organizer/Scoring');
    }

    /**
     * Display a timeline of pageant events
     */
    public function timeline()
    {
        try {
            // Get the currently logged in organizer
            $organizer = Auth::user();
            
            // Get pageant IDs assigned to this organizer
            $pageantIds = DB::table('pageant_organizers')
                ->where('user_id', $organizer->id)
                ->pluck('pageant_id');
            
            // Get all pageants with their events
            $pageants = Pageant::whereIn('id', $pageantIds)
                ->with(['events' => function($query) {
                    $query->orderBy('start_datetime');
                }])
                ->orderBy('start_date')
                ->get()
                ->map(function ($pageant) {
                    // Format dates for display
                    $startDate = $pageant->start_date ? $pageant->start_date->format('M d, Y') : null;
                    $endDate = $pageant->end_date ? $pageant->end_date->format('M d, Y') : null;
                    
                    // Get events formatted for display
                    $events = $pageant->events->map(function ($event) {
                        // Format dates to a consistent string format
                        $startDateTime = $event->start_datetime ? $event->start_datetime->format('Y-m-d H:i:s') : null;
                        $endDateTime = $event->end_datetime ? $event->end_datetime->format('Y-m-d H:i:s') : null;
                        
                        // Format for display
                        $startDateTimeFormatted = $startDateTime ? date('M d, Y h:i A', strtotime($startDateTime)) : null;
                        $endDateTimeFormatted = $endDateTime ? date('M d, Y h:i A', strtotime($endDateTime)) : null;
                        
                        return [
                            'id' => $event->id,
                            'name' => $event->name,
                            'description' => $event->description,
                            'type' => $event->type ?? 'General',
                            'start_datetime' => $startDateTimeFormatted,
                            'end_datetime' => $endDateTimeFormatted,
                            'raw_start_datetime' => $startDateTime,
                            'raw_end_datetime' => $endDateTime,
                            'venue' => $event->venue,
                            'location' => $event->location,
                            'status' => $event->status ?? 'Pending',
                            'is_milestone' => $event->is_milestone ?? false,
                            'display_order' => $event->display_order ?? 0,
                        ];
                    });
                    
                    // Calculate progress
                    $progress = $pageant->calculateProgress();
                    
                    return [
                        'id' => $pageant->id,
                        'name' => $pageant->name,
                        'description' => $pageant->description,
                        'status' => $pageant->status,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'venue' => $pageant->venue,
                        'location' => $pageant->location,
                        'progress' => $progress,
                        'cover_image' => $pageant->cover_image,
                        'events' => $events,
                        'events_count' => $events->count(),
                        'completed_events_count' => $events->where('status', 'Completed')->count(),
                    ];
                });
            
            return Inertia::render('Organizer/Timeline', [
                'pageants' => $pageants,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in timeline page: ' . $e->getMessage());
            
            // Return the page with an empty pageants array and error message
            return Inertia::render('Organizer/Timeline', [
                'pageants' => [],
                'error' => 'An error occurred while loading your timeline. Please try again.'
            ]);
        }
    }

    /**
     * Display a specific pageant's timeline
     */
    public function pageantTimeline($id)
    {
        try {
            // Get the currently logged in organizer
            $organizer = Auth::user();
            
            // Check if this organizer has access to this pageant
            $hasAccess = DB::table('pageant_organizers')
                ->where('user_id', $organizer->id)
                ->where('pageant_id', $id)
                ->exists();
                
            if (!$hasAccess) {
                return redirect()->route('organizer.timeline')
                    ->with('error', 'You do not have access to this pageant');
            }
            
            // Get the pageant details with events
            $pageant = Pageant::with(['events' => function($query) {
                    $query->orderBy('start_datetime');
                }])
                ->findOrFail($id);
            
            // Format dates for display
            $startDate = $pageant->start_date ? $pageant->start_date->format('M d, Y') : null;
            $endDate = $pageant->end_date ? $pageant->end_date->format('M d, Y') : null;
            
            // Get events formatted for display
            $events = $pageant->events->map(function ($event) {
                // Format dates to a consistent string format
                $startDateTime = $event->start_datetime ? $event->start_datetime->format('Y-m-d H:i:s') : null;
                $endDateTime = $event->end_datetime ? $event->end_datetime->format('Y-m-d H:i:s') : null;
                
                // Format for display
                $startDateTimeFormatted = $startDateTime ? date('M d, Y h:i A', strtotime($startDateTime)) : null;
                $endDateTimeFormatted = $endDateTime ? date('M d, Y h:i A', strtotime($endDateTime)) : null;
                
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'type' => $event->type ?? 'General',
                    'start_datetime' => $startDateTimeFormatted,
                    'end_datetime' => $endDateTimeFormatted,
                    'raw_start_datetime' => $startDateTime,
                    'raw_end_datetime' => $endDateTime,
                    'venue' => $event->venue,
                    'location' => $event->location,
                    'status' => $event->status ?? 'Pending',
                    'is_milestone' => $event->is_milestone ?? false,
                    'display_order' => $event->display_order ?? 0,
                ];
            });
            
            // Calculate progress
            $progress = $pageant->calculateProgress();
            
            $pageantData = [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'description' => $pageant->description,
                'status' => $pageant->status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'venue' => $pageant->venue,
                'location' => $pageant->location,
                'progress' => $progress,
                'cover_image' => $pageant->cover_image,
                'events' => $events,
                'events_count' => $events->count(),
                'completed_events_count' => $events->where('status', 'Completed')->count(),
            ];
            
            return Inertia::render('Organizer/PageantTimeline', [
                'pageant' => $pageantData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in pageant timeline: ' . $e->getMessage());
            
            return redirect()->route('organizer.timeline')
                ->with('error', 'An error occurred while loading the pageant timeline. Please try again.');
        }
    }

    /**
     * Display all pageants assigned to the organizer
     */
    public function myPageants()
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Get pageant IDs assigned to this organizer
        $pageantIds = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->pluck('pageant_id');
        
        // Get all pageants assigned to this organizer
        $pageants = Pageant::whereIn('id', $pageantIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                    'end_date' => $pageant->end_date?->format('M d, Y'),
                    'venue' => $pageant->venue,
                    'location' => $pageant->location,
                    'contestants_count' => DB::table('contestants')->where('pageant_id', $pageant->id)->count(),
                    'criteria_count' => DB::table('criteria')->where('pageant_id', $pageant->id)->count(),
                    'judges_count' => DB::table('pageant_judges')->where('pageant_id', $pageant->id)->count(),
                ];
            });
        
        $pageantCounts = [
            'active' => Pageant::whereIn('id', $pageantIds)->where('status', 'Active')->count(),
            'draft' => Pageant::whereIn('id', $pageantIds)->where('status', 'Draft')->count(),
            'setup' => Pageant::whereIn('id', $pageantIds)->where('status', 'Setup')->count(),
            'completed' => Pageant::whereIn('id', $pageantIds)->where('status', 'Completed')->count(),
            'unlocked_for_edit' => Pageant::whereIn('id', $pageantIds)->where('status', 'Unlocked_For_Edit')->count(),
            'total' => count($pageantIds),
        ];
        
        return Inertia::render('Organizer/MyPageants', [
            'pageants' => $pageants,
            'pageantCounts' => $pageantCounts,
        ]);
    }
    
    /**
     * Display detailed view of a specific pageant
     */
    public function viewPageant($id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Get the pageant details with relationships
        $pageant = Pageant::with([
                'contestants', 
                'criteria',
                'judges',
                'tabulators',
                'organizers',
                'events'
            ])
            ->findOrFail($id);
            
        // Get all tabulators for selection
        $availableTabulators = User::where('role', 'tabulator')
            ->orderBy('name')
            ->get()
            ->map(function($tabulator) {
                return [
                    'id' => $tabulator->id,
                    'name' => $tabulator->name,
                    'username' => $tabulator->username,
                    'email' => $tabulator->email,
                ];
            });
            
        // Transform data for frontend consumption
        $pageantData = [
            'id' => $pageant->id,
            'name' => $pageant->name,
            'description' => $pageant->description,
            'status' => $pageant->status,
            'start_date' => $pageant->start_date?->format('M d, Y'),
            'end_date' => $pageant->end_date?->format('M d, Y'),
            'venue' => $pageant->venue,
            'location' => $pageant->location,
            'coverImage' => $pageant->cover_image,
            'scoring_system' => $pageant->scoring_system,
            'contestants_count' => $pageant->contestants->count(),
            'criteria_count' => $pageant->criteria->count(),
            'judges_count' => $pageant->judges->count(),
            'required_judges' => $pageant->required_judges,
            'progress' => $pageant->calculateProgress(),
            'contestants' => $pageant->contestants->map(function($contestant) {
                return [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'age' => $contestant->age,
                    'photo' => $contestant->photo,
                    'origin' => $contestant->origin,
                ];
            }),
            'criteria' => $pageant->criteria->map(function($criterion) {
                return [
                    'id' => $criterion->id,
                    'name' => $criterion->name,
                    'description' => $criterion->description,
                    'weight' => $criterion->weight,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                ];
            }),
            'judges' => $pageant->judges->map(function($judge) {
                return [
                    'id' => $judge->id,
                    'name' => $judge->name,
                    'username' => $judge->username,
                ];
            }),
            'tabulators' => $pageant->tabulators->map(function($tabulator) {
                return [
                    'id' => $tabulator->id,
                    'name' => $tabulator->name,
                    'username' => $tabulator->username,
                    'active' => $tabulator->pivot->active,
                ];
            }),
            'events' => $pageant->events->map(function($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'type' => $event->type,
                    'start_datetime' => $event->start_datetime?->format('M d, Y h:i A'),
                    'end_datetime' => $event->end_datetime?->format('M d, Y h:i A'),
                    'raw_start_datetime' => $event->start_datetime,
                    'raw_end_datetime' => $event->end_datetime,
                    'venue' => $event->venue,
                    'location' => $event->location,
                    'status' => $event->status,
                    'is_milestone' => $event->is_milestone,
                    'display_order' => $event->display_order,
                ];
            }),
        ];
        
        return Inertia::render('Organizer/PageantView', [
            'pageant' => $pageantData,
            'availableTabulators' => $availableTabulators,
        ]);
    }

    public function editPageant($id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Check if the pageant status allows editing
        $pageant = Pageant::findOrFail($id);
        
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Get the pageant details with relationships
        $pageant = Pageant::with([
                'contestants', 
                'criteria',
                'judges',
                'tabulators',
                'organizers',
                'events'
            ])
            ->findOrFail($id);
            
        // Get all tabulators for selection
        $availableTabulators = User::where('role', 'tabulator')
            ->orderBy('name')
            ->get()
            ->map(function($tabulator) {
                return [
                    'id' => $tabulator->id,
                    'name' => $tabulator->name,
                    'username' => $tabulator->username,
                    'email' => $tabulator->email,
                ];
            });
            
        // Transform data for frontend consumption
        $pageantData = [
            'id' => $pageant->id,
            'name' => $pageant->name,
            'description' => $pageant->description,
            'status' => $pageant->status,
            'start_date' => $pageant->start_date,
            'end_date' => $pageant->end_date,
            'venue' => $pageant->venue,
            'location' => $pageant->location,
            'coverImage' => $pageant->cover_image,
            'scoring_system' => $pageant->scoring_system,
            'contestants_count' => $pageant->contestants->count(),
            'criteria_count' => $pageant->criteria->count(),
            'judges_count' => $pageant->judges->count(),
            'required_judges' => $pageant->required_judges,
            'progress' => $pageant->calculateProgress(),
            'contestants' => $pageant->contestants->map(function($contestant) {
                return [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'age' => $contestant->age,
                    'photo' => $contestant->photo,
                    'origin' => $contestant->origin,
                ];
            }),
            'criteria' => $pageant->criteria->map(function($criterion) {
                return [
                    'id' => $criterion->id,
                    'name' => $criterion->name,
                    'description' => $criterion->description,
                    'weight' => $criterion->weight,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                ];
            }),
            'judges' => $pageant->judges->map(function($judge) {
                return [
                    'id' => $judge->id,
                    'name' => $judge->name,
                    'username' => $judge->username,
                ];
            }),
            'tabulators' => $pageant->tabulators->map(function($tabulator) {
                return [
                    'id' => $tabulator->id,
                    'name' => $tabulator->name,
                    'username' => $tabulator->username,
                    'active' => $tabulator->pivot->active,
                ];
            }),
            'events' => $pageant->events->map(function($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'type' => $event->type,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->end_datetime,
                    'venue' => $event->venue,
                    'location' => $event->location,
                    'status' => $event->status,
                    'is_milestone' => $event->is_milestone,
                    'display_order' => $event->display_order,
                ];
            }),
        ];
        
        return Inertia::render('Organizer/PageantEdit', [
            'pageant' => $pageantData,
            'availableTabulators' => $availableTabulators,
        ]);
    }
    
    public function updatePageant(Request $request, $id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'venue' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Remove cover_image and logo from validated data since we'll handle them separately
        $pageantData = collect($validated)->except(['cover_image', 'logo'])->toArray();
        
        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $coverImageName = 'pageant_cover_' . $pageant->id . '_' . time() . '.' . $coverImage->getClientOriginalExtension();
            $coverImage->storeAs('public/pageants/covers', $coverImageName);
            $pageantData['cover_image'] = '/storage/pageants/covers/' . $coverImageName;
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'pageant_logo_' . $pageant->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs('public/pageants/logos', $logoName);
            $pageantData['logo'] = '/storage/pageants/logos/' . $logoName;
        }
        
        // Update the pageant
        $pageant->update($pageantData);
        
        // Log the action
        $this->auditLogService->log(
            'PAGEANT_UPDATED',
            'Pageant',
            $pageant->id,
            "Updated pageant '{$pageant->name}'"
        );
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Pageant updated successfully');
    }
    
    public function storeEvent(Request $request, $id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:50',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'venue' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_milestone' => 'boolean',
        ]);
        
        // Set status and display order
        $validated['status'] = 'Pending';
        $validated['pageant_id'] = $id;
        $validated['display_order'] = $pageant->events()->max('display_order') + 1;
        
        // Create the event
        $event = Event::create($validated);
        
        // Log the action
        $this->auditLogService->log(
            'EVENT_CREATED',
            'Event',
            $event->id,
            "Created event '{$event->name}' for pageant '{$pageant->name}'"
        );
        
        // Broadcast event creation notification to admin
        try {
            broadcast(new EventUpdated([
                'type' => 'event_created',
                'pageant_id' => $pageant->id,
                'pageant_name' => $pageant->name,
                'event_id' => $event->id,
                'event_name' => $event->name,
                'organizer_name' => $organizer->name,
                'is_milestone' => $event->is_milestone,
                'message' => "New event '{$event->name}' created for pageant '{$pageant->name}' by {$organizer->name}",
                'timestamp' => now()->toIso8601String()
            ]))->toOthers();
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            Log::error('Failed to broadcast event notification: ' . $e->getMessage());
        }
        
        // Update pageant progress
        $this->updatePageantProgress($pageant);
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Event created successfully');
    }
    
    public function updateEvent(Request $request, $id, $eventId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Find the event and make sure it belongs to this pageant
        $event = Event::where('id', $eventId)
            ->where('pageant_id', $id)
            ->firstOrFail();
        
        // Store original status to check for changes
        $originalStatus = $event->status;
        
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:50',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'venue' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|in:Pending,In Progress,Completed,Cancelled',
            'is_milestone' => 'boolean',
            'display_order' => 'integer',
        ]);
        
        // Update the event
        $event->update($validated);
        
        // Log the action
        $this->auditLogService->log(
            'EVENT_UPDATED',
            'Event',
            $event->id,
            "Updated event '{$event->name}' for pageant '{$pageant->name}'"
        );
        
        // Broadcast event update notification to admin, with special notice for status changes
        $notificationType = 'event_updated';
        $notificationMessage = "Event '{$event->name}' updated for pageant '{$pageant->name}' by {$organizer->name}";
        
        // If status changed, make a more specific notification
        if ($originalStatus !== $event->status) {
            $notificationType = 'event_status_changed';
            $notificationMessage = "Event '{$event->name}' status changed from '{$originalStatus}' to '{$event->status}' for pageant '{$pageant->name}'";
        }
        
        try {
            broadcast(new EventUpdated([
                'type' => $notificationType,
                'pageant_id' => $pageant->id,
                'pageant_name' => $pageant->name,
                'event_id' => $event->id,
                'event_name' => $event->name,
                'organizer_name' => $organizer->name,
                'is_milestone' => $event->is_milestone,
                'status' => $event->status,
                'previous_status' => $originalStatus,
                'message' => $notificationMessage,
                'timestamp' => now()->toIso8601String()
            ]))->toOthers();
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            Log::error('Failed to broadcast event notification: ' . $e->getMessage());
        }
        
        // Update pageant progress if event status changed
        if ($originalStatus !== $event->status) {
            $this->updatePageantProgress($pageant);
        }
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Event updated successfully');
    }
    
    public function deleteEvent($id, $eventId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Find the event and make sure it belongs to this pageant
        $event = Event::where('id', $eventId)
            ->where('pageant_id', $id)
            ->firstOrFail();
        
        // Get event name for the log
        $eventName = $event->name;
        $isMilestone = $event->is_milestone;
        
        // Delete the event
        $event->delete();
        
        // Log the action
        $this->auditLogService->log(
            'EVENT_DELETED',
            'Event',
            $eventId,
            "Deleted event '{$eventName}' from pageant '{$pageant->name}'"
        );
        
        // Broadcast event deletion notification to admin
        try {
            broadcast(new EventUpdated([
                'type' => 'event_deleted',
                'pageant_id' => $pageant->id,
                'pageant_name' => $pageant->name,
                'event_id' => $eventId,
                'event_name' => $eventName,
                'organizer_name' => $organizer->name,
                'is_milestone' => $isMilestone,
                'message' => "Event '{$eventName}' deleted from pageant '{$pageant->name}' by {$organizer->name}",
                'timestamp' => now()->toIso8601String()
            ]))->toOthers();
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            Log::error('Failed to broadcast event notification: ' . $e->getMessage());
        }
        
        // Update pageant progress
        $this->updatePageantProgress($pageant);
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Event deleted successfully');
    }
    
    /**
     * Update the progress of a pageant based on event completion
     */
    private function updatePageantProgress(Pageant $pageant)
    {
        // Get count of all events
        $totalEvents = $pageant->events()->count();
        
        if ($totalEvents > 0) {
            // Count completed events
            $completedEvents = $pageant->events()->where('status', 'Completed')->count();
            
            // Calculate progress percentage
            $progress = round(($completedEvents / $totalEvents) * 100);
            
            // Update pageant progress
            $pageant->progress = $progress;
            $pageant->save();
        }
    }

    /**
     * Update the number of required judges for a pageant
     */
    public function updateRequiredJudges(Request $request, $id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Validate request
        $validated = $request->validate([
            'required_judges' => 'required|integer|min:0|max:20',
        ]);
        
        // Update the pageant
        $pageant->update([
            'required_judges' => $validated['required_judges']
        ]);
        
        // Log the action
        $this->auditLogService->log(
            'REQUIRED_JUDGES_UPDATED',
            'Pageant',
            $pageant->id,
            "Updated required judges count for pageant '{$pageant->name}' to {$validated['required_judges']}"
        );
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Required judges updated successfully');
    }
    
    /**
     * Update the scoring system for a pageant.
     */
    public function updateScoringSystem(Request $request, $id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Validate request
        $validated = $request->validate([
            'scoring_system' => 'required|string|in:percentage,1-10,1-5,points',
        ]);
        
        // Update the pageant
        $pageant->update([
            'scoring_system' => $validated['scoring_system']
        ]);
        
        // Log the action
        $this->auditLogService->log(
            'SCORING_SYSTEM_UPDATED',
            'Pageant',
            $pageant->id,
            "Updated scoring system for pageant '{$pageant->name}' to {$validated['scoring_system']}"
        );
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Scoring system updated successfully');
    }
    
    /**
     * Assign a tabulator to a pageant
     */
    public function assignTabulator(Request $request, $id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Validate request
        $validated = $request->validate([
            'tabulator_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);
        
        // Check if the user is a tabulator
        $tabulator = User::findOrFail($validated['tabulator_id']);
        if (!$tabulator->isTabulator()) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'Selected user is not a tabulator');
        }
        
        // Check if the tabulator is already assigned to this pageant
        $exists = DB::table('pageant_tabulators')
            ->where('pageant_id', $id)
            ->where('user_id', $validated['tabulator_id'])
            ->exists();
            
        if ($exists) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'Tabulator is already assigned to this pageant');
        }
        
        // Assign the tabulator to the pageant
        $pageant->tabulators()->attach($validated['tabulator_id'], [
            'active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // Log the action
        $this->auditLogService->log(
            'TABULATOR_ASSIGNED',
            'Pageant',
            $pageant->id,
            "Assigned tabulator '{$tabulator->name}' to pageant '{$pageant->name}'"
        );
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Tabulator assigned successfully');
    }
    
    /**
     * Remove a tabulator from a pageant
     */
    public function removeTabulator($id, $tabulatorId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Find the pageant
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant cannot be edited in its current status');
        }
        
        // Check if the tabulator is assigned to this pageant
        $exists = DB::table('pageant_tabulators')
            ->where('pageant_id', $id)
            ->where('user_id', $tabulatorId)
            ->exists();
            
        if (!$exists) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'Tabulator is not assigned to this pageant');
        }
        
        // Get tabulator details for the log
        $tabulator = User::findOrFail($tabulatorId);
        
        // Remove the tabulator from the pageant
        $pageant->tabulators()->detach($tabulatorId);
        
        // Log the action
        $this->auditLogService->log(
            'TABULATOR_REMOVED',
            'Pageant',
            $pageant->id,
            "Removed tabulator '{$tabulator->name}' from pageant '{$pageant->name}'"
        );
        
        return redirect()->route('organizer.pageant.view', $id)
            ->with('success', 'Tabulator removed successfully');
    }

    /**
     * Show the contestants management page for a specific pageant
     */
    public function pageantContestants($id)
    {
        $pageant = Pageant::findOrFail($id);
        
        // Ensure this pageant belongs to the organizer
        $organizer = Auth::user();
        $hasAccess = DB::table('pageant_organizers')
            ->where('pageant_id', $pageant->id)
            ->where('user_id', $organizer->id)
            ->exists();
        
        if (!$hasAccess) {
            abort(403, 'You do not have access to this pageant.');
        }
        
        return Inertia::render('Organizer/PageantContestants', [
            'pageant' => [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'status' => $pageant->status,
                'start_date' => $pageant->start_date?->format('Y-m-d'),
                'end_date' => $pageant->end_date?->format('Y-m-d'),
                'venue' => $pageant->venue,
                'location' => $pageant->location,
            ],
        ]);
    }

    /**
     * Show the criteria management page for a specific pageant
     */
    public function pageantCriteria($id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        // dd($organizer);
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Get the pageant details
        $pageant = Pageant::findOrFail($id);
            
        // Transform pageant data for frontend consumption
        $pageantData = [
            'id' => $pageant->id,
            'name' => $pageant->name,
            'description' => $pageant->description,
            'status' => $pageant->status,
            'start_date' => $pageant->start_date?->format('Y-m-d'),
            'end_date' => $pageant->end_date?->format('Y-m-d'),
            'venue' => $pageant->venue,
            'location' => $pageant->location,
        ];
        
        return Inertia::render('Organizer/Criteria', [
            'pageant' => $pageantData,
            'pageantId' => $pageant->id
        ]);
    }

    /**
     * Show the judges management page for a specific pageant
     */
    public function pageantJudges($id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Get the pageant details
        $pageant = Pageant::findOrFail($id);
            
        // Transform pageant data for frontend consumption
        $pageantData = [
            'id' => $pageant->id,
            'name' => $pageant->name,
            'description' => $pageant->description,
            'status' => $pageant->status,
            'start_date' => $pageant->start_date?->format('Y-m-d'),
            'end_date' => $pageant->end_date?->format('Y-m-d'),
            'venue' => $pageant->venue,
            'location' => $pageant->location,
            'required_judges' => $pageant->required_judges,
            'judges' => $pageant->PageantJudges ?? []
        ];
        
        // Get available tabulators for assignment
        $availableTabulators = User::where('role', 'tabulator')
            ->where('status', 'active')
            ->select('id', 'name', 'username')
            ->orderBy('name')
            ->get();
        
        return Inertia::render('Organizer/JudgesManagement', [
            'pageant' => $pageantData,
            'pageantId' => $pageant->id,
            'availableTabulators' => $availableTabulators
        ]);
    }
} 