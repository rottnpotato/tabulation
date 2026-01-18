<template>
  <div class="bg-white rounded-xl shadow-md border border-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <Table2 class="h-6 w-6 text-white" />
          <h3 class="text-lg font-semibold text-white">{{ title }}</h3>
        </div>
        <div class="flex items-center space-x-2">
          <button
            v-if="showToggle"
            @click="toggleView"
            class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg text-sm font-medium transition-all"
          >
            <component :is="viewMode === 'detailed' ? BarChart3 : ListChecks" class="h-4 w-4 mr-1.5" />
            {{ viewMode === 'detailed' ? 'Summary View' : 'Detailed View' }}
          </button>
        </div>
      </div>
    </div>

    <!-- No Data State -->
    <div v-if="!contestants.length || !judges.length" class="px-6 py-12 text-center">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
        <AlertCircle class="h-6 w-6 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Data Available</h3>
      <p class="text-gray-600">{{ emptyMessage }}</p>
    </div>

    <!-- Summary View (Aggregated Scores) -->
    <div v-else-if="viewMode === 'summary'" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 overflow-x-auto">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="sticky left-0 z-10 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Contestant
            </th>
            <th
              v-for="judge in judges"
              :key="judge.id"
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              {{ judge.name }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" :class="isRankSumMethod ? 'bg-purple-50' : 'bg-teal-50'">
              {{ isRankSumMethod ? 'Total Rank' : 'Total Score' }}
            </th>
            <th v-if="isRankSumMethod" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-purple-50">
              Average Rank
            </th>
            <th v-if="showBackedOutActions" scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="contestant in displayContestants" 
            :key="contestant.id" 
            class="transition-colors"
            :class="[
              contestant.backed_out ? 'bg-red-50/50 opacity-60' : 'hover:bg-gray-50'
            ]"
          >
            <td class="sticky left-0 z-10 px-6 py-4 whitespace-nowrap" :class="contestant.backed_out ? 'bg-red-50/50' : 'bg-white'">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10 relative">
                  <img class="h-10 w-10 rounded-full object-cover" :class="{ 'grayscale': contestant.backed_out }" :src="contestant.image" :alt="contestant.name" />
                  <div v-if="contestant.backed_out" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
                    <UserX class="w-3 h-3 text-white" />
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium" :class="contestant.backed_out ? 'text-gray-500 line-through' : 'text-gray-900'">
                    #{{ contestant.number }} {{ contestant.name }}
                  </div>
                  <div v-if="contestant.backed_out" class="flex items-center gap-1 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">
                      <UserX class="w-3 h-3 mr-1" />
                      BACKED OUT
                    </span>
                  </div>
                </div>
              </div>
            </td>
            <td
              v-for="judge in judges"
              :key="judge.id"
              class="px-6 py-4 whitespace-nowrap"
            >
              <div v-if="isRankSumMethod && getJudgeRank(contestant.id, judge.id) !== null" class="flex flex-col items-center">
                <span
                  class="inline-flex text-center items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800"
                >
                  {{ getJudgeRank(contestant.id, judge.id) }}
                </span>
              </div>
              <div v-else-if="!isRankSumMethod && getTotalScore(contestant.id, judge.id) !== null" class="flex flex-col items-center">
                <span
                  class="inline-flex text-center items-center px-3 py-1 rounded-full text-sm font-semibold bg-teal-100 text-teal-800"
                >
                  {{ getTotalScore(contestant.id, judge.id) }}
                </span>
                <!-- Show weighted score when there's a tie (same raw score but different weighted) -->
                <span 
                  v-if="hasTieForJudge(contestant.id, judge.id)"
                  class="text-xs text-amber-600 mt-1"
                  :title="`Weighted score: ${getWeightedScore(contestant.id, judge.id)} (used for ranking when raw scores are tied)`"
                >
                  ({{ getWeightedScore(contestant.id, judge.id) }})
                </span>
              </div>
              <span v-else class=" text-right text-gray-400 text-sm">—</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap" :class="contestant.backed_out ? 'bg-red-50/30' : (isRankSumMethod ? 'bg-purple-50' : 'bg-teal-50')">
              <span
                v-if="isRankSumMethod && getContestantRankTotal(contestant.id) !== null && !contestant.backed_out"
                class="inline-flex text-center items-center px-3 py-1 rounded-full text-sm font-bold bg-purple-600 text-white"
              >
                {{ formatRankValue(getContestantRankTotal(contestant.id), 0) }}
              </span>
              <span
                v-else-if="!isRankSumMethod && getContestantTotal(contestant.id) !== null && !contestant.backed_out"
                class="inline-flex text-center items-center px-3 py-1 rounded-full text-sm font-bold bg-teal-600 text-white"
              >
                {{ getContestantTotal(contestant.id) }}
              </span>
              <span v-else-if="contestant.backed_out" class="text-red-400 text-sm italic">N/A</span>
              <span v-else class="text-right text-gray-400 text-sm">—</span>
            </td>
            <td v-if="isRankSumMethod" class="px-6 py-4 whitespace-nowrap" :class="contestant.backed_out ? 'bg-red-50/30' : 'bg-purple-50'">
              <span
                v-if="getContestantAverageRank(contestant.id) !== null && !contestant.backed_out"
                class="inline-flex text-center items-center px-3 py-1 rounded-full text-sm font-bold bg-purple-100 text-purple-800"
              >
                {{ formatRankValue(getContestantAverageRank(contestant.id), 2) }}
              </span>
              <span v-else-if="contestant.backed_out" class="text-red-400 text-sm italic">N/A</span>
              <span v-else class="text-right text-gray-400 text-sm">—</span>
            </td>
            <!-- Actions Column -->
            <td v-if="showBackedOutActions" class="px-6 py-4 whitespace-nowrap text-center">
              <button
                v-if="contestant.backed_out"
                @click="emit('restore', contestant)"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-lg transition-colors" 
                title="Restore this contestant"
              >
                <RotateCcw class="w-3.5 h-3.5 mr-1" />
                Restore
              </button>
              <button
                v-else
                @click="emit('back-out', contestant)"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors"
                title="Mark as backed out"
              >
                <UserX class="w-3.5 h-3.5 mr-1" />
                Back Out
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detailed View (Individual Criteria Scores) -->
    <div v-else class="overflow-x-auto">
      <div 
        v-for="contestant in displayContestants" 
        :key="contestant.id" 
        class="border-b border-gray-200 last:border-b-0"
        :class="{ 'opacity-60 bg-red-50/30': contestant.backed_out }"
      >
        <!-- Contestant Header -->
        <div 
          class="px-6 py-4 flex items-center justify-between"
          :class="contestant.backed_out ? 'bg-gradient-to-r from-red-50 to-red-100' : 'bg-gradient-to-r from-gray-50 to-gray-100'"
        >
          <div class="flex items-center space-x-4">
            <div class="relative">
              <img 
                class="h-12 w-12 rounded-full object-cover border-2 shadow-sm" 
                :class="contestant.backed_out ? 'border-red-200 grayscale' : 'border-white'"
                :src="contestant.image" 
                :alt="contestant.name" 
              />
              <div v-if="contestant.backed_out" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
                <UserX class="w-3 h-3 text-white" />
              </div>
            </div>
            <div>
              <h4 
                class="text-base font-semibold" 
                :class="contestant.backed_out ? 'text-gray-500 line-through' : 'text-gray-900'"
              >
                #{{ contestant.number }} {{ contestant.name }}
              </h4>
              <p v-if="contestant.is_pair && contestant.members_text" class="text-sm text-gray-600">
                {{ contestant.members_text }}
              </p>
              <div v-if="contestant.backed_out" class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">
                  <UserX class="w-3 h-3 mr-1" />
                  BACKED OUT
                </span>
                <span v-if="contestant.backed_out_reason" class="text-xs text-red-600 italic">
                  {{ contestant.backed_out_reason }}
                </span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <!-- Action Buttons -->
            <div v-if="showBackedOutActions">
              <button
                v-if="contestant.backed_out"
                @click="emit('restore', contestant)"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-lg transition-colors"
                title="Restore this contestant"
              >
                <RotateCcw class="w-3.5 h-3.5 mr-1" />
                Restore
              </button>
              <button
                v-else
                @click="emit('back-out', contestant)"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors"
                title="Mark as backed out"
              >
                <UserX class="w-3.5 h-3.5 mr-1" />
                Back Out
              </button>
            </div>
            <!-- Total Score -->
            <div class="text-right">
              <div class="text-xs text-gray-500 uppercase tracking-wider">{{ isRankSumMethod ? 'Total Rank' : 'Total Score' }}</div>
              <div v-if="!contestant.backed_out" class="text-2xl font-bold text-teal-600">
                {{ isRankSumMethod ? (getContestantRankTotal(contestant.id) || '—') : (getContestantTotal(contestant.id) || '—') }}
              </div>
              <div v-else class="text-lg font-medium text-red-400 italic">
                N/A
              </div>
            </div>
          </div>
        </div>

        <!-- Criteria Scores Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                  Criteria
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">
                  Weight
                </th>
                <th
                  v-for="judge in judges"
                  :key="judge.id"
                  scope="col"
                  class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider"
                >
                  {{ judge.name }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="criterion in criteria" :key="criterion.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-3">
                  <div class="text-sm font-medium text-gray-900">{{ criterion.name }}</div>
                  <div v-if="criterion.description" class="text-xs text-gray-500 mt-0.5">{{ criterion.description }}</div>
                </td>
                <td class="px-6 py-3 text-center">
                  <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                    {{ criterion.weight }}%
                  </span>
                </td>
                <td
                  v-for="judge in judges"
                  :key="judge.id"
                  class="px-6 py-3 text-center"
                >
                  <span
                    v-if="getDetailedScore(contestant.id, judge.id, criterion.id) !== null"
                    :class="[
                      'inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold',
                      getScoreColor(getDetailedScore(contestant.id, judge.id, criterion.id), criterion.max_score)
                    ]"
                  >
                    {{ getDetailedScore(contestant.id, judge.id, criterion.id) }}
                  </span>
                  <span v-else class="text-gray-300 text-sm">—</span>
                </td>
              </tr>
              <!-- Judge Totals Row -->
              <tr class="bg-teal-50 font-semibold">
                <td class="px-6 py-3 text-sm text-gray-900" colspan="2">
                  {{ isRankSumMethod ? 'Judge Ranks' : 'Judge Totals (Weighted)' }}
                </td>
                <td
                  v-for="judge in judges"
                  :key="judge.id"
                  class="px-6 py-3 text-center"
                >
                  <span
                    v-if="isRankSumMethod && getJudgeRank(contestant.id, judge.id) !== null"
                    class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-purple-600 text-white"
                  >
                    {{ getJudgeRank(contestant.id, judge.id) }}
                  </span>
                  <span
                    v-else-if="!isRankSumMethod && getTotalScore(contestant.id, judge.id) !== null"
                    class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-teal-600 text-white"
                  >
                    {{ getTotalScore(contestant.id, judge.id) }}
                  </span>
                  <span v-else class="text-gray-400 text-sm">—</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Table2, BarChart3, ListChecks, AlertCircle, UserX, RotateCcw } from 'lucide-vue-next'

