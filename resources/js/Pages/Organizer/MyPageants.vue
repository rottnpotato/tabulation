<template>
  <div class="space-y-8">
    <!-- Page Header -->
    <!-- Page Header -->
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
              My Pageants
            </h1>
            <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
              Manage all pageants assigned to you
            </p>
          </div>
          
          <div class="flex items-center space-x-3">
            <Tooltip text="Filter and sort pageants by status, date, or name" position="bottom">
              <button 
                class="inline-flex items-center px-4 py-2 border border-teal-200 rounded-xl shadow-sm text-sm font-medium text-teal-700 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all transform hover:-translate-y-0.5"
                @click="toggleFilters"
              >
                <Filter class="h-4 w-4 mr-2 text-teal-500" />
                Filter
              </button>
            </Tooltip>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Status Tabs -->
    <div class="bg-white shadow-sm rounded-2xl overflow-hidden border border-slate-100">
      <div class="border-b border-gray-200">
        <nav class="flex overflow-x-auto">
          <Tooltip
            v-for="tab in tabs" 
            :key="tab.value"
            :text="getTabTooltip(tab.value)"
            position="bottom"
          >
            <button 
              :class="[
                activeTab === tab.value 
                  ? 'border-teal-500 text-teal-600' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'group inline-flex items-center py-4 px-4 sm:px-6 border-b-2 font-medium text-sm whitespace-nowrap flex-shrink-0 transition-all'
              ]"
              @click="activeTab = tab.value"
            >
              <component :is="tab.icon" class="h-5 w-5 mr-2 transition-colors" :class="activeTab === tab.value ? 'text-teal-500' : 'text-gray-400 group-hover:text-gray-500'" />
              {{ tab.name }}
              <span
                :class="[
                  activeTab === tab.value ? 'bg-teal-100 text-teal-600' : 'bg-gray-100 text-gray-900',
                  'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium transition-colors'
                ]"
              >
                {{ pageantCounts[tab.value] || 0 }}
              </span>
            </button>
          </Tooltip>
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
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md"
            placeholder="Search pageants..."
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
          <CustomSelect
            v-model="sortBy"
            :options="sortByOptions"
            variant="teal"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
          <CustomSelect
            v-model="sortOrder"
            :options="sortOrderOptions"
            variant="teal"
          />
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
            class="bg-white overflow-hidden rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group cursor-pointer"
            @click="managePageant(pageant)"
          >
            <!-- Card Header with Banner + Logo -->
            <div class="h-32 relative overflow-hidden rounded-t-xl">
              <!-- Banner image when available; fallback to existing gradient -->
              <div v-if="getBannerUrl(pageant)" class="absolute inset-0">
                <img :src="getBannerUrl(pageant)" alt="Pageant banner"
                     class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black/30"></div>
              </div>
              <div v-else class="absolute inset-0 bg-gradient-to-r from-teal-500 to-teal-300"></div>

              <!-- Status Badge -->
              <Tooltip :text="getStatusTooltip(pageant.status)" position="right">
                <span :class="[
                  getStatusClass(pageant.status).badge,
                  'absolute top-3 left-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium shadow-sm hover:shadow-md transition-shadow cursor-help'
                ]">
                  {{ pageant.status }}
                </span>
              </Tooltip>

              <!-- Logo, if present -->
              <div v-if="getLogoUrl(pageant)" class="absolute top-3 right-3">
                <img :src="getLogoUrl(pageant)" alt="Pageant logo"
                     class="w-10 h-10 rounded-full object-cover border border-white shadow-md" />
              </div>
              
              <!-- Overlay with Title -->
              <div class="absolute inset-0 bg-black/20 p-4 flex flex-col justify-end">
                <h3 class="text-lg font-bold text-white truncate">{{ pageant.name }}</h3>
                <div class="flex items-center text-white/80 text-xs mt-1">
                  <Calendar class="h-3.5 w-3.5 mr-1" />
                  {{ formatDateRange(pageant) || 'Date not set' }}
                </div>
              </div>
              
              <!-- View Details hover effect -->
              <div class="absolute inset-0 bg-teal-600 bg-opacity-80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
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
                  <Users class="h-4 w-4 text-teal-400 mr-1" />
                  <span>{{ pageant.contestants_count }} Contestants</span>
                </div>
                <div class="flex items-center">
                  <ListChecks class="h-4 w-4 text-teal-400 mr-1" />
                  <span>{{ pageant.criteria_count }} Criteria</span>
                </div>
                <div class="flex items-center">
                  <Scale class="h-4 w-4 text-teal-400 mr-1" />
                  <span>{{ pageant.judges_count }} Judges</span>
                </div>
              </div>
            </div>
            
            <!-- Card Footer -->
            <div class="border-t border-gray-100 bg-gray-50 p-3 flex justify-between items-center">
              <div class="flex items-center">
                <Crown class="h-4 w-4 text-teal-500 mr-1.5" />
                <span class="text-xs font-medium text-gray-600">
                  {{ getActionTextByStatus(pageant.status) }}
                </span>
              </div>
              <div class="flex-shrink-0">
                <Tooltip :text="getActionTextByStatus(pageant.status)" position="left">
                  <button
                    @click.stop="managePageant(pageant)"
                    class="p-1.5 rounded-full text-gray-400 bg-white border border-gray-200 hover:text-teal-600 hover:border-teal-300 transition-all transform hover:scale-110 hover:shadow-md"
                  >
                    <ChevronRight class="h-4 w-4" />
                  </button>
                </Tooltip>
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
import Tooltip from '@/Components/Tooltip.vue'
import CustomSelect from '@/Components/CustomSelect.vue'
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

