<template>
  <div class="space-y-4 sm:space-y-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header Section -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-500 via-sky-500 to-blue-600 shadow-lg">
        <div class="pointer-events-none absolute inset-0 opacity-30">
          <div class="absolute -left-24 -top-24 h-40 w-40 rounded-full bg-white/30 blur-2xl"></div>
          <div class="absolute -right-16 bottom-0 h-40 w-40 rounded-full bg-sky-900/20 blur-2xl"></div>
        </div>
        <div class="relative p-5 sm:p-6 lg:p-8">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="text-white">
              <div class="inline-flex items-center rounded-full bg-black/15 px-3 py-1 text-xs font-medium uppercase tracking-wide backdrop-blur-sm">
                <Trophy class="mr-2 h-4 w-4" />
                <span>Tabulator Results</span>
              </div>
              <h1 class="mt-3 text-2xl sm:text-3xl lg:text-4xl font-semibold tracking-tight">
                {{ pageant ? `${pageant.name} – Results` : 'Results Overview' }}
              </h1>
              <p class="mt-2 max-w-xl text-sm sm:text-base text-sky-50/90">
                Final rankings, stage breakdowns, and consolidated contestant scores in one modern view.
              </p>
            </div>
            <div
              v-if="pageant"
              class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:flex-wrap sm:justify-end"
            >
              <button
                @click="refreshData"
                class="inline-flex items-center justify-center rounded-full bg-white/10 px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-sm backdrop-blur-sm transition-all hover:bg-white/20 hover:shadow-md"
              >
                <RefreshCw class="mr-2 h-4 w-4" />
                <span>Refresh data</span>
              </button>
              <Link
                :href="route('tabulator.minor-awards', pageant.id)"
                class="inline-flex items-center justify-center rounded-full bg-white/95 px-4 py-2 text-xs sm:text-sm font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-50 hover:shadow-md"
              >
                <Trophy class="mr-2 h-4 w-4 text-blue-600" />
                <span>Minor awards</span>
              </Link>
              <Link
                :href="route('tabulator.print', pageant.id)"
                class="inline-flex items-center justify-center rounded-full border border-white/40 bg-white/10 px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-sm backdrop-blur-sm transition-all hover:bg-white/20 hover:shadow-md"
              >
                <Printer class="mr-2 h-4 w-4" />
                <span>Print results</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="py-14 text-center">
        <div class="mx-auto max-w-xl rounded-2xl border border-dashed border-gray-200 bg-white/80 p-10 shadow-sm">
          <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-100 ring-4 ring-white shadow">
            <Trophy class="h-12 w-12 text-blue-600" />
          </div>
          <h3 class="mb-2 text-xl font-semibold text-gray-900">No pageant assignment yet</h3>
          <p class="mx-auto mb-6 max-w-md text-sm sm:text-base text-gray-600">
            Once an organizer assigns you to a pageant, you will see consolidated rankings, stages, and results here.
          </p>
          <Link
            :href="route('tabulator.dashboard')"
            class="inline-flex items-center rounded-full bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
          >
            <LayoutDashboard class="mr-2 h-4 w-4" />
            Go to dashboard
          </Link>
        </div>
      </div>

      <!-- Controls -->
      <div
        v-if="pageant"
        class="mb-6 rounded-2xl border border-gray-100 bg-white/80 p-4 shadow-sm backdrop-blur-sm sm:p-6"
      >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
            <div>
              <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Results view</h2>
              <p class="text-xs text-gray-500">Quickly switch between overall and per-stage rankings.</p>
            </div>
            <div class="w-full sm:w-56">
              <CustomSelect
                v-model="activeRound"
                :options="roundOptions"
                placeholder="Select round"
              />
            </div>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-600">Stage:</span>
              <div class="inline-flex items-center rounded-full border border-gray-200/80 bg-gray-100 p-1 shadow-inner">
                <button
                  class="relative rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors"
                  :class="stage === 'overall' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                  @click="stage = 'overall'"
                >
                  Overall
                </button>
                <button
                  class="relative rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors"
                  :class="stage === 'semi-final' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                  @click="stage = 'semi-final'"
                >
                  Semi-final
                </button>
                <button
                  class="relative rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors"
                  :class="stage === 'final' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                  @click="stage = 'final'"
                >
                  Final
                </button>
                <button
                  class="relative rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors"
                  :class="stage === 'final-top3' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                  @click="stage = 'final-top3'"
                >
                  Top 3
                </button>
              </div>
            </div>
            <div class="flex items-center justify-end space-x-3">
              <button
                @click="refreshData"
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
              >
                <RefreshCw class="mr-2 h-4 w-4" />
                Refresh
              </button>
              <Link
                :href="route('tabulator.print', pageant.id)"
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
              >
                <Printer class="mr-2 h-4 w-4" />
                Print results
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Results Display -->
      <div v-if="pageant && displayedContestants && displayedContestants.length > 0">
        <ResultsRanking
          :title="getResultsTitle()"
          :contestants="displayedContestants"
          :rounds="displayedRounds"
        />

        <!-- Summary Statistics (minimal style) -->
        <section
          class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3"
          aria-label="Results summary statistics"
        >
          <div class="rounded-xl border border-gray-100 bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Highest score</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 tabular-nums">
              {{ highestScore }}
            </p>
          </div>

          <div class="rounded-xl border border-gray-100 bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Average score</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 tabular-nums">
              {{ averageScore }}
            </p>
          </div>

          <div class="rounded-xl border border-gray-100 bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total contestants</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 tabular-nums">
              {{ contestants.length }}
            </p>
          </div>
        </section>
      </div>

      <!-- Empty State -->
      <div v-if="pageant && (!displayedContestants || displayedContestants.length === 0)" class="py-12 text-center">
        <div class="mx-auto max-w-md rounded-2xl border border-dashed border-gray-200 bg-white/80 p-10 shadow-sm">
          <Trophy class="mb-4 mx-auto h-12 w-12 text-gray-300" />
          <h3 class="mb-2 text-lg font-semibold text-gray-900">No results available</h3>
          <p class="text-sm text-gray-500">
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

const props = defineProps<Props>()

const activeRound = ref('overall')
const stage = ref<'overall' | 'semi-final' | 'final' | 'final-top3'>('overall')

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
  let baseList: Contestant[]
  switch (stage.value) {
    case 'semi-final':
      baseList = props.contestantsSemiFinal
      break
    case 'final':
      baseList = props.contestantsFinal
      break
    case 'final-top3':
      baseList = props.contestantsFinal.slice(0, 3)
      break
    default:
      baseList = props.contestants
  }

  // Round filter selection currently does not recalc per round; we keep the list as-is
  return baseList
})

const displayedRounds = computed(() => {
  switch (stage.value) {
    case 'semi-final':
      return props.rounds.filter(r => r.type === 'semi-final')
    case 'final':
    case 'final-top3':
      return props.rounds.filter(r => r.type === 'final')
    default:
      return props.rounds
  }
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
  if (stage.value === 'semi-final') return 'Semi-Final Rankings'
  if (stage.value === 'final') return 'Final Rankings'
  if (stage.value === 'final-top3') return 'Top 3 (Final)'
  return 'Overall Rankings'
}

const refreshData = () => {
  router.reload()
}
</script>