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
            <!-- <th
              v-if="!hideRankColumn"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Rank
            </th> -->
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
            <!-- Total Raw Score column - only shown in inherit mode for score-based ranking (not in round view) -->
            <th
              v-if="!isRankSumMethod && finalScoreMode === 'inherit' && isLastFinalRound && !isRoundView"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-emerald-700 bg-emerald-50"
            >
              <div class="flex flex-col items-center gap-1">
                <span>Total Raw Score</span>
                <span class="text-[9px] font-medium opacity-75">WEIGHTED</span>
              </div>
            </th>
            <th
              v-if="isRankSumMethod && !isRoundView"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Total Rank
            </th>
            <th
              v-if="isRankSumMethod && !isRoundView"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Average Rank
            </th>
            <th
              v-if="isRankSumMethod && !isRoundView"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Final Rank
            </th>
            <th
              v-else-if="!isRoundView"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              <span v-if="isLastFinalRound">Final Result (Top {{ numberOfWinners }})</span>
              <span v-else-if="isScoreAverageMethod">Total Score</span>
              <span v-else>Total</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <template v-for="contestant in displayContestants" :key="contestant.id">
            <tr
              class="hover:bg-gray-50/80 transition-all duration-500 ease-out"
              :class="[
                {
                  'bg-emerald-50/40 border-l-4 border-l-emerald-500': !hideRankColumn && contestant.qualified && contestant.qualification_cutoff,
                  'opacity-50 border-l-4 border-l-slate-300': !hideRankColumn && !contestant.qualified && contestant.qualification_cutoff !== null && contestant.qualification_cutoff !== undefined,
                  'animate-rank-up': getRankChange(contestant.id, getRankPosition(contestant.id)) === 'up',
                  'animate-rank-down': getRankChange(contestant.id, getRankPosition(contestant.id)) === 'down',
                  'animate-pulse-subtle': isUpdating && getRankChange(contestant.id, getRankPosition(contestant.id)) === 'same'
                }
              ]"
            >
            <!-- Rank -->
            <td v-if="!hideRankColumn" class="whitespace-nowrap px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <div class="relative">
                  <!-- <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold transition-all duration-300"
                    :class="getRankBadgeClass(getRankPosition(contestant.id), contestant.qualified)"
                  >
                  
                    <span v-if="hasTiedRank(contestant.id)" class="mr-1">Tied</span>
                     <span class="mr-1 tabular-nums">{{ getOrdinalRank(getRankPosition(contestant.id)) }}</span> -->
                    <!-- <span v-if="showWinners && getRankPosition(contestant.id) <= numberOfWinners">{{ getRankDisplay(getRankPosition(contestant.id)) }}</span>
                    <span v-else-if="!showWinners && getRankPosition(contestant.id) <= 3">{{ getRankDisplay(getRankPosition(contestant.id)) }}</span> 
                  </span> -->
                  <!-- Rank change indicator -->
                  <transition name="rank-indicator">
                    <span
                      v-if="isUpdating && getRankChange(contestant.id, getRankPosition(contestant.id)) === 'up'"
                      class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-green-500 text-white text-[10px] font-bold shadow-lg"
                    >
                      ↑
                    </span>
                    <span
                      v-else-if="isUpdating && getRankChange(contestant.id, getRankPosition(contestant.id)) === 'down'"
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
                <!-- Rank Sum (for rank_sum) or Score (score_average) -->
                <span
                  v-if="isRankSumMethod && shouldShowRoundRank && getRoundAverageRankPlacement(contestant, round.name) !== null"
                  class="text-sm font-semibold tabular-nums"
                  :title="`Average rank: ${formatScore(getRoundAverageRank(contestant, round.name), 2)}`"
                >
                  {{ formatPlacementValue(getRoundAverageRankPlacement(contestant, round.name)) }}
                </span>
                <span v-else-if="isRankSumMethod && getRoundAverageRank(contestant, round.name) !== null" class="text-sm font-medium tabular-nums">
                  {{ formatScore(getRoundAverageRank(contestant, round.name), 2) }}
                </span>
                <span v-else-if="!isRankSumMethod && hasValidScore(getDisplayScore(contestant, round.name))" class="text-sm font-medium tabular-nums">
                  {{ formatScore(getDisplayScore(contestant, round.name)) }}
                </span>
                <span v-else-if="contestant.scores[round.name] === 0" class="text-gray-300 italic text-sm" title="Did not compete in this round">—</span>
                <span v-else class="text-gray-300 italic text-sm">—</span>
                
                <!-- Advancement Badge for this round -->
                <template v-if="hasValidScore(contestant.scores[round.name])">
                  <!-- Show "Finalist" badge for final round (both fresh and inherit modes) -->
                  <!-- Ranking is shown in the Final Result column to avoid redundancy -->
                  <span
                    v-if="round.type?.toLowerCase() === 'final'"
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

            <!-- Total Raw Score column - only shown in inherit mode for score-based ranking (not in round view) -->
            <td 
              v-if="!isRankSumMethod && finalScoreMode === 'inherit' && isLastFinalRound && !isRoundView"
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
            <td v-if="isRankSumMethod && !isRoundView" class="whitespace-nowrap px-4 py-3 text-right">
              <span v-if="shouldShowRankStats(contestant) && getTotalAverageRankSum(contestant) !== null" class="text-sm font-semibold tabular-nums text-slate-700">
                {{ formatScore(getTotalAverageRankSum(contestant), 2) }}
              </span>
              <span v-else class="text-gray-300 italic text-sm">—</span>
            </td>
            <td v-if="isRankSumMethod && !isRoundView" class="whitespace-nowrap px-4 py-3 text-right">
              <span v-if="shouldShowRankStats(contestant) && getAverageRank(contestant) !== null" class="text-sm font-semibold tabular-nums text-slate-700">
                {{ formatScore(getAverageRank(contestant), 2) }}
              </span>
              <span v-else class="text-gray-300 italic text-sm">—</span>
            </td>
            <td v-if="isRankSumMethod && !isRoundView" class="whitespace-nowrap px-4 py-3 text-right">
              <div class="flex items-center justify-end">
                <span
                  v-if="isLastFinalRound && showWinners && hasValidFinalScore(contestant) && getFinalRankAmongFinalists(contestant) <= numberOfWinners"
                  class="inline-flex items-center gap-0.5 rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-semibold text-white border border-amber-600 whitespace-nowrap"
                  :title="hasScoreTie(contestant) ? `Tied - Top ${getFinalRankAmongFinalists(contestant)}` : `Winner - Top ${numberOfWinners}`"
                >
                  <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  <span v-if="hasScoreTie(contestant)" class="mr-1">Tied</span>
                  <span>Top {{ getFinalRankAmongFinalists(contestant) }}</span>
                </span>
                <span v-else class="text-gray-300 italic text-sm">—</span>
              </div>
            </td>
            <td v-else-if="!isRoundView" class="whitespace-nowrap px-4 py-3 text-right">
              <div class="flex flex-col items-end gap-1">
                <div class="flex items-center gap-2">
                  <!-- Score Average method: show computed score (finalScore for inherit mode, displayTotal otherwise) -->
                  <!-- In inherit mode, show score only when there's a tie (for tie-breaking purposes) -->
                  <span
                    v-if="finalScoreMode !== 'inherit' || !isLastFinalRound"
                    class="text-sm font-semibold tabular-nums"
                    :class="getScoreClass(getNonRankSumTotal(contestant))"
                    :title="getFinalScoreBreakdown(contestant)"
                  >
                    {{ formatScore(getNonRankSumTotal(contestant)) }}
                  </span>
                  <!-- Inherit mode: only show score for tie-breaker display -->
                  <span
                    v-else-if="hasScoreTie(contestant)"
                    class="text-sm font-semibold tabular-nums text-emerald-600"
                    :title="`Tie-breaker score: ${formatScore(getNonRankSumTotal(contestant))}`"
                  >
                    {{ formatScore(getNonRankSumTotal(contestant)) }}
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
                    :title="hasScoreTie(contestant) ? `Tied - Top ${getFinalRankAmongFinalists(contestant)}` : `Winner - Top ${numberOfWinners}`"
                  >
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span v-if="hasScoreTie(contestant)" class="mr-1">Tied</span>
                    <span>Top {{ getFinalRankAmongFinalists(contestant) }}</span>
                  </span>
                </div>
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
          <!-- <tr 
            v-if="!hideRankColumn && shouldShowCutoffLine(contestant)"
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
          </tr> -->
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
import { computeTiedRankMap } from '@/utils/ranking'

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
  isRoundView?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isUpdating: false,
  numberOfWinners: 3,
  showWinners: false,
  rankingMethod: 'score_average',
  hideRankColumn: false,
  isLastFinalRound: false,
  finalScoreMode: 'fresh',
  isRoundView: false
})

