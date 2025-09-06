# Scoring System Optimizations

## Critical Fixes Needed

### 1. Fix Results Calculation
Replace mock data with actual score aggregation:

```php
// app/Http/Controllers/TabulatorController.php
public function results($pageantId)
{
    $pageant = $this->getPageantForTabulator($pageantId, $tabulator->id);
    
    // Get real aggregated scores instead of mock data
    $contestants = $this->calculateFinalScores($pageant);
    
    return Inertia::render('Tabulator/Results', [
        'pageant' => ['id' => $pageant->id, 'name' => $pageant->name],
        'contestants' => $contestants,
        'rounds' => $rounds,
    ]);
}

private function calculateFinalScores($pageant)
{
    $contestants = [];
    
    foreach ($pageant->contestants as $contestant) {
        $roundScores = [];
        $totalWeightedScore = 0;
        $totalRoundWeight = 0;
        
        foreach ($pageant->rounds as $round) {
            $judgeAverages = [];
            
            // Get all judges for this pageant
            foreach ($pageant->judges as $judge) {
                $scores = Score::where('pageant_id', $pageant->id)
                    ->where('round_id', $round->id)
                    ->where('judge_id', $judge->id)
                    ->where('contestant_id', $contestant->id)
                    ->with('criteria')
                    ->get();
                
                if ($scores->isNotEmpty()) {
                    $criteriaWeightedSum = 0;
                    $criteriaWeightTotal = 0;
                    
                    foreach ($scores as $score) {
                        $weight = $score->criteria->weight ?? 1;
                        $criteriaWeightedSum += $score->score * $weight;
                        $criteriaWeightTotal += $weight;
                    }
                    
                    if ($criteriaWeightTotal > 0) {
                        $judgeAverages[] = $criteriaWeightedSum / $criteriaWeightTotal;
                    }
                }
            }
            
            // Average across all judges for this round
            if (!empty($judgeAverages)) {
                $roundScore = array_sum($judgeAverages) / count($judgeAverages);
                $roundScores[$round->name] = $roundScore;
                
                $roundWeight = $round->weight ?? 1;
                $totalWeightedScore += $roundScore * $roundWeight;
                $totalRoundWeight += $roundWeight;
            }
        }
        
        $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;
        
        $contestants[] = [
            'id' => $contestant->id,
            'number' => $contestant->number,
            'name' => $contestant->name,
            'region' => $contestant->origin,
            'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
            'scores' => $roundScores,
            'finalScore' => round($finalScore, 2),
        ];
    }
    
    // Sort by final score descending and add ranks
    usort($contestants, fn($a, $b) => $b['finalScore'] <=> $a['finalScore']);
    
    foreach ($contestants as $index => &$contestant) {
        $contestant['rank'] = $index + 1;
    }
    
    return collect($contestants);
}
```

### 2. Add Performance Indexes

```sql
-- Add these indexes for better query performance
CREATE INDEX idx_scores_aggregation ON scores(pageant_id, round_id, judge_id, contestant_id);
CREATE INDEX idx_scores_criteria_lookup ON scores(criteria_id, contestant_id);
CREATE INDEX idx_criteria_weight_lookup ON criteria(round_id, weight);
```

### 3. Implement Score Caching

```php
// app/Services/ScoreCalculationService.php
class ScoreCalculationService
{
    public function getContestantFinalScore($pageantId, $contestantId, $useCache = true)
    {
        $cacheKey = "contestant_final_score_{$pageantId}_{$contestantId}";
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $finalScore = $this->calculateContestantFinalScore($pageantId, $contestantId);
        
        Cache::put($cacheKey, $finalScore, now()->addMinutes(30));
        
        return $finalScore;
    }
    
    public function invalidateContestantCache($pageantId, $contestantId)
    {
        Cache::forget("contestant_final_score_{$pageantId}_{$contestantId}");
        Cache::forget("pageant_rankings_{$pageantId}");
    }
}
```

### 4. Fix Score Normalization

```php
// app/Http/Controllers/TabulatorController.php
private function normalizeScore($score, $criterion, $scoringSystem)
{
    // Use criteria-specific ranges instead of global scoring system
    $minScore = $criterion->min_score ?? 0;
    $maxScore = $criterion->max_score ?? 100;
    
    return max($minScore, min($maxScore, $score));
}
```

### 5. Add Real-time Updates

```php
// app/Events/ScoreUpdated.php - Enhance existing event
class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function broadcastOn()
    {
        return [
            new PrivateChannel("pageant.{$this->score->pageant_id}"),
            new PrivateChannel("judge.{$this->score->judge_id}"),
        ];
    }
    
    public function broadcastWith()
    {
        return [
            'score_id' => $this->score->id,
            'contestant_id' => $this->score->contestant_id,
            'judge_id' => $this->score->judge_id,
            'round_id' => $this->score->round_id,
            'criteria_id' => $this->score->criteria_id,
            'score' => $this->score->score,
        ];
    }
}
```

## Performance Benchmarks

### Current Issues:
- Results page loads mock data (0ms calculation, but incorrect)
- Score aggregation requires N+1 queries per contestant
- No caching leads to repeated calculations

### Optimized Performance Targets:
- Final results calculation: <500ms for 50 contestants
- Individual score updates: <100ms with cache invalidation
- Real-time updates: <50ms broadcast latency

## Implementation Priority:
1. **CRITICAL**: Fix results calculation (replace mock data)
2. **HIGH**: Add database indexes
3. **MEDIUM**: Implement caching layer
4. **LOW**: Add real-time broadcasting