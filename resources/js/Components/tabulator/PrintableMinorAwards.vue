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
    <div v-if="roundEntries.length > 0" class="space-y-12">
      <div
        v-for="(entry, idx) in roundEntries"
        :key="entry.round.id + '-' + idx"
        class="border border-gray-300 rounded-lg overflow-hidden"
      >
        <div class="bg-gray-100 px-8 py-6 border-b border-gray-300">
          <h2 class="text-xl font-medium text-center text-gray-800 tracking-wide uppercase">
            Best in {{ entry.round.name }}
          </h2>
        </div>
        <div class="p-12 bg-white">
          <!-- Centered winners with elegant print spacing -->
          <div class="flex flex-wrap justify-center gap-12">
            <div
              v-for="winner in entry.winners"
              :key="winner.id"
              class="flex flex-col items-center text-center"
            >
              <!-- Winner Image -->
              <div class="relative mb-8">
                <div class="relative">
                  <img :src="winner.image" :alt="winner.name" 
                    class="h-32 w-32 rounded-full object-cover ring-4 ring-gray-300" />
                  <div class="absolute -bottom-2 -right-2 bg-gray-800 text-white text-sm rounded-full w-8 h-8 flex items-center justify-center font-semibold">
                    {{ winner.number }}
                  </div>
                </div>
              </div>
              
              <!-- Winner Details -->
              <div class="space-y-4 min-w-[280px]">
                <h3 class="text-2xl font-medium text-gray-900 leading-tight tracking-wide">
                  {{ getTitle(winner) }} {{ winner.name }}
                </h3>
                <p v-if="winner.is_pair && winner.member_names && winner.member_names.length > 0" class="text-sm text-gray-600 -mt-2">
                  ({{ winner.member_names.join(' & ') }})
                </p>
                <div class="border-t-2 border-gray-200 pt-4">
                  <div class="text-sm text-gray-500 uppercase tracking-widest font-medium mb-2">Award Score</div>
                  <div class="text-3xl font-light text-gray-900 tracking-wide">{{ formatScore(winner.score) }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Tie Notice -->
          <div v-if="entry.winners.length > 1" class="mt-12 text-center">
            <div class="inline-flex items-center gap-3 px-6 py-3 border border-gray-300 text-gray-600 rounded text-sm tracking-wide">
              <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
              TIED FOR HIGHEST SCORE
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-gray-100 border-2 border-gray-200 mb-8">
        <Trophy class="h-8 w-8 text-gray-400" />
      </div>
      <h3 class="text-xl font-medium text-gray-900 mb-3 tracking-wide">No Minor Awards Available</h3>
      <p class="text-gray-500 max-w-md mx-auto leading-relaxed">Minor awards will appear once semi-final round scoring is complete.</p>
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
import { Trophy } from 'lucide-vue-next'

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
  gender?: string
  is_pair?: boolean
  member_names?: string[]
  member_genders?: string[]
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

const getTitle = (winner: WinnerInfo): string => {
  if (winner.is_pair && winner.member_genders && winner.member_genders.length > 0) {
    return winner.member_genders.map(g => g === 'male' ? 'Mr' : 'Miss').join(' & ')
  }
  if (winner.gender === 'male') return 'Mr'
  if (winner.gender === 'female') return 'Miss'
  return ''
}
</script>

<style scoped>
@media print {
  .bg-white, 
  .bg-gray-100 {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .border-gray-200,
  .border-gray-300,
  .ring-gray-300 {
    border-color: #d1d5db !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .text-gray-800,
  .text-gray-900,
  .text-gray-600,
  .text-gray-500 {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .bg-gray-800 {
    background-color: #1f2937 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>