const isRankSumMethod = computed(() => props.rankingMethod === 'rank_sum')
const isScoreAverageMethod = computed(() => props.rankingMethod === 'score_average')
const shouldShowRoundRank = computed(() => isRankSumMethod.value)
const isRoundView = computed(() => props.isRoundView)

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
// const getFinalRoundName = (): string | null => {
//   const finalRound = props.rounds.find(r => r.type?.toLowerCase() === 'final')
//   return finalRound?.name || null
// }

const getFinalRoundName = (): string | null => {
  const finalRounds = props.rounds.filter(round => round.type?.toLowerCase() === 'final')
  if (finalRounds.length === 0) return null
  return finalRounds[finalRounds.length - 1].name
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

const shouldShowRankStats = (contestant: Contestant): boolean => {
  // For final rounds (Overall Tally view), only show rank stats for finalists
  if (props.isLastFinalRound) {
    return hasValidFinalScore(contestant)
  }
  
  // For non-final rounds, show stats if no qualification cutoff is set
  if (contestant.qualification_cutoff === null || contestant.qualification_cutoff === undefined) {
    return true
  }

  if (contestant.qualified === true) {
    return true
  }

  return hasValidFinalScore(contestant)
}

// Get the rank of a contestant among only those who competed in the final round
const getFinalRankAmongFinalists = (contestant: Contestant): number => {
  if (!hasValidFinalScore(contestant)) return -1

  return finalistRankMap.value.get(contestant.id) ?? -1
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
    return `Final Score: ${formatScore(getNonRankSumTotal(contestant))}`
  }
  
  const judgeData = contestant.judgeRanks[finalRoundName]
  if (!judgeData.details || judgeData.details.length === 0) {
    return `Final Score: ${formatScore(getNonRankSumTotal(contestant))}`
  }
  
  const judgeScores = judgeData.details.map(d => formatScore(d.score)).join(', ')
  const average = formatScore(getNonRankSumTotal(contestant))
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

  const rank = finalistRankMap.value.get(contestant.id)
  if (!rank) return false

  let count = 0
  finalistRankMap.value.forEach((value) => {
    if (value === rank) {
      count += 1
    }
  })

  return count > 1
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

const getScoreAverageTotal = (contestant: Contestant): number => {
  const scores = contestant.scores ?? {}

  if (props.finalScoreMode === 'inherit') {
    const sum = Object.values(scores).reduce((total, score) => {
      const numeric = toNumber(score)
      return numeric !== null ? total + numeric : total
    }, 0)

    if (sum > 0 || Object.keys(scores).length > 0) {
      return sum
    }
  }

  const finalRoundName = getFinalRoundName()
  if (finalRoundName && scores[finalRoundName] !== undefined) {
    return toNumber(scores[finalRoundName]) ?? 0
  }

  return toNumber(contestant.totalScore) ?? 0
}

const getNonRankSumTotal = (contestant: Contestant): number => {
  if (isScoreAverageMethod.value) {
    return getScoreAverageTotal(contestant)
  }

  return getDisplayTotal(contestant) ?? 0
}

// const getFinalRoundName = (): string | null => {
//   const finalRounds = props.rounds.filter(round => round.type?.toLowerCase() === 'final')
//   if (finalRounds.length === 0) return null
//   return finalRounds[finalRounds.length - 1].name
// }

const rankSumRounds = computed(() => {
  if (!isRankSumMethod.value) return []
  if (props.finalScoreMode === 'fresh') {
    const finalRoundName = getFinalRoundName()
    if (!finalRoundName) return props.rounds
    return props.rounds.filter(round => round.name === finalRoundName)
  }
  return props.rounds
})

const roundAverageRankMap = computed(() => {
  const map = new Map<string, Map<number, number>>()

  if (!isRankSumMethod.value || props.rounds.length === 0 || props.contestants.length === 0) {
    return map
  }

  props.rounds.forEach(round => {
    const roundName = round.name
    const judgeScores = new Map<number, Array<{ contestantId: number; score: number }>>()

    props.contestants.forEach(contestant => {
      const details = contestant.judgeRanks?.[roundName]?.details
      if (!details || details.length === 0) return

      details.forEach(detail => {
        const score = typeof detail.score === 'number' ? detail.score : Number(detail.score)
        if (!Number.isFinite(score)) return

        const entries = judgeScores.get(detail.judge_id) ?? []
        entries.push({ contestantId: contestant.id, score })
        judgeScores.set(detail.judge_id, entries)
      })
    })

    const roundRanks = new Map<number, { sum: number; count: number }>()

    judgeScores.forEach(entries => {
      const sorted = [...entries].sort((a, b) => b.score - a.score)
      let index = 0
      let betterCount = 0

      while (index < sorted.length) {
        const currentScore = sorted[index].score
        const sameScoreGroup: typeof sorted = []
        while (index < sorted.length && sorted[index].score === currentScore) {
          sameScoreGroup.push(sorted[index])
          index += 1
        }

        const rank = betterCount + 1
        sameScoreGroup.forEach(entry => {
          const current = roundRanks.get(entry.contestantId) ?? { sum: 0, count: 0 }
          roundRanks.set(entry.contestantId, {
            sum: current.sum + rank,
            count: current.count + 1
          })
        })

        betterCount += sameScoreGroup.length
      }
    })

    const roundMap = new Map<number, number>()
    roundRanks.forEach((value, contestantId) => {
      if (value.count > 0) {
        roundMap.set(contestantId, Number((value.sum / value.count).toFixed(2)))
      }
    })

    map.set(roundName, roundMap)
  })

  return map
})

const roundAverageRankPlacementMap = computed(() => {
  const map = new Map<string, Map<number, number>>()

  if (!isRankSumMethod.value || props.rounds.length === 0 || props.contestants.length === 0) {
    return map
  }

  props.rounds.forEach(round => {
    const averageRanks = roundAverageRankMap.value.get(round.name)
    if (!averageRanks || averageRanks.size === 0) {
      return
    }

    const scoreBuckets = new Map<string, number[]>()
    averageRanks.forEach((averageRank, contestantId) => {
      const key = averageRank.toFixed(6)
      if (!scoreBuckets.has(key)) {
        scoreBuckets.set(key, [])
      }
      scoreBuckets.get(key)?.push(contestantId)
    })

    const sortedScores = Array.from(scoreBuckets.keys())
      .map(key => ({ key, value: Number(key) }))
      .sort((a, b) => a.value - b.value)

    let currentRank = 1
    const roundPlacements = new Map<number, number>()

    sortedScores.forEach(({ key }) => {
      const contestantsWithScore = scoreBuckets.get(key) ?? []
      const startRank = currentRank
      const endRank = currentRank + contestantsWithScore.length - 1
      const placement = (startRank + endRank) / 2

      contestantsWithScore.forEach(contestantId => {
        roundPlacements.set(contestantId, placement)
      })

      currentRank += contestantsWithScore.length
    })

    map.set(round.name, roundPlacements)
  })

  return map
})

const getRoundAverageRank = (contestant: Contestant, roundName: string): number | null => {
  if (!isRankSumMethod.value) return null
  return roundAverageRankMap.value.get(roundName)?.get(contestant.id) ?? null
}

const getRoundAverageRankPlacement = (contestant: Contestant, roundName: string): number | null => {
  if (!isRankSumMethod.value) return null
  return roundAverageRankPlacementMap.value.get(roundName)?.get(contestant.id) ?? null
}

const getRoundAverageCount = (contestant: Contestant): number => {
  if (!contestant.judgeRanks || rankSumRounds.value.length === 0) return 0
  return rankSumRounds.value.reduce((total, round) => {
    return getRoundAverageRankPlacement(contestant, round.name) !== null ? total + 1 : total
  }, 0)
}

const getTotalAverageRankSum = (contestant: Contestant): number | null => {
  if (!isRankSumMethod.value) return null
  if (!contestant.judgeRanks || rankSumRounds.value.length === 0) return null

  let sum = 0
  let hasAny = false

  rankSumRounds.value.forEach(round => {
    const roundPlacement = getRoundAverageRankPlacement(contestant, round.name)
    if (roundPlacement !== null) {
      sum += roundPlacement
      hasAny = true
    }
  })

  return hasAny ? Number(sum.toFixed(2)) : null
}

const getAverageRank = (contestant: Contestant): number | null => {
  if (!isRankSumMethod.value) return null
  const totalAverage = getTotalAverageRankSum(contestant)
  const roundCount = getRoundAverageCount(contestant)
  if (totalAverage === null || roundCount === 0) return null
  return Number((totalAverage / roundCount).toFixed(2))
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
  let colspan = props.rounds.length + 1 // Contestant + Rounds

  // Only add summary columns if not viewing a specific round
  if (!props.isRoundView) {
    if (isRankSumMethod.value) {
      colspan += 3 // Total Rank + Average Rank + Final Rank
    } else {
      colspan += 1 // Final Result column
    }
  }

  if (!props.hideRankColumn) {
    colspan += 1 // Add Rank column
  }

  if (!isRankSumMethod.value && props.finalScoreMode === 'inherit' && props.isLastFinalRound && !props.isRoundView) {
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
    const aTotal = getNonRankSumTotal(a)
    const bTotal = getNonRankSumTotal(b)
    return bTotal - aTotal
  })
})

