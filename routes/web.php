<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JudgeController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\TabulatorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ContestantController;

use App\Http\Controllers\CriteriaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public verification routes (no auth required)
Route::get('/verify-organizer/{token}', [OrganizerController::class, 'verify'])->name('verify.organizer');

// Organizer Routes
Route::middleware(['auth', 'verified'])->prefix('organizer')->group(function () {
    Route::get('/dashboard', [OrganizerController::class, 'dashboard'])->name('organizer.dashboard');
    Route::get('/criteria', [OrganizerController::class, 'criteria'])->name('organizer.criteria');
    Route::get('/contestants', [OrganizerController::class, 'contestants'])->name('organizer.contestants');
    Route::get('/scoring', [OrganizerController::class, 'scoring'])->name('organizer.scoring');
    Route::get('/my-pageants', [OrganizerController::class, 'myPageants'])->name('organizer.my-pageants');
    
    // Pageant creation routes
    Route::get('/pageants/create', [OrganizerController::class, 'createPageant'])->name('organizer.pageants.create');
    Route::post('/pageants/create', [OrganizerController::class, 'storePageant'])->name('organizer.pageants.store');
    Route::get('/timeline', [OrganizerController::class, 'timeline'])->name('organizer.timeline');
    Route::get('/pageant/{id}/timeline', [OrganizerController::class, 'pageantTimeline'])->name('organizer.pageant.timeline');
    Route::get('/pageant/{id}', [OrganizerController::class, 'viewPageant'])->name('organizer.pageant.view');
    Route::get('/pageant/{id}/edit', [OrganizerController::class, 'editPageant'])->name('organizer.pageant.edit');
    Route::put('/pageant/{id}', [OrganizerController::class, 'updatePageant'])->name('organizer.pageant.update');
    Route::post('/pageant/{id}', [OrganizerController::class, 'updatePageant']);
    
    // Judge and Tabulator Management Routes
    Route::put('/pageant/{id}/required-judges', [OrganizerController::class, 'updateRequiredJudges'])->name('organizer.pageant.required-judges.update');
    Route::put('/pageant/{id}/scoring-system', [OrganizerController::class, 'updateScoringSystem'])->name('organizer.pageant.scoring-system.update');
    Route::post('/pageant/{id}/tabulators', [OrganizerController::class, 'assignTabulator'])->name('organizer.pageant.tabulators.assign');
    
    // Pageant status and locking routes
    Route::post('/pageant/{id}/toggle-status', [OrganizerController::class, 'togglePageantStatus'])->name('organizer.pageant.toggle-status');
    Route::put('/pageant/{id}/status', [OrganizerController::class, 'updatePageantStatus'])->name('organizer.pageant.status.update');
    Route::post('/pageant/{id}/lock', [OrganizerController::class, 'lockPageant'])->name('organizer.pageant.lock');
    Route::post('/pageant/{id}/unlock', [OrganizerController::class, 'unlockPageant'])->name('organizer.pageant.unlock');
    Route::delete('/pageant/{id}/tabulators/{tabulatorId}', [OrganizerController::class, 'removeTabulator'])->name('organizer.pageant.tabulators.remove');
    
    // Criteria routes
    Route::get('/pageant/{pageantId}/criteria', [CriteriaController::class, 'index'])->name('organizer.pageant.criteria.index');
    Route::post('/pageant/{pageantId}/criteria', [CriteriaController::class, 'store'])->name('organizer.pageant.criteria.store');
    Route::put('/pageant/{pageantId}/criteria/{criterionId}', [CriteriaController::class, 'update'])->name('organizer.pageant.criteria.update');
    Route::delete('/pageant/{pageantId}/criteria/{criterionId}', [CriteriaController::class, 'destroy'])->name('organizer.pageant.criteria.destroy');
    
    // Contestants routes
    Route::get('/pageant/{pageantId}/contestants', [ContestantController::class, 'index'])->name('organizer.pageant.contestants.index');
    Route::post('/pageant/{pageantId}/contestants', [ContestantController::class, 'store'])->name('organizer.pageant.contestants.store');
    Route::get('/pageant/{pageantId}/contestants/{contestantId}', [ContestantController::class, 'show'])->name('organizer.pageant.contestants.show');
    Route::put('/pageant/{pageantId}/contestants/{contestantId}', [ContestantController::class, 'update'])->name('organizer.pageant.contestants.update');
    Route::delete('/pageant/{pageantId}/contestants/{contestantId}', [ContestantController::class, 'destroy'])->name('organizer.pageant.contestants.destroy');
    
    Route::get('/pageant/{id}/contestants-management', [OrganizerController::class, 'pageantContestants'])->name('organizer.pageant.contestants-management');
    Route::get('/pageant/{id}/criteria-management', [OrganizerController::class, 'pageantCriteria'])->name('organizer.pageant.criteria-management');
    Route::get('/pageant/{id}/judges-management', [OrganizerController::class, 'pageantJudges'])->name('organizer.pageant.judges-management');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'check_role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('admin.audit_log');
    
    // User Management Routes
    Route::prefix('users')->group(function () {
        // Organizer management
        Route::get('/organizers', [UserManagementController::class, 'organizers'])->name('admin.users.organizers');
        Route::get('/organizers/create', [UserManagementController::class, 'createOrganizer'])->name('admin.users.organizers.create');
        Route::post('/organizers', [UserManagementController::class, 'storeOrganizer'])->name('admin.users.organizers.store');
        Route::get('/organizers/{id}', [UserManagementController::class, 'showOrganizer'])->name('admin.users.organizers.show');
        Route::get('/organizers/{id}/edit', [UserManagementController::class, 'editOrganizer'])->name('admin.users.organizers.edit');
        Route::put('/organizers/{id}', [UserManagementController::class, 'updateOrganizer'])->name('admin.users.organizers.update');
        Route::delete('/organizers/{id}', [UserManagementController::class, 'deleteOrganizer'])->name('admin.users.organizers.delete');
        Route::post('/organizers/{id}/resend-verification', [UserManagementController::class, 'resendVerificationEmail'])->name('admin.users.organizers.resend-verification');
        
        // Admin management
        Route::get('/admins', [UserManagementController::class, 'admins'])->name('admin.users.admins');
        Route::get('/admins/create', [UserManagementController::class, 'createAdmin'])->name('admin.users.admins.create');
        Route::post('/admins', [UserManagementController::class, 'storeAdmin'])->name('admin.users.admins.store');
        Route::get('/admins/{id}', [UserManagementController::class, 'showAdmin'])->name('admin.users.admins.show');
        Route::get('/admins/{id}/edit', [UserManagementController::class, 'editAdmin'])->name('admin.users.admins.edit');
        Route::put('/admins/{id}', [UserManagementController::class, 'updateAdmin'])->name('admin.users.admins.update');
        Route::delete('/admins/{id}', [UserManagementController::class, 'deleteAdmin'])->name('admin.users.admins.delete');
        
        // Tabulator management
        Route::get('/tabulators', [UserManagementController::class, 'tabulators'])->name('admin.users.tabulators');
        Route::get('/tabulators/{id}', [UserManagementController::class, 'showTabulator'])->name('admin.users.tabulators.show');
        
        // Judge management
        Route::get('/judges', [UserManagementController::class, 'judges'])->name('admin.users.judges');
        Route::get('/judges/{id}', [UserManagementController::class, 'showJudge'])->name('admin.users.judges.show');
        
        // User permissions
        Route::get('/permissions', [UserManagementController::class, 'permissions'])->name('admin.users.permissions');
        Route::put('/permissions/{id}', [UserManagementController::class, 'updatePermissions'])->name('admin.users.permissions.update');
    });
    
    // Organizer management routes (legacy, maintain for backward compatibility)
    Route::post('/check-username', [OrganizerController::class, 'checkUsername'])->name('admin.check-username');
    Route::post('/organizers', [OrganizerController::class, 'store'])->name('admin.organizers.store');
    
    // Pageant routes
    Route::prefix('pageants')->group(function () {
        Route::get('/create', [AdminController::class, 'createPageant'])->name('admin.pageants.create');
        Route::post('/create', [AdminController::class, 'storePageant'])->name('admin.pageants.store');
        
        // Approval routes
        Route::get('/pending-approvals', [AdminController::class, 'pendingApprovals'])->name('admin.pageants.pending-approvals');
        Route::post('/{id}/approve', [AdminController::class, 'approvePageant'])->name('admin.pageants.approve');
        Route::post('/{id}/reject', [AdminController::class, 'rejectPageant'])->name('admin.pageants.reject');
        
        Route::get('/', [AdminController::class, 'allPageants'])->name('admin.pageants.index');
        
        Route::get('/previous', [AdminController::class, 'previousPageants'])->name('admin.pageants.previous');
        Route::get('/previous/{id}', [AdminController::class, 'ongoingPageantDetail'])->name('admin.pageants.previous.detail');
        Route::post('/previous/{id}/grant-edit-permission', [AdminController::class, 'grantEditPermission'])->name('admin.pageants.grant_edit_permission');
        Route::delete('/previous/{id}/revoke-edit-permission', [AdminController::class, 'revokeEditPermission'])->name('admin.pageants.revoke_edit_permission');
        
        Route::get('/archived', [AdminController::class, 'archivedPageants'])->name('admin.pageants.archived');
        Route::get('/archived/{id}', [AdminController::class, 'archivedPageantDetail'])->name('admin.pageants.archived.detail');
        
        // These routes should be last to avoid conflicts with the named routes above
        Route::get('/{id}', [AdminController::class, 'ongoingPageantDetail'])->name('admin.pageants.detail');
        Route::put('/{id}', [AdminController::class, 'updatePageant'])->name('admin.pageants.update');
        Route::patch('/{id}/status', [AdminController::class, 'updatePageantStatus'])->name('admin.pageants.update_status');
    });
});

// Judge Routes
Route::prefix('judge')->group(function () {
    Route::get('/scoring', [JudgeController::class, 'scoring'])->name('judge.scoring');
});


// Tabulator Routes
Route::prefix('tabulator')->group(function () {
    Route::get('/dashboard', [TabulatorController::class, 'dashboard'])->name('tabulator.dashboard');
    Route::get('/judges', [TabulatorController::class, 'judges'])->name('tabulator.judges');
    Route::get('/scores', [TabulatorController::class, 'scores'])->name('tabulator.scores');
    Route::get('/results', [TabulatorController::class, 'results'])->name('tabulator.results');
    Route::get('/print', [TabulatorController::class, 'print'])->name('tabulator.print');
});
