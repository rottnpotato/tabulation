<template>
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4">
      <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
      <div v-if="slots.actions" class="flex items-center space-x-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Results Table -->
    <div v-if="contestants.length" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-if="!hideRankColumn"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Rank
            </th>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Contestant
            </th>
            <th
              v-for="(round, roundIndex) in rounds"
              :key="round.id"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide"
              :class="getRoundHeaderClass(round, roundIndex)"
            >
              <div class="flex flex-col items-center gap-1">
                <span>{{ round.name }}</span>
                <span v-if="round.type" class="text-[9px] font-medium opacity-75 uppercase">{{ round.type }}</span>
              </div>
            </th>
            <!-- Total Raw Score column - only shown in inherit mode -->
            <th
              v-if="finalScoreMode === 'inherit' && isLastFinalRound"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-emerald-700 bg-emerald-50"
            >
              <div class="flex flex-col items-center gap-1">
                <span>Total Raw Score</span>
                <span class="text-[9px] font-medium opacity-75">WEIGHTED</span>
              </div>
            </th>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              <span v-if="isLastFinalRound">Final Result (Top {{ numberOfWinners }})</span>
              <span v-else>{{ rankingMethod === 'rank_sum' ? 'Rank Sum' : 'Total' }}</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <template v-for="(contestant, index) in rankedContestants" :key="contestant.id">
            <tr
              class="hover:bg-gray-50/80 transition-all duration-500 ease-out"
              :class="[
                {
                  'bg-emerald-50/40 border-l-4 border-l-emerald-500': !hideRankColumn && contestant.qualified && contestant.qualification_cutoff,
                  'opacity-50 border-l-4 border-l-slate-300': !hideRankColumn && !contestant.qualified && contestant.qualification_cutoff !== null && contestant.qualification_cutoff !== undefined,
                  'animate-rank-up': getRankChange(contestant.id, index + 1) === 'up',
                  'animate-rank-down': getRankChange(contestant.id, index + 1) === 'down',
                  'animate-pulse-subtle': isUpdating && getRankChange(contestant.id, index + 1) === 'same'
                }
              ]"
            >
            <!-- Rank -->
            <td v-if="!hideRankColumn" class="whitespace-nowrap px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <div class="relative">
                  <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold transition-all duration-300"
                    :class="getRankBadgeClass(index + 1, contestant.qualified)"
                  >
                    <span class="mr-1 tabular-nums">{{ index + 1 }}</span>
                    <span v-if="showWinners && index < numberOfWinners">{{ getRankDisplay(index + 1) }}</span>
                    <span v-else-if="!showWinners && index < 3">{{ getRankDisplay(index + 1) }}</span>
                  </span>
                  <!-- Rank change indicator -->
                  <transition name="rank-indicator">
                    <span
                      v-if="isUpdating && getRankChange(contestant.id, index + 1) === 'up'"
                      class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-green-500 text-white text-[10px] font-bold shadow-lg"
                    >
                      ↑
                    </span>
                    <span
                      v-else-if="isUpdating && getRankChange(contestant.id, index + 1) === 'down'"
                      class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold shadow-lg"
                    >
                      ↓
                    </span>
                  </transition>
                </div>
                <span
                  v-if="contestant.qualified && contestant.qualification_cutoff"
                  class="inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2.5 py-1 text-xs font-semibold text-white border border-emerald-600 shadow-sm"
                  :title="`Proceeded to Next Round (Top ${contestant.qualification_cutoff})`"
                >
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span>Proceeded</span>
                </span>
                <span
                  v-else-if="!contestant.qualified && contestant.qualification_cutoff !== null && contestant.qualification_cutoff !== undefined"
                  class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 border border-slate-200"
                  :title="`Did not proceed (below Top ${contestant.qualification_cutoff})`"
                >
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                  <span>Eliminated</span>
                </span>
              </div>
            </td>

            <!-- Contestant Info -->
            <td class="whitespace-nowrap px-4 py-3">
              <div class="flex items-center gap-3">
                <img
                  :src="contestant.image"
                  :alt="contestant.name"
                  class="h-10 w-10 flex-shrink-0 rounded-full border border-gray-200 object-cover"
                />
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-gray-900">
                    {{ contestant.name }}
                  </p>
                  <p class="mt-0.5 text-xs text-gray-500">
                    <span v-if="contestant.is_pair && contestant.member_names && contestant.member_names.length > 0" class="italic">
                      {{ contestant.member_names.join(' & ') }} •
                    </span>
                    #{{ contestant.number }}
                    <span v-if="contestant.region"> • {{ contestant.region }}</span>
                  </p>
                </div>
              </div>
            </td>

            <!-- Round Scores -->
            <td
              v-for="(round, roundIndex) in rounds"
              :key="round.id"
              class="whitespace-nowrap px-4 py-3"
              :class="[getRoundCellClass(round, roundIndex), getDisplayScore(contestant, round.name) !== null ? 'text-gray-900' : 'text-gray-300']">
              <div class="flex flex-col items-center gap-1">
                <!-- Score: show display score (sum of judge totals) for visual, use scores for ranking logic -->
                <span v-if="hasValidScore(getDisplayScore(contestant, round.name))" class="text-sm font-medium tabular-nums">
                  {{ formatScore(getDisplayScore(contestant, round.name)) }}
                </span>
                <span v-else-if="contestant.scores[round.name] === 0" class="text-gray-300 italic text-sm" title="Did not compete in this round">—</span>
                <span v-else class="text-gray-300 italic text-sm">—</span>
                
                <!-- Advancement Badge for this round -->
                <template v-if="hasValidScore(contestant.scores[round.name])">
                  <!-- Show "Top N" badge for final round - only in fresh mode (inherit mode uses FINAL RESULT column for ranking) -->
                  <span
                    v-if="round.type?.toLowerCase() === 'final' && finalScoreMode !== 'inherit'"
                    class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-semibold text-white border"
                    :class="getRankInRound(contestant, round) <= numberOfWinners ? 'bg-amber-500 border-amber-600' : 'bg-blue-500 border-blue-600'"
                    :title="getRankInRound(contestant, round) <= numberOfWinners ? `Winner - Top ${numberOfWinners}` : 'Finalist'"
                  >
                    <svg v-if="getRankInRound(contestant, round) <= numberOfWinners" class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg v-else class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span v-if="getRankInRound(contestant, round) <= numberOfWinners">Top {{ getRankInRound(contestant, round) }}</span>
                    <span v-else>Finalist</span>
                  </span>
                  
                  <!-- Show "Finalist" badge for final round in inherit mode (no ranking, just indicates participation) -->
                  <span
                    v-else-if="round.type?.toLowerCase() === 'final' && finalScoreMode === 'inherit'"
                    class="inline-flex items-center gap-0.5 rounded-full bg-indigo-500 px-1.5 py-0.5 text-[10px] font-semibold text-white border border-indigo-600"
                    title="Competed in Final Round"
                  >
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Finalist</span>
                  </span>
                  
                  <!-- Show "Advanced" badge only for non-final rounds that lead to next stage -->
                  <span
                    v-else-if="shouldShowAdvancementBadge(roundIndex) && round.top_n_proceed && getRankAtRound(contestant, roundIndex) <= round.top_n_proceed"
                    class="inline-flex items-center gap-0.5 rounded-full bg-emerald-500 px-1.5 py-0.5 text-[10px] font-semibold text-white border border-emerald-600"
                    :title="`Advanced from ${round.name} (Top ${round.top_n_proceed})`"
                  >
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Advanced</span>
                  </span>
                </template>
                <!-- Show 0 score indicator for contestants who didn't actually compete -->
                <span 
                  v-else-if="contestant.scores[round.name] === 0 && round.type?.toLowerCase() === 'final'"
                  class="text-[10px] text-gray-400 italic"
                >
                  No score
                </span>
              </div>
            </td>

            <!-- Total Raw Score column - only shown in inherit mode -->
            <td 
              v-if="finalScoreMode === 'inherit' && isLastFinalRound"
              class="whitespace-nowrap px-4 py-3 text-center bg-emerald-50/50"
            >
              <span 
                v-if="hasValidFinalScore(contestant) && getWeightedRawTotal(contestant) !== null"
                class="text-sm font-semibold tabular-nums text-emerald-700"
                :title="`Total weighted raw score: ${formatScore(getWeightedRawTotal(contestant))} (sum of score × round weight)`"
              >
                {{ formatScore(getWeightedRawTotal(contestant)) }}
              </span>
              <span v-else class="text-gray-300 italic text-sm">—</span>
            </td>

            <!-- Total Score / Rank Sum -->
            <td class="whitespace-nowrap px-4 py-3 text-right">
              <div class="flex flex-col items-end gap-1">
                <div class="flex items-center gap-2">
                  <!-- Rank Sum method: show rank sum -->
                  <span
                    v-if="rankingMethod === 'rank_sum'"
                    class="text-sm font-semibold tabular-nums text-purple-700"
                    :title="`Rank Sum: ${formatScore(contestant.totalRankSum, 1)} (lower is better)`"
                  >
                    {{ formatScore(contestant.totalRankSum, 1) }}
                  </span>
                  <!-- Tie-break indicator for rank_sum -->
                  <span
                    v-if="rankingMethod === 'rank_sum' && (contestant as any).tieBreakInfo"
                    class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-100 text-amber-600 cursor-help"
                    :title="(contestant as any).tieBreakInfo"
                  >
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                  </span>
                  <!-- Score Average method: show computed score (finalScore for inherit mode, displayTotal otherwise) -->
                  <!-- In inherit mode, show score only when there's a tie (for tie-breaking purposes) -->
                  <span
                    v-else-if="finalScoreMode !== 'inherit' || !isLastFinalRound"
                    class="text-sm font-semibold tabular-nums"
                    :class="getScoreClass(finalScoreMode === 'inherit' ? contestant.totalScore : getDisplayTotal(contestant))"
                    :title="getFinalScoreBreakdown(contestant)"
                  >
                    {{ formatScore(finalScoreMode === 'inherit' ? contestant.totalScore : getDisplayTotal(contestant)) }}
                  </span>
                  <!-- Inherit mode: only show score for tie-breaker display -->
                  <!-- For rank_sum method, show weightedRawTotal (actual score) as tie-breaker, not totalScore (which is rank sum) -->
                  <span
                    v-else-if="hasScoreTie(contestant)"
                    class="text-sm font-semibold tabular-nums text-emerald-600"
                    :title="`Tie-breaker score: ${formatScore(rankingMethod === 'rank_sum' ? getWeightedRawTotal(contestant) : contestant.totalScore)}`"
                  >
                    {{ formatScore(rankingMethod === 'rank_sum' ? getWeightedRawTotal(contestant) : contestant.totalScore) }}
                  </span>
                  
                  <!-- Info icon for transparency when inheritance breakdown available -->
                  <button
                    v-if="finalScoreMode === 'inherit' && contestant.inheritanceBreakdown"
                    @click="toggleScoreBreakdown(contestant.id)"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition-colors cursor-pointer"
                    title="Click to see inheritance breakdown"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </button>
                  
                  <!-- Top N Winner Badge in Final Result Column - ranked among finalists only -->
                  <span
                    v-if="isLastFinalRound && showWinners && hasValidFinalScore(contestant) && getFinalRankAmongFinalists(contestant) <= numberOfWinners"
                    class="inline-flex items-center gap-0.5 rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-semibold text-white border border-amber-600 whitespace-nowrap"
                    :title="`Winner - Top ${numberOfWinners}`"
                  >
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span>Top {{ getFinalRankAmongFinalists(contestant) }}</span>
                  </span>
                </div>
                
                <!-- Show secondary metric ONLY when there's a tie (tieBreakInfo exists) -->
                <span 
                  v-if="rankingMethod === 'rank_sum' && (contestant as any).tieBreakInfo && (contestant as any).displayScore"
                  class="text-xs text-gray-400"
                  :title="`Score: ${formatScore((contestant as any).displayScore)} (tie-breaker)`"
                >
                  ({{ formatScore((contestant as any).displayScore) }})
                </span>
              </div>
            </td>
          </tr>
          
          <!-- Score Breakdown Expandable Row (Inheritance Only) -->
          <tr
            v-if="expandedBreakdowns.has(contestant.id) && finalScoreMode === 'inherit' && contestant.inheritanceBreakdown"
            class="bg-emerald-50 border-t border-emerald-200"
          >
            <td :colspan="getColspanForBreakdown()" class="px-4 py-4">
              <div class="space-y-3">
                <!-- Inheritance Breakdown -->
                <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                  Inheritance Score Breakdown
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                  <div
                    v-for="(breakdown, stageKey) in contestant.inheritanceBreakdown"
                    :key="stageKey"
                    class="flex flex-col p-3 bg-white rounded-lg border border-emerald-200 shadow-sm"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-sm font-medium text-gray-700">{{ breakdown.stageType }}</span>
                      <span class="text-xs font-semibold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">
                        {{ breakdown.percentage }}%
                      </span>
                    </div>
                    <div class="text-xs text-gray-500 space-y-1">
                      <div class="flex justify-between">
                        <span>{{ rankingMethod === 'rank_sum' ? 'Stage Score:' : 'Stage Average:' }}</span>
                        <span class="font-medium tabular-nums">{{ formatScore(breakdown.stageAverage) }}</span>
                      </div>
                      <div class="flex justify-between text-emerald-700 font-medium">
                        <span>{{ rankingMethod === 'rank_sum' ? 'Rank Sum:' : 'Contribution:' }}</span>
                        <span class="tabular-nums">{{ formatScore(breakdown.contribution) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-emerald-200">
                  <span class="text-sm font-semibold text-gray-700">{{ rankingMethod === 'rank_sum' ? 'Total Rank Sum:' : 'Final Weighted Score:' }}</span>
                  <span class="text-lg font-bold text-emerald-700 tabular-nums">{{ formatScore(rankingMethod === 'rank_sum' ? contestant.totalRankSum : contestant.totalScore) }}</span>
                </div>
              </div>
            </td>
          </tr>
          
          <!-- Qualification Cutoff Line (hidden in overall tally view) -->
          <tr 
            v-if="!hideRankColumn && shouldShowCutoffLine(index)"
            class="bg-slate-100 border-t-2 border-b-2 border-slate-400"
          >
            <td :colspan="getColspanForBreakdown()" class="px-4 py-2">
              <div class="flex items-center justify-center gap-2 text-xs font-semibold text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>Qualification Cutoff (Top {{ contestant.qualification_cutoff }})</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
              </div>
            </td>
          </tr>
        </template>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="py-12 text-center">
      <div class="text-gray-500">
        <Trophy class="mx-auto h-12 w-12 text-gray-400" />
        <p class="mt-2 text-lg font-medium">No results available</p>
        <p class="text-sm">Results will appear here once scoring is complete.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useSlots, ref, watch } from 'vue'
import { Trophy } from 'lucide-vue-next'

const slots = useSlots()

interface Round {
  id: number
  name: string
  weight: number
  type?: string
  top_n_proceed?: number
  is_last_of_type?: boolean
}

interface InheritanceBreakdownItem {
  stageType: string
  percentage: number
  stageAverage: number
  contribution: number
}

interface Contestant {
  id: number
  name: string
  number: number
  gender?: string
  is_pair?: boolean
  member_names?: string[]
  member_genders?: string[]
  region?: string
  image: string
  scores: Record<string, number>
  displayScores?: Record<string, number>
  displayTotal?: number
  weightedRawTotal?: number
  totalScore: number
  totalRankSum?: number
  weightedRankAvg?: number
  perRoundRanks?: Record<string, number>
  judgeRanks?: Record<string, { scores: number[], ranks: number[], details: Array<{ judge_id: number, judge_name: string, score: number, rank: number }> }>
  qualified?: boolean
  qualification_cutoff?: number | null
  inheritanceBreakdown?: Record<string, InheritanceBreakdownItem>
}

interface Props {
  title: string
  contestants: Contestant[]
  rounds: Round[]
  isUpdating?: boolean
  numberOfWinners?: number
  showWinners?: boolean
  rankingMethod?: 'score_average' | 'rank_sum' | 'ordinal'
  hideRankColumn?: boolean
  isLastFinalRound?: boolean
  finalScoreMode?: 'fresh' | 'inherit'
}

const props = withDefaults(defineProps<Props>(), {
  isUpdating: false,
  numberOfWinners: 3,
  showWinners: false,
  rankingMethod: 'score_average',
  hideRankColumn: false,
  isLastFinalRound: false,
  finalScoreMode: 'fresh'
})

// Track previous rankings for animation
const previousRankMap = ref<Map<number, number>>(new Map())

// Track expanded score breakdowns
const expandedBreakdowns = ref<Set<number>>(new Set())

// Toggle score breakdown visibility
const toggleScoreBreakdown = (contestantId: number) => {
  if (expandedBreakdowns.value.has(contestantId)) {
    expandedBreakdowns.value.delete(contestantId)
  } else {
    expandedBreakdowns.value.add(contestantId)
  }
}

// Get the final round name
const getFinalRoundName = (): string | null => {
  const finalRound = props.rounds.find(r => r.type?.toLowerCase() === 'final')
  return finalRound?.name || null
}

// Check if contestant has participated in the final round
// Returns true if they have ANY score entry (including 0) in the final round
const hasValidFinalScore = (contestant: Contestant): boolean => {
  const finalRoundName = getFinalRoundName()
  if (!finalRoundName) return false
  
  // Check if the contestant has a score entry for the final round
  // Using 'in' operator to check if the key exists, regardless of value
  if (!contestant.scores) return false
  
  return finalRoundName in contestant.scores
}

// Get the rank of a contestant among only those who competed in the final round
const getFinalRankAmongFinalists = (contestant: Contestant): number => {
  if (!hasValidFinalScore(contestant)) return -1
  
  const finalRoundName = getFinalRoundName()
  if (!finalRoundName) return -1
  
  // Get all contestants who have a final round score
  const finalists = rankedContestants.value.filter(c => hasValidFinalScore(c))
  
  // Find the position of this contestant among finalists
  const position = finalists.findIndex(c => c.id === contestant.id)
  return position >= 0 ? position + 1 : -1
}

// Get final score breakdown tooltip
const getFinalScoreBreakdown = (contestant: Contestant): string => {
  const finalRoundName = getFinalRoundName()
  
  // For rank_sum method, show rank information instead of scores
  if (props.rankingMethod === 'rank_sum') {
    const rankSum = formatScore((contestant as any).totalRankSum, 1)
    const displayScore = formatScore((contestant as any).displayTotal ?? (contestant as any).displayScore)
    return `Rank Sum: ${rankSum} (lower is better)\\nScore: ${displayScore}`
  }
  
  if (!finalRoundName || !contestant.judgeRanks || !contestant.judgeRanks[finalRoundName]) {
    return `Final Score: ${formatScore(contestant.totalScore)}`
  }
  
  const judgeData = contestant.judgeRanks[finalRoundName]
  if (!judgeData.details || judgeData.details.length === 0) {
    return `Final Score: ${formatScore(contestant.totalScore)}`
  }
  
  const judgeScores = judgeData.details.map(d => formatScore(d.score)).join(', ')
  const average = formatScore(contestant.totalScore)
  return `Judge Scores: [${judgeScores}]\\nAverage: ${average}`
}

// Helper function to safely convert values to numbers
const toNumber = (value: unknown): number | null => {
  if (value === null || value === undefined) {
    return null
  }
  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : null
  }
  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (trimmed.length === 0) {
      return null
    }
    const parsed = Number(trimmed)
    return Number.isFinite(parsed) ? parsed : null
  }
  return null
}

