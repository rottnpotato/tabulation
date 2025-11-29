# Unified Score Calculation Implementation

## Overview
This document describes the implementation of a unified calculation method for pageant scoring to ensure a single source of truth across all controllers and views.

## Problem Statement
Previously, multiple locations were independently calculating round scores with different logic:
- `TabulatorController::results()` - had its own calculation logic
- `JudgeController` - may have had similar calculations
- `ScoreCalculationService` - had various methods but no single unified method

This led to:
- Duplicate code
- Potential inconsistencies
- Maintenance challenges
- Incorrect score accumulation across round types

## Solution: Unified Calculation Method

### New Method: `ScoreCalculationService::calculateRoundViewScores()`

Located in: `app/Services/ScoreCalculationService.php`

#### Signature
```php
public function calculateRoundViewScores(
    Pageant $pageant, 
    Round $targetRound, 
    bool $useCache = true
): array
```

#### Business Rules Implemented
1. **Same-type round accumulation**: Rounds of the same type (e.g., multiple Preliminary rounds) accumulate together
2. **Type isolation**: Different round types (Preliminary vs Semi-Final vs Final) are calculated independently
3. **Final round reset**: Final round type always starts fresh with no accumulation from previous stages
4. **Stage filtering**: Contestants are filtered based on the previous STAGE's `top_n_proceed` value

#### How It Works

1. **Determine rounds to use**:
   - If target round is Final type → use only that round
   - Otherwise → accumulate all rounds of the same type up to the target round

2. **Calculate scores**:
   - For each contestant, calculate their score in each relevant round
   - Apply round weights
   - Calculate weighted average as final score

3. **Apply ranking**:
   - Sort contestants by score (descending)
   - Apply gender-separated ranking if configured
   - Handle ties according to pageant settings

4. **Filter by advancement**:
   - Determine the previous stage type
   - Get contestants who advanced from that stage
   - Filter results to only show advanced contestants
   - Re-rank after filtering

5. **Cache results**:
   - Cache with key: `round_view_scores_{pageant_id}_{round_id}`
   - TTL: 30 minutes
   - Can be disabled with `$useCache = false`

#### Return Format
```php
[
    [
        'id' => 1,
        'number' => 'C001',
        'name' => 'John Doe',
        'gender' => 'male',
        'region' => 'Region A',
        'is_pair' => false,
        'member_names' => [],
        'member_genders' => [],
        'image' => '/path/to/image.jpg',
        'scores' => [
            'Round Name' => 75.50,
            // ... other rounds
        ],
        'totalScore' => 75.50,
        'finalScore' => 75.50,
        'rank' => 1,
    ],
    // ... more contestants
]
```

## Controllers Updated

### TabulatorController::results()

**Before**: ~100 lines of inline calculation logic
**After**: 3 lines calling the unified method

```php
foreach ($orderedRounds as $currentRound) {
    $roundContestants = $this->scoreCalculationService->calculateRoundViewScores($pageant, $currentRound);
    
    $roundResults['round_'.$currentRound->id] = [
        'contestants' => $roundContestants,
        'top_n_proceed' => $currentRound->top_n_proceed,
    ];
}
```

## Benefits

1. **Single Source of Truth**: All round calculations now use the same method
2. **Consistency**: Results are guaranteed to be calculated the same way everywhere
3. **Maintainability**: Changes to calculation logic only need to be made in one place
4. **Testability**: The unified method can be thoroughly unit tested
5. **Performance**: Built-in caching reduces redundant calculations
6. **Code Reduction**: Eliminated ~80 lines of duplicate code from TabulatorController

## Example Scenario

### Pageant: ms&mrDAUIS
- **Preliminary rounds**: Production Number, Evening Gown
- **Semi-Final round**: Casual
- **Final round**: Question & Answer

### Calculation Logic:
1. **Production Number** (Preliminary):
   - Uses only: Production Number scores
   
2. **Evening Gown** (Preliminary):
   - Uses: Production Number + Evening Gown (accumulated)
   
3. **Casual** (Semi-Final):
   - Uses only: Casual scores
   - Shows only contestants who advanced from Preliminary stage
   - Does NOT accumulate Preliminary scores
   
4. **Question & Answer** (Final):
   - Uses only: Question & Answer scores
   - Shows only contestants who advanced from Semi-Final stage
   - Starts fresh (no accumulation)

## Testing

To verify the implementation:

1. Navigate to Results page for a pageant
2. Switch between different rounds
3. Verify scores only accumulate within same round types
4. Verify Final rounds show only their own scores
5. Verify contestant filtering based on stage advancement

## Next Steps

Consider updating other controllers that may calculate scores:
- JudgeController (if it has similar logic)
- Any API endpoints returning scores
- Print/Export functionality

## Cache Management

To clear calculation cache:
```php
Cache::forget("round_view_scores_{$pageant_id}_{$round_id}");

// Or clear all round view caches for a pageant
Cache::flush(); // (use with caution - clears all cache)
```

## Related Files
- `app/Services/ScoreCalculationService.php`
- `app/Http/Controllers/TabulatorController.php`
- `resources/js/Pages/Tabulator/Results.vue`
