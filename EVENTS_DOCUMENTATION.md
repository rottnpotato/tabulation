# System Events Documentation
## Pageant Tabulation System

---

## Event Flow Architecture

```
┌────────────────────────────────────────────────────────────────────────────┐
│                     PAGEANT TABULATION SYSTEM EVENTS                        │
└────────────────────────────────────────────────────────────────────────────┘

                        ┌─────────────────────┐
                        │   USER ACTIONS      │
                        └──────────┬──────────┘
                                   │
        ┌──────────────────────────┼──────────────────────────┐
        │                          │                           │
        ▼                          ▼                           ▼
┌───────────────┐         ┌────────────────┐         ┌────────────────┐
│ AUTHENTICATION│         │ CRUD OPERATIONS │         │  REAL-TIME     │
│   EVENTS      │         │    EVENTS       │         │   EVENTS       │
└───────┬───────┘         └────────┬───────┘         └────────┬───────┘
        │                          │                           │
        ├─ Login                   ├─ Create Pageant          ├─ Score Updated
        ├─ Logout                  ├─ Update Pageant          ├─ Round Updated
        ├─ Password Reset          ├─ Delete Pageant          ├─ Rankings Updated
        ├─ Verify Email            ├─ Create Contestant       ├─ Activity Created
        │                          ├─ Update Contestant       ├─ Judge Notified
        │                          ├─ Delete Contestant       └─ Pageant Created
        │                          ├─ Create Judge                    │
        │                          ├─ Update Judge                    │
        │                          ├─ Delete Judge                    │
        │                          ├─ Create Criteria                 │
        │                          ├─ Update Criteria                 │
        │                          ├─ Delete Criteria                 │
        │                          ├─ Submit Score                    │
        │                          ├─ Lock Round                      │
        │                          ├─ Unlock Round                    │
        │                          └─ Set Current Round               │
        │                                     │                        │
        └─────────────────────────────────────┼────────────────────────┘
                                              │
                                              ▼
                        ┌──────────────────────────────────┐
                        │   LARAVEL BACKEND PROCESSING     │
                        │  ┌────────────────────────────┐  │
                        │  │  Database Operations       │  │
                        │  │  Audit Logging            │  │
                        │  │  Activity Logging         │  │
                        │  │  Cache Invalidation       │  │
                        │  └────────────────────────────┘  │
                        └────────────┬─────────────────────┘
                                     │
                    ┌────────────────┼────────────────┐
                    │                │                │
                    ▼                ▼                ▼
        ┌────────────────┐  ┌────────────────┐  ┌────────────────┐
        │   BROADCAST    │  │   RESPONSE     │  │  AUDIT/LOG     │
        │  (Laravel Echo)│  │   (Inertia)    │  │   (Database)   │
        └────────┬───────┘  └────────┬───────┘  └────────────────┘
                 │                   │
                 ▼                   ▼
        ┌─────────────────────────────────────┐
        │    VUE.JS FRONTEND HANDLING         │
        │  ┌───────────────────────────────┐  │
        │  │  Echo Listeners               │  │
        │  │  Inertia Router Events        │  │
        │  │  UI Updates                   │  │
        │  │  Notifications                │  │
        │  └───────────────────────────────┘  │
        └─────────────────────────────────────┘
```

---

## 1. AUTHENTICATION EVENTS

### 1.1 User Login
**Trigger:** User submits login credentials  
**Route:** `POST /login`  
**Controller:** `AuthController@login`  
**Process:**
1. Validate credentials (email, password)
2. Authenticate user via `Auth::attempt()`
3. Regenerate session
4. Redirect based on role (admin/organizer/tabulator/judge)

**Data Flow:**
```
Form Submit → Validation → Auth::attempt() → Session Regenerate → Role-based Redirect
```

---

### 1.2 User Logout
**Trigger:** User clicks logout  
**Route:** `POST /logout`  
**Controller:** `AuthController@logout`  
**Process:**
1. Call `Auth::logout()`
2. Invalidate session
3. Regenerate CSRF token
4. Redirect to login with success message

**Data Flow:**
```
Logout Click → Auth::logout() → Session Invalidate → CSRF Regenerate → Redirect to Login
```