// Helper function to check if a score is valid (not null, undefined, or 0)
const hasValidScore = (score: unknown): boolean => {
  if (score === null || score === undefined) return false
  const numScore = toNumber(score)
  return numScore !== null && numScore > 0
}

// Check if contestant has a tie with another finalist (for tie-breaker display)
// For rank_sum method: check if rank sums are tied
// For score_average method: check if total scores are tied
const hasScoreTie = (contestant: Contestant): boolean => {
  if (!hasValidFinalScore(contestant)) return false
  
  // For rank_sum method, check if rank sums are tied
  if (props.rankingMethod === 'rank_sum') {
    const contestantRankSum = contestant.totalRankSum
    if (contestantRankSum === null || contestantRankSum === undefined) return false
    
    // Find other finalists with the same rank sum
    const finalistsWithSameRankSum = props.contestants.filter(c => {
      if (c.id === contestant.id) return false
      if (!hasValidFinalScore(c)) return false
      return c.totalRankSum === contestantRankSum
    })
    
    return finalistsWithSameRankSum.length > 0
  }
  
  // For score_average method, check total scores
  const contestantScore = contestant.totalScore
  if (contestantScore === null || contestantScore === undefined) return false
  
  // Find other finalists with the same score
  const finalistsWithSameScore = props.contestants.filter(c => {
    if (c.id === contestant.id) return false
    if (!hasValidFinalScore(c)) return false
    return c.totalScore === contestantScore
  })
  
  return finalistsWithSameScore.length > 0
}

