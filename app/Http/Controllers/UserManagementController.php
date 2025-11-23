<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Display organizers list
     */
    public function organizers()
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->back()->with('error', 'You do not have permission to manage users.');
        }

        $organizers = User::where('role', 'organizer')
            ->withCount('pageants')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($organizer) {
                return [
                    'id' => $organizer->id,
                    'name' => $organizer->name,
                    'username' => $organizer->username,
                    'email' => $organizer->email,
                    'status' => $organizer->is_active ? 'Active' : 'Inactive',
                    'email_verified' => $organizer->email_verified_at ? true : false,
                    'created_at' => $organizer->created_at->format('M d, Y'),
                    'pageants_count' => $organizer->pageants_count,
                ];
            });

        return Inertia::render('Admin/Users/Organizers', [
            'organizers' => $organizers,
        ]);
    }

    /**
     * Display organizer creation form
     */
    public function createOrganizer()
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to manage users.');
        }

        return Inertia::render('Admin/Users/CreateOrganizer');
    }

    /**
     * Store a new organizer
     */
    public function storeOrganizer(Request $request)
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->back()->with('error', 'You do not have permission to manage users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|max:50|unique:users',
        ]);

        // Generate username if not provided
        if (empty($validated['username'])) {
            $baseUsername = Str::slug($validated['name']);
            $username = $baseUsername;
            $counter = 1;

            // Make sure username is unique
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername.$counter;
                $counter++;
            }

            $validated['username'] = $username;
        }

        // Create the organizer user
        $organizer = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Str::random(16),
            'role' => 'organizer',
            'is_active' => true,
        ]);

        // Log the action
        $this->auditLogService->log(
            'CREATE_ORGANIZER',
            'User',
            $organizer->id,
            "Created new organizer user: {$organizer->name}"
        );

        // Send verification email
        $organizer->sendVerificationEmail();

        return redirect()->route('admin.users.organizers')->with('success', "Organizer {$organizer->name} created successfully. A verification email has been sent.");
    }

    /**
     * Display specific organizer details
     */
    public function showOrganizer($id)
    {
        $organizer = User::with('pageants')
            ->findOrFail($id);

        $organizerData = [
            'id' => $organizer->id,
            'name' => $organizer->name,
            'username' => $organizer->username,
            'email' => $organizer->email,
            'is_active' => $organizer->is_active,
            'email_verified_at' => $organizer->email_verified_at,
            'created_at' => $organizer->created_at->format('M d, Y'),
            'pageants' => $organizer->pageants->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                ];
            }),
        ];

        return Inertia::render('Admin/Users/ShowOrganizer', [
            'organizer' => $organizerData,
        ]);
    }

    /**
     * Display organizer edit form
     */
    public function editOrganizer($id)
    {
        $organizer = User::findOrFail($id);

        $organizerData = [
            'id' => $organizer->id,
            'name' => $organizer->name,
            'username' => $organizer->username,
            'email' => $organizer->email,
            'is_active' => $organizer->is_active,
        ];

        return Inertia::render('Admin/Users/EditOrganizer', [
            'organizer' => $organizerData,
        ]);
    }

    /**
     * Update organizer details
     */
    public function updateOrganizer(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->back()->with('error', 'You do not have permission to manage users.');
        }

        $organizer = User::findOrFail($id);

        // Check if username is null or empty and generate one if needed
        if ($request->has('username') && (is_null($request->username) || empty(trim($request->username)))) {
            // Generate a username based on name
            $baseUsername = Str::slug($request->name);
            $username = $baseUsername;
            $counter = 1;

            // Make sure username is unique
            while (User::where('username', $username)->where('id', '!=', $id)->exists()) {
                $username = $baseUsername.$counter;
                $counter++;
            }

            // Set the generated username in the request
            $request->merge(['username' => $username]);

            // Log the username generation
            Log::info("Generated username '{$username}' for organizer ID {$id} during update");
        }

        // Check if this is a status-only update (toggle)
        $isStatusToggle = $request->has('is_active') && count($request->only(['name', 'email', 'username', 'is_active'])) === 4;

        if ($isStatusToggle) {
            // Only validate required fields for status toggle
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'username' => 'required|string|max:50|unique:users,username,'.$id,
                'is_active' => 'required|boolean',
            ]);

            // Update only the changed fields
            $organizer->fill($validated);
            $organizer->save();

            // Log the status change specifically
            $this->auditLogService->log(
                'UPDATE_ORGANIZER_STATUS',
                'User',
                $organizer->id,
                "Updated organizer status: {$organizer->name}. Status changed to: ".($organizer->is_active ? 'Active' : 'Inactive')
            );

            return back()->with('success', "Organizer {$organizer->name}'s status updated successfully.");
        } else {
            // Full update with all validations
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'username' => 'required|string|max:50|unique:users,username,'.$id,
                'is_active' => 'required|boolean',
            ]);

            // Update only the changed fields
            $organizer->fill($validated);
            $organizer->save();

            // Log the action
            $this->auditLogService->log(
                'UPDATE_ORGANIZER',
                'User',
                $organizer->id,
                "Updated organizer user: {$organizer->name}. Status: ".($organizer->is_active ? 'Active' : 'Inactive')
            );

            return back()->with('success', "Organizer {$organizer->name} updated successfully.");
        }
    }

    /**
     * Delete an organizer
     */
    public function deleteOrganizer($id)
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->back()->with('error', 'You do not have permission to manage users.');
        }

        $organizer = User::findOrFail($id);
        $name = $organizer->name;

        // Check if organizer has pageants
        if ($organizer->pageants()->count() > 0) {
            return back()->with('error', "Cannot delete organizer {$name} because they have associated pageants.");
        }

        // Log the action before deletion
        $this->auditLogService->log(
            'DELETE_ORGANIZER',
            'User',
            $organizer->id,
            "Deleted organizer user: {$name}"
        );

        $organizer->delete();

        return back()->with('success', "Organizer {$name} deleted successfully.");
    }

    /**
     * Resend verification email to organizer
     */
    public function resendVerificationEmail($id)
    {
        $organizer = User::findOrFail($id);

        if ($organizer->email_verified_at) {
            return back()->with('warning', "Email for {$organizer->name} is already verified.");
        }

        try {
            // Send the verification email
            $organizer->sendVerificationEmail();

            // Log the action
            $this->auditLogService->log(
                'RESEND_VERIFICATION',
                'User',
                $organizer->id,
                "Resent verification email to organizer: {$organizer->name}"
            );

            return back()->with('success', "Verification email has been resent to {$organizer->name}.");
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: '.$e->getMessage());

            return back()->with('error', 'Failed to send verification email. Please try again later.');
        }
    }

    /**
     * Display administrators list
     */
    public function admins()
    {
        $admins = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'username' => $admin->username,
                    'email' => $admin->email,
                    'status' => $admin->is_active ? 'Active' : 'Inactive',
                    'created_at' => $admin->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('Admin/Users/Admins', [
            'admins' => $admins,
        ]);
    }

    /**
     * Display admin creation form
     */
    public function createAdmin()
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to manage users.');
        }

        return Inertia::render('Admin/Users/CreateAdmin');
    }

    /**
     * Store a new admin
     */
    public function storeAdmin(Request $request)
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->back()->with('error', 'You do not have permission to manage users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the admin user
        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(), // Admins are pre-verified
        ]);

        // Log the action
        $this->auditLogService->log(
            'CREATE_ADMIN',
            'User',
            $admin->id,
            "Created new admin user: {$admin->name}"
        );

        return redirect()->route('admin.users.admins')->with('success', "Administrator {$admin->name} created successfully.");
    }

    /**
     * Display specific admin details
     */
    public function showAdmin($id)
    {
        $admin = User::findOrFail($id);

        // Don't show current user - prevent self-modification
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.users.admins')->with('warning', 'You cannot modify your own account through this interface.');
        }

        $adminData = [
            'id' => $admin->id,
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'is_active' => $admin->is_active,
            'created_at' => $admin->created_at->format('M d, Y'),
        ];

        return Inertia::render('Admin/Users/ShowAdmin', [
            'admin' => $adminData,
        ]);
    }

    /**
     * Display admin edit form
     */
    public function editAdmin($id)
    {
        $admin = User::findOrFail($id);

        // Don't allow editing current user - prevent self-modification
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.users.admins')->with('warning', 'You cannot modify your own account through this interface.');
        }

        $adminData = [
            'id' => $admin->id,
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'is_active' => $admin->is_active,
        ];

        return Inertia::render('Admin/Users/EditAdmin', [
            'admin' => $adminData,
        ]);
    }

    /**
     * Update admin details
     */
    public function updateAdmin(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->back()->with('error', 'You do not have permission to manage users.');
        }

        $admin = User::findOrFail($id);

        // Don't update current user - prevent self-modification
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.users.admins')->with('error', 'You cannot modify your own account through this interface.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'username' => 'required|string|max:50|unique:users,username,'.$id,
            'is_active' => 'required|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Only update password if provided
        if (! isset($validated['password'])) {
            unset($validated['password']);
        }

        $admin->update($validated);

        // Log the action
        $this->auditLogService->log(
            'UPDATE_ADMIN',
            'User',
            $admin->id,
            "Updated admin user: {$admin->name}"
        );

        return redirect()->route('admin.users.admins.show', $id)->with('success', "Administrator {$admin->name} updated successfully.");
    }

    /**
     * Delete an admin
     */
    public function deleteAdmin($id)
    {
        if (! Auth::user()->hasPermission('admin_manage_users')) {
            return redirect()->back()->with('error', 'You do not have permission to manage users.');
        }

        $admin = User::findOrFail($id);

        // Don't delete current user - prevent self-deletion
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.users.admins')->with('error', 'You cannot delete your own account.');
        }

        $name = $admin->name;

        // Log the action before deletion
        $this->auditLogService->log(
            'DELETE_ADMIN',
            'User',
            $admin->id,
            "Deleted admin user: {$name}"
        );

        $admin->delete();

        return redirect()->route('admin.users.admins')->with('success', "Administrator {$name} deleted successfully.");
    }

    /**
     * Display tabulators list (read-only)
     */
    public function tabulators()
    {
        try {
            // Debug current user and auth state
            $currentUser = Auth::user();
            Log::info('Current user accessing tabulators:', [
                'user_id' => $currentUser?->id,
                'user_role' => $currentUser?->role,
                'is_authenticated' => Auth::check(),
            ]);

            $tabulators = User::where('role', 'tabulator')
                ->withCount('tabulatorPageants as pageants_count')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($tabulator) {
                    return [
                        'id' => $tabulator->id,
                        'name' => $tabulator->name,
                        'username' => $tabulator->username,
                        'email' => $tabulator->email,
                        'status' => $tabulator->is_active ? 'Active' : 'Inactive',
                        'created_at' => $tabulator->created_at->format('M d, Y'),
                        'pageants_count' => $tabulator->pageants_count ?? 0,
                    ];
                });

            Log::info('Tabulators data prepared:', [
                'count' => $tabulators->count(),
                'sample' => $tabulators->take(2)->toArray(),
            ]);

            $data = [
                'tabulators' => $tabulators->toArray(),
            ];

            Log::info('Final data being passed to Inertia:', $data);

            return Inertia::render('Admin/Users/Tabulators', $data);
        } catch (\Exception $e) {
            Log::error('Error in tabulators method: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return Inertia::render('Admin/Users/Tabulators', [
                'tabulators' => [],
            ]);
        }
    }

    /**
     * Display specific tabulator details
     */
    public function showTabulator($id)
    {
        $tabulator = User::with('tabulatorPageants')
            ->findOrFail($id);

        $tabulatorData = [
            'id' => $tabulator->id,
            'name' => $tabulator->name,
            'username' => $tabulator->username,
            'email' => $tabulator->email,
            'is_active' => $tabulator->is_active,
            'created_at' => $tabulator->created_at->format('M d, Y'),
            'pageants' => $tabulator->tabulatorPageants->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                ];
            }),
        ];

        return Inertia::render('Admin/Users/ShowTabulator', [
            'tabulator' => $tabulatorData,
        ]);
    }

    /**
     * Display tabulator edit form
     */
    public function editTabulator($id)
    {
        $tabulator = User::findOrFail($id);

        $tabulatorData = [
            'id' => $tabulator->id,
            'name' => $tabulator->name,
            'username' => $tabulator->username,
            'email' => $tabulator->email,
            'is_active' => $tabulator->is_active,
        ];

        return Inertia::render('Admin/Users/EditTabulator', [
            'tabulator' => $tabulatorData,
        ]);
    }

    /**
     * Update tabulator details
     */
    public function updateTabulator(Request $request, $id)
    {
        $tabulator = User::findOrFail($id);

        // Check if username is null or empty and generate one if needed
        if ($request->has('username') && (is_null($request->username) || empty(trim($request->username)))) {
            // Generate a username based on name
            $baseUsername = Str::slug($request->name);
            $username = $baseUsername;
            $counter = 1;

            // Make sure username is unique
            while (User::where('username', $username)->where('id', '!=', $id)->exists()) {
                $username = $baseUsername.$counter;
                $counter++;
            }

            // Set the generated username in the request
            $request->merge(['username' => $username]);

            // Log the username generation
            Log::info("Generated username '{$username}' for tabulator ID {$id} during update");
        }

        // Check if this is a status-only update (toggle)
        $isStatusToggle = $request->has('is_active') && count($request->only(['name', 'email', 'username', 'is_active'])) === 4;

        if ($isStatusToggle) {
            // Only validate required fields for status toggle
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'username' => 'required|string|max:50|unique:users,username,'.$id,
                'is_active' => 'required|boolean',
            ]);

            // Update only the changed fields
            $tabulator->fill($validated);
            $tabulator->save();

            // Log the status change specifically
            $this->auditLogService->log(
                'UPDATE_TABULATOR_STATUS',
                'User',
                $tabulator->id,
                "Updated tabulator status: {$tabulator->name}. Status changed to: ".($tabulator->is_active ? 'Active' : 'Inactive')
            );

            return back()->with('success', "Tabulator {$tabulator->name}'s status updated successfully.");
        } else {
            // Full update with all validations
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'username' => 'required|string|max:50|unique:users,username,'.$id,
                'is_active' => 'required|boolean',
            ]);

            // Update only the changed fields
            $tabulator->fill($validated);
            $tabulator->save();

            // Log the action
            $this->auditLogService->log(
                'UPDATE_TABULATOR',
                'User',
                $tabulator->id,
                "Updated tabulator user: {$tabulator->name}. Status: ".($tabulator->is_active ? 'Active' : 'Inactive')
            );

            return redirect()->route('admin.users.tabulators.show', $id)->with('success', "Tabulator {$tabulator->name} updated successfully.");
        }
    }

    /**
     * Display judges list (read-only)
     */
    public function judges()
    {
        try {
            // Debug current user and auth state
            $currentUser = Auth::user();
            Log::info('Current user accessing judges:', [
                'user_id' => $currentUser?->id,
                'user_role' => $currentUser?->role,
                'is_authenticated' => Auth::check(),
            ]);

            $judges = User::where('role', 'judge')
                ->withCount('judgePageants as pageants_count')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($judge) {
                    return [
                        'id' => $judge->id,
                        'name' => $judge->name,
                        'username' => $judge->username,
                        'email' => $judge->email,
                        'status' => $judge->is_active ? 'Active' : 'Inactive',
                        'created_at' => $judge->created_at->format('M d, Y'),
                        'pageants_count' => $judge->pageants_count ?? 0,
                    ];
                });

            Log::info('Judges data prepared:', [
                'count' => $judges->count(),
                'sample' => $judges->take(2)->toArray(),
            ]);

            $data = [
                'judges' => $judges->toArray(),
            ];

            Log::info('Final data being passed to Inertia:', $data);

            return Inertia::render('Admin/Users/Judges', $data);
        } catch (\Exception $e) {
            Log::error('Error in judges method: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return Inertia::render('Admin/Users/Judges', [
                'judges' => [],
            ]);
        }
    }

    /**
     * Display specific judge details
     */
    public function showJudge($id)
    {
        $judge = User::with('judgePageants')
            ->findOrFail($id);

        $judgeData = [
            'id' => $judge->id,
            'name' => $judge->name,
            'username' => $judge->username,
            'email' => $judge->email,
            'is_active' => $judge->is_active,
            'created_at' => $judge->created_at->format('M d, Y'),
            'pageants' => $judge->judgePageants->map(function ($pageant) {
                return [
                    'id' => $pageant->id,
                    'name' => $pageant->name,
                    'status' => $pageant->status,
                    'start_date' => $pageant->start_date?->format('M d, Y'),
                ];
            }),
        ];

        return Inertia::render('Admin/Users/ShowJudge', [
            'judge' => $judgeData,
        ]);
    }

    /**
     * Display judge edit form
     */
    public function editJudge($id)
    {
        $judge = User::findOrFail($id);

        $judgeData = [
            'id' => $judge->id,
            'name' => $judge->name,
            'username' => $judge->username,
            'email' => $judge->email,
            'is_active' => $judge->is_active,
        ];

        return Inertia::render('Admin/Users/EditJudge', [
            'judge' => $judgeData,
        ]);
    }

    /**
     * Update judge details
     */
    public function updateJudge(Request $request, $id)
    {
        $judge = User::findOrFail($id);

        // Check if username is null or empty and generate one if needed
        if ($request->has('username') && (is_null($request->username) || empty(trim($request->username)))) {
            // Generate a username based on name
            $baseUsername = Str::slug($request->name);
            $username = $baseUsername;
            $counter = 1;

            // Make sure username is unique
            while (User::where('username', $username)->where('id', '!=', $id)->exists()) {
                $username = $baseUsername.$counter;
                $counter++;
            }

            // Set the generated username in the request
            $request->merge(['username' => $username]);

            // Log the username generation
            Log::info("Generated username '{$username}' for judge ID {$id} during update");
        }

        // Check if this is a status-only update (toggle)
        $isStatusToggle = $request->has('is_active') && count($request->only(['name', 'email', 'username', 'is_active'])) === 4;

        if ($isStatusToggle) {
            // Only validate required fields for status toggle
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'username' => 'required|string|max:50|unique:users,username,'.$id,
                'is_active' => 'required|boolean',
            ]);

            // Update only the changed fields
            $judge->fill($validated);
            $judge->save();

            // Log the status change specifically
            $this->auditLogService->log(
                'UPDATE_JUDGE_STATUS',
                'User',
                $judge->id,
                "Updated judge status: {$judge->name}. Status changed to: ".($judge->is_active ? 'Active' : 'Inactive')
            );

            return back()->with('success', "Judge {$judge->name}'s status updated successfully.");
        } else {
            // Full update with all validations
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'username' => 'required|string|max:50|unique:users,username,'.$id,
                'is_active' => 'required|boolean',
            ]);

            // Update only the changed fields
            $judge->fill($validated);
            $judge->save();

            // Log the action
            $this->auditLogService->log(
                'UPDATE_JUDGE',
                'User',
                $judge->id,
                "Updated judge user: {$judge->name}. Status: ".($judge->is_active ? 'Active' : 'Inactive')
            );

            return redirect()->route('admin.users.judges.show', $id)->with('success', "Judge {$judge->name} updated successfully.");
        }
    }

    /**
     * Display user permissions management interface
     */
    public function permissions()
    {
        if (! Auth::user()->hasPermission('admin_grant_permissions')) {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to manage permissions.');
        }

        // Get all permissions from database, grouped by role
        $adminPermissions = \App\Models\RolePermission::where('role', 'admin')->get();
        $organizerPermissions = \App\Models\RolePermission::where('role', 'organizer')->get();
        $tabulatorPermissions = \App\Models\RolePermission::where('role', 'tabulator')->get();
        $judgePermissions = \App\Models\RolePermission::where('role', 'judge')->get();

        return Inertia::render('Admin/Users/Permissions', [
            'adminPermissions' => $adminPermissions->map(function ($perm) {
                return [
                    'id' => $perm->permission_key,
                    'name' => $perm->permission_name,
                    'granted' => $perm->granted,
                ];
            }),
            'organizerPermissions' => $organizerPermissions->map(function ($perm) {
                return [
                    'id' => $perm->permission_key,
                    'name' => $perm->permission_name,
                    'granted' => $perm->granted,
                ];
            }),
            'tabulatorPermissions' => $tabulatorPermissions->map(function ($perm) {
                return [
                    'id' => $perm->permission_key,
                    'name' => $perm->permission_name,
                    'granted' => $perm->granted,
                ];
            }),
            'judgePermissions' => $judgePermissions->map(function ($perm) {
                return [
                    'id' => $perm->permission_key,
                    'name' => $perm->permission_name,
                    'granted' => $perm->granted,
                ];
            }),
        ]);
    }

    /**
     * Update user permissions for a role
     */
    public function updatePermissions(Request $request, $role)
    {
        if (! Auth::user()->hasPermission('admin_grant_permissions')) {
            return redirect()->back()->with('error', 'You do not have permission to manage permissions.');
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.id' => 'required|string',
            'permissions.*.name' => 'required|string',
            'permissions.*.granted' => 'required|boolean',
        ]);

        try {
            // Update permissions in database
            \App\Models\RolePermission::updateRolePermissions($role, $validated['permissions']);

            // Log the action
            $this->auditLogService->log(
                'UPDATE_PERMISSIONS',
                'Role',
                null,
                "Updated permissions for role: {$role}"
            );

            return back()->with('success', ucfirst($role).' permissions updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update permissions: '.$e->getMessage());

            return back()->with('error', 'Failed to update permissions. Please try again.'.$e->getMessage());
        }
    }
}