---

### 1.3 Password Reset Request
**Trigger:** User requests password reset  
**Route:** `POST /forgot-password`  
**Controller:** `AuthController@sendResetLinkEmail`  
**Process:**
1. Validate email
2. Send reset link via `Password::sendResetLink()`
3. Email sent with reset token

**Data Flow:**
```
Form Submit → Email Validation → Password::sendResetLink() → Email Notification
```

---

### 1.4 Password Reset
**Trigger:** User submits new password with token  
**Route:** `POST /reset-password`  
**Controller:** `AuthController@resetPassword`  
**Process:**
1. Validate token, email, password
2. Reset password via `Password::reset()`
3. Hash and save new password
4. Generate new remember token
5. Redirect to login

**Data Flow:**
```
Form Submit → Token Validation → Password Hash → Save User → Redirect to Login
```

---

### 1.5 Organizer Email Verification
**Trigger:** Organizer clicks verification link in email  
**Route:** `GET /verify-organizer/{token}`  
**Controller:** `OrganizerController@verify`  
**Process:**
1. Find user by verification token
2. Mark email as verified
3. Clear verification token
4. Update password if provided
5. Show success/failure page

---

## 2. PAGEANT CRUD EVENTS

### 2.1 Create Pageant
**Trigger:** Organizer/Admin creates new pageant  
**Routes:**
- `POST /organizer/pageants/create` (Organizer)
- `POST /admin/pageants/create` (Admin)

**Controller:** 
- `OrganizerController@storePageant`
- `AdminController@storePageant`

**Process:**
1. Validate pageant data (name, date, location, type, scoring system)
2. Create pageant record
3. Attach organizer(s)
4. **Broadcast: `PageantCreated` event**
5. Log activity
6. Redirect with success message

**Broadcast Event:** `App\Events\PageantCreated`
- **Channel:** `private-admin-notifications`
- **Data:** `pageant_id`, `pageant_name`, `organizer_id`, `organizer_name`, `pageant_date`, `event_type`, `location`

**Data Flow:**
```
Form Submit → Validation → DB Insert → Attach Organizers → Broadcast PageantCreated 
→ Activity Log → Redirect
```

---

### 2.2 Update Pageant
**Trigger:** Update pageant details  
**Routes:**
- `PUT /organizer/pageant/{id}`
- `PUT /admin/pageants/{id}`

**Controller:**
- `OrganizerController@updatePageant`
- `AdminController@updatePageant`

**Process:**
1. Authorize user access
2. Validate update data
3. Update pageant record
4. Update organizer assignments if changed
5. Log activity
6. Redirect with success message

**Data Flow:**
```
Form Submit → Authorization → Validation → DB Update → Sync Organizers 
→ Activity Log → Redirect
```

---

### 2.3 Update Pageant Status
**Trigger:** Change pageant status (draft/active/completed/cancelled)  
**Route:** `PUT /organizer/pageant/{id}/status`  
**Controller:** `OrganizerController@updatePageantStatus`  
**Process:**
1. Validate status
2. Update pageant status
3. Log activity
4. Redirect

---

### 2.4 Lock/Unlock Pageant
**Trigger:** Lock or unlock pageant for editing  
**Routes:**
- `POST /organizer/pageant/{id}/lock`
- `POST /organizer/pageant/{id}/unlock`

**Controller:**
- `OrganizerController@lockPageant`
- `OrganizerController@unlockPageant`

**Process:**
1. Authorize user
2. Update `is_locked` status
3. Log activity
4. Redirect

---

## 3. CONTESTANT CRUD EVENTS

### 3.1 Create Contestant
**Trigger:** Add contestant to pageant  
**Routes:**
- `POST /organizer/pageant/{pageantId}/contestants`
- `POST /organizer/pageant/{pageantId}/contestants/bulk-store`

**Controller:**
- `ContestantController@store`
- `OrganizerController@storeContestant`

**Process:**
1. Authorize access to pageant
2. Validate contestant data (name, number, age, height, etc.)
3. Handle photo upload if provided
4. Create contestant record
5. Log activity
6. Return success response

