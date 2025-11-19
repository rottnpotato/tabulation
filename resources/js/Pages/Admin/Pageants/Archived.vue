<template>
  <Head title="Archived Pageants" />
  <div class="container mx-auto pb-4 sm:pb-8">
    <!-- Main content container with card styling -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 sm:mb-6">
      <!-- Header section -->
      <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Archived Pageants</h1>
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
            class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:border-teal-300 hover:shadow-md transition-all cursor-pointer relative overflow-hidden"
            @click="navigateToDetails(pageant.id)"
          >
            <!-- Archive status ribbon -->
            <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 bg-red-500 text-white w-40 h-5 flex items-center justify-center text-2xs font-bold rotate-45 shadow-md">
              ARCHIVED
            </div>
            
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-r from-gray-500 to-red-600 flex items-center justify-center text-white font-bold">
                {{ pageant.title?.charAt(0) || 'A' }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ pageant.title }}</p>
                  <span class="px-2 py-0.5 inline-flex text-2xs leading-5 font-semibold rounded-full" :class="getReasonClass(pageant.reason)">
                    {{ pageant.reason }}
                  </span>
                </div>
                <p v-if="pageant.venue" class="text-xs text-gray-500 truncate mt-0.5">{{ pageant.venue }}</p>
                
                <div class="mt-2 space-y-1.5">
                  <div class="flex items-center text-xs text-gray-500">
                    <Calendar class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                    <span>{{ formatDate(pageant.date) }}</span>
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
            class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer group relative"
            @click="navigateToDetails(pageant.id)"
          >
            <!-- Archived badge - diagonal ribbon at the corner -->
            <div class="absolute top-0 right-0 w-20 h-20 overflow-hidden">
              <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 bg-red-500 text-white w-40 h-5 flex items-center justify-center text-2xs font-bold rotate-45 shadow-md">
                ARCHIVED
              </div>
            </div>
            
            <!-- Gradient background with event theme info -->
            <div class="h-36 bg-gradient-to-r from-gray-700 to-red-700 relative overflow-hidden">
              <!-- Blur overlay with themed icons representing the archived event -->
              <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                <div class="grid grid-cols-3 gap-4 px-4 w-full">
                  <div class="flex flex-col items-center text-white opacity-75">
                    <UserMinus class="w-8 h-8 mb-1" />
                    <span class="text-2xs text-center">{{ pageant.contestants || '0' }} contestants</span>
                  </div>
                  <div class="flex flex-col items-center text-white opacity-75">
                    <Calendar class="w-8 h-8 mb-1" />
                    <span class="text-2xs text-center">{{ formatYear(pageant.date) }}</span>
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
              <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
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
            <div class="p-4">
              <h3 class="text-lg font-semibold text-gray-800 mb-1 truncate group-hover:text-teal-600 transition-colors">{{ pageant.title }}</h3>
              <p v-if="pageant.venue" class="text-sm text-gray-500 mb-3">{{ pageant.venue }}</p>
              
              <div class="space-y-2">
                <!-- Original date -->
                <div class="flex items-center text-xs text-gray-600">
                  <Calendar class="h-3.5 w-3.5 mr-1.5 text-gray-500 flex-shrink-0" />
                  <span>Originally planned for {{ formatDate(pageant.date) }}</span>
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
                <span v-if="pageant.budget" class="px-2 py-0.5 bg-blue-100 text-blue-700 text-2xs rounded-full inline-flex items-center">
                  <DollarSign class="h-3 w-3 mr-1" />
                  Budget: ${{ pageant.budget }}
                </span>
                
                <span v-if="pageant.organizer" class="px-2 py-0.5 bg-purple-100 text-purple-700 text-2xs rounded-full inline-flex items-center">
                  <User class="h-3 w-3 mr-1" />
                  {{ pageant.organizer }}
                </span>
                
                <span v-if="pageant.category" class="px-2 py-0.5 bg-amber-100 text-amber-700 text-2xs rounded-full inline-flex items-center">
                  <Tag class="h-3 w-3 mr-1" />
                  {{ pageant.category }}
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
  DollarSign,
  User,
  Tag,
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
const archivedPageants = ref([
  {
    id: 1,
    title: 'Summer Queen 2024',
    date: '2024-07-15',
    venue: 'Sunset Beach Resort',
    reason: 'Cancelled',
    archived_at: '2024-02-10',
    archive_note: 'Venue booking conflicts and insufficient sponsorship led to cancellation.',
    category: 'Beauty',
    budget: '25000',
    organizer: 'Coastal Events Ltd.',
    contestants: 15,
    replacement_pageant: 'Winter Wonderland 2024'
  },
  {
    id: 2,
    title: 'Teen Model 2024',
    date: '2024-08-20',
    venue: 'Youth Center',
    reason: 'Postponed',
    archived_at: '2024-03-01',
    archive_note: 'Postponed to next year due to renovations at the venue. Will be rescheduled.',
    category: 'Modeling',
    budget: '12000',
    organizer: 'Youth Fashion Council',
    contestants: 22
  },
  {
    id: 3,
    title: 'Mr. Fitness 2023',
    date: '2023-11-05',
    venue: 'Central Gym Arena',
    reason: 'Merged',
    archived_at: '2023-09-15',
    archive_note: 'This event was merged with the National Bodybuilding Championship.',
    category: 'Fitness',
    budget: '18500',
    organizer: 'PowerFit Promotions',
    contestants: 28,
    replacement_pageant: 'National Bodybuilding Championship 2023'
  },
  {
    id: 4,
    title: 'Miss Talent 2023',
    date: '2023-06-30',
    venue: 'Community Theater',
    reason: 'Low Participation',
    archived_at: '2023-05-25',
    archive_note: 'Insufficient number of registrations. Only 7 participants registered out of minimum 15 required.',
    category: 'Talent',
    budget: '15000',
    organizer: 'Arts Council',
    contestants: 7
  },
  {
    id: 5,
    title: 'Eco Ambassador 2025',
    date: '2025-04-22',
    venue: 'Botanical Gardens',
    reason: 'Other',
    archived_at: '2024-02-29',
    archive_note: 'Main sponsor withdrew and new concept being developed. Will be rebranded as "Green Earth Champions".',
    category: 'Environmental',
    budget: '30000',
    organizer: 'EcoVision Foundation',
    contestants: 0,
    replacement_pageant: 'Green Earth Champions 2025'
  }
]);

// Filtered and sorted pageants
const filteredPageants = computed(() => {
  let filtered = [...archivedPageants.value];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(pageant => 
      (pageant.title && pageant.title.toLowerCase().includes(query)) ||
      (pageant.venue && pageant.venue.toLowerCase().includes(query)) ||
      (pageant.archive_note && pageant.archive_note.toLowerCase().includes(query)) ||
      (pageant.category && pageant.category.toLowerCase().includes(query)) ||
      (pageant.organizer && pageant.organizer.toLowerCase().includes(query))
    );
  }
  
  // Apply year filter
  if (yearFilter.value !== 'all') {
    filtered = filtered.filter(pageant => {
      if (!pageant.date) return false;
      const year = formatYear(pageant.date);
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
      aValue = a.title || '';
      bValue = b.title || '';
    } else if (sortBy.value === 'date') {
      aValue = a.date ? new Date(a.date) : new Date(0);
      bValue = b.date ? new Date(b.date) : new Date(0);
    } else if (sortBy.value === 'venue') {
      aValue = a.venue || '';
      bValue = b.venue || '';
    } else if (sortBy.value === 'reason') {
      aValue = a.reason || '';
      bValue = b.reason || '';
    } else {
      aValue = a.title || '';
      bValue = b.title || '';
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