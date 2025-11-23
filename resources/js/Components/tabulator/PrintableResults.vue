<template>
  <div class="print-document bg-white text-black font-serif">
    <!-- Report Header -->
    <div class="text-center mb-8 pb-4 border-b-2 border-black">
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

    <!-- Top Winners Summary (Compact) -->
    <div v-if="topThree.length > 0" class="mb-8">
      <div class="grid grid-cols-3 gap-4 items-end">
        <!-- Second Place -->
        <div v-if="topThree[1]" class="text-center pb-2">
          <div class="text-xs font-bold uppercase text-gray-500 mb-1">2nd Place</div>
          <div class="border border-gray-300 p-3 rounded bg-gray-50">
            <div class="text-lg font-bold">#{{ topThree[1].number }}</div>
            <div class="text-sm font-semibold truncate">{{ getTitle(topThree[1]) }} {{ topThree[1].name }}</div>
            <div v-if="topThree[1].is_pair && topThree[1].member_names && topThree[1].member_names.length > 0" class="text-[10px] text-gray-500 italic mt-0.5">
              {{ topThree[1].member_names.join(' & ') }}
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ formatScore(topThree[1].final_score) }} pts</div>
          </div>
        </div>

        <!-- First Place -->
        <div v-if="topThree[0]" class="text-center">
          <div class="text-sm font-bold uppercase text-black mb-1">Winner</div>
          <div class="border-2 border-black p-4 rounded bg-gray-100 relative">
            <div class="text-2xl font-bold">#{{ topThree[0].number }}</div>
            <div class="text-base font-bold truncate">{{ getTitle(topThree[0]) }} {{ topThree[0].name }}</div>
            <div v-if="topThree[0].is_pair && topThree[0].member_names && topThree[0].member_names.length > 0" class="text-[11px] text-gray-600 italic mt-1">
              {{ topThree[0].member_names.join(' & ') }}
            </div>
            <div class="text-sm font-bold mt-1">{{ formatScore(topThree[0].final_score) }} pts</div>
          </div>
        </div>

        <!-- Third Place -->
        <div v-if="topThree[2]" class="text-center pb-2">
          <div class="text-xs font-bold uppercase text-gray-500 mb-1">3rd Place</div>
          <div class="border border-gray-300 p-3 rounded bg-gray-50">
            <div class="text-lg font-bold">#{{ topThree[2].number }}</div>
            <div class="text-sm font-semibold truncate">{{ getTitle(topThree[2]) }} {{ topThree[2].name }}</div>
            <div v-if="topThree[2].is_pair && topThree[2].member_names && topThree[2].member_names.length > 0" class="text-[10px] text-gray-500 italic mt-0.5">
              {{ topThree[2].member_names.join(' & ') }}
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ formatScore(topThree[2].final_score) }} pts</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Results Table -->
    <div class="mb-8">
      <table class="w-full border-collapse text-xs">
        <thead>
          <tr class="bg-gray-100 border-y-2 border-black">
            <th class="py-2 px-2 text-left font-bold w-12">Rank</th>
            <th class="py-2 px-2 text-left font-bold w-12">#</th>
            <th class="py-2 px-2 text-left font-bold">Contestant</th>
            <th 
              v-for="(score, roundName) in getScoreHeaders()" 
              :key="roundName"
              class="py-2 px-2 text-center font-bold border-l border-gray-300"
            >
              {{ roundName }}
            </th>
            <th class="py-2 px-2 text-center font-bold border-l-2 border-black w-20">Final</th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(result, index) in results" 
            :key="result.id" 
            class="border-b border-gray-200"
            :class="{'bg-gray-50': index % 2 === 0}"
          >
            <td class="py-2 px-2 font-bold">
              {{ index + 1 }}
            </td>
            <td class="py-2 px-2">
              {{ result.number }}
            </td>
            <td class="py-2 px-2">
              <div class="font-semibold">{{ getTitle(result) }} {{ result.name }}</div>
              <div v-if="result.is_pair && result.member_names" class="text-[10px] text-gray-500">
                {{ result.member_names.join(' & ') }}
              </div>
            </td>
            <td 
              v-for="(score, roundName) in getScoreHeaders()" 
              :key="roundName"
              class="py-2 px-2 text-center border-l border-gray-300 tabular-nums"
            >
              {{ formatScore(result.scores[roundName] || 0) }}
            </td>
            <td class="py-2 px-2 text-center font-bold border-l-2 border-black tabular-nums">
              {{ formatScore(result.final_score) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Signatures Section -->
    <div class="mt-12 page-break-inside-avoid">
      <div class="grid grid-cols-3 gap-8">
        <!-- Judges Signatures -->
        <div class="col-span-3 mb-8">
          <h3 class="text-xs font-bold uppercase border-b border-black mb-4 pb-1">Panel of Judges</h3>
          <div class="grid grid-cols-3 gap-x-8 gap-y-12">
            <div v-for="judge in judges" :key="judge.id" class="text-center">
              <div class="border-b border-gray-400 h-8"></div>
              <div class="text-xs font-bold mt-1">{{ judge.name }}</div>
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
}

const props = defineProps<Props>()

const topThree = computed(() => {
  return props.results.slice(0, 3)
})

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
  return props.results[0].scores || {}
}

const formatScore = (score: number): string => {
  return score.toFixed(2)
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
