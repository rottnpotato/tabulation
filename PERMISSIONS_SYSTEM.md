# Role-Based Permissions System

## Overview

This application implements a comprehensive role-based permissions system that allows fine-grained control over what actions different user roles can perform.

## Database Structure

### Role Permissions Table

The `role_permissions` table stores all permissions for each role:

- `id`: Primary key
- `role`: User role (admin, organizer, tabulator, judge)
- `permission_key`: Unique identifier for the permission (e.g., 'organizer_edit_own_pageant')
- `permission_name`: Human-readable name
- `permission_description`: Optional description
- `granted`: Boolean indicating if permission is granted
- `timestamps`: Created/updated timestamps

## Seeded Permissions

### Administrator Permissions (All granted by default)
- `admin_create_pageant` - Create Pageants
- `admin_edit_pageant` - Edit Pageants
- `admin_delete_pageant` - Delete Pageants
- `admin_manage_users` - Manage All Users
- `admin_view_audit_log` - View Audit Logs
- `admin_system_settings` - Configure System Settings
- `admin_grant_permissions` - Grant/Revoke Permissions
- `admin_view_reports` - Access All Reports

### Organizer Permissions
- `organizer_edit_own_pageant` - Edit Own Pageants (granted)
- `organizer_create_contestant` - Create & Edit Contestants (granted)
- `organizer_manage_judges` - Assign Judges (granted)
- `organizer_manage_criteria` - Configure Criteria & Scoring (granted)
- `organizer_view_results` - View Results & Reports (granted)
- `organizer_publish_results` - Publish Final Results (granted)
- `organizer_export_data` - Export Pageant Data (granted)
- `organizer_assign_tabulators` - Assign Tabulators (granted)

### Tabulator Permissions
- `tabulator_view_judges` - View Judge Information (granted)
- `tabulator_view_scores` - View Individual Scores (granted)
- `tabulator_tabulate_results` - Tabulate & Verify Results (granted)
- `tabulator_print_reports` - Generate Score Reports (granted)
- `tabulator_view_contestants` - View Contestant Details (granted)
- `tabulator_edit_scores` - Edit Submitted Scores (NOT granted by default)
- `tabulator_export_data` - Export Score Data (granted)
- `tabulator_publish_results` - Publish Results (NOT granted by default)

### Judge Permissions
- `judge_view_criteria` - View Scoring Criteria (granted)
- `judge_submit_scores` - Submit Scores (granted)
- `judge_edit_own_scores` - Edit Own Submitted Scores (granted)
- `judge_view_contestants` - View Contestant Profiles (granted)
- `judge_view_other_judges` - View Other Judges Profiles (NOT granted by default)
- `judge_view_results` - View Results (NOT granted by default)
- `judge_export_scores` - Export Own Scores (NOT granted by default)

## Usage

### Checking Permissions in Controllers

The permission system is actively used in controllers to protect sensitive actions:

```php
// Example from UserManagementController
public function storeOrganizer(Request $request)
{
    if (! Auth::user()->hasPermission('admin_manage_users')) {
        abort(403, 'You do not have permission to manage users.');
    }
    
    // Continue with the action...
}

// Example from OrganizerController
public function updatePageant(UpdatePageantRequest $request, $id)
{
    if (! Auth::user()->hasPermission('organizer_edit_own_pageant')) {
        abort(403, 'You do not have permission to edit pageants.');
    }
    
    // Continue with the action...
}

// Example from JudgeController
public function submitScores(Request $request, $pageantId, $roundId)
{
    if (! Auth::user()->hasPermission('judge_submit_scores')) {
        abort(403, 'You do not have permission to submit scores.');
    }
    
    // Continue with the action...
}
```

**Controllers with Permission Checks:**
- `UserManagementController` - All user management actions
- `OrganizerController` - Pageant creation/editing, contestant management, criteria management
- `JudgeController` - Score submission
- `TabulatorController` - Viewing judges, locking/unlocking rounds

### Checking Permissions Programmatically

```php
// Check if user has a specific permission
if (auth()->user()->hasPermission('organizer_edit_own_pageant')) {
    // User has permission
}

// Get all permissions for current user's role
$permissions = auth()->user()->permissions();
```

### Using Permission Middleware in Routes

```php
// Protect a route with permission middleware
Route::get('/pageants/create', [PageantController::class, 'create'])
    ->middleware(['auth', 'permission:organizer_create_pageant']);
```

### Checking Permissions in Blade Templates

```blade
@if(auth()->user()->hasPermission('organizer_edit_own_pageant'))
    <a href="{{ route('pageants.edit', $pageant) }}">Edit Pageant</a>
@endif
```

### Checking Permissions in Vue Components

```vue
<template>
  <button v-if="canEditPageant">Edit</button>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const canEditPageant = computed(() => {
  return page.props.auth.permissions?.includes('organizer_edit_own_pageant')
})
</script>
```

