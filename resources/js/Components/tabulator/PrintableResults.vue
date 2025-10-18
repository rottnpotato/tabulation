<template>
  <div class="p-8 bg-white">
    <!-- Header -->
    <div class="text-center mb-8 border-b-2 border-gray-200 pb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ pageant.name }}</h1>
  <p class="text-lg text-gray-600">{{ reportTitle || 'Final Results Report' }}</p>
      <div class="mt-4 text-sm text-gray-500 space-y-1">
        <p v-if="pageant.date">Date: {{ pageant.date }}</p>
        <p v-if="pageant.venue">Venue: {{ pageant.venue }}</p>
        <p v-if="pageant.location">Location: {{ pageant.location }}</p>
      </div>
    </div>

    <!-- Winners Podium -->
    <div v-if="topThree.length > 0" class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4 text-center">Top 3 Winners</h2>
      <div class="grid grid-cols-3 gap-4 mb-8">
        <!-- Second Place -->
        <div v-if="topThree[1]" class="text-center">
          <div class="bg-gray-100 rounded-lg p-4">
            <img 
              :src="topThree[1].image" 
              :alt="topThree[1].name"
              class="w-20 h-20 rounded-full mx-auto mb-3 object-cover border-4 border-gray-300"
            />
            <div class="text-2xl font-bold text-gray-600 mb-1">2nd</div>
            <h3 class="font-semibold text-gray-900">#{{ topThree[1].number }}</h3>
            <h3 class="font-semibold text-gray-900">{{ getTitle(topThree[1]) }} {{ topThree[1].name }}</h3>
            
          </div>
        </div>

        <!-- First Place -->
        <div v-if="topThree[0]" class="text-center">
          <div class="bg-yellow-50 rounded-lg p-4 border-2 border-yellow-300">
            <img 
              :src="topThree[0].image" 
              :alt="topThree[0].name"
              class="w-24 h-24 rounded-full mx-auto mb-3 object-cover border-4 border-yellow-400"
            />
            <div class="text-3xl font-bold text-yellow-600 mb-1">👑 1st</div>
            <h3 class="font-semibold text-gray-900">#{{ topThree[0].number }}</h3>
            <h3 class="font-semibold text-gray-900">{{ getTitle(topThree[0]) }} {{ topThree[0].name }}</h3>
            
          </div>
        </div>

        <!-- Third Place -->
        <div v-if="topThree[2]" class="text-center">
          <div class="bg-orange-50 rounded-lg p-4 border-2 border-orange-300">
            <img 
              :src="topThree[2].image" 
              :alt="topThree[2].name"
              class="w-20 h-20 rounded-full mx-auto mb-3 object-cover border-4 border-orange-400"
            />
            <div class="text-2xl font-bold text-orange-600 mb-1">3rd</div>
            <h3 class="font-semibold text-gray-900">#{{ topThree[2].number }}</h3>
            <h3 class="font-semibold text-gray-900">{{ getTitle(topThree[2]) }} {{ topThree[2].name }}</h3>
            
          </div>
        </div>
      </div>
    </div>

    <!-- Complete Rankings Table -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Complete Rankings</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 border-b">Rank</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 border-b">Number</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 border-b">Contestant</th>
              <th 
                v-for="(score, roundName) in getScoreHeaders()" 
                :key="roundName"
                class="px-4 py-3 text-center text-sm font-medium text-gray-900 border-b"
              >
                {{ roundName }}
              </th>
              <th class="px-4 py-3 text-center text-sm font-medium text-gray-900 border-b">Final Score</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="(result, index) in results" :key="result.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-semibold text-gray-900 border-b">
                {{ index + 1 }}{{ getOrdinalSuffix(index + 1) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 border-b">
                #{{ result.number }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 border-b">
                <div class="font-medium">
                  {{ getTitle(result) }} {{ result.name }}
                  <span v-if="result.is_pair && result.member_names && result.member_names.length > 0" class="text-xs text-gray-500 block mt-1">
                    ({{ result.member_names.join(' & ') }})
                  </span>
                </div>
              </td>
              <td 
                v-for="(score, roundName) in getScoreHeaders()" 
                :key="roundName"
                class="px-4 py-3 text-sm text-center text-gray-900 border-b"
              >
                {{ formatScore(result.scores[roundName] || 0) }}
              </td>
              <td class="px-4 py-3 text-sm text-center font-semibold text-gray-900 border-b">
                {{ formatScore(result.final_score) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Judges Section -->
    <div class="mt-12 pt-6 border-t-2 border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Panel of Judges</h3>
      <div class="flex flex-wrap justify-center gap-x-8 gap-y-2 mb-8">
        <div 
          v-for="judge in judges" 
          :key="judge.id"
          class="text-center"
        >
          <div class="border-b border-gray-400 w-40 mb-1"></div>
          <p class="text-sm font-medium text-gray-900">{{ judge.name }}</p>
          <p class="text-xs text-gray-600">{{ judge.role }}</p>
        </div>
      </div>
    </div>

    <!-- Tabulator Certification -->
    <div class="mt-8 pt-6 border-t border-gray-300">
      <div class="text-center">
        <div class="border-b border-gray-400 w-64 mx-auto mb-2"></div>
        <p class="text-sm font-semibold text-gray-900">Certified by: Head Tabulator</p>
        <p class="text-xs text-gray-500 mt-2">Date: {{ new Date().toLocaleDateString() }}</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-6 text-center text-xs text-gray-500">
      <p>This report was generated on {{ new Date().toLocaleString() }}</p>
      <p>{{ pageant.name }} - Official Results</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

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
  final_score: number
}

interface Pageant {
  id: number
  name: string
  date?: string
  venue?: string
  location?: string
}

interface Judge {
  id: number
  name: string
  role: string
}

interface Props {
  pageant: Pageant
  results: Result[]
  judges: Judge[]
  reportTitle?: string
}

const props = defineProps<Props>()

const topThree = computed(() => {
  return props.results.slice(0, 3)
})

// Helper function to get title based on gender
const getTitle = (result: Result): string => {
  if (result.is_pair && result.member_genders && result.member_genders.length > 0) {
    return result.member_genders.map(g => g === 'male' ? 'Mr' : 'Miss').join(' & ')
  }
  if (result.gender === 'male') return 'Mr'
  if (result.gender === 'female') return 'Miss'
  return ''
}

const getScoreHeaders = () => {
  if (props.results.length === 0) return {}
  
  // Get all unique round names from the first result's scores
  return props.results[0].scores || {}
}

const formatScore = (score: number): string => {
  return score.toFixed(2)
}

const getOrdinalSuffix = (rank: number): string => {
  const j = rank % 10
  const k = rank % 100
  
  if (j === 1 && k !== 11) return 'st'
  if (j === 2 && k !== 12) return 'nd'
  if (j === 3 && k !== 13) return 'rd'
  return 'th'
}
</script>

<style scoped>
@media print {
  .bg-white {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
