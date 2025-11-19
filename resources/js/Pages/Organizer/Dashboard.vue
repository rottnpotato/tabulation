<template>
  <div class="space-y-4 sm:space-y-6">
    <!-- Page Header with Enhanced Design -->
    <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-indigo-100">
      <!-- Abstract Background Pattern -->
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-blue-50/50 to-white opacity-90"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
      </div>
      
      <div class="relative z-10 p-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0 gap-4">
          <div class="space-y-2">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-indigo-50 rounded-xl border border-indigo-100">
                <Crown class="h-8 w-8 text-indigo-600" />
              </div>
              <div>
                <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">Organizer Dashboard</h1>
                <p class="text-slate-500 text-lg font-light">Manage pageants, contestants, and track progress</p>
              </div>
            </div>
          </div>
          
          <!-- Enhanced Stats Cards -->
          <div class="flex flex-wrap gap-3">
            <div class="group bg-white/80 backdrop-blur-sm border border-indigo-100 rounded-2xl px-5 py-4 text-sm font-medium text-slate-600 transition-all duration-300 hover:shadow-md hover:border-indigo-200">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                  <Calendar class="h-5 w-5" />
                </div>
                <div>
                  <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Pageants</div>
                  <div class="text-2xl font-bold text-slate-900 leading-none mt-0.5">{{ totalPageants }}</div>
                </div>
              </div>
            </div>
            
            <div class="group bg-white/80 backdrop-blur-sm border border-indigo-100 rounded-2xl px-5 py-4 text-sm font-medium text-slate-600 transition-all duration-300 hover:shadow-md hover:border-indigo-200">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                  <Activity class="h-5 w-5" />
                </div>
                <div>
                  <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Active Now</div>
                  <div class="text-2xl font-bold text-slate-900 leading-none mt-0.5">{{ pageantCounts?.active || 0 }}</div>
                </div>
              </div>
            </div>
            
            <div class="group bg-white/80 backdrop-blur-sm border border-indigo-100 rounded-2xl px-5 py-4 text-sm font-medium text-slate-600 transition-all duration-300 hover:shadow-md hover:border-indigo-200">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                  <CheckCircle class="h-5 w-5" />
                </div>
                <div>
                  <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Completed</div>
                  <div class="text-2xl font-bold text-slate-900 leading-none mt-0.5">{{ pageantCounts?.completed || 0 }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Enhanced Quick Links Bar -->
      <div class="relative bg-slate-50/50 border-t border-indigo-50 px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-wrap gap-2 sm:gap-3">
          <Link :href="route('organizer.pageants.create')" class="group flex items-center px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-300 shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transform hover:-translate-y-0.5 text-xs sm:text-sm font-medium">
            <div class="p-1 bg-white/20 rounded-lg mr-2 group-hover:bg-white/30 transition-colors">
              <Plus class="h-3 w-3 sm:h-4 sm:w-4 text-white" />
            </div>
            <span>Create Pageant</span>
          </Link>
          
          <Link :href="route('organizer.my-pageants')" class="group flex items-center px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 text-xs sm:text-sm font-medium">
            <Crown class="h-3 w-3 sm:h-4 sm:w-4 mr-2 text-indigo-500" />
            <span>My Pageants</span>
          </Link>
          
          <Link :href="route('organizer.contestants')" class="group flex items-center px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 text-xs sm:text-sm font-medium">
            <UserPlus class="h-3 w-3 sm:h-4 sm:w-4 mr-2 text-blue-500" />
            <span>Contestants</span>
          </Link>
          
          <Link :href="route('organizer.criteria')" class="group flex items-center px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 text-xs sm:text-sm font-medium">
            <ListChecks class="h-3 w-3 sm:h-4 sm:w-4 mr-2 text-teal-500" />
            <span>Criteria</span>
          </Link>
          
          <Link :href="route('organizer.scoring')" class="group flex items-center px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 text-xs sm:text-sm font-medium">
            <BarChart2 class="h-3 w-3 sm:h-4 sm:w-4 mr-2 text-purple-500" />
            <span>Scoring</span>
          </Link>
        </div>
      </div>
    </div>


    <!-- Recent Pageants -->
    <div class="grid grid-cols-1 gap-4 sm:gap-6">
      <!-- Recent Pageants with Enhanced Design -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
        <div class="bg-slate-50/50 p-5 border-b border-slate-100">
          <div class="flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center">
              <div class="p-2 bg-indigo-200 rounded-lg mr-3">
                <Calendar class="h-5 w-5 text-indigo-700" />
              </div>
              Recent Pageants
            </h2>
            <div class="text-sm text-indigo-700 font-medium">
              {{ recentPageants.length }} {{ recentPageants.length === 1 ? 'pageant' : 'pageants' }}
            </div>
          </div>
        </div>
        
        <div v-if="isLoading" class="p-6 space-y-4">
          <div v-for="i in 5" :key="i" class="border-b pb-4 last:border-b-0 last:pb-0">
            <div class="h-6 w-2/3 bg-gray-200 rounded-lg shimmer shimmer-blue mb-3"></div>
            <div class="h-4 w-1/2 bg-gray-200 rounded shimmer shimmer-blue"></div>
          </div>
        </div>
        
        <div v-else-if="recentPageants.length === 0" class="p-8 text-center py-16">
          <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-200 mb-6 shadow-md">
            <Calendar class="h-10 w-10 text-indigo-600" />
          </div>
          <h3 class="mt-2 text-lg font-bold text-gray-900">No pageants found</h3>
          <p class="mt-2 text-sm text-gray-600 max-w-sm mx-auto">
            You haven't been assigned to any pageants yet. Create your first pageant to get started!
          </p>
          <div class="mt-6">
            <Link :href="route('organizer.pageants.create')" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-xl hover:from-indigo-600 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-medium">
              <Plus class="h-5 w-5 mr-2" />
              Create Your First Pageant
            </Link>
          </div>
        </div>
        
        <div v-else class="divide-y divide-gray-100">
          <div v-for="(pageant, index) in recentPageants" :key="index" 
               class="group p-5 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-indigo-50/50 transition-all duration-300 cursor-pointer border-l-4 border-transparent hover:border-indigo-500"
               @click="viewPageant(pageant)">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-start gap-3">
                  <div class="p-2 bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors">
                    <Crown class="h-5 w-5 text-indigo-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-indigo-700 transition-colors truncate">
                      {{ pageant.name }}
                    </h3>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs sm:text-sm text-gray-600">
                      <div class="flex items-center">
                        <Calendar class="h-4 w-4 mr-1.5 text-gray-400" />
                        <span>{{ formatDateRange(pageant) || 'Date not set' }}</span>
                      </div>
                      <div class="flex items-center">
                        <MapPin class="h-4 w-4 mr-1.5 text-gray-400" />
                        <span class="truncate">{{ pageant.venue || pageant.location || 'Location TBA' }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-3 flex-shrink-0">
                <Tooltip :text="getPageantStatusTooltip(pageant.status)" position="left">
                  <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-help"
                        :class="getStatusClass(pageant.status)">
                    {{ formatStatusText(pageant.status) }}
                  </span>
                </Tooltip>
                <ChevronRight class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>
          </div>
        </div>
        
        <div class="border-t-2 border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-4 rounded-b-2xl">
          <Tooltip text="View and manage all your pageants" position="top">
            <Link :href="route('organizer.my-pageants')" class="group text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center transition-all">
              <span>View all pageants</span>
              <ChevronRight class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" />
            </Link>
          </Tooltip>
        </div>
      </div>
    </div>

    <!-- Enhanced Recent Activity Section -->
    <div class="bg-white shadow-sm rounded-3xl border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
      <div class="bg-slate-50/50 p-5 border-b border-slate-100">
        <div class="flex items-center justify-between">
          <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center">
            <div class="p-2 bg-indigo-200 rounded-lg mr-3">
              <Activity class="h-5 w-5 text-indigo-700" />
            </div>
            Recent Activity
          </h2>
          <div v-if="activities.length > 0" class="text-sm text-indigo-700 font-medium">
            {{ activities.length }} {{ activities.length === 1 ? 'activity' : 'activities' }}
          </div>
        </div>
      </div>
      
      <div class="p-6 sm:p-8">
        <div v-if="isLoading" class="space-y-6">
          <div v-for="i in 5" :key="i" class="flex items-start gap-4">
            <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-200 shimmer shimmer-blue"></div>
            <div class="flex-1 space-y-2">
              <div class="h-5 w-3/4 bg-gray-200 rounded shimmer shimmer-blue"></div>
              <div class="h-4 w-1/2 bg-gray-200 rounded shimmer shimmer-blue"></div>
            </div>
          </div>
        </div>
        
        <div v-else-if="activities.length === 0" class="relative">
          <div class="flex items-center justify-center py-12 text-center">
            <div class="max-w-md">
              <div class="relative inline-flex items-center justify-center">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-full blur-xl opacity-20 animate-pulse"></div>
                <div class="relative flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-200 shadow-lg">
                  <Activity class="h-10 w-10 text-indigo-600" />
                </div>
              </div>
              <h3 class="mt-6 text-xl font-bold text-gray-900">No Recent Activity</h3>
              <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                Activities from your tabulators and judges will appear here. This includes scoring updates, contestant changes, and other pageant actions.
              </p>
              <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <Link :href="route('organizer.pageants.create')" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-xl hover:from-indigo-600 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                  <Plus class="h-5 w-5 mr-2" />
                  Create Pageant
                </Link>
                <Link :href="route('organizer.my-pageants')" class="inline-flex items-center justify-center px-6 py-3 bg-white text-indigo-600 border-2 border-indigo-300 rounded-xl hover:bg-indigo-50 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold">
                  <Crown class="h-5 w-5 mr-2" />
                  View Pageants
                </Link>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Activity List -->
        <div v-else class="space-y-3">
          <div v-for="activity in activities" :key="activity.id"
               class="group flex items-start gap-4 p-4 rounded-xl hover:bg-gradient-to-r hover:from-indigo-50 hover:to-indigo-50/30 transition-all duration-300 border border-gray-100 hover:border-indigo-200">
            <!-- Activity Icon -->
            <div class="flex-shrink-0">
              <div class="p-3 rounded-xl bg-gradient-to-br group-hover:scale-110 transition-transform duration-300"
                   :class="getActivityIconClass(activity.action_type)">
                <component :is="getActivityIcon(activity.action_type)" class="h-5 w-5 text-white" />
              </div>
            </div>
            
            <!-- Activity Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 mb-1">
                    {{ activity.description }}
                  </p>
                  <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-medium"
                          :class="getRoleBadgeClass(activity.user_role)">
                      {{ activity.user_name }}
                    </span>
                    <span class="text-gray-400">•</span>
                    <span class="font-medium text-indigo-600">{{ activity.pageant_name }}</span>
                    <span class="text-gray-400">•</span>
                    <span>{{ activity.formatted_time }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Modal -->
    <OrganizerSettingsModal 
      :is-visible="isSettingsModalVisible" 
      @close="CloseSettingsModal"
      @update="UpdateSettings" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import '@/Components/skeletons/skeleton.css'
import OrganizerSettingsModal from '@/Components/modals/OrganizerSettingsModal.vue'
import Tooltip from '@/Components/Tooltip.vue'
import { 
  Calendar, MapPin, Users, ChevronRight, 
  UserPlus, ListChecks, BarChart2, Settings,
  Crown, Scale, Timer, CheckCircle, Sparkles, 
  Layers, Zap, Star, Clock, Edit, Activity, 
  Unlock, Plus
} from 'lucide-vue-next'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageantCounts: Object,
  recentPageants: Array,
  totalPageants: Number,
  recentActivities: Array,
  pageantIds: Array,
})

// State
const isLoading = ref(true)
const isSettingsModalVisible = ref(false)
const activities = ref(props.recentActivities || [])

// Status cards data with enhanced styling
const statusCards = [
  { 
    title: 'Pending', 
    key: 'pending_approval',
    icon: Clock, 
    bgColor: 'bg-white',
    borderColor: '#fb923c',
    bgGradient: 'bg-gradient-to-br from-orange-400 to-orange-500',
    textColor: 'text-orange-700',
    countColor: 'text-orange-600',
    iconBg: 'bg-orange-100',
    iconColor: 'text-orange-600'
  },
  { 
    title: 'Draft', 
    key: 'draft',
    icon: Edit, 
    bgColor: 'bg-white',
    borderColor: '#9ca3af',
    bgGradient: 'bg-gradient-to-br from-gray-400 to-gray-500',
    textColor: 'text-gray-700',
    countColor: 'text-gray-600',
    iconBg: 'bg-gray-100',
    iconColor: 'text-gray-600'
  },
  { 
    title: 'Setup', 
    key: 'setup',
    icon: Settings, 
    bgColor: 'bg-white',
    borderColor: '#60a5fa',
    bgGradient: 'bg-gradient-to-br from-blue-400 to-blue-500',
    textColor: 'text-blue-700',
    countColor: 'text-blue-600',
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600'
  },
  { 
    title: 'Active', 
    key: 'active',
    icon: Activity, 
    bgColor: 'bg-white',
    borderColor: '#34d399',
    bgGradient: 'bg-gradient-to-br from-green-400 to-green-500',
    textColor: 'text-green-700',
    countColor: 'text-green-600',
    iconBg: 'bg-green-100',
    iconColor: 'text-green-600'
  },
  { 
    title: 'Completed', 
    key: 'completed',
    icon: CheckCircle, 
    bgColor: 'bg-white',
    borderColor: '#a78bfa',
    bgGradient: 'bg-gradient-to-br from-purple-400 to-purple-500',
    textColor: 'text-purple-700',
    countColor: 'text-purple-600',
    iconBg: 'bg-purple-100',
    iconColor: 'text-purple-600'
  }
]

// Function to get count for a status
const getStatusCount = (key) => {
  return props.pageantCounts?.[key] || 0
}

// Format date range function
const formatDateRange = (pageant) => {
  if (pageant.start_date && pageant.end_date) {
    return `${pageant.start_date} - ${pageant.end_date}`
  } else if (pageant.start_date) {
    return `Starts: ${pageant.start_date}`
  } else if (pageant.end_date) {
    return `Ends: ${pageant.end_date}`
  }
  return ''
}

// Format date time
const formatDateTime = (datetime) => {
  if (!datetime) return 'Date not set'
  
  try {
    const date = new Date(datetime)
    return date.toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric', 
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    return datetime // Return as is if parsing fails
  }
}

// Status styling with better colors
const getStatusClass = (status) => {
  const statusMap = {
    'Pending_Approval': 'bg-orange-100 text-orange-800 border border-orange-200',
    'Draft': 'bg-gray-100 text-gray-800 border border-gray-200',
    'Setup': 'bg-blue-100 text-blue-800 border border-blue-200',
    'Active': 'bg-green-100 text-green-800 border border-green-200',
    'Completed': 'bg-purple-100 text-purple-800 border border-purple-200',
    'Unlocked_For_Edit': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800 border border-gray-200'
}

// Format status text for better readability
const formatStatusText = (status) => {
  return status.replace(/_/g, ' ')
}

// Navigate to pageant details
const viewPageant = (pageant) => {
  router.visit(route('organizer.pageant.view', { id: pageant.id }))
}



// Get tooltip text for pageant status badges
const getPageantStatusTooltip = (status) => {
  switch (status) {
    case 'Pending_Approval':
      return 'Awaiting admin approval before you can manage this pageant'
    case 'Draft':
      return 'In planning phase - add contestants and criteria'
    case 'Setup':
      return 'Ready for contestant registration and judge assignments'
    case 'Active':
      return 'Currently running with live scoring in progress'
    case 'Completed':
      return 'Finished with final results available'
    case 'Unlocked_For_Edit':
      return 'Temporarily unlocked for editing'
    default:
      return 'Current pageant status'
  }
}

// Settings modal functions
const OpenSettingsModal = () => {
  isSettingsModalVisible.value = true
}

const CloseSettingsModal = () => {
  isSettingsModalVisible.value = false
}

const UpdateSettings = (settings) => {
  console.log('Updating settings:', settings)
  // Show success message (would be a toast notification in a real app)
  alert('Settings updated successfully!')
}

// Helper functions for activity display
const getActivityIcon = (actionType) => {
  const iconMap = {
    'SCORE_SUBMITTED': Star,
    'SCORE_UPDATED': Edit,
    'CONTESTANT_ADDED': UserPlus,
    'CONTESTANT_UPDATED': Users,
    'CONTESTANT_REMOVED': Users,
    'JUDGE_ASSIGNED': Scale,
    'JUDGE_REMOVED': Users,
    'TABULATOR_ASSIGNED': BarChart2,
    'TABULATOR_REMOVED': BarChart2,
    'ROUND_STARTED': Timer,
    'ROUND_COMPLETED': CheckCircle,
    'CRITERIA_CREATED': ListChecks,
    'CRITERIA_UPDATED': ListChecks,
    'PAGEANT_UPDATED': Crown,
    'STATUS_CHANGED': Sparkles,
  }
  return iconMap[actionType] || Activity
}

const getActivityIconClass = (actionType) => {
  const classMap = {
    'SCORE_SUBMITTED': 'from-yellow-400 to-yellow-600',
    'SCORE_UPDATED': 'from-blue-400 to-blue-600',
    'CONTESTANT_ADDED': 'from-green-400 to-green-600',
    'CONTESTANT_UPDATED': 'from-blue-400 to-blue-600',
    'CONTESTANT_REMOVED': 'from-red-400 to-red-600',
    'JUDGE_ASSIGNED': 'from-purple-400 to-purple-600',
    'JUDGE_REMOVED': 'from-red-400 to-red-600',
    'TABULATOR_ASSIGNED': 'from-indigo-400 to-indigo-600',
    'TABULATOR_REMOVED': 'from-red-400 to-red-600',
    'ROUND_STARTED': 'from-green-400 to-green-600',
    'ROUND_COMPLETED': 'from-purple-400 to-purple-600',
    'CRITERIA_CREATED': 'from-teal-400 to-teal-600',
    'CRITERIA_UPDATED': 'from-blue-400 to-blue-600',
    'PAGEANT_UPDATED': 'from-indigo-400 to-indigo-600',
    'STATUS_CHANGED': 'from-pink-400 to-pink-600',
  }
  return classMap[actionType] || 'from-gray-400 to-gray-600'
}

const getRoleBadgeClass = (role) => {
  const classMap = {
    'judge': 'bg-purple-100 text-purple-800',
    'tabulator': 'bg-indigo-100 text-indigo-800',
    'organizer': 'bg-indigo-100 text-indigo-800',
    'admin': 'bg-red-100 text-red-800',
    'system': 'bg-gray-100 text-gray-800',
  }
  return classMap[role] || 'bg-gray-100 text-gray-800'
}

// Real-time updates using Laravel Echo
onMounted(() => {
  // Simulate loading time with staggered animation
  setTimeout(() => {
    isLoading.value = false
  }, 800)
  
  // Subscribe to real-time activity updates for each pageant
  if (window.Echo && props.pageantIds && props.pageantIds.length > 0) {
    props.pageantIds.forEach(pageantId => {
      window.Echo.private(`organizer.pageant.${pageantId}`)
        .listen('.activity.created', (event) => {
          console.log('New activity received:', event)
          
          // Add the new activity to the top of the list
          activities.value.unshift({
            id: event.id,
            pageant_id: event.pageant_id,
            pageant_name: event.pageant_name || 'Unknown Pageant',
            user_name: event.user_name,
            user_role: event.user_role,
            action_type: event.action_type,
            description: event.description,
            icon: event.icon,
            entity_type: event.entity_type,
            entity_id: event.entity_id,
            metadata: event.metadata,
            created_at: event.created_at,
            formatted_time: event.formatted_time,
          })
          
          // Keep only the latest 15 activities
          if (activities.value.length > 15) {
            activities.value = activities.value.slice(0, 15)
          }
        })
    })
  }
})

// Clean up Echo listeners when component unmounts
onUnmounted(() => {
  if (window.Echo && props.pageantIds && props.pageantIds.length > 0) {
    props.pageantIds.forEach(pageantId => {
      window.Echo.leave(`organizer.pageant.${pageantId}`)
    })
  }
})
</script>

<style scoped>
/* Pulse animation for loading states */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, transform, box-shadow;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Enhanced hover effects */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.group:hover .group-hover\:translate-x-1 {
  transform: translateX(0.25rem);
}

/* Card hover effects */
.hover\:-translate-y-1:hover {
  transform: translateY(-0.25rem);
}

.hover\:scale-105:hover {
  transform: scale(1.05);
}

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