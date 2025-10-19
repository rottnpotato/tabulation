<template>
  <div class="space-y-8">
    <!-- Header Section -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 shadow-lg">
      <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_1px_1px,#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
      <div class="relative p-6 md:p-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div class="text-white">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Judge Dashboard</h1>
              <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full bg-white/20 border border-white/30">Judge</span>
            </div>
            <p class="mt-1 text-orange-50">Welcome back, <span class="font-semibold">{{ judge.name }}</span>.</p>
            <p class="text-sm text-orange-100">Review and score contestants across your assigned pageants with clarity.</p>
          </div>
          <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md rounded-xl px-4 py-3 border border-white/20">
            <User class="h-5 w-5 text-orange-100" />
            <div class="text-orange-50 leading-tight">
              <div class="text-sm font-medium">{{ judge.email }}</div>
              <div class="text-[11px] opacity-80">Signed in</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <!-- <div class="grid grid-cols-1 md:grid-cols-4 gap-6"> -->
      <!-- Assigned Pageants -->
      <!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-xl bg-amber-100">
            <Calendar class="h-6 w-6 text-amber-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Assigned Pageants</p>
            <p class="text-2xl font-bold text-gray-900">{{ pageants.length }}</p>
          </div>
        </div>
      </div> -->

      <!-- Total Scores Needed -->
      <!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-xl bg-blue-100">
            <FileText class="h-6 w-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Scores Needed</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalScoresNeeded }}</p>
          </div>
        </div>
      </div> -->

      <!-- Scores Submitted -->
      <!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-xl bg-green-100">
            <CheckCircle class="h-6 w-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Scores Submitted</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalScoresSubmitted }}</p>
          </div>
        </div>
      </div> -->

      <!-- Overall Completion -->
      <!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Overall Completion</p>
            <p class="text-2xl font-bold text-gray-900">{{ overallCompletion }}%</p>
          </div>
          <div
            class="relative h-12 w-12 rounded-full grid place-items-center"
            :style="{
              background: `conic-gradient(#16a34a ${overallCompletion}%, #e5e7eb 0)`
            }"
            aria-hidden="true"
          >
            <div class="h-9 w-9 rounded-full bg-white grid place-items-center text-xs font-semibold text-gray-700">{{ overallCompletion }}</div>
          </div>
        </div>
      </div>
    </div> -->

    <!-- Tools: Search / Filter / Sort -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 md:p-5">
      <div class="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
        <div class="flex-1">
          <label class="sr-only" for="search">Search</label>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              id="search"
              v-model="searchQuery"
              type="text"
              placeholder="Search pageants, venue, location…"
              class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm"
            />
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <Filter class="h-4 w-4 text-gray-400" />
            <select v-model="statusFilter" class="rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
              <option value="all">All statuses</option>
              <option value="ACTIVE">Active</option>
              <option value="SETUP">Setup</option>
              <option value="DRAFT">Draft</option>
              <option value="PENDING_APPROVAL">Pending approval</option>
              <option value="COMPLETED">Completed</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <ArrowUpDown class="h-4 w-4 text-gray-400" />
            <select v-model="sortKey" class="rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
              <option value="date">By date</option>
              <option value="progress">By progress</option>
              <option value="name">By name</option>
            </select>
            <button @click="toggleSortDir" type="button" class="ml-1 inline-flex items-center rounded-lg border border-gray-200 px-2.5 py-2 text-sm hover:bg-gray-50">
              <span class="sr-only">Toggle sort direction</span>
              <span v-if="sortDir === 'desc'">↓</span>
              <span v-else>↑</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- No Pageants State -->
    <div v-if="pageants.length === 0" class="relative text-center py-16">
      <div class="absolute inset-0 bg-gradient-to-b from-amber-50/60 to-transparent rounded-2xl"></div>
      <div class="relative bg-white rounded-2xl shadow-md border border-gray-100 p-12 max-w-3xl mx-auto">
        <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-orange-100 to-amber-200 mb-6">
          <Calendar class="h-12 w-12 text-amber-600" />
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Pageants Assigned</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          You haven't been assigned to any pageants yet. Please wait for a tabulator to assign you to a pageant.
        </p>
        <button
          @click="refreshData"
          class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
        >
          <RefreshCw class="w-4 h-4 mr-2" />
          Refresh
        </button>
      </div>
    </div>

    <!-- Pageants Grid -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div
        v-for="pageant in displayedPageants"
        :key="pageant.id"
        class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all"
      >
        <!-- Pageant Header -->
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <h3 class="text-lg font-semibold text-gray-900 truncate">{{ pageant.name }}</h3>
              <p v-if="pageant.description" class="text-sm text-gray-600 mt-1 line-clamp-2">{{ pageant.description }}</p>

              <div class="flex flex-wrap gap-2 text-xs mt-2">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full font-medium"
                  :class="getStatusClass(pageant.status)"
                >
                  {{ formatStatus(pageant.status) }}
                </span>
                <span v-if="pageant.pageant_date" class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 rounded-full">
                  <Calendar class="h-3 w-3 mr-1" />
                  {{ formatDate(pageant.pageant_date) }}
                </span>
              </div>
            </div>

            <div class="text-right">
              <div class="text-xs text-gray-500 mb-1">Scoring Progress</div>
              <div class="text-lg font-bold" :class="getProgressColor(pageant.scoring_progress)">
                {{ pageant.scoring_progress }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Pageant Body -->
        <div class="p-6">
          <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="text-center">
              <div class="text-sm text-gray-500">Contestants</div>
              <div class="text-lg font-semibold text-gray-900">{{ pageant.contestants_count }}</div>
            </div>
            <div class="text-center">
              <div class="text-sm text-gray-500">Rounds</div>
              <div class="text-lg font-semibold text-gray-900">{{ pageant.rounds_count }}</div>
            </div>
            <div class="text-center">
              <div class="text-sm text-gray-500">Scores Left</div>
              <div class="text-lg font-semibold text-gray-900">{{ Math.max(0, pageant.total_scores_needed - pageant.scores_submitted) }}</div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mb-4">
            <div class="flex justify-between text-xs text-gray-600 mb-1">
              <span>Progress</span>
              <span>{{ pageant.scores_submitted }}/{{ pageant.total_scores_needed }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all duration-300"
                :class="getProgressBarColor(pageant.scoring_progress)"
                :style="{ width: pageant.scoring_progress + '%' }"
              ></div>
            </div>
          </div>

          <!-- Venue Info -->
          <div v-if="pageant.venue || pageant.location" class="text-xs text-gray-500 mb-4">
            <div v-if="pageant.venue" class="flex items-center">
              <MapPin class="h-3 w-3 mr-1" />
              {{ pageant.venue }}
            </div>
            <div v-if="pageant.location" class="flex items-center mt-1">
              <Globe class="h-3 w-3 mr-1" />
              {{ pageant.location }}
            </div>
          </div>

          <!-- Current Round Status -->
          <div v-if="pageant.current_round" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm text-amber-800">
                  <span class="font-medium">Current Round:</span> {{ pageant.current_round.name }}
                </p>
                <p v-if="pageant.current_round.is_locked" class="text-xs text-amber-600 mt-1">
                  🔒 Round is locked for editing
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end">
            <Link
              v-if="pageant.id && pageant.rounds_count > 0"
              :href="getScoringUrl(pageant.id)"
              prefetch
              class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors"
            >
              <Star class="h-4 w-4 mr-2" />
              {{ pageant.current_round ? 'Continue Scoring' : 'Start Scoring' }}
            </Link>
            <div
              v-else
              class="inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg cursor-not-allowed"
              title="No rounds available for scoring"
            >
              <Star class="h-4 w-4 mr-2" />
              No Rounds Available
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { 
  Calendar, 
  User, 
  FileText, 
  CheckCircle, 
  RefreshCw, 
  Star, 
  MapPin, 
  Globe,
  Search,
  Filter,
  ArrowUpDown
} from 'lucide-vue-next'
import JudgeLayout from '../../Layouts/JudgeLayout.vue'

defineOptions({
  layout: JudgeLayout
})

const props = defineProps({
  pageants: {
    type: Array,
    default: () => []
  },
  judge: {
    type: Object,
    required: true
  }
})

// Toolbar state
const searchQuery = ref('')
const statusFilter = ref('all')
const sortKey = ref('date') // 'date' | 'progress' | 'name'
const sortDir = ref('desc') // 'asc' | 'desc'

const totalScoresNeeded = computed(() => {
  return props.pageants.reduce((total, pageant) => total + pageant.total_scores_needed, 0)
})

const totalScoresSubmitted = computed(() => {
  return props.pageants.reduce((total, pageant) => total + pageant.scores_submitted, 0)
})

const overallCompletion = computed(() => {
  const needed = totalScoresNeeded.value
  if (!needed || needed <= 0) {
    return 0
  }
  const pct = Math.round((totalScoresSubmitted.value / needed) * 100)
  return Math.max(0, Math.min(100, pct))
})

const refreshData = () => {
  router.reload()
}

const getScoringUrl = (pageantId) => {
  try {
    // Find the pageant and use its current round ID, or default to no round ID
    const pageant = props.pageants.find(p => p.id === pageantId)
    const roundId = pageant?.current_round?.id

    if (roundId) {
      return route('judge.scoring', [pageantId, roundId])
    } else {
      return route('judge.scoring', [pageantId])
    }
  } catch (error) {
    console.error('Error generating scoring URL:', error)
    return '#'
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatStatus = (status) => {
  if (!status) {
    return 'Unknown'
  }
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusClass = (status) => {
  const normalized = (status || '').toString().trim().toUpperCase().replace(/\s+/g, '_')
  const map = {
    DRAFT: 'bg-yellow-100 text-yellow-800',
    SETUP: 'bg-blue-100 text-blue-800',
    ACTIVE: 'bg-green-100 text-green-800',
    COMPLETED: 'bg-gray-100 text-gray-800',
    PENDING_APPROVAL: 'bg-orange-100 text-orange-800'
  }
  return map[normalized] || 'bg-gray-100 text-gray-800'
}

const getProgressColor = (progress) => {
  if (progress >= 100) return 'text-green-600'
  if (progress >= 80) return 'text-orange-600'
  if (progress >= 60) return 'text-blue-600'
  if (progress >= 40) return 'text-yellow-600'
  return 'text-red-600'
}

const getProgressBarColor = (progress) => {
  if (progress >= 100) return 'bg-green-500'
  if (progress >= 80) return 'bg-orange-500'
  if (progress >= 60) return 'bg-blue-500'
  if (progress >= 40) return 'bg-yellow-500'
  return 'bg-red-500'
}

// Derived list with search / filter / sort
const displayedPageants = computed(() => {
  const norm = (s) => (s || '').toString().toLowerCase()
  const statusNorm = (s) => (s || '').toString().trim().toUpperCase().replace(/\s+/g, '_')

  let list = [...props.pageants]

  // Filter by status
  if (statusFilter.value !== 'all') {
    list = list.filter(p => statusNorm(p.status) === statusFilter.value)
  }

  // Search
  const q = norm(searchQuery.value)
  if (q) {
    list = list.filter(p => [p.name, p.description, p.venue, p.location].some(v => norm(v).includes(q)))
  }

  // Sort
  list.sort((a, b) => {
    let av = 0
    let bv = 0
    switch (sortKey.value) {
      case 'progress':
        av = a.scoring_progress || 0
        bv = b.scoring_progress || 0
        break
      case 'name':
        return sortDir.value === 'asc'
          ? (a.name || '').localeCompare(b.name || '')
          : (b.name || '').localeCompare(a.name || '')
      case 'date':
      default:
        av = a.pageant_date ? new Date(a.pageant_date).getTime() : (a.id || 0)
        bv = b.pageant_date ? new Date(b.pageant_date).getTime() : (b.id || 0)
        break
    }
    return sortDir.value === 'asc' ? av - bv : bv - av
  })

  return list
})

const toggleSortDir = () => {
  sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
}
</script>

<style>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
<style>
/* Subtle dot grid helper (scoped to this SFC) */
.bg-dots {
  background-image: radial-gradient(#ffffff 1px, transparent 1px);
  background-size: 16px 16px;
}
</style>
