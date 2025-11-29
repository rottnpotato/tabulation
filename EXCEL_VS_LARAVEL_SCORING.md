# Excel Tabulation System vs Laravel Implementation

## Overview

This document compares the Excel-based tabulation system (`Tabulation-pageant-admin-2025.xlsx`) with our Laravel implementation, identifies gaps, and documents the improvements made to achieve accurate scoring and Top N selection.

---

## Excel System Analysis

### Source File: `storage/app/public/Tabulation-pageant-admin-2025.xlsx`

**Event:** Mister & Miss BISU TROPIKO 2024 (HIMAMAT 2024)  
**Date:** September 6, 2024

### Sheet Structure

| Sheet | Purpose | Rows × Cols |
|-------|---------|-------------|
| Dashboard | Event information (title, date, venue) | 26 × 6 |
| Semi-Final | Main scoring with consolidated results | 96 × 22 |
| Final | Top 3 finalists Q&A scoring | 33 × 22 |
| Semi-Final Results | Displays semi-final category winners | 58 × 12 |
| Final Results | Displays final winners (1st, 2nd, 3rd) | 46 × 12 |

### Categories Scored (Semi-Final)

1. **Production Number** (Weight: 25%)
2. **Tropical Attire** (Weight: 40%)
3. **Casual Interview** (Weight: 35%)

### Categories Scored (Final)

1. **Production Number & Vintage Attire Presentation** (Weight: 70%)
2. **Question and Answer** (Weight: 30%)

---

## Excel Scoring Methodology

### Step 1: Judge Score Import

Each judge's score is imported from separate judge sheets:

```excel
=('[1]Production Number'!$G16/100)*100
=('[2]Production Number'!$G16/100)*100
=('[3]Production Number'!$G16/100)*100
```

- Scores are normalized to percentage (0-1 scale × 100)
- Each judge has their own sheet for each category

### Step 2: Per-Judge Ranking (RANK.AVG)

For each judge, contestants are ranked using Excel's `RANK.AVG` function:

```excel
=RANK.AVG(C15,$C$15:$C$20)      -- Judge 1's ranking of contestants
=RANK.AVG(E15,$E$15:$E$20)      -- Judge 2's ranking of contestants
=RANK.AVG(G15,$G$15:$G$20)      -- Judge 3's ranking of contestants
```

**RANK.AVG Behavior:**
- If two contestants tie with the same score, they share the average of the ranks they would occupy
- Example: If contestants #1 and #2 both have 95% and would be ranks 2 and 3, they both get rank **2.5**

### Step 3: Total Percentage Calculation

```excel
=(C15+E15+G15)/3*100   -- Average of 3 judges' percentages
```

### Step 4: Total Rank Sum

```excel
=(D15+F15+H15)   -- Sum of ranks from all 3 judges
```

**This is the KEY difference from score averaging!**

The Excel system uses **sum of ranks** to determine winners, not average of scores.

### Step 5: Final Ranking

```excel
=RANK.AVG(J15,$J$15:$J$20,1)   -- The ",1" means ascending (lower rank sum = better)
```

Winners are determined by who has the **LOWEST sum of ranks**.

### Step 6: Category Consolidation with Weights

```excel
-- Production Number contribution
=(C71/100)*25    -- 25% weight

-- Tropical Attire contribution  
=(E71/100)*40    -- 40% weight

-- Casual Interview contribution
=(G71/100)*35    -- 35% weight

-- Combined weighted score
=(D71+F71+H71)/3
```

### Step 7: Top N Selection

```excel
=INDEX('Semi-Final'!B$71:B$76,MATCH(SMALL('Semi-Final'!K$71:K$76,1),'Semi-Final'!K$71:K$76,0))
=INDEX('Semi-Final'!B$71:B$76,MATCH(SMALL('Semi-Final'!K$71:K$76,2),'Semi-Final'!K$71:K$76,0))
=INDEX('Semi-Final'!B$71:B$76,MATCH(SMALL('Semi-Final'!K$71:K$76,3),'Semi-Final'!K$71:K$76,0))
```

- Uses `SMALL()` to find the 1st, 2nd, 3rd lowest rank sums
- `INDEX/MATCH` retrieves the contestant number for each position

---

## Scoring Method Comparison

### Excel: Rank-Sum Method