**Data Flow:**
```
Form Submit → Authorization → Validation → Photo Upload → DB Insert 
→ Activity Log → JSON Response
```

---

### 3.2 Update Contestant
**Trigger:** Edit contestant information  
**Route:** `PUT /organizer/pageant/{pageantId}/contestants/{contestantId}`  
**Controller:** `ContestantController@update`  
**Process:**
1. Authorize access
2. Validate update data
3. Handle photo uploads/deletions
4. Update contestant record
5. Update primary photo if changed
6. Log activity
7. Return success response

---

### 3.3 Delete Contestant
**Trigger:** Remove contestant from pageant  
**Route:** `DELETE /organizer/pageant/{pageantId}/contestants/{contestantId}`  
**Controller:** `ContestantController@destroy`  
**Process:**
1. Authorize access
2. Find contestant
3. Delete associated photos from storage
4. Delete contestant record
5. If paired, delete partner contestant
6. Log activity
7. Return success response

**Data Flow:**
```
Delete Request → Authorization → Find Contestant → Delete Photos → Delete DB Record 
→ Handle Partner → Activity Log → JSON Response
```

---

### 3.4 Delete Contestant Photo
**Trigger:** Remove specific photo from contestant  
**Route:** `DELETE /organizer/pageant/{pageantId}/contestants/{contestantId}/photos/{photoIndex}`  
**Controller:** `ContestantController@deletePhoto`  
**Process:**
1. Find contestant image
2. Delete from storage
3. Delete database record
4. Update primary photo if needed
5. Return success response

---

### 3.5 Create/Delete Contestant Pair
**Trigger:** Pair or unpair contestants  
**Routes:**
- `POST /organizer/pageant/{pageantId}/pairs`
- `DELETE /organizer/pageant/{pageantId}/pairs/{pairId}`
- `POST /organizer/pageant/{pageantId}/contestants/{contestantId}/unpair`

**Controller:**
- `ContestantController@storePair`
- `ContestantController@destroyPair`
- `ContestantController@unpair`

**Process:**
1. Validate contestants
2. Create/delete pair record
3. Update contestant pair_id
4. Return success response

---

## 4. JUDGE MANAGEMENT EVENTS

### 4.1 Create Judge (by Tabulator)
**Trigger:** Tabulator creates judge account  
**Route:** `POST /tabulator/pageant/{pageantId}/judges/add`  
**Controller:** `TabulatorController@addJudge`  
**Process:**
1. Validate judge data (name, email, username)
2. Generate random password
3. Create user with role 'judge'
4. Attach to pageant
5. Log activity
6. Return judge details with temporary password

---

### 4.2 Update Judge
**Trigger:** Update judge information  
**Route:** `PUT /tabulator/pageant/{pageantId}/judges/{judgeId}`  
**Controller:** `TabulatorController@updateJudge`  
**Process:**
1. Validate update data
2. Update judge record
3. Attach/detach from rounds if specified
4. Log activity
5. Return success response

---

### 4.3 Delete Judge
**Trigger:** Remove judge from system  
**Route:** `DELETE /admin/users/judges/{id}`  
**Controller:** `UserManagementController@deleteJudge`  
**Process:**
1. Authorize admin access
2. Delete associated scores
3. Detach from pageants
4. Delete user record
5. Log audit
6. Return success response

---

### 4.4 Reset Judge Password
**Trigger:** Tabulator resets judge password  
**Route:** `POST /tabulator/pageant/{pageantId}/judges/{judgeId}/reset-password`  
**Controller:** `TabulatorController@resetJudgePassword`  
**Process:**
1. Generate new random password
2. Hash and update password
3. Log activity
4. Return new password

---

## 5. CRITERIA MANAGEMENT EVENTS

### 5.1 Create Criteria
**Trigger:** Add judging criteria to pageant  
**Route:** `POST /organizer/pageant/{pageantId}/criteria`  
**Controller:** `CriteriaController@store`  
**Process:**
1. Validate criteria data (name, weight, max_score)
2. Create criteria record
3. Log activity
4. Return criteria details

---

