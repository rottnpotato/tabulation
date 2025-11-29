<template>
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4">
      <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
      <div class="text-sm text-gray-500">
        <span class="font-medium">{{ contestants.length }}</span> contestants
      </div>
    </div>

    <!-- Scores Table (No Ranking) -->
    <div v-if="contestants.length" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              #
            </th>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Contestant
            </th>
            <th
              v-for="round in rounds"
              :key="round.id"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              {{ round.name }}
            </th>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Total Score
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr
            v-for="(contestant, index) in sortedContestants"
            :key="contestant.id"
            class="hover:bg-gray-50/80 transition-all duration-200"
          >
            <!-- Number -->
            <td class="whitespace-nowrap px-4 py-3 text-center">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                {{ contestant.number }}
              </span>
            </td>

            <!-- Contestant -->
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <img
                  v-if="contestant.image"
                  :src="contestant.image"
                  :alt="contestant.name"
                  class="h-10 w-10 rounded-full object-cover ring-2 ring-gray-100"
                />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <p class="font-semibold text-gray-900 truncate">
                      {{ contestant.name }}
                    </p>
                    <span
                      v-if="contestant.is_pair && contestant.member_genders && contestant.member_genders.length === 2"
                      class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                      :class="getPairBadgeClass(contestant.member_genders)"
                    >
                      <span>{{ contestant.member_genders[0] === 'male' ? '♂' : '♀' }}</span>
                      <span>+</span>
                      <span>{{ contestant.member_genders[1] === 'male' ? '♂' : '♀' }}</span>
                    </span>
                    <span
                      v-else-if="contestant.gender"
                      class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs"
                      :class="contestant.gender === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'"
                    >
                      {{ contestant.gender === 'male' ? '♂' : '♀' }}
                    </span>
                  </div>
                  <p v-if="contestant.region" class="text-xs text-gray-500 truncate mt-0.5">
                    {{ contestant.region }}
                  </p>
                </div>
              </div>
            </td>

            <!-- Round Scores -->
            <td
              v-for="round in rounds"
              :key="`${contestant.id}-${round.id}`"
              class="whitespace-nowrap px-4 py-3 text-center"
            >
              <span
                v-if="contestant.scores && contestant.scores[round.name] !== undefined"
                class="inline-flex items-center rounded-lg bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-700"
              >
                {{ formatScore(contestant.scores[round.name]) }}
              </span>
              <span v-else class="text-gray-400 text-xs">—</span>
            </td>

            <!-- Total Score -->
            <td class="whitespace-nowrap px-4 py-3 text-right">
              <span class="inline-flex items-center rounded-lg bg-teal-50 px-3 py-1.5 text-sm font-bold text-teal-700 border border-teal-200">
                {{ formatScore(contestant.totalScore || contestant.finalScore || 0) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="px-6 py-12 text-center">
      <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-gray-50 mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No Scores Available</h3>
      <p class="text-gray-500">
        Score data will appear here once all rounds are completed and calculated.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Round {
  id: number
  name: string
  type?: string
  weight: number
  top_n_proceed?: number
  display_order?: number
}

interface Contestant {
  id: number
  number: number
  name: string
  region?: string
  image: string
  gender?: string
  is_pair?: boolean
  member_genders?: string[]
  scores: Record<string, number>
  totalScore?: number
  finalScore?: number
}

interface Props {
  title: string
  contestants: Contestant[]
  rounds: Round[]
}

const props = defineProps<Props>()

// Sort contestants by number for display (no ranking)
const sortedContestants = computed(() => {
  return [...props.contestants].sort((a, b) => a.number - b.number)
})

const formatScore = (score: number): string => {
  if (score === null || score === undefined || isNaN(score)) {
    return '—'
  }
  return parseFloat(score.toFixed(2)).toString()
}

const getPairBadgeClass = (genders: string[]) => {
  if (!genders || genders.length !== 2) return 'bg-gray-100 text-gray-700'
  
  const hasMale = genders.includes('male')
  const hasFemale = genders.includes('female')
  
  if (hasMale && hasFemale) {
    return 'bg-gradient-to-r from-blue-100 to-pink-100 text-purple-700 border border-purple-200'
  } else if (hasMale) {
    return 'bg-blue-100 text-blue-700 border border-blue-200'
  } else if (hasFemale) {
    return 'bg-pink-100 text-pink-700 border border-pink-200'
  }
  
  return 'bg-gray-100 text-gray-700'
}
</script>

<style scoped>
/* Ensure smooth transitions */
tr {
  transition: background-color 0.2s ease;
}
</style>