```
┌─────────────────────────────────────────────────────────────┐
│                    RANK-SUM METHOD                          │
│                                                             │
│  1. Each judge assigns percentage scores                    │
│  2. Each judge's scores are RANKED (1st, 2nd, 3rd...)      │
│  3. Ranks from all judges are SUMMED                        │
│  4. Winner = LOWEST total rank sum                          │
│                                                             │
│  Example:                                                   │
│  ┌──────────┬─────┬─────┬─────┬─────┬────────────┐         │
│  │Contestant│ J1  │ J2  │ J3  │ Sum │ Final Rank │         │
│  ├──────────┼─────┼─────┼─────┼─────┼────────────┤         │
│  │    #4    │  3  │  1  │  3  │  7  │     1      │         │
│  │    #3    │  1  │  3  │  1  │  5  │     2      │ ← Tied  │
│  │    #1    │  1  │  3  │ 4.5 │ 8.5 │     3      │         │
│  └──────────┴─────┴─────┴─────┴─────┴────────────┘         │
│                                                             │
│  Note: J1 ranked #1 and #3 both 1st (tied), so both = 1    │
│  but in RANK.AVG they would be 1.5 each                    │
└─────────────────────────────────────────────────────────────┘
```

### Laravel (Before): Score-Average Method

```
┌─────────────────────────────────────────────────────────────┐
│                  SCORE-AVERAGE METHOD                       │
│                                                             │
│  1. Each judge assigns scores                               │
│  2. Scores are weighted and averaged                        │
│  3. Winner = HIGHEST average score                          │
│                                                             │
│  Example:                                                   │
│  ┌──────────┬─────┬─────┬─────┬─────────┬────────────┐     │
│  │Contestant│ J1  │ J2  │ J3  │ Average │ Final Rank │     │
│  ├──────────┼─────┼─────┼─────┼─────────┼────────────┤     │
│  │    #3    │ 95  │ 85  │ 94  │  91.33  │     1      │     │
│  │    #4    │ 95  │ 91  │ 88  │  91.33  │     2      │     │
│  │    #1    │ 97  │ 85  │ 87  │  89.67  │     3      │     │
│  └──────────┴─────┴─────┴─────┴─────────┴────────────┘     │
│                                                             │
│  Note: #3 and #4 have SAME average but different rankings  │
│  because of insertion order (not fair!)                    │
└─────────────────────────────────────────────────────────────┘
```

---

## Why Rank-Sum is Often Preferred

### Advantages of Rank-Sum

1. **Eliminates Score Inflation**: Judges who consistently score high don't skew results
2. **Focuses on Relative Performance**: What matters is who each judge thinks is best
3. **Handles Judge Discrepancies**: If one judge gives 95% and another gives 75% to the same person, averaging would hurt them unfairly
4. **Forces Differentiation**: Judges must rank contestants, creating clear separation

### Disadvantages of Rank-Sum

1. **Doesn't Show Margin**: A narrow win looks the same as a landslide
2. **More Complex Calculation**: Harder to explain to audience
3. **Ties Require Careful Handling**: Need RANK.AVG or similar

---

## Laravel Implementation Changes

### 1. Database Schema Updates

Added to `pageants` table:

```php
$table->enum('ranking_method', ['score_average', 'rank_sum'])
      ->default('score_average');

$table->enum('tie_handling', ['sequential', 'average', 'minimum'])
      ->default('average');
```

### 2. New ScoreCalculationService Methods

#### `calculateWithRankSum()`

Implements the Excel's rank-sum methodology:

```php
public function calculateWithRankSum(Pageant $pageant): array
{
    // For each round, calculate each judge's ranking of contestants
    // Sum the ranks across all judges
    // Final rank = lowest total rank sum
}
```

#### `calculateRankAvg()`

Implements Excel's RANK.AVG function:

```php
private function calculateRankAvg(float $value, array $allValues, string $direction = 'desc'): float
{
    // Count how many values are better
    // Count how many values are tied
    // Return: startRank + (tieCount - 1) / 2
}
```

### 3. Improved Top N Selection

The `getAdvancingContestantIds()` method now:

1. Uses the pageant's configured `ranking_method`
2. Properly handles ties with RANK.AVG
3. Includes all tied contestants at the cutoff (no arbitrary exclusion)
4. Separates by gender for pair pageants

### 4. Unified Score Display

