<template>
  <Head title="Previous Pageants" />
  <div class="container mx-auto pb-4 sm:pb-8">
    <!-- Main content container with card styling -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 sm:mb-6">
      <!-- Header section -->
      <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Previous Pageants</h1>
          <div class="flex items-center gap-2">
            <Link 
              href="/admin/pageants"
              class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
              </svg>
              All Pageants
            </Link>
            <Link
              href="/admin/pageants/create"
              class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              Create New Pageant
            </Link>
          </div>
        </div>
      </div>

      <!-- Filter & Search Section -->
      <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
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

            <!-- Status filter -->
            <div class="flex items-center space-x-2">
              <label class="text-xs sm:text-sm font-medium text-gray-700">Status:</label>
              <select 
                v-model="statusFilter" 
                class="border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm"
              >
                <option value="all">All Status</option>
                <option value="Completed">Completed</option>
                <option value="Unlocked_For_Edit">Unlocked For Edit</option>
              </select>
            </div>

            <!-- Sort by -->
            <div class="flex items-center space-x-2">
              <label class="text-xs sm:text-sm font-medium text-gray-700">Sort By:</label>
              <select 
                v-model="sortBy" 
                class="border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm"
              >
                <option value="name">Name</option>
                <option value="date">Date</option>
                <option value="location">Location</option>
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
                placeholder="Search previous pageants..." 
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
              <p class="text-base font-medium">No pageants found</p>
              <p class="text-sm">Try adjusting your search or filter criteria</p>
            </div>
          </div>

          <div 
            v-for="pageant in filteredPageants" 
            :key="pageant.id"
            @click="router.visit(`/admin/pageants/previous/${pageant.id}`)"
            class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:border-teal-300 hover:shadow-md transition-all cursor-pointer relative"
          >
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-teal-600 flex items-center justify-center text-white font-bold">
                {{ pageant.name?.charAt(0) || 'P' }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ pageant.name }}</p>
                  <span class="px-2 py-0.5 inline-flex text-2xs leading-5 font-semibold rounded-full" :class="getStatusClass(pageant.status || 'Completed')">
                    {{ pageant.status || 'Completed' }}
                  </span>
                </div>
                <p v-if="pageant.location" class="text-xs text-gray-500 truncate mt-0.5">{{ pageant.location }}</p>
                
                <div class="mt-2 space-y-1.5">
                  <div class="flex items-center text-xs text-gray-500">
                    <Calendar class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span>{{ formatDate(pageant.start_date) }}</span>
                  </div>
                  
                  <div class="flex items-center text-xs text-gray-500">
                    <Trophy class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span v-if="pageant.winner" class="truncate">
                      {{ pageant.winner }}
                    </span>
                    <span v-else class="text-gray-400">No winner recorded</span>
                  </div>
                  
                  <div class="flex items-center text-xs text-gray-500">
                    <Users class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span v-if="pageant.contestants_count && pageant.contestants_count > 0" class="truncate">
                      {{ pageant.contestants_count }} contestants
                    </span>
                    <span v-else class="text-gray-400">Contestants data not available</span>
                  </div>

                  <div v-if="pageant.is_edit_permission_granted" class="flex items-center text-xs text-gray-500 mt-1">
                    <Unlock class="h-3 w-3 mr-1.5 text-amber-500 flex-shrink-0" />
                    <span class="text-amber-600">Edit permission granted</span>
                  </div>
                </div>
                
                <div class="mt-3 flex items-center justify-end">
                  <Link 
                    :href="`/admin/pageants/previous/${pageant.id}`"
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

      <!-- Desktop view: Grid layout for better visualization -->
      <div class="hidden sm:block p-6">
        <div v-if="filteredPageants.length === 0" class="text-center py-8">
          <div class="flex flex-col items-center justify-center">
            <div class="w-96 h-96">
              <Vue3Lottie :animationData="emptyStateAnimation" :height="350" :width="350" />
            </div>
            <p class="text-xl font-medium">No previous pageants found</p>
            <p class="text-sm mt-2">Try adjusting your search or filter criteria</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="pageant in filteredPageants" 
            :key="pageant.id"
            class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer group relative"
            @click="router.visit(`/admin/pageants/previous/${pageant.id}`)"
          >
            <!-- Pageant cover photo or gradient background -->
            <div class="h-36 bg-gradient-to-r from-purple-500 to-teal-500 relative">
              <img 
                v-if="pageant.cover_image" 
                :src="pageant.cover_image" 
                alt="Pageant cover" 
                class="w-full h-full object-cover"
              />
              
              <!-- Status badge -->
              <div class="absolute top-3 right-3">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(pageant.status || 'Completed')">
                  {{ pageant.status || 'Completed' }}
                </span>
              </div>
              
              <!-- Edit permission indicator if applicable -->
              <div v-if="pageant.is_edit_permission_granted" class="absolute top-3 left-3">
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full inline-flex items-center">
                  <Unlock class="h-3 w-3 mr-1" />
                  Editable
                </span>
              </div>
              
              <!-- Hover effect with view details button -->
              <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <Link 
                  :href="`/admin/pageants/previous/${pageant.id}`"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors"
                  @click.stop
                >
                  <Eye class="h-4 w-4 mr-2" />
                  View Details
                </Link>
              </div>
            </div>
            
            <!-- Pageant card body -->
            <div class="p-4">
              <h3 class="text-lg font-semibold text-gray-800 mb-1 truncate group-hover:text-teal-600 transition-colors">{{ pageant.name }}</h3>
              <p v-if="pageant.location" class="text-sm text-gray-500 mb-3">{{ pageant.location }}</p>
              
              <div class="space-y-2">
                <!-- Date -->
                <div class="flex items-center text-xs text-gray-600">
                  <Calendar class="h-3.5 w-3.5 mr-1.5 text-gray-500 flex-shrink-0" />
                  <span>{{ formatDate(pageant.start_date) }}</span>
                </div>
                
                <!-- Winner information -->
                <div class="flex items-center text-xs text-gray-600">
                  <Trophy class="h-3.5 w-3.5 mr-1.5 text-amber-500 flex-shrink-0" />
                  <span v-if="pageant.winner" class="truncate text-amber-700 font-medium">
                    {{ pageant.winner }}
                  </span>
                  <span v-else class="text-gray-500">Winner undetermined</span>
                </div>
                
                <!-- Organizers information -->
                <div class="flex items-center text-xs text-gray-600">
                  <Users class="h-3.5 w-3.5 mr-1.5 text-gray-500 flex-shrink-0" />
                  <span class="truncate">
                    {{ pageant.organizers && pageant.organizers.length > 0 
                      ? pageant.organizers.map(org => org.name).join(', ') 
                      : 'No organizers assigned' }}
                  </span>
                </div>
              </div>
              
              <!-- Quick stats -->
              <div class="flex flex-wrap gap-2 mt-3">
                <span v-if="pageant.contestants_count" class="px-2 py-0.5 bg-purple-100 text-purple-700 text-2xs rounded-full inline-flex items-center">
                  <Users class="h-3 w-3 mr-1" />
                  {{ pageant.contestants_count }} contestants
                </span>
                
                <span v-if="pageant.judges_count" class="px-2 py-0.5 bg-teal-100 text-teal-700 text-2xs rounded-full inline-flex items-center">
                  <Award class="h-3 w-3 mr-1" />
                  {{ pageant.judges_count }} judges
                </span>
                
                <span v-if="pageant.start_date" class="px-2 py-0.5 bg-blue-100 text-blue-700 text-2xs rounded-full inline-flex items-center">
                  <Calendar class="h-3 w-3 mr-1" />
                  {{ getYear(pageant.start_date) }}
                </span>
              </div>
              
              <!-- Edit permission indicator at the bottom of the card -->
              <div v-if="pageant.is_edit_permission_granted" class="flex items-center mt-3 pt-2 border-t border-gray-100">
                <Unlock class="h-3.5 w-3.5 mr-1.5 text-amber-500 flex-shrink-0" />
                <div>
                  <span class="text-2xs text-amber-700">
                    Edit permission granted to {{ pageant.edit_permission_granted_to?.name }}
                  </span>
                  <span v-if="pageant.edit_permission_expires_at" class="text-2xs text-gray-500 block">
                    Expires: {{ formatExpiryDate(pageant.edit_permission_expires_at) }}
                  </span>
                </div>
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
  Trophy, 
  Users, 
  Award, 
  ChevronDown, 
  Eye, 
  Unlock
} from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Vue3Lottie } from 'vue3-lottie';
import emptyStateAnimationData from '@/Assets/lotties/empty-state.json';

