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
      <div class="mt-6 space-y-8">
        <div v-for="(entry, idx) in roundEntries" :key="entry.round.id + '-' + idx" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h2 class="text-lg font-medium text-center text-gray-800 tracking-wide">
              Best in {{ entry.round.name }}
            </h2>
          </div>
          <div class="p-8">
            <!-- Centered winners with elegant spacing -->
            <div class="flex flex-wrap justify-center gap-8">
              <div v-for="winner in entry.winners" :key="winner.id" 
                class="flex flex-col items-center text-center group">
                
                <!-- Winner Image -->
                <div class="relative mb-6">
                  <div class="relative">
                    <img :src="winner.image" :alt="winner.name" 
                      class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-200 group-hover:ring-gray-300 transition-all duration-200" />
                    <div class="absolute -bottom-1 -right-1 bg-gray-800 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-medium">
                      {{ winner.number }}
                    </div>
                  </div>
                </div>
                
                <!-- Winner Details -->
                <div class="space-y-3 min-w-[200px]">
                  <h3 class="text-lg font-semibold text-gray-900 leading-tight">{{ winner.name }}</h3>
                  <div class="border-t border-gray-200 pt-2">
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-medium mb-1">Score</div>
                    <div class="text-xl font-light text-gray-900">{{ formatScore(winner.score) }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Tie Notice -->
            <div v-if="entry.winners.length > 1" class="mt-8 text-center">
              <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-600 rounded-md text-sm">
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                Tied for highest score
              </div>
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