Results now show:
- **Total Score**: The weighted average percentage (for display)
- **Rank Sum**: The sum of ranks across judges (for ranking)
- **Final Rank**: Determined by rank sum (ascending)

---

## Score Calculation Flow (New)

```
┌─────────────────────────────────────────────────────────────┐
│                     JUDGE INPUT                             │
│  Judge enters score for each criteria                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              LEVEL 1: CRITERIA WEIGHTING                    │
│  JudgeContestantScore = Σ(score × weight) / Σ(weight)      │
│  This gives each judge's overall score for contestant       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              LEVEL 2: JUDGE RANKING                         │
│  For each judge, rank all contestants using RANK.AVG        │
│  Ties get averaged ranks (e.g., tied for 2nd = 2.5 each)   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              LEVEL 3: RANK SUMMATION                        │
│  TotalRankSum = Σ(ranks from all judges for all rounds)    │
│  Lower sum = better performance                             │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              LEVEL 4: FINAL RANKING                         │
│  Sort by TotalRankSum ascending                             │
│  Apply RANK.AVG for any ties                               │
│  Assign final placement (1st, 2nd, 3rd...)                 │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              TOP N SELECTION                                │
│  If top_n_proceed = 4:                                      │
│  - Take contestants with final rank ≤ 4                    │
│  - Include ALL ties at the cutoff                          │
│  - For pairs: apply per gender                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Configuration Options

### Ranking Method

| Value | Description | Winner Criteria |
|-------|-------------|-----------------|
| `score_average` | Traditional weighted average | Highest score |
| `rank_sum` | Sum of judge rankings | Lowest rank sum |

### Tie Handling

| Value | Description | Example (tied at 2nd) |
|-------|-------------|----------------------|
| `sequential` | Consecutive ranks | Both get 2, next gets 3 |
| `average` | RANK.AVG style | Both get 2.5, next gets 4 |
| `minimum` | RANK.MIN style | Both get 2, next gets 4 |

---

## Example Calculation

### Scenario: 6 Contestants, 3 Judges, 1 Round

**Raw Scores (percentage):**

| Contestant | Judge 1 | Judge 2 | Judge 3 |
|------------|---------|---------|---------|
| #1 | 97% | 85% | 87% |
| #2 | 94% | 82% | 87% |
| #3 | 95% | 85% | 94% |
| #4 | 95% | 91% | 88% |
| #5 | 92% | 85% | 90% |
| #6 | 95% | 84% | 79% |

**Judge Rankings (using RANK.AVG):**

| Contestant | J1 Rank | J2 Rank | J3 Rank | Rank Sum |
|------------|---------|---------|---------|----------|
| #1 | 1 | 3 | 4.5 | **8.5** |
| #2 | 5 | 6 | 4.5 | **15.5** |
| #3 | 3 | 3 | 1 | **7** |
| #4 | 3 | 1 | 3 | **7** |
| #5 | 6 | 3 | 2 | **11** |
| #6 | 3 | 5 | 6 | **14** |

**Final Ranking (by Rank Sum, ascending):**

| Final Rank | Contestant | Rank Sum | Note |
|------------|------------|----------|------|
| 1.5 | #3 | 7 | Tied |
| 1.5 | #4 | 7 | Tied |
| 3 | #1 | 8.5 | |
| 4 | #5 | 11 | |
| 5 | #6 | 14 | |
| 6 | #2 | 15.5 | |

**If Top 3 Proceed:**
- #3, #4, and #1 advance (ranks 1.5, 1.5, and 3)

---

## Files Modified

1. `database/migrations/XXXX_add_ranking_method_to_pageants.php` - Schema update
2. `app/Services/ScoreCalculationService.php` - Rank-sum calculation
3. `app/Models/Pageant.php` - Ranking method accessor
4. `resources/js/Pages/Tabulator/Results.vue` - Unified score display
5. `resources/js/Pages/Tabulator/Scores.vue` - Rank breakdown display
6. `resources/js/Pages/Organizer/PageantView.vue` - Ranking method selection

---

## Best Practices

1. **For Traditional Pageants**: Use `score_average` with `sequential` tie handling
2. **For Competitive Pageants**: Use `rank_sum` with `average` tie handling (Excel method)
3. **Always**: Include all tied contestants at Top N cutoff
4. **For Pairs**: Apply Top N separately per gender

---

*Last Updated: November 29, 2025*
