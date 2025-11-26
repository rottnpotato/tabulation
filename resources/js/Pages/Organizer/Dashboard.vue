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
              <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                Organizer Dashboard
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                <Crown class="w-5 h-5 text-teal-500" />
                Manage pageants, contestants, and track progress
              </p>
            </div>
            
            <div class="flex items-center bg-white/60 backdrop-blur-md rounded-2xl p-4 border border-teal-100 shadow-sm">
              <div class="text-teal-600 mr-3">
                <div class="p-2 bg-teal-50 rounded-lg">
                  <Calendar class="w-5 h-5" />
                </div>
              </div>
              <div>
                <div class="text-xs font-bold text-teal-500 uppercase tracking-wider mb-0.5">Today</div>
                <div class="text-lg font-bold text-slate-900 leading-none">{{ new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4 group hover:shadow-md transition-all">
          <div class="p-3 bg-teal-50 text-teal-600 rounded-xl group-hover:scale-110 transition-transform">
            <Crown class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Total Pageants</p>
            <p class="text-2xl font-bold text-slate-900">{{ totalPageants }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4 group hover:shadow-md transition-all">
          <div class="p-3 bg-teal-50 text-teal-600 rounded-xl group-hover:scale-110 transition-transform">
            <Activity class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Active Now</p>
            <p class="text-2xl font-bold text-slate-900">{{ pageantCounts?.active || 0 }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4 group hover:shadow-md transition-all">
          <div class="p-3 bg-teal-50 text-teal-600 rounded-xl group-hover:scale-110 transition-transform">
            <CheckCircle class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Completed</p>
            <p class="text-2xl font-bold text-slate-900">{{ pageantCounts?.completed || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-fade-in relative z-20">
        <Link :href="route('organizer.pageants.create')" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative z-30 group hover:shadow-md transition-all overflow-hidden">
          <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
          <div class="relative">
            <div class="p-2 bg-teal-50 w-fit rounded-xl mb-4 text-teal-600 group-hover:scale-110 transition-transform origin-left">
              <Plus class="h-6 w-6" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Create Pageant</h3>
            <p class="text-sm text-slate-500">Start a new event</p>
          </div>
        </Link>

        <Link :href="route('organizer.my-pageants')" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative z-30 group hover:shadow-md transition-all overflow-hidden">
          <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
          <div class="relative">
            <div class="p-2 bg-teal-50 w-fit rounded-xl mb-4 text-teal-600 group-hover:scale-110 transition-transform origin-left">
              <Crown class="h-6 w-6" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">My Pageants</h3>
            <p class="text-sm text-slate-500">View all events</p>
          </div>
        </Link>

        <Link :href="route('organizer.contestants')" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative z-30 group hover:shadow-md transition-all overflow-hidden">
          <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
          <div class="relative">
            <div class="p-2 bg-teal-50 w-fit rounded-xl mb-4 text-teal-600 group-hover:scale-110 transition-transform origin-left">
              <UserPlus class="h-6 w-6" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Contestants</h3>
            <p class="text-sm text-slate-500">Manage participants</p>
          </div>
        </Link>

        <Link :href="route('organizer.criteria')" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative z-30 group hover:shadow-md transition-all overflow-hidden">
          <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
          <div class="relative">
            <div class="p-2 bg-teal-50 w-fit rounded-xl mb-4 text-teal-600 group-hover:scale-110 transition-transform origin-left">
              <ListChecks class="h-6 w-6" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Criteria</h3>
            <p class="text-sm text-slate-500">Setup scoring rules</p>
          </div>
        </Link>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Pageants -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden animate-fade-in h-fit">
          <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Recent Pageants</h2>
              <p class="text-sm text-slate-500">Your latest events</p>
            </div>
            <Link :href="route('organizer.my-pageants')" class="text-sm font-medium text-teal-600 hover:text-teal-700 flex items-center gap-1 transition-colors">
              View All <ChevronRight class="w-4 h-4" />
            </Link>
          </div>
          
          <div class="divide-y divide-slate-100">
            <div v-if="isLoading" class="p-6 space-y-4">
              <div v-for="i in 3" :key="i" class="flex items-center gap-4">
                <div class="h-12 w-12 bg-slate-100 rounded-xl animate-pulse"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-4 w-2/3 bg-slate-100 rounded animate-pulse"></div>
                  <div class="h-3 w-1/2 bg-slate-100 rounded animate-pulse"></div>
                </div>
              </div>
            </div>
            
            <div v-else-if="recentPageants.length === 0" class="p-8 text-center">
              <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-teal-50 mb-4">
                <Calendar class="h-8 w-8 text-teal-600" />
              </div>
              <h3 class="text-lg font-bold text-slate-900">No pageants found</h3>
              <p class="text-sm text-slate-500 mt-1 mb-4">Create your first pageant to get started!</p>
              <Link :href="route('organizer.pageants.create')" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-xl hover:bg-teal-700 transition-colors shadow-lg shadow-teal-200">
                <Plus class="h-4 w-4 mr-2" />
                Create Pageant
              </Link>
            </div>
            
            <div v-else v-for="(pageant, index) in recentPageants" :key="index" 
                 class="group p-5 hover:bg-slate-50/80 transition-all cursor-pointer"
                 @click="viewPageant(pageant)">
              <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                  <div class="p-3 bg-teal-50 rounded-xl group-hover:scale-110 transition-transform">
                    <Crown class="h-6 w-6 text-teal-600" />
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-slate-900 group-hover:text-teal-700 transition-colors">
                      {{ pageant.name }}
                    </h3>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-500">
                      <div class="flex items-center">
                        <Calendar class="h-3.5 w-3.5 mr-1.5" />
                        <span>{{ formatDateRange(pageant) || 'Date not set' }}</span>
                      </div>
                      <div class="flex items-center">
                        <MapPin class="h-3.5 w-3.5 mr-1.5" />
                        <span class="truncate max-w-[150px]">{{ pageant.venue || pageant.location || 'Location TBA' }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border"
                        :class="getStatusClass(pageant.status)">
                    {{ formatStatusText(pageant.status) }}
                  </span>
                  <ChevronRight class="h-5 w-5 text-slate-300 group-hover:text-teal-500 group-hover:translate-x-1 transition-all" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden animate-fade-in h-fit">
          <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Recent Activity</h2>
              <p class="text-sm text-slate-500">Latest updates</p>
            </div>
          </div>
          
          <div class="divide-y divide-slate-100">
            <div v-if="isLoading" class="p-6 space-y-4">
              <div v-for="i in 3" :key="i" class="flex items-center gap-4">
                <div class="h-10 w-10 bg-slate-100 rounded-full animate-pulse"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-4 w-3/4 bg-slate-100 rounded animate-pulse"></div>
                  <div class="h-3 w-1/2 bg-slate-100 rounded animate-pulse"></div>
                </div>
              </div>
            </div>
            
            <div v-else-if="activities.length === 0" class="p-8 text-center">
              <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
                <Activity class="h-8 w-8 text-slate-400" />
              </div>
              <h3 class="text-lg font-bold text-slate-900">No recent activity</h3>
              <p class="text-sm text-slate-500 mt-1">Activities will appear here as they happen.</p>
            </div>
            
            <div v-else v-for="activity in activities" :key="activity.id"
                 class="group p-5 hover:bg-slate-50/80 transition-all">
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                  <div class="p-2 rounded-xl bg-gradient-to-br shadow-sm group-hover:scale-110 transition-transform"
                       :class="getActivityIconClass(activity.action_type)">
                    <component :is="getActivityIcon(activity.action_type)" class="h-4 w-4 text-white" />
                  </div>
                </div>
                
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-slate-900">
                    {{ activity.description }}
                  </p>
                  <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
                    <span class="font-medium text-teal-600">{{ activity.pageant_name }}</span>
                    <span class="text-slate-300">•</span>
                    <span>{{ activity.formatted_time }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium">
                      {{ activity.user_name }}
                    </span>
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
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
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

// Format date range function
const formatDateRange = (pageant) => {
  if (pageant.start_date && pageant.end_date) {
    let range = `${pageant.start_date}`
    if (pageant.start_time) range += ` @ ${pageant.start_time}`
    range += ` - ${pageant.end_date}`
    if (pageant.end_time) range += ` @ ${pageant.end_time}`
    return range
  } else if (pageant.start_date) {
    let start = `Starts: ${pageant.start_date}`
    if (pageant.start_time) start += ` @ ${pageant.start_time}`
    return start
  } else if (pageant.end_date) {
    let end = `Ends: ${pageant.end_date}`
    if (pageant.end_time) end += ` @ ${pageant.end_time}`
    return end
  }
  return ''
}

// Status styling
const getStatusClass = (status) => {
  const statusMap = {
    'Pending_Approval': 'bg-amber-50 text-amber-700 border-amber-200',
    'Draft': 'bg-slate-100 text-slate-600 border-slate-200',
    'Setup': 'bg-blue-50 text-blue-700 border-blue-200',
    'Active': 'bg-teal-50 text-teal-700 border-teal-200',
    'Completed': 'bg-purple-50 text-purple-700 border-purple-200',
    'Unlocked_For_Edit': 'bg-rose-50 text-rose-700 border-rose-200',
  }
  return statusMap[status] || 'bg-slate-100 text-slate-600 border-slate-200'
}

// Format status text
const formatStatusText = (status) => {
  return status.replace(/_/g, ' ')
}

// Navigate to pageant details
const viewPageant = (pageant) => {
  router.visit(route('organizer.pageant.view', { id: pageant.id }))
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
    'SCORE_SUBMITTED': 'from-amber-400 to-amber-600',
    'SCORE_UPDATED': 'from-amber-400 to-amber-600',
    'CONTESTANT_ADDED': 'from-blue-400 to-blue-600',
    'CONTESTANT_UPDATED': 'from-blue-400 to-blue-600',
    'CONTESTANT_REMOVED': 'from-red-400 to-red-600',
    'JUDGE_ASSIGNED': 'from-indigo-400 to-indigo-600',
    'JUDGE_REMOVED': 'from-red-400 to-red-600',
    'TABULATOR_ASSIGNED': 'from-purple-400 to-purple-600',
    'TABULATOR_REMOVED': 'from-red-400 to-red-600',
    'ROUND_STARTED': 'from-teal-400 to-teal-600',
    'ROUND_COMPLETED': 'from-teal-400 to-teal-600',
    'CRITERIA_CREATED': 'from-cyan-400 to-cyan-600',
    'CRITERIA_UPDATED': 'from-cyan-400 to-cyan-600',
    'PAGEANT_UPDATED': 'from-emerald-400 to-emerald-600',
    'STATUS_CHANGED': 'from-pink-400 to-pink-600',
  }
  return classMap[actionType] || 'from-slate-400 to-slate-600'
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
          
          if (activities.value.length > 15) {
            activities.value = activities.value.slice(0, 15)
          }
        })
    })
  }
})

onUnmounted(() => {
  if (window.Echo && props.pageantIds && props.pageantIds.length > 0) {
    props.pageantIds.forEach(pageantId => {
      window.Echo.leave(`organizer.pageant.${pageantId}`)
    })
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