for(const pageant of props.pageants) {
  console.log(pageant)
}

// State
const isLoading = ref(false)
const showFilters = ref(false)
const searchQuery = ref('')
const sortBy = ref('name')
const sortOrder = ref('asc')
const activeTab = ref('total')

// Options for selects
const sortByOptions = [
  { value: 'name', label: 'Name' },
  { value: 'created_at', label: 'Date Created' },
  { value: 'start_date', label: 'Start Date' }
]

const sortOrderOptions = [
  { value: 'asc', label: 'Ascending' },
  { value: 'desc', label: 'Descending' }
]

// Define tabs
const tabs = [
  { name: 'All Pageants', value: 'total', icon: Calendar },
  { name: 'Draft', value: 'draft', icon: Clock },
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

// Asset helpers
const resolveAssetUrl = (path) => {
  if (!path || typeof path !== 'string') {
    return null
  }
  if (path.startsWith('http') || path.startsWith('//') || path.startsWith('/')) {
    return path
  }
  // Assume storage-relative path
  return `/storage/${path}`
}

const getBannerUrl = (pageant) => {
  return resolveAssetUrl(
    pageant.coverImage || pageant.cover_image || pageant.banner || pageant.banner_image || null
  )
}

const getLogoUrl = (pageant) => {
  return resolveAssetUrl(
    pageant.logo || pageant.logo_path || null
  )
}

// Get status styling
const getStatusClass = (status) => {
  switch (status) {
    case 'Draft':
      return { badge: 'bg-gray-100 text-gray-800' }
    case 'Active':
      return { badge: 'bg-teal-100 text-teal-800' }
    case 'Completed':
      return { badge: 'bg-teal-100 text-teal-800' }
    case 'Unlocked_For_Edit':
      return { badge: 'bg-teal-100 text-teal-800' }
    default:
      return { badge: 'bg-gray-100 text-gray-800' }
  }
}

// Get action text based on pageant status
const getActionTextByStatus = (status) => {
  switch (status) {
    case 'Draft':
      return 'Continue Setup'
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

// Get tooltip text for tabs
const getTabTooltip = (tabValue) => {
  switch (tabValue) {
    case 'total':
      return 'View all pageants assigned to you'
    case 'draft':
      return 'Pageants still being planned and configured'
    case 'active':
      return 'Currently running pageants with ongoing scoring'
    case 'completed':
      return 'Finished pageants with final results'
    case 'unlocked_for_edit':
      return 'Completed pageants temporarily unlocked for editing'
    default:
      return 'Filter pageants by status'
  }
}

// Get tooltip text for status badges
const getStatusTooltip = (status) => {
  switch (status) {
    case 'Draft':
      return 'This pageant is still being planned. Add contestants and criteria to progress.'
    case 'Active':
      return 'This pageant is currently running. Judges are scoring and results are being tabulated live.'
    case 'Completed':
      return 'This pageant has finished and final results have been calculated and announced.'
    case 'Unlocked_For_Edit':
      return 'This completed pageant has been temporarily unlocked to allow corrections or updates.'
    default:
      return 'Pageant status information'
  }
}

// Computed
const filteredPageants = computed(() => {
  let result = [...props.pageants]
  
  // Filter by tab (status)
  if (activeTab.value !== 'total') {
    const statusMap = {
      'draft': 'Draft',
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

<style scoped>
/* Line clamp utility for multi-line text truncation */
.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
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