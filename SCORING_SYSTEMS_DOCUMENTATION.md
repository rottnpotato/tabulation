# Scoring Systems Documentation

## Overview

This document provides a comprehensive overview of the scoring systems implemented in the Pageant Tabulation System. The system supports four distinct scoring methods, each with its own calculation logic and use cases.

## Available Scoring Systems

The system supports the following scoring systems as defined in the `pageants.scoring_system` enum field:

1. **Percentage System** (`percentage`)
2. **Scale 1-10 System** (`1-10`)
3. **Scale 1-5 System** (`1-5`)
4. **Points System** (`points`)

---

## Scoring System Details

### 1. Percentage System (`percentage`)

**Range**: 0-100%  
**Default**: Yes (fallback system)  
**Decimal Support**: Yes (2 decimal places)

#### Description
Traditional percentage-based scoring where judges assign percentages (0-100%) for each criterion. The percentage directly represents the quality of performance, with 100% being perfect.

#### Configuration
- **Min Score**: 0
- **Max Score**: 100
- **Allow Decimals**: Yes
- **Decimal Places**: 2

#### Pros
- Intuitive for judges familiar with percentage scoring
- Provides a wide range for precise scoring differentiation
- Familiar to contestants and audience members
- Works well with weighted criteria systems

#### Cons
- May lead to score inflation with many judges scoring near 90-100%
- Large range can lead to inconsistent scoring between different judges
- Requires more mental calculation than simpler scales
- Differences between high scores (e.g., 95% vs 97%) may seem arbitrary

#### Best For
Traditional pageants with experienced judges who can utilize the full percentage scale effectively.

---

### 2. Scale 1-10 System (`1-10`)

**Range**: 1-10 points  
**Decimal Support**: Yes (2 decimal places)

#### Description
Judges score each contestant on a scale of 1-10 for each criterion. A straightforward numerical scale where 1 represents poor performance and 10 represents excellence. Half-points may be allowed for more granular scoring.

#### Configuration
- **Min Score**: 1
- **Max Score**: 10
- **Allow Decimals**: Yes
- **Decimal Places**: 2

#### Pros
- Simple and universally understood scoring system
- Provides enough range for meaningful differentiation
- Easy for judges to quickly decide on scores
- Works well for both novice and experienced judges

#### Cons
- Less granular than percentage scoring
- May still suffer from score clustering at the high end (8-10)
- Limited range may not capture subtle performance differences
- Requires clear guidelines for what each number represents

#### Best For
Pageants where scoring simplicity is prioritized and judges prefer straightforward numerical scales.

---

### 3. Scale 1-5 System (`1-5`)

**Range**: 1-5 points  
**Decimal Support**: No (integer values only)

#### Description
Simplified 1 to 5 scale for easier scoring. This system forces judges to make clear distinctions between performance levels, reducing overthinking and speeding up the judging process.

#### Configuration
- **Min Score**: 1
- **Max Score**: 5
- **Allow Decimals**: No
- **Decimal Places**: 0

#### Pros
- Very fast scoring process
- Forces clear distinctions between performance levels
- Reduces judge overthinking and analysis paralysis
- Easy to understand for all participants

#### Cons
- May result in more ties that require additional tie-breakers
- Less precise than wider scales
- May not satisfy contestants looking for more detailed feedback
- Limited differentiation capability

#### Best For
Fast-paced pageants, preliminary rounds, or when using many criteria where scoring speed is important.

---

### 4. Points System (`points`)

**Range**: 0-50 points  
**Decimal Support**: Yes (2 decimal places)

#### Description
Custom points allocation based on performance and ranking in each criterion. Instead of direct scoring, judges rank contestants for each criterion, and points are allocated based on rank (e.g., 1st place = 50 points, 2nd place = 49 points, etc.).

#### Configuration
- **Min Score**: 0
- **Max Score**: 50
- **Allow Decimals**: Yes
- **Decimal Places**: 2

#### Pros
- Eliminates score inflation and forces differentiation
- Focuses on relative performance rather than absolute scores
- Reduces judge bias toward specific contestants
- Creates clear separation in the final standings

#### Cons
- More complex to tabulate and explain to audience
- Doesn't communicate how close performances were to each other
- May unfairly penalize contestants who are very close in quality
- Requires judges to rank all contestants for each criterion

#### Best For
Competitive pageants where clear ranking differentiation is required and eliminating scoring bias is important.

---

