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
                  <p class="text-sm text-slate-500">Select a round or view overall results</p>
                </div>
                <div class="w-full sm:w-64">
                  <CustomSelect
                    v-model="activeRound"
                    :options="roundOptions"
                    placeholder="Select round"
                  />
                </div>
              </div>

              <!-- Ranking Method Indicator -->
              <div v-if="pageant" class="flex items-center gap-3 mb-4">
                <div 
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium"
                  :class="isRankSumMethod ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200'"
                >
                  <BarChart3 class="w-4 h-4" />
                  <span>{{ isRankSumMethod ? 'Rank Sum Method' : 'Score Average Method' }}</span>
                </div>
                <div 
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium bg-slate-100 text-slate-600 border border-slate-200"
                  :title="`Tie handling: ${pageant.tie_handling || 'average'}`"
                >
                  <span>{{ getTieHandlingLabel() }}</span>
                </div>
              </div>

              <div v-if="currentRoundInfo" class="flex items-center gap-2 p-3 bg-teal-50 border border-teal-100 rounded-xl">
                <div class="text-sm text-teal-700">
                  <span class="font-semibold">{{ currentRoundInfo.name }}</span>
                  <span v-if="currentRoundInfo.top_n_proceed" class="ml-2">
                    • Top {{ currentRoundInfo.top_n_proceed }} advance
                  </span>
                </div>
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
                  :is-updating="isUpdating"
                  :number-of-winners="pageant?.number_of_winners || 3"
                  :show-winners="shouldShowWinners"
                  :ranking-method="pageant?.ranking_method || 'score_average'"
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
                  :is-updating="isUpdating"
                  :number-of-winners="pageant?.number_of_winners || 3"
                  :show-winners="shouldShowWinners"
                  :ranking-method="pageant?.ranking_method || 'score_average'"
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
                :is-updating="isUpdating"
                :number-of-winners="pageant?.number_of_winners || 3"
                :show-winners="shouldShowWinners"
                :ranking-method="pageant?.ranking_method || 'score_average'"
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
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
  top_n_proceed?: number
  display_order?: number
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
  totalRankSum?: number
  judgeRanks?: Record<string, { scores: number[], ranks: number[], details: Array<{ judge_id: number, judge_name: string, score: number, rank: number }> }>
  rank?: number
  gender?: string
}

interface RoundResult {
  contestants: Contestant[]
  top_n_proceed?: number
}

interface Pageant {
  id: number
  name: string
  contestant_type?: string
  number_of_winners?: number
  ranking_method?: 'score_average' | 'rank_sum'
  tie_handling?: 'sequential' | 'average' | 'minimum'
}

interface Props {
  pageant?: Pageant
  contestants: Contestant[]
  rounds: Round[]
  roundResults: Record<string, RoundResult>
}

const props = withDefaults(defineProps<Props>(), {
  contestants: () => [],
  rounds: () => [],
  roundResults: () => ({})
})

// Track previous rankings for animation
const previousRankings = ref<Map<number, number>>(new Map())
const isUpdating = ref(false)

const activeRound = ref('overall')
const selectedRoundId = ref<number | null>(null)

const isRankSumMethod = computed(() => {
  return props.pageant?.ranking_method === 'rank_sum'
})

const getTieHandlingLabel = () => {
  const method = props.pageant?.tie_handling || 'average'
  switch (method) {
    case 'average': return 'Avg Rank Ties'
    case 'sequential': return 'Sequential Ties'
    case 'minimum': return 'Min Rank Ties'
    default: return 'Standard Ties'
  }
}

const roundOptions = computed(() => {
  const options = [
    { value: 'overall', label: 'Overall Results' }
  ]
  
  if (props.rounds) {
    props.rounds.forEach((round, index) => {
      const label = round.top_n_proceed 
        ? `${round.name} (Top ${round.top_n_proceed})` 
        : round.name
      options.push({
        value: round.id.toString(),
        label: label
      })
    })
  }
  
  return options
})

const displayedContestants = computed(() => {
  let baseList: Contestant[] = []
  let topNProceed: number | null = null
  
  if (activeRound.value === 'overall') {
    baseList = props.contestants || []
    // For overall, use number of winners as the cutoff
    topNProceed = props.pageant?.number_of_winners || null
  } else {
    // Get contestants for the selected round
    const roundKey = `round_${activeRound.value}`
    const roundResult = props.roundResults[roundKey]
    if (roundResult && roundResult.contestants) {
      baseList = roundResult.contestants
      topNProceed = roundResult.top_n_proceed || null
    }
  }

  // Deduplicate by contestant ID (keep first occurrence)
  const seenIds = new Set<number>()
  const deduplicated = baseList.filter(contestant => {
    if (seenIds.has(contestant.id)) {
      return false
    }
    seenIds.add(contestant.id)
    return true
  })

  // Sort by total score to determine current rankings
  const sorted = [...deduplicated].sort((a, b) => {
    const scoreA = a.totalScore ?? a.finalScore ?? 0
    const scoreB = b.totalScore ?? b.finalScore ?? 0
    return scoreB - scoreA
  })

  // Track ranking changes and recompute qualified status based on current rank
  const newRankings = new Map<number, number>()
  const result = sorted.map((contestant, index) => {
    const currentRank = index + 1
    newRankings.set(contestant.id, currentRank)
    
    // Recompute qualified status based on current position
    const qualified = topNProceed === null || currentRank <= topNProceed
    
    return {
      ...contestant,
      rank: currentRank,
      qualified,
      qualification_cutoff: topNProceed
    }
  })

  // Store new rankings for next comparison
  previousRankings.value = newRankings

  return result
})