// Get display score (sum of judge totals) for a contestant in a round
// Falls back to regular score if displayScores not available
const getDisplayScore = (contestant: Contestant, roundName: string): number | null => {
  // Use displayScores if available (sum of judge totals for display)
  if (contestant.displayScores && contestant.displayScores[roundName] !== undefined) {
    return contestant.displayScores[roundName]
  }
  // Fallback to regular scores (for backwards compatibility)
  if (contestant.scores && contestant.scores[roundName] !== undefined) {
    return contestant.scores[roundName]
  }
  return null
}

// Get display total (sum of all judge totals across all rounds)
const getDisplayTotal = (contestant: Contestant): number | null => {
  if (contestant.displayTotal !== undefined && contestant.displayTotal !== null) {
    return contestant.displayTotal
  }
  // Fallback to regular totalScore
  return contestant.totalScore ?? null
}

// Get weighted raw total (sum of score × round weight) for inherit mode
const getWeightedRawTotal = (contestant: Contestant): number | null => {
  if ((contestant as any).weightedRawTotal !== undefined && (contestant as any).weightedRawTotal !== null) {
    return (contestant as any).weightedRawTotal
  }
  // Fallback to displayTotal if weightedRawTotal not available
  return getDisplayTotal(contestant)
}

// Calculate colspan for breakdown/cutoff rows (accounts for Total Raw Score column in inherit mode)
const getColspanForBreakdown = (): number => {
  // Base: Contestant + Rounds + Final Result = 2 + rounds.length + 1 = rounds.length + 3
  // With Rank column: add 1 more
  // With Total Raw Score column (inherit mode + last final round): add 1 more
  let colspan = props.rounds.length + 2 // Contestant + Rounds + Final Result
  if (!props.hideRankColumn) {
    colspan += 1 // Add Rank column
  }
  if (props.finalScoreMode === 'inherit' && props.isLastFinalRound) {
    colspan += 1 // Add Total Raw Score column
  }
  return colspan
}

