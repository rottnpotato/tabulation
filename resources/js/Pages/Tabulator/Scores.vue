
<template>
  <div class="min-h-screen bg-slate-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-indigo-100">
        <!-- Abstract Background Pattern -->
        <div class="absolute inset-0">
          <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-blue-50/50 to-white opacity-90"></div>
          <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
          <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div class="relative z-10 p-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
              <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                Scores & Tabulation
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                <Activity class="w-5 h-5 text-indigo-500" />
                Real-time Score Monitoring
              </p>
            </div>
            
            <div v-if="pageant" class="flex items-center bg-white/60 backdrop-blur-md rounded-2xl p-4 border border-indigo-100 shadow-sm">
              <div class="text-indigo-600 mr-3">
                <div class="p-2 bg-indigo-50 rounded-lg">
                  <Crown class="w-5 h-5" />
                </div>
              </div>
              <div>
                <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Active Pageant</div>
                <div class="text-lg font-bold text-slate-900 leading-none">{{ pageant.name }}</div>
              </div>
            </div>
            
            <div v-else class="flex flex-wrap gap-3">
              <button 
                @click="refreshData"
                class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center gap-2 shadow-sm group"
              >
                <RefreshCw class="w-4 h-4 text-slate-500 group-hover:rotate-180 transition-transform duration-500" />
                <span>Refresh Data</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-20 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 max-w-2xl mx-auto">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-slate-50 mb-6">
            <ClipboardList class="h-12 w-12 text-slate-400" />
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-4">No Pageant Selected</h3>
          <p class="text-slate-500 mb-8 text-lg">
            Please select a pageant from the dashboard to view scores.
          </p>
          <Link 
            :href="route('tabulator.dashboard')"
            class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <LayoutDashboard class="w-5 h-5 mr-2" />
            Return to Dashboard
          </Link>
        </div>
      </div>

      <div v-else class="space-y-6 animate-fade-in">
        <!-- Toolbar -->
        <!-- Toolbar -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full -mr-8 -mt-8 opacity-50"></div>
          <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1">
              <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-200 w-fit">
                <Target class="w-4 h-4 text-slate-500" />
                <span class="text-sm font-medium text-slate-700">Current Round</span>
              </div>
              <div class="w-full sm:w-72">
                <CustomSelect
                  v-model="currentRoundId"
                  :options="roundOptions"
                  placeholder="Select Round"
                />
              </div>
            </div>
            
            <div class="flex items-center gap-2">
               <button
                @click="exportScores"
                class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 shadow-sm"
              >
                <Download class="w-4 h-4 mr-2 text-slate-500" />
                Export CSV
              </button>
            </div>
          </div>
        </div>

        <!-- No Rounds Message -->
        <div v-if="!rounds || rounds.length === 0" class="text-center py-20">
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-12 max-w-xl mx-auto">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Target class="h-8 w-8 text-slate-400" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No Rounds Found</h3>
            <p class="text-slate-500">
              This pageant doesn't have any scoring rounds configured yet.
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
    </div>

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { RefreshCw, Download, Target, ClipboardList, LayoutDashboard, FileText, Activity, Crown } from 'lucide-vue-next'
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

const exportScores = () => {
  // TODO: Implement CSV export
  console.log('Export scores functionality to be implemented')
}

const refreshData = () => {
  router.reload()
}
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