const displayContestants = computed(() => {
  return [...props.contestants].sort((a, b) => {
    const aNumber = toNumber(a.number) ?? Infinity
    const bNumber = toNumber(b.number) ?? Infinity
    if (aNumber !== bNumber) {
      return aNumber - bNumber
    }
    return a.id - b.id
  })
})

const rankPositionMap = computed(() => {
  const map = new Map<number, number>()
  const contestants = rankedContestants.value
  
  let currentRank = 1
  let previousValue: number | null = null
  
  contestants.forEach((contestant, index) => {
    let currentValue: number
    
    if (props.rankingMethod === 'rank_sum') {
      currentValue = toNumber((contestant as any).weightedRankAvg) ?? toNumber(contestant.totalRankSum) ?? Infinity
    } else {
      currentValue = getNonRankSumTotal(contestant)
    }
    
    if (previousValue !== null && Math.abs(currentValue - previousValue) < 0.0001) {
      // Same value as previous contestant - assign same rank (tie)
      map.set(contestant.id, currentRank)
    } else {
      // Different value - assign new rank based on position
      currentRank = index + 1
      map.set(contestant.id, currentRank)
      previousValue = currentValue
    }
  })
  
  return map
})

const getFinalistRankValue = (contestant: Contestant): number => {
  if (props.rankingMethod === 'rank_sum') {
    const averageRank = getAverageRank(contestant)
    if (averageRank !== null) {
      return averageRank
    }

    return toNumber((contestant as any).weightedRankAvg) ?? toNumber(contestant.totalRankSum) ?? Infinity
  }

  return getNonRankSumTotal(contestant)
}

