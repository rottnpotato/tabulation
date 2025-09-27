<template>
  <div class="space-y-4 sm:space-y-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow border border-gray-200">
        <div class="p-5 sm:p-6">
          <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">
                {{ pageant ? `${pageant.name} — Minor Awards` : 'Minor Awards' }}
              </h1>
              <p class="mt-1 text-sm sm:text-base text-gray-500">Best in each semi-final round</p>
            </div>
            <div v-if="pageant" class="flex flex-wrap gap-2">
              <Link :href="route('tabulator.minor-awards.print', pageant.id)"
                class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
                <Printer class="h-4 w-4 mr-2 text-gray-600" />
                <span>Print</span>
              </Link>
              <Link :href="route('tabulator.results', pageant.id)"
                class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-black">
                <Trophy class="h-4 w-4 mr-2 text-white/90" />
                <span>Back to Results</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Awards List -->
      <div class="mt-6 space-y-6">
        <div v-for="(entry, idx) in roundEntries" :key="entry.round.id + '-' + idx" class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Best in {{ entry.round.name }}</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
              <div v-for="winner in entry.winners" :key="winner.id" class="flex items-center gap-4 rounded-lg p-4 border border-gray-200 hover:bg-gray-50 transition-colors">
                <img :src="winner.image" :alt="winner.name" class="h-16 w-16 rounded-full object-cover border-2 border-gray-200" />
                <div>
                  <div class="text-sm text-gray-500">#{{ winner.number }}</div>
                  <div class="text-base font-semibold text-gray-900 tracking-tight">{{ winner.name }}</div>
                  <div class="text-sm text-gray-500">Score: {{ formatScore(winner.score) }}</div>
                </div>
              </div>
            </div>
            <div v-if="entry.winners.length > 1" class="mt-4 text-sm text-gray-500">
              Tie for highest score.
            </div>
          </div>
        </div>

        <div v-if="roundEntries.length === 0" class="text-center py-12">
          <div class="bg-white rounded-xl shadow border border-gray-200 p-12">
            <Trophy class="mx-auto h-12 w-12 text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Minor Awards Available</h3>
            <p class="text-gray-500">Minor awards will appear once semi-final round scoring is complete.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Printer, Trophy } from 'lucide-vue-next'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface PageantInfo {
  id: number
  name: string
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

interface Props {
  pageant?: PageantInfo
  awardsByRound: Record<string, RoundEntry>
}

const props = defineProps<Props>()

const roundEntries = computed(() => {
  if (!props.awardsByRound) return []
  return Object.values(props.awardsByRound)
})

const formatScore = (score: number): string => {
  return score.toFixed(2)
}
</script>


