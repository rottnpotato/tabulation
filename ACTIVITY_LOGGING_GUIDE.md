# Activity Logging Implementation Guide

## Overview
The Activity Logging system tracks actions performed by tabulators and judges in real-time and displays them on the Organizer Dashboard.

## Usage in Controllers

### Import the ActivityService
```php
use App\Services\ActivityService;

class YourController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
}
```

### Log Activities

#### When a Judge Submits a Score
```php
public function submitScore(Request $request, $pageantId)
{
    // ... score submission logic ...
    
    $this->activityService->log(
        pageantId: $pageantId,
        actionType: 'SCORE_SUBMITTED',
        description: "Judge {$judge->name} submitted score for contestant #{$contestant->number}",
        entityType: 'Score',
        entityId: $score->id,
        userId: $judge->id,
        metadata: [
            'contestant_id' => $contestant->id,
            'criteria_id' => $criteria->id,
            'score' => $score->score,
        ],
        icon: 'star'
    );
    
    return response()->json(['success' => true]);
}
```

#### When a Tabulator Adds a Contestant
```php
public function addContestant(Request $request, $pageantId)
{
    // ... contestant creation logic ...
    
    $this->activityService->log(
        pageantId: $pageantId,
        actionType: 'CONTESTANT_ADDED',
        description: "Tabulator {$tabulator->name} added contestant #{$contestant->number} - {$contestant->name}",
        entityType: 'Contestant',
        entityId: $contestant->id,
        userId: $tabulator->id,
        icon: 'user-plus'
    );
    
    return redirect()->back()->with('success', 'Contestant added successfully');
}
```

#### When a Tabulator Updates Round Status
```php
public function updateRoundStatus(Request $request, $pageantId, $roundId)
{
    // ... round update logic ...
    
    $this->activityService->log(
        pageantId: $pageantId,
        actionType: 'ROUND_COMPLETED',
        description: "Tabulator {$tabulator->name} completed round: {$round->name}",
        entityType: 'Round',
        entityId: $round->id,
        userId: $tabulator->id,
        metadata: [
            'round_name' => $round->name,
            'status' => $round->status,
        ],
        icon: 'check-circle'
    );
}
```

#### When Judge Updates a Score
```php
public function updateScore(Request $request, $scoreId)
{
    // ... score update logic ...
    
    $this->activityService->log(
        pageantId: $score->pageant_id,
        actionType: 'SCORE_UPDATED',
        description: "Judge {$judge->name} updated score for contestant #{$contestant->number}",
        entityType: 'Score',
        entityId: $score->id,
        userId: $judge->id,
        metadata: [
            'old_score' => $oldScore,
            'new_score' => $score->score,
            'contestant_id' => $contestant->id,
        ],
        icon: 'edit'
    );
}
```

## Available Action Types

- `SCORE_SUBMITTED` - When a judge submits a new score
- `SCORE_UPDATED` - When a judge updates an existing score
- `CONTESTANT_ADDED` - When a tabulator adds a new contestant
- `CONTESTANT_UPDATED` - When a tabulator updates contestant details
- `CONTESTANT_REMOVED` - When a tabulator removes a contestant
- `JUDGE_ASSIGNED` - When a judge is assigned to a pageant
- `JUDGE_REMOVED` - When a judge is removed from a pageant
- `TABULATOR_ASSIGNED` - When a tabulator is assigned to a pageant
- `TABULATOR_REMOVED` - When a tabulator is removed from a pageant
- `ROUND_STARTED` - When a new round begins
- `ROUND_COMPLETED` - When a round is completed
- `CRITERIA_CREATED` - When judging criteria is created
- `CRITERIA_UPDATED` - When judging criteria is updated
- `PAGEANT_UPDATED` - When pageant details are updated
- `STATUS_CHANGED` - When pageant status changes

## Real-Time Broadcasting

Activities are automatically broadcast to:
- `pageant.{pageantId}` - All users connected to that pageant
- `organizer.pageant.{pageantId}` - Organizers of that specific pageant

The organizer dashboard automatically subscribes to these channels and updates the activity feed in real-time.

## Frontend Implementation

The Organizer Dashboard component (`Dashboard.vue`) automatically:
1. Loads recent activities on page load
2. Subscribes to real-time updates via Laravel Echo
3. Displays activities with appropriate icons and styling
4. Updates the activity list when new activities are broadcast

No additional frontend work is needed - just log activities in your controllers!