const finalistRankMap = computed(() => {
  const finalists = rankedContestants.value.filter(contestant => hasValidFinalScore(contestant))

  if (finalists.length === 0) {
    return new Map<number, number>()
  }

  const direction = props.rankingMethod === 'rank_sum' ? 'asc' : 'desc'

  return computeTiedRankMap(
    finalists,
    (contestant) => getFinalistRankValue(contestant),
    (contestant) => contestant.id,
    direction
  )
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
const shouldShowCutoffLine = (contestant: Contestant): boolean => {
  const cutoff = contestant.qualification_cutoff
  if (!cutoff || cutoff <= 0) {
    return false
  }

  const rank = getRankPosition(contestant.id)
  return rank === cutoff
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
  if (currentRank <= 0) {
    return 'same'
  }

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

const getRankPosition = (contestantId: number): number => {
  return rankPositionMap.value.get(contestantId) ?? -1
}

// Check if a contestant has a tied rank with another contestant
const hasTiedRank = (contestantId: number): boolean => {
  const position = getRankPosition(contestantId)
  if (position <= 0) return false
  
  // Count how many contestants have the same rank
  let count = 0
  rankPositionMap.value.forEach((rank) => {
    if (rank === position) count++
  })
  
  return count > 1
}

// Get ordinal suffix for a rank (1st, 2nd, 3rd, etc.)
const getOrdinalRank = (rank: number): string => {
  if (rank <= 0) return ''
  const j = rank % 10
  const k = rank % 100
  if (j === 1 && k !== 11) return rank + 'st'
  if (j === 2 && k !== 12) return rank + 'nd'
  if (j === 3 && k !== 13) return rank + 'rd'
  return rank + 'th'
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

const formatPlacementValue = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return '-'
  return Number(value.toFixed(2)).toString()
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