### 5.2 Update Criteria
**Trigger:** Edit criteria information  
**Route:** `PUT /organizer/pageant/{pageantId}/criteria/{criterionId}`  
**Controller:** `CriteriaController@update`  
**Process:**
1. Validate update data
2. Update criteria record
3. Log activity
4. Return success response

---

### 5.3 Delete Criteria
**Trigger:** Remove criteria from pageant  
**Route:** `DELETE /organizer/pageant/{pageantId}/criteria/{criterionId}`  
**Controller:** `CriteriaController@destroy`  
**Process:**
1. Check if criteria is in use
2. Delete criteria record
3. Log activity
4. Return success response

---

## 6. ROUND MANAGEMENT EVENTS

### 6.1 Create Round
**Trigger:** Add competition round to pageant  
**Route:** `POST /organizer/pageant/{pageantId}/rounds`  
**Controller:** `OrganizerController@storeRound`  
**Process:**
1. Validate round data (name, order, stage_type)
2. Create round record
3. Attach criteria
4. Log activity
5. Return round details

---

### 6.2 Update Round
**Trigger:** Edit round information  
**Route:** `PUT /organizer/pageant/{pageantId}/rounds/{roundId}`  
**Controller:** `OrganizerController@updateRound`  
**Process:**
1. Validate update data
2. Update round record
3. Sync criteria if provided
4. Log activity
5. Return success response

---

### 6.3 Delete Round
**Trigger:** Remove round from pageant  
**Route:** `DELETE /organizer/pageant/{pageantId}/rounds/{roundId}`  
**Controller:** `OrganizerController@destroyRound`  
**Process:**
1. Check dependencies
2. Delete associated scores
3. Delete round record
4. Log activity
5. Return success response

---

### 6.4 Set Current Round
**Trigger:** Set active judging round  
**Route:** `POST /tabulator/pageant/{pageantId}/rounds/{roundId}/set-current`  
**Controller:** `TabulatorController@setCurrentRound`  
**Process:**
1. Authorize tabulator
2. Update pageant's current_round_id
3. **Broadcast: `RoundUpdated` event** with action='set_current'
4. Log activity
5. Return success response

**Broadcast Event:** `App\Events\RoundUpdated`
- **Channel:** `private-pageant.{pageant_id}`
- **Data:** `round_id`, `round_name`, `pageant_id`, `action`, `is_locked`, `is_current`, `message`, `timestamp`

---

### 6.5 Lock/Unlock Round
**Trigger:** Lock or unlock round for scoring  
**Routes:**
- `POST /tabulator/pageant/{pageantId}/rounds/{roundId}/lock`
- `POST /tabulator/pageant/{pageantId}/rounds/{roundId}/unlock`

**Controller:**
- `TabulatorController@lockRound`
- `TabulatorController@unlockRound`

**Process:**
1. Authorize tabulator
2. Update round's is_locked status
3. **Broadcast: `RoundUpdated` event** with action='locked'/'unlocked'
4. Log activity
5. Return success response

**Broadcast Event:** `App\Events\RoundUpdated`
- **Channel:** `private-pageant.{pageant_id}`
- **Data:** `round_id`, `round_name`, `action` (locked/unlocked), `is_locked`, `message`

---

## 7. SCORING EVENTS

### 7.1 Submit Score
**Trigger:** Judge submits score for contestant  
**Route:** `POST /judge/pageant/{pageantId}/score`  
**Controller:** `JudgeController@storeScore`  
**Process:**
1. Authorize judge access
2. Validate score data (contestant_id, criteria_id, score, round_id)
3. Check for duplicate score
4. Create or update score record
5. Set submitted_at timestamp
6. **Broadcast: `ScoreUpdated` event**
7. **Trigger: Score Model `saved` event** → Cache invalidation
8. Log activity
9. Return success response

**Broadcast Event:** `App\Events\ScoreUpdated`
- **Channels:** 
  - `private-pageant.{pageant_id}`
  - `private-pageant.{pageant_id}.round.{round_id}`
  - `private-judge.{judge_id}`
  - `private-tabulator.pageant.{pageant_id}`
- **Data:** `score_id`, `score`, `contestant_id`, `contestant_name`, `contestant_number`, `criteria_id`, `criteria_name`, `round_id`, `judge_id`, `judge_name`, `pageant_id`, `submitted_at`, `notes`

