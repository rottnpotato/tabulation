
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
              <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                Scores & Tabulation
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                <Activity class="w-5 h-5 text-teal-500" />
                Real-time Score Monitoring
              </p>
            </div>
            
            <div v-if="pageant" class="flex items-center bg-white/60 backdrop-blur-md rounded-2xl p-4 border border-teal-100 shadow-sm">
              <div class="text-teal-600 mr-3">
                <div class="p-2 bg-teal-50 rounded-lg">
                  <Crown class="w-5 h-5" />
                </div>
              </div>
              <div>
                <div class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-0.5">Active Pageant</div>
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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative z-30">
          <div class="absolute inset-0 rounded-2xl pointer-events-none">
            <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 opacity-50"></div>
          </div>
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
              <!-- Ranking Method Indicator -->
              <div 
                v-if="pageant"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium"
                :class="getRankingMethodClass()"
                :title="getRankingMethodTooltip()"
              >
                <BarChart3 class="w-3.5 h-3.5" />
                <span>{{ getRankingMethodLabel() }}</span>
              </div>
            </div>
            
            <div class="flex items-center gap-2">
               <!-- <button
                @click="exportScores"
                class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 shadow-sm"
              >
                <Download class="w-4 h-4 mr-2 text-slate-500" />
                Export CSV
              </button> -->
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
        <div class="mb-3 flex items-center gap-2">
          <h3 class="text-lg font-semibold text-slate-900">Judge Scores - {{ getCurrentRoundLabel() }}</h3>
          <span v-if="currentRound.type" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="[
                  currentRound.type === 'final' ? 'bg-purple-100 text-purple-800' :
                  currentRound.type === 'semi-final' ? 'bg-blue-100 text-blue-800' :
                  'bg-amber-100 text-amber-800'
              ]">
              {{ getRoundTypeDisplay(currentRound) }}
          </span>
          <span v-if="isPairsType" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
            Pairs Competition
          </span>
        </div>
        
        <!-- For pairs type: Show separate tables for male and female contestants -->
        <div v-if="isPairsType" class="space-y-8">
          <!-- Male Contestants Table -->
          <div v-if="maleContestants.length > 0">
            <div class="mb-3 flex items-center gap-2">
              <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 rounded-lg border border-blue-200">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 12a4 4 0 100-8 4 4 0 000 8z"/>
                  <path fill-rule="evenodd" d="M10 14c-4.418 0-8 1.79-8 4v1a1 1 0 001 1h14a1 1 0 001-1v-1c0-2.21-3.582-4-8-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-semibold text-blue-700">Male Contestants</span>
                <span class="text-xs text-blue-500">({{ maleContestants.length }})</span>
              </div>
            </div>
            <DetailedScoreTable 
              :title="`Male - ${getCurrentRoundLabel()}`"
              :contestants="maleContestants"
              :judges="judges"
              :scores="localScores"
              :criteria="criteria"
              :detailed-scores="detailedScores"
              :score-key="currentRound?.id.toString()"
              empty-title="No Male Contestants"
              empty-message="No male contestants have been added to this round."
            />
          </div>
          
          <!-- Female Contestants Table -->
          <div v-if="femaleContestants.length > 0">
            <div class="mb-3 flex items-center gap-2">
              <div class="flex items-center gap-2 px-3 py-1.5 bg-pink-50 rounded-lg border border-pink-200">
                <svg class="w-4 h-4 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 12a4 4 0 100-8 4 4 0 000 8z"/>
                  <path fill-rule="evenodd" d="M10 14c-4.418 0-8 1.79-8 4v1a1 1 0 001 1h14a1 1 0 001-1v-1c0-2.21-3.582-4-8-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-semibold text-pink-700">Female Contestants</span>
                <span class="text-xs text-pink-500">({{ femaleContestants.length }})</span>
              </div>
            </div>
            <DetailedScoreTable 
              :title="`Female - ${getCurrentRoundLabel()}`"
              :contestants="femaleContestants"
              :judges="judges"
              :scores="localScores"
              :criteria="criteria"
              :detailed-scores="detailedScores"
              :score-key="currentRound?.id.toString()"
              empty-title="No Female Contestants"
              empty-message="No female contestants have been added to this round."
            />
          </div>
        </div>
        
        <!-- For solo/both types: Show single table with all contestants -->
        <div v-else>
          <DetailedScoreTable 
            :title="`${getCurrentRoundLabel()}`"
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
      </div>

      <!-- Audit Logs Viewer -->
      <div v-if="pageant && currentRound">
        <AuditLogsViewer 
          :key="currentRound.id"
          :pageant-id="pageant.id"
          :round-id="currentRound.id"
        />
      </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { RefreshCw, Download, Target, ClipboardList, LayoutDashboard, FileText, Activity, Crown, BarChart3 } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import DetailedScoreTable from '../../Components/tabulator/DetailedScoreTable.vue'