// Page setup
defineOptions({
  layout: AdminLayout,
});

// Define props
const props = defineProps({
  pageants: {
    type: Array,
    default: () => []
  }
});

// UI state
const showFilters = ref(false);
const currentPage = ref(1);
const searchQuery = ref('');
const yearFilter = ref('all');
const statusFilter = ref('all');
const sortBy = ref('date');
const sortDirection = ref('desc');
const itemsPerPage = 9;

// Lottie animation
const emptyStateAnimation = emptyStateAnimationData;

// Filtered and sorted pageants
const filteredPageants = computed(() => {
  let filtered = [...props.pageants];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(pageant => 
      (pageant.name && pageant.name.toLowerCase().includes(query)) ||
      (pageant.location && pageant.location.toLowerCase().includes(query)) ||
      (pageant.winner && pageant.winner.toLowerCase().includes(query)) ||
      (pageant.organizers && pageant.organizers.some(org => org.name.toLowerCase().includes(query)))
    );
  }
  
  // Apply year filter
  if (yearFilter.value !== 'all') {
    filtered = filtered.filter(pageant => {
      if (!pageant.start_date) return false;
      const year = getYear(pageant.start_date);
      return year === yearFilter.value;
    });
  }
  
  // Apply status filter
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(pageant => pageant.status === statusFilter.value);
  }
  
  // Apply sorting
  filtered.sort((a, b) => {
    let aValue, bValue;
    
    if (sortBy.value === 'name') {
      aValue = a.name || '';
      bValue = b.name || '';
    } else if (sortBy.value === 'date') {
      aValue = a.start_date ? new Date(a.start_date) : new Date(0);
      bValue = b.start_date ? new Date(b.start_date) : new Date(0);
    } else if (sortBy.value === 'location') {
      aValue = a.location || '';
      bValue = b.location || '';
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
  
  // Apply pagination
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  
  return filtered;
});

// Reset all filters
const resetFilters = () => {
  searchQuery.value = '';
  yearFilter.value = 'all';
  statusFilter.value = 'all';
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

// Get year helper
const getYear = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.getFullYear().toString();
};

// Format expiry date helper
const formatExpiryDate = (dateString) => {
  if (!dateString) return '';
  
  // Parse the date string
  const date = new Date(dateString);
  
  // Format as MM/DD/YYYY
  return `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
};

// Utility for status class
const getStatusClass = (status) => {
  switch (status) {
    case 'Completed':
      return 'bg-teal-100 text-teal-800';
    case 'Unlocked_For_Edit':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Check for empty lottie animation file during mounted
onMounted(() => {
  if (!emptyStateAnimation) {
    console.warn('Empty state animation not loaded. Please create the animation file.');
  }
});
</script>

<style scoped>
.text-2xs {
  font-size: 0.65rem;
  line-height: 1rem;
}
</style>