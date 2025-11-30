# Pageant Calculation and Progression Logic

This document outlines how the system calculates scores, applies ranking methods, handles ties, and determines contestant progression between rounds.

---

## 1. Ranking Methods

The system supports **three distinct ranking methods** that determine how winners are selected. Each method has different use cases and produces different results.

### 1.1 Score Average Method (`score_average`)

**Winner Determination**: Highest average score wins  
**Best For**: Traditional pageants with experienced judges

#### How It Works:
1. Judges assign scores (percentage, 1-10, 1-5, or points) for each criterion
2. Scores are weighted by criteria weight and averaged per judge
3. All judge averages are combined into a final score
4. Contestants are ranked from **highest to lowest** score

#### Formula:
```
Judge Score = Σ(Score × Criteria Weight) / Σ(Criteria Weight)
Round Score = Σ(Judge Scores) / Count(Judges)  
Final Score = Σ(Round Score × Round Weight) / Σ(Round Weight)
```

#### Example:
| Contestant | Judge 1 | Judge 2 | Judge 3 | Average | Rank |
|------------|---------|---------|---------|---------|------|
| #1         | 92%     | 88%     | 90%     | 90.00%  | 1    |
| #2         | 85%     | 91%     | 87%     | 87.67%  | 2    |
| #3         | 80%     | 82%     | 84%     | 82.00%  | 3    |

---

### 1.2 Rank Sum Method (`rank_sum`)

**Winner Determination**: Lowest sum of ranks wins (Golf-style scoring)  
**Best For**: Competitive pageants; matches Excel tabulation systems

#### How It Works:
1. Judges assign scores for each contestant
2. Each judge's scores are converted to **ranks** (1st, 2nd, 3rd, etc.)
3. Ranks from all judges are **summed** together
4. Contestants are ranked from **lowest to highest** rank sum

#### Formula:
```
Per Judge: Convert score → rank using RANK.AVG
Total Rank Sum = Σ(Judge Ranks)
Final Rank = Contestant with lowest total rank sum
```

#### RANK.AVG Tie Handling:
When contestants tie with the same score, they share the average of the ranks they would occupy.
- Example: If two contestants tie for 2nd/3rd place, both receive rank **2.5**

#### Example:
| Contestant | J1 Score | J1 Rank | J2 Score | J2 Rank | J3 Score | J3 Rank | Rank Sum | Final |
|------------|----------|---------|----------|---------|----------|---------|----------|-------|
| #4         | 88%      | 3       | 95%      | 1       | 87%      | 3       | **7**    | 1st   |
| #3         | 92%      | 1       | 85%      | 3       | 94%      | 1       | **5**    | 2nd   |
| #1         | 92%      | 1       | 83%      | 4       | 82%      | 4.5     | **9.5**  | 3rd   |

> **Note**: In the example above, J1 ranked contestants #1 and #3 both 1st (tied scores), so using RANK.AVG, both receive rank 1. However, if they were competing for 2nd/3rd, both would get 2.5.

---

### 1.3 Ordinal/Final Ballot Method (`ordinal`)

**Winner Determination**: Majority of #1 votes wins; if no majority, lowest sum of ranks wins  
**Best For**: Major pageants (Miss Universe, Miss World format)

#### How It Works:
1. Each judge gives scores that are converted to ranks (1 = best)
2. **First Check - Majority Rule**: Count how many judges ranked each contestant #1
   - If a contestant has > 50% of judges ranking them #1, they win immediately
3. **Second Check - Sum of Ranks**: If no majority, use lowest total rank sum (golf system)

#### Majority Threshold:
```
Majority = floor(Judge Count / 2) + 1
Example: 5 judges → need 3+ first-place votes for majority
Example: 7 judges → need 4+ first-place votes for majority
```

#### Example (5 Judges):
| Contestant | #1 Votes | Rank Sum | Outcome |
|------------|----------|----------|---------|
| #2         | **3**    | 8        | **WINNER** (Majority: 3/5 = 60%) |
| #1         | 2        | 7        | 2nd (Lower rank sum, but no majority) |
| #3         | 0        | 10       | 3rd |

#### Ordinal Data Tracked:
- `rank1Count`: Number of #1 votes received
- `rankSum`: Total sum of all ranks
- `hasMajority`: Boolean indicating if contestant achieved majority
- `finalRank`: Computed position after ordinal rules applied

---

## 2. Tie Handling Methods

When contestants have identical scores or rank sums, the system offers three tie-handling strategies:

### 2.1 Average Rank (`average`) - Default
- Tied contestants share the average of the ranks they would occupy
- Mimics Excel's `RANK.AVG` function
- **Example**: Two contestants tied for 2nd/3rd → both get rank **2.5**

### 2.2 Minimum Rank (`minimum`)
- Tied contestants all receive the better (lower) rank number
- Similar to Excel's `RANK` or `RANK.EQ` function
- **Example**: Two contestants tied for 2nd/3rd → both get rank **2**

