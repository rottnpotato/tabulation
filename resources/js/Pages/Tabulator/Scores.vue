<template>
  <div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Score Entries</h2>
        <div class="flex space-x-4">
          <div class="min-w-[160px]">
            <CustomSelect
              v-model="CurrentRound"
              :options="roundOptions"
              variant="blue"
              placeholder="Select Round"
            />
          </div>
          <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
          >
            <RefreshCw class="h-5 w-5 mr-2" />
            Refresh Scores
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Contestant
              </th>
              <th
                v-for="judge in Judges"
                :key="judge.id"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                {{ judge.name }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Average
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="contestant in Contestants" :key="contestant.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 flex-shrink-0">
                    <img
                      :src="contestant.image"
                      :alt="contestant.name"
                      class="h-10 w-10 rounded-full object-cover"
                    />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ contestant.name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      #{{ contestant.number }}
                    </div>
                  </div>
                </div>
              </td>
              <td
                v-for="judge in Judges"
                :key="judge.id"
                class="px-6 py-4 whitespace-nowrap"
              >
                <div
                  class="text-sm"
                  :class="GetScoreClass(GetScore(contestant.id, judge.id))"
                >
                  {{ GetScore(contestant.id, judge.id) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-gray-900">
                  {{ CalculateAverage(contestant.id) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RefreshCw } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

const CurrentRound = ref('evening_gown')

const roundOptions = ref([
  { value: 'evening_gown', label: 'Evening Gown' },
  { value: 'swimsuit', label: 'Swimsuit' },
  { value: 'qa', label: 'Q&A' }
])

const Judges = ref([
  { id: 1, name: 'Judge 1' },
  { id: 2, name: 'Judge 2' },
  { id: 3, name: 'Judge 3' },
  { id: 4, name: 'Judge 4' },
  { id: 5, name: 'Judge 5' }
])

const Contestants = ref([
  {
    id: 1,
    number: 1,
    name: 'Sarah Johnson',
    image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80'
  },
  {
    id: 2,
    number: 2,
    name: 'Emily Davis',
    image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80'
  }
])

// Simulated scores
const Scores = ref(new Map())

// Initialize random scores
Contestants.value.forEach(contestant => {
  Judges.value.forEach(judge => {
    const key = `${contestant.id}-${judge.id}-${CurrentRound.value}`
    Scores.value.set(key, Math.floor(Math.random() * 20) + 80) // Random score between 80-100
  })
})

const GetScore = (contestantId: number, judgeId: number) => {
  const key = `${contestantId}-${judgeId}-${CurrentRound.value}`
  return Scores.value.get(key) || '-'
}

const CalculateAverage = (contestantId: number) => {
  const contestantScores: number[] = []
  
  Judges.value.forEach(judge => {
    const key = `${contestantId}-${judge.id}-${CurrentRound.value}`
    const score = Scores.value.get(key)
    if (score) {
      contestantScores.push(score)
    }
  })
  
  if (contestantScores.length === 0) return '-'
  
  const sum = contestantScores.reduce((acc, score) => acc + score, 0)
  return (sum / contestantScores.length).toFixed(2)
}

const GetScoreClass = (score: number) => {
  if (score >= 90) return 'text-green-600 font-semibold'
  if (score >= 80) return 'text-blue-600'
  return 'text-gray-900'
}
</script>