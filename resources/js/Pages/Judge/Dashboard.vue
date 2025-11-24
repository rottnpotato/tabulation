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
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
            <div class="space-y-2">
              <div class="flex items-center gap-3">
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900">Judge Dashboard</h1>
                <span class="inline-flex items-center text-xs font-bold px-3 py-1 rounded-full bg-teal-50 text-teal-700 border border-teal-100">
                  Judge Panel
                </span>
              </div>
              <p class="text-slate-500 text-lg font-light">
                Welcome back, <span class="font-semibold text-slate-900">{{ judge.name }}</span>.
              </p>
              <p class="text-sm text-slate-400 max-w-xl">
                Review and score contestants across your assigned pageants with precision and clarity.
              </p>
            </div>

            <div class="flex items-center gap-4 bg-white/60 backdrop-blur-md rounded-2xl px-5 py-4 border border-teal-100 shadow-sm">
              <div class="p-2 bg-teal-50 rounded-xl">
                <User class="h-6 w-6 text-teal-600" />
              </div>
              <div class="leading-tight">
                <div class="text-sm font-bold text-slate-900">{{ judge.email }}</div>
                <div class="text-xs text-slate-500 flex items-center gap-1">
                  <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                  Active Session
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tools: Search / Filter / Sort -->
      <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-2 mb-8 relative z-20">
        <div class="flex flex-col lg:flex-row gap-2">
          <div class="flex-1 relative group">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-teal-400/70 group-focus-within:text-teal-500 transition-colors" />
            <input
              id="search"
              v-model="searchQuery"
              type="text"
              placeholder="Search pageants, venue, location…"
              class="w-full pl-12 pr-4 py-3 rounded-xl bg-white border border-teal-100 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all text-sm font-medium text-teal-900 placeholder:text-teal-400/60 shadow-sm"
            />
          </div>
          
          <div class="flex items-center gap-2 overflow-x-auto pb-2 lg:pb-0">
            <div class="flex items-center bg-white rounded-xl p-1 border border-teal-100 shadow-sm">
              <div class="px-3 py-2 text-teal-500">
                <Filter class="h-4 w-4" />
              </div>
              <select v-model="statusFilter" class="bg-transparent border-none text-sm font-medium text-teal-900 focus:ring-0 cursor-pointer py-2 pr-8 pl-0">
                <option value="all">All Statuses</option>
                <option value="ACTIVE">Active</option>
                <option value="SETUP">Setup</option>
                <option value="DRAFT">Draft</option>
                <option value="PENDING_APPROVAL">Pending</option>
                <option value="COMPLETED">Completed</option>
              </select>
            </div>

            <div class="flex items-center bg-white rounded-xl p-1 border border-teal-100 shadow-sm">
              <div class="px-3 py-2 text-teal-500">
                <ArrowUpDown class="h-4 w-4" />
              </div>
              <select v-model="sortKey" class="bg-transparent border-none text-sm font-medium text-teal-900 focus:ring-0 cursor-pointer py-2 pr-8 pl-0">
                <option value="date">Date</option>
                <option value="progress">Progress</option>
                <option value="name">Name</option>
              </select>
              <button 
                @click="toggleSortDir" 
                type="button" 
                class="ml-1 p-2 rounded-lg hover:bg-teal-50 text-teal-500 transition-all"
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
          <h3 class="text-2xl font-bold text-teal-900 mb-3">No Pageants Assigned</h3>
          <p class="text-teal-600/80 mb-8 max-w-md mx-auto leading-relaxed">
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
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="pageant in displayedPageants"
          :key="pageant.id"
          class="group relative flex flex-col bg-teal-950 rounded-[2rem] overflow-hidden shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-teal-900/50"
        >
          <!-- Image / Cover Area -->
          <div class="relative h-64 w-full overflow-hidden">
            <!-- Cover Image or Gradient -->
            <div 
              v-if="pageant.cover_image"
              class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
              :style="{ backgroundImage: `url(${pageant.cover_image})` }"
            ></div>
            <div 
              v-else
              class="absolute inset-0 bg-gradient-to-br from-teal-500 via-emerald-600 to-cyan-600 group-hover:scale-110 transition-transform duration-700"
            ></div>
            
            <!-- Dark Overlay for text readability if image exists -->
            <div class="absolute inset-0 bg-gradient-to-t from-teal-950/90 via-teal-950/40 to-transparent opacity-60"></div>
            
            <!-- Decorative Pattern overlay (only for gradient) -->
            <div v-if="!pageant.cover_image" class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_1px_1px,#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
            
            <!-- Top Right: Progress Indicator -->
            <div class="absolute top-4 right-4">
              <div class="relative flex items-center justify-center w-12 h-12 bg-black/30 backdrop-blur-md rounded-full border border-white/10 shadow-lg">
                 <span class="text-xs font-bold text-white">{{ pageant.scoring_progress }}%</span>
                 <svg class="absolute inset-0 w-full h-full -rotate-90" viewBox="0 0 36 36">
                    <path class="text-white/20" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                    <path class="text-teal-400 transition-all duration-1000 ease-out" :stroke-dasharray="pageant.scoring_progress + ', 100'" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                 </svg>
              </div>
            </div>

            <!-- Status Badge (Top Left) -->
             <div class="absolute top-4 left-4">
                <span 
                  class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md border border-white/10 shadow-sm"
                  :class="getStatusBadgeClass(pageant.status)"
                >
                  {{ formatStatus(pageant.status) }}
                </span>
             </div>
          </div>

          <!-- Content Body -->
          <div class="p-6 flex flex-col flex-grow gap-4">
            <!-- Title & Meta -->
            <div class="flex justify-between items-start gap-2">
              <div class="flex-1">
                <h3 class="text-2xl font-bold text-white leading-tight">{{ pageant.name }}</h3>
                <p v-if="pageant.start_date" class="text-teal-200/60 text-xs mt-1.5 flex items-center gap-1.5">
                  <Calendar class="w-3 h-3" />
                  Start Date: {{ formatDate(pageant.start_date) }}
                </p>
              </div>
              <!-- Date Pill -->
              <div v-if="pageant.pageant_date" class="shrink-0 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-white text-xs font-bold backdrop-blur-sm">
                 {{ formatDateShort(pageant.pageant_date) }}
              </div>
            </div>

            <!-- Description -->
            <p v-if="pageant.description" class="text-teal-200/70 text-sm line-clamp-2 leading-relaxed">
              {{ pageant.description }}
            </p>

            <!-- Tags / Stats Row -->
            <div class="flex flex-wrap gap-2 mt-auto">
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-900/50 text-teal-100 text-xs font-medium border border-teal-800/50">
                 <User class="w-3.5 h-3.5 text-teal-400" />
                 {{ pageant.contestants_count }} Contestants
              </div>
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-900/50 text-teal-100 text-xs font-medium border border-teal-800/50">
                 <Layers class="w-3.5 h-3.5 text-teal-400" />
                 {{ pageant.rounds_count }} Rounds
              </div>
               <div v-if="pageant.venue" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-900/50 text-teal-100 text-xs font-medium border border-teal-800/50">
                 <MapPin class="w-3.5 h-3.5 text-teal-400" />
                 {{ pageant.venue }}
              </div>
            </div>

            <!-- Action Button -->
            <div class="pt-2">
              <!-- Date restriction warning -->
              <div v-if="!pageant.can_be_scored && pageant.pageant_date" class="mb-3 px-3 py-2 rounded-lg bg-amber-900/30 border border-amber-700/50 text-amber-200 text-xs flex items-center gap-2">
                <Calendar class="w-3.5 h-3.5 text-amber-400" />
                <span>Scoring opens on {{ formatDate(pageant.pageant_date) }}</span>
              </div>
              
              <Link
                v-if="pageant.id && pageant.rounds_count > 0 && pageant.can_be_scored"
                :href="getScoringUrl(pageant.id)"
                class="w-full py-3.5 bg-white hover:bg-teal-50 text-teal-950 font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-white/5 group-hover:scale-[1.02]"
              >
                <Star class="w-4 h-4 text-teal-900" />
                {{ pageant.current_round ? 'Continue Scoring' : 'Start Scoring' }}
              </Link>
              <div
                v-else-if="!pageant.can_be_scored && pageant.rounds_count > 0"
                class="w-full py-3.5 bg-teal-900/40 text-teal-500/50 font-bold rounded-xl cursor-not-allowed flex items-center justify-center gap-2 border border-teal-900/50"
              >
                <Calendar class="w-4 h-4" />
                Available on Pageant Date
              </div>
              <div
                v-else
                class="w-full py-3.5 bg-teal-900/40 text-teal-500/50 font-bold rounded-xl cursor-not-allowed flex items-center justify-center gap-2 border border-teal-900/50"
              >
                <Star class="w-4 h-4" />
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
  ArrowUpDown,
  Layers
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

