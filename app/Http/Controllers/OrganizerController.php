<?php

namespace App\Http\Controllers;

use App\Models\Contestant;
use App\Models\User;
use App\Models\Pageant;
use App\Models\Round;
use App\Models\Criteria;

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

        // Check if this is an AJAX request (Inertia expects redirects)
        if ($request->wantsJson()) {
            return response()->json([
                'usernameExists' => $exists
            ]);
        }

        // Return a redirect for Inertia requests
        return redirect()->back()->with([
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

        // Check if this is an AJAX request (Inertia expects redirects)
        if ($request->wantsJson()) {
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

        // Return a redirect for Inertia requests
        return redirect()->back()->with([
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
        // Send email using the dedicated mail class
        Mail::to($organizer->email)->send(new \App\Mail\OrganizerVerification($organizer, $token));
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
            'pending_approval' => Pageant::whereIn('id', $pageantIds)->where('status', 'Pending_Approval')->count(),
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
            


        return Inertia::render('Organizer/Dashboard', [
            'pageantCounts' => $pageantsByStatus,
            'recentPageants' => $recentPageants,
            'totalPageants' => count($pageantIds),

        ]);
    }

    public function criteria()
    {
        return Inertia::render('Organizer/Criteria');
    }

    public function contestants()
    {
        $organizer = Auth::user();
        
        // Get pageant IDs that this organizer has access to
        $pageantIds = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->pluck('pageant_id');
        
        // Get contestants only from pageants this organizer manages
        $contestants = Contestant::with(['pageant:id,name,status,pageant_date'])
            ->whereIn('pageant_id', $pageantIds)
            ->where('active', true)
            ->orderBy('pageant_id')
            ->orderBy('number')
            ->get()
            ->map(function ($contestant) {
                return [
                    'id' => $contestant->id,
                    'name' => $contestant->name,
                    'number' => $contestant->number,
                    'age' => $contestant->age,
                    'origin' => $contestant->origin,
                    'photo' => $contestant->photo,
                    'bio' => $contestant->bio,
                    'pageant' => [
                        'id' => $contestant->pageant->id,
                        'name' => $contestant->pageant->name,
                        'status' => $contestant->pageant->status,
                        'pageant_date' => $contestant->pageant->pageant_date,
                    ],
                    'contestNumber' => $contestant->number, // For compatibility with existing frontend
                    'city' => $contestant->origin, // For compatibility with existing frontend
                ];
            });

        // Group contestants by pageant
        $contestantsByPageant = $contestants->groupBy('pageant.id');
        
        // Get pageant summaries
        $pageantSummaries = $contestantsByPageant->map(function ($contestants, $pageantId) {
            $firstContestant = $contestants->first();
            return [
                'id' => $pageantId,
                'name' => $firstContestant['pageant']['name'],
                'status' => $firstContestant['pageant']['status'],
                'pageant_date' => $firstContestant['pageant']['pageant_date'],
                'contestant_count' => $contestants->count(),
            ];
        })->values();
        
        return Inertia::render('Organizer/Contestants', [
            'Contestants' => $contestants->values(),
            'PageantSummaries' => $pageantSummaries,
            'TotalContestants' => $contestants->count(),
        ]);
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
            
            // Get all pageants
            $pageants = Pageant::whereIn('id', $pageantIds)
                ->orderBy('pageant_date')
                ->get()
                ->map(function ($pageant) {
                    // Format dates for display
                    $startDate = $pageant->start_date ? $pageant->start_date->format('M d, Y') : null;
                    $endDate = $pageant->end_date ? $pageant->end_date->format('M d, Y') : null;
                    $pageantDate = $pageant->pageant_date ? $pageant->pageant_date->format('M d, Y') : null;
                    
                    // Calculate progress
                    $progress = $pageant->calculateProgress();
                    
                    return [
                        'id' => $pageant->id,
                        'name' => $pageant->name,
                        'description' => $pageant->description,
                        'status' => $pageant->status,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'pageant_date' => $pageantDate,
                        'venue' => $pageant->venue,
                        'location' => $pageant->location,
                        'progress' => $progress,
                        'cover_image' => $pageant->cover_image,
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
            
                    // Get the pageant details
        $pageant = Pageant::findOrFail($id);
            
            // Format dates for display
            $startDate = $pageant->start_date ? $pageant->start_date->format('M d, Y') : null;
            $endDate = $pageant->end_date ? $pageant->end_date->format('M d, Y') : null;
            
            // Events have been removed from the system
            
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
                'events' => [],
                'events_count' => 0,
                'completed_events_count' => 0,
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
                'contestants' => function($query) {
                    $query->where('active', true)->orderBy('number');
                }, 
                'criteria',
                'judges',
                'tabulators',
                'organizers'
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
            'events' => [], // Events functionality has been removed
        ];
        
        return Inertia::render('Organizer/PageantView', [
            'pageant' => $pageantData,
            'availableTabulators' => $availableTabulators,
            'auth' => [
                'user' => Auth::user()
            ],
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
                'contestants' => function($query) {
                    $query->where('active', true)->orderBy('number');
                }, 
                'criteria',
                'judges',
                'tabulators',
                'organizers'
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
            'events' => [], // Events functionality has been removed
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
     * Show the rounds management page for a specific pageant
     */
    public function pageantRounds($id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }
        
        // Get the pageant with rounds and criteria
        $pageant = Pageant::with(['rounds' => function ($query) {
            $query->orderBy('display_order');
        }, 'rounds.criteria' => function ($query) {
            $query->orderBy('display_order');
        }])->findOrFail($id);
            
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
            'rounds' => $pageant->rounds->map(function ($round) {
                return [
                    'id' => $round->id,
                    'name' => $round->name,
                    'description' => $round->description,
                    'type' => $round->type,
                    'weight' => $round->weight,
                    'display_order' => $round->display_order,
                    'is_active' => $round->is_active,
                    'scoring_config' => $round->scoring_config,
                    'criteria_count' => $round->criteria->count(),
                    'criteria' => $round->criteria->map(function ($criteria) {
                        return [
                            'id' => $criteria->id,
                            'name' => $criteria->name,
                            'description' => $criteria->description,
                            'weight' => $criteria->weight,
                            'min_score' => $criteria->min_score,
                            'max_score' => $criteria->max_score,
                            'allow_decimals' => $criteria->allow_decimals,
                            'decimal_places' => $criteria->decimal_places,
                            'display_order' => $criteria->display_order,
                        ];
                    }),
                ];
            }),
        ];
        
        return Inertia::render('Organizer/Rounds', [
            'pageant' => $pageantData,
            'pageantId' => $pageant->id
        ]);
    }

    /**
     * Store a new round for a pageant
     */
    public function storeRound(Request $request, $pageantId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:competition,preliminary,final',
            'weight' => 'required|integer|min:1|max:100',
            'display_order' => 'required|integer|min:0',
        ]);

        $round = Round::create([
            'pageant_id' => $pageantId,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'weight' => $request->weight,
            'display_order' => $request->display_order,
            'is_active' => true,
        ]);

        // Log activity
        $this->auditLogService->log(
            'ROUND_CREATED',
            "Created round: {$round->name}",
            'round',
            $round->id,
            $organizer->id,
            $pageantId
        );

        return response()->json([
            'message' => 'Round created successfully',
            'round' => [
                'id' => $round->id,
                'name' => $round->name,
                'description' => $round->description,
                'type' => $round->type,
                'weight' => $round->weight,
                'display_order' => $round->display_order,
                'is_active' => $round->is_active,
                'criteria_count' => 0,
            ]
        ]);
    }

    /**
     * Update a round
     */
    public function updateRound(Request $request, $pageantId, $roundId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:competition,preliminary,final',
            'weight' => 'required|integer|min:1|max:100',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $round = Round::where('pageant_id', $pageantId)->findOrFail($roundId);
        
        $round->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'weight' => $request->weight,
            'display_order' => $request->display_order,
            'is_active' => $request->is_active ?? $round->is_active,
        ]);

        // Log activity
        $this->auditLogService->log(
            'ROUND_UPDATED',
            "Updated round: {$round->name}",
            'round',
            $round->id,
            $organizer->id,
            $pageantId
        );

        return response()->json([
            'message' => 'Round updated successfully',
            'round' => [
                'id' => $round->id,
                'name' => $round->name,
                'description' => $round->description,
                'type' => $round->type,
                'weight' => $round->weight,
                'display_order' => $round->display_order,
                'is_active' => $round->is_active,
                'criteria_count' => $round->criteria()->count(),
            ]
        ]);
    }

    /**
     * Delete a round
     */
    public function destroyRound($pageantId, $roundId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $round = Round::where('pageant_id', $pageantId)->findOrFail($roundId);
        $roundName = $round->name;
        
        $round->delete();

        // Log activity
        $this->auditLogService->log(
            'ROUND_DELETED',
            "Deleted round: {$roundName}",
            'round',
            $roundId,
            $organizer->id,
            $pageantId
        );

        return response()->json([
            'message' => 'Round deleted successfully'
        ]);
    }

    /**
     * Store a new criteria for a round
     */
    public function storeRoundCriteria(Request $request, $pageantId, $roundId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:1|max:100',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'allow_decimals' => 'boolean',
            'decimal_places' => 'required|integer|min:0|max:4',
            'display_order' => 'required|integer|min:0',
        ]);

        // Verify round exists and belongs to pageant
        $round = Round::where('pageant_id', $pageantId)->findOrFail($roundId);

        $criteria = Criteria::create([
            'pageant_id' => $pageantId,
            'round_id' => $roundId,
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight,
            'min_score' => $request->min_score,
            'max_score' => $request->max_score,
            'allow_decimals' => $request->allow_decimals ?? true,
            'decimal_places' => $request->decimal_places,
            'display_order' => $request->display_order,
        ]);

        // Log activity
        $this->auditLogService->log(
            'CRITERIA_CREATED',
            "Created criteria: {$criteria->name} for round: {$round->name}",
            'criteria',
            $criteria->id,
            $organizer->id,
            $pageantId
        );

        return response()->json([
            'message' => 'Criteria created successfully',
            'criteria' => [
                'id' => $criteria->id,
                'name' => $criteria->name,
                'description' => $criteria->description,
                'weight' => $criteria->weight,
                'min_score' => $criteria->min_score,
                'max_score' => $criteria->max_score,
                'allow_decimals' => $criteria->allow_decimals,
                'decimal_places' => $criteria->decimal_places,
                'display_order' => $criteria->display_order,
            ]
        ]);
    }

    /**
     * Update round criteria
     */
    public function updateRoundCriteria(Request $request, $pageantId, $roundId, $criteriaId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:1|max:100',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'allow_decimals' => 'boolean',
            'decimal_places' => 'required|integer|min:0|max:4',
            'display_order' => 'required|integer|min:0',
        ]);

        $criteria = Criteria::where('pageant_id', $pageantId)
                           ->where('round_id', $roundId)
                           ->findOrFail($criteriaId);
        
        $criteria->update([
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight,
            'min_score' => $request->min_score,
            'max_score' => $request->max_score,
            'allow_decimals' => $request->allow_decimals ?? $criteria->allow_decimals,
            'decimal_places' => $request->decimal_places,
            'display_order' => $request->display_order,
        ]);

        // Log activity
        $this->auditLogService->log(
            'CRITERIA_UPDATED',
            "Updated criteria: {$criteria->name}",
            'criteria',
            $criteria->id,
            $organizer->id,
            $pageantId
        );

        return response()->json([
            'message' => 'Criteria updated successfully',
            'criteria' => [
                'id' => $criteria->id,
                'name' => $criteria->name,
                'description' => $criteria->description,
                'weight' => $criteria->weight,
                'min_score' => $criteria->min_score,
                'max_score' => $criteria->max_score,
                'allow_decimals' => $criteria->allow_decimals,
                'decimal_places' => $criteria->decimal_places,
                'display_order' => $criteria->display_order,
            ]
        ]);
    }

    /**
     * Delete round criteria
     */
    public function destroyRoundCriteria($pageantId, $roundId, $criteriaId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $criteria = Criteria::where('pageant_id', $pageantId)
                           ->where('round_id', $roundId)
                           ->findOrFail($criteriaId);
        $criteriaName = $criteria->name;
        
        $criteria->delete();

        // Log activity
        $this->auditLogService->log(
            'CRITERIA_DELETED',
            "Deleted criteria: {$criteriaName}",
            'criteria',
            $criteriaId,
            $organizer->id,
            $pageantId
        );

        return response()->json([
            'message' => 'Criteria deleted successfully'
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

    /**
     * Show the form for creating a new pageant (for organizers)
     */
    public function createPageant()
    {
        return Inertia::render('Organizer/CreatePageant');
    }
    
    /**
     * Store a newly created pageant (submitted for approval)
     */
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
                'scoring_system' => 'required|string|in:percentage,1-10,1-5,points',
            ]);
            
            // Create pageant with Pending_Approval status
            $pageant = Pageant::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'venue' => $validated['venue'],
                'location' => $validated['location'],
                'status' => 'Pending_Approval',
                'created_by' => Auth::id(),
                'scoring_system' => $validated['scoring_system'],
            ]);
            
            // Attach the organizer who created it
            $pageant->organizers()->attach(Auth::id());
            
            // Log the action
            $this->auditLogService->log(
                'PAGEANT_CREATED',
                'Pageant',
                $pageant->id,
                "Created pageant '{$pageant->name}' for approval"
            );
            
            return redirect()->route('organizer.dashboard')
                ->with('success', "Pageant '{$pageant->name}' has been submitted for approval!");
            
        } catch (\Exception $e) {
            Log::error('Error creating pageant for approval: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'There was an error submitting your pageant. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Toggle pageant between draft and final status
     */
    public function togglePageantStatus(Request $request, $id)
    {
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
        
        $pageant = Pageant::findOrFail($id);
        $validated = $request->validate([
            'action' => 'required|in:set_draft,set_final'
        ]);
        
        if ($validated['action'] === 'set_final') {
            if (!$pageant->isDraft()) {
                return redirect()->back()
                    ->with('error', 'Only draft pageants can be finalized');
            }
            $pageant->setFinal($organizer->id);
            $message = 'Pageant has been finalized and locked for editing';
        } else {
            if (!($pageant->isSetup() || $pageant->isLocked())) {
                return redirect()->back()
                    ->with('error', 'Only setup/locked pageants can be set to draft');
            }
            $pageant->setDraft();
            $message = 'Pageant has been set to draft and unlocked for editing';
        }
        
        // Log the action
        $this->auditLogService->log(
            'PAGEANT_STATUS_CHANGED',
            'Pageant',
            $pageant->id,
            "Organizer {$organizer->name} changed pageant '{$pageant->name}' status via toggle: {$validated['action']}"
        );
        
        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Lock pageant configuration
     */
    public function lockPageant(Request $request, $id)
    {
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
        
        $pageant = Pageant::findOrFail($id);
        
        if ($pageant->isLocked()) {
            return redirect()->back()
                ->with('error', 'Pageant is already locked');
        }
        
        if (!in_array($pageant->status, ['Draft', 'Setup'])) {
            return redirect()->back()
                ->with('error', 'Only draft or setup pageants can be locked');
        }
        
        $pageant->lockConfiguration($organizer->id);
        
        // Log the action
        $this->auditLogService->log(
            'PAGEANT_LOCKED',
            'Pageant',
            $pageant->id,
            "Organizer {$organizer->name} locked pageant '{$pageant->name}' configuration"
        );
        
        return redirect()->back()
            ->with('success', 'Pageant configuration has been locked');
    }

    /**
     * Unlock pageant configuration
     */
    public function unlockPageant(Request $request, $id)
    {
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
        
        $pageant = Pageant::findOrFail($id);
        
        if (!$pageant->isLocked()) {
            return redirect()->back()
                ->with('error', 'Pageant is not locked');
        }
        
        $pageant->unlockConfiguration();
        
        // Log the action
        $this->auditLogService->log(
            'PAGEANT_UNLOCKED',
            'Pageant',
            $pageant->id,
            "Organizer {$organizer->name} unlocked pageant '{$pageant->name}' configuration"
        );
        
        return redirect()->back()
            ->with('success', 'Pageant configuration has been unlocked');
    }

    /**
     * Update pageant status (for organizers)
     */
    public function updatePageantStatus(Request $request, $id)
    {
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
        
        $pageant = Pageant::findOrFail($id);
        
        // Validate the status change request
        $validated = $request->validate([
            'status' => 'required|in:Draft,Setup,Active,Completed,Unlocked_For_Edit',
        ]);
        
        $newStatus = $validated['status'];
        $oldStatus = $pageant->status;
        
        // Check if pageant date has elapsed and should be auto-completed
        $pageantDate = $pageant->start_date ? $pageant->start_date->startOfDay() : null;
        $today = now()->startOfDay();
        $isDateElapsed = $pageantDate && $pageantDate < $today;
        
        // If pageant date has elapsed and it's not completed, suggest completion
        if ($isDateElapsed && !in_array($oldStatus, ['Completed', 'Unlocked_For_Edit'])) {
            if ($newStatus !== 'Completed') {
                return redirect()->back()
                    ->with('warning', 'This pageant\'s date has elapsed. It should be marked as completed.');
            }
        }
        
        // Prevent invalid status transitions
        if ($oldStatus === $newStatus) {
            return redirect()->back()
                ->with('error', 'Pageant is already in this status');
        }
        
        // Check if the status transition is allowed
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            $user = Auth::user();
            $errorMessage = "Cannot change status from {$oldStatus} to {$newStatus}";
            
            // Add specific error messages for common issues
            if ($oldStatus === 'Completed' && $user->role !== 'admin') {
                $errorMessage .= '. Only administrators can modify completed pageants.';
            } elseif ($newStatus === 'Unlocked_For_Edit' && $user->role !== 'admin') {
                $errorMessage .= '. Only administrators can unlock pageants for editing.';
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);
        }
        
        // Special handling for certain status changes
        if ($newStatus === 'Setup') {
            // Moving to Setup should lock the pageant
            $pageant->lockConfiguration($organizer->id);
        } elseif ($oldStatus === 'Setup' && $newStatus === 'Draft') {
            // Moving from Setup back to Draft should unlock
            $pageant->unlockConfiguration();
        } else {
            // Regular status update
            $pageant->update(['status' => $newStatus]);
        }
        
        // Log the action
        $this->auditLogService->log(
            'PAGEANT_STATUS_CHANGED',
            'Pageant',
            $pageant->id,
            "Organizer {$organizer->name} changed pageant '{$pageant->name}' status from '{$oldStatus}' to '{$newStatus}'"
        );
        
        return redirect()->back()
            ->with('success', "Pageant status changed from '{$oldStatus}' to '{$newStatus}'");
    }
    
    /**
     * Store a new contestant for a specific pageant
     */
    public function storeContestant(Request $request, $id)
    {
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }
        
        $pageant = Pageant::findOrFail($id);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return response()->json(['error' => 'This pageant cannot be edited in its current status'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:10|unique:contestants,number,NULL,id,pageant_id,' . $id,
            'age' => 'required|integer|min:16|max:35',
            'origin' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // Handle photo upload if provided
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = 'contestant_' . $id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/contestants', $photoName);
            $photoPath = '/storage/contestants/' . $photoName;
        }
        
        $contestant = Contestant::create([
            'pageant_id' => $id,
            'name' => $validated['name'],
            'number' => $validated['number'],
            'age' => $validated['age'],
            'origin' => $validated['origin'],
            'bio' => $validated['bio'],
            'photo' => $photoPath,
            'active' => true,
        ]);
        
        // Log the action
        $this->auditLogService->log(
            'CONTESTANT_CREATED',
            'Contestant',
            $contestant->id,
            "Created contestant '{$contestant->name}' for pageant '{$pageant->name}'"
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Contestant added successfully',
            'contestant' => [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->name,
                'age' => $contestant->age,
                'origin' => $contestant->origin,
                'photo' => $contestant->photo,
                'bio' => $contestant->bio,
            ]
        ]);
    }
    
    /**
     * Update a contestant for a specific pageant
     */
    public function updateContestant(Request $request, $id, $contestantId)
    {
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }
        
        $pageant = Pageant::findOrFail($id);
        $contestant = Contestant::where('pageant_id', $id)->findOrFail($contestantId);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return response()->json(['error' => 'This pageant cannot be edited in its current status'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:10|unique:contestants,number,' . $contestantId . ',id,pageant_id,' . $id,
            'age' => 'required|integer|min:16|max:35',
            'origin' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = 'contestant_' . $id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/contestants', $photoName);
            $validated['photo'] = '/storage/contestants/' . $photoName;
        }
        
        $contestant->update($validated);
        
        // Log the action
        $this->auditLogService->log(
            'CONTESTANT_UPDATED',
            'Contestant',
            $contestant->id,
            "Updated contestant '{$contestant->name}' for pageant '{$pageant->name}'"
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Contestant updated successfully',
            'contestant' => [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->name,
                'age' => $contestant->age,
                'origin' => $contestant->origin,
                'photo' => $contestant->photo,
                'bio' => $contestant->bio,
            ]
        ]);
    }
    
    /**
     * Remove a contestant from a specific pageant
     */
    public function removeContestant($id, $contestantId)
    {
        $organizer = Auth::user();
        
        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();
            
        if (!$hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }
        
        $pageant = Pageant::findOrFail($id);
        $contestant = Contestant::where('pageant_id', $id)->findOrFail($contestantId);
        
        // Check if the pageant status allows editing
        if (!($pageant->isDraft() || $pageant->isSetup() || $pageant->isUnlockedForEdit())) {
            return response()->json(['error' => 'This pageant cannot be edited in its current status'], 403);
        }
        
        // Store contestant name for logging before deletion
        $contestantName = $contestant->name;
        
        // Soft delete by setting active to false (preserve for historical data)
        $contestant->update(['active' => false]);
        
        // Log the action
        $this->auditLogService->log(
            'CONTESTANT_REMOVED',
            'Contestant',
            $contestant->id,
            "Removed contestant '{$contestantName}' from pageant '{$pageant->name}'"
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Contestant removed successfully'
        ]);
    }

    /**
     * Check if a status transition is valid for organizers
     */
    private function isValidStatusTransition($fromStatus, $toStatus)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        
        // Base transitions for organizers
        $allowedTransitions = [
            'Draft' => ['Setup', 'Active'],
            'Setup' => ['Draft', 'Active'],
            'Active' => ['Completed'],
            'Completed' => [], // Completed pageants cannot be reverted by organizers
            'Unlocked_For_Edit' => ['Completed'], // Can only go back to completed
        ];
        
        // Only admins can set or modify Unlocked_For_Edit status
        if ($isAdmin) {
            $allowedTransitions['Completed'] = ['Unlocked_For_Edit'];
            // Admins can also transition any status to Unlocked_For_Edit (for emergencies)
            foreach ($allowedTransitions as $status => $transitions) {
                if ($status !== 'Unlocked_For_Edit' && !in_array('Unlocked_For_Edit', $transitions)) {
                    $allowedTransitions[$status][] = 'Unlocked_For_Edit';
                }
            }
        }
        
        return isset($allowedTransitions[$fromStatus]) && 
               in_array($toStatus, $allowedTransitions[$fromStatus]);
    }
} 