<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          {{ pageant ? `${pageant.name} - Score Tracking` : 'Score Tracking' }}
        </h1>
        <p class="text-gray-600 mt-2">Monitor judge submissions and scoring progress</p>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-16">
        <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 mb-6">
          <ClipboardList class="h-12 w-12 text-blue-500" />
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No Pageant Selected</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          You haven't been assigned to any pageants yet, or you need to select a pageant to view scores.
        </p>
        <Link 
          :href="route('tabulator.dashboard')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
        >
          <LayoutDashboard class="w-4 h-4 mr-2" />
          Go to Dashboard
        </Link>
      </div>

      <!-- Round Selection -->
      <div v-if="pageant" class="mb-6 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <h2 class="text-lg font-semibold text-gray-900">Current Round:</h2>
          <div class="w-48">
            <CustomSelect 
              v-model="currentRoundId"
              :options="roundOptions"
              placeholder="Select Round"
              @change="handleRoundChange"
            />
          </div>
        </div>
        <button 
          @click="refreshData"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
        >
          <RefreshCw class="w-4 h-4 mr-2" />
          Refresh Data
        </button>
      </div>

      <!-- No Rounds Message -->
      <div v-if="pageant && (!rounds || rounds.length === 0)" class="text-center py-12">
        <div class="text-gray-500">
          <Target class="mx-auto h-12 w-12 text-gray-400 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Rounds Available</h3>
          <p class="text-gray-500">
            No competition rounds have been set up for this pageant yet.
          </p>
        </div>
      </div>

      <!-- Scores Table -->
      <div v-if="pageant && rounds && rounds.length > 0">
        <ScoreTable 
          :title="`Judge Scores - ${getCurrentRoundLabel()}`"
          :contestants="contestants"
          :judges="judges"
          :scores="scores"
          :score-key="currentRound?.id.toString()"
          empty-title="No Scores Available"
          empty-message="Scores will appear here once judges start submitting their evaluations."
        >
          <template #actions>
            <button
              @click="exportScores"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out"
            >
              <Download class="w-4 h-4 mr-2" />
              Export
            </button>
          </template>
        </ScoreTable>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { RefreshCw, Download, Target, ClipboardList, LayoutDashboard } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import ScoreTable from '../../Components/tabulator/ScoreTable.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Round {
  id: number
  name: string
  type: string
  weight: number
}

interface Contestant {
  id: number
  name: string
  number: number
  image: string
}

interface Judge {
  id: number
  name: string
}

interface Pageant {
  id: number
  name: string
}

interface Props {
  pageant?: Pageant
  rounds: Round[]
  currentRound: Round | null
  contestants: Contestant[]
  judges: Judge[]
  scores: Record<string, number> | Map<string, number>
}

const props = defineProps<Props>()

const currentRoundId = ref(props.currentRound?.id || (props.rounds[0]?.id))

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name
  }))
})

const getCurrentRoundLabel = () => {
  const selectedRound = props.rounds.find(r => r.id.toString() === currentRoundId.value?.toString())
  return selectedRound ? selectedRound.name : 'Unknown Round'
}

const handleRoundChange = (value: string) => {
  const roundId = parseInt(value)
  router.visit(route('tabulator.scores', { pageantId: props.pageant?.id, roundId }))
}

const refreshData = () => {
  router.reload()
}

const exportScores = () => {
  // TODO: Implement score export functionality
  console.log('Export scores for round:', currentRoundId.value)
}
</script>