interface Contestant {
  id: number
  number: number
  name: string
  image: string
  is_pair?: boolean
  members_text?: string
  backed_out?: boolean
  backed_out_at?: string
  backed_out_reason?: string
}

interface Judge {
  id: number
  name: string
}

interface Criteria {
  id: number
  name: string
  description?: string
  weight: number
  min_score: number
  max_score: number
}

interface Props {
  title: string
  contestants: Contestant[]
  judges: Judge[]
  scores: Map<string, number> | Record<string, number>
  totalScores?: Map<string, number> | Record<string, number>
  weightedScores?: Map<string, number> | Record<string, number>
  rankingMethod?: 'score_average' | 'rank_sum' | 'ordinal'
  finalScoreMode?: 'fresh' | 'inherit'
  criteria?: Criteria[]
  detailedScores?: Record<string, any>
  scoreKey?: string
  emptyTitle?: string
  emptyMessage?: string
  showToggle?: boolean
  showBackedOutActions?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  criteria: () => [],
  detailedScores: () => ({}),
  totalScores: () => ({}),
  weightedScores: () => ({}),
  emptyTitle: 'No Scores Yet',
  emptyMessage: 'Scores will appear here once judges start submitting.',
  showToggle: true,
  showBackedOutActions: false,
  rankingMethod: 'score_average',
  finalScoreMode: 'fresh'
})

