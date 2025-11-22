<template>
  <OrganizerLayout>
    <Head title="Pageant Timeline" />
    
    <div class="flex flex-col items-start md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Pageant Timeline</h1>
        <p class="text-gray-600">Track your pageant events and milestones</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
        <div class="relative flex-grow">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search pageants..."
            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 shadow-sm"
          />
          <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <Search class="w-5 h-5 text-gray-400" />
          </div>
        </div>
        
        <div class="min-w-[140px]">
          <CustomSelect
            v-model="statusFilter"
            :options="statusFilterOptions"
            variant="teal"
          />
        </div>
        
        <div class="min-w-[180px]">
          <CustomSelect
            v-model="sortOrder"
            :options="sortOrderOptions"
            variant="teal"
          />
        </div>
      </div>
    </div>
    
    <!-- Error message -->
    <div v-if="error" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-md shadow-sm">
      <div class="flex">
        <div class="flex-shrink-0">
          <AlertCircle class="h-5 w-5 text-red-400" />
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-700">{{ error }}</p>
        </div>
      </div>
    </div>
    
    <!-- Empty state message if no pageants -->
    <div v-if="!props.pageants || props.pageants.length === 0" class="bg-white rounded-xl shadow-md p-12 text-center max-w-lg mx-auto">
      <div class="p-4 bg-gray-50 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
        <Calendar class="h-10 w-10 text-teal-400" />
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">No pageants found</h3>
      <p class="text-gray-500 mb-6">There are no pageants assigned to you yet.</p>
      <Link 
        href="/organizer/dashboard" 
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
      >
        Return to Dashboard
      </Link>
    </div>
    
    <!-- Pageants Grid -->
    <div v-else-if="filteredPageants.length > 0" class="flex flex-col space-y-6 mb-8 max-w-3xl mx-auto">
      <Link 
        v-for="pageant in filteredPageants" 
        :key="pageant.id" 
        :href="route('organizer.pageant.timeline', pageant.id, false)"
        class="pageant-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-all duration-300 cursor-pointer transform hover:-translate-y-1"
      >
        <div class="flex flex-col md:flex-row">
          <!-- Pageant Cover Image or Gradient Background -->
          <div 
            class="h-48 md:w-1/3 relative overflow-hidden"
          >
            <div 
              class="w-full h-full absolute inset-0"
              :class="{ 
                'bg-gradient-to-r from-teal-600 to-red-500': !pageant.cover_image && pageant.status === 'Draft',
                'bg-gradient-to-r from-teal-600 to-teal-500': !pageant.cover_image && pageant.status === 'Setup',
                'bg-gradient-to-r from-teal-600 to-teal-500': !pageant.cover_image && pageant.status === 'Active',
                'bg-gradient-to-r from-teal-600 to-teal-500': !pageant.cover_image && pageant.status === 'Completed',
                'bg-gradient-to-r from-teal-600 to-teal-500': !pageant.cover_image && pageant.status === 'Unlocked_For_Edit',
                'bg-gradient-to-r from-teal-500 to-teal-500': !pageant.cover_image
              }"
            ></div>
            
            <img 
              v-if="pageant.cover_image" 
              :src="pageant.cover_image" 
              class="w-full h-full object-cover"
              alt="Pageant cover" 
            />
            
            <!-- Content overlay with status -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-4">
              <div class="mb-2">
                <div 
                  class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                  :class="getStatusClass(pageant.status)"
                >
                  {{ pageant.status }}
                </div>
              </div>
            </div>
          </div>
          
          <!-- Pageant Info -->
          <div class="p-5 md:w-2/3 flex flex-col justify-between">
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">{{ pageant.name }}</h3>
              
              <!-- Date and Location -->
              <div class="flex items-center mb-3 text-gray-500 text-sm">
                <Calendar class="w-4 h-4 mr-1" />
                <span>{{ pageant.start_date || 'Not scheduled' }}</span>
                <span v-if="pageant.end_date && pageant.start_date !== pageant.end_date" class="mx-1">-</span>
                <span v-if="pageant.end_date && pageant.start_date !== pageant.end_date">{{ pageant.end_date }}</span>
                
                <div class="mx-2 w-px h-4 bg-gray-300"></div>
                
                <MapPin class="w-4 h-4 mr-1" />
                <span>{{ pageant.venue || pageant.location || 'No location set' }}</span>
              </div>
              
              <!-- Description Text -->
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                {{ pageant.description || 'No description available for this pageant.' }}
              </p>
            </div>
            
            <div>
              <!-- Progress Section -->
              <div class="mb-4">
                <div class="flex items-center justify-between text-xs mb-1.5">
                  <span class="font-medium">Completion</span>
                  <span class="font-bold text-teal-600">{{ pageant.progress || 0 }}%</span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                  <div 
                    class="h-full rounded-full bg-gradient-to-r from-teal-500 to-teal-400"
                    :class="{
                      'from-gray-400 to-gray-300': !pageant.progress || pageant.progress === 0,
                      'from-teal-500 to-teal-400': pageant.progress === 100
                    }"
                    :style="{ width: `${pageant.progress || 0}%` }"
                  ></div>
                </div>
              </div>
              
              <!-- Footer with Stats and CTA -->
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <!-- Events functionality has been removed -->
                  <div class="flex items-center">
                    <Calendar class="w-5 h-5 text-gray-400" />
                    <span class="ml-1 text-sm font-medium text-gray-700">{{ pageant.contestants_count || 0 }} contestants</span>
                  </div>
                </div>
                
                <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-md transition-colors">
                  View Timeline
                  <ChevronRight class="w-4 h-4 ml-1" />
                </span>
              </div>
            </div>
          </div>
        </div>
      </Link>
    </div>
    
    <!-- Empty state after filtering -->
    <div v-else-if="filteredPageants.length === 0 && props.pageants.length > 0" class="bg-white rounded-xl shadow-md p-12 text-center max-w-lg mx-auto">
      <div class="p-4 bg-gray-50 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
        <Filter class="h-10 w-10 text-gray-400" />
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">No matching pageants</h3>
      <p class="text-gray-500 mb-6">Try adjusting your search filters to see more pageants.</p>
      <button 
        @click="clearFilters" 
        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
      >
        <X class="w-4 h-4 mr-2" />
        Clear Filters
      </button>
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
  Search, 
  MapPin, 
  Clock, 
  Award, 
  User2, 
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
  ChevronRight
} from 'lucide-vue-next'

