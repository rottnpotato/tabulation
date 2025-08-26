<template>
  <div class="space-y-4 sm:space-y-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header Section -->
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-md overflow-hidden">
        <div class="p-4 sm:p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="text-white">
              <h1 class="text-2xl sm:text-3xl font-bold">
                {{ pageant ? `${pageant.name} - Results` : 'Results' }}
              </h1>
              <p class="mt-1 text-sm sm:text-base opacity-90">Final rankings and contestant scores</p>
            </div>
            <div v-if="pageant" class="flex flex-wrap gap-2">
              <button 
                @click="refreshData"
                class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white hover:bg-white/30 flex items-center shadow-sm transition-all"
              >
                <RefreshCw class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
                <span>Refresh</span>
              </button>
              <Link 
                :href="route('tabulator.print', pageant.id)"
                class="bg-white text-blue-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-blue-50 flex items-center shadow-sm transition-all"
              >
                <Printer class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-blue-600" />
                <span>Print Results</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-16">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-blue-200 mb-6">
            <Trophy class="h-12 w-12 text-blue-600" />
          </div>
          <h3 class="text-xl font-medium text-gray-900 mb-2">No Pageant Assignment</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            You haven't been assigned to any pageants yet. Once an organizer assigns you to a pageant, you'll be able to consolidate and view the results here.
          </p>
          <Link 
            :href="route('tabulator.dashboard')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
          >
            <LayoutDashboard class="w-4 h-4 mr-2" />
            Go to Dashboard
          </Link>
        </div>
      </div>

      <!-- Controls -->
      <div v-if="pageant" class="bg-white rounded-xl shadow-md border border-gray-100 p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
          <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
            <h2 class="text-lg font-semibold text-gray-900">Results View:</h2>
            <div class="w-full sm:w-48">
              <CustomSelect 
                v-model="activeRound"
                :options="roundOptions"
                placeholder="Select Round"
              />
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <button 
              @click="refreshData"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out"
            >
              <RefreshCw class="w-4 h-4 mr-2" />
              Refresh
            </button>
            <Link 
              :href="route('tabulator.print', pageant.id)"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
            >
              <Printer class="w-4 h-4 mr-2" />
              Print Results
            </Link>
          </div>
        </div>
      </div>

      <!-- Results Display -->
      <div v-if="pageant && displayedContestants && displayedContestants.length > 0">
        <ResultsRanking 
          :title="getResultsTitle()"
          :contestants="displayedContestants"
          :rounds="rounds"
        />

        <!-- Summary Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
          <TabulatorCard 
            title="Highest Score"
            :value="highestScore"
            description="Top contestant score"
            :icon="Trophy"
            color="green"
          />

          <TabulatorCard 
            title="Average Score"
            :value="averageScore"
            description="Overall average"
            :icon="BarChart3"
            color="blue"
          />

          <TabulatorCard 
            title="Total Contestants"
            :value="contestants.length"
            description="Participants ranked"
            :icon="Users"
            color="purple"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="pageant && (!displayedContestants || displayedContestants.length === 0)" class="text-center py-12">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12">
          <Trophy class="mx-auto h-12 w-12 text-gray-400 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Results Available</h3>
          <p class="text-gray-500">
            Results will appear here once all scoring is complete and calculated.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { RefreshCw, Printer, Trophy, BarChart3, Users, LayoutDashboard } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import ResultsRanking from '../../Components/tabulator/ResultsRanking.vue'
import TabulatorCard from '../../Components/tabulator/TabulatorCard.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Round {
  id: number
  name: string
  weight: number
}

interface Contestant {
  id: number
  number: number
  name: string
  region?: string
  image: string
  scores: Record<string, number>
  totalScore: number
}

interface Pageant {
  id: number
  name: string
}

interface Props {
  pageant?: Pageant
  contestants: Contestant[]
  rounds: Round[]
}

const props = defineProps<Props>()

const activeRound = ref('overall')

const roundOptions = computed(() => {
  const options = [
    { value: 'overall', label: 'Overall Results' }
  ]
  
  props.rounds.forEach(round => {
    options.push({
      value: round.id.toString(),
      label: round.name
    })
  })
  
  return options
})

const displayedContestants = computed(() => {
  if (activeRound.value === 'overall') {
    return props.contestants
  }
  
  // For individual rounds, we would need to calculate scores for that specific round
  // For now, return all contestants - this would be enhanced with proper round-specific scoring
  return props.contestants
})

const highestScore = computed(() => {
  if (displayedContestants.value.length === 0) return 0
  const highest = Math.max(...displayedContestants.value.map(c => c.totalScore))
  return parseFloat(highest.toFixed(2))
})

const averageScore = computed(() => {
  if (displayedContestants.value.length === 0) return 0
  const sum = displayedContestants.value.reduce((acc, contestant) => acc + contestant.totalScore, 0)
  return parseFloat((sum / displayedContestants.value.length).toFixed(2))
})

const getResultsTitle = () => {
  if (activeRound.value === 'overall') {
    return 'Final Rankings'
  }
  
  const round = props.rounds.find(r => r.id.toString() === activeRound.value)
  return round ? `${round.name} Results` : 'Round Results'
}

const refreshData = () => {
  router.reload()
}
</script>