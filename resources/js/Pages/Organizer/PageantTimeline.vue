<template>
  <OrganizerLayout>
    <Head :title="`${pageant.name} - Timeline`" />
    
    <!-- Pageant Header -->
    <div class="relative rounded-xl overflow-hidden mb-8 shadow-md">
      <div 
        class="h-64 relative overflow-hidden bg-gradient-to-r from-orange-600 to-orange-400"
        :class="{ 'bg-gradient-to-r from-orange-600 to-orange-400': !pageant.cover_image }"
      >
        <img 
          v-if="pageant.cover_image" 
          :src="pageant.cover_image" 
          class="w-full h-full object-cover opacity-70"
          alt="Pageant cover" 
        />
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex flex-col justify-end p-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-4xl font-bold text-white drop-shadow-sm mb-2">{{ pageant.name }}</h1>
              <p class="text-orange-100 flex items-center">
                <Calendar class="w-5 h-5 mr-2" />
                <span v-if="pageant.start_date">{{ pageant.start_date }}</span>
                <span v-if="pageant.end_date && pageant.start_date !== pageant.end_date"> to {{ pageant.end_date }}</span>
              </p>
            </div>
            <div class="flex gap-2">
              <Link 
                :href="route('organizer.pageant.view', pageant.id)" 
                class="inline-flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none backdrop-blur-sm"
              >
                <Eye class="w-4 h-4 mr-2" />
                View Details
              </Link>
              <Link 
                :href="route('organizer.timeline')" 
                class="inline-flex items-center px-4 py-2.5 rounded-md text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none backdrop-blur-sm"
              >
                <ChevronLeft class="w-4 h-4 mr-2" />
                Back to Pageants
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Pageant Stats -->
    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-8">
      <div class="flex flex-wrap gap-8">
        <div class="flex flex-col">
          <span class="text-xs text-gray-500 uppercase font-semibold">Total Events</span>
          <span class="text-2xl font-bold text-gray-900">0</span>
        </div>
        
        <div class="flex flex-col">
          <span class="text-xs text-gray-500 uppercase font-semibold">Completed</span>
          <span class="text-2xl font-bold text-gray-900">0</span>
        </div>
        
        <div class="flex flex-col">
          <span class="text-xs text-gray-500 uppercase font-semibold">Progress</span>
          <span class="text-2xl font-bold text-gray-900">{{ pageant.progress || 0 }}%</span>
        </div>
        
        <div class="ml-auto">
          <Link 
            :href="route('organizer.pageant.view', {id: pageant.id})" 
            class="inline-flex items-center px-4 py-2 border border-orange-600 rounded-md text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-sm"
          >
            <Plus class="w-4 h-4 mr-2" />
            Manage Events
          </Link>
        </div>
      </div>
      
      <!-- Progress Bar -->
      <div class="mt-6">
        <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
          <div 
            class="h-full rounded-full bg-gradient-to-r from-orange-500 to-orange-400"
            :class="{
              'from-gray-400 to-gray-300': pageant.progress === 0,
              'from-green-500 to-green-400': pageant.progress === 100
            }"
            :style="{ width: `${pageant.progress || 0}%` }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Timeline Controls -->
    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <h2 class="text-xl font-bold text-gray-900">Our Pageant Milestones</h2>
        <div class="ml-auto flex items-center gap-3">
          <div class="flex space-x-2">
            <div class="min-w-[140px]">
              <CustomSelect
                v-model="timelineStatusFilter"
                :options="statusFilterOptions"
                variant="orange"
              />
            </div>
            
            <div class="min-w-[130px]">
              <CustomSelect
                v-model="milestoneFilter"
                :options="milestoneFilterOptions"
                variant="orange"
              />
            </div>
          </div>
          
          <div class="flex items-center">
            <span class="text-sm text-gray-500">{{ filteredEvents.length }} event{{ filteredEvents.length !== 1 ? 's' : '' }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Events functionality has been removed -->
    <div class="bg-white p-12 text-center rounded-xl shadow-md border border-gray-100">
      <div class="p-4 bg-gray-50 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
        <Calendar class="h-10 w-10 text-gray-400" />
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Events functionality removed</h3>
      <p class="text-gray-500 mb-6">Event scheduling has been removed from the system. Use the main pageant view to manage your pageant.</p>
      <Link 
        :href="route('organizer.pageant.view', {id: pageant.id})"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
      >
        View Pageant Details
      </Link>
    </div>
  </OrganizerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import CustomSelect from '@/Components/CustomSelect.vue'
import { 
  Calendar, 
  MapPin, 
  Clock, 
  ChevronLeft,
  Star, 
  Trophy,
  MicVocal, 
  PartyPopper,
  Ticket,
  AlertCircle,
  Eye,
  Plus,
  X,
  Filter,
  Check,
  User2,
  ArrowRight
} from 'lucide-vue-next'

// Props
const props = defineProps({
  pageant: {
    type: Object,
    required: true
  }
})

// State
const timelineStatusFilter = ref('all')
const milestoneFilter = ref('all')

// Options for selects
const statusFilterOptions = [
  { value: 'all', label: 'All Statuses' },
  { value: 'Pending', label: 'Pending' },
  { value: 'In Progress', label: 'In Progress' },
  { value: 'Completed', label: 'Completed' },
  { value: 'Cancelled', label: 'Cancelled' }
]

const milestoneFilterOptions = [
  { value: 'all', label: 'All Events' },
  { value: 'true', label: 'Milestones Only' }
]

// Helper methods for date formatting
const getDateNumber = (dateStr) => {
  if (!dateStr) return '01'
  try {
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return '01'
    return date.getDate().toString().padStart(2, '0')
  } catch (e) {
    return '01'
  }
}

const getDateMonth = (dateStr) => {
  if (!dateStr) return 'Jan'
  try {
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return 'Jan'
    return date.toLocaleString('en-US', { month: 'short' })
  } catch (e) {
    return 'Jan'
  }
}

const getDateYear = (dateStr) => {
  if (!dateStr) return '2025'
  try {
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return '2025'
    return date.getFullYear().toString()
  } catch (e) {
    return '2025'
  }
}

// Filtered events for selected pageant
const filteredEvents = computed(() => {
  // Events functionality has been removed
  return []
})

// Methods
const clearEventFilters = () => {
  timelineStatusFilter.value = 'all'
  milestoneFilter.value = 'all'
}

const getEventStatusColor = (status) => {
  switch (status) {
    case 'Completed': return 'bg-green-500 border-green-100'
    case 'In Progress': return 'bg-blue-500 border-blue-100'
    case 'Cancelled': return 'bg-red-500 border-red-100'
    case 'Pending': 
    default: return 'bg-gray-500 border-gray-100'
  }
}

const getEventStatusTextColor = (status) => {
  switch (status) {
    case 'Completed': return 'bg-green-100 text-green-800'
    case 'In Progress': return 'bg-blue-100 text-blue-800'
    case 'Cancelled': return 'bg-red-100 text-red-800'
    case 'Pending': 
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getEventIcon = (type) => {
  switch (type) {
    case 'Preliminary': return Star
    case 'Interview': return MicVocal
    case 'Performance': return PartyPopper
    case 'Coronation': return Trophy 
    case 'Rehearsal': return User2
    case 'Registration': return Ticket
    default: return Calendar
  }
}

const formatTimeOnly = (dateStr) => {
  if (!dateStr) return ''
  
  try {
    // Try to extract time part from "Mar 30, 2025 09:30 PM" format
    const timeParts = dateStr.split(' ')
    if (timeParts.length >= 2) {
      const lastTwo = timeParts.slice(-2)
      return lastTwo.join(' ')
    }
    
    // Fallback: try to parse date and format time
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return ''
    
    return date.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    })
  } catch (e) {
    console.error('Error formatting time:', e)
    return ''
  }
}
</script>

<style scoped>
/* Timeline dot styles */
.timeline-dot::before {
  content: '';
  position: absolute;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: white;
  border: 4px solid;
  border-color: inherit;
  opacity: 0.3;
  transform: scale(0);
  animation: pulse 2s infinite;
  z-index: -1;
}

@keyframes pulse {
  0% {
    transform: scale(0.95);
    opacity: 0.5;
  }
  70% {
    transform: scale(1.1);
    opacity: 0.2;
  }
  100% {
    transform: scale(0.95);
    opacity: 0.5;
  }
}

/* Animation for hover effect */
.timeline-dot {
  transition: all 0.3s ease-out;
}

.timeline-dot:hover {
  transform: scale(1.2);
  box-shadow: 0 0 15px rgba(249, 115, 22, 0.4);
}

/* Adding media query for mobile responsive design */
@media (max-width: 768px) {
  /* Make timeline layout vertical on mobile */
  .timeline-dot {
    left: 0 !important;
    transform: translateX(0) !important;
  }
  
  .timeline-item {
    flex-direction: column !important;
    align-items: flex-start !important;
  }
  
  .timeline-date, 
  .timeline-content {
    width: 100% !important;
    text-align: left !important;
    margin-left: 2rem !important;
  }
  
  .timeline-date {
    margin-bottom: 0.5rem !important;
  }
}
</style> 