**Model Event:** `Score::saved`
- **Listener:** Cache invalidation for contestant scores

**Data Flow:**
```
Form Submit → Authorization → Validation → Check Duplicate → Create/Update Score 
→ Broadcast ScoreUpdated → Model Event (Cache Invalidation) → Activity Log 
→ JSON Response
```

---

### 7.2 Score Model Deletion
**Trigger:** Score is deleted  
**Process:**
1. **Trigger: Score Model `deleted` event**
2. Cache invalidation service called
3. Clear contestant score cache

**Model Event:** `Score::deleted`
- **Listener:** Cache invalidation for contestant scores

---

## 8. RANKING & CALCULATION EVENTS

### 8.1 Rankings Updated
**Trigger:** Automatic calculation when scores change  
**Service:** `ScoreCalculationService`  
**Process:**
1. Score submitted/updated/deleted
2. Cache invalidation triggered
3. Rankings recalculated
4. **Broadcast: `RankingsUpdated` event**

**Broadcast Event:** `App\Events\RankingsUpdated`
- **Channels:**
  - `private-pageant.{pageant_id}`
  - `private-tabulator.pageant.{pageant_id}`
- **Data:** `pageant_id`, `rankings` (array), `updated_at`

**Frontend Listeners:**
- `Tabulator/Results.vue`
- `Tabulator/MinorAwards.vue`
- `Tabulator/Dashboard.vue`

---

## 9. REAL-TIME NOTIFICATION EVENTS

### 9.1 Judge Notification
**Trigger:** Tabulator sends notification to judge(s)  
**Route:** `POST /tabulator/pageant/{pageantId}/notify-judges`  
**Controller:** `TabulatorController@notifyJudges`  
**Process:**
1. Validate judge IDs and message
2. Loop through selected judges
3. **Dispatch: `JudgeNotified` event** for each judge
4. Log to audit logs
5. Return success response

**Broadcast Event:** `App\Events\JudgeNotified`
- **Channel:** `private-judge.{judge_id}`
- **Data:** `judge_id`, `pageant_id`, `message`, `title`, `round_name`, `action`, `timestamp`
- **Auto-logs to:** `audit_logs` table

**Frontend Listeners:**
- `Judge/Scoring.vue`
- `Judge/Dashboard.vue`

**Data Flow:**
```
Form Submit → Validation → Loop Judges → Dispatch JudgeNotified Event 
→ Broadcast to Judge Channel → Log to Audit → JSON Response
```

---

### 9.2 Activity Created
**Trigger:** Any significant action in the system  
**Service:** `ActivityService@log`  
**Process:**
1. Action occurs (score submit, contestant add, etc.)
2. Activity service logs action
3. **Broadcast: `ActivityCreated` event**

**Broadcast Event:** `App\Events\ActivityCreated`
- **Channels:**
  - `private-pageant.{pageant_id}`
  - `private-organizer.pageant.{pageant_id}`
- **Data:** `id`, `pageant_id`, `user_id`, `user_name`, `user_role`, `action_type`, `entity_type`, `entity_id`, `description`, `icon`, `metadata`, `created_at`, `formatted_time`

**Frontend Listeners:**
- `organizer/ActivityLogsViewer.vue`

**Activity Types:**
- `SCORE_SUBMITTED`
- `CONTESTANT_ADDED`
- `CONTESTANT_UPDATED`
- `CONTESTANT_REMOVED`
- `JUDGE_ADDED`
- `JUDGE_UPDATED`
- `JUDGE_REMOVED`
- `ROUND_LOCKED`
- `ROUND_UNLOCKED`
- `CURRENT_ROUND_CHANGED`
- `PAGEANT_CREATED`
- `PAGEANT_UPDATED`
- And more...

---

## 10. AUDIT LOGGING EVENTS

### 10.1 Audit Log Creation
**Trigger:** Administrative or security-sensitive actions  
**Service:** `AuditLogService@log` or `AuditLogService@logSystemAction`  
**Process:**
1. Action occurs
2. Log to `audit_logs` table
3. Record user, role, action type, target entity, details, IP address

