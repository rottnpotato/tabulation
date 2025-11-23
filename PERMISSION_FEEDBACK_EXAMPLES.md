# Permission System - User Feedback Examples

This document provides examples of how the permission system provides feedback to users across different scenarios.

## 1. Flash Messages (Redirects)

When a user attempts an action they don't have permission for, they receive a flash message:

### Admin Attempting Unauthorized Action
```php
// Controller
if (! Auth::user()->hasPermission('admin_manage_users')) {
    return redirect()->back()->with('error', 'You do not have permission to manage users.');
}
```

**User Experience:**
- ❌ Red notification appears at top of screen
- 📝 Message: "You do not have permission to manage users."
- ⏱️ Auto-dismisses after 5 seconds
- ↩️ User stays on current page

### Organizer Accessing Restricted Feature
```php
if (! Auth::user()->hasPermission('organizer_edit_own_pageant')) {
    return redirect()->back()->with('error', 'You do not have permission to edit pageants.');
}
```

**User Experience:**
- ❌ Error notification with clear message
- 🔙 Automatically returned to previous page
- 💡 Message suggests contacting admin

## 2. JSON/API Responses

For AJAX/API calls, JSON responses are returned:

### Judge Submitting Score Without Permission
```php
return response()->json([
    'success' => false,
    'message' => 'You do not have permission to submit scores.'
], 403);
```

**Frontend Handling:**
```javascript
try {
  await axios.post('/judge/scores', scoreData)
} catch (error) {
  if (error.response.status === 403) {
    notify.error(error.response.data.message)
    // User sees: "You do not have permission to submit scores."
  }
}
```

**User Experience:**
- ❌ Toast notification appears
- 🎯 Specific error message displayed
- 🔄 Form stays intact (data not lost)
- 🖱️ User can try alternative action

### Organizer Creating Contestant Without Permission
```php
if (! Auth::user()->hasPermission('organizer_create_contestant')) {
    return response()->json([
        'success' => false,
        'message' => 'You do not have permission to manage contestants.'
    ], 403);
}
```

**User Experience:**
- ❌ Inline error message in form
- 📋 Form data preserved
- 💾 Changes not submitted
- 🔍 User can review their input

## 3. Custom 403 Error Page

When accessing a protected route directly:

### Direct URL Access Without Permission
```
User types: /admin/users/permissions
```

**User Experience:**
- 🎨 Beautiful custom 403 error page
- 🔒 Animated lock icon
- 📝 Clear explanation:
  - "You do not have permission to access this resource"
  - Why this happened
  - What they can do next
- 🔘 Action buttons:
  - "Go Back" - Returns to previous page
  - "Go to Dashboard" - Role-specific dashboard redirect
- 📧 Contact support link

## 4. Inline Permission Denied Component

For sections within a page that require permissions:

### Usage in Vue Component
```vue
<template>
  <div>
    <div v-if="canManageUsers">
      <!-- User management interface -->
    </div>
    
    <PermissionDenied 
      v-else
      message="You need administrator permissions to manage users"
      :show-details="true"
      :dismissible="false"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PermissionDenied from '@/Components/PermissionDenied.vue'

const page = usePage()
const canManageUsers = computed(() => {
  return page.props.auth.user?.role === 'admin'
})
</script>
```

**User Experience:**
- 🎨 Styled warning box with icon
- 📝 Clear message about missing permission
- 🔗 "Contact administrator" link
- ❌ Dismiss button (if enabled)
- 🎯 Preserves page context

## 5. Real-Time Feedback Examples

### All User Roles

#### Admin User
```
Action: Attempts to access permissions page
Result: ✅ Access granted (admin has all permissions)
Feedback: Page loads normally
```

```
Action: Attempts to delete admin account (own account)
Result: ❌ Blocked by business logic
Feedback: "You cannot delete your own account" (error notification)
```

