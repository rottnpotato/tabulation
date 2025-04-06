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
          <span class="text-2xl font-bold text-gray-900">{{ pageant.events_count || 0 }}</span>
        </div>
        
        <div class="flex flex-col">
          <span class="text-xs text-gray-500 uppercase font-semibold">Completed</span>
          <span class="text-2xl font-bold text-gray-900">{{ pageant.completed_events_count || 0 }}</span>
        </div>
        
        <div class="flex flex-col">
          <span class="text-xs text-gray-500 uppercase font-semibold">Progress</span>
          <span class="text-2xl font-bold text-gray-900">{{ pageant.progress || 0 }}%</span>
        </div>
        
        <div class="ml-auto">
          <Link 
            :href="route('organizer.pageant.view', {id: pageant.id, tab: 'events'})" 
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
            <select 
              v-model="timelineStatusFilter"
              class="px-3 py-1.5 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500 bg-white text-sm shadow-sm"
            >
              <option value="all">All Statuses</option>
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            
            <select 
              v-model="milestoneFilter"
              class="px-3 py-1.5 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500 bg-white text-sm shadow-sm"
            >
              <option value="all">All Events</option>
              <option value="true">Milestones Only</option>
            </select>
          </div>
          
          <div class="flex items-center">
            <span class="text-sm text-gray-500">{{ filteredEvents.length }} event{{ filteredEvents.length !== 1 ? 's' : '' }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- No events message -->
    <div v-if="!pageant.events || pageant.events.length === 0" class="bg-white p-12 text-center rounded-xl shadow-md border border-gray-100">
      <div class="p-4 bg-gray-50 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
        <Calendar class="h-10 w-10 text-gray-400" />
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">No events found</h3>
      <p class="text-gray-500 mb-6">There are no events scheduled for this pageant yet.</p>
      <Link 
        :href="route('organizer.pageant.view', {id: pageant.id, tab: 'events'})"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
      >
        <Plus class="w-4 h-4 mr-2" />
        Add First Event
      </Link>
    </div>
    
    <!-- No matching events message -->
    <div v-else-if="filteredEvents.length === 0" class="bg-white p-12 text-center rounded-xl shadow-md border border-gray-100">
      <div class="p-4 bg-gray-50 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
        <Filter class="h-10 w-10 text-gray-400" />
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">No matching events</h3>
      <p class="text-gray-500 mb-6">Try adjusting your filters to see more events.</p>
      <button 
        @click="clearEventFilters" 
        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
      >
        <X class="w-4 h-4 mr-2" />
        Clear Filters
      </button>
    </div>
    
    <!-- Timeline -->
    <div v-else class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
      <p class="text-center text-gray-600 mb-12">WOW..!! WHAT A JOURNEY SO FAR...!!!</p>
      
      <!-- Timeline container -->
      <div class="relative max-w-4xl mx-auto">
        <!-- Vertical line -->
        <div class="absolute top-0 bottom-0 left-1/2 w-1 bg-gradient-to-b from-orange-200 via-orange-400 to-orange-200 transform -translate-x-1/2"></div>
        
        <!-- Timeline items -->
        <div v-for="(event, index) in filteredEvents" :key="event.id" class="relative mb-24 last:mb-2">
          <!-- Timeline item - alternating left and right -->
          <div class="flex items-center" :class="index % 2 === 0 ? 'flex-row' : 'flex-row-reverse'">
            <!-- Date indicator -->
            <div :class="[
              'w-1/2 px-4 text-center', 
              index % 2 === 0 ? 'text-right' : 'text-left'
            ]">
              <div :class="[
                'inline-block rounded-lg py-3 px-4 shadow-md', 
                event.is_milestone ? 'bg-orange-100 text-orange-800 border border-orange-200' : 'bg-white border border-gray-200'
              ]">
                <div class="font-bold text-2xl">{{ getDateNumber(event.start_datetime) }}</div>
                <div class="font-medium text-sm uppercase">{{ getDateMonth(event.start_datetime) }}</div>
                <div class="text-sm text-gray-500">{{ getDateYear(event.start_datetime) }}</div>
              </div>
            </div>
            
            <!-- Circle marker -->
            <div class="z-20 flex-shrink-0 w-8 h-8 rounded-full border-4 border-white shadow-lg timeline-dot relative"
              :class="getEventStatusColor(event.status)">
              <component :is="getEventIcon(event.type)" class="w-4 h-4 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white" />
            </div>
            
            <!-- Content card -->
            <div :class="[
              'w-1/2 px-4',
              index % 2 === 0 ? 'text-left' : 'text-right'
            ]">
              <div class="rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-100">
                <!-- Card gradient header -->
                <div class="relative h-24 overflow-hidden">
                  <div 
                    class="absolute inset-0 w-full h-full"
                    :class="{
                      'bg-gradient-to-r from-blue-600 to-indigo-500': event.status === 'Pending',
                      'bg-gradient-to-r from-amber-500 to-orange-500': event.status === 'In Progress',
                      'bg-gradient-to-r from-green-600 to-emerald-500': event.status === 'Completed',
                      'bg-gradient-to-r from-red-600 to-pink-500': event.status === 'Cancelled',
                      'bg-gradient-to-r from-orange-500 to-red-400': !event.status
                    }"
                  ></div>
                  
                  <!-- Card header content -->
                  <div class="absolute inset-0 p-4 bg-gradient-to-t from-black/70 to-transparent flex flex-col justify-end">
                    <div class="flex items-center justify-between">
                      <h4 class="font-bold text-lg text-white">{{ event.name || 'Untitled Event' }}</h4>
                      <div v-if="event.is_milestone" class="bg-orange-100 text-orange-800 border border-orange-200 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                        <Star class="w-3 h-3 mr-1 fill-orange-500 text-orange-500" />
                        Milestone
                      </div>
                    </div>
                    
                    <!-- Status badge -->
                    <div class="mt-2">
                      <span class="px-2 py-1 rounded-full text-xs font-medium bg-white/20 text-white">
                        {{ event.status }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <!-- Card body -->
                <div class="bg-white p-4">
                  <!-- Time info -->
                  <div class="flex items-start mb-2 text-sm text-gray-600" :class="index % 2 === 0 ? '' : 'justify-end'">
                    <Clock class="w-4 h-4 mr-2 text-orange-400 mt-0.5" :class="index % 2 === 0 ? '' : 'order-2 ml-2 mr-0'" />
                    <div :class="index % 2 === 0 ? '' : 'text-right'">
                      <div class="font-medium">{{ formatTimeOnly(event.start_datetime) }}</div>
                    </div>
                  </div>
                  
                  <!-- Location info -->
                  <div v-if="event.venue || event.location" class="flex items-start mb-2 text-sm text-gray-600" :class="index % 2 === 0 ? '' : 'justify-end'">
                    <MapPin class="w-4 h-4 mr-2 text-orange-400 mt-0.5" :class="index % 2 === 0 ? '' : 'order-2 ml-2 mr-0'" />
                    <div :class="index % 2 === 0 ? '' : 'text-right'">
                      <div class="font-medium">{{ event.venue || event.location || 'No venue specified' }}</div>
                    </div>
                  </div>
                  
                  <!-- Description -->
                  <div v-if="event.description" class="mt-3 p-3 bg-gray-50 rounded-md text-sm text-gray-700" :class="index % 2 === 0 ? 'text-left' : 'text-right'">
                    {{ event.description }}
                  </div>
                  
                  <!-- View details button -->
                  <div :class="['mt-3 flex', index % 2 === 0 ? 'justify-start' : 'justify-end']">
                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 rounded-md transition-colors cursor-pointer">
                      View Details
                      <ArrowRight class="w-3 h-3 ml-1" />
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </OrganizerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
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
  if (!props.pageant || !props.pageant.events || props.pageant.events.length === 0) {
    return []
  }
  
  let events = [...props.pageant.events]
  
  // Filter by status
  if (timelineStatusFilter.value !== 'all') {
    events = events.filter(event => event.status === timelineStatusFilter.value)
  }
  
  // Filter by milestone
  if (milestoneFilter.value !== 'all') {
    const isMilestone = milestoneFilter.value === 'true'
    events = events.filter(event => event.is_milestone === isMilestone)
  }
  
  // Sort by date and display order
  events.sort((a, b) => {
    // First compare display_order if both events have it
    if (a.display_order !== undefined && b.display_order !== undefined) {
      if (a.display_order !== b.display_order) {
        return a.display_order - b.display_order
      }
    }
    
    // If display_order is the same or not defined, sort by start_datetime
    const aDateStr = a.raw_start_datetime || a.start_datetime || ''
    const bDateStr = b.raw_start_datetime || b.start_datetime || ''
    
    // If either date is missing, handle gracefully
    if (!aDateStr && !bDateStr) return 0
    if (!aDateStr) return 1 // Put items without dates at the end
    if (!bDateStr) return -1
    
    // Parse dates safely
    const dateA = new Date(aDateStr)
    const dateB = new Date(bDateStr)
    
    // Handle invalid dates
    if (isNaN(dateA.getTime()) && isNaN(dateB.getTime())) return 0
    if (isNaN(dateA.getTime())) return 1
    if (isNaN(dateB.getTime())) return -1
    
    // Normal comparison (always chronological for timeline)
    return dateA - dateB
  })
  
  return events
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