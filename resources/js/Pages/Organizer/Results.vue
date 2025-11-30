<template>
  <div class="min-h-screen bg-slate-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-teal-100">
        <!-- Abstract Background Pattern -->
        <div class="absolute inset-0">
          <div class="absolute inset-0 bg-gradient-to-br from-teal-50 via-teal-50/50 to-white opacity-90"></div>
          <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
          <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div class="relative z-10 p-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
              <div class="flex items-center gap-3 mb-2">
                <Link 
                  :href="route('organizer.pageant.view', { id: pageant.id })" 
                  class="inline-flex items-center text-sm font-medium text-teal-600 hover:text-teal-700 transition-colors"
                >
                  <ChevronLeft class="h-4 w-4 mr-1" />
                  Back to Pageant
                </Link>
              </div>
              <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                {{ pageant.name }} - Results
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                View overall and final results for this pageant
              </p>
            </div>
            
            <div class="flex items-center space-x-3">
              <Link 
                :href="route('organizer.pageant.view', { id: pageant.id })" 
                class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all"
              >
                <LayoutDashboard class="h-4 w-4 mr-2 text-slate-500" />
                Dashboard
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Warning: Not All Rounds Locked -->
      <!-- <div v-if="pageant && !allRoundsLocked" class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <AlertCircle class="h-6 w-6 text-amber-600" />
          </div>
          <div class="flex-1">
            <h3 class="text-sm font-medium text-amber-800 mb-1">Results May Be Incomplete</h3>
            <p class="text-sm text-amber-700 mb-3">
              Some rounds are not yet locked. Results shown may not reflect the final scores.
            </p>
            <div v-if="unlockedRounds && unlockedRounds.length > 0" class="text-sm text-amber-600">
              <span class="font-medium">Unlocked rounds:</span>
              <ul class="list-disc list-inside mt-1">
                <li v-for="round in unlockedRounds" :key="round.id">
                  {{ round.name }} ({{ round.type }})
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div> -->

      <!-- Stage Selection and Results -->
      <div class="space-y-8">
        <!-- Stage Selection Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Stage</label>
            <CustomSelect
              v-model="selectedStage"
              :options="stageOptions"
              variant="teal"
            />
          </div>
        </div>

        <!-- Results Display Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-teal-50 rounded-lg">
                <Award class="h-5 w-5 text-teal-600" />
              </div>
              <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ reportTitle }}</h2>
                <p class="text-sm text-slate-500">{{ resultsToShow.length }} {{ isPairPageant ? 'pairs' : 'contestants' }}</p>
              </div>
            </div>
          </div>
          
          <div class="p-8 bg-slate-100/50">
            <!-- For pair pageants, show side by side -->
            <template v-if="isPairPageant && maleResults.length > 0 && femaleResults.length > 0">
              <div class="grid grid-cols-2 gap-6">
                <!-- Male Column -->
                <div class="border-r-2 border-slate-200 pr-3">
                  <div class="mb-4 pb-2 border-b border-blue-200">
                    <h3 class="text-lg font-bold text-blue-900">Male Category</h3>
                  </div>
                  <ResultsTable
                    :pageant="pageant"
                    :results="maleResults"
                    :judges="judges"
                    :rounds="rounds"
                    :is-male-category="true"
                    :show-all-rounds="selectedStage === 'overall'"
                  />
                </div>
                
                <!-- Female Column -->
                <div class="pl-3">
                  <div class="mb-4 pb-2 border-b border-pink-200">
                    <h3 class="text-lg font-bold text-pink-900">Female Category</h3>
                  </div>
                  <ResultsTable
                    :pageant="pageant"
                    :results="femaleResults"
                    :judges="judges"
                    :rounds="rounds"
                    :is-female-category="true"
                    :show-all-rounds="selectedStage === 'overall'"
                  />
                </div>
              </div>
            </template>
            
            <!-- Single gender or standard pageant -->
            <template v-else-if="isPairPageant">
              <div v-if="maleResults.length > 0" class="mb-8">
                <div class="mb-4 pb-2 border-b-2 border-blue-300">
                  <h3 class="text-xl font-bold text-blue-900">Male Category</h3>
                </div>
                <ResultsTable
                  :pageant="pageant"
                  :results="maleResults"
                  :judges="judges"
                  :rounds="rounds"
                  :is-male-category="true"
                  :show-all-rounds="selectedStage === 'overall'"
                />
              </div>
              
              <div v-if="femaleResults.length > 0">
                <div class="mb-4 pb-2 border-b-2 border-pink-300">
                  <h3 class="text-xl font-bold text-pink-900">Female Category</h3>
                </div>
                <ResultsTable
                  :pageant="pageant"
                  :results="femaleResults"
                  :judges="judges"
                  :rounds="rounds"
                  :is-female-category="true"
                  :show-all-rounds="selectedStage === 'overall'"
                />
              </div>
            </template>
            
            <!-- Standard single ranking -->
            <ResultsTable
              v-else
              :pageant="pageant"
              :results="resultsToShow"
              :judges="judges"
              :rounds="rounds"
              :show-all-rounds="selectedStage === 'overall'"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ChevronLeft, LayoutDashboard, Award, AlertCircle } from 'lucide-vue-next'
import { route } from 'ziggy-js'
import ResultsTable from '@/Components/organizer/ResultsTable.vue'
import CustomSelect from '@/Components/CustomSelect.vue'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  rounds: {
    type: Array,
    required: true
  },
  roundTypes: {
    type: Array,
    required: true
  },
  overallTally: {
    type: Array,
    required: true
  },
  finalTopN: {
    type: Array,
    required: true
  },
  judges: {
    type: Array,
    required: true
  },
  allRoundsLocked: {
    type: Boolean,
    default: false
  },
  unlockedRounds: {
    type: Array,
    default: () => []
  }
})

const selectedStage = ref('overall')

// Build stage options
const stageOptions = computed(() => {
  const options = [
    { value: 'overall', label: 'Overall Tally' },
    { value: 'final', label: 'Final Results' }
  ]
  
  return options
})

// Get stage label
const stageLabels = computed(() => ({
  overall: 'Overall Tally',
  final: 'Final Results'
}))

const resultsToShow = computed(() => {
  if (selectedStage.value === 'overall') {
    return props.overallTally || []
  } else if (selectedStage.value === 'final') {
    return props.finalTopN || []
  }
  return props.overallTally || []
})

const isPairPageant = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

const maleResults = computed(() => {
  if (!isPairPageant.value) return []
  return resultsToShow.value.filter(r => r.gender === 'male')
})

const femaleResults = computed(() => {
  if (!isPairPageant.value) return []
  return resultsToShow.value.filter(r => r.gender === 'female')
})

const reportTitle = computed(() => stageLabels.value[selectedStage.value])
</script>

<style scoped>
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