#### Organizer User
```
Action: Attempts to create contestant with permission
Result: ✅ Contestant created successfully
Feedback: "Contestant added successfully" (success notification)
```

```
Action: Attempts to create contestant without permission
Result: ❌ Blocked by permission check
Feedback: JSON error response → "You do not have permission to manage contestants"
```

```
Action: Attempts to edit another organizer's pageant
Result: ❌ Blocked by access check
Feedback: "You do not have access to this pageant" (error notification)
```

#### Tabulator User
```
Action: Views judge information with permission
Result: ✅ Judge list displayed
Feedback: No message (success is implicit)
```

```
Action: Attempts to view judges without permission
Result: ❌ Redirected back with error
Feedback: "You do not have permission to view judge information"
```

```
Action: Locks round for tabulation
Result: ✅ Round locked successfully
Feedback: "Round 'Evening Gown' has been locked for editing" (success notification)
```

#### Judge User
```
Action: Submits scores with permission
Result: ✅ Scores saved
Feedback: "Scores submitted successfully" (success notification)
```

```
Action: Attempts to submit scores without permission
Result: ❌ JSON error returned
Feedback: "You do not have permission to submit scores"
```

```
Action: Tries to access organizer features
Result: ❌ 403 error page shown
Feedback: Custom 403 page with explanation
```

## 6. Notification Types

The system uses different notification styles:

### Success (Green)
- ✅ Permission granted
- ✅ Action completed successfully
- ✅ Changes saved

### Error (Red)
- ❌ Permission denied
- ❌ Action failed
- ❌ Validation error

### Warning (Yellow)
- ⚠️ Limited permissions
- ⚠️ Action partially completed
- ⚠️ About to expire

### Info (Blue)
- ℹ️ Permission information
- ℹ️ System notifications
- ℹ️ Helpful tips

## 7. Best Practices for Developers

### Always Provide Context
```php
// ❌ Bad
return redirect()->back()->with('error', 'Permission denied');

// ✅ Good
return redirect()->back()->with('error', 'You do not have permission to manage users. Contact your administrator if you need access.');
```

### Match Response to Request Type
```php
// For page navigation (redirects)
return redirect()->back()->with('error', $message);

// For API calls (JSON)
return response()->json(['success' => false, 'message' => $message], 403);

// For middleware (abort to custom page)
abort(403, $message);
```

### Preserve User Data
```javascript
// ✅ Good - Form data preserved
axios.post('/api/contestants', formData)
  .catch(error => {
    if (error.response.status === 403) {
      notify.error(error.response.data.message)
      // formData still available for editing
    }
  })
```

### Provide Next Steps
```vue
<!-- Show actionable feedback -->
<PermissionDenied 
  message="You cannot edit this pageant"
  :show-details="true"
  @contact-admin="openContactModal"
/>
```

## 8. Testing User Feedback

### Manual Testing Checklist
- [ ] Error messages display correctly
- [ ] Notifications auto-dismiss
- [ ] Flash messages appear on redirect
- [ ] JSON errors handled by frontend
- [ ] 403 page displays properly
- [ ] "Go Back" button works
- [ ] Dashboard redirect goes to correct role
- [ ] Permission denied component renders
- [ ] No console errors

### Test Different Scenarios
1. Remove permission from database
2. Try to access protected page
3. Submit protected form via AJAX
4. Access route with middleware
5. Navigate after permission error
6. Refresh page with active notification
7. Test all four user roles

## Summary

The permission system provides comprehensive feedback through:
- 📬 **Flash Messages** - For page redirects
- 📡 **JSON Responses** - For API calls  
- 🎨 **Custom Error Pages** - For direct access
- 🔔 **Toast Notifications** - For real-time feedback
- 📦 **Inline Components** - For contextual warnings

All feedback is:
- ✅ User-friendly and clear
- ✅ Contextually appropriate
- ✅ Actionable with next steps
- ✅ Consistent across the application
- ✅ Accessible and dismissible
