<template>
  <div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">My Pageants</h1>
        <p class="mt-1 text-sm text-gray-500">Manage all pageants assigned to you</p>
      </div>
      
      <div class="flex items-center space-x-3">
        <button 
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 btn-transition"
          @click="toggleFilters"
        >
          <Filter class="h-4 w-4 mr-2 text-gray-500" />
          Filter
        </button>
      </div>
    </div>
    
    <!-- Status Tabs -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <div class="border-b border-gray-200">
        <nav class="flex overflow-x-auto">
          <button 
            v-for="tab in tabs" 
            :key="tab.value"
            :class="[
              activeTab === tab.value 
                ? 'border-orange-500 text-orange-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'group inline-flex items-center py-4 px-4 sm:px-6 border-b-2 font-medium text-sm whitespace-nowrap flex-shrink-0'
            ]"
            @click="activeTab = tab.value"
          >
            <component :is="tab.icon" class="h-5 w-5 mr-2" :class="activeTab === tab.value ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-500'" />
            {{ tab.name }}
            <span
              :class="[
                activeTab === tab.value ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-900',
                'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
              ]"
            >
              {{ pageantCounts[tab.value] || 0 }}
            </span>
          </button>
        </nav>
      </div>
      
      <!-- Filter panel (hidden by default) -->
      <div v-if="showFilters" class="p-4 bg-gray-50 border-b border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
          <input 
            type="text" 
            id="search" 
            v-model="searchQuery" 
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
            placeholder="Search pageants..."
          />
        </div>
        
        <div>
          <label for="sortBy" class="block text-sm font-medium text-gray-700">Sort By</label>
          <select 
            id="sortBy" 
            v-model="sortBy"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
          >
            <option value="name">Name</option>
            <option value="created_at">Date Created</option>
            <option value="start_date">Start Date</option>
          </select>
        </div>
        
        <div>
          <label for="sortOrder" class="block text-sm font-medium text-gray-700">Sort Order</label>
          <select 
            id="sortOrder" 
            v-model="sortOrder"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
          >
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
          </select>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="filteredPageants.length === 0" class="py-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100">
          <Calendar class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No pageants found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ activeTab === 'total' ? 'You have not been assigned to any pageants yet.' : `You don't have any ${activeTab} pageants.` }}
        </p>
      </div>
        
      <!-- Pageant Cards Grid -->
      <div v-else class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="pageant in filteredPageants" 
            :key="pageant.id" 
            class="bg-white overflow-hidden rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 group cursor-pointer"
            @click="managePageant(pageant)"
          >
            <!-- Card Header with Gradient Background -->
            <div class="h-32 bg-gradient-to-r from-orange-500 to-orange-300 relative">
              <!-- Status Badge -->
              <span :class="[
                getStatusClass(pageant.status).badge,
                'absolute top-3 left-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium shadow-sm'
              ]">
                {{ pageant.status }}
              </span>
              
              <!-- Overlay with Title -->
              <div class="absolute inset-0 bg-black bg-opacity-20 p-4 flex flex-col justify-end">
                <h3 class="text-lg font-bold text-white truncate">{{ pageant.name }}</h3>
                <div class="flex items-center text-white/80 text-xs mt-1">
                  <Calendar class="h-3.5 w-3.5 mr-1" />
                  {{ formatDateRange(pageant) || 'Date not set' }}
                </div>
              </div>
              
              <!-- View Details hover effect -->
              <div class="absolute inset-0 bg-orange-600 bg-opacity-80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="text-white flex items-center font-medium">
                  <ChevronRight class="h-5 w-5 mr-1" />
                  View Details
                </div>
              </div>
            </div>
            
            <!-- Card Body -->
            <div class="p-4">
              <!-- Location Info -->
              <div class="flex items-start mb-4">
                <MapPin class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" />
                <p class="ml-2 text-sm text-gray-600 line-clamp-2">
                  {{ pageant.location || pageant.venue || 'Location not specified' }}
                </p>
              </div>
              
              <!-- Stats -->
              <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center">
                  <Users class="h-4 w-4 text-orange-400 mr-1" />
                  <span>{{ pageant.contestants_count }} Contestants</span>
                </div>
                <div class="flex items-center">
                  <ListChecks class="h-4 w-4 text-orange-400 mr-1" />
                  <span>{{ pageant.criteria_count }} Criteria</span>
                </div>
                <div class="flex items-center">
                  <Scale class="h-4 w-4 text-orange-400 mr-1" />
                  <span>{{ pageant.judges_count }} Judges</span>
                </div>
              </div>
            </div>
            
            <!-- Card Footer -->
            <div class="border-t border-gray-100 bg-gray-50 p-3 flex justify-between items-center">
              <div class="flex items-center">
                <Crown class="h-4 w-4 text-orange-500 mr-1.5" />
                <span class="text-xs font-medium text-gray-600">
                  {{ getActionTextByStatus(pageant.status) }}
                </span>
              </div>
              <div class="flex-shrink-0">
                <button
                  @click.stop="managePageant(pageant)"
                  class="p-1.5 rounded-full text-gray-400 bg-white border border-gray-200 hover:text-orange-600 hover:border-orange-300 transition-colors"
                >
                  <ChevronRight class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  Calendar, Crown, ChevronRight, Filter, Users, ListChecks, Scale, 
  ExternalLink, Play, Pause, CheckCircle, Clock, AlertCircle, 
  Archive, MapPin
} from 'lucide-vue-next'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageants: {
    type: Array,
    required: true
  },
  pageantCounts: {
    type: Object,
    required: true
  }
})

