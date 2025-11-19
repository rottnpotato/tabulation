<template>
  <div class="space-y-8">
    <!-- Header Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 via-teal-600 to-teal-700 shadow-xl">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_1px_1px,#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>
      <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-teal-400 opacity-10 rounded-full blur-3xl"></div>
      
      <div class="relative p-8 md:p-12">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
          <div class="text-white space-y-2">
            <div class="flex items-center gap-3">
              <h1 class="text-3xl md:text-4xl font-black tracking-tight">Judge Dashboard</h1>
              <span class="inline-flex items-center text-xs font-bold px-3 py-1 rounded-full bg-white/20 border border-white/30 backdrop-blur-sm shadow-sm">
                Judge Panel
              </span>
            </div>
            <p class="text-teal-100 text-lg font-light">Welcome back, <span class="font-semibold text-white">{{ judge.name }}</span>.</p>
            <p class="text-sm text-teal-200 max-w-xl">Review and score contestants across your assigned pageants with precision and clarity.</p>
          </div>
          
          <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md rounded-2xl px-5 py-4 border border-white/20 shadow-inner">
            <div class="p-2 bg-white/20 rounded-xl">
              <User class="h-6 w-6 text-white" />
            </div>
            <div class="text-teal-50 leading-tight">
              <div class="text-sm font-bold text-white">{{ judge.email }}</div>
              <div class="text-xs opacity-80 flex items-center gap-1">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                Active Session
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tools: Search / Filter / Sort -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-1">
      <div class="flex flex-col lg:flex-row gap-2 p-2">
        <div class="flex-1 relative group">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-teal-500 transition-colors" />
          <input
            id="search"
            v-model="searchQuery"
            type="text"
            placeholder="Search pageants, venue, location…"
            class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-50 border-transparent focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium text-slate-700 placeholder:text-slate-400"
          />
        </div>
        
        <div class="flex items-center gap-2 overflow-x-auto pb-2 lg:pb-0">
          <div class="flex items-center bg-slate-50 rounded-xl p-1 border border-slate-100">
            <div class="px-3 py-2 text-slate-500">
              <Filter class="h-4 w-4" />
            </div>
            <select v-model="statusFilter" class="bg-transparent border-none text-sm font-medium text-slate-700 focus:ring-0 cursor-pointer py-2 pr-8 pl-0">
              <option value="all">All Statuses</option>
              <option value="ACTIVE">Active</option>
              <option value="SETUP">Setup</option>
              <option value="DRAFT">Draft</option>
              <option value="PENDING_APPROVAL">Pending</option>
              <option value="COMPLETED">Completed</option>
            </select>
          </div>

          <div class="flex items-center bg-slate-50 rounded-xl p-1 border border-slate-100">
            <div class="px-3 py-2 text-slate-500">
              <ArrowUpDown class="h-4 w-4" />
            </div>
            <select v-model="sortKey" class="bg-transparent border-none text-sm font-medium text-slate-700 focus:ring-0 cursor-pointer py-2 pr-8 pl-0">
              <option value="date">Date</option>
              <option value="progress">Progress</option>
              <option value="name">Name</option>
            </select>
            <button 
              @click="toggleSortDir" 
              type="button" 
              class="ml-1 p-2 rounded-lg hover:bg-white hover:shadow-sm text-slate-500 transition-all"
              :title="sortDir === 'asc' ? 'Ascending' : 'Descending'"
            >
              <span v-if="sortDir === 'desc'">↓</span>
              <span v-else>↑</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- No Pageants State -->
    <div v-if="pageants.length === 0" class="relative text-center py-24">
      <div class="absolute inset-0 bg-gradient-to-b from-teal-50/50 to-transparent rounded-3xl"></div>
      <div class="relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-teal-100 p-12 max-w-2xl mx-auto">
        <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-teal-50 mb-6 shadow-inner">
          <Calendar class="h-10 w-10 text-teal-600" />
        </div>
        <h3 class="text-2xl font-bold text-slate-900 mb-3">No Pageants Assigned</h3>
        <p class="text-slate-600 mb-8 max-w-md mx-auto leading-relaxed">
          You haven't been assigned to any pageants yet. Once a tabulator assigns you, they will appear here automatically.
        </p>
        <button
          @click="refreshData"
          class="inline-flex items-center px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-lg shadow-teal-600/20 hover:shadow-teal-600/30 transition-all duration-200 transform hover:-translate-y-0.5"
        >
          <RefreshCw class="w-5 h-5 mr-2" />
          Refresh Dashboard
        </button>
      </div>
    </div>

    <!-- Pageants Grid -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div
        v-for="pageant in displayedPageants"
        :key="pageant.id"
        class="group bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:border-teal-200 transition-all duration-300 flex flex-col"
      >
        <!-- Pageant Header -->
        <div class="p-7 border-b border-slate-100 bg-gradient-to-b from-slate-50/50 to-white">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0 space-y-3">
              <div class="flex flex-wrap gap-2">
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase"
                  :class="getStatusClass(pageant.status)"
                >
                  {{ formatStatus(pageant.status) }}
                </span>
                <span v-if="pageant.pageant_date" class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                  <Calendar class="h-3 w-3 mr-1.5" />
                  {{ formatDate(pageant.pageant_date) }}
                </span>
              </div>
              
              <h3 class="text-xl font-bold text-slate-900 leading-tight group-hover:text-teal-600 transition-colors">
                {{ pageant.name }}
              </h3>
              <p v-if="pageant.description" class="text-sm text-slate-500 line-clamp-2 leading-relaxed">
                {{ pageant.description }}
              </p>
            </div>

            <div class="text-right pl-4">
              <div class="relative inline-flex items-center justify-center">
                <svg class="w-16 h-16 transform -rotate-90">
                  <circle
                    class="text-slate-100"
                    stroke-width="6"
                    stroke="currentColor"
                    fill="transparent"
                    r="28"
                    cx="32"
                    cy="32"
                  />
                  <circle
                    class="transition-all duration-1000 ease-out"
                    :class="getProgressColor(pageant.scoring_progress)"
                    stroke-width="6"
                    :stroke-dasharray="175.9"
                    :stroke-dashoffset="175.9 - (175.9 * pageant.scoring_progress) / 100"
                    stroke-linecap="round"
                    stroke="currentColor"
                    fill="transparent"
                    r="28"
                    cx="32"
                    cy="32"
                  />
                </svg>
                <span class="absolute text-xs font-bold text-slate-700">
                  {{ pageant.scoring_progress }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Pageant Body -->
        <div class="p-7 flex-grow flex flex-col">
          <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-50 rounded-2xl p-3 text-center border border-slate-100">
              <div class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Contestants</div>
              <div class="text-lg font-bold text-slate-900">{{ pageant.contestants_count }}</div>
            </div>
            <div class="bg-slate-50 rounded-2xl p-3 text-center border border-slate-100">
              <div class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Rounds</div>
              <div class="text-lg font-bold text-slate-900">{{ pageant.rounds_count }}</div>
            </div>
            <div class="bg-slate-50 rounded-2xl p-3 text-center border border-slate-100">
              <div class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Scores Left</div>
              <div class="text-lg font-bold text-slate-900">{{ Math.max(0, pageant.total_scores_needed - pageant.scores_submitted) }}</div>
            </div>
          </div>

          <!-- Venue Info -->
          <div v-if="pageant.venue || pageant.location" class="space-y-2 mb-6">
            <div v-if="pageant.venue" class="flex items-center text-sm text-slate-600">
              <div class="w-8 h-8 rounded-full bg-teal-50 flex items-center justify-center mr-3 text-teal-500">
                <MapPin class="h-4 w-4" />
              </div>
              <span class="font-medium">{{ pageant.venue }}</span>
            </div>
            <div v-if="pageant.location" class="flex items-center text-sm text-slate-600">
              <div class="w-8 h-8 rounded-full bg-teal-50 flex items-center justify-center mr-3 text-teal-500">
                <Globe class="h-4 w-4" />
              </div>
              <span class="font-medium">{{ pageant.location }}</span>
            </div>
          </div>

          <div class="mt-auto space-y-4">
            <!-- Current Round Status -->
            <div v-if="pageant.current_round" class="p-4 bg-teal-50/50 border border-teal-100 rounded-2xl">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-teal-600 uppercase tracking-wide">Current Round</span>
                <span v-if="pageant.current_round.is_locked" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">
                  LOCKED
                </span>
                <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                  ACTIVE
                </span>
              </div>
              <div class="font-semibold text-slate-900">
                {{ pageant.current_round.name }}
              </div>
            </div>

            <!-- Actions -->
            <div class="flex">
              <Link
                v-if="pageant.id && pageant.rounds_count > 0"
                :href="getScoringUrl(pageant.id)"
                prefetch
                class="w-full inline-flex items-center justify-center px-6 py-3.5 bg-slate-900 hover:bg-teal-600 text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-lg shadow-slate-900/10 hover:shadow-teal-600/20 group-hover:translate-y-0"
              >
                <Star class="h-4 w-4 mr-2" />
                {{ pageant.current_round ? 'Continue Scoring' : 'Start Scoring' }}
              </Link>
              <div
                v-else
                class="w-full inline-flex items-center justify-center px-6 py-3.5 bg-slate-100 text-slate-400 text-sm font-bold rounded-xl cursor-not-allowed"
              >
                <Star class="h-4 w-4 mr-2" />
                No Rounds Available
              </div>
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

const refreshData = () => {
  router.reload()
}

const getScoringUrl = (pageantId) => {
  try {
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
  if (!status) return 'Unknown'
  return status.replace(/_/g, ' ')
}

const getStatusClass = (status) => {
  const normalized = (status || '').toString().trim().toUpperCase().replace(/\s+/g, '_')
  const map = {
    DRAFT: 'bg-slate-100 text-slate-600',
    SETUP: 'bg-teal-100 text-teal-700',
    ACTIVE: 'bg-emerald-100 text-emerald-700',
    COMPLETED: 'bg-teal-100 text-teal-700',
    PENDING_APPROVAL: 'bg-amber-100 text-amber-700'
  }
  return map[normalized] || 'bg-slate-100 text-slate-600'
}

const getProgressColor = (progress) => {
  if (progress >= 100) return 'text-emerald-500'
  if (progress >= 80) return 'text-teal-500'
  if (progress >= 60) return 'text-teal-500'
  if (progress >= 40) return 'text-teal-500'
  return 'text-slate-300'
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