**Logged Actions:**
- User account creation/deletion
- Permission changes
- Password resets
- Judge notifications
- Pageant approvals
- System configuration changes

---

## 11. FRONTEND (INERTIA) EVENTS

### 11.1 Router Events
**Location:** `resources/js/app.js`  
**Events:**

| Event | Trigger | Action |
|-------|---------|--------|
| `start` | Inertia navigation begins | `NProgress.start()` - Show progress bar |
| `finish` | Inertia navigation completes | `NProgress.done()` - Hide progress bar |
| `error` | Navigation error (e.g., 419 CSRF) | Reload page if 419 error |

**Implementation:**
```javascript
router.on('start', () => NProgress.start());
router.on('finish', () => NProgress.done());
router.on('error', (event) => {
  if (event.detail.response?.status === 419) {
    window.location.reload();
  }
})
```

---

### 11.2 Echo Connection Events
**Trigger:** WebSocket connection lifecycle  
**Events:**
- `subscribed` - Successfully subscribed to channel
- `error` - Connection/subscription error

**Example:**
```javascript
window.Echo.private(`pageant.${pageantId}`)
  .subscribed(() => {
    console.log('✅ Successfully subscribed to pageant channel')
  })
  .error((error) => {
    console.error('❌ Error subscribing:', error)
  })
```

---

## 12. BROADCAST CHANNELS

### Channel Authorization
**Location:** `routes/channels.php`

| Channel | Authorization | Description |
|---------|--------------|-------------|
| `admin-notifications` | Admin only | System-wide admin notifications |
| `pageant.all` | Admin only | Global pageant events |
| `pageant.{id}` | Admin, assigned organizers, tabulators, judges | Pageant-specific events |
| `pageant.{id}.round.{roundId}` | Same as pageant channel | Round-specific score updates |
| `organizer.pageant.{id}` | Admin, assigned organizers | Organizer activity logs |
| `judge.{id}` | Admin, specific judge | Judge notifications |
| `tabulator.pageant.{id}` | Admin, assigned tabulators | Tabulator-specific updates |

---

## 13. FRONTEND EVENT LISTENERS BY PAGE

### 13.1 Judge/Scoring.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}`
- `private-judge.{judge_id}`

**Listened Events:**
- `RoundUpdated` → Handle round changes (lock/unlock, set current)
- `ScoreUpdated` → Refresh score status
- `JudgeNotified` → Show notification alert with sound

---

### 13.2 Judge/Dashboard.vue
**Subscribed Channels:**
- `private-judge.{judge_id}`

**Listened Events:**
- `JudgeNotified` → Display notification

---

### 13.3 Tabulator/Dashboard.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}`

**Listened Events:**
- `ScoreUpdated` → Reload summary and recent activity
- `RoundUpdated` → Reload pageant info

---

### 13.4 Tabulator/Scores.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}`

**Listened Events:**
- `ScoreUpdated` → Refresh scores data

---

### 13.5 Tabulator/Results.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}`

**Listened Events:**
- `ScoreUpdated` → Refresh results
- `RankingsUpdated` → Update rankings display

---

### 13.6 Tabulator/RoundManagement.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}`

**Listened Events:**
- `RoundUpdated` → Refresh round status
- `ScoreUpdated` → Update score counts

---

### 13.7 Tabulator/MinorAwards.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}`

**Listened Events:**
- `ScoreUpdated` → Recalculate awards
- `RankingsUpdated` → Update award rankings

---

### 13.8 Tabulator/Judges.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}`

**Listened Events:**
- `ScoreUpdated` → Update judge progress

---

### 13.9 TabulatorLayout.vue
**Subscribed Channels:**
- `private-pageant.{pageant_id}` (all pages)
- `private-tabulator.pageant.{pageant_id}` (specific pages)

**Listened Events:**
- `ScoreUpdated` → Handle score updates across layout

---

### 13.10 AdminEventListener.vue
**Subscribed Channels:**
- `private-admin-notifications`
- `private-pageant.all`
- `private-pageant.{pageant_id}` (if viewing specific pageant)

