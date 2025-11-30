<template>
  <Head title="Archived Pageants" />
  <div class="container mx-auto pb-4 sm:pb-8">
    <!-- Main content container with card styling -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 sm:mb-6">
      <!-- Header section -->
      <div class="px-4 sm:px-6 py-5 sm:py-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Archived Pageants</h1>
            <p class="text-sm text-gray-500 mt-1">View and manage historical pageant records</p>
          </div>
          <div class="flex items-center gap-2">
            <Link 
              href="/admin/pageants"
              class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <ChevronLeft class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              All Pageants
            </Link>
            <Link
              href="/admin/pageants/create"
              class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <Plus class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Create New Pageant
            </Link>
          </div>
        </div>
      </div>

      <!-- Filter & Search Section -->
      <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 bg-gradient-to-br from-gray-50 via-white to-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0">
          <div class="sm:hidden w-full">
            <button 
              @click="showFilters = !showFilters" 
              class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
            >
              <span class="flex items-center">
                <Filter class="h-4 w-4 mr-2 text-gray-500" />
                Filters & Sorting
              </span>
              <ChevronDown class="h-4 w-4 text-gray-500 transition-transform" :class="{'transform rotate-180': showFilters}" />
            </button>
          </div>

          <div :class="['space-y-3 sm:space-y-0 sm:flex sm:flex-wrap sm:items-center gap-2 sm:gap-4', showFilters ? 'block mt-3' : 'hidden sm:flex']">
            <!-- Year filter -->
            <div class="flex items-center space-x-2">
              <label class="text-xs sm:text-sm font-medium text-gray-700">Year:</label>
              <select 
                v-model="yearFilter" 
                class="border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm"
              >
                <option value="all">All Years</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
              </select>
            </div>

            <!-- Reason filter -->
            <div class="flex items-center space-x-2">
              <label class="text-xs sm:text-sm font-medium text-gray-700">Reason:</label>
              <select 
                v-model="reasonFilter" 
                class="border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm"
              >
                <option value="all">All Reasons</option>
                <option value="Cancelled">Cancelled</option>
                <option value="Postponed">Postponed</option>
                <option value="Merged">Merged</option>
                <option value="Low Participation">Low Participation</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- Sort by -->
            <div class="flex items-center space-x-2">
              <label class="text-xs sm:text-sm font-medium text-gray-700">Sort By:</label>
              <select 
                v-model="sortBy" 
                class="border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm"
              >
                <option value="title">Title</option>
                <option value="date">Date</option>
                <option value="venue">Venue</option>
                <option value="reason">Reason</option>
              </select>
            </div>

            <!-- Sort direction -->
            <div class="flex items-center space-x-2">
              <label class="text-xs sm:text-sm font-medium text-gray-700">Order:</label>
              <select 
                v-model="sortDirection" 
                class="border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm"
              >
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
              </select>
            </div>
          </div>

          <!-- Search box -->
          <div class="flex items-center space-x-2 sm:ml-auto">
            <div class="relative flex-grow">
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search archived pageants..." 
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm pl-4 pr-10"
              />
              <Search class="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
            </div>
            <button 
              @click="resetFilters"
              class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile view: Card listing -->
      <div class="sm:hidden">
        <div class="px-4 py-3 space-y-3">
          <div v-if="filteredPageants.length === 0" class="text-center py-8">
            <div class="flex flex-col items-center justify-center">
              <div class="w-64 h-64">
                <Vue3Lottie :animationData="emptyStateAnimation" :height="250" :width="250" />
              </div>
              <p class="text-base font-medium">No archived pageants found</p>
              <p class="text-sm">Try adjusting your search or filter criteria</p>
            </div>
          </div>

          <div 
            v-for="pageant in filteredPageants" 
            :key="pageant.id"
            class="bg-white border border-gray-200 rounded-xl p-4 shadow-md hover:border-red-300 hover:shadow-xl transition-all duration-300 cursor-pointer relative overflow-hidden transform hover:-translate-y-1"
            @click="navigateToDetails(pageant.id)"
          >
            <!-- Archive status ribbon -->
            <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 bg-gradient-to-r from-red-600 to-red-500 text-white w-40 h-5 flex items-center justify-center text-2xs font-bold rotate-45 shadow-lg">
              ARCHIVED
            </div>
            
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-r from-gray-500 to-red-600 flex items-center justify-center text-white font-bold overflow-hidden">
                <img v-if="pageant.logo" :src="pageant.logo" alt="Logo" class="w-full h-full object-cover p-1 bg-white" />
                <span v-else>{{ pageant.name?.charAt(0) || 'A' }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ pageant.name }}</p>
                  <span class="px-2 py-0.5 inline-flex text-2xs leading-5 font-semibold rounded-full" :class="getReasonClass(pageant.reason)">
                    {{ pageant.reason }}
                  </span>
                </div>
                <p v-if="pageant.venue" class="text-xs text-gray-500 truncate mt-0.5">{{ pageant.venue }}</p>
                
                <div class="mt-2 space-y-1.5">
                  <div class="flex items-center text-xs text-gray-500">
                    <Calendar class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span>{{ formatDate(pageant.start_date) }}</span>
                  </div>
                  
                  <div class="flex items-center text-xs text-gray-500">
                    <AlertOctagon class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span class="truncate">
                      {{ pageant.archive_note || 'No additional notes' }}
                    </span>
                  </div>
                  
                  <div class="flex items-center text-xs text-gray-500">
                    <Archive class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span class="truncate">
                      Archived on {{ formatDate(pageant.archived_at || new Date()) }}
                    </span>
                  </div>
                  
                  <div class="flex items-center text-xs text-gray-500">
                    <UserMinus class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span class="truncate">
                      {{ pageant.contestants || '0' }} contestants
                    </span>
                  </div>
                </div>
                
                <div class="mt-3 flex items-center justify-end">
                  <Link 
                    :href="safeRoute('admin.pageants.archived.detail', { id: pageant.id })"
                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded text-teal-700 bg-teal-100 hover:bg-teal-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors"
                    @click.stop
                  >
                    <Eye class="h-3 w-3 mr-1" />
                    View Details
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Desktop view: Grid layout -->
      <div class="hidden sm:block p-6">
        <div v-if="filteredPageants.length === 0" class="text-center py-8">
          <div class="flex flex-col items-center justify-center">
            <div class="w-96 h-96">
              <Vue3Lottie :animationData="emptyStateAnimation" :height="350" :width="350" />
            </div>
            <p class="text-xl font-medium">No archived pageants found</p>
            <p class="text-sm mt-2">Try adjusting your search or filter criteria</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="pageant in filteredPageants" 
            :key="pageant.id"
            class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 cursor-pointer group relative transform hover:-translate-y-2"
            @click="navigateToDetails(pageant.id)"
          >
            <!-- Archived badge - diagonal ribbon at the corner -->
            <div class="absolute top-0 right-0 w-20 h-20 overflow-hidden z-10">
              <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 bg-gradient-to-r from-red-600 to-red-500 text-white w-40 h-5 flex items-center justify-center text-2xs font-bold rotate-45 shadow-lg">
                ARCHIVED
              </div>
            </div>
            
            <!-- Cover image or gradient background with event theme info -->
            <div class="h-40 bg-gradient-to-br from-gray-800 via-gray-700 to-red-700 relative overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
              <img 
                v-if="pageant.cover_image" 
                :src="pageant.cover_image" 
                alt="Pageant cover" 
                class="w-full h-full object-cover"
              />
              
              <!-- Logo overlay -->
              <div v-if="pageant.logo" class="absolute bottom-3 left-3 opacity-75">
                <div class="h-14 w-14 rounded-lg bg-white p-1.5 shadow-lg">
                  <img :src="pageant.logo" alt="Pageant logo" class="w-full h-full object-contain" />
                </div>
              </div>
              
              <!-- Blur overlay with themed icons representing the archived event -->
              <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                <div class="grid grid-cols-3 gap-4 px-4 w-full">
                  <div class="flex flex-col items-center text-white opacity-75">
                    <UserMinus class="w-8 h-8 mb-1" />
                    <span class="text-2xs text-center">{{ pageant.contestants || '0' }} contestants</span>
                  </div>
                  <div class="flex flex-col items-center text-white opacity-75">
                    <Calendar class="w-8 h-8 mb-1" />
                    <span class="text-2xs text-center">{{ formatYear(pageant.start_date) }}</span>
                  </div>
                  <div class="flex flex-col items-center text-white opacity-75">
                    <MapPin class="w-8 h-8 mb-1" />
                    <span class="text-2xs text-center truncate">{{ pageant.venue }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Reason badge -->
              <div class="absolute top-3 left-3">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full shadow-sm" :class="getReasonClass(pageant.reason)">
                  {{ pageant.reason }}
                </span>
              </div>
              
              <!-- Hover effect with view details button -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center backdrop-blur-sm">
                <Link 
                  :href="safeRoute('admin.pageants.archived.detail', { id: pageant.id })"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors"
                  @click.stop
                >
                  <Eye class="h-4 w-4 mr-2" />
                  View Details
                </Link>
              </div>
            </div>
            
            <!-- Pageant card body -->
            <div class="p-5">
              <h3 class="text-lg font-semibold text-gray-800 mb-1 truncate group-hover:text-teal-600 transition-colors">{{ pageant.name }}</h3>
              <p v-if="pageant.venue" class="text-sm text-gray-500 mb-3">{{ pageant.venue }}</p>
              
              <div class="space-y-2">
                <!-- Original date -->
                <div class="flex items-center text-xs text-gray-600">
                  <Calendar class="h-3.5 w-3.5 mr-1.5 text-gray-500 flex-shrink-0" />
                  <span>Originally planned for {{ formatDate(pageant.start_date) }}</span>
                </div>
                
                <!-- Archive date -->
                <div class="flex items-center text-xs text-gray-600">
                  <Archive class="h-3.5 w-3.5 mr-1.5 text-red-500 flex-shrink-0" />
                  <span class="text-red-700">
                    Archived on {{ formatDate(pageant.archived_at || new Date()) }}
                  </span>
                </div>
                
                <!-- Archive note -->
                <div class="flex items-start text-xs text-gray-600 mt-1">
                  <AlertOctagon class="h-3.5 w-3.5 mr-1.5 text-gray-500 flex-shrink-0 mt-0.5" />
                  <span class="line-clamp-2">
                    {{ pageant.archive_note || 'No additional information provided about this archived pageant.' }}
                  </span>
                </div>
              </div>
              
              <!-- Quick stats -->
              <div class="flex flex-wrap gap-2 mt-3">
                <span v-if="pageant.organizer" class="px-2 py-0.5 bg-purple-100 text-purple-700 text-2xs rounded-full inline-flex items-center">
                  <User class="h-3 w-3 mr-1" />
                  {{ pageant.organizer }}
                </span>
              </div>
              
              <!-- Recovery options or linked to replacement pageant if applicable -->
              <div v-if="pageant.replacement_pageant" class="flex items-center mt-3 pt-2 border-t border-gray-100">
                <ArrowRight class="h-3.5 w-3.5 mr-1.5 text-green-500 flex-shrink-0" />
                <span class="text-2xs text-green-700">
                  Replaced by "{{ pageant.replacement_pageant }}"
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
  Calendar, 
  Filter, 
  Search,
  ChevronDown,
  ChevronLeft, 
  Eye, 
  Archive,
  AlertOctagon,
  MapPin,
  Plus,
  UserMinus,
  User,
  ArrowRight
} from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Vue3Lottie } from 'vue3-lottie';
import emptyStateAnimationData from '@/Assets/lotties/empty-state.json';

