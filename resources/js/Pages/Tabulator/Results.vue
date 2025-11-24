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
              <div class="inline-flex items-center rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700 uppercase tracking-wide border border-teal-100 mb-2">
                <Trophy class="mr-1.5 h-3.5 w-3.5" />
                <span>Tabulator Results</span>
              </div>
              <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                {{ pageant ? `${pageant.name} – Results` : 'Results Overview' }}
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                Final rankings, stage breakdowns, and consolidated scores.
              </p>
            </div>
            
            <div v-if="pageant" class="flex flex-col sm:flex-row gap-3">
              <button
                @click="refreshData"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm group"
              >
                <RefreshCw class="mr-2 h-4 w-4 text-slate-400 group-hover:rotate-180 transition-transform duration-500" />
                <span>Refresh Data</span>
              </button>
              <Link
                :href="route('tabulator.minor-awards', pageant.id)"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm"
              >
                <Award class="mr-2 h-4 w-4 text-teal-500" />
                <span>Minor Awards</span>
              </Link>
              <Link
                :href="route('tabulator.print', pageant.id)"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition-all shadow-md hover:shadow-lg"
              >
                <Printer class="mr-2 h-4 w-4" />
                <span>Print Results</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-20 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 max-w-2xl mx-auto">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-slate-50 mb-6">
            <Trophy class="h-12 w-12 text-slate-400" />
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-4">No Pageant Selected</h3>
          <p class="text-slate-500 mb-8 text-lg">
            Once an organizer assigns you to a pageant, you will see consolidated rankings, stages, and results here.
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

      <div v-else class="space-y-8 animate-fade-in">
        <!-- Controls & Statistics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Controls Card -->
          <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative">
            <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 opacity-50 -z-10"></div>
            <div class="relative z-10">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                  <h3 class="text-lg font-bold text-slate-900">Results View</h3>
                  <p class="text-sm text-slate-500">Filter results by round or stage</p>
                </div>
                <div class="w-full sm:w-64">
                  <CustomSelect
                    v-model="activeRound"
                    :options="roundOptions"
                    placeholder="Select round"
                  />
                </div>
              </div>

              <div class="flex flex-wrap gap-2">
                <button
                  v-for="s in ['overall', 'semi-final', 'final', 'final-top3']"
                  :key="s"
                  @click="stage = s as typeof stage"
                  class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border"
                  :class="stage === s 
                    ? 'bg-teal-50 text-teal-700 border-teal-200 shadow-sm' 
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300'"
                >
                  {{ s === 'final-top3' ? 'Top 3' : s.charAt(0).toUpperCase() + s.slice(1).replace('-', ' ') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col justify-center gap-4">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-teal-100 text-teal-600 rounded-lg">
                  <BarChart3 class="w-4 h-4" />
                </div>
                <span class="text-sm font-medium text-slate-600">Highest Score</span>
              </div>
              <span class="text-lg font-bold text-slate-900">{{ highestScore }}</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-teal-100 text-teal-600 rounded-lg">
                  <Target class="w-4 h-4" />
                </div>
                <span class="text-sm font-medium text-slate-600">Average Score</span>
              </div>
              <span class="text-lg font-bold text-slate-900">{{ averageScore }}</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-teal-100 text-teal-600 rounded-lg">
                  <Users class="w-4 h-4" />
                </div>
                <span class="text-sm font-medium text-slate-600">Contestants</span>
              </div>
              <span class="text-lg font-bold text-slate-900">{{ contestants.length }}</span>
            </div>
          </div>
        </div>

        <!-- Results Display -->
        <div v-if="displayedContestants && displayedContestants.length > 0" class="space-y-8">
          <!-- For pair pageants, show separate rankings -->
          <template v-if="isPairPageant">
            <!-- Male Rankings -->
            <div v-if="maleContestants.length > 0" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
              <div class="px-6 py-5 border-b border-slate-100 bg-blue-50/30">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700">
                    ♂
                  </span>
                  {{ getResultsTitle() }} - Male
                </h2>
              </div>
              <div class="p-0">
                <ResultsRanking
                  :title="`${getResultsTitle()} - Male`"
                  :contestants="maleContestants"
                  :rounds="displayedRounds"
                />
              </div>
            </div>

            <!-- Female Rankings -->
            <div v-if="femaleContestants.length > 0" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
              <div class="px-6 py-5 border-b border-slate-100 bg-pink-50/30">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-pink-100 text-pink-700">
                    ♀
                  </span>
                  {{ getResultsTitle() }} - Female
                </h2>
              </div>
              <div class="p-0">
                <ResultsRanking
                  :title="`${getResultsTitle()} - Female`"
                  :contestants="femaleContestants"
                  :rounds="displayedRounds"
                />
              </div>
            </div>
          </template>

          <!-- Standard single ranking -->
          <div v-else class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
              <h2 class="text-lg font-bold text-slate-900">{{ getResultsTitle() }}</h2>
            </div>
            <div class="p-0">
              <ResultsRanking
                :title="getResultsTitle()"
                :contestants="displayedContestants"
                :rounds="displayedRounds"
              />
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-3xl shadow-sm border border-slate-100 p-12 text-center">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <Trophy class="h-8 w-8 text-slate-400" />
          </div>
          <h3 class="text-xl font-bold text-slate-900 mb-2">No Results Available</h3>
          <p class="text-slate-500">
            Results will appear here once all scoring is complete and calculated.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { RefreshCw, Printer, Trophy, BarChart3, Users, LayoutDashboard, Target, Award } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import ResultsRanking from '../../Components/tabulator/ResultsRanking.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Round {
  id: number
  name: string
  type?: string
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
  finalScore?: number
  rank?: number
  gender?: string
}

interface Pageant {
  id: number
  name: string
  contestant_type?: string
}

interface Props {
  pageant?: Pageant
  contestants: Contestant[]
  contestantsSemiFinal: Contestant[]
  contestantsFinal: Contestant[]
  rounds: Round[]
}

const props = withDefaults(defineProps<Props>(), {
  contestants: () => [],
  contestantsSemiFinal: () => [],
  contestantsFinal: () => [],
  rounds: () => []
})

const activeRound = ref('overall')
const stage = ref<'overall' | 'semi-final' | 'final' | 'final-top3'>('overall')

const roundOptions = computed(() => {
  const options = [
    { value: 'overall', label: 'Overall Results' }
  ]
  
  if (props.rounds) {
    props.rounds.forEach(round => {
      options.push({
        value: round.id.toString(),
        label: round.name
      })
    })
  }
  
  return options
})

const displayedContestants = computed(() => {
  let baseList: Contestant[] = []
  switch (stage.value) {
    case 'semi-final':
      baseList = props.contestantsSemiFinal || []
      break
    case 'final':
      baseList = props.contestantsFinal || []
      break
    case 'final-top3':
      baseList = (props.contestantsFinal || []).slice(0, 3)
      break
    default:
      baseList = props.contestants || []
  }

  // Debug logging
  if (stage.value !== 'overall') {
    console.log(`Stage: ${stage.value}, Contestants:`, baseList.length, baseList.map(c => ({
      id: c.id,
      name: c.name,
      totalScore: c.totalScore,
      finalScore: c.finalScore
    })))
  }

  return baseList
})

const isPairPageant = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

const maleContestants = computed(() => {
  if (!isPairPageant.value) return []
  return displayedContestants.value.filter(c => c.gender === 'male')
})

const femaleContestants = computed(() => {
  if (!isPairPageant.value) return []
  return displayedContestants.value.filter(c => c.gender === 'female')
})

const displayedRounds = computed(() => {
  const rounds = props.rounds || []
  switch (stage.value) {
    case 'semi-final':
      return rounds.filter(r => r.type === 'semi-final')
    case 'final':
    case 'final-top3':
      return rounds.filter(r => r.type === 'final')
    default:
      return rounds
  }
})

const highestScore = computed(() => {
  if (!displayedContestants.value || displayedContestants.value.length === 0) return '-'
  
  const scores = displayedContestants.value
    .map(c => {
      const score = c.totalScore ?? c.finalScore ?? 0
      return typeof score === 'number' && !isNaN(score) ? score : null
    })
    .filter(score => score !== null)
  
  if (scores.length === 0) return '-'
  const highest = Math.max(...scores)
  return parseFloat(highest.toFixed(2))
})

const averageScore = computed(() => {
  if (!displayedContestants.value || displayedContestants.value.length === 0) return '-'
  
  const scores = displayedContestants.value
    .map(c => {
      const score = c.totalScore ?? c.finalScore ?? 0
      return typeof score === 'number' && !isNaN(score) ? score : null
    })
    .filter(score => score !== null)
  
  if (scores.length === 0) return '-'
  const sum = scores.reduce((acc, score) => acc + score, 0)
  return parseFloat((sum / scores.length).toFixed(2))
})

const getResultsTitle = () => {
  if (stage.value === 'semi-final') return 'Semi-Final Rankings'
  if (stage.value === 'final') return 'Final Rankings'
  if (stage.value === 'final-top3') return 'Top 3 (Final)'
  return 'Overall Rankings'
}

const refreshData = () => {
  router.reload()
}

// WebSocket handling for real-time updates
let echoChannel: any = null
let refreshTimeout: ReturnType<typeof setTimeout> | null = null
const REFRESH_DEBOUNCE_MS = 500

const handleScoreUpdate = (e: any) => {
  console.log('🔔 ScoreUpdated event received on Results page:', e)
  
  // Clear existing timeout
  if (refreshTimeout) {
    clearTimeout(refreshTimeout)
  }
  
  // Debounce full page refresh to avoid multiple rapid reloads
  refreshTimeout = setTimeout(() => {
    console.log('🔄 Refreshing results data after score update')
    refreshData()
    refreshTimeout = null
  }, REFRESH_DEBOUNCE_MS)
}

const handleRankingsUpdate = (e: any) => {
  console.log('🏆 RankingsUpdated event received:', e)
  
  // Clear existing timeout
  if (refreshTimeout) {
    clearTimeout(refreshTimeout)
  }
  
  // Refresh immediately for ranking updates
  refreshTimeout = setTimeout(() => {
    console.log('🔄 Refreshing results due to rankings update')
    refreshData()
    refreshTimeout = null
  }, 500)
}

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

  // Subscribe to the pageant channel for real-time updates
  echoChannel = window.Echo.private(channelName)
  echoChannel
    .listen('ScoreUpdated', handleScoreUpdate)
    .listen('RankingsUpdated', handleRankingsUpdate)
  
  console.log('✅ Successfully subscribed to results updates')
})

onUnmounted(() => {
  // Clear any pending refresh
  if (refreshTimeout) {
    clearTimeout(refreshTimeout)
    refreshTimeout = null
  }
  
  // Stop listening to events
  if (echoChannel && props.pageant) {
    const channelName = `pageant.${props.pageant.id}`
    console.log('🔌 Unsubscribing from channel:', channelName)
    echoChannel
      .stopListening('ScoreUpdated', handleScoreUpdate)
      .stopListening('RankingsUpdated', handleRankingsUpdate)
    echoChannel = null
  }
})
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