const isPairPageant = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

// Get the current qualification cutoff for the active round
const currentQualificationCutoff = computed(() => {
  if (activeRound.value === 'overall') {
    return props.pageant?.number_of_winners || null
  }
  const roundKey = `round_${activeRound.value}`
  const roundResult = props.roundResults[roundKey]
  return roundResult?.top_n_proceed || null
})

const maleContestants = computed(() => {
  if (!isPairPageant.value) return []
  
  // Filter and sort males by score
  const males = displayedContestants.value
    .filter(c => c.gender === 'male')
    .sort((a, b) => {
      const scoreA = a.totalScore ?? a.finalScore ?? 0
      const scoreB = b.totalScore ?? b.finalScore ?? 0
      return scoreB - scoreA
    })
  
  // Recompute rank and qualified status within male group
  const topN = currentQualificationCutoff.value
  return males.map((contestant, index) => {
    const genderRank = index + 1
    return {
      ...contestant,
      rank: genderRank,
      qualified: topN === null || genderRank <= topN,
      qualification_cutoff: topN
    }
  })
})

const femaleContestants = computed(() => {
  if (!isPairPageant.value) return []
  
  // Filter and sort females by score
  const females = displayedContestants.value
    .filter(c => c.gender === 'female')
    .sort((a, b) => {
      const scoreA = a.totalScore ?? a.finalScore ?? 0
      const scoreB = b.totalScore ?? b.finalScore ?? 0
      return scoreB - scoreA
    })
  
  // Recompute rank and qualified status within female group
  const topN = currentQualificationCutoff.value
  return females.map((contestant, index) => {
    const genderRank = index + 1
    return {
      ...contestant,
      rank: genderRank,
      qualified: topN === null || genderRank <= topN,
      qualification_cutoff: topN
    }
  })
})

const currentRoundInfo = computed(() => {
  if (activeRound.value === 'overall') return null
  const round = props.rounds.find(r => r.id.toString() === activeRound.value)
  return round || null
})

const displayedRounds = computed(() => {
  if (activeRound.value === 'overall') {
    return props.rounds
  }
  // Show only rounds up to and including the selected round
  const selectedRound = props.rounds.find(r => r.id.toString() === activeRound.value)
  if (!selectedRound) return props.rounds
  
  // Filter rounds and verify they have score data
  const roundsUpToSelected = props.rounds.filter(r => (r.display_order || 0) <= (selectedRound.display_order || 0))
  
  // For round-specific views, only show rounds that have actual scores in the contestants data
  if (displayedContestants.value && displayedContestants.value.length > 0) {
    const firstContestant = displayedContestants.value[0]
    const availableRoundNames = new Set(Object.keys(firstContestant.scores || {}))
    return roundsUpToSelected.filter(r => availableRoundNames.has(r.name))
  }
  
  return roundsUpToSelected
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
  if (activeRound.value === 'overall') return 'Overall Rankings'
  const round = props.rounds.find(r => r.id.toString() === activeRound.value)
  return round ? `${round.name} Rankings` : 'Rankings'
}

const isWinner = (contestant: Contestant) => {
  // Only show winner status for final round or overall view
  if (activeRound.value !== 'overall') {
    const round = props.rounds.find(r => r.id.toString() === activeRound.value)
    if (!round || round.type !== 'final') return false
  }
  
  const numberOfWinners = props.pageant?.number_of_winners || 3
  return contestant.rank && contestant.rank <= numberOfWinners
}

const getWinnerPosition = (rank: number) => {
  const positions = ['1st', '2nd', '3rd']
  return positions[rank - 1] || `${rank}th`
}

const shouldShowWinners = computed(() => {
  // Show winners for overall view or when viewing the last final round
  if (activeRound.value === 'overall') return true
  
  const selectedRound = props.rounds.find(r => r.id.toString() === activeRound.value)
  if (!selectedRound) return false
  
  // Check if this is the last final round
  const finalRounds = props.rounds.filter(r => r.type === 'final')
  if (finalRounds.length === 0) return false
  
  const lastFinalRound = finalRounds.sort((a, b) => (b.display_order || 0) - (a.display_order || 0))[0]
  return selectedRound.id === lastFinalRound.id
})

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
  
  // Set updating flag and debounce refresh
  isUpdating.value = true
  refreshTimeout = setTimeout(() => {
    console.log('🔄 Refreshing results data after score update')
    refreshData()
    // Reset flag after a delay to allow transition to complete
    setTimeout(() => {
      isUpdating.value = false
    }, 1000)
    refreshTimeout = null
  }, REFRESH_DEBOUNCE_MS)
}

const handleRankingsUpdate = (e: any) => {
  console.log('🏆 RankingsUpdated event received:', e)
  
  // Clear existing timeout
  if (refreshTimeout) {
    clearTimeout(refreshTimeout)
  }
  
  // Set updating flag and refresh immediately for ranking updates
  isUpdating.value = true
  refreshTimeout = setTimeout(() => {
    console.log('🔄 Refreshing results due to rankings update')
    refreshData()
    // Reset flag after transition
    setTimeout(() => {
      isUpdating.value = false
    }, 1000)
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