## Admin Interface

Administrators can manage permissions through the Admin Panel:

1. Navigate to **Admin → Users → Permissions**
2. Select the role tab (Organizers, Tabulators, or Judges)
3. Toggle permissions on/off as needed
4. Click "Save [Role] Permissions" to apply changes

**Note:** Administrator permissions are system-defined and cannot be modified through the UI for security reasons.

## Model Methods

### User Model

```php
// Check if user has permission
$user->hasPermission('permission_key'); // Returns boolean

// Get all permissions for user's role
$user->permissions(); // Returns collection
```

### RolePermission Model

```php
// Get all permissions for a role
RolePermission::getPermissionsForRole('organizer');

// Check if role has permission
RolePermission::hasPermission('organizer', 'organizer_edit_own_pageant');

// Update permissions for a role
RolePermission::updateRolePermissions('organizer', $permissions);
```

## Special Cases

### Administrator Override

Administrators always have all permissions regardless of the database settings. The `User::hasPermission()` method returns `true` for any permission when called on an admin user.

### Permission Middleware

The `CheckPermission` middleware automatically:
- Checks if user is authenticated
- Validates user has the required permission
- Returns 403 Forbidden if permission is denied
- Redirects to login if not authenticated

## Database Seeding

**IMPORTANT:** Permissions must be seeded for the system to work properly!

To seed initial permissions:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

This is **required** after:
- Fresh installation
- Running `php artisan migrate:fresh`
- Database resets

### Verify Permissions Are Seeded

Check if permissions exist:
```bash
php artisan tinker --execute="echo App\Models\RolePermission::count();"
```

Should return `31` (total number of permissions across all roles).

### What Happens If Permissions Aren't Seeded?

- ❌ Users can login but cannot access dashboard routes
- ❌ Permission checks will fail
- ⚠️ System will log warnings about missing permissions
- 🔒 Non-admin users will be blocked from most actions

**Solution:** Run the seeder command above.

This is automatically run during fresh installations.

## Testing

Run permission system tests:

```bash
php artisan test --filter=RolePermissionTest
```

## User Feedback and UI

### Flash Messages

The system provides user-friendly feedback when permissions are denied:

**Backend Flash Messages:**
```php
// Controllers return flash messages instead of hard aborts
return redirect()->back()->with('error', 'You do not have permission to manage users.');
return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to manage permissions.');
```

**Frontend Flash Handling:**
Flash messages are automatically displayed using the notification system in all layouts:
- `AdminLayout.vue` - Watches for flash messages and displays notifications
- `OrganizerLayout.vue` - Automatically handles success/error/warning/info messages

### Custom 403 Error Page

A beautiful, user-friendly 403 error page is displayed when permission is denied:
- Animated icon and friendly message
- Explains why access was denied
- Provides helpful suggestions
- Quick actions to go back or return to dashboard
- Role-specific dashboard redirects

Location: `resources/js/Pages/Errors/403.vue`

### Permission Denied Component

A reusable Vue component for displaying permission errors inline:

```vue
<template>
  <PermissionDenied 
    message="You do not have permission to edit this pageant"
    :show-details="true"
    :dismissible="true"
    @dismiss="handleDismiss"
    @contact-admin="contactAdmin"
  />
</template>

<script setup>
import PermissionDenied from '@/Components/PermissionDenied.vue'

const handleDismiss = () => {
  // Handle dismissal
}

const contactAdmin = () => {
  // Open contact modal or redirect
}
</script>
```

### API Error Responses

For API/AJAX requests, JSON error responses are returned:

```json
{
  "success": false,
  "message": "You do not have permission to manage contestants."
}
```

Example handling in JavaScript:
```javascript
axios.post('/api/contestants', data)
  .catch(error => {
    if (error.response.status === 403) {
      notify.error(error.response.data.message)
    }
  })
```

## Best Practices

1. **Always check permissions** before allowing sensitive operations
2. **Use descriptive permission keys** following the pattern: `role_action_object`
3. **Audit permission changes** - all changes are logged in the audit log
4. **Test permission changes** thoroughly before deploying
5. **Document new permissions** when adding features that require them

## Adding New Permissions

To add new permissions:

1. **Create a migration** to add the permission:
```php
RolePermission::create([
    'role' => 'organizer',
    'permission_key' => 'organizer_new_feature',
    'permission_name' => 'Access New Feature',
    'granted' => true,
]);
```

2. **Update the seeder** if it should be included in fresh installations

3. **Add permission checks** in controllers/middleware where needed

4. **Update the frontend** to show/hide UI elements based on permission

5. **Document the permission** in this file

## Security Considerations

- Permissions are checked server-side on every request
- Frontend permission checks are for UX only (hiding/showing UI elements)
- Never rely solely on frontend permission checks
- Always validate permissions in backend controllers
- Audit logs track all permission changes
- Admin permissions cannot be modified through the UI
