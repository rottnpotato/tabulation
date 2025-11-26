<template>
  <div class="print-document bg-white text-black font-serif">
    <!-- Report Header - Show only if NOT a gender-split category (unified header shown in parent) -->
    <div v-if="!isMaleCategory && !isFemaleCategory" class="text-center mb-8 pb-4 border-b-2 border-black">
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

    <!-- Top Winners Summary - Side by Side for Gender Split (Vertical stacked) -->
    <div v-if="(isMaleCategory || isFemaleCategory) && shouldShowPodium" class="mb-6">
      <div v-if="topThree.length > 0" class="space-y-2">
        <!-- First Place -->
        <div v-if="topThree[0]" class="text-center">
          <div class="text-xs font-bold uppercase text-black mb-1">🏆 Winner</div>
          <div class="border-3 border-yellow-600 p-3 rounded bg-yellow-50">
            <div class="text-xl font-bold">#{{ topThree[0].number }}</div>
            <div class="text-sm font-bold">{{ getTitle(topThree[0]) }} {{ capitalizeName(topThree[0].name) }}</div>
            <div v-if="topThree[0].is_pair && topThree[0].member_names" class="text-[10px] text-gray-700 italic mt-0.5">
              {{ topThree[0].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-xs font-bold text-yellow-800 mt-1">{{ formatScore(topThree[0].final_score) }} pts</div>
          </div>
        </div>
        <!-- Second Place -->
        <div v-if="topThree[1]" class="text-center">
          <div class="text-[10px] font-bold uppercase text-gray-600 mb-0.5">2nd Place</div>
          <div class="border-2 border-gray-400 p-2 rounded bg-gray-50">
            <div class="text-base font-bold">#{{ topThree[1].number }}</div>
            <div class="text-xs font-bold">{{ getTitle(topThree[1]) }} {{ capitalizeName(topThree[1].name) }}</div>
            <div v-if="topThree[1].is_pair && topThree[1].member_names" class="text-[9px] text-gray-600 italic">
              {{ topThree[1].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-[10px] text-gray-700">{{ formatScore(topThree[1].final_score) }} pts</div>
          </div>
        </div>
        <!-- Third Place -->
        <div v-if="topThree[2]" class="text-center">
          <div class="text-[10px] font-bold uppercase text-gray-600 mb-0.5">3rd Place</div>
          <div class="border-2 border-gray-400 p-2 rounded bg-gray-50">
            <div class="text-base font-bold">#{{ topThree[2].number }}</div>
            <div class="text-xs font-bold">{{ getTitle(topThree[2]) }} {{ capitalizeName(topThree[2].name) }}</div>
            <div v-if="topThree[2].is_pair && topThree[2].member_names" class="text-[9px] text-gray-600 italic">
              {{ topThree[2].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-[10px] text-gray-700">{{ formatScore(topThree[2].final_score) }} pts</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Winners Summary - Traditional Layout for Non-Split -->
    <div v-else-if="shouldShowPodium && topThree.length > 0" class="mb-8">
      <div class="grid grid-cols-3 gap-4 items-end">
        <!-- Second Place -->
        <div v-if="topThree[1]" class="text-center pb-4">
          <div class="text-sm font-bold uppercase text-gray-600 mb-2">2nd Place</div>
          <div class="border-2 border-gray-400 p-4 rounded bg-gray-50">
            <div class="text-2xl font-bold mb-2">#{{ topThree[1].number }}</div>
            <div class="text-base font-bold mb-1">{{ getTitle(topThree[1]) }} {{ capitalizeName(topThree[1].name) }}</div>
            <div v-if="topThree[1].is_pair && topThree[1].member_names && topThree[1].member_names.length > 0" class="text-xs text-gray-600 italic mb-2">
              {{ topThree[1].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-sm font-semibold text-gray-700">{{ formatScore(topThree[1].final_score) }} pts</div>
          </div>
        </div>

        <!-- First Place -->
        <div v-if="topThree[0]" class="text-center">
          <div class="text-base font-bold uppercase text-black mb-2">🏆 Winner</div>
          <div class="border-4 border-yellow-600 p-5 rounded bg-yellow-50 shadow-lg">
            <div class="text-3xl font-bold mb-2">#{{ topThree[0].number }}</div>
            <div class="text-lg font-bold mb-1">{{ getTitle(topThree[0]) }} {{ capitalizeName(topThree[0].name) }}</div>
            <div v-if="topThree[0].is_pair && topThree[0].member_names && topThree[0].member_names.length > 0" class="text-sm text-gray-700 italic mb-2">
              {{ topThree[0].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-base font-bold text-yellow-800">{{ formatScore(topThree[0].final_score) }} pts</div>
          </div>
        </div>

        <!-- Third Place -->
        <div v-if="topThree[2]" class="text-center pb-4">
          <div class="text-sm font-bold uppercase text-gray-600 mb-2">3rd Place</div>
          <div class="border-2 border-gray-400 p-4 rounded bg-gray-50">
            <div class="text-2xl font-bold mb-2">#{{ topThree[2].number }}</div>
            <div class="text-base font-bold mb-1">{{ getTitle(topThree[2]) }} {{ capitalizeName(topThree[2].name) }}</div>
            <div v-if="topThree[2].is_pair && topThree[2].member_names && topThree[2].member_names.length > 0" class="text-xs text-gray-600 italic mb-2">
              {{ topThree[2].member_names.map(n => capitalizeName(n)).join(' & ') }}
            </div>
            <div class="text-sm font-semibold text-gray-700">{{ formatScore(topThree[2].final_score) }} pts</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Results Table -->
    <div class="mb-6">
      <table class="w-full border-collapse text-[10px]">
        <thead>
          <tr class="bg-gray-100 border-y-2 border-black">
            <th class="py-1 px-1 text-left font-bold w-8">Rank</th>
            <th class="py-1 px-1 text-left font-bold w-6">#</th>
            <th class="py-1 px-2 text-left font-bold">Contestant</th>
            <th class="py-1 px-1 text-center font-bold w-12 border-l-2 border-black">Final Score</th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(result, index) in results" 
            :key="result.id" 
            class="border-b border-gray-200"
            :class="{'bg-gray-50': index % 2 === 0}"
          >
            <td class="py-1 px-1 font-bold">
              {{ index + 1 }}
            </td>
            <td class="py-1 px-1">
              {{ result.number }}
            </td>
            <td class="py-1 px-2">
              <div class="font-semibold">{{ getTitle(result) }} {{ capitalizeName(result.name) }}</div>
              <div v-if="result.is_pair && result.member_names" class="text-[8px] text-gray-500">
                {{ result.member_names.map(n => capitalizeName(n)).join(' & ') }}
              </div>
            </td>
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
  isMaleCategory?: boolean
  isFemaleCategory?: boolean
  isLastFinalRound?: boolean
  numberOfWinners?: number
}

const props = withDefaults(defineProps<Props>(), {
  numberOfWinners: 3
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
  // Only show the podium display for last final round with results
  return props.isLastFinalRound && props.results.length > 0
})

const isPairCategory = computed(() => {
  return props.results.length > 0 && props.results[0].is_pair
})

const getTitle = (result: Result): string => {
  // For last final round, show special titles
  if (props.isLastFinalRound) {
    const resultIndex = props.results.findIndex(r => r.id === result.id)
    
    if (resultIndex === 0) {
      // Winner - show Mr/Miss [Pageant Name]
      if (result.is_pair && result.member_genders && result.member_genders.length > 0) {
        return result.member_genders.map(g => (g === 'male' ? 'Mr' : 'Miss') + ' ' + props.pageant.name).join(' & ')
      }
      if (result.gender === 'male') return `Mr ${props.pageant.name}`
      if (result.gender === 'female') return `Miss ${props.pageant.name}`
      return `Winner ${props.pageant.name}`
    } else if (resultIndex < props.numberOfWinners) {
      // Runner-ups with proper ordinal suffixes (only for results within numberOfWinners)
      const position = resultIndex
      const ordinal = getOrdinalSuffix(position)
      return `${ordinal} Runner-up`
    }
  }
  
  // For other rounds or results beyond numberOfWinners, show Top X
  const resultIndex = props.results.findIndex(r => r.id === result.id)
  if (result.is_pair && result.member_genders && result.member_genders.length > 0) {
    return `Top ${resultIndex + 1}`
  }
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