const rankedContestants = computed(() => {
  return [...props.contestants].sort((a, b) => {
    if (props.rankingMethod === 'rank_sum') {
      // Use weightedRankAvg (Excel formula) - lower is better
      const aWeighted = toNumber((a as any).weightedRankAvg) ?? toNumber(a.totalRankSum) ?? Infinity
      const bWeighted = toNumber((b as any).weightedRankAvg) ?? toNumber(b.totalRankSum) ?? Infinity
      return aWeighted - bWeighted
    }
    // Higher score is better
    const aTotal = toNumber(a.totalScore) ?? 0
    const bTotal = toNumber(b.totalScore) ?? 0
    return bTotal - aTotal
  })
})

// Watch for ranking changes and update previous map
watch(
  () => rankedContestants.value,
  (newRanked, oldRanked) => {
    if (oldRanked && oldRanked.length > 0) {
      // Store old rankings before update
      const oldMap = new Map<number, number>()
      oldRanked.forEach((c, idx) => {
        oldMap.set(c.id, idx + 1)
      })
      previousRankMap.value = oldMap
    }
  },
  { deep: true }
)

// Helper function to determine if cutoff line should be shown after this contestant
const shouldShowCutoffLine = (index: number): boolean => {
  const currentContestant = rankedContestants.value[index]
  const nextContestant = rankedContestants.value[index + 1]
  
  // Only show cutoff line if there's a qualification cutoff defined
  if (!currentContestant.qualification_cutoff) {
    return false
  }
  
  // Show line after the last qualified contestant (before first non-qualified)
  return !!(currentContestant.qualified && nextContestant && !nextContestant.qualified)
}

