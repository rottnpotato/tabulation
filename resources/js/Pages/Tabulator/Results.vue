<template>
  <div class="min-h-screen bg-slate-50 pb-20">
    <!-- Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between py-4 gap-4">
          <div class="flex items-center gap-4">
            <div class="p-2 bg-indigo-50 rounded-xl">
              <Trophy class="w-6 h-6 text-indigo-600" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Results</h1>
              <p class="text-sm text-slate-500 flex items-center gap-2">
                <span v-if="pageant" class="font-medium text-indigo-600">{{ pageant.name }}</span>
                <span v-else>Select a pageant</span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3" v-if="pageant">
            <button 
              @click="refreshData" 
              class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
              title="Refresh Results"
            >
              <RefreshCw class="w-5 h-5" />
            </button>
            <Link 
              :href="route('tabulator.print', pageant.id)"
              class="px-4 py-2 rounded-xl bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:text-indigo-600 transition-all flex items-center gap-2 border border-slate-200 shadow-sm"
            >
              <Printer class="w-4 h-4" />
              <span>Print Results</span>
            </Link>
            <Link 
              :href="route('tabulator.minor-awards', pageant.id)"
              class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition-all flex items-center gap-2 shadow-lg shadow-indigo-200"
            >
              <Award class="w-4 h-4" />
              <span>Minor Awards</span>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-20 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 max-w-2xl mx-auto">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-slate-50 mb-6">
            <Trophy class="h-12 w-12 text-slate-400" />
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-4">No Pageant Assignment</h3>
          <p class="text-slate-500 mb-8 text-lg">
            You haven't been assigned to any pageants yet. Once assigned, you'll see the results here.
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

      <div v-else>
        <!-- Controls -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-8 animate-fade-in">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1">
              <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-200 whitespace-nowrap">
                <Target class="w-4 h-4 text-slate-500" />
                <span class="text-sm font-medium text-slate-700">Results View</span>
              </div>
              <div class="w-full sm:w-64">
                <CustomSelect 
                  v-model="activeRound"
                  :options="roundOptions"
                  placeholder="Select Round"
                />
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
              <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-xl border border-slate-200">
                <button
                  v-for="s in ['overall', 'semi-final', 'final', 'final-top3']"
                  :key="s"
                  class="px-4 py-1.5 text-sm font-medium rounded-lg transition-all duration-200"
                  :class="stage === s ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'"
                  @click="stage = s as any"
                >
                  {{ s === 'final-top3' ? 'Top 3' : s.charAt(0).toUpperCase() + s.slice(1).replace('-', ' ') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Results Display -->
        <div v-if="displayedContestants && displayedContestants.length > 0" class="space-y-8 animate-fade-in">
          
          <!-- Summary Statistics -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
              <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
              <div class="relative flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-slate-500 mb-1">Highest Score</p>
                  <h4 class="text-3xl font-bold text-slate-900">{{ highestScore }}</h4>
                  <p class="text-xs text-indigo-600 font-medium mt-2 flex items-center gap-1">
                    <Trophy class="w-3 h-3" /> Top Performance
                  </p>
                </div>
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                  <Trophy class="w-6 h-6" />
                </div>
              </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
              <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
              <div class="relative flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-slate-500 mb-1">Average Score</p>
                  <h4 class="text-3xl font-bold text-slate-900">{{ averageScore }}</h4>
                  <p class="text-xs text-blue-600 font-medium mt-2 flex items-center gap-1">
                    <BarChart3 class="w-3 h-3" /> Overall Mean
                  </p>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                  <BarChart3 class="w-6 h-6" />
                </div>
              </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
              <div class="absolute top-0 right-0 w-24 h-24 bg-sky-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
              <div class="relative flex items-start justify-between">
                <div>
                  <p class="text-sm font-medium text-slate-500 mb-1">Total Contestants</p>
                  <h4 class="text-3xl font-bold text-slate-900">{{ contestants.length }}</h4>
                  <p class="text-xs text-sky-600 font-medium mt-2 flex items-center gap-1">
                    <Users class="w-3 h-3" /> Ranked Participants
                  </p>
                </div>
                <div class="p-3 bg-sky-50 text-sky-600 rounded-xl">
                  <Users class="w-6 h-6" />
                </div>
              </div>
            </div>
          </div>

          <!-- Main Ranking Table -->
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <ResultsRanking 
              :title="getResultsTitle()"
              :contestants="displayedContestants"
              :rounds="displayedRounds"
            />
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-20">
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-12 max-w-xl mx-auto">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Trophy class="h-8 w-8 text-slate-400" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No Results Available</h3>
            <p class="text-slate-500">
              Results will appear here once scoring is complete and calculations are finalized.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
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
}

interface Pageant {
  id: number
  name: string
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

  return baseList
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
  if (!displayedContestants.value || displayedContestants.value.length === 0) return 0
  const highest = Math.max(...displayedContestants.value.map(c => c.totalScore))
  return parseFloat(highest.toFixed(2))
})

const averageScore = computed(() => {
  if (!displayedContestants.value || displayedContestants.value.length === 0) return 0
  const sum = displayedContestants.value.reduce((acc, contestant) => acc + contestant.totalScore, 0)
  return parseFloat((sum / displayedContestants.value.length).toFixed(2))
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