// Props
const props = defineProps({
  pageants: {
    type: Array,
    default: () => []
  },
  error: {
    type: String,
    default: null
  }
})

// State
const searchQuery = ref('')
const statusFilter = ref('all')
const sortOrder = ref('asc')

// Options for selects
const statusFilterOptions = [
  { value: 'all', label: 'All Statuses' },
  { value: 'Draft', label: 'Draft' },
  { value: 'Setup', label: 'Setup' },
  { value: 'Active', label: 'Active' },
  { value: 'Completed', label: 'Completed' },
  { value: 'Unlocked_For_Edit', label: 'Unlocked For Edit' }
]

const sortOrderOptions = [
  { value: 'asc', label: 'Start Date (Oldest First)' },
  { value: 'desc', label: 'Start Date (Newest First)' }
]

// Computed
const filteredPageants = computed(() => {
  if (!props.pageants || !Array.isArray(props.pageants) || props.pageants.length === 0) {
    return []
  }
  
  let result = [...props.pageants]
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(pageant => 
      (pageant.name || '').toLowerCase().includes(query) || 
      (pageant.description || '').toLowerCase().includes(query) ||
      (pageant.venue || '').toLowerCase().includes(query) ||
      (pageant.location || '').toLowerCase().includes(query)
    )
  }
  
  // Apply status filter
  if (statusFilter.value !== 'all') {
    result = result.filter(pageant => pageant.status === statusFilter.value)
  }
  
  // Sort by date
  result.sort((a, b) => {
    // Extract start dates, with fallbacks for missing data
    const aStartDate = a.start_date || ''
    const bStartDate = b.start_date || ''
    
    // If either date is missing, handle gracefully
    if (!aStartDate && !bStartDate) return 0
    if (!aStartDate) return sortOrder.value === 'asc' ? -1 : 1
    if (!bStartDate) return sortOrder.value === 'asc' ? 1 : -1
    
    // Parse dates safely
    const dateA = new Date(aStartDate)
    const dateB = new Date(bStartDate)
    
    // Handle invalid dates
    if (isNaN(dateA.getTime()) && isNaN(dateB.getTime())) return 0
    if (isNaN(dateA.getTime())) return sortOrder.value === 'asc' ? -1 : 1
    if (isNaN(dateB.getTime())) return sortOrder.value === 'asc' ? 1 : -1
    
    // Normal comparison
    return sortOrder.value === 'asc' ? dateA - dateB : dateB - dateA
  })
  
  return result
})

// Methods
const getStatusClass = (status) => {
  switch (status) {
    case 'Active': return 'bg-teal-100 text-teal-800'
    case 'Setup': return 'bg-teal-100 text-teal-800'
    case 'Draft': return 'bg-gray-100 text-gray-800'
    case 'Completed': return 'bg-teal-100 text-teal-800'
    case 'Unlocked_For_Edit': return 'bg-teal-100 text-teal-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
  sortOrder.value = 'asc'
}
</script>

<style scoped>
/* Pageant card hover effects */
.pageant-card {
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
}

.pageant-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
  border-bottom: 3px solid #0d9488;
}

/* Custom scrollbar styles for modern browsers */
::-webkit-scrollbar {
  width: 12px;
}
  
::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}
  
::-webkit-scrollbar-thumb {
  background: #0d9488;
  border-radius: 10px;
  border: 3px solid #f1f1f1;
}
  
::-webkit-scrollbar-thumb:hover {
  background: #0f766e;
}
</style>