**Listened Events:**
- `.pageant.created` → Show pageant creation notification
- `.pageant.event.updated` → Handle pageant events

---

### 13.11 organizer/ActivityLogsViewer.vue
**Subscribed Channels:**
- `private-organizer.pageant.{pageant_id}`

**Listened Events:**
- `.activity.created` → Add new activity to log viewer

---

### 13.12 tabulator/AuditLogsViewer.vue
**Subscribed Channels:**
- `private-tabulator.pageant.{pageant_id}`

**Listened Events:**
- `ScoreUpdated` → Refresh audit logs

---

## 14. USER MANAGEMENT EVENTS

### 14.1 Create Organizer
**Route:** `POST /admin/users/organizers`  
**Controller:** `UserManagementController@storeOrganizer`  
**Process:**
1. Validate organizer data
2. Generate verification token
3. Create user record (role: organizer)
4. Send verification email
5. Log audit
6. Return success response

---

### 14.2 Update Organizer
**Route:** `PUT /admin/users/organizers/{id}`  
**Controller:** `UserManagementController@updateOrganizer`  
**Process:**
1. Validate update data
2. Update user record
3. Log audit
4. Return success response

---

### 14.3 Delete Organizer
**Route:** `DELETE /admin/users/organizers/{id}`  
**Controller:** `UserManagementController@deleteOrganizer`  
**Process:**
1. Detach from all pageants
2. Delete user record
3. Log audit
4. Return success response

---

### 14.4 Create Admin
**Route:** `POST /admin/users/admins`  
**Controller:** `UserManagementController@storeAdmin`  
**Process:**
1. Validate admin data
2. Create user record (role: admin)
3. Log audit
4. Return success response

---

### 14.5 Delete Admin
**Route:** `DELETE /admin/users/admins/{id}`  
**Controller:** `UserManagementController@deleteAdmin`  
**Process:**
1. Check if last admin (prevent deletion)
2. Delete user record
3. Log audit
4. Return success response

---

### 14.6 Update User Permissions
**Route:** `PUT /admin/users/permissions/{id}`  
**Controller:** `UserManagementController@updatePermissions`  
**Process:**
1. Validate permissions
2. Update user permissions
3. Log audit
4. Return success response

---

## 15. SYSTEM EVENT SUMMARY

### Total Event Types: 60+

**Authentication:** 5 events
**Pageant Management:** 6 events  
**Contestant Management:** 5 events  
**Judge Management:** 4 events  
**Criteria Management:** 3 events  
**Round Management:** 5 events  
**Scoring:** 2 events  
**Rankings:** 1 event  
**Notifications:** 2 events  
**Activity Logging:** 1 event (many action types)  
**Audit Logging:** 1 event (many action types)  
**User Management:** 8 events  
**Frontend Router:** 3 events  
**Broadcasting:** 7 custom events  

---

## 16. EVENT NAMING CONVENTIONS

### Backend Laravel Events
- PascalCase class names: `ScoreUpdated`, `RoundUpdated`, `PageantCreated`
- Action-based naming: `{Entity}{Action}` format

### Frontend Echo Events
- Listen without dot prefix for class-based: `.listen('ScoreUpdated')`
- Listen with dot prefix for custom broadcast names: `.listen('.pageant.created')`

### Activity Action Types
- SCREAMING_SNAKE_CASE: `SCORE_SUBMITTED`, `CONTESTANT_ADDED`, `ROUND_LOCKED`

### Audit Action Types
- SCREAMING_SNAKE_CASE: `USER_CREATED`, `PERMISSION_GRANTED`, `PAGEANT_APPROVED`

---

## 17. CRITICAL EVENT FLOWS

### Flow 1: Judge Submits Score
```
1. Judge fills score form in Judge/Scoring.vue
2. POST /judge/pageant/{id}/score
3. JudgeController@storeScore validates and saves
4. ScoreUpdated event broadcast to 4 channels
5. Score model 'saved' event triggers cache invalidation
6. Activity logged via ActivityService
7. Frontend Echo listeners receive ScoreUpdated:
   - Judge/Scoring.vue: Updates UI
   - Tabulator/Scores.vue: Refreshes scores table
   - Tabulator/Results.vue: Recalculates results
   - Tabulator/Dashboard.vue: Updates summary
8. If rankings change, RankingsUpdated event broadcast
9. Frontend updates rankings displays
```