// State
const isLoading = ref(false)
const showFilters = ref(false)
const searchQuery = ref('')
const sortBy = ref('name')
const sortOrder = ref('asc')
const activeTab = ref('total')

// Define tabs
const tabs = [
  { name: 'All Pageants', value: 'total', icon: Calendar },
  { name: 'Draft', value: 'draft', icon: Clock },
  { name: 'Setup', value: 'setup', icon: Play },
  { name: 'Active', value: 'active', icon: Play },
  { name: 'Completed', value: 'completed', icon: CheckCircle },
  { name: 'Unlocked', value: 'unlocked_for_edit', icon: AlertCircle },
]

// Methods
const toggleFilters = () => {
  showFilters.value = !showFilters.value
}

const managePageant = (pageant) => {
  // Navigate to pageant detail view
  router.visit(route('organizer.pageant.view', { id: pageant.id }))
}

const shouldShowDateRange = (pageant) => {
  return pageant.start_date || pageant.end_date
}

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

// Get status styling
const getStatusClass = (status) => {
  switch (status) {
    case 'Draft':
      return { badge: 'bg-gray-100 text-gray-800' }
    case 'Setup':
      return { badge: 'bg-blue-100 text-blue-800' }
    case 'Active':
      return { badge: 'bg-green-100 text-green-800' }
    case 'Completed':
      return { badge: 'bg-purple-100 text-purple-800' }
    case 'Unlocked_For_Edit':
      return { badge: 'bg-yellow-100 text-yellow-800' }
    default:
      return { badge: 'bg-gray-100 text-gray-800' }
  }
}

// Get action text based on pageant status
const getActionTextByStatus = (status) => {
  switch (status) {
    case 'Draft':
      return 'Continue Setup'
    case 'Setup':
      return 'Complete Setup'
    case 'Active':
      return 'View Live Status'
    case 'Completed':
      return 'View Results'
    case 'Unlocked_For_Edit':
      return 'Edit & Relock'
    default:
      return 'Manage Pageant'
  }
}

// Computed
const filteredPageants = computed(() => {
  let result = [...props.pageants]
  
  // Filter by tab (status)
  if (activeTab.value !== 'total') {
    const statusMap = {
      'draft': 'Draft',
      'setup': 'Setup',
      'active': 'Active',
      'completed': 'Completed',
      'unlocked_for_edit': 'Unlocked_For_Edit'
    }
    result = result.filter(pageant => pageant.status === statusMap[activeTab.value])
  }
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(pageant => 
      pageant.name.toLowerCase().includes(query) || 
      (pageant.location && pageant.location.toLowerCase().includes(query)) ||
      (pageant.venue && pageant.venue.toLowerCase().includes(query))
    )
  }
  
  // Apply sorting
  result.sort((a, b) => {
    // Handle null values
    if (a[sortBy.value] === null) return sortOrder.value === 'asc' ? -1 : 1
    if (b[sortBy.value] === null) return sortOrder.value === 'asc' ? 1 : -1
    
    // Compare values
    if (a[sortBy.value] < b[sortBy.value]) return sortOrder.value === 'asc' ? -1 : 1
    if (a[sortBy.value] > b[sortBy.value]) return sortOrder.value === 'asc' ? 1 : -1
    return 0
  })
  
  return result
})

onMounted(() => {
  isLoading.value = false
})
</script>

<style>
/* Line clamp utility for multi-line text truncation */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 