import AuditLogsViewer from '../../Components/tabulator/AuditLogsViewer.vue'
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

interface ContestantMember {
  id: number
  name: string
  gender?: string
}

interface Contestant {
  id: number
  name: string
  number: number
  image: string
  is_pair?: boolean
  gender?: string
  members_text?: string
  members?: ContestantMember[]
}

interface Judge {
  id: number
  name: string
}

interface Pageant {
  id: number
  name: string
  contestant_type?: 'solo' | 'pairs' | 'both'
  ranking_method?: 'score_average' | 'rank_sum' | 'ordinal'
  tie_handling?: 'sequential' | 'average' | 'minimum'
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
// Ensure currentRoundId is a string to match the option values in CustomSelect
const currentRoundId = ref((props.currentRound?.id ?? props.rounds[0]?.id)?.toString() || '')

// Check if using rank-sum method
const isRankSumMethod = computed(() => props.pageant?.ranking_method === 'rank_sum')

// Check if using ordinal method
const isOrdinalMethod = computed(() => props.pageant?.ranking_method === 'ordinal')

// Get ranking method display info
const getRankingMethodLabel = () => {
  const method = props.pageant?.ranking_method || 'score_average'
  switch (method) {
    case 'rank_sum': return 'Rank Sum'
    case 'ordinal': return 'Ordinal'
    case 'score_average':
    default: return 'Avg Score'
  }
}

const getRankingMethodClass = () => {
  const method = props.pageant?.ranking_method || 'score_average'
  switch (method) {
    case 'rank_sum': return 'bg-purple-100 text-purple-700 border border-purple-200'
    case 'ordinal': return 'bg-amber-100 text-amber-700 border border-amber-200'
    case 'score_average':
    default: return 'bg-blue-100 text-blue-700 border border-blue-200'
  }
}

const getRankingMethodTooltip = () => {
  const method = props.pageant?.ranking_method || 'score_average'
  switch (method) {
    case 'rank_sum': return 'Scores are converted to ranks per judge - lowest sum wins'
    case 'ordinal': return 'Final Ballot system - Majority of #1 votes wins, else lowest sum of ranks'
    case 'score_average':
    default: return 'Scores are averaged - highest average wins'
  }
}

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name
  }))
})

// Check if we should show contestants separated by gender (only for pairs type)
const isPairsType = computed(() => props.pageant?.contestant_type === 'pairs')

// Separate contestants by gender for pairs view
const maleContestants = computed(() => {
  if (!isPairsType.value) return []
  return props.contestants.filter(c => c.gender?.toLowerCase() === 'male')
})

const femaleContestants = computed(() => {
  if (!isPairsType.value) return []
  return props.contestants.filter(c => c.gender?.toLowerCase() === 'female')
})

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

// Watch for round changes and navigate
watch(currentRoundId, (newRoundId, oldRoundId) => {
  if (newRoundId && newRoundId !== oldRoundId && props.pageant) {
    const roundId = parseInt(newRoundId.toString())
    if (!isNaN(roundId)) {
      router.visit(route('tabulator.scores', { pageantId: props.pageant.id, roundId }))
    }
  }
})

// Track in-flight fetches to avoid parallel requests for the same key
const inFlightAggregations = new Set<string>()
const pendingUpdates = new Map<string, ReturnType<typeof setTimeout>>()

// Debounce updates to batch multiple score changes
const DEBOUNCE_MS = 100

