<template>
  <div class="print-document bg-white text-black font-serif">
    <!-- Report Header - Show only if NOT a gender-split category (unified header shown in parent) -->
    <div v-if="!isMaleCategory && !isFemaleCategory" class="flex items-center gap-4 mb-8 pb-4 border-b-2 border-black">
      <!-- Logo -->
      <div v-if="getLogoUrl" class="flex-shrink-0">
        <img :src="getLogoUrl" alt="Pageant Logo" class="w-20 h-20 object-contain rounded-lg border border-gray-200" />
      </div>
      <!-- Header Content -->
      <div class="flex-1 text-center" :class="{ 'pr-20': getLogoUrl }">
        <h1 class="text-2xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
        <div class="text-sm uppercase tracking-widest text-gray-600 mb-4">Official Tabulation Report</div>
        
        <div class="flex justify-center items-center gap-8 text-xs text-gray-600">
          <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
          <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
        </div>
        
        <div class="mt-6">
          <h2 class="text-xl font-bold text-black">{{ reportTitle || 'Final Results' }}</h2>
          <div class="text-xs text-gray-500 mt-1">
            <span class="uppercase tracking-wide">Scoring Mode: </span>
            <span class="font-semibold">{{ finalScoreMode === 'fresh' ? 'Fresh Start' : 'Inherit Computation' }}</span>
            <span class="mx-2">•</span>
            <span class="uppercase tracking-wide">Ranking Method: </span>
            <span class="font-semibold">{{ rankingMethodLabel }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Winners Summary - Compact Layout for Gender Split (Pair Pageants) -->
    <div v-if="shouldShowPodium && (isMaleCategory || isFemaleCategory) && topThree.length > 0" class="mb-6">
      <div class="space-y-3">
        <!-- First Place / Winner -->
        <div v-if="topThree[0]" class="text-center">
          <div class="text-xs font-bold uppercase text-amber-700 mb-1">🏆 {{ getWinnerTitle(topThree[0]) }}</div>
          <div class="border-2 border-amber-500 p-3 rounded bg-amber-50">
            <div class="text-lg font-bold">#{{ topThree[0].number }}</div>
            <div class="text-sm font-semibold text-gray-800">{{ capitalizeName(topThree[0].name) }}</div>
            <div v-if="topThree[0]?.is_pair && topThree[0]?.member_names && topThree[0]?.member_names!.length > 0" class="text-[10px] text-gray-600 italic mt-0.5">
              {{ topThree[0].member_names?.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-xs font-bold text-amber-700 mt-1">
              {{ isRankSumMethod ? getRankDisplayValue(topThree[0], 0) : `${formatScore(getDisplayTotal(topThree[0]))} pts` }}
            </div>
          </div>
        </div>
        <!-- Runner-ups -->
        <div class="grid grid-cols-2 gap-2">
          <!-- 1st Runner-up -->
          <div v-if="topThree[1]" class="text-center">
            <div class="text-[10px] font-bold uppercase text-gray-600 mb-0.5">1st Runner-up</div>
            <div class="border border-gray-300 p-2 rounded bg-gray-50">
              <div class="text-base font-bold">#{{ topThree[1].number }}</div>
              <div class="text-xs font-semibold text-gray-700">{{ capitalizeName(topThree[1].name) }}</div>
              <div v-if="topThree[1]?.is_pair && topThree[1]?.member_names && topThree[1].member_names!.length > 0" class="text-[9px] text-gray-500 italic">
                {{ topThree[1].member_names?.map(n => capitalizeName(n)).join(' & ') }}
              </div>
              <div class="text-[10px] text-gray-600">
                {{ isRankSumMethod ? getRankDisplayValue(topThree[1], 1) : `${formatScore(getDisplayTotal(topThree[1]))} pts` }}
              </div>
            </div>
          </div>
          <!-- 2nd Runner-up -->
          <div v-if="topThree[2]" class="text-center">
            <div class="text-[10px] font-bold uppercase text-gray-600 mb-0.5">2nd Runner-up</div>
            <div class="border border-gray-300 p-2 rounded bg-gray-50">
              <div class="text-base font-bold">#{{ topThree[2].number }}</div>
              <div class="text-xs font-semibold text-gray-700">{{ capitalizeName(topThree[2].name) }}</div>
              <div v-if="topThree[2]?.is_pair && topThree[2]?.member_names && topThree[2]?.member_names!.length > 0" class="text-[9px] text-gray-500 italic">
                {{ topThree[2].member_names?.map(n => capitalizeName(n)).join(' & ') }}
              </div>
              <div class="text-[10px] text-gray-600">
                {{ isRankSumMethod ? getRankDisplayValue(topThree[2], 2) : `${formatScore(getDisplayTotal(topThree[2]))} pts` }}
              </div>
            </div>
          </div>
        </div>
        <!-- Additional Runner-ups (4th, 5th, etc.) -->
        <div v-if="topThree.length > 3" class="grid grid-cols-3 gap-2">
          <div v-for="(result, idx) in topThree.slice(3)" :key="result.id" class="text-center">
            <div class="text-[9px] font-bold uppercase text-gray-500 mb-0.5">{{ getOrdinalSuffix(idx + 3) }} Runner-up</div>
            <div class="border border-gray-200 p-1.5 rounded bg-gray-50">
              <div class="text-sm font-bold">#{{ result.number }}</div>
              <div class="text-[10px] font-medium text-gray-700">{{ capitalizeName(result.name) }}</div>
              <div class="text-[9px] text-gray-500">
                {{ isRankSumMethod ? getRankDisplayValue(result, idx + 3) : `${formatScore(getDisplayTotal(result))} pts` }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Winners Summary - Traditional Layout for Non-Split (Solo Pageants) -->
    <div v-if="shouldShowPodium && !isMaleCategory && !isFemaleCategory && topThree.length > 0" class="mb-8">
      <div class="grid grid-cols-3 gap-4 items-end">
        <!-- 1st Runner-up (2nd Place) -->
        <div v-if="topThree[1]" class="text-center pb-4">
          <div class="text-sm font-bold uppercase text-gray-600 mb-2">1st Runner-up</div>
          <div class="border-2 border-gray-400 p-4 rounded bg-gray-50">
            <div class="text-2xl font-bold mb-2">#{{ topThree[1].number }}</div>
            <div class="text-sm font-semibold text-gray-700 mb-1">{{ capitalizeName(topThree[1].name) }}</div>
            <div v-if="topThree[1].is_pair && topThree[1].member_names && topThree[1].member_names.length > 0" class="text-xs text-gray-600 italic mb-2">
              {{ topThree[1].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-sm font-semibold text-gray-700">
              {{ isRankSumMethod ? getRankDisplayValue(topThree[1], 1) : `${formatScore(getDisplayTotal(topThree[1]))} pts` }}
            </div>
          </div>
        </div>

        <!-- Winner (1st Place) -->
        <div v-if="topThree[0]" class="text-center">
          <div class="text-base font-bold uppercase text-amber-700 mb-2">🏆 {{ getWinnerTitle(topThree[0]) }}</div>
          <div class="border-4 border-amber-500 p-5 rounded bg-amber-50 shadow-lg">
            <div class="text-3xl font-bold mb-2">#{{ topThree[0].number }}</div>
            <div class="text-base font-bold text-gray-800 mb-1">{{ capitalizeName(topThree[0].name) }}</div>
            <div v-if="topThree[0].is_pair && topThree[0].member_names && topThree[0].member_names.length > 0" class="text-sm text-gray-700 italic mb-2">
              {{ topThree[0].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-base font-bold text-amber-700">
              {{ isRankSumMethod ? getRankDisplayValue(topThree[0], 0) : `${formatScore(getDisplayTotal(topThree[0]))} pts` }}
            </div>
          </div>
        </div>

        <!-- 2nd Runner-up (3rd Place) -->
        <div v-if="topThree[2]" class="text-center pb-4">
          <div class="text-sm font-bold uppercase text-gray-600 mb-2">2nd Runner-up</div>
          <div class="border-2 border-gray-400 p-4 rounded bg-gray-50">
            <div class="text-2xl font-bold mb-2">#{{ topThree[2].number }}</div>
            <div class="text-sm font-semibold text-gray-700 mb-1">{{ capitalizeName(topThree[2].name) }}</div>
            <div v-if="topThree[2].is_pair && topThree[2].member_names && topThree[2].member_names.length > 0" class="text-xs text-gray-600 italic mb-2">
              {{ topThree[2].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-sm font-semibold text-gray-700">
              {{ isRankSumMethod ? getRankDisplayValue(topThree[2], 2) : `${formatScore(getDisplayTotal(topThree[2]))} pts` }}
            </div>
          </div>
        </div>
      </div>
      
      <!-- Additional Runner-ups Row (4th, 5th, etc. if numberOfWinners > 3) -->
      <div v-if="topThree.length > 3" class="mt-4 grid grid-cols-4 gap-3">
        <div v-for="(result, idx) in topThree.slice(3)" :key="result.id" class="text-center">
          <div class="text-xs font-bold uppercase text-gray-500 mb-1">{{ getOrdinalSuffix(idx + 3) }} Runner-up</div>
          <div class="border border-gray-300 p-3 rounded bg-gray-50">
            <div class="text-xl font-bold mb-1">#{{ result.number }}</div>
            <div class="text-sm font-medium text-gray-700">{{ capitalizeName(result.name) }}</div>
            <div v-if="result.is_pair && result.member_names && result.member_names.length > 0" class="text-xs text-gray-500 italic">
              {{ result.member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-xs text-gray-600">
              {{ isRankSumMethod ? getRankDisplayValue(result, idx + 3) : `${formatScore(getDisplayTotal(result))} pts` }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Results Table -->
    <div class="mb-6">
      <table class="w-full border-collapse text-[10px]">
        <thead>
          <tr class="bg-gray-100 border-y-2 border-black">
            <th v-if="!hideRankColumn && !isSemiFinalStage" class="py-1 px-1 text-left font-bold w-8">Rank</th>
            <th class="py-1 px-1 text-left font-bold w-6">#</th>
            <th class="py-1 px-2 text-left font-bold">Contestant</th>
            <!-- Show all round columns when showAllRounds is true -->
            <template v-if="showAllRounds && rounds && rounds.length > 0">
              <th 
                v-for="round in rounds" 
                :key="round.id"
                class="py-1 px-1 text-center font-bold border-l border-gray-300"
              >
                <div class="flex flex-col items-center">
                  <span>{{ round.name }}</span>
                  <span class="text-[8px] font-normal opacity-75 uppercase">{{ round.type }}</span>
                </div>
              </th>
            </template>
            <th class="py-1 px-1 text-center font-bold w-12 border-l-2 border-black">
              <span v-if="isLastFinalRound">Final Result (Top {{ numberOfWinners }})</span>
              <span v-else-if="isRankSumMethod && isFreshOverall">Final Rank</span>
              <span v-else-if="isRankSumMethod">Total Rank</span>
              <span v-else-if="finalScoreMode === 'inherit'">Weighted Total</span>
              <span v-else>Final Score</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(result, index) in results" 
            :key="result.id" 
            class="border-b border-gray-200"
            :class="{'bg-gray-50': index % 2 === 0}"
          >
            <td v-if="!hideRankColumn && !isSemiFinalStage" class="py-1 px-1">
              <span 
                class="rank-badge"
                :class="{
                  'rank-badge-1': shouldShowPodium && index === 0,
                  'rank-badge-2': shouldShowPodium && index === 1,
                  'rank-badge-3': shouldShowPodium && index === 2 && numberOfWinners >= 3,
                  'rank-badge-default': !shouldShowPodium || index >= 3
                }"
              >
                {{ index + 1 }}
              </span>
            </td>
            <td class="py-1 px-1">
              {{ result.number }}
            </td>
            <td class="py-1 px-2">
              <div class="font-semibold">{{ capitalizeName(result.name) }}</div>
              <div v-if="result.is_pair && result.member_names && result.member_names.length > 0" class="text-[8px] text-gray-500">
                {{ result.member_names.map(n => capitalizeName(n)).join(' & ') }}
              </div>
            </td>
            <!-- Show scores for each round when showAllRounds is true -->
            <template v-if="showAllRounds && rounds && rounds.length > 0">
              <td 
                v-for="round in rounds" 
                :key="round.id"
                class="py-1 px-1 text-center tabular-nums border-l border-gray-300"
                :class="getDisplayScore(result, round.name) !== null ? 'text-gray-900' : 'text-gray-300'"
              >
                <template v-if="hasValidScore(getDisplayScore(result, round.name))">
                  <!-- For rank_sum method, show raw score with rank sum below -->
                  <template v-if="isRankSumMethod">
                    <div class="font-semibold">{{ formatScore(getDisplayScore(result, round.name)!) }}</div>
                    <div class="text-[8px] text-gray-500">
                      (Rank Sum: {{ getRoundRankSum(result, round.name) }})
                    </div>
                  </template>
                  <!-- For score_average method, show computed score with raw score below -->
                  <template v-else>
                    <div class="font-semibold">{{ formatScore(getDisplayScore(result, round.name)!) }}</div>
                    <div v-if="hasRawScore(result, round.name) && getRawScore(result, round.name) !== getDisplayScore(result, round.name)" class="text-[8px] text-gray-500">
                      (Raw: {{ formatScore(getRawScore(result, round.name)!) }})
                    </div>
                  </template>
                </template>
                <span v-else-if="result.scores[round.name] === 0" class="italic text-gray-300" title="Did not compete in this round">—</span>
                <span v-else class="italic">—</span>
              </td>
            </template>
            <td class="py-1 px-1 text-center font-bold tabular-nums border-l-2 border-black">
              <template v-if="isRankSumMethod">
                <!-- Fresh start mode on overall tally: show position in the sorted list (already sorted by final rank) -->
                <template v-if="isFreshOverall">
                  <span v-if="result.hasQualifiedForFinal !== false">{{ getFinalRankPosition(result, index) }}</span>
                  <span v-else class="text-gray-300">—</span>
                </template>
                <!-- Inherit mode or other stages: show total rank sum -->
                <template v-else>
                  <span v-if="result.hasQualifiedForFinal !== false && (result.totalRankSum || result.final_score)">{{ result.totalRankSum ?? result.final_score }}</span>
                  <span v-else class="text-gray-300">—</span>
                </template>
              </template>
              <template v-else>
                <span v-if="result.hasQualifiedForFinal !== false">{{ formatScore(getDisplayTotal(result)) }}</span>
                <span v-else class="text-gray-300">—</span>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Signatures Section - Always show for all categories -->
    <div v-if="!isMaleCategory && !isFemaleCategory" class="mt-12 page-break-inside-avoid">
      <div class="grid grid-cols-3 gap-8">
        <!-- Judges Signatures -->
        <div class="col-span-3 mb-8">
          <h3 class="text-xs font-bold uppercase border-b border-black mb-4 pb-1">Panel of Judges</h3>
          <div class="grid grid-cols-3 gap-x-8 gap-y-12">
            <div v-for="judge in judges" :key="judge.id" class="text-center">
              <div class="border-b border-gray-400 h-8"></div>
              <div class="text-xs font-bold mt-1">{{ capitalizeName(judge.name) }}</div>
              <div class="text-[10px] uppercase text-gray-500">{{ judge.role }}</div>
            </div>
          </div>
        </div>

        <!-- Certification -->
        <div class="col-span-3">
          <div class="flex justify-end">
            <div class="w-64 text-center">
              <div class="text-[10px] uppercase text-gray-500 mb-8 text-left">Certified Correct:</div>
              <div class="border-b border-black h-8"></div>
              <div class="text-xs font-bold mt-1">Head Tabulator</div>
              <div class="text-[10px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface JudgeRankData {
  ranks: number[]
  scores?: number[]
}

interface Result {
  id: number
  number: number
  name: string
  gender?: string
  is_pair?: boolean
  member_names?: string[]
  member_genders?: string[]
  image: string
  scores: Record<string, number>
  displayScores?: Record<string, number>
  displayTotal?: number
  final_score: number
  totalScore?: number
  totalRankSum?: number
  weightedRawTotal?: number
  judgeRanks?: Record<string, JudgeRankData>
  rank?: number
  qualified?: boolean
  qualification_cutoff?: number | null
  hasQualifiedForFinal?: boolean
}

interface Pageant {
  id: number
  name: string
  date?: string
  venue?: string
  location?: string
  logo?: string
}

interface Judge {
  id: number
  name: string
  role: string
}

interface Round {
  id: number
  name: string
  type: string
  display_order: number
}

interface Props {
  pageant: Pageant
  results: Result[]
  judges: Judge[]
  rounds?: Round[]
  reportTitle?: string
  isMaleCategory?: boolean
  isFemaleCategory?: boolean
  isLastFinalRound?: boolean
  numberOfWinners?: number
  hideRankColumn?: boolean
  showAllRounds?: boolean
  selectedStage?: string
  rankingMethod?: 'score_average' | 'rank_sum' | 'sum_of_ranks'
  finalScoreMode?: 'fresh' | 'inherit'
}

const props = withDefaults(defineProps<Props>(), {
  numberOfWinners: 3,
  hideRankColumn: false,
  showAllRounds: false,
  selectedStage: '',
  rankingMethod: 'score_average',
  finalScoreMode: 'fresh'
})

const isRankSumMethod = computed(() => {
  return props.rankingMethod === 'sum_of_ranks' || props.rankingMethod === 'rank_sum'
})

const rankingMethodLabel = computed(() => {
  switch (props.rankingMethod) {
    case 'sum_of_ranks':
    case 'rank_sum':
      return 'Sum of Ranks'
    case 'score_average':
    default:
      return 'Score Average'
  }
})

const finalScoreMode = computed(() => {
  return props.finalScoreMode || 'fresh'
})

// Check if we're in fresh start mode on overall tally (show final rank only)
const isFreshOverall = computed(() => {
  return finalScoreMode.value === 'fresh' && props.selectedStage === 'overall'
})

// Get the rank display value for a result (final rank for fresh mode, total rank sum otherwise)
// For podium displays, we can pass the position directly since topThree is already filtered/sorted
const getRankDisplayValue = (result: Result, position?: number): string => {
  if (isFreshOverall.value) {
    // For fresh mode, show the position among finalists
    if (position !== undefined) {
      return `Rank: ${position + 1}`
    }
    return result.rank ? `Rank: ${result.rank}` : '—'
  }
  return `Rank Sum: ${result.totalRankSum ?? result.final_score}`
}

// Get final rank position among finalists only
// For fresh start mode, count only contestants who qualified for final
const getFinalRankPosition = (result: Result, currentIndex: number): number | string => {
  if (!result.hasQualifiedForFinal) return '—'
  
  // Count how many finalists come before this one in the sorted list
  let finalistPosition = 0
  for (let i = 0; i <= currentIndex; i++) {
    if (props.results[i]?.hasQualifiedForFinal !== false) {
      finalistPosition++
    }
  }
  return finalistPosition
}

// Get pageant logo URL
const getLogoUrl = computed(() => {
  const logo = props.pageant?.logo
  if (!logo || typeof logo !== 'string') return null
  if (logo.startsWith('http') || logo.startsWith('//') || logo.startsWith('/')) {
    return logo
  }
  return `/storage/${logo}`
})

const topThree = computed(() => {
  // For last final round, show top N winners based on numberOfWinners
  if (props.isLastFinalRound) {
    return props.results.slice(0, props.numberOfWinners)
  }
  // For other rounds, show all results (no special podium display)
  return props.results.slice(0, props.numberOfWinners)
})

const shouldShowPodium = computed(() => {
  // Show podium only for final round (not for semi-final or overall tally)
  const stage = props.selectedStage?.toLowerCase() || ''
  // Exclude semi-final from showing podium
  if (stage === 'semi-final' || stage === 'semifinal' || isSemiFinalStage.value) {
    return false
  }
  const isFinal = props.isLastFinalRound || stage === 'final'
  return isFinal && props.results.length > 0 && stage !== 'overall'
})

// Determine if this is a semi-final stage
const isSemiFinalStage = computed(() => {
  const stage = props.selectedStage?.toLowerCase() || ''
  return stage === 'semi-final' || stage === 'semifinal'
})

const isPairCategory = computed(() => {
  return props.results.length > 0 && props.results[0].is_pair
})

// Get gender prefix (Mr/Ms) based on category or individual gender
const getGenderPrefix = (result: Result): string => {
  if (props.isMaleCategory || result.gender === 'male') return 'Mr'
  if (props.isFemaleCategory || result.gender === 'female') return 'Ms'
  return ''
}

// Get the winner title (e.g., "Mr Pageant Name" or "Ms Pageant Name" or "Advancing to Finals")
const getWinnerTitle = (result: Result): string => {
  // For semi-final stage, show "Advancing to Finals" instead of Mr/Ms
  if (isSemiFinalStage.value) {
    return 'Advancing to Finals'
  }
  
  const prefix = getGenderPrefix(result)
  if (prefix) {
    return `${prefix} ${props.pageant.name}`
  }
  // For pairs without specific gender
  if (result.is_pair && result.member_genders && result.member_genders.length > 0) {
    const prefixes = result.member_genders.map(g => g === 'male' ? 'Mr' : 'Ms')
    return `${prefixes[0]} & ${prefixes[1]} ${props.pageant.name}`
  }
  return props.pageant.name
}

// Get runner-up label with proper ordinal
const getRunnerUpLabel = (position: number): string => {
  // For semi-final stage, show "Advancing" instead of "Runner-up"
  if (isSemiFinalStage.value) {
    return 'Advancing to Finals'
  }
  const ordinal = getOrdinalSuffix(position)
  return `${ordinal} Runner-up`
}

const getTitle = (result: Result): string => {
  const resultIndex = props.results.findIndex(r => r.id === result.id)
  const stage = props.selectedStage?.toLowerCase() || ''
  
  // For final round or semi-final, show special titles
  const isFinalOrSemiFinal = props.isLastFinalRound || stage === 'semi-final' || stage === 'semifinal' || stage.includes('final')
  
  if (isFinalOrSemiFinal && stage !== 'overall') {
    if (resultIndex === 0) {
      // Winner - show Mr/Ms [Pageant Name]
      return getWinnerTitle(result)
    } else if (resultIndex < props.numberOfWinners) {
      // Runner-ups with proper ordinal suffixes
      return getRunnerUpLabel(resultIndex)
    }
  }
  
  // For other rounds or results beyond numberOfWinners, show Top X
  return `Top ${resultIndex + 1}`
}

const getOrdinalSuffix = (num: number): string => {
  const j = num % 10
  const k = num % 100
  if (j === 1 && k !== 11) {
    return num + 'st'
  }
  if (j === 2 && k !== 12) {
    return num + 'nd'
  }
  if (j === 3 && k !== 13) {
    return num + 'rd'
  }
  return num + 'th'
}

const getScoreHeaders = () => {
  if (props.results.length === 0) return {}
  return props.results[0].scores || {}
}

const formatScore = (score: number): string => {
  return score.toFixed(2)
}

// Helper function to check if a score is valid (not null, undefined, or 0)
const hasValidScore = (score: unknown): boolean => {
  if (score === null || score === undefined) return false
  const numScore = typeof score === 'number' ? score : Number(score)
  return !isNaN(numScore) && numScore > 0
}

// Get display score (sum of judge totals) for a result in a round
// Falls back to regular score if displayScores not available
const getDisplayScore = (result: Result, roundName: string): number | null => {
  // Use displayScores if available (sum of judge totals for display)
  if (result.displayScores && result.displayScores[roundName] !== undefined) {
    return result.displayScores[roundName]
  }
  // Fallback to regular scores (for backwards compatibility)
  if (result.scores && result.scores[roundName] !== undefined) {
    return result.scores[roundName]
  }
  return null
}

// Get raw score (averaged/weighted score used for ranking)
const getRawScore = (result: Result, roundName: string): number | null => {
  if (result.scores && result.scores[roundName] !== undefined) {
    return result.scores[roundName]
  }
  return null
}

// Check if raw score exists
const hasRawScore = (result: Result, roundName: string): boolean => {
  return result.scores && result.scores[roundName] !== undefined && result.scores[roundName] !== null
}

// Get round rank sum (sum of judge ranks for a specific round)
const getRoundRankSum = (result: Result, roundName: string): string => {
  if (result.judgeRanks && result.judgeRanks[roundName] && result.judgeRanks[roundName].ranks) {
    const ranks = result.judgeRanks[roundName].ranks
    const rankSum = ranks.reduce((sum, rank) => sum + rank, 0)
    return rankSum.toFixed(1)
  }
  return '—'
}

// Get weighted raw total (sum of score × round weight) for inherit mode
const getWeightedRawTotal = (result: Result): number | null => {
  if (result.weightedRawTotal !== undefined && result.weightedRawTotal !== null) {
    return result.weightedRawTotal
  }
  // Fallback to displayTotal if weightedRawTotal not available
  return getDisplayTotal(result)
}

// Get display total (sum of all judge totals across all rounds)
// In inherit mode, use totalScore which includes weighted inheritance from previous stages
const getDisplayTotal = (result: Result): number => {
  // In inherit mode, use totalScore which has the weighted inheritance calculation
  if (props.finalScoreMode === 'inherit') {
    return result.totalScore ?? result.final_score ?? 0
  }
  
  // In fresh mode, use displayTotal (sum of judge averages)
  if (result.displayTotal !== undefined && result.displayTotal !== null) {
    return result.displayTotal
  }
  // Fallback to totalScore then final_score
  return result.totalScore ?? result.final_score ?? 0
}

const capitalizeName = (name: string): string => {
  if (!name) return ''
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ')
}
</script>

<style scoped>
.print-document {
  max-width: 100%;
  margin: 0 auto;
}

.rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 11px;
  padding: 0 6px;
}

.rank-badge-1 {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  color: #78350f;
  border: 2px solid #f59e0b;
  box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.rank-badge-2 {
  background: linear-gradient(135deg, #a5b4fc, #818cf8);
  color: #312e81;
  border: 2px solid #6366f1;
  box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
}

.rank-badge-3 {
  background: linear-gradient(135deg, #fdba74, #fb923c);
  color: #7c2d12;
  border: 2px solid #ea580c;
  box-shadow: 0 2px 4px rgba(234, 88, 12, 0.3);
}

.rank-badge-default {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #d1d5db;
}

@media print {
  .print-document {
    width: 100%;
  }
  
  /* Ensure background colors print */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  /* Print badge styles */
  .rank-badge {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .rank-badge-1 {
    background: #fbbf24 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .rank-badge-2 {
    background: #a5b4fc !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .rank-badge-3 {
    background: #fdba74 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  /* Ensure images display */
  img {
    display: block !important;
    max-width: 80px !important;
    max-height: 80px !important;
  }
}
</style>