### Flow 2: Tabulator Sets Current Round
```
1. Tabulator clicks "Set as Current" in RoundManagement.vue
2. POST /tabulator/pageant/{id}/rounds/{roundId}/set-current
3. TabulatorController@setCurrentRound updates pageant
4. RoundUpdated event broadcast with action='set_current'
5. Activity logged
6. Frontend Echo listeners receive RoundUpdated:
   - Judge/Scoring.vue: Switches to new round
   - Tabulator/RoundManagement.vue: Updates UI
   - Tabulator/Dashboard.vue: Reloads pageant data
7. Judges see current round indicator change
```

### Flow 3: Organizer Creates Pageant
```
1. Organizer submits pageant form
2. POST /organizer/pageants/create
3. OrganizerController@storePageant creates pageant
4. PageantCreated event broadcast to admin channel
5. Activity logged
6. AdminEventListener.vue receives notification
7. Admins see real-time notification of new pageant
8. Redirect to pageant setup
```

### Flow 4: Tabulator Notifies Judges
```
1. Tabulator selects judges and types message
2. POST /tabulator/pageant/{id}/notify-judges
3. TabulatorController@notifyJudges loops judges
4. For each judge: JudgeNotified event dispatched
5. Event broadcast to private-judge.{id} channel
6. Event auto-logs to audit_logs table
7. Judge/Scoring.vue and Judge/Dashboard.vue receive event
8. Judge sees notification modal with sound alert
9. Audit log records notification for tracking
```

---

## 18. TESTING EVENT FLOWS

### Manual Testing Checklist
- [ ] Login/logout functionality
- [ ] Password reset flow
- [ ] Email verification
- [ ] Create/update/delete pageant
- [ ] Create/update/delete contestant
- [ ] Create/update/delete judge
- [ ] Submit score and verify broadcast
- [ ] Lock/unlock round and verify broadcast
- [ ] Set current round and verify broadcast
- [ ] Send judge notification and verify receipt
- [ ] Check activity logs populate
- [ ] Check audit logs populate
- [ ] Verify rankings update on score change
- [ ] Test all Echo subscriptions
- [ ] Test authorization on all channels
- [ ] Verify NProgress on page transitions

---

## 19. TROUBLESHOOTING EVENTS

### Event Not Broadcasting
1. Check Laravel Reverb is running
2. Verify `.env` has correct BROADCAST_CONNECTION=reverb
3. Check channel authorization in `routes/channels.php`
4. Verify user has permission for channel
5. Check browser console for Echo errors
6. Verify event implements `ShouldBroadcast`

### Frontend Not Receiving Events
1. Check Echo is initialized in `resources/js/bootstrap.js`
2. Verify channel subscription in component
3. Check `.listen()` event name matches backend
4. Verify `onMounted()` hook runs
5. Check `onUnmounted()` properly leaves channels
6. Inspect WebSocket connection in Network tab

### Activity/Audit Logs Not Saving
1. Check service is called in controller
2. Verify database connection
3. Check model fillable properties
4. Verify user is authenticated (if required)

---

## 20. BEST PRACTICES

1. **Always broadcast significant state changes** - Users need real-time feedback
2. **Log both activities and audits** - Activities for user actions, audits for security
3. **Use private channels** - Protect sensitive data
4. **Clean up subscriptions** - Call `onUnmounted()` and leave channels
5. **Handle connection errors** - Provide fallback UI
6. **Throttle broadcasts** - Avoid spamming updates
7. **Cache invalidation on model events** - Keep data fresh
8. **Test authorization** - Verify channel access controls
9. **Use typed event data** - Consistent broadcast structure
10. **Document new events** - Keep this file updated

---

*Document Generated: November 30, 2025*  
*System: Pageant Tabulation & Scoring Management*  
*Laravel Version: 12*  
*Inertia Version: 2*  
*Vue Version: 3*  
*Broadcasting: Laravel Reverb + Laravel Echo*
