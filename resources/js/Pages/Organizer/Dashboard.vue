<template>
  <div class="space-y-4 sm:space-y-6">
    <!-- Page Header and Quick Links -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-md overflow-hidden">
      <div class="p-4 sm:p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
          <div class="text-white">
            <h1 class="text-2xl sm:text-3xl font-bold">Organizer Dashboard</h1>
            <p class="mt-1 text-sm sm:text-base opacity-90">Manage your pageants and track progress</p>
          </div>
          <div class="flex flex-wrap gap-2 sm:space-x-3">
            <div class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white flex items-center shadow-sm">
              <Calendar class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-white" />
              <span>Total: {{ totalPageants }}</span>
            </div>
            <div class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white flex items-center shadow-sm">
              <Activity class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-white" />
              <span>Active: {{ pageantCounts?.active || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Quick Links Bar -->
      <div class="bg-orange-700/30 backdrop-blur-sm px-3 sm:px-6 py-3 sm:py-4">
        <div class="flex flex-wrap gap-2 sm:gap-3">
          <Link :href="route('organizer.pageants.create')" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white text-orange-700 hover:bg-orange-50 transition-all shadow-sm text-xs sm:text-sm">
            <Plus class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-orange-600" />
            <span class="font-medium">Create Pageant</span>
          </Link>
          
          <Link :href="route('organizer.my-pageants')" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition-all shadow-sm text-xs sm:text-sm">
            <Crown class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            <span class="font-medium">My Pageants</span>
          </Link>
          
          <Link :href="route('organizer.contestants')" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition-all shadow-sm text-xs sm:text-sm">
            <UserPlus class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            <span class="font-medium">Contestants</span>
          </Link>
          
          <Link :href="route('organizer.criteria')" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition-all shadow-sm text-xs sm:text-sm">
            <ListChecks class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            <span class="font-medium">Criteria</span>
          </Link>
          
          <Link :href="route('organizer.scoring')" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition-all shadow-sm text-xs sm:text-sm">
            <BarChart2 class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            <span class="font-medium">Scoring</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div
        v-for="(status, index) in statusCards"
        :key="index"
        :class="[status.color, 'rounded-lg shadow-sm p-4 flex flex-col items-center justify-center text-center border border-gray-100']"
      >
        <template v-if="isLoading">
          <div class="flex flex-col items-center space-y-2">
            <div class="h-10 w-10 bg-white bg-opacity-20 rounded-full shimmer"></div>
            <div class="h-5 w-20 bg-white bg-opacity-20 rounded shimmer"></div>
            <div class="h-8 w-12 bg-white bg-opacity-20 rounded shimmer"></div>
          </div>
        </template>
        <template v-else>
          <component :is="status.icon" class="h-6 w-6 text-gray-600 mb-3" />
          <h3 :class="['text-sm font-medium', status.textColor]">{{ status.title }}</h3>
          <p class="text-xl font-bold mt-1 text-gray-900">{{ getStatusCount(status.key) }}</p>
        </template>
      </div>
    </div>

    <!-- Quick Actions and Recent Pageants Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Recent Pageants -->
      <div class="md:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <Calendar class="h-5 w-5 mr-2 text-orange-500" />
            Recent Pageants
          </h2>
        </div>
        
        <div v-if="isLoading" class="p-6 space-y-4">
          <div v-for="i in 5" :key="i" class="border-b pb-4 last:border-b-0 last:pb-0">
            <div class="h-5 w-2/3 bg-gray-200 rounded shimmer shimmer-orange mb-2"></div>
            <div class="h-4 w-1/2 bg-gray-200 rounded shimmer shimmer-orange"></div>
          </div>
        </div>
        
        <div v-else-if="recentPageants.length === 0" class="p-6 text-center py-12">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
            <Calendar class="h-6 w-6 text-gray-500" />
          </div>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No pageants found</h3>
          <p class="mt-1 text-sm text-gray-500">
            You haven't been assigned to any pageants yet.
          </p>
        </div>
        
        <div v-else class="divide-y divide-gray-100">
          <div v-for="(pageant, index) in recentPageants" :key="index" 
               class="p-4 hover:bg-gray-50 transition-colors cursor-pointer"
               @click="viewPageant(pageant)">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-base font-medium text-gray-900">
                  {{ pageant.name }}
                </h3>
                <div class="flex items-center mt-1 text-sm text-gray-500">
                  <Calendar class="h-4 w-4 mr-1 text-gray-400" />
                  <span>{{ formatDateRange(pageant) || 'Date not set' }}</span>
                  <span class="mx-2">•</span>
                  <MapPin class="h-4 w-4 mr-1 text-gray-400" />
                  <span>{{ pageant.venue || pageant.location || 'Location not specified' }}</span>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <Tooltip :text="getPageantStatusTooltip(pageant.status)" position="left">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium hover:shadow-md transition-shadow cursor-help"
                        :class="getStatusClass(pageant.status)">
                    {{ pageant.status }}
                  </span>
                </Tooltip>
                <ChevronRight class="h-5 w-5 text-gray-400" />
              </div>
            </div>
          </div>
        </div>
        
        <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 rounded-b-lg">
          <Tooltip text="Go to pageants management page" position="top">
            <Link :href="route('organizer.my-pageants')" class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center transition-all transform hover:translate-x-1">
              View all pageants <ChevronRight class="h-4 w-4 ml-1" />
            </Link>
          </Tooltip>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <Zap class="h-5 w-5 mr-2 text-orange-500" />
            Quick Actions
          </h2>
        </div>
        
        <div class="p-4 space-y-2">
          <Tooltip text="View and manage all your assigned pageants" position="right">
            <Link :href="route('organizer.my-pageants')" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition-all border border-gray-100 transform hover:-translate-y-0.5 hover:shadow-md">
              <Crown class="h-5 w-5 text-orange-500 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-900">My Pageants</div>
                <div class="text-xs text-gray-500">View all pageants assigned to you</div>
              </div>
            </Link>
          </Tooltip>
          
          <Tooltip text="Add and manage contestants for your pageants" position="right">
            <Link :href="route('organizer.contestants')" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition-all border border-gray-100 transform hover:-translate-y-0.5 hover:shadow-md">
              <UserPlus class="h-5 w-5 text-orange-500 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-900">Manage Contestants</div>
                <div class="text-xs text-gray-500">Register new pageant participants</div>
              </div>
            </Link>
          </Tooltip>
          
          <Tooltip text="Set up scoring criteria and weight distributions" position="right">
            <Link :href="route('organizer.criteria')" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition-all border border-gray-100 transform hover:-translate-y-0.5 hover:shadow-md">
              <ListChecks class="h-5 w-5 text-orange-500 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-900">Configure Criteria</div>
                <div class="text-xs text-gray-500">Set up judging criteria and weights</div>
              </div>
            </Link>
          </Tooltip>
          
          <Tooltip text="Choose and configure your pageant's scoring methodology" position="right">
            <Link :href="route('organizer.scoring')" class="flex items-center p-3 rounded-md hover:bg-gray-50 transition-all border border-gray-100 transform hover:-translate-y-0.5 hover:shadow-md">
              <BarChart2 class="h-5 w-5 text-orange-500 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-900">Scoring System</div>
                <div class="text-xs text-gray-500">Configure scoring methodology</div>
              </div>
            </Link>
          </Tooltip>
        </div>
      </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-100">
      <div class="p-4 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
          <Calendar class="h-5 w-5 mr-2 text-orange-500" />
          Recent Activity
        </h2>
      </div>
      
      <div class="p-6">
        <div v-if="isLoading" class="space-y-6">
          <div v-for="i in 3" :key="i" class="flex">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 shimmer shimmer-orange"></div>
            <div class="ml-4 flex-1">
              <div class="h-5 w-3/4 bg-gray-200 rounded shimmer shimmer-orange mb-2"></div>
              <div class="h-4 w-1/2 bg-gray-200 rounded shimmer shimmer-orange"></div>
            </div>
          </div>
        </div>
        
        <div v-else class="relative">
          <div class="flex items-center justify-center py-12 text-center">
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-50 mb-4">
                <Crown class="h-8 w-8 text-orange-400" />
              </div>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Welcome to Your Dashboard</h3>
              <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                Manage your pageants, contestants, and judging criteria from here. Create your first pageant to get started.
              </p>
              <div class="mt-4">
                <Link :href="route('organizer.pageants.create')" class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-800 border border-orange-200 rounded-md text-sm font-medium hover:bg-orange-200 transition-colors">
                  <Plus class="h-4 w-4 mr-2" />
                  Create Pageant
                </Link>
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
import { ref, onMounted, computed } from 'vue'
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

})