const emit = defineEmits<{
  (e: 'back-out', contestant: Contestant): void
  (e: 'restore', contestant: Contestant): void
}>()

const viewMode = ref<'summary' | 'detailed'>('summary')

const isRankSumMethod = computed(() => props.rankingMethod === 'rank_sum')

const displayContestants = computed(() => {
  const list = [...props.contestants]
  if (isRankSumMethod.value) {
    return list.sort((a, b) => (a.number ?? 0) - (b.number ?? 0))
  }
  return list
})

const toggleView = () => {
  viewMode.value = viewMode.value === 'summary' ? 'detailed' : 'summary'
}

const getScoreKey = (contestantId: number, judgeId: number): string => {
  return props.scoreKey 
    ? `${contestantId}-${judgeId}-${props.scoreKey}`
    : `${contestantId}-${judgeId}`
}

const getScore = (contestantId: number, judgeId: number): number | null => {
  const key = getScoreKey(contestantId, judgeId)
  
  const scoresMap = props.scores instanceof Map ? props.scores : new Map(Object.entries(props.scores))
  const score = scoresMap.get(key)
  if (score === undefined || score === null || score === '') return null
  const numScore = Number(score)
  return isNaN(numScore) ? null : numScore
}

const getTotalScore = (contestantId: number, judgeId: number): number | null => {
  const key = getScoreKey(contestantId, judgeId)
  
  const totalScoresMap = props.totalScores instanceof Map ? props.totalScores : new Map(Object.entries(props.totalScores || {}))
  const score = totalScoresMap.get(key)
  if (score === undefined || score === null || score === '') return null
  const numScore = Number(score)
  return isNaN(numScore) ? null : numScore
}