// Helper function to get a contestant's rank at a specific round
// Uses Excel formula: weight applied to RANK, not score
const getRankAtRound = (contestant: Contestant, roundIndex: number): number => {
  const targetRound = props.rounds[roundIndex]
  
  // Get all rounds of the SAME TYPE up to and including this round
  const roundsOfSameType = props.rounds
    .slice(0, roundIndex + 1)
    .filter(r => r.type === targetRound.type)
  
  const useRankSum = props.rankingMethod === 'rank_sum'
  
  if (useRankSum) {
    // Excel formula implementation:
    // 1. For each round, get rank sum (sum of judge ranks)
    // 2. Rank contestants within each round by rank sum
    // 3. Apply weight to RANK: (rank/100) * weight%
    // 4. Average weighted ranks
    
    // Step 1: Calculate rank sums per round for all contestants
    const roundRankSums: Record<string, Record<number, number>> = {}
    for (const round of roundsOfSameType) {
      roundRankSums[round.name] = {}
      for (const c of props.contestants) {
        if (c.judgeRanks && c.judgeRanks[round.name]) {
          const ranks = c.judgeRanks[round.name].ranks || []
          roundRankSums[round.name][c.id] = ranks.reduce((sum, rank) => sum + rank, 0)
        }
      }
    }
    
    // Step 2: Calculate per-round rank for each contestant
    const roundRanks: Record<string, Record<number, number>> = {}
    for (const round of roundsOfSameType) {
      roundRanks[round.name] = {}
      const allRankSums = Object.values(roundRankSums[round.name])
      for (const [contestantId, rankSum] of Object.entries(roundRankSums[round.name])) {
        // RANK.AVG equivalent - lower rank sum gets better (lower) rank
        const betterCount = allRankSums.filter(rs => rs < rankSum).length
        const tieCount = allRankSums.filter(rs => Math.abs(rs - rankSum) < 0.0001).length
        const rank = betterCount + 1 + (tieCount - 1) / 2
        roundRanks[round.name][Number(contestantId)] = rank
      }
    }
    
    // Step 3 & 4: Calculate weighted rank average for all contestants
    const contestantsWithWeightedAvg = props.contestants.map(c => {
      let weightedRankSum = 0
      let roundCount = 0
      
      for (const round of roundsOfSameType) {
        const roundRank = roundRanks[round.name]?.[c.id]
        if (roundRank !== undefined) {
          const weight = round.weight || 1
          // Excel formula: (rank / 100) * weight%
          weightedRankSum += (roundRank / 100) * weight
          roundCount++
        }
      }
      
      const weightedAvg = roundCount > 0 ? weightedRankSum / roundCount : Infinity
      return { id: c.id, weightedAvg }
    })
    
    // Sort by weighted average (lower is better)
    contestantsWithWeightedAvg.sort((a, b) => a.weightedAvg - b.weightedAvg)
    
    // Find the rank of current contestant
    const rank = contestantsWithWeightedAvg.findIndex(c => c.id === contestant.id) + 1
    return rank
    
  } else {
    // For score average: sum up scores from rounds of same type
    const contestantsWithMetrics = props.contestants.map(c => {
      let metric = 0
      for (const round of roundsOfSameType) {
        const score = c.scores[round.name]
        if (score !== undefined) {
          metric += score
        }
      }
      return { id: c.id, metric }
    })
    
    // Sort descending (higher is better)
    contestantsWithMetrics.sort((a, b) => b.metric - a.metric)
    
    const rank = contestantsWithMetrics.findIndex(c => c.id === contestant.id) + 1
    return rank
  }
}

