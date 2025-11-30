# Pageant Calculation and Progression Logic

This document outlines how the system calculates scores and determines contestant progression between rounds.

## 1. Score Calculation Logic

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

## 2. Progression Logic (Who Proceeds?)

Progression is controlled by the `top_n_proceed` setting configured on specific rounds.

### Configuration

*   You define the **"Top N Proceed"** value on the **last round of a stage**.
*   *Example*: To select a Top 5 for the Finals, set `top_n_proceed = 5` on the last `semi-final` (or `preliminary`) round.

### Automatic Filtering Process

When the system renders the results for a subsequent stage (e.g., Finals), it performs the following steps automatically:

1.  **Identify Previous Stage**: The system looks at the round order to find the preceding stage type.
2.  **Calculate Previous Rankings**: It calculates the final standings for that previous stage.
3.  **Apply Cutoff**: It selects the top N contestants based on the `top_n_proceed` value.
    *   *Note*: Ties at the cutoff point are handled automatically (e.g., if rank 5 and 6 are tied, both may proceed depending on configuration).
4.  **Filter Current View**: The current round's scoring view and results will **only display the advancing contestants**. All eliminated contestants are hidden.

---

## 3. Key Code References

*   **Calculation Service**: `app/Services/ScoreCalculationService.php`
    *   `calculateRoundViewScores()`: The primary method for generating results.
    *   `getAdvancingContestantIds()`: The logic that determines exactly which IDs qualify for the next round.
*   **Database Configuration**: `rounds` table
    *   `top_n_proceed`: The integer field defining the cutoff number (e.g., 5, 10, 15).

## 4. Example Scenario

**Setup**: A pageant with **Preliminary** rounds leading to a **Top 5 Final**.

1.  **Preliminary Round**: All contestants compete. Scores are entered.
2.  **Configuration**: In the Admin panel, edit the last Preliminary Round and set **"Top N Proceed"** to `5`.
3.  **Final Round**:
    *   When judges or tabulators open the Final Round, the system checks the Preliminary results.
    *   It identifies the Top 5 contestants.
    *   **Only those 5 contestants** are shown on the scoring screen.