// State
const isLoading = ref(true)
const isSettingsModalVisible = ref(false)

// Status cards data
const statusCards = [
  { 
    title: 'Pending', 
    key: 'pending_approval',
    icon: Clock, 
    color: 'bg-white', 
    textColor: 'text-gray-600' 
  },
  { 
    title: 'Draft', 
    key: 'draft',
    icon: Edit, 
    color: 'bg-white', 
    textColor: 'text-gray-600' 
  },
  { 
    title: 'Setup', 
    key: 'setup',
    icon: Settings, 
    color: 'bg-white', 
    textColor: 'text-gray-600' 
  },
  { 
    title: 'Active', 
    key: 'active',
    icon: Activity, 
    color: 'bg-white', 
    textColor: 'text-gray-600' 
  },
  { 
    title: 'Completed', 
    key: 'completed',
    icon: CheckCircle, 
    color: 'bg-white', 
    textColor: 'text-gray-600' 
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

// Status styling
const getStatusClass = (status) => {
  const statusMap = {
    'Pending_Approval': 'bg-orange-100 text-orange-800',
    'Draft': 'bg-gray-100 text-gray-800',
    'Setup': 'bg-blue-100 text-blue-800',
    'Active': 'bg-green-100 text-green-800',
    'Completed': 'bg-purple-100 text-purple-800',
    'Unlocked_For_Edit': 'bg-yellow-100 text-yellow-800',
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
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

onMounted(() => {
  // Simulate loading time
  setTimeout(() => {
    isLoading.value = false
  }, 1000)
})
</script>

<style>
/* This is already defined in the parent component's CSS */
</style>