// Get weighted score for a contestant-judge pair
const getWeightedScore = (contestantId: number, judgeId: number): number | null => {
  const key = getScoreKey(contestantId, judgeId)
  
  const weightedScoresMap = props.weightedScores instanceof Map ? props.weightedScores : new Map(Object.entries(props.weightedScores || {}))
  const score = weightedScoresMap.get(key)
  if (score === undefined || score === null || score === '') return null
  const numScore = Number(score)
  return isNaN(numScore) ? null : numScore
}

// Check if there's a tie for a judge's raw score (same raw score but different weighted score)
const hasTieForJudge = (contestantId: number, judgeId: number): boolean => {
  if (isRankSumMethod.value) return false

  const rawScore = getTotalScore(contestantId, judgeId)
  if (rawScore === null) return false
  
  // Find other contestants with the same raw score from this judge
  const contestantsWithSameRawScore = props.contestants.filter(c => {
    if (c.id === contestantId || c.backed_out) return false
    const otherRawScore = getTotalScore(c.id, judgeId)
    return otherRawScore === rawScore
  })
  
  if (contestantsWithSameRawScore.length === 0) return false
  
  // Check if any of them have different weighted scores
  const myWeighted = getWeightedScore(contestantId, judgeId)
  return contestantsWithSameRawScore.some(c => {
    const otherWeighted = getWeightedScore(c.id, judgeId)
    return myWeighted !== otherWeighted
  })
}

// Get max score for a specific judge across all contestants
const getJudgeMaxScore = (judgeId: number): number => {
  let maxScore = 0
  props.contestants.forEach(contestant => {
    const score = getTotalScore(contestant.id, judgeId)
    if (score !== null && score > maxScore) {
      maxScore = score
    }
  })
  return maxScore
}

// Get normalized score for a specific judge (0-100 scale)
const getNormalizedJudgeScore = (contestantId: number, judgeId: number): number | null => {
  const score = getTotalScore(contestantId, judgeId)
  if (score === null) return null
  
  const maxScore = getJudgeMaxScore(judgeId)
  if (maxScore === 0) return null
  
  return Number(((score / maxScore) * 100).toFixed(2))
}

const getDetailedScore = (contestantId: number, judgeId: number, criteriaId: number): number | null => {
  const key = `${contestantId}-${judgeId}-${criteriaId}`
  const scoreRecord = props.detailedScores[key]
  if (!scoreRecord || scoreRecord.score === undefined || scoreRecord.score === null) return null
  const numScore = Number(scoreRecord.score)
  return isNaN(numScore) ? null : numScore
}

const getContestantAverage = (contestantId: number): number | null => {
  const judgeScores = props.judges
    .map(judge => getScore(contestantId, judge.id))
    .filter(score => score !== null) as number[]
  
  if (judgeScores.length === 0) {
    return null
  }
  
  const sum = judgeScores.reduce((acc, score) => acc + score, 0)
  return Number((sum / judgeScores.length).toFixed(2))
}