// Get contestant's rank within a specific round based on that round's score only
const getRankInRound = (contestant: Contestant, round: Round): number => {
  // Get all contestants who have a score in this specific round
  const contestantsWithScore = props.contestants
    .filter(c => c.scores[round.name] !== undefined && c.scores[round.name] !== null)
    .map(c => ({
      id: c.id,
      score: c.scores[round.name]
    }))
    .sort((a, b) => b.score - a.score) // Sort descending by score
  
  // Find the rank of the current contestant
  const rankIndex = contestantsWithScore.findIndex(c => c.id === contestant.id)
  return rankIndex >= 0 ? rankIndex + 1 : -1
}

const getRankChange = (contestantId: number, currentRank: number): 'up' | 'down' | 'same' | 'new' => {
  const prevRank = previousRankMap.value.get(contestantId)
  
  if (prevRank === undefined) {
    return 'new'
  }
  
  if (prevRank > currentRank) {
    return 'up' // Moved to a better rank (lower number)
  } else if (prevRank < currentRank) {
    return 'down' // Moved to a worse rank (higher number)
  }
  
  return 'same'
}

const getRankDisplay = (rank: number): string => {
  if (props.showWinners && rank <= props.numberOfWinners) {
    // Show winning medals for final rounds
    if (rank === 1) return '👑'
    if (rank === 2) return '🥈'
    if (rank === 3) return '🥉'
    // For 4th place and beyond, show trophy
    return '🏆'
  }
  // For non-final rounds (advancing), show checkmark/advancing indicator
  if (rank <= 3) {
    return '✓'
  }
  return rank.toString()
}