const formatDateShort = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const formatStatus = (status) => {
  if (!status) return 'Unknown'
  return status.replace(/_/g, ' ')
}

const getStatusBadgeClass = (status) => {
  const normalized = (status || '').toString().trim().toUpperCase().replace(/\s+/g, '_')
  const map = {
    DRAFT: 'bg-slate-500/20 text-slate-200 border-slate-500/30',
    SETUP: 'bg-teal-500/20 text-teal-200 border-teal-500/30',
    ACTIVE: 'bg-teal-400/20 text-teal-100 border-teal-400/30 shadow-[0_0_10px_rgba(45,212,191,0.2)]',
    ONGOING: 'bg-teal-400/20 text-teal-100 border-teal-400/30 shadow-[0_0_10px_rgba(45,212,191,0.2)]',
    COMPLETED: 'bg-cyan-500/20 text-cyan-200 border-cyan-500/30',
    PENDING_APPROVAL: 'bg-amber-500/20 text-amber-200 border-amber-500/30'
  }
  return map[normalized] || 'bg-slate-500/20 text-slate-200 border-slate-500/30'
}

const getStatusClass = (status) => {
  // Kept for backward compatibility if needed, though getStatusBadgeClass is preferred for new cards
  return getStatusBadgeClass(status)
}

const getProgressColor = (progress) => {
  if (progress >= 100) return 'text-teal-300'
  if (progress >= 80) return 'text-teal-400'
  if (progress >= 60) return 'text-teal-500'
  if (progress >= 40) return 'text-teal-600'
  return 'text-teal-800'
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
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