## Score Calculation Logic

### Individual Score Processing

Each individual score submitted by a judge follows this flow:

1. **Input Validation**: Score must be within the min/max range for the criteria
2. **Decimal Validation**: Decimals are validated based on system configuration
3. **Storage**: Score is stored with up to 2 decimal places in the database

### Weighted Average Calculation

The system uses a multi-level weighted average approach:

#### Level 1: Criteria Weighting (Judge-Contestant-Round)

For each judge scoring a contestant in a specific round:

```
Judge Contestant Score = Σ(Individual Score × Criteria Weight) / Σ(Criteria Weight)
```

**Implementation** (from `ScoreCalculationService::calculateJudgeContestantScore`):
```php
$criteriaWeightedSum = 0;
$criteriaWeightTotal = 0;

foreach ($scores as $score) {
    $weight = $score->criteria->weight ?? 1;
    $criteriaWeightedSum += $score->score * $weight;
    $criteriaWeightTotal += $weight;
}

return $criteriaWeightTotal > 0 ? $criteriaWeightedSum / $criteriaWeightTotal : null;
```

#### Level 2: Judge Averaging (Contestant-Round)

For each contestant in a specific round across all judges:

```
Contestant Round Score = Σ(Judge Contestant Score) / Count(Judges)
```

**Implementation** (from `ScoreCalculationService::calculateContestantRoundScore`):
```php
$judgeAverages = [];

foreach ($pageant->judges as $judge) {
    $judgeScore = $this->calculateJudgeContestantScore($judge->id, $contestant->id, $round->id, $pageant->id);
    if ($judgeScore !== null) {
        $judgeAverages[] = $judgeScore;
    }
}

$roundScore = !empty($judgeAverages) ? array_sum($judgeAverages) / count($judgeAverages) : null;
```

#### Level 3: Round Weighting (Final Score)

For each contestant across all rounds:

```
Final Score = Σ(Round Score × Round Weight) / Σ(Round Weight)
```

**Implementation** (from `ScoreCalculationService::calculatePageantFinalScores`):
```php
$totalWeightedScore = 0;
$totalRoundWeight = 0;

foreach ($pageant->rounds as $round) {
    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);
    
    if ($roundScore !== null) {
        $roundWeight = $round->weight ?? 1;
        $totalWeightedScore += $roundScore * $roundWeight;
        $totalRoundWeight += $roundWeight;
    }
}

$finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;
```

### Score Normalization

The system includes normalization logic to ensure scores stay within valid ranges for each scoring system:

**Implementation** (from `TabulatorController::normalizeScore`):
```php
switch ($scoringSystem) {
    case 'percentage':
        return max(0, min(100, $score));
    case '1-10':
        return max(1, min(10, $score));
    case '1-5':
        return max(1, min(5, $score));
    case 'points':
        return max(0, min(50, $score));
    default:
        return max(0, min(100, $score));
}
```

---

## Database Schema

### Pageants Table
- `scoring_system` (enum): `['percentage', '1-10', '1-5', 'points']`

### Criteria Table
- `weight` (integer): Weight for criteria in calculations (default: 1)
- `min_score` (decimal): Minimum allowed score for this criteria
- `max_score` (decimal): Maximum allowed score for this criteria
- `allow_decimals` (boolean): Whether decimal scores are allowed
- `decimal_places` (integer): Number of decimal places allowed

### Rounds Table
- `weight` (integer): Weight for round in final calculations (default: 1)

### Scores Table
- `score` (decimal): The actual score value (stored with 2 decimal places)
- `notes` (text): Optional notes from the judge

---

## Caching Strategy

The system implements intelligent caching to optimize performance:

### Cache Keys
- `pageant_final_scores_{pageant_id}`: Complete final scores for all contestants
- `contestant_final_score_{pageant_id}_{contestant_id}`: Individual contestant final score
- `contestant_round_score_{contestant_id}_{round_id}`: Contestant score for specific round

### Cache Duration
- Final scores: 30 minutes
- Round scores: 15 minutes

### Cache Invalidation
Automatic cache invalidation occurs when:
- Any score is created, updated, or deleted
- Triggered by model events in `Score::boot()` method

---

## Ranking System

After final scores are calculated:

1. **Sorting**: Contestants are sorted by final score in descending order
2. **Rank Assignment**: Ranks are assigned sequentially (1st, 2nd, 3rd, etc.)
3. **Tie Handling**: Currently uses natural ordering; tied scores receive consecutive ranks