// Page setup
defineOptions({
  layout: AdminLayout,
});

// Helper function to safely create route URLs
const safeRoute = (name, params = {}) => {
  try {
    // Check if the route function is available globally
    if (typeof route === 'function') {
      return route(name, params);
    }
    
    // Fallback to direct URLs if route function is not available
    const baseRoute = {
      'admin.pageants.archived.detail': '/admin/pageants/archived/',
    };
    
    const baseUrl = baseRoute[name] || '/';
    
    if (params && params.id) {
      return `${baseUrl}${params.id}`;
    }
    
    return baseUrl;
  } catch (error) {
    console.error(`Error generating route for ${name}:`, error);
    return '/';
  }
};

// UI state
const showFilters = ref(false);
const currentPage = ref(1);
const searchQuery = ref('');
const yearFilter = ref('all');
const reasonFilter = ref('all');
const sortBy = ref('date');
const sortDirection = ref('desc');
const itemsPerPage = 9;

// Lottie animation
const emptyStateAnimation = emptyStateAnimationData;

// Sample data - would be passed as props in real implementation
const props = defineProps({
  pageants: {
    type: Array,
    required: true
  }
});

const archivedPageants = computed(() => props.pageants);

// Filtered and sorted pageants
const filteredPageants = computed(() => {
  let filtered = [...archivedPageants.value];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(pageant => 
      (pageant.name && pageant.name.toLowerCase().includes(query)) ||
      (pageant.venue && pageant.venue.toLowerCase().includes(query)) ||
      (pageant.archive_note && pageant.archive_note.toLowerCase().includes(query)) ||
      (pageant.category && pageant.category.toLowerCase().includes(query)) ||
      (pageant.organizers && pageant.organizers.some(o => o.name.toLowerCase().includes(query)))
    );
  }
  
  // Apply year filter
  if (yearFilter.value !== 'all') {
    filtered = filtered.filter(pageant => {
      if (!pageant.start_date) return false;
      const year = formatYear(pageant.start_date);
      return year === yearFilter.value;
    });
  }
  
  // Apply reason filter
  if (reasonFilter.value !== 'all') {
    filtered = filtered.filter(pageant => pageant.reason === reasonFilter.value);
  }
  
  // Apply sorting
  filtered.sort((a, b) => {
    let aValue, bValue;
    
    if (sortBy.value === 'title') {
      aValue = a.name || '';
      bValue = b.name || '';
    } else if (sortBy.value === 'date') {
      aValue = a.start_date ? new Date(a.start_date) : new Date(0);
      bValue = b.start_date ? new Date(b.start_date) : new Date(0);
    } else if (sortBy.value === 'venue') {
      aValue = a.venue || '';
      bValue = b.venue || '';
    } else if (sortBy.value === 'reason') {
      aValue = a.reason || '';
      bValue = b.reason || '';
    } else {
      aValue = a.name || '';
      bValue = b.name || '';
    }
    
    const compareResult = 
      typeof aValue === 'string' 
        ? aValue.localeCompare(bValue) 
        : aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
        
    return sortDirection.value === 'asc' ? compareResult : -compareResult;
  });
  
  return filtered;
});

