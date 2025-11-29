<template>
  <div class="p-4 bg-white">
    <!-- Header with Logo -->
    <div class="mb-3 border-b border-gray-200 pb-2">
      <div class="flex items-center justify-center gap-2">
        <img v-if="pageant.logo" :src="pageant.logo" alt="Pageant Logo" class="h-10 w-10 object-contain" />
        <div class="text-center">
          <h1 class="text-xl font-bold text-gray-900">{{ capitalize(pageant.name) }}</h1>
          <p class="text-xs text-gray-600">Semi-Final Minor Awards</p>
          <div v-if="pageant.date || pageant.venue || pageant.location" class="mt-0.5 text-[10px] text-gray-500">
            <span v-if="pageant.date">{{ pageant.date }}</span>
            <span v-if="pageant.venue"> • {{ pageant.venue }}</span>
            <span v-if="pageant.location"> • {{ pageant.location }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Awards Grid -->
    <div v-if="roundEntries.length > 0" class="space-y-3">
      <div
        v-for="(entry, idx) in roundEntries"
        :key="entry.round.id + '-' + idx"
        class="border-b border-gray-300 pb-2"
      >
        <div class="mb-2">
          <h2 class="text-sm font-bold text-center text-gray-800 tracking-wide uppercase">
            Best in {{ entry.round.name }}
          </h2>
        </div>
        <div class="px-1">
          <!-- For pair pageants, show separate male and female winners side by side -->
          <template v-if="entry.is_pair_pageant || isPairPageant">
            <div class="grid grid-cols-2 gap-3">
              <!-- Male Winners Section -->
              <div v-if="entry.male_winners && entry.male_winners.length > 0" class="flex flex-col">
                <div class="text-center mb-1">
                  <div class="inline-flex items-center gap-1 px-1.5 py-0.5 text-blue-700 text-[10px] font-semibold">
                    <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-blue-100 text-[9px]">♂</span>
                    Male
                  </div>
                </div>
                <div class="flex flex-col items-center gap-2">
                  <div v-for="winner in entry.male_winners" :key="winner.id" 
                    class="flex flex-col items-center text-center w-full">
                    <div class="relative mb-1">
                      <img :src="winner.image" :alt="winner.name" 
                        class="h-12 w-12 rounded-full object-cover ring-1 ring-gray-300" />
                      <div class="absolute -bottom-0.5 -right-0.5 bg-gray-800 text-white text-[9px] rounded-full w-4 h-4 flex items-center justify-center font-semibold">
                        {{ winner.number }}
                      </div>
                    </div>
                    <div class="space-y-0">
                      <h3 class="text-[10px] font-semibold text-gray-900 leading-tight">
                        <span class="text-blue-600">{{ getTitle(winner) }}</span> {{ capitalize(winner.name) }}
                      </h3>
                      <p v-if="winner.is_pair && winner.member_names && winner.member_names.length > 0" class="text-[9px] text-gray-500 italic leading-tight">
                        {{ winner.member_names.map(name => capitalize(name)).join(' & ') }}
                      </p>
                      <div class="text-[10px] font-medium text-gray-900">{{ formatScore(winner.score) }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Female Winners Section -->
              <div v-if="entry.female_winners && entry.female_winners.length > 0" class="flex flex-col">
                <div class="text-center mb-1">
                  <div class="inline-flex items-center gap-1 px-1.5 py-0.5 text-pink-700 text-[10px] font-semibold">
                    <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-pink-100 text-[9px]">♀</span>
                    Female
                  </div>
                </div>
                <div class="flex flex-col items-center gap-2">
                  <div v-for="winner in entry.female_winners" :key="winner.id" 
                    class="flex flex-col items-center text-center w-full">
                    <div class="relative mb-1">
                      <img :src="winner.image" :alt="winner.name" 
                        class="h-12 w-12 rounded-full object-cover ring-1 ring-gray-300" />
                      <div class="absolute -bottom-0.5 -right-0.5 bg-gray-800 text-white text-[9px] rounded-full w-4 h-4 flex items-center justify-center font-semibold">
                        {{ winner.number }}
                      </div>
                    </div>
                    <div class="space-y-0">
                      <h3 class="text-[10px] font-semibold text-gray-900 leading-tight">
                        <span class="text-pink-600">{{ getTitle(winner) }}</span> {{ capitalize(winner.name) }}
                      </h3>
                      <p v-if="winner.is_pair && winner.member_names && winner.member_names.length > 0" class="text-[9px] text-gray-500 italic leading-tight">
                        {{ winner.member_names.map(name => capitalize(name)).join(' & ') }}
                      </p>
                      <div class="text-[10px] font-medium text-gray-900">{{ formatScore(winner.score) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- Standard single winner display (non-pair pageants) -->
          <template v-else>
            <div class="flex flex-wrap justify-center gap-3">
              <div
                v-for="winner in entry.winners"
                :key="winner.id"
                class="flex flex-col items-center text-center"
              >
                <div class="relative mb-1">
                  <img :src="winner.image" :alt="winner.name" 
                    class="h-12 w-12 rounded-full object-cover ring-1 ring-gray-300" />
                  <div class="absolute -bottom-0.5 -right-0.5 bg-gray-800 text-white text-[9px] rounded-full w-4 h-4 flex items-center justify-center font-semibold">
                    {{ winner.number }}
                  </div>
                </div>
                
                <div class="space-y-0 min-w-[100px]">
                  <h3 class="text-[10px] font-semibold text-gray-900 leading-tight">
                    <span class="text-teal-600">{{ getTitle(winner) }}</span> {{ capitalize(winner.name) }}
                  </h3>
                  <p v-if="winner.is_pair && winner.member_names && winner.member_names.length > 0" class="text-[9px] text-gray-500 italic leading-tight">
                    {{ winner.member_names.map(name => capitalize(name)).join(' & ') }}
                  </p>
                  <div class="text-[10px] font-medium text-gray-900">{{ formatScore(winner.score) }}</div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-3">
      <p class="text-[10px] text-gray-500">Minor awards will appear once semi-final round scoring is complete.</p>
    </div>

    <!-- Judges Section -->
    <div class="mt-3 pt-1.5 border-t border-gray-200">
      <h3 class="text-[10px] font-semibold text-gray-900 mb-1 text-center">Panel of Judges</h3>
      <div class="flex flex-wrap justify-center gap-x-3 gap-y-0">
        <div v-for="judge in judges" :key="judge.id" class="text-center">
          <p class="text-[9px] font-medium text-gray-900">{{ judge.name }}</p>
        </div>
      </div>
    </div>

    <!-- Tabulator Certification -->
    <div class="mt-2 pt-1.5 border-t border-gray-200">
      <div class="text-center">
        <p class="text-[9px] font-semibold text-gray-900">Certified by: Tabulator • {{ new Date().toLocaleDateString() }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Trophy } from 'lucide-vue-next'

interface PageantInfo {
  id: number
  name: string
  contestant_type?: string
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
  is_pair_pageant?: boolean
  male_winners?: WinnerInfo[]
  female_winners?: WinnerInfo[]
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
  return Object.values(props.awardsByRound)
})

const isPairPageant = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

const capitalize = (text: string): string => {
  if (!text) return ''
  return text.toLowerCase().split(' ').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

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
  @page {
    margin: 0.4in;
    size: letter;
  }
  
  /* Force single page */
  * {
    page-break-inside: avoid;
  }
  
  .space-y-3 > * + * {
    margin-top: 0.5rem !important;
  }
  
  .bg-white, 
  .bg-gray-100,
  .bg-blue-50,
  .bg-pink-50 {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .border-gray-200,
  .border-gray-300,
  .border-blue-200,
  .border-pink-200,
  .ring-gray-300 {
    border-color: #d1d5db !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .text-gray-800,
  .text-gray-900,
  .text-gray-600,
  .text-gray-500,
  .text-blue-600,
  .text-blue-700,
  .text-pink-600,
  .text-pink-700,
  .text-teal-600 {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .bg-gray-800,
  .bg-blue-100,
  .bg-pink-100 {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  /* Compress images slightly more on print */
  img {
    max-width: 2.5rem !important;
    max-height: 2.5rem !important;
  }
}
</style>


