<template>
  <div class="space-y-4 sm:space-y-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header Section -->
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-md overflow-hidden">
        <div class="p-4 sm:p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="text-white">
              <h1 class="text-2xl sm:text-3xl font-bold">
                {{ pageant ? `${pageant.name} - Score Tracking` : 'Score Tracking' }}
              </h1>
              <p class="mt-1 text-sm sm:text-base opacity-90">Monitor judge submissions and scoring progress</p>
            </div>
            <div v-if="pageant" class="flex flex-wrap gap-2">
              <button 
                @click="refreshData"
                class="bg-white text-blue-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-blue-50 flex items-center shadow-sm transition-all"
              >
                <RefreshCw class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-blue-600" />
                <span>Refresh Data</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-16">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-blue-200 mb-6">
            <ClipboardList class="h-12 w-12 text-blue-600" />
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
      </div>

      <!-- Round Selection -->
      <div v-if="pageant" class="bg-white rounded-xl shadow-md border border-gray-100 p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
          <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
            <h2 class="text-lg font-semibold text-gray-900">Current Round:</h2>
            <div class="w-full sm:w-48">
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
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out"
          >
            <RefreshCw class="w-4 h-4 mr-2" />
            Refresh Data
          </button>
        </div>
      </div>

      <!-- No Rounds Message -->
      <div v-if="pageant && (!rounds || rounds.length === 0)" class="text-center py-12">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12">
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
          :scores="localScores"
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
import { onMounted, onUnmounted } from 'vue'

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

const localScores = ref(props.scores ? new Map(Object.entries(props.scores)) : new Map())

onMounted(() => {
  if (props.pageant) {
    window.Echo.private(`pageant.${props.pageant.id}`)
      .listen('ScoreUpdated', (e: any) => {
        const { score, contestant_id, criteria_id } = e;
        const key = `${contestant_id}-${criteria_id}`;
        localScores.value.set(key, score);
      });
  }
});

onUnmounted(() => {
    if (props.pageant) {
        window.Echo.leave(`pageant.${props.pageant.id}`);
    }
});

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