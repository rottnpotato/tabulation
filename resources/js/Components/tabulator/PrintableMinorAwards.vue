<template>
  <div class="p-8 bg-white">
    <!-- Header with Logo -->
    <div class="mb-8 border-b-2 border-gray-200 pb-6">
      <div class="flex items-center justify-center gap-4 mb-4">
        <img v-if="pageant.logo" :src="pageant.logo" alt="Pageant Logo" class="h-16 w-16 object-contain" />
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900">{{ pageant.name }}</h1>
          <p class="text-lg text-gray-600">Semi-Final Minor Awards</p>
          <div class="mt-2 text-sm text-gray-500 space-y-0.5">
            <p v-if="pageant.date">Date: {{ pageant.date }}</p>
            <p v-if="pageant.venue">Venue: {{ pageant.venue }}</p>
            <p v-if="pageant.location">Location: {{ pageant.location }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Awards Grid -->
    <div v-if="roundEntries.length > 0" class="space-y-6">
      <div
        v-for="(entry, idx) in roundEntries"
        :key="entry.round.id + '-' + idx"
        class="border border-gray-200 rounded-xl overflow-hidden shadow-sm"
      >
        <div class="bg-gradient-to-r from-amber-50 to-amber-100 px-6 py-4 border-b border-amber-200">
          <h2 class="text-xl font-semibold text-amber-900">Best in {{ entry.round.name }}</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="winner in entry.winners"
              :key="winner.id"
              class="flex items-center gap-4 bg-white rounded-lg border border-gray-100 p-4 shadow-sm"
            >
              <img :src="winner.image" :alt="winner.name" class="h-16 w-16 rounded-full object-cover border-4 border-amber-200" />
              <div>
                <div class="text-sm text-gray-500">#{{ winner.number }}</div>
                <div class="text-base font-semibold text-gray-900">{{ winner.name }}</div>
                
              </div>
            </div>
          </div>
          <div v-if="entry.winners.length > 1" class="mt-3 text-sm text-gray-600">
            Note: Tie for highest score.
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-blue-200 mb-6">
        <span class="text-2xl">🏆</span>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Minor Awards Available</h3>
      <p class="text-gray-500">Minor awards will appear once semi-final round scoring is complete.</p>
    </div>

    <!-- Judges Section -->
    <div class="mt-12 pt-6 border-t-2 border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Panel of Judges</h3>
      <div class="flex flex-wrap justify-center gap-x-8 gap-y-2 mb-8">
        <div v-for="judge in judges" :key="judge.id" class="text-center">
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
        <p class="text-sm font-semibold text-gray-900">Certified by: Tabulator</p>
        <p class="text-xs text-gray-500 mt-2">Date: {{ new Date().toLocaleDateString() }}</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-6 text-center text-xs text-gray-500">
      <p>This report was generated on {{ new Date().toLocaleString() }}</p>
      <p>{{ pageant.name }} - Semi-Final Minor Awards</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface PageantInfo {
  id: number
  name: string
  logo?: string
  date?: string
  venue?: string
  location?: string
}

interface RoundInfo {
  id: number
  name: string
  type?: string
  weight?: number
}

interface WinnerInfo {
  id: number
  number: number
  name: string
  image: string
  score: number
}

interface RoundEntry {
  round: RoundInfo
  winners: WinnerInfo[]
}

interface JudgeInfo {
  id: number
  name: string
  role: string
}

interface Props {
  pageant: PageantInfo
  awardsByRound: Record<string, RoundEntry>
  judges: JudgeInfo[]
}

const props = defineProps<Props>()

const roundEntries = computed(() => {
  if (!props.awardsByRound) return []
  // Keep the original order of object keys as provided by backend
  return Object.values(props.awardsByRound)
})

const formatScore = (score: number): string => {
  return score.toFixed(2)
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