### 2.3 Sequential Rank (`sequential`)
- Tied contestants receive consecutive ranks based on their position in the sorted list
- No shared ranks; order is determined by secondary factors (contestant number)
- **Example**: Two contestants tied for 2nd/3rd → ranks **2** and **3** assigned sequentially

---

## 3. Score Calculation Logic

The system uses a **Unified Calculation Method** (`ScoreCalculationService::calculateRoundViewScores`) that ensures consistency across all views.

### Core Rules

1.  **Accumulation (Same Type)**
    *   Rounds of the **same type** accumulate scores together.
    *   *Example*: If "Production Number" and "Evening Gown" are both set as `preliminary`, their scores are combined for the Preliminary stage result.

2.  **Isolation (Different Types)**
    *   Different round types are calculated independently.
    *   *Example*: A "Casual Interview" round set as `semi-final` does **not** include scores from `preliminary` rounds.

3.  **Final Reset**
    *   The `final` round type always starts fresh. It does not inherit scores from previous stages (Preliminary or Semi-Final).

### The Mathematical Formula

1.  **Judge Score** (Per Criteria)
    *   Calculated as a weighted average of all criteria for one judge.
    *   `Formula: Σ(Score × Weight) / Σ(Total Weight)`

2.  **Round Score** (Per Contestant)
    *   Calculated as the average of all judges' scores for that contestant.
    *   `Formula: Σ(Judge Scores) / Count(Judges)`

3.  **Total Stage Score**
    *   Calculated as the weighted average of all rounds within that stage.
    *   `Formula: Σ(Round Score × Round Weight) / Σ(Total Round Weight)`

---

## 4. Progression Logic (Who Proceeds?)

Progression is controlled by the `top_n_proceed` setting configured on specific rounds.

### Configuration

*   You define the **"Top N Proceed"** value on the **last round of a stage**.
*   *Example*: To select a Top 5 for the Finals, set `top_n_proceed = 5` on the last `semi-final` (or `preliminary`) round.

### Automatic Filtering Process

When the system renders the results for a subsequent stage (e.g., Finals), it performs the following steps automatically:

1.  **Identify Previous Stage**: The system looks at the round order to find the preceding stage type.
2.  **Calculate Previous Rankings**: It calculates the final standings for that previous stage using the configured ranking method.
3.  **Apply Cutoff**: It selects the top N contestants based on the `top_n_proceed` value.
    *   Ties at the cutoff point are handled automatically (e.g., if rank 5 and 6 are tied, both may proceed).
4.  **Filter Current View**: The current round's scoring view and results will **only display the advancing contestants**. All eliminated contestants are hidden.

### Gender-Separated Progression

For **pair pageants** (Mr. & Ms. categories), the system:
- Calculates rankings separately for male and female contestants
- Applies Top N cutoff independently per gender
- Example: `top_n_proceed = 5` selects Top 5 Males AND Top 5 Females

---

## 5. Ranking Method Selection Guide

| Scenario | Recommended Method | Tie Handling |
|----------|-------------------|--------------|
| Traditional beauty pageant | `score_average` | `sequential` |
| Competitive pageant (Excel-style) | `rank_sum` | `average` |
| Major international pageant format | `ordinal` | N/A (built-in) |
| School/amateur competitions | `score_average` | `average` |
| Events requiring clear differentiation | `rank_sum` | `minimum` |

---

## 6. Key Code References

*   **Calculation Service**: `app/Services/ScoreCalculationService.php`
    *   `calculateRoundViewScores()`: Primary method for generating results
    *   `calculateWithRankSum()`: Implements rank-sum methodology
    *   `applyOrdinalRanking()`: Implements ordinal/final ballot system
    *   `calculateRankAvg()`: RANK.AVG tie handling implementation
    *   `getAdvancingContestantIds()`: Determines which contestants qualify for next round
    *   `applyGenderSeparatedRanking()`: Handles pair pageant rankings

*   **Database Configuration**: `pageants` table
    *   `ranking_method`: enum (`score_average`, `rank_sum`, `ordinal`)
    *   `tie_handling`: enum (`sequential`, `average`, `minimum`)

*   **Database Configuration**: `rounds` table
    *   `top_n_proceed`: Integer field defining the cutoff number

---

## 7. Example Scenario

**Setup**: A pageant with **Preliminary** rounds leading to a **Top 5 Final** using **rank_sum** method.

1.  **Preliminary Round**: All contestants compete. Scores are entered.
2.  **Ranking**: Each judge's scores are converted to ranks, then summed across judges.
3.  **Configuration**: In the Admin panel, edit the last Preliminary Round and set **"Top N Proceed"** to `5`.
4.  **Top N Selection**: System identifies the 5 contestants with the **lowest rank sums**.
5.  **Final Round**:
    *   When judges or tabulators open the Final Round, the system checks the Preliminary results.
    *   **Only those 5 contestants** are shown on the scoring screen.
    *   Final round scoring starts fresh (no score carryover from Preliminary).

---

*Last Updated: December 1, 2025*