const getContestantTotal = (contestantId: number): number | null => {
  // Sum all judge totals for this contestant (no normalization)
  const judgeTotals: number[] = []
  
  props.judges.forEach(judge => {
    const score = getTotalScore(contestantId, judge.id)
    if (score !== null) {
      judgeTotals.push(score)
    }
  })
  
  if (judgeTotals.length === 0) {
    return null
  }
  
  // Simple sum of all judge totals
  const sum = judgeTotals.reduce((acc, score) => acc + score, 0)
  return Number(sum.toFixed(2))
}

const getRankingScore = (contestantId: number, judgeId: number): number | null => {
  // For rank_sum method, use raw total scores (not weighted) to match judge interface ranking
  // The judge's ScoringTable ranks by raw score sum, so we must do the same
  if (isRankSumMethod.value) {
    return getTotalScore(contestantId, judgeId)
  }
  // For score_average method, use weighted scores for ranking
  const weighted = getWeightedScore(contestantId, judgeId)
  if (weighted !== null) return weighted
  return getTotalScore(contestantId, judgeId)
}

const judgeRankMap = computed(() => {
  const ranks = new Map<string, number>()
  if (!isRankSumMethod.value) return ranks

  props.judges.forEach(judge => {
    const entries: Array<{ contestantId: number; score: number }> = []
    displayContestants.value.forEach(contestant => {
      if (contestant.backed_out) return
      const score = getRankingScore(contestant.id, judge.id)
      if (score !== null) {
        entries.push({ contestantId: contestant.id, score })
      }
    })

    if (entries.length === 0) return

    const scoreBuckets = new Map<string, number[]>()
    entries.forEach(({ contestantId, score }) => {
      const key = score.toFixed(6)
      if (!scoreBuckets.has(key)) {
        scoreBuckets.set(key, [])
      }
      scoreBuckets.get(key)?.push(contestantId)
    })

    const sortedScores = Array.from(scoreBuckets.keys())
      .map(key => ({ key, value: Number(key) }))
      .sort((a, b) => b.value - a.value)

    let betterCount = 0
    sortedScores.forEach(({ key }) => {
      const contestantsWithScore = scoreBuckets.get(key) ?? []
      const rank = betterCount + 1
      contestantsWithScore.forEach(contestantId => {
        ranks.set(getScoreKey(contestantId, judge.id), rank)
      })
      betterCount += contestantsWithScore.length
    })
  })

  return ranks
})

const getJudgeRank = (contestantId: number, judgeId: number): number | null => {
  if (!isRankSumMethod.value) return null
  const key = getScoreKey(contestantId, judgeId)
  const rank = judgeRankMap.value.get(key)
  return rank === undefined ? null : rank
}

const getContestantRankTotal = (contestantId: number): number | null => {
  if (!isRankSumMethod.value) return null
  const judgeRanks = props.judges
    .map(judge => getJudgeRank(contestantId, judge.id))
    .filter(rank => rank !== null) as number[]

  if (judgeRanks.length === 0) return null
  return Number(judgeRanks.reduce((acc, rank) => acc + rank, 0).toFixed(2))
}

const getContestantAverageRank = (contestantId: number): number | null => {
  if (!isRankSumMethod.value) return null
  const judgeRanks = props.judges
    .map(judge => getJudgeRank(contestantId, judge.id))
    .filter(rank => rank !== null) as number[]

  if (judgeRanks.length === 0) return null
  const sum = judgeRanks.reduce((acc, rank) => acc + rank, 0)
  return Number((sum / judgeRanks.length).toFixed(2))
}

const formatRankValue = (value: number | null, decimals = 2): string => {
  if (value === null || value === undefined) return '—'
  return value.toFixed(decimals)
}

const getScoreColor = (score: number | null, maxScore: number): string => {
  if (score === null) {
    return ''
  }
  
  const percentage = (score / maxScore) * 100
  
  if (percentage >= 90) {
    return 'bg-green-100 text-green-800'
  } else if (percentage >= 75) {
    return 'bg-teal-100 text-teal-800'
  } else if (percentage >= 60) {
    return 'bg-yellow-100 text-yellow-800'
  } else {
    return 'bg-orange-100 text-orange-800'
  }
}
</script>
