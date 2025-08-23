<template>
  <div class="space-y-6">
    <!-- Greeting and Quick Stats -->
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-gray-900">Welcome to Your Dashboard</h1>
          <p class="mt-1 text-sm text-gray-500">Manage your pageants and track event progress</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-4">
          <div class="px-4 py-2 bg-gray-50 border border-gray-100 rounded-md flex items-center">
            <Calendar class="h-5 w-5 text-orange-500" />
            <div class="ml-3">
              <span class="text-xs font-medium text-gray-500">Total Pageants</span>
              <p class="text-lg font-bold text-gray-900">{{ totalPageants }}</p>
            </div>
          </div>
          <div class="px-4 py-2 bg-gray-50 border border-gray-100 rounded-md flex items-center">
            <Activity class="h-5 w-5 text-green-500" />
            <div class="ml-3">
              <span class="text-xs font-medium text-gray-500">Active</span>
              <p class="text-lg font-bold text-gray-900">{{ pageantCounts?.active || 0 }}</p>
            </div>
          </div>
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
          <Clock class="h-5 w-5 mr-2 text-orange-500" />
          Upcoming Events
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
          <!-- Timeline events -->
          <div class="relative space-y-8">
            <div v-if="upcomingEvents && upcomingEvents.length === 0" class="flex items-center justify-center py-12 text-center">
              <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-50 mb-4">
                  <Calendar class="h-8 w-8 text-orange-400" />
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming events</h3>
                <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                  Add events to your pageant timelines to keep track of important dates and milestones. Events will appear here based on their start dates.
                </p>
                <div class="mt-4">
                  <Link :href="route('organizer.my-pageants')" class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-800 border border-orange-200 rounded-md text-sm font-medium hover:bg-orange-200 transition-colors">
                    <Clock class="h-4 w-4 mr-2" />
                    Manage Pageant Events
                  </Link>
                </div>
              </div>
            </div>
            
            <div v-for="(event, index) in upcomingEvents" :key="index" 
                 class="relative pl-10 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors"
                 @click="viewEventPageant(event)">
              <!-- Timeline dot -->
              <div class="absolute left-0 top-1.5 w-4 h-4 rounded-full border border-orange-500 bg-white"></div>
              
              <!-- Event content -->
              <div>
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ event.name }}</h3>
                    <p class="text-xs font-medium text-orange-600">
                      <span>{{ event.pageant_name }}</span>
                    </p>
                    <p class="text-sm text-gray-500">{{ event.description || 'No description provided' }}</p>
                    <div class="mt-1 flex items-center text-xs text-gray-500">
                      <MapPin class="h-3.5 w-3.5 mr-1 text-gray-400" />
                      <span>{{ event.venue || event.location || 'Location not specified' }}</span>
                    </div>
                  </div>
                  <span class="text-xs font-medium rounded-full px-2.5 py-0.5 bg-gray-100 text-gray-800">
                    {{ event.start_datetime }}
                  </span>
                </div>
                
                <div v-if="event.is_milestone" class="mt-2 inline-flex items-center text-xs font-medium text-orange-600">
                  <Star class="h-3.5 w-3.5 mr-1" />
                  <span>Milestone Event</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="upcomingEvents && upcomingEvents.length > 0" class="border-t border-gray-100 bg-gray-50 px-4 py-3 rounded-b-lg">
        <Tooltip text="View complete timeline of all pageant events" position="top">
          <Link :href="route('organizer.timeline')" class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center transition-all transform hover:translate-x-1">
            View all events <ChevronRight class="h-4 w-4 ml-1" />
          </Link>
        </Tooltip>
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
  Unlock
} from 'lucide-vue-next'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageantCounts: Object,
  recentPageants: Array,
  totalPageants: Number,
  upcomingEvents: Array
})

// State
const isLoading = ref(true)
const isSettingsModalVisible = ref(false)

// Status cards data
const statusCards = [
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
  },
  { 
    title: 'Unlocked', 
    key: 'unlocked_for_edit',
    icon: Unlock, 
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

// Navigate to event's pageant
const viewEventPageant = (event) => {
  router.visit(route('organizer.pageant.view', { id: event.pageant_id }))
}

// Get tooltip text for pageant status badges
const getPageantStatusTooltip = (status) => {
  switch (status) {
    case 'Draft':
      return 'In planning phase - add contestants, criteria, and events'
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