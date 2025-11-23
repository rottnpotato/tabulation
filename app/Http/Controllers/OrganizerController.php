<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizerRegistrationRequest;
use App\Http\Requests\StoreRoundRequest;
use App\Http\Requests\UpdatePageantRequest;
use App\Http\Requests\UpdateRoundRequest;
use App\Http\Requests\UpdateScoringSystemRequest;
use App\Models\Contestant;
use App\Models\Criteria;
use App\Models\Pageant;
use App\Models\Round;
use App\Models\Score;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrganizerController extends Controller
{
    protected $auditLogService;

    protected $activityService;

    public function __construct(AuditLogService $auditLogService, ActivityService $activityService)
    {
        $this->auditLogService = $auditLogService;
        $this->activityService = $activityService;
    }

    /**
     * Check if a username already exists
     */
    public function checkUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $exists = User::where('username', $request->username)->exists();

        // Check if this is an AJAX request (Inertia expects redirects)
        if ($request->wantsJson()) {
            return response()->json([
                'usernameExists' => $exists,
            ]);
        }

        // Return a redirect for Inertia requests
        return back()->with([
            'usernameExists' => $exists,
        ]);
    }

    /**
     * Create a new organizer user with pending verification
     */
    public function store(OrganizerRegistrationRequest $request)
    {
        $validated = $request->validated();

        // Generate verification token
        $verificationToken = Str::random(64);
        $expiresAt = now()->addHours(48);

        // Create the user with pending verification
        $organizer = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => $validated['password'],
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
                ],
            ]);
        }

        // Return a redirect for Inertia requests
        return back()->with([
            'success' => true,
            'message' => 'Organizer created successfully! A verification email has been sent.',
            'organizer' => [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'email' => $organizer->email,
            ],
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

        if (! $organizer) {
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

        // Ensure user is logged out before showing verification success
        Auth::logout();

        // Show verification success page with link to set new password
        $resetToken = $this->generatePasswordResetToken($organizer);

        return Inertia::render('VerificationSuccess', [
            'user' => [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'email' => $organizer->email,
            ],
            'resetPasswordUrl' => route('password.reset', ['token' => $resetToken, 'email' => $organizer->email]),
        ]);
    }

    /**
     * Generate a password reset token for the user
     */
    private function generatePasswordResetToken(User $user)
    {
        $token = Str::random(64);

        // Store the token in the password reset table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
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
            'draft' => Pageant::whereIn('id', $pageantIds)->where('status', 'Draft')->count(),
            'ongoing' => Pageant::whereIn('id', $pageantIds)->where('status', 'Ongoing')->count(),
            'completed' => Pageant::whereIn('id', $pageantIds)->where('status', 'Completed')->count(),
            // Legacy statuses for backward compatibility
            'active' => Pageant::whereIn('id', $pageantIds)->where('status', 'Active')->count(),
            'setup' => Pageant::whereIn('id', $pageantIds)->where('status', 'Setup')->count(),
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

        // Get recent activities for this organizer's pageants
        $recentActivities = $this->activityService->getOrganizerActivities($organizer->id, 15);

        return Inertia::render('Organizer/Dashboard', [
            'pageantCounts' => $pageantsByStatus,
            'recentPageants' => $recentPageants,
            'totalPageants' => count($pageantIds),
            'recentActivities' => $recentActivities,
            'pageantIds' => $pageantIds->toArray(), // For real-time channel subscriptions
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
                    'gender' => $contestant->gender,
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

        // Build pageant summaries for ALL managed pageants (including those with 0 contestants)
        $managedPageants = Pageant::whereIn('id', $pageantIds)
            ->select('id', 'name', 'status', 'pageant_date')
            ->get();

        $pageantSummaries = $managedPageants->map(function ($pageant) use ($contestantsByPageant) {
            $count = $contestantsByPageant->has($pageant->id)
                ? $contestantsByPageant->get($pageant->id)->count()
                : 0;

            return [
                'id' => $pageant->id,
                'name' => $pageant->name,
                'status' => $pageant->status,
                'pageant_date' => $pageant->pageant_date,
                'contestant_count' => $count,
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
            Log::error('Error in timeline page: '.$e->getMessage());

            // Return the page with an empty pageants array and error message
            return Inertia::render('Organizer/Timeline', [
                'pageants' => [],
                'error' => 'An error occurred while loading your timeline. Please try again.',
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

            if (! $hasAccess) {
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
            Log::error('Error in pageant timeline: '.$e->getMessage());

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
                    'contestant_type' => $pageant->contestant_type,
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
            'draft' => Pageant::whereIn('id', $pageantIds)->where('status', 'Draft')->count(),
            'ongoing' => Pageant::whereIn('id', $pageantIds)->where('status', 'Ongoing')->count(),
            'completed' => Pageant::whereIn('id', $pageantIds)->where('status', 'Completed')->count(),
            // Legacy statuses for backward compatibility
            'active' => Pageant::whereIn('id', $pageantIds)->where('status', 'Active')->count(),
            'setup' => Pageant::whereIn('id', $pageantIds)->where('status', 'Setup')->count(),
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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        // Get the pageant details with relationships
        $pageant = Pageant::with([
            'contestants' => function ($query) {
                $query->where('active', true)->orderBy('number');
            },
            'contestants.images',
            'criteria',
            'rounds' => function ($query) {
                $query->orderBy('display_order');
            },
            'rounds.criteria' => function ($query) {
                $query->orderBy('display_order');
            },
            'judges',
            'tabulators',
            'organizers',
        ])
            ->findOrFail($id);

        // Get all tabulators for selection
        $availableTabulators = User::where('role', 'tabulator')
            ->orderBy('name')
            ->get()
            ->map(function ($tabulator) {
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
            'contestant_type' => $pageant->contestant_type,
            'venue' => $pageant->venue,
            'location' => $pageant->location,
            'coverImage' => $pageant->cover_image,
            'logo' => $pageant->logo,
            'scoring_system' => $pageant->scoring_system,
            'contestants_count' => $pageant->contestants->count(),
            'criteria_count' => $pageant->criteria->count(),
            'judges_count' => $pageant->judges->count(),
            'required_judges' => $pageant->required_judges,
            'progress' => $pageant->calculateProgress(),
            'can_be_edited' => $pageant->canBeEdited(),
            'has_start_date_reached' => $pageant->hasStartDateReached(),
            'contestants' => $pageant->contestants->map(function ($contestant) {
                // Get images that belong ONLY to this specific contestant with explicit database filtering
                $contestantImages = $contestant->images()
                    ->where('contestant_id', $contestant->id)
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('display_order', 'asc')
                    ->get();

                // Find primary image with strict contestant ID verification
                $primaryImage = $contestantImages
                    ->where('contestant_id', $contestant->id)
                    ->where('is_primary', true)
                    ->first();

                // Get other images with strict contestant ID verification
                $otherImages = $contestantImages
                    ->where('contestant_id', $contestant->id)
                    ->where('is_primary', false)
                    ->sortBy('display_order');

                // Build photos array with explicit contestant ID validation
                $photos = collect();
                $photoDetails = collect();

                if ($primaryImage && $primaryImage->contestant_id === $contestant->id) {
                    $photos->push('/storage/'.$primaryImage->image_path);
                    $photoDetails->push([
                        'id' => $primaryImage->id,
                        'url' => '/storage/'.$primaryImage->image_path,
                        'is_primary' => true,
                        'display_order' => $primaryImage->display_order,
                        'contestant_id' => $primaryImage->contestant_id,
                    ]);
                }

                $otherImages->each(function ($image) use ($photos, $photoDetails, $contestant) {
                    // Double-check contestant ID matches before adding
                    if ($image->contestant_id === $contestant->id) {
                        $photos->push('/storage/'.$image->image_path);
                        $photoDetails->push([
                            'id' => $image->id,
                            'url' => '/storage/'.$image->image_path,
                            'is_primary' => false,
                            'display_order' => $image->display_order,
                            'contestant_id' => $image->contestant_id,
                        ]);
                    }
                });

                // Determine the best primary image to display
                $displayImage = null;
                if ($primaryImage && $primaryImage->contestant_id === $contestant->id) {
                    $displayImage = '/storage/'.$primaryImage->image_path;
                } elseif ($photos->isNotEmpty()) {
                    $displayImage = $photos->first();
                } else {
                    $displayImage = $contestant->photo;
                }

                return [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'gender' => $contestant->gender,
                    'age' => $contestant->age,
                    'photo' => $contestant->photo,
                    'origin' => $contestant->origin,
                    'bio' => $contestant->bio,
                    'primary_image' => $displayImage,
                    'photos' => $photos->toArray(),
                    'photo_details' => $photoDetails->toArray(),
                    'images_count' => $contestantImages->count(),
                    'contestant_id' => $contestant->id, // Explicit contestant ID for frontend validation
                ];
            }),
            'criteria' => $pageant->criteria->map(function ($criterion) {
                return [
                    'id' => $criterion->id,
                    'name' => $criterion->name,
                    'description' => $criterion->description,
                    'weight' => $criterion->weight,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                ];
            }),
            'judges' => $pageant->judges->map(function ($judge) {
                return [
                    'id' => $judge->id,
                    'name' => $judge->name,
                    'username' => $judge->username,
                ];
            }),
            'tabulators' => $pageant->tabulators->map(function ($tabulator) {
                return [
                    'id' => $tabulator->id,
                    'name' => $tabulator->name,
                    'username' => $tabulator->username,
                    'active' => $tabulator->pivot->active,
                ];
            }),
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
            'events' => [], // Events functionality has been removed
        ];

        return Inertia::render('Organizer/PageantView', [
            'pageant' => $pageantData,
            'availableTabulators' => $availableTabulators,
            'auth' => [
                'user' => Auth::user(),
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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        // Check if the pageant status allows editing
        $pageant = Pageant::findOrFail($id);

        if (! $pageant->canBeEdited()) {
            $errorMessage = 'This pageant cannot be edited';

            if ($pageant->hasStartDateReached()) {
                $errorMessage = 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.';
            } elseif (! $pageant->isDraft()) {
                $errorMessage = 'This pageant cannot be edited in its current status';
            }

            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', $errorMessage);
        }

        // Get the pageant details with relationships
        $pageant = Pageant::with([
            'contestants' => function ($query) {
                $query->where('active', true)->orderBy('number');
            },
            'criteria',
            'rounds' => function ($query) {
                $query->orderBy('display_order');
            },
            'rounds.criteria' => function ($query) {
                $query->orderBy('display_order');
            },
            'judges',
            'tabulators',
            'organizers',
        ])
            ->findOrFail($id);

        // Get all tabulators for selection
        $availableTabulators = User::where('role', 'tabulator')
            ->orderBy('name')
            ->get()
            ->map(function ($tabulator) {
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
            'logo' => $pageant->logo,
            'scoring_system' => $pageant->scoring_system,
            'contestants_count' => $pageant->contestants->count(),
            'criteria_count' => $pageant->criteria->count(),
            'judges_count' => $pageant->judges->count(),
            'required_judges' => $pageant->required_judges,
            'progress' => $pageant->calculateProgress(),
            'contestants' => $pageant->contestants->map(function ($contestant) {
                return [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'age' => $contestant->age,
                    'photo' => $contestant->photo,
                    'origin' => $contestant->origin,
                ];
            }),
            'criteria' => $pageant->criteria->map(function ($criterion) {
                return [
                    'id' => $criterion->id,
                    'name' => $criterion->name,
                    'description' => $criterion->description,
                    'weight' => $criterion->weight,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                ];
            }),
            'judges' => $pageant->judges->map(function ($judge) {
                return [
                    'id' => $judge->id,
                    'name' => $judge->name,
                    'username' => $judge->username,
                ];
            }),
            'tabulators' => $pageant->tabulators->map(function ($tabulator) {
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

    public function updatePageant(UpdatePageantRequest $request, $id)
    {
        if (! Auth::user()->hasPermission('organizer_edit_own_pageant')) {
            return redirect()->back()->with('error', 'You do not have permission to edit pageants.');
        }

        // Get the currently logged in organizer
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        // Find the pageant
        $pageant = Pageant::findOrFail($id);

        // Check if the pageant can be edited
        if (! $pageant->canBeEdited()) {
            $errorMessage = $pageant->hasStartDateReached()
                ? 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.'
                : 'This pageant cannot be edited in its current status';

            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', $errorMessage);
        }

        // Validate request
        $validated = $request->validated();

        // Remove cover_image and logo from validated data since we'll handle them separately
        $pageantData = collect($validated)->except(['cover_image', 'logo'])->toArray();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $coverImageName = 'pageant_cover_'.$pageant->id.'_'.time().'.'.$coverImage->getClientOriginalExtension();
            $coverImage->storeAs('public/pageants/covers', $coverImageName);
            $pageantData['cover_image'] = '/storage/public/pageants/covers/'.$coverImageName;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'pageant_logo_'.$pageant->id.'_'.time().'.'.$logo->getClientOriginalExtension();
            $logo->storeAs('public/pageants/logos', $logoName);
            $pageantData['logo'] = '/storage/public/pageants/logos/'.$logoName;
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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        // Find the pageant
        $pageant = Pageant::findOrFail($id);

        // Check if the pageant can be edited
        if (! $pageant->canBeEdited()) {
            $errorMessage = $pageant->hasStartDateReached()
                ? 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.'
                : 'This pageant cannot be edited in its current status';

            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', $errorMessage);
        }

        // Validate request
        $validated = $request->validate([
            'required_judges' => 'required|integer|min:0|max:20',
        ]);

        // Update the pageant
        $pageant->update([
            'required_judges' => $validated['required_judges'],
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
    public function updateScoringSystem(UpdateScoringSystemRequest $request, $id)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        // Find the pageant
        $pageant = Pageant::findOrFail($id);

        // Check if the pageant can be edited
        if (! $pageant->canBeEdited()) {
            $errorMessage = $pageant->hasStartDateReached()
                ? 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.'
                : 'This pageant cannot be edited in its current status';

            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', $errorMessage);
        }

        // Validate request
        $validated = $request->validated();

        // Update the pageant
        $pageant->update([
            'scoring_system' => $validated['scoring_system'],
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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        // Find the pageant
        $pageant = Pageant::findOrFail($id);

        // Check if pageant already has a tabulator assigned
        if ($pageant->tabulators()->count() > 0) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant already has a tabulator assigned. Please remove the existing tabulator first.');
        }

        // Check if pageant already has a tabulator assigned
        if ($pageant->tabulators()->count() > 0) {
            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', 'This pageant already has a tabulator assigned. Please remove the existing tabulator first.');
        }

        // Check if the pageant can be edited
        if (! $pageant->canBeEdited()) {
            $errorMessage = $pageant->hasStartDateReached()
                ? 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.'
                : 'This pageant cannot be edited in its current status';

            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', $errorMessage);
        }

        // Validate request
        $validated = $request->validate([
            'tabulator_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Check if the user is a tabulator
        $tabulator = User::findOrFail($validated['tabulator_id']);
        if (! $tabulator->isTabulator()) {
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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        // Find the pageant
        $pageant = Pageant::findOrFail($id);

        // Check if the pageant can be edited
        if (! $pageant->canBeEdited()) {
            $errorMessage = $pageant->hasStartDateReached()
                ? 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.'
                : 'This pageant cannot be edited in its current status';

            return redirect()->route('organizer.pageant.view', $id)
                ->with('error', $errorMessage);
        }

        // Check if the tabulator is assigned to this pageant
        $exists = DB::table('pageant_tabulators')
            ->where('pageant_id', $id)
            ->where('user_id', $tabulatorId)
            ->exists();

        if (! $exists) {
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

        if (! $hasAccess) {
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
                'contestant_type' => $pageant->contestant_type,
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

        if (! $hasAccess) {
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
            'pageantId' => $pageant->id,
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

        if (! $hasAccess) {
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
            'pageantId' => $pageant->id,
        ]);
    }

    /**
     * Store a new round for a pageant
     */
    public function storeRound(StoreRoundRequest $request, $pageantId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $validated = $request->validated();

        $round = Round::create([
            'pageant_id' => $pageantId,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'identifier' => $validated['identifier'],
            'weight' => $validated['weight'],
            'display_order' => $validated['display_order'],
            'is_active' => true,
        ]);

        // Generate identifier if not provided
        if (! $round->identifier) {
            $round->update(['identifier' => $round->generateIdentifier()]);
        }

        // Log activity
        $this->auditLogService->log(
            'ROUND_CREATED',
            'Round',
            $round->id,
            "Created round: {$round->name}"
        );

        return back()->with('success', 'Round created successfully');
    }

    /**
     * Update a round
     */
    public function updateRound(UpdateRoundRequest $request, $pageantId, $roundId)
    {
        // Get the currently logged in organizer
        $organizer = Auth::user() ?? abort(401, 'Unauthenticated');

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $validated = $request->validated();

        $round = Round::where('pageant_id', $pageantId)->findOrFail($roundId);

        $round->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'identifier' => $validated['identifier'] ?? $round->identifier,
            'weight' => $validated['weight'],
            'display_order' => $validated['display_order'],
            'is_active' => $validated['is_active'] ?? $round->is_active,
        ]);

        // Log activity
        $this->auditLogService->log(
            'ROUND_UPDATED',
            'Round',
            $round->id,
            "Updated round: {$round->name}"
        );

        return back()->with('success', 'Round updated successfully');
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

        if (! $hasAccess) {
            return response()->json(['error' => 'You do not have access to this pageant'], 403);
        }

        $round = Round::where('pageant_id', $pageantId)->findOrFail($roundId);
        $roundName = $round->name;

        $round->delete();

        // Log activity
        $this->auditLogService->log(
            'ROUND_DELETED',
            'Round',
            $roundId,
            "Deleted round: {$roundName}"
        );

        return back()->with('success', 'Round deleted successfully');
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

        if (! $hasAccess) {
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
            'Criteria',
            $criteria->id,
            "Created criteria: {$criteria->name} for round: {$round->name}"
        );

        return back()->with('success', 'Criteria created successfully');
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

        if (! $hasAccess) {
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
            'Criteria',
            $criteria->id,
            "Updated criteria: {$criteria->name}"
        );

        return back()->with('success', 'Criteria updated successfully');
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

        if (! $hasAccess) {
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
            'Criteria',
            $criteriaId,
            "Deleted criteria: {$criteriaName}"
        );

        return back()->with('success', 'Criteria deleted successfully');
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

        if (! $hasAccess) {
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
            'judges' => $pageant->PageantJudges ?? [],
        ];

        // Get available tabulators for assignment
        $availableTabulators = User::where('role', 'tabulator')
            ->where('is_active', true)
            ->select('id', 'name', 'username')
            ->orderBy('name')
            ->get();

        return Inertia::render('Organizer/JudgesManagement', [
            'pageant' => $pageantData,
            'pageantId' => $pageant->id,
            'availableTabulators' => $availableTabulators,
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
        if (! Auth::user()->hasPermission('organizer_edit_own_pageant')) {
            abort(403, 'You do not have permission to create pageants.');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'pageant_date' => 'required|date|after_or_equal:today',
                'venue' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'scoring_system' => 'required|string|in:percentage,1-10,1-5,points',
                'contestant_type' => 'required|string|in:solo,pairs,both',
            ]);

            // Create pageant with Pending_Approval status
            $pageant = Pageant::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'pageant_date' => $validated['pageant_date'],
                'venue' => $validated['venue'],
                'location' => $validated['location'],
                'status' => 'Pending_Approval',
                'created_by' => Auth::id(),
                'scoring_system' => $validated['scoring_system'],
                'contestant_type' => $validated['contestant_type'] ?? 'both',
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

            // Broadcast event to notify admins
            broadcast(new \App\Events\PageantCreated($pageant, Auth::user()))->toOthers();

            return redirect()->route('organizer.dashboard')
                ->with('success', "Pageant '{$pageant->name}' has been submitted for approval!");

        } catch (\Exception $e) {
            Log::error('Error creating pageant for approval: '.$e->getMessage());

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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($id);
        $validated = $request->validate([
            'action' => 'required|in:set_draft,set_final',
        ]);

        if ($validated['action'] === 'set_final') {
            if (! $pageant->isDraft()) {
                return back()
                    ->with('error', 'Only draft pageants can be finalized');
            }
            $pageant->setFinal($organizer->id);
            $message = 'Pageant has been finalized and locked for editing';
        } else {
            if (! ($pageant->isSetup() || $pageant->isLocked())) {
                return back()
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

        return back()
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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($id);

        if ($pageant->isLocked()) {
            return back()
                ->with('error', 'Pageant is already locked');
        }

        if (! in_array($pageant->status, ['Draft', 'Setup'])) {
            return back()
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

        return back()
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

        if (! $hasAccess) {
            return redirect()->route('organizer.my-pageants')
                ->with('error', 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($id);

        if (! $pageant->isLocked()) {
            return back()
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

        return back()
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

        if (! $hasAccess) {
            return back()->with('error', 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($id);

        // Validate the status change request
        $validated = $request->validate([
            'status' => 'required|in:Draft,Ongoing,Completed',
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $pageant->status;

        // Check if pageant date has elapsed and should be auto-completed
        $pageantDate = $pageant->start_date ? $pageant->start_date->startOfDay() : null;
        $today = now()->startOfDay();
        $isDateElapsed = $pageantDate && $pageantDate < $today;

        // If pageant date has elapsed and it's not completed, suggest completion
        if ($isDateElapsed && $oldStatus !== 'Completed') {
            if ($newStatus !== 'Completed') {
                return back()
                    ->with('warning', 'This pageant\'s date has elapsed. It should be marked as completed.');
            }
        }

        // Prevent invalid status transitions
        if ($oldStatus === $newStatus) {
            return back()
                ->with('error', 'Pageant is already in this status');
        }

        // Check if the status transition is allowed
        // Allow Draft -> Completed OR Ongoing/Active -> Completed if date has elapsed
        $allowDirectCompletion = $isDateElapsed &&
            ($oldStatus === 'Draft' || $oldStatus === 'Ongoing' || $oldStatus === 'Active') &&
            $newStatus === 'Completed';

        if (! $allowDirectCompletion && ! $this->isValidStatusTransition($oldStatus, $newStatus)) {
            $user = Auth::user();
            $errorMessage = "Cannot change status from {$oldStatus} to {$newStatus}";

            // Add specific error messages for common issues
            if ($oldStatus === 'Completed' && $user->role !== 'admin') {
                $errorMessage .= '. Only administrators can modify completed pageants.';
            }

            return back()
                ->with('error', $errorMessage);
        }

        // Check requirements for status transitions
        // Skip score requirement if date has elapsed and we're auto-completing
        $skipScoreRequirement = $isDateElapsed && $newStatus === 'Completed';
        $requirementCheck = $this->checkStatusRequirements($pageant, $newStatus, $skipScoreRequirement);
        if (! $requirementCheck['passed']) {
            return back()
                ->with('error', 'Cannot change status: '.$requirementCheck['message']);
        }

        // Update the status
        $pageant->update(['status' => $newStatus]);

        // Log the action
        $logMessage = "Organizer {$organizer->name} changed pageant '{$pageant->name}' status from '{$oldStatus}' to '{$newStatus}'";
        if ($isDateElapsed && $newStatus === 'Completed') {
            $logMessage .= ' (auto-completion due to elapsed date)';
        }

        $this->auditLogService->log(
            'PAGEANT_STATUS_CHANGED',
            'Pageant',
            $pageant->id,
            $logMessage
        );

        $successMessage = "Pageant status changed from '{$oldStatus}' to '{$newStatus}'";
        if ($isDateElapsed && $newStatus === 'Completed') {
            $successMessage .= '. This pageant has been auto-completed because the scheduled date has elapsed.';
        }

        return back()
            ->with('success', $successMessage);
    }

    /**
     * Request edit access for a pageant with start date reached
     */
    public function requestEditAccess(Request $request, $id)
    {
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $id)
            ->exists();

        if (! $hasAccess) {
            return back()->with('error', 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($id);

        // Validate the reason
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        // Check if pageant is already editable
        if ($pageant->canBeEdited()) {
            return back()->with('info', 'This pageant is already editable');
        }

        // Check if there's already a pending request
        $existingRequest = DB::table('edit_access_requests')
            ->where('pageant_id', $id)
            ->where('organizer_id', $organizer->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('warning', 'You already have a pending edit access request for this pageant');
        }

        // Create the edit access request
        DB::table('edit_access_requests')->insert([
            'pageant_id' => $id,
            'organizer_id' => $organizer->id,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log the action
        $this->auditLogService->log(
            'EDIT_ACCESS_REQUESTED',
            'Pageant',
            $pageant->id,
            "Organizer {$organizer->name} requested edit access for pageant '{$pageant->name}'"
        );

        // Send email notification to admins
        try {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new \App\Mail\EditAccessRequested($organizer, $pageant, $validated['reason']));
            }
        } catch (\Exception $e) {
            Log::error('Error sending edit access request email: '.$e->getMessage());
            // Continue execution, don't fail the request just because email failed
        }

        return back()
            ->with('success', 'Your edit access request has been submitted. An administrator will review it soon.');
    }

    /**
     * Check if pageant meets requirements for status transition
     */
    private function checkStatusRequirements($pageant, $targetStatus, $skipScoreRequirement = false)
    {
        $result = ['passed' => true, 'message' => ''];

        switch ($targetStatus) {
            case 'Ongoing':
                // Requires contestants, rounds with criteria, and judges
                $hasContestants = $pageant->contestants()->count() > 0;
                $hasRounds = $pageant->rounds()->count() > 0;
                $hasCriteria = $pageant->criteria()->count() > 0;
                $hasJudges = $pageant->judges()->count() > 0;
                $meetsJudgeRequirement = ! $pageant->required_judges ||
                    $pageant->judges()->count() >= $pageant->required_judges;

                if (! $hasContestants) {
                    $result['passed'] = false;
                    $result['message'] = 'Pageant must have at least one contestant';
                } elseif (! $hasRounds) {
                    $result['passed'] = false;
                    $result['message'] = 'Pageant must have at least one round';
                } elseif (! $hasCriteria) {
                    $result['passed'] = false;
                    $result['message'] = 'Rounds must have scoring criteria';
                } elseif (! $hasJudges) {
                    $result['passed'] = false;
                    $result['message'] = 'Pageant must have at least one judge assigned';
                } elseif (! $meetsJudgeRequirement) {
                    $result['passed'] = false;
                    $result['message'] = "Pageant requires {$pageant->required_judges} judges, currently has {$pageant->judges()->count()}";
                }
                break;

            case 'Completed':
                // Should have scores submitted, unless we're auto-completing due to elapsed date
                if (! $skipScoreRequirement) {
                    $hasScores = Score::where('pageant_id', $pageant->id)->exists();
                    if (! $hasScores) {
                        $result['passed'] = false;
                        $result['message'] = 'No scores have been submitted for this pageant';
                    }
                }
                break;
        }

        return $result;
    }

    /**
     * Store a new contestant for a specific pageant
     */
    public function storeContestant(Request $request, $pageantId)
    {
        if (! Auth::user()->hasPermission('organizer_create_contestant')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage contestants.',
            ], 403);
        }

        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($pageantId);

        // Check if the pageant can be edited
        if (! $pageant->canBeEdited()) {
            $errorMessage = 'This pageant cannot be edited';

            if ($pageant->hasStartDateReached()) {
                $errorMessage = 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.';
            } elseif (! $pageant->isDraft()) {
                $errorMessage = 'This pageant cannot be edited in its current status';
            }

            abort(403, $errorMessage);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => [
                'required',
                'integer',
                Rule::unique('contestants')->where(function ($query) use ($pageantId, $request) {
                    return $query->where('pageant_id', $pageantId)
                        ->where('gender', $request->input('gender'))
                        ->where('is_pair', false);
                }),
            ],
            'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'age' => 'required|integer|min:16|max:35',
            'origin' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle legacy single photo upload if provided
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = 'contestant_'.$pageantId.'_'.time().'.'.$photo->getClientOriginalExtension();
            $photo->storeAs('public/contestants', $photoName);
            $photoPath = '/storage/contestants/'.$photoName;
        }

        $contestant = Contestant::create([
            'pageant_id' => $pageantId,
            'name' => $validated['name'],
            'number' => (int) $validated['number'],
            'gender' => $validated['gender'],
            'age' => $validated['age'],
            'origin' => $validated['origin'],
            'bio' => $validated['bio'],
            'photo' => $photoPath,
            'active' => true,
        ]);

        // Handle multiple image uploads if provided
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $displayOrder = 0;

            foreach ($images as $index => $image) {
                $imageName = 'contestant_'.$contestant->id.'_'.time().'_'.$index.'.'.$image->getClientOriginalExtension();
                $imagePath = $image->storeAs('contestants', $imageName, 'public');

                // Create contestant image record with explicit contestant_id
                $contestant->images()->create([
                    'contestant_id' => $contestant->id, // Explicit contestant ID
                    'image_path' => $imagePath,
                    'is_primary' => $index === 0, // First image is primary
                    'display_order' => $displayOrder++,
                ]);
            }
        }

        // Log the action
        $this->auditLogService->log(
            'CONTESTANT_CREATED',
            'Contestant',
            $contestant->id,
            "Created contestant '{$contestant->name}' for pageant '{$pageant->name}'"
        );

        return back()->with('success', 'Contestant added successfully');
    }

    /**
     * Update a contestant for a specific pageant
     */
    public function updateContestant(Request $request, $pageantId, $contestantId)
    {
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($pageantId);
        $contestant = Contestant::where('pageant_id', $pageantId)->findOrFail($contestantId);

        // Check if the pageant can be edited
        $this->ensurePageantCanBeEdited($pageant);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => [
                'required',
                'integer',
                Rule::unique('contestants')->where(function ($query) use ($pageantId, $request) {
                    return $query->where('pageant_id', $pageantId)
                        ->where('gender', $request->input('gender'))
                        ->where('is_pair', false);
                })->ignore($contestantId),
            ],
            'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'age' => 'required|integer|min:16|max:35',
            'origin' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'contestant_id' => 'nullable|integer', // For frontend validation
        ]);

        // Validate contestant ID if provided (extra security)
        if ($request->has('contestant_id') && $request->contestant_id != $contestantId) {
            Log::warning('Contestant ID mismatch in update request', [
                'expected' => $contestantId,
                'provided' => $request->contestant_id,
                'user_id' => Auth::id(),
            ]);
            abort(400, 'Contestant ID mismatch');
        }

        // Handle legacy single photo upload if provided
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = 'contestant_'.$pageantId.'_'.time().'.'.$photo->getClientOriginalExtension();
            $photo->storeAs('public/contestants', $photoName);
            $validated['photo'] = '/storage/contestants/'.$photoName;
        }

        // Remove contestant_id from validated data before update
        unset($validated['contestant_id']);

        $contestant->update($validated);

        // Handle multiple image uploads if provided
        if ($request->hasFile('images')) {
            $images = $request->file('images');

            // Get the current highest display order for this contestant
            $maxDisplayOrder = $contestant->images()
                ->where('contestant_id', $contestant->id)
                ->max('display_order') ?? -1;

            $displayOrder = $maxDisplayOrder + 1;

            foreach ($images as $index => $image) {
                $imageName = 'contestant_'.$contestant->id.'_'.time().'_'.$index.'.'.$image->getClientOriginalExtension();
                $imagePath = $image->storeAs('contestants', $imageName, 'public');

                // Determine if this should be primary (if no primary image exists)
                $isPrimary = $contestant->images()->where('contestant_id', $contestant->id)->where('is_primary', true)->count() === 0 && $index === 0;

                // Create contestant image record with explicit contestant_id
                $contestant->images()->create([
                    'contestant_id' => $contestant->id, // Explicit contestant ID
                    'image_path' => $imagePath,
                    'is_primary' => $isPrimary,
                    'display_order' => $displayOrder++,
                ]);
            }
        }

        // Log the action
        $this->auditLogService->log(
            'CONTESTANT_UPDATED',
            'Contestant',
            $contestant->id,
            "Updated contestant '{$contestant->name}' for pageant '{$pageant->name}'"
        );

        return back()->with('success', 'Contestant updated successfully');
    }

    /**
     * Remove a contestant from a specific pageant
     */
    public function removeContestant($pageantId, $contestantId)
    {
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to this pageant');
        }

        $pageant = Pageant::findOrFail($pageantId);
        $contestant = Contestant::where('pageant_id', $pageantId)->findOrFail($contestantId);

        // Check if the pageant can be edited
        $this->ensurePageantCanBeEdited($pageant);

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

        return back()->with('success', 'Contestant removed successfully');
    }

    /**
     * Check if a status transition is valid for organizers
     */
    private function isValidStatusTransition($fromStatus, $toStatus)
    {
        // Simplified transitions for new status system
        $allowedTransitions = [
            'Draft' => ['Ongoing'],
            'Ongoing' => ['Completed'],
            'Completed' => [], // Completed pageants cannot be reverted
        ];

        return isset($allowedTransitions[$fromStatus]) &&
               in_array($toStatus, $allowedTransitions[$fromStatus]);
    }

    /**
     * Check if pageant can be edited and abort with appropriate message if not
     */
    private function ensurePageantCanBeEdited($pageant, $abortMode = true)
    {
        if ($pageant->canBeEdited()) {
            return true;
        }

        $errorMessage = 'This pageant cannot be edited';

        if ($pageant->hasStartDateReached()) {
            $errorMessage = 'This pageant cannot be edited because the start date has been reached. Please contact an administrator for edit approval.';
        } elseif (! $pageant->isDraft()) {
            $errorMessage = 'This pageant cannot be edited in its current status';
        }

        if ($abortMode) {
            abort(403, $errorMessage);
        }

        return false;
    }

    /**
     * Create a new tabulator account for a specific pageant
     */
    public function createTabulator(Request $request, $pageantId)
    {
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            return back()->withErrors(['message' => 'You do not have access to this pageant.']);
        }

        $pageant = Pageant::findOrFail($pageantId);

        // Check if pageant already has a tabulator assigned
        if ($pageant->tabulators()->count() > 0) {
            return back()->withErrors(['message' => 'This pageant already has a tabulator assigned. Please remove the existing tabulator first.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'notes' => 'nullable|string',
        ]);

        // Create the tabulator account
        $tabulator = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'tabulator',
            'pageant_id' => $pageantId,
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Assign the tabulator to this pageant
        $pageant->tabulators()->attach($tabulator->id, [
            'active' => true,
            'notes' => $request->notes,
        ]);

        // Log the action
        $this->auditLogService->log(
            'TABULATOR_CREATED',
            'User',
            $tabulator->id,
            "Organizer '{$organizer->name}' created tabulator account '{$tabulator->name}' for pageant '{$pageant->name}'"
        );

        return back()->with('success', 'Tabulator account created successfully.');
    }

    /**
     * Update tabulator account
     */
    public function updateTabulator(Request $request, $pageantId, $tabulatorId)
    {
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            return back()->withErrors(['message' => 'You do not have access to this pageant.']);
        }

        $pageant = Pageant::findOrFail($pageantId);
        $tabulator = User::findOrFail($tabulatorId);

        // Verify tabulator is assigned to this pageant
        if (! $pageant->tabulators()->where('user_id', $tabulatorId)->exists()) {
            return back()->withErrors(['message' => 'Tabulator not found for this pageant.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|unique:users,username,'.$tabulatorId,
            'email' => 'nullable|email|max:255|unique:users,email,'.$tabulatorId,
            'password' => 'nullable|string|min:6',
            'notes' => 'nullable|string',
        ]);

        // Update tabulator account
        $updateData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        $tabulator->update($updateData);

        // Update pivot notes if provided
        if ($request->has('notes')) {
            $pageant->tabulators()->updateExistingPivot($tabulatorId, [
                'notes' => $request->notes,
            ]);
        }

        // Log the action
        $this->auditLogService->log(
            'TABULATOR_UPDATED',
            'User',
            $tabulator->id,
            "Organizer '{$organizer->name}' updated tabulator account '{$tabulator->name}' for pageant '{$pageant->name}'"
        );

        return back()->with('success', 'Tabulator account updated successfully.');
    }

    /**
     * Disable accounts (judges and tabulators) when pageant is completed
     */
    public function disablePageantAccounts($pageantId)
    {
        $organizer = Auth::user();

        // Check if this organizer has access to this pageant
        $hasAccess = DB::table('pageant_organizers')
            ->where('user_id', $organizer->id)
            ->where('pageant_id', $pageantId)
            ->exists();

        if (! $hasAccess) {
            return back()->withErrors(['message' => 'You do not have access to this pageant.']);
        }

        $pageant = Pageant::findOrFail($pageantId);

        // Only allow disabling accounts if pageant is completed
        if ($pageant->status !== 'Completed') {
            return back()->withErrors(['message' => 'Can only disable accounts for completed pageants.']);
        }

        // Disable all judges and tabulators linked to this pageant
        $disabledCount = User::where('pageant_id', $pageantId)
            ->whereIn('role', ['judge', 'tabulator'])
            ->update(['status' => 'inactive']);

        // Log the action
        $this->auditLogService->log(
            'PAGEANT_ACCOUNTS_DISABLED',
            'Pageant',
            $pageant->id,
            "Organizer '{$organizer->name}' disabled {$disabledCount} accounts for completed pageant '{$pageant->name}'"
        );

        return back()->with('success', "Successfully disabled {$disabledCount} account(s) for this pageant.");
    }
}
