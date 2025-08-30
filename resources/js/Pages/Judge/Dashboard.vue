<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-md overflow-hidden">
      <div class="p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="text-white">
            <h1 class="text-2xl md:text-3xl font-bold">Judge Dashboard</h1>
            <p class="mt-1 text-orange-100">Welcome back, {{ judge.name }}!</p>
            <p class="text-sm text-orange-200">Review and score contestants for your assigned pageants</p>
          </div>
          <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
            <User class="h-5 w-5 text-orange-100 mr-2" />
            <span class="text-orange-50 font-medium">{{ judge.email }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 bg-orange-100 rounded-lg">
            <Calendar class="h-6 w-6 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Assigned Pageants</p>
            <p class="text-2xl font-bold text-gray-900">{{ pageants.length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <FileText class="h-6 w-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Scores Needed</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalScoresNeeded }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <CheckCircle class="h-6 w-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Scores Submitted</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalScoresSubmitted }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- No Pageants State -->
    <div v-if="pageants.length === 0" class="text-center py-16">
      <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12">
        <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-orange-100 to-orange-200 mb-6">
          <Calendar class="h-12 w-12 text-orange-600" />
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No Pageants Assigned</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          You haven't been assigned to any pageants yet. Please wait for a tabulator to assign you to a pageant.
        </p>
        <button 
          @click="refreshData"
          class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
        >
          <RefreshCw class="w-4 h-4 mr-2" />
          Refresh
        </button>
      </div>
    </div>

    <!-- Pageants Grid -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div 
        v-for="pageant in pageants" 
        :key="pageant.id"
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all"
      >
        <!-- Pageant Header -->
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ pageant.name }}</h3>
              <p v-if="pageant.description" class="text-sm text-gray-600 mb-2 line-clamp-2">{{ pageant.description }}</p>
              
              <div class="flex flex-wrap gap-2 text-xs">
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

        <!-- Pageant Stats -->
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
              <div class="text-lg font-semibold text-gray-900">
                {{ pageant.total_scores_needed - pageant.scores_submitted }}
              </div>
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

          <!-- Action Button -->
          <div class="flex justify-end">
            <Link 
              v-if="pageant.id && pageant.rounds_count > 0"
              :href="getScoringUrl(pageant.id)"
              class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors"
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
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { 
  Calendar, 
  User, 
  FileText, 
  CheckCircle, 
  RefreshCw, 
  Star, 
  MapPin, 
  Globe 
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

const totalScoresNeeded = computed(() => {
  return props.pageants.reduce((total, pageant) => total + pageant.total_scores_needed, 0)
})

const totalScoresSubmitted = computed(() => {
  return props.pageants.reduce((total, pageant) => total + pageant.scores_submitted, 0)
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
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusClass = (status) => {
  const statusClasses = {
    'Draft': 'bg-yellow-100 text-yellow-800',
    'Setup': 'bg-blue-100 text-blue-800',
    'Active': 'bg-green-100 text-green-800',
    'Completed': 'bg-gray-100 text-gray-800',
    'Pending_Approval': 'bg-orange-100 text-orange-800',
  }
  return statusClasses[status] || 'bg-gray-100 text-gray-800'
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