---

## Event Broadcasting

The system broadcasts real-time updates for:

- **Score Updates**: When individual scores are submitted (`ScoreUpdated` event)
- **Rankings Updates**: When final rankings change (`RankingsUpdated` event)
- **Round Updates**: When rounds are locked/unlocked or current round changes (`RoundUpdated` event)

---

## Configuration Examples

### Setting Up Criteria for Different Scoring Systems

#### Percentage System
```php
Criteria::create([
    'name' => 'Evening Gown',
    'weight' => 30,
    'min_score' => 0.00,
    'max_score' => 100.00,
    'allow_decimals' => true,
    'decimal_places' => 2
]);
```

#### 1-10 Scale System
```php
Criteria::create([
    'name' => 'Talent',
    'weight' => 25,
    'min_score' => 1.00,
    'max_score' => 10.00,
    'allow_decimals' => true,
    'decimal_places' => 2
]);
```

#### 1-5 Scale System
```php
Criteria::create([
    'name' => 'Interview',
    'weight' => 20,
    'min_score' => 1.00,
    'max_score' => 5.00,
    'allow_decimals' => false,
    'decimal_places' => 0
]);
```

#### Points System
```php
Criteria::create([
    'name' => 'Swimwear',
    'weight' => 25,
    'min_score' => 0.00,
    'max_score' => 50.00,
    'allow_decimals' => true,
    'decimal_places' => 2
]);
```

---

## API Endpoints

### Score Submission
**POST** `/judge/pageants/{pageantId}/rounds/{roundId}/scores`

**Payload**:
```json
{
    "contestant_id": 1,
    "scores": {
        "criteria_id_1": 85.5,
        "criteria_id_2": 92.0
    },
    "notes": "Excellent performance overall"
}
```

### Get Final Results
**GET** `/tabulator/pageants/{pageantId}/results`

**Response**:
```json
{
    "contestants": [
        {
            "id": 1,
            "number": "001",
            "name": "Jane Doe",
            "scores": {
                "Preliminaries": 87.5,
                "Finals": 91.2
            },
            "finalScore": 89.35,
            "rank": 1
        }
    ]
}
```

---

## Frontend Integration

### Score Display Format

The frontend automatically formats scores based on the scoring system:

```javascript
const formatScore = (score, scoringSystem) => {
    if (score === null || score === undefined) return 'Pending';
    
    switch (scoringSystem) {
        case 'percentage':
            return score.toFixed(1) + '%';
        case '1-10':
            return score.toFixed(1) + '/10';
        case '1-5':
            return score.toFixed(1) + '/5';
        case 'points':
            return score.toFixed(0) + ' pts';
        default:
            return score.toFixed(1);
    }
};
```

### Performance Indicators

Score percentages are normalized for visual indicators:

```javascript
const normalizeScoreToPercentage = (score, scoringSystem) => {
    switch (scoringSystem) {
        case 'percentage':
            return score;
        case '1-10':
            return (score / 10) * 100;
        case '1-5':
            return (score / 5) * 100;
        case 'points':
            const maxScore = 50; // configurable
            return (score / maxScore) * 100;
    }
};
```

---

## Migration History

- **2025_03_30_104031**: Added `scoring_system` enum to `pageants` table
- Various criteria-related migrations set up min/max scores and weights

---

## Best Practices

1. **Choose Appropriate System**: Select scoring system based on pageant complexity and judge experience
2. **Consistent Weights**: Ensure criteria weights reflect their intended importance
3. **Clear Instructions**: Provide judges with clear scoring guidelines for each system
4. **Test Calculations**: Verify scoring calculations with sample data before live events
5. **Monitor Cache**: Keep an eye on cache performance during high-traffic events
6. **Backup Scores**: Regularly backup score data during active competitions

---

## Troubleshooting

### Common Issues

1. **Score Validation Errors**: Verify min/max ranges match scoring system
2. **Cache Inconsistencies**: Clear relevant caches when scores seem outdated
3. **Ranking Ties**: Implement additional tie-breaking criteria if needed
4. **Performance Issues**: Monitor database queries during large-scale events

### Debug Commands

```bash
# Clear all scoring caches
php artisan cache:forget "pageant_final_scores_*"

# Recalculate scores for a pageant
php artisan pageant:recalculate-scores {pageant_id}
```

---

*Last Updated: September 7, 2025*