const handleScoreUpdate = async (e: any) => {
  console.log('🔔 ScoreUpdated event received:', e)
  
  const { contestant_id, judge_id, round_id, score, criteria_name } = e

  // Only process if this event is for the currently displayed round
  if (round_id != currentRoundId.value) {
    console.log('⏭️ Ignoring score update for different round:', round_id, 'vs current:', currentRoundId.value)
    return
  }

  const key = `${contestant_id}-${judge_id}-${round_id}`

  // Clear any existing pending update for this key
  const existingTimeout = pendingUpdates.get(key)
  if (existingTimeout) {
    clearTimeout(existingTimeout)
  }

  // Debounce the update to batch rapid score changes
  const timeoutId = setTimeout(async () => {
    pendingUpdates.delete(key)
    
    // Avoid duplicate work if a fetch for this key is already in progress
    if (inFlightAggregations.has(key)) {
      console.log('⚠️ Update already in progress for:', key)
      return
    }
    
    inFlightAggregations.add(key)

    // Fetch the updated aggregated score for this specific judge-contestant-round
    try {
      const response = await fetch(
        route('tabulator.scores.aggregated', [props.pageant?.id, round_id]) + 
        `?judge_id=${judge_id}&contestant_id=${contestant_id}`,
        {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        }
      )

      if (response.ok) {
        const data = await response.json()
        if (data.aggregated_score !== undefined && data.aggregated_score !== null) {
          const oldScore = localScores.value.get(key)
          const newScore = Number(data.aggregated_score)
          const changed = oldScore === undefined || Math.abs(Number(oldScore) - newScore) > 1e-9

          // Update local cache
          localScores.value.set(key, newScore)
          
          if (changed) {
            console.log(`✅ Score updated for contestant ${contestant_id} by judge ${judge_id}: ${newScore}`)
          } else {
            console.log(`ℹ️ Score unchanged for contestant ${contestant_id} by judge ${judge_id}: ${newScore}`)
          }
        }
      } else {
        console.warn(`⚠️ Failed to fetch aggregated score: HTTP ${response.status}`)
        // On error, do a full refresh after a delay
        setTimeout(() => refreshData(), 2000)
      }
    } catch (error) {
      console.error('❌ Network error while fetching aggregated score:', error)
      // Retry once after a delay
      setTimeout(() => refreshData(), 2000)
    } finally {
      inFlightAggregations.delete(key)
    }
  }, DEBOUNCE_MS)

  pendingUpdates.set(key, timeoutId)
}

let echoChannel: any = null

onMounted(() => {
  if (!props.pageant) {
    console.log('⚠️ No pageant selected, skipping WebSocket subscription')
    return
  }

  if (typeof window === 'undefined' || !window.Echo) {
    console.error('❌ Laravel Echo not available')
    return
  }

  const channelName = `pageant.${props.pageant.id}`
  console.log('🔌 Subscribing to channel:', channelName)

  // Subscribe to the pageant channel
  echoChannel = window.Echo.private(channelName)
  echoChannel.listen('ScoreUpdated', handleScoreUpdate)
  
  console.log('✅ Successfully subscribed to score updates')
})

onUnmounted(() => {
  // Clear any pending debounced updates
  pendingUpdates.forEach(timeout => clearTimeout(timeout))
  pendingUpdates.clear()
  
  // Clean up in-flight requests
  inFlightAggregations.clear()
  
  // Stop listening to this specific handler
  if (echoChannel && props.pageant) {
    const channelName = `pageant.${props.pageant.id}`
    console.log('🔌 Unsubscribing from channel:', channelName)
    echoChannel.stopListening('ScoreUpdated', handleScoreUpdate)
    echoChannel = null
  }
})

const getCurrentRoundLabel = () => {
  const selectedRound = props.rounds.find(r => r.id.toString() === currentRoundId.value?.toString())
  return selectedRound ? selectedRound.name : 'Unknown Round'
}

const getRoundTypeDisplay = (round: any) => {
  if (!round || !round.type) return ''
  if (round.top_n_proceed) {
    return `${round.type} (Top ${round.top_n_proceed})`
  }
  return round.type
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