# Real-Time Activity Tracking System

## Features Implemented

### ✅ Backend Components

1. **ActivityService** (`app/Services/ActivityService.php`)
   - Centralized service for logging pageant activities
   - Automatically broadcasts activities to connected clients
   - Provides methods for retrieving organizer activities
   - Default icon mapping for common action types

2. **ActivityCreated Event** (`app/Events/ActivityCreated.php`)
   - ShouldBroadcast event for real-time updates
   - Broadcasts to pageant-specific channels
   - Includes formatted activity data for frontend

3. **Activity Broadcast Channels** (`routes/channels.php`)
   - `organizer.pageant.{id}` - Private channel for organizers
   - Authorization checks ensure only assigned organizers receive updates

4. **Enhanced OrganizerController** (`app/Http/Controllers/OrganizerController.php`)
   - Dashboard method now loads recent activities
   - Provides pageant IDs for real-time subscriptions

### ✅ Frontend Components

1. **Enhanced Dashboard** (`resources/js/Pages/Organizer/Dashboard.vue`)
   - Real-time activity feed with live updates
   - Beautiful activity cards with color-coded action types
   - Role-based user badges (Judge, Tabulator, etc.)
   - Automatic Laravel Echo subscriptions
   - Activity icons mapped to action types
   - Gradient backgrounds for different activity types
   - Empty state when no activities exist

2. **Activity Display Features**
   - User name and role badges
   - Pageant name for each activity
   - Relative timestamps (e.g., "2 minutes ago")
   - Color-coded icons based on activity type
   - Hover effects and animations
   - Responsive design for mobile devices

## How It Works

### Activity Logging Flow

1. **Action Occurs**: Judge submits a score or Tabulator adds a contestant
2. **Controller Logs Activity**: `ActivityService::log()` is called
3. **Activity Created**: Record saved to `activities` table
4. **Event Broadcast**: `ActivityCreated` event fires
5. **Real-Time Update**: Connected organizers receive the update via Laravel Echo
6. **UI Updates**: Dashboard automatically displays the new activity

### Real-Time Updates

```javascript
// Dashboard automatically subscribes to each pageant
window.Echo.private(`organizer.pageant.${pageantId}`)
  .listen('.activity.created', (event) => {
    // New activity appears instantly at the top
    activities.value.unshift(event)
  })
```

## Usage Examples

### For Judge Actions

```php
// When a judge submits a score
$this->activityService->log(
    pageantId: $pageantId,
    actionType: 'SCORE_SUBMITTED',
    description: "Judge {$judge->name} scored contestant #{$contestant->number} - {$criteria->name}",
    entityType: 'Score',
    entityId: $score->id,
    userId: $judge->id
);
```

### For Tabulator Actions

```php
// When a tabulator adds a contestant
$this->activityService->log(
    pageantId: $pageantId,
    actionType: 'CONTESTANT_ADDED',
    description: "Tabulator {$tabulator->name} added contestant #{$contestant->number}",
    entityType: 'Contestant',
    entityId: $contestant->id,
    userId: $tabulator->id
);
```

## Activity Types with Icons

| Action Type | Icon | Color | Description |
|------------|------|-------|-------------|
| SCORE_SUBMITTED | ⭐ Star | Yellow | Judge submitted a new score |
| SCORE_UPDATED | ✏️ Edit | Blue | Judge updated an existing score |
| CONTESTANT_ADDED | 👤+ UserPlus | Green | Tabulator added a contestant |
| CONTESTANT_UPDATED | 👥 Users | Blue | Tabulator updated contestant info |
| JUDGE_ASSIGNED | ⚖️ Scale | Purple | Judge assigned to pageant |
| TABULATOR_ASSIGNED | 📊 BarChart | Indigo | Tabulator assigned to pageant |
| ROUND_STARTED | ▶️ Timer | Green | New round started |
| ROUND_COMPLETED | ✅ CheckCircle | Purple | Round completed |
| CRITERIA_CREATED | ✓✓ ListChecks | Teal | Judging criteria created |
| PAGEANT_UPDATED | 👑 Crown | Orange | Pageant details updated |

## Setup Requirements

### 1. Service Provider Registration

Already added to `bootstrap/providers.php`:
```php
App\Providers\ActivityServiceProvider::class,
```

### 2. Laravel Echo Configuration

Ensure Laravel Echo is configured in your frontend (`resources/js/bootstrap.js`):
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
```

### 3. Run Laravel Reverb

Start the WebSocket server:
```bash
php artisan reverb:start
```

Or in development mode:
```bash
php artisan reverb:start --debug
```

## Testing the System

### 1. Start Required Services
```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite for frontend
npm run dev

# Terminal 3: Laravel Reverb WebSocket server
php artisan reverb:start --debug

# Terminal 4 (optional): Queue worker if using queues
php artisan queue:work
```

### 2. Test Activity Logging

Open the Organizer Dashboard in one browser tab, then in another tab:
- Have a judge submit scores
- Have a tabulator add/update contestants
- Watch activities appear in real-time on the dashboard

### 3. Check Browser Console

You should see:
```
New activity received: {id: 1, action_type: 'SCORE_SUBMITTED', ...}
```

## Database Schema

The `activities` table stores:
- `pageant_id` - Which pageant this activity belongs to
- `user_id` - Who performed the action (nullable for system actions)
- `action_type` - Type of action (SCORE_SUBMITTED, etc.)
- `entity_type` - Type of entity affected (Score, Contestant, etc.)
- `entity_id` - ID of the affected entity
- `description` - Human-readable description
- `icon` - Icon name for UI display
- `metadata` - Additional JSON data
- `ip_address` - User's IP address
- `created_at` / `updated_at` - Timestamps

## Benefits

✅ **Real-Time Updates** - Organizers see activities instantly
✅ **Audit Trail** - Complete history of all actions
✅ **User Attribution** - Know exactly who did what and when
✅ **Beautiful UI** - Color-coded, animated activity feed
✅ **Scalable** - Works across multiple pageants simultaneously
✅ **Filtered by Organizer** - Only see activities for your pageants
✅ **Mobile Responsive** - Works perfectly on all devices

## Next Steps

To fully integrate this system, add `ActivityService` logging to:
1. Judge scoring actions
2. Tabulator contestant management
3. Tabulator round management
4. Any other significant pageant actions

Refer to `ACTIVITY_LOGGING_GUIDE.md` for detailed integration instructions.
