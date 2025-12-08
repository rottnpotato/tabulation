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
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-teal-50">
              Overall Score (Computed)
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="contestant in contestants" :key="contestant.id" class="hover:bg-gray-50 transition-colors">
            <td class="sticky left-0 z-10 bg-white px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <img class="h-10 w-10 rounded-full object-cover" :src="contestant.image" :alt="contestant.name" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    #{{ contestant.number }} {{ contestant.name }}
                  </div>
                </div>
              </div>
            </td>
            <td
              v-for="judge in judges"
              :key="judge.id"
              class="px-6 py-4 whitespace-nowrap"
            >
              <span
                v-if="getScore(contestant.id, judge.id) !== null"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-teal-100 text-teal-800"
              >
                {{ getScore(contestant.id, judge.id) }}
              </span>
              <span v-else class="text-gray-400 text-sm">—</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap bg-teal-50">
              <span
                v-if="getContestantAverage(contestant.id) !== null"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-teal-600 text-white"
              >
                {{ getContestantAverage(contestant.id) }}
              </span>
              <span v-else class="text-gray-400 text-sm">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detailed View (Individual Criteria Scores) -->
    <div v-else class="overflow-x-auto">
      <div v-for="contestant in contestants" :key="contestant.id" class="border-b border-gray-200 last:border-b-0">
        <!-- Contestant Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <img class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-sm" :src="contestant.image" :alt="contestant.name" />
            <div>
              <h4 class="text-base font-semibold text-gray-900">
                #{{ contestant.number }} {{ contestant.name }}
              </h4>
              <p v-if="contestant.is_pair && contestant.members_text" class="text-sm text-gray-600">
                {{ contestant.members_text }}
              </p>
            </div>
          </div>
          <div class="text-right">
            <div class="text-xs text-gray-500 uppercase tracking-wider">Overall Average</div>
            <div class="text-2xl font-bold text-teal-600">
              {{ getContestantAverage(contestant.id) || '—' }}
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
              <!-- Judge Average Row -->
              <tr class="bg-teal-50 font-semibold">
                <td class="px-6 py-3 text-sm text-gray-900" colspan="2">
                  Judge Averages (Weighted)
                </td>
                <td
                  v-for="judge in judges"
                  :key="judge.id"
                  class="px-6 py-3 text-center"
                >
                  <span
                    v-if="getScore(contestant.id, judge.id) !== null"
                    class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-teal-600 text-white"
                  >
                    {{ getScore(contestant.id, judge.id) }}
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
import { Table2, BarChart3, ListChecks, AlertCircle } from 'lucide-vue-next'

interface Contestant {
  id: number
  number: number
  name: string
  image: string
  is_pair?: boolean
  members_text?: string
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
  criteria?: Criteria[]
  detailedScores?: Record<string, any>
  scoreKey?: string
  emptyTitle?: string
  emptyMessage?: string
  showToggle?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  criteria: () => [],
  detailedScores: () => ({}),
  emptyTitle: 'No Scores Yet',
  emptyMessage: 'Scores will appear here once judges start submitting.',
  showToggle: true
})

const viewMode = ref<'summary' | 'detailed'>('summary')

const toggleView = () => {
  viewMode.value = viewMode.value === 'summary' ? 'detailed' : 'summary'
}

const getScore = (contestantId: number, judgeId: number): number | null => {
  const key = props.scoreKey 
    ? `${contestantId}-${judgeId}-${props.scoreKey}`
    : `${contestantId}-${judgeId}`
  
  const scoresMap = props.scores instanceof Map ? props.scores : new Map(Object.entries(props.scores))
  const score = scoresMap.get(key)
  return score !== undefined ? Number(score) : null
}

const getDetailedScore = (contestantId: number, judgeId: number, criteriaId: number): number | null => {
  const key = `${contestantId}-${judgeId}-${criteriaId}`
  const score = props.detailedScores[key]
  return score?.score !== undefined ? Number(score.score) : null
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