const getRankBadgeClass = (rank: number, qualified?: boolean): string => {
  const isQualified = qualified !== false
  const isWinner = props.showWinners && rank <= props.numberOfWinners
  
  if (isWinner) {
    // Final round - show winning badges with gold/silver/bronze
    switch (rank) {
      case 1:
        return 'bg-yellow-100 text-yellow-900 border-2 border-yellow-400 shadow-lg ring-2 ring-yellow-200'
      case 2:
        return 'bg-gray-100 text-gray-800 border-2 border-gray-400 shadow-lg ring-2 ring-gray-200'
      case 3:
        return 'bg-orange-100 text-orange-800 border-2 border-orange-400 shadow-lg ring-2 ring-orange-200'
      default:
        // For 4th, 5th, etc. when numberOfWinners > 3
        return 'bg-purple-100 text-purple-800 border-2 border-purple-400 shadow-md ring-2 ring-purple-200'
    }
  }
  
  // Non-final rounds - show advancing badges with green/emerald for qualifiers
  switch (rank) {
    case 1:
      return isQualified ? 'bg-emerald-100 text-emerald-900 border-2 border-emerald-400 shadow-md ring-2 ring-emerald-200' : 'bg-slate-50 text-slate-500 border-2 border-slate-200'
    case 2:
      return isQualified ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300 shadow-sm ring-2 ring-emerald-100' : 'bg-slate-50 text-slate-500 border-2 border-slate-200'
    case 3:
      return isQualified ? 'bg-emerald-50 text-emerald-700 border-2 border-emerald-200 shadow-sm ring-2 ring-emerald-100' : 'bg-slate-50 text-slate-500 border-2 border-slate-200'
    default:
      if (!isQualified) {
        return 'bg-slate-50 text-slate-500 border-2 border-slate-200'
      }
      return 'bg-emerald-50 text-emerald-700 border-2 border-emerald-200 shadow-sm ring-1 ring-emerald-100'
  }
}

const getScoreClass = (score: number | null | undefined): string => {
  const n = typeof score === 'number' && Number.isFinite(score) ? score : 0
  if (n >= 95) {
    return 'text-teal-700'
  }
  if (n >= 90) {
    return 'text-teal-600'
  }
  if (n >= 85) {
    return 'text-teal-500'
  }
  return 'text-slate-700'
}