// Reset all filters
const resetFilters = () => {
  searchQuery.value = '';
  yearFilter.value = 'all';
  reasonFilter.value = 'all';
  sortBy.value = 'date';
  sortDirection.value = 'desc';
  currentPage.value = 1;
};

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

// Extract year from date string
const formatYear = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.getFullYear().toString();
};

// Utility for reason class
const getReasonClass = (reason) => {
  switch (reason) {
    case 'Cancelled':
      return 'bg-red-100 text-red-800';
    case 'Postponed':
      return 'bg-yellow-100 text-yellow-800';
    case 'Merged':
      return 'bg-blue-100 text-blue-800';
    case 'Low Participation':
      return 'bg-orange-100 text-orange-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Navigation to details page
const navigateToDetails = (id) => {
  router.visit(safeRoute('admin.pageants.archived.detail', { id }));
};

// Check for empty lottie animation file during mounted
onMounted(() => {
  if (!emptyStateAnimation) {
    console.warn('Empty state animation not loaded. Please check the animation file.');
  }
});
</script>

<style scoped>
.text-2xs {
  font-size: 0.65rem;
  line-height: 1rem;
}

/* Gradient background for archived cards */
.from-gray-700.to-red-700 {
  background-image: linear-gradient(to right, #4a5568, #c53030);
}

/* Enhanced text truncation */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style> 