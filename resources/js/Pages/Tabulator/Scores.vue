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

      <!-- Detailed Scores Table -->
      <div v-if="pageant && rounds && rounds.length > 0 && currentRound">
        <DetailedScoreTable 
          :title="`Judge Scores - ${getCurrentRoundLabel()}`"
          :contestants="contestants"
          :judges="judges"
          :scores="localScores"
          :criteria="criteria"
          :detailed-scores="detailedScores"
          :score-key="currentRound?.id.toString()"
          empty-title="No Scores Available"
          empty-message="Scores will appear here once judges start submitting their evaluations."
        />
      </div>

      <!-- Audit Logs Viewer -->
      <div v-if="pageant && currentRound">
        <AuditLogsViewer 
          :pageant-id="pageant.id"
          :round-id="currentRound.id"
        />
      </div>
    </div>

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { RefreshCw, Download, Target, ClipboardList, LayoutDashboard } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import DetailedScoreTable from '../../Components/tabulator/DetailedScoreTable.vue'
import AuditLogsViewer from '../../Components/tabulator/AuditLogsViewer.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'
import NotificationSystem from '../../Components/NotificationSystem.vue'
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
  is_pair?: boolean
  members_text?: string
}

interface Judge {
  id: number
  name: string
}

interface Pageant {
  id: number
  name: string
}

interface Criteria {
  id: number
  name: string
  description?: string
  weight: number
  min_score: number
  max_score: number
}

interface Props {
  pageant?: Pageant
  rounds: Round[]
  currentRound: Round | null
  contestants: Contestant[]
  judges: Judge[]
  scores: Record<string, number> | Map<string, number>
  criteria: Criteria[]
  detailedScores: Record<string, any>
}

const props = defineProps<Props>()

const localScores = ref(props.scores ? new Map(Object.entries(props.scores)) : new Map())
const criteria = ref(props.criteria || [])
const detailedScores = ref(props.detailedScores || {})
const notificationSystem = ref<any>(null)

// Watch for changes in props.scores and update localScores accordingly
watch(() => props.scores, (newScores) => {
  localScores.value = newScores ? new Map(Object.entries(newScores)) : new Map()
}, { immediate: true })

// Watch for changes in criteria
watch(() => props.criteria, (newCriteria) => {
  criteria.value = newCriteria || []
}, { immediate: true })

// Watch for changes in detailedScores
watch(() => props.detailedScores, (newDetailedScores) => {
  detailedScores.value = newDetailedScores || {}
}, { immediate: true })

onMounted(() => {
  if (props.pageant) {
    const channelName = `pageant.${props.pageant.id}`
    console.log('Subscribing to pageant channel:', channelName)

    // Ensure we do not attach multiple listeners when the component remounts (HMR / navigation)
    const channel = window.Echo.private(channelName)
    channel.stopListening('ScoreUpdated')

    // Track in-flight fetches to avoid parallel requests for the same key
    const inFlightAggregations = new Set<string>()

    // Notification cooldown per (contestant-judge-round)
    const NOTIFICATION_COOLDOWN_MS = 1500
    const notificationCooldowns = new Map<string, number>()

    channel.listen('ScoreUpdated', async (e: any) => {
        console.log('ScoreUpdated event received:', e)
        // When a score is updated, we need to recalculate the aggregated judge score
        // for this contestant-judge-round combination
        const { contestant_id, judge_id, round_id } = e;

        console.log('Current round ID:', currentRoundId.value, 'Event round ID:', round_id)
        if (round_id == currentRoundId.value) {
          const key = `${contestant_id}-${judge_id}-${round_id}`

          // Avoid duplicate work if a fetch for this key is already in progress
          if (inFlightAggregations.has(key)) {
            return
          }
          inFlightAggregations.add(key)

          // Fetch just the updated aggregated score for this specific judge-contestant-round
          try {
            const response = await fetch(route('tabulator.scores.aggregated', [props.pageant?.id, round_id]) + `?judge_id=${judge_id}&contestant_id=${contestant_id}`, {
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              }
            });

            if (response.ok) {
              const data = await response.json();
              if (data.aggregated_score !== undefined && data.aggregated_score !== null) {
                const oldScore = localScores.value.get(key)
                const newScore = Number(data.aggregated_score)
                const changed = oldScore === undefined || Math.abs(Number(oldScore) - newScore) > 1e-9

                // Update local cache
                localScores.value.set(key, newScore)
                console.log(`Score updated for contestant ${contestant_id} by judge ${judge_id}: ${newScore}`)

                // Only notify if the value actually changed and not within cooldown window
                const now = Date.now()
                const lastNotified = notificationCooldowns.get(key) ?? 0
                const withinCooldown = now - lastNotified < NOTIFICATION_COOLDOWN_MS

                if (changed && !withinCooldown && notificationSystem.value) {
                  notificationSystem.value.success(`Score updated in real-time: ${newScore}`, {
                    title: 'Live Score Update',
                    timeout: 3000
                  })
                  notificationCooldowns.set(key, now)
                }
              }
            } else {
              console.warn(`Failed to fetch aggregated score: HTTP ${response.status}`);
              // Fallback: refresh entire scores data
              refreshData();
            }
          } catch (error) {
            console.error('Network error while refreshing aggregated score:', error);
            // small delay before fallback to avoid rapid requests
            setTimeout(() => {
              refreshData();
            }, 1000);
          } finally {
            inFlightAggregations.delete(key)
          }
        }
      });
  }
});

onUnmounted(() => {
    if (props.pageant) {
        const channelName = `pageant.${props.pageant.id}`
        window.Echo.leave(channelName);
    }
});

const currentRoundId = ref(props.currentRound?.id || (props.rounds[0]?.id))

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name
  }))
})

// Watch for round changes and navigate
watch(currentRoundId, (newRoundId, oldRoundId) => {
  if (newRoundId && newRoundId !== oldRoundId && props.pageant) {
    const roundId = parseInt(newRoundId.toString())
    if (!isNaN(roundId)) {
      router.visit(route('tabulator.scores', { pageantId: props.pageant.id, roundId }))
    }
  }
})

const getCurrentRoundLabel = () => {
  const selectedRound = props.rounds.find(r => r.id.toString() === currentRoundId.value?.toString())
  return selectedRound ? selectedRound.name : 'Unknown Round'
}

const refreshData = () => {
  router.reload()
}
</script>