// Helper function to get round type color scheme
const getRoundTypeColors = (type?: string): { header: string, cell: string, border: string } => {
  const normalizedType = type?.toLowerCase()
  
  switch (normalizedType) {
    case 'preliminary':
    case 'prelim':
      return {
        header: 'bg-blue-100 text-blue-800 border-blue-300',
        cell: 'bg-blue-50/30 border-blue-200',
        border: 'border-blue-300'
      }
    case 'semi-final':
    case 'semifinal':
    case 'semi':
      return {
        header: 'bg-purple-100 text-purple-800 border-purple-300',
        cell: 'bg-purple-50/30 border-purple-200',
        border: 'border-purple-300'
      }
    case 'final':
    case 'finals':
      return {
        header: 'bg-amber-100 text-amber-800 border-amber-300',
        cell: 'bg-amber-50/30 border-amber-200',
        border: 'border-amber-300'
      }
    default:
      return {
        header: 'bg-gray-100 text-gray-700 border-gray-300',
        cell: 'bg-gray-50/30 border-gray-200',
        border: 'border-gray-300'
      }
  }
}

// Helper to determine if this is the first round of a new type (for grouping)
const isFirstOfRoundType = (roundIndex: number): boolean => {
  if (roundIndex === 0) return true
  const currentType = props.rounds[roundIndex]?.type
  const previousType = props.rounds[roundIndex - 1]?.type
  return currentType !== previousType
}

// Get header class for round columns with grouping
const getRoundHeaderClass = (round: Round, roundIndex: number): string => {
  const colors = getRoundTypeColors(round.type)
  const isFirst = isFirstOfRoundType(roundIndex)
  return `${colors.header} ${isFirst ? 'border-l-4' : ''} ${colors.border}`
}

// Get cell class for round score cells with grouping
const getRoundCellClass = (round: Round, roundIndex: number): string => {
  const colors = getRoundTypeColors(round.type)
  const isFirst = isFirstOfRoundType(roundIndex)
  return `${colors.cell} ${isFirst ? 'border-l-4' : ''} ${colors.border}`
}


const formatScore = (value: unknown, decimals = 2, empty = '-'): string => {
  const n = toNumber(value)
  if (n === null) {
    return empty
  }
  return n.toFixed(decimals)
}

// Get judge breakdown for a contestant in the final round
const getJudgeBreakdown = (contestant: Contestant) => {
  const finalRoundName = getFinalRoundName()
  if (!finalRoundName || !contestant.judgeRanks || !contestant.judgeRanks[finalRoundName]) {
    return []
  }
  
  const judgeData = contestant.judgeRanks[finalRoundName]
  if (!judgeData.details || judgeData.details.length === 0) {
    return []
  }
  
  return judgeData.details.map(d => ({
    id: d.judge_id,
    name: d.judge_name || `Judge ${d.judge_id}`,
    initials: getInitials(d.judge_name || `Judge ${d.judge_id}`),
    score: d.score
  }))
}

// Get initials from a name
const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('')
}

// Check if this round should show advancement badges
// Show badge if the round has top_n_proceed set (backend only sets this for last round of each type)
const shouldShowAdvancementBadge = (roundIndex: number): boolean => {
  const currentRound = props.rounds[roundIndex]
  
  // Don't show advancement for final rounds (they show winner badges instead)
  if (currentRound.type?.toLowerCase() === 'final') {
    return false
  }
  
  // Show if this round has top_n_proceed set (backend sets this only for last round of each type)
  return !!(currentRound.top_n_proceed && currentRound.top_n_proceed > 0)
}
</script>

<style scoped>
@keyframes rank-up {
  0% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
    background-color: rgba(34, 197, 94, 0.1);
  }
  100% {
    transform: translateY(0);
  }
}

@keyframes rank-down {
  0% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(8px);
    background-color: rgba(239, 68, 68, 0.1);
  }
  100% {
    transform: translateY(0);
  }
}

@keyframes pulse-subtle {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
    background-color: rgba(20, 184, 166, 0.05);
  }
}

.animate-rank-up {
  animation: rank-up 0.6s ease-out;
}

.animate-rank-down {
  animation: rank-down 0.6s ease-out;
}

.animate-pulse-subtle {
  animation: pulse-subtle 1s ease-in-out;
}

.rank-indicator-enter-active,
.rank-indicator-leave-active {
  transition: all 0.3s ease;
}

.rank-indicator-enter-from {
  opacity: 0;
  transform: scale(0) rotate(-180deg);
}

.rank-indicator-leave-to {
  opacity: 0;
  transform: scale(0) rotate(180deg);
}
</style>

```
