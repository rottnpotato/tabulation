<template>
  <div class="print-document bg-white text-black font-serif">
    <!-- Report Header - Show only if NOT a gender-split category (unified header shown in parent) -->
    <div v-if="!isMaleCategory && !isFemaleCategory" class="flex items-center gap-4 mb-8 pb-4 border-b-2 border-black">
      <!-- Logo -->
      <div v-if="getLogoUrl" class="flex-shrink-0">
        <img :src="getLogoUrl" alt="Pageant Logo" class="w-20 h-20 object-contain rounded-lg border border-gray-200" />
      </div>
      <!-- Header Content -->
      <div class="flex-1 text-center" :class="{ 'pr-20': getLogoUrl }">
        <h1 class="text-2xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
        <div class="text-sm uppercase tracking-widest text-gray-600 mb-4">Official Tabulation Report</div>
        
        <div class="flex justify-center items-center gap-8 text-xs text-gray-600">
          <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
          <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
        </div>
        
        <div class="mt-6">
          <h2 class="text-xl font-bold text-black">{{ reportTitle || 'Final Results' }}</h2>
        </div>
      </div>
    </div>

    <!-- Top Winners Summary - Compact Layout for Gender Split (Pair Pageants) -->
    <div v-if="shouldShowPodium && (isMaleCategory || isFemaleCategory) && topThree.length > 0" class="mb-6">
      <div class="space-y-3">
        <!-- First Place / Winner -->
        <div v-if="topThree[0]" class="text-center">
          <div class="text-xs font-bold uppercase text-amber-700 mb-1">🏆 {{ getWinnerTitle(topThree[0]) }}</div>
          <div class="border-2 border-amber-500 p-3 rounded bg-amber-50">
            <div class="text-lg font-bold">#{{ topThree[0].number }}</div>
            <div class="text-sm font-semibold text-gray-800">{{ capitalizeName(topThree[0].name) }}</div>
            <div v-if="topThree[0]?.is_pair && topThree[0]?.member_names && topThree[0]?.member_names!.length > 0" class="text-[10px] text-gray-600 italic mt-0.5">
              {{ topThree[0].member_names?.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-xs font-bold text-amber-700 mt-1">{{ formatScore(topThree[0].final_score) }} pts</div>
          </div>
        </div>
        <!-- Runner-ups -->
        <div class="grid grid-cols-2 gap-2">
          <!-- 1st Runner-up -->
          <div v-if="topThree[1]" class="text-center">
            <div class="text-[10px] font-bold uppercase text-gray-600 mb-0.5">1st Runner-up</div>
            <div class="border border-gray-300 p-2 rounded bg-gray-50">
              <div class="text-base font-bold">#{{ topThree[1].number }}</div>
              <div class="text-xs font-semibold text-gray-700">{{ capitalizeName(topThree[1].name) }}</div>
              <div v-if="topThree[1]?.is_pair && topThree[1]?.member_names && topThree[1].member_names!.length > 0" class="text-[9px] text-gray-500 italic">
                {{ topThree[1].member_names?.map(n => capitalizeName(n)).join(' & ') }}
              </div>
              <div class="text-[10px] text-gray-600">{{ formatScore(topThree[1].final_score) }} pts</div>
            </div>
          </div>
          <!-- 2nd Runner-up -->
          <div v-if="topThree[2]" class="text-center">
            <div class="text-[10px] font-bold uppercase text-gray-600 mb-0.5">2nd Runner-up</div>
            <div class="border border-gray-300 p-2 rounded bg-gray-50">
              <div class="text-base font-bold">#{{ topThree[2].number }}</div>
              <div class="text-xs font-semibold text-gray-700">{{ capitalizeName(topThree[2].name) }}</div>
              <div v-if="topThree[2]?.is_pair && topThree[2]?.member_names && topThree[2]?.member_names!.length > 0" class="text-[9px] text-gray-500 italic">
                {{ topThree[2].member_names?.map(n => capitalizeName(n)).join(' & ') }}
              </div>
              <div class="text-[10px] text-gray-600">{{ formatScore(topThree[2].final_score) }} pts</div>
            </div>
          </div>
        </div>
        <!-- Additional Runner-ups (4th, 5th, etc.) -->
        <div v-if="topThree.length > 3" class="grid grid-cols-3 gap-2">
          <div v-for="(result, idx) in topThree.slice(3)" :key="result.id" class="text-center">
            <div class="text-[9px] font-bold uppercase text-gray-500 mb-0.5">{{ getOrdinalSuffix(idx + 3) }} Runner-up</div>
            <div class="border border-gray-200 p-1.5 rounded bg-gray-50">
              <div class="text-sm font-bold">#{{ result.number }}</div>
              <div class="text-[10px] font-medium text-gray-700">{{ capitalizeName(result.name) }}</div>
              <div class="text-[9px] text-gray-500">{{ formatScore(result.final_score) }} pts</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Winners Summary - Traditional Layout for Non-Split (Solo Pageants) -->
    <div v-if="shouldShowPodium && !isMaleCategory && !isFemaleCategory && topThree.length > 0" class="mb-8">
      <div class="grid grid-cols-3 gap-4 items-end">
        <!-- 1st Runner-up (2nd Place) -->
        <div v-if="topThree[1]" class="text-center pb-4">
          <div class="text-sm font-bold uppercase text-gray-600 mb-2">1st Runner-up</div>
          <div class="border-2 border-gray-400 p-4 rounded bg-gray-50">
            <div class="text-2xl font-bold mb-2">#{{ topThree[1].number }}</div>
            <div class="text-sm font-semibold text-gray-700 mb-1">{{ capitalizeName(topThree[1].name) }}</div>
            <div v-if="topThree[1].is_pair && topThree[1].member_names && topThree[1].member_names.length > 0" class="text-xs text-gray-600 italic mb-2">
              {{ topThree[1].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-sm font-semibold text-gray-700">{{ formatScore(topThree[1].final_score) }} pts</div>
          </div>
        </div>

        <!-- Winner (1st Place) -->
        <div v-if="topThree[0]" class="text-center">
          <div class="text-base font-bold uppercase text-amber-700 mb-2">🏆 {{ getWinnerTitle(topThree[0]) }}</div>
          <div class="border-4 border-amber-500 p-5 rounded bg-amber-50 shadow-lg">
            <div class="text-3xl font-bold mb-2">#{{ topThree[0].number }}</div>
            <div class="text-base font-bold text-gray-800 mb-1">{{ capitalizeName(topThree[0].name) }}</div>
            <div v-if="topThree[0].is_pair && topThree[0].member_names && topThree[0].member_names.length > 0" class="text-sm text-gray-700 italic mb-2">
              {{ topThree[0].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-base font-bold text-amber-700">{{ formatScore(topThree[0].final_score) }} pts</div>
          </div>
        </div>

        <!-- 2nd Runner-up (3rd Place) -->
        <div v-if="topThree[2]" class="text-center pb-4">
          <div class="text-sm font-bold uppercase text-gray-600 mb-2">2nd Runner-up</div>
          <div class="border-2 border-gray-400 p-4 rounded bg-gray-50">
            <div class="text-2xl font-bold mb-2">#{{ topThree[2].number }}</div>
            <div class="text-sm font-semibold text-gray-700 mb-1">{{ capitalizeName(topThree[2].name) }}</div>
            <div v-if="topThree[2].is_pair && topThree[2].member_names && topThree[2].member_names.length > 0" class="text-xs text-gray-600 italic mb-2">
              {{ topThree[2].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-sm font-semibold text-gray-700">{{ formatScore(topThree[2].final_score) }} pts</div>
          </div>
        </div>
      </div>
      
      <!-- Additional Runner-ups Row (4th, 5th, etc. if numberOfWinners > 3) -->
      <div v-if="topThree.length > 3" class="mt-4 grid grid-cols-4 gap-3">
        <div v-for="(result, idx) in topThree.slice(3)" :key="result.id" class="text-center">
          <div class="text-xs font-bold uppercase text-gray-500 mb-1">{{ getOrdinalSuffix(idx + 3) }} Runner-up</div>
          <div class="border border-gray-300 p-3 rounded bg-gray-50">
            <div class="text-xl font-bold mb-1">#{{ result.number }}</div>
            <div class="text-sm font-medium text-gray-700">{{ capitalizeName(result.name) }}</div>
            <div v-if="result.is_pair && result.member_names && result.member_names.length > 0" class="text-xs text-gray-500 italic">
              {{ result.member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-xs text-gray-600">{{ formatScore(result.final_score) }} pts</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Results Table -->
    <div class="mb-6">
      <table class="w-full border-collapse text-[10px]">
        <thead>
          <tr class="bg-gray-100 border-y-2 border-black">
            <th v-if="!hideRankColumn" class="py-1 px-1 text-left font-bold w-8">Rank</th>
            <th class="py-1 px-1 text-left font-bold w-6">#</th>
            <th class="py-1 px-2 text-left font-bold">Contestant</th>
            <!-- Show all round columns when showAllRounds is true -->
            <template v-if="showAllRounds && rounds && rounds.length > 0">
              <th 
                v-for="round in rounds" 
                :key="round.id"
                class="py-1 px-1 text-center font-bold w-16 border-l border-gray-300"
              >
                <div class="flex flex-col items-center">
                  <span class="truncate max-w-[60px]">{{ round.name }}</span>
                  <span class="text-[8px] font-normal opacity-75 uppercase">{{ round.type }}</span>
                </div>
              </th>
            </template>
            <th class="py-1 px-1 text-center font-bold w-12 border-l-2 border-black">
              <span v-if="isLastFinalRound">Final Result (Top {{ numberOfWinners }})</span>
              <span v-else>Final Score</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(result, index) in results" 
            :key="result.id" 
            class="border-b border-gray-200"
            :class="{'bg-gray-50': index % 2 === 0}"
          >
            <td v-if="!hideRankColumn" class="py-1 px-1 font-bold">
              {{ index + 1 }}
            </td>
            <td class="py-1 px-1">
              {{ result.number }}
            </td>
            <td class="py-1 px-2">
              <div class="font-semibold">{{ capitalizeName(result.name) }}</div>
              <div v-if="result.is_pair && result.member_names && result.member_names.length > 0" class="text-[8px] text-gray-500">
                {{ result.member_names.map(n => capitalizeName(n)).join(' & ') }}
              </div>
            </td>
            <!-- Show scores for each round when showAllRounds is true -->
            <template v-if="showAllRounds && rounds && rounds.length > 0">
              <td 
                v-for="round in rounds" 
                :key="round.id"
                class="py-1 px-1 text-center tabular-nums border-l border-gray-300"
                :class="result.scores[round.name] !== undefined ? 'text-gray-900' : 'text-gray-300'"
              >
                <span v-if="result.scores[round.name] !== undefined">{{ formatScore(result.scores[round.name]) }}</span>
                <span v-else class="italic">—</span>
              </td>
            </template>
            <td class="py-1 px-1 text-center font-bold tabular-nums border-l-2 border-black">
              {{ formatScore(result.final_score) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Signatures Section - Always show for all categories -->
    <div v-if="!isMaleCategory && !isFemaleCategory" class="mt-12 page-break-inside-avoid">
      <div class="grid grid-cols-3 gap-8">
        <!-- Judges Signatures -->
        <div class="col-span-3 mb-8">
          <h3 class="text-xs font-bold uppercase border-b border-black mb-4 pb-1">Panel of Judges</h3>
          <div class="grid grid-cols-3 gap-x-8 gap-y-12">
            <div v-for="judge in judges" :key="judge.id" class="text-center">
              <div class="border-b border-gray-400 h-8"></div>
              <div class="text-xs font-bold mt-1">{{ capitalizeName(judge.name) }}</div>
              <div class="text-[10px] uppercase text-gray-500">{{ judge.role }}</div>
            </div>
          </div>
        </div>

        <!-- Certification -->
        <div class="col-span-3">
          <div class="flex justify-end">
            <div class="w-64 text-center">
              <div class="text-[10px] uppercase text-gray-500 mb-8 text-left">Certified Correct:</div>
              <div class="border-b border-black h-8"></div>
              <div class="text-xs font-bold mt-1">Head Tabulator</div>
              <div class="text-[10px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 pt-2 border-t border-gray-200 text-[10px] text-gray-400 flex justify-between">
      <div>Generated via Tabulator System</div>
      <div>Page 1 of 1</div>
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
  logo?: string
}

interface Judge {
  id: number
  name: string
  role: string
}

interface Round {
  id: number
  name: string
  type: string
  display_order: number
}

interface Props {
  pageant: Pageant
  results: Result[]
  judges: Judge[]
  rounds?: Round[]
  reportTitle?: string
  isMaleCategory?: boolean
  isFemaleCategory?: boolean
  isLastFinalRound?: boolean
  numberOfWinners?: number
  hideRankColumn?: boolean
  showAllRounds?: boolean
  selectedStage?: string
}

const props = withDefaults(defineProps<Props>(), {
  numberOfWinners: 3,
  hideRankColumn: false,
  showAllRounds: false,
  selectedStage: ''
})

// Get pageant logo URL
const getLogoUrl = computed(() => {
  const logo = props.pageant?.logo
  if (!logo || typeof logo !== 'string') return null
  if (logo.startsWith('http') || logo.startsWith('//') || logo.startsWith('/')) {
    return logo
  }
  return `/storage/${logo}`
})

const topThree = computed(() => {
  // For last final round, show top N winners based on numberOfWinners
  if (props.isLastFinalRound) {
    return props.results.slice(0, props.numberOfWinners)
  }
  // For other rounds, show all results (no special podium display)
  return props.results.slice(0, props.numberOfWinners)
})

const shouldShowPodium = computed(() => {
  // Show podium for final round or semi-final round (not for overall tally)
  const stage = props.selectedStage?.toLowerCase() || ''
  const isFinalOrSemiFinal = props.isLastFinalRound || stage === 'semi-final' || stage === 'semifinal' || stage.includes('final')
  return isFinalOrSemiFinal && props.results.length > 0 && stage !== 'overall'
})

// Determine if this is a semi-final stage
const isSemiFinalStage = computed(() => {
  const stage = props.selectedStage?.toLowerCase() || ''
  return stage === 'semi-final' || stage === 'semifinal'
})

const isPairCategory = computed(() => {
  return props.results.length > 0 && props.results[0].is_pair
})

// Get gender prefix (Mr/Ms) based on category or individual gender
const getGenderPrefix = (result: Result): string => {
  if (props.isMaleCategory || result.gender === 'male') return 'Mr'
  if (props.isFemaleCategory || result.gender === 'female') return 'Ms'
  return ''
}

// Get the winner title (e.g., "Mr Pageant Name" or "Ms Pageant Name")
const getWinnerTitle = (result: Result): string => {
  const prefix = getGenderPrefix(result)
  if (prefix) {
    return `${prefix} ${props.pageant.name}`
  }
  // For pairs without specific gender
  if (result.is_pair && result.member_genders && result.member_genders.length > 0) {
    const prefixes = result.member_genders.map(g => g === 'male' ? 'Mr' : 'Ms')
    return `${prefixes[0]} & ${prefixes[1]} ${props.pageant.name}`
  }
  return props.pageant.name
}

// Get runner-up label with proper ordinal
const getRunnerUpLabel = (position: number): string => {
  const ordinal = getOrdinalSuffix(position)
  return `${ordinal} Runner-up`
}

const getTitle = (result: Result): string => {
  const resultIndex = props.results.findIndex(r => r.id === result.id)
  const stage = props.selectedStage?.toLowerCase() || ''
  
  // For final round or semi-final, show special titles
  const isFinalOrSemiFinal = props.isLastFinalRound || stage === 'semi-final' || stage === 'semifinal' || stage.includes('final')
  
  if (isFinalOrSemiFinal && stage !== 'overall') {
    if (resultIndex === 0) {
      // Winner - show Mr/Ms [Pageant Name]
      return getWinnerTitle(result)
    } else if (resultIndex < props.numberOfWinners) {
      // Runner-ups with proper ordinal suffixes
      return getRunnerUpLabel(resultIndex)
    }
  }
  
  // For other rounds or results beyond numberOfWinners, show Top X
  return `Top ${resultIndex + 1}`
}

const getOrdinalSuffix = (num: number): string => {
  const j = num % 10
  const k = num % 100
  if (j === 1 && k !== 11) {
    return num + 'st'
  }
  if (j === 2 && k !== 12) {
    return num + 'nd'
  }
  if (j === 3 && k !== 13) {
    return num + 'rd'
  }
  return num + 'th'
}

const getScoreHeaders = () => {
  if (props.results.length === 0) return {}
  return props.results[0].scores || {}
}

const formatScore = (score: number): string => {
  return score.toFixed(2)
}

const capitalizeName = (name: string): string => {
  if (!name) return ''
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ')
}
</script>

<style scoped>
.print-document {
  max-width: 100%;
  margin: 0 auto;
}

@media print {
  .print-document {
    width: 100%;
  }
  
  /* Ensure background colors print */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
