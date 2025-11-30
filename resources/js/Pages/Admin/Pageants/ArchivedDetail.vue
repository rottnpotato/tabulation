<template>
  <Head :title="`${pageant.title} - Archived Pageant`" />
  <div class="container mx-auto pb-4 sm:pb-8">
    <!-- Main content container -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 sm:mb-6">
      <!-- Header section -->
      <div class="px-4 sm:px-6 py-5 sm:py-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center space-x-3">
            <Link 
              href="/admin/pageants/archived"
              class="inline-flex items-center px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 hover:text-red-600 transition-colors duration-150"
            >
              <ChevronLeft class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Back to Archived Pageants
            </Link>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">{{ pageant.title }}</h1>
          </div>
          
          <div class="flex items-center gap-2">
            <button 
              v-if="pageant.replacement_pageant"
              @click="viewReplacementPageant"
              class="bg-white border border-green-300 hover:bg-green-50 text-green-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <ArrowRight class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              View Replacement Pageant
            </button>
            
            <button 
              @click="restorePageant"
              class="bg-white border border-blue-300 hover:bg-blue-50 text-blue-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <RefreshCw class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Restore Pageant
            </button>
            
            <!-- <button 
              @click="exportData"
              class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <Download class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Export Archive Data
            </button> -->
          </div>
        </div>
      </div>

      <!-- Archive banner -->
      <div class="relative bg-gradient-to-r from-red-50 to-red-100/50 py-4 px-4 sm:px-6 border-b border-red-200 shadow-inner">
        <div class="absolute inset-0 bg-red-100/20"></div>
        <div class="relative">
        <div class="flex items-center">
          <Archive class="h-5 w-5 text-red-500 mr-2" />
          <span class="text-sm font-medium text-red-800">This pageant has been archived</span>
          <span class="bg-red-100 text-red-800 text-2xs px-2 py-0.5 rounded-full ml-3 font-medium">{{ pageant.reason }}</span>
        </div>
          <p class="text-xs text-red-700 mt-1 ml-7">
            Archived on {{ formatDate(pageant.archived_at) }} by {{ pageant.archived_by || "Admin" }}
          </p>
        </div>
      </div>

      <!-- Hero section with gradient background -->
      <div class="relative h-56 sm:h-72 bg-gradient-to-br from-gray-900 via-gray-800 to-red-900 overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjAzIiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=')] opacity-30"></div>
        <div class="absolute inset-0 overflow-hidden bg-opacity-60 bg-black flex items-center justify-center">
          <!-- Large archive icon watermark -->
          <Archive class="h-24 w-24 text-white opacity-10" />
        </div>
        
        <!-- Archive reason badge -->
        <div class="absolute top-4 right-4">
          <span class="px-3 py-1.5 text-sm font-semibold rounded-full shadow-sm" :class="getReasonClass(pageant.reason)">
            {{ pageant.reason }}
          </span>
        </div>
        
        <div class="absolute bottom-0 left-0 p-4 sm:p-6 text-white">
          <div class="flex items-center">
            <Calendar class="h-5 w-5 mr-2 text-white opacity-80" />
            <p class="text-white text-sm sm:text-base opacity-90">Originally planned for {{ formatDate(pageant.date) }}</p>
          </div>
          <div class="flex items-center mt-1.5">
            <MapPin class="h-5 w-5 mr-2 text-white opacity-80" />
            <p class="text-white text-sm sm:text-base opacity-90">{{ pageant.venue }}</p>
          </div>
        </div>
      </div>

      <!-- Archive note & information -->
      <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
          <AlertOctagon class="h-5 w-5 mr-2 text-red-500" />
          Archive Information
        </h2>
        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 sm:p-6 border border-gray-200 shadow-inner">
          <h3 class="text-md font-medium text-gray-700 mb-2">Reason for Archiving:</h3>
          <p class="text-gray-600 mb-4">{{ pageant.archive_note }}</p>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-200">
              <div class="flex items-start">
                <Calendar class="h-5 w-5 text-gray-400 mr-2" />
                <div>
                  <p class="text-xs text-gray-500 mb-1">Archive Date</p>
                  <p class="text-sm font-medium">{{ formatDate(pageant.archived_at) }}</p>
                </div>
              </div>
            </div>
            
            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-200">
              <div class="flex items-start">
                <User class="h-5 w-5 text-gray-400 mr-2" />
                <div>
                  <p class="text-xs text-gray-500 mb-1">Organizer</p>
                  <p class="text-sm font-medium">{{ pageant.organizer || "Not assigned" }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Replacement pageant information (if exists) -->
      <div v-if="pageant.replacement_pageant" class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
          <ArrowRight class="h-5 w-5 mr-2 text-green-500" />
          Replacement Pageant
        </h2>
        <div class="bg-gradient-to-r from-green-50 via-green-50/50 to-white rounded-xl p-5 sm:p-6 border border-green-200 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-grow">
              <h3 class="text-md font-medium text-gray-700 mb-1">{{ pageant.replacement_pageant }}</h3>
              <p class="text-sm text-gray-600">
                This archived pageant was replaced by a new event. Click the button to view the replacement pageant details.
              </p>
            </div>
            <button 
              @click="viewReplacementPageant"
              class="inline-flex items-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <ArrowRight class="h-4 w-4 mr-2" />
              View Details
            </button>
          </div>
        </div>
      </div>

      <!-- Original contestants information -->
      <div class="p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
          <UserMinus class="h-5 w-5 mr-2 text-gray-600" />
          Registered Contestants
        </h2>
        <div v-if="pageant.contestants > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-md">
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <p class="text-sm text-gray-700">
              This pageant had <span class="font-semibold">{{ pageant.contestants }}</span> contestants registered before it was archived.
            </p>
          </div>
          <div class="p-4 text-center">
            <div class="bg-gray-100 rounded-full h-4 overflow-hidden mb-3 w-full max-w-md mx-auto">
              <div 
                class="bg-gray-400 h-full rounded-full" 
                :style="`width: ${(pageant.contestants / 30) * 100}%`"
              ></div>
            </div>
            <p class="text-xs text-gray-500">
              {{ Math.round((pageant.contestants / 30) * 100) }}% of typical participation (based on 30 contestants average)
            </p>
          </div>
          <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 text-right">
            <button 
              @click="downloadContestantList"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              <Download class="h-3.5 w-3.5 mr-1.5" />
              Download Contestant List
            </button>
          </div>
        </div>
        <div v-else class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-10 border border-gray-200 flex flex-col items-center justify-center shadow-inner">
          <UserMinus class="h-12 w-12 text-gray-300 mb-2" />
          <p class="text-gray-500 text-center">No contestants were registered before this pageant was archived</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
  Calendar, 
  ChevronLeft, 
  Download, 
  Archive,
  AlertOctagon,
  MapPin,
  ArrowRight,
  RefreshCw,
  UserMinus,
  User
} from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Page setup
defineOptions({
  layout: AdminLayout,
});

// Define props
const props = defineProps({
  pageant: {
    type: Object,
    default: () => ({
      id: 1,
      title: 'Summer Queen 2024',
      date: '2024-07-15',
      venue: 'Sunset Beach Resort',
      reason: 'Cancelled',
      archived_at: '2024-02-10',
      archive_note: 'Venue booking conflicts and insufficient sponsorship led to cancellation.',
      organizer: 'Coastal Events Ltd.',
      contestants: 15,
      replacement_pageant: 'Winter Wonderland 2024',
      archived_by: 'John Admin'
    })
  }
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
      'admin.pageants.index': '/admin/pageants',
      'admin.pageants.archived': '/admin/pageants/archived',
    };
    
    const baseUrl = baseRoute[name] || '/';
    
    if (params && params.id) {
      return `${baseUrl}/${params.id}`;
    }
    
    return baseUrl;
  } catch (error) {
    console.error(`Error generating route for ${name}:`, error);
    return '/';
  }
};

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
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

// Functions for buttons
const restorePageant = () => {
  if (!confirm('Are you sure you want to restore this pageant? This will change its status back to Completed.')) {
    return;
  }
  
  router.post(route('admin.pageants.archived.restore', props.pageant.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect will be handled by the backend
    },
    onError: (errors) => {
      alert(errors.error || 'Failed to restore pageant. Please try again.');
    }
  });
};

const viewReplacementPageant = () => {
  // If there's a specific route to the replacement pageant, navigate there
  if (props.pageant.replacement_pageant) {
    // This would navigate to the replacement pageant if we had its ID
    alert(`This would navigate to ${props.pageant.replacement_pageant}. The functionality is not yet implemented.`);
  }
};

const exportData = () => {
  // This would typically trigger a download of the archived pageant data
  alert('This would export all archived pageant data. The functionality is not yet implemented.');
};

const downloadContestantList = () => {
  // This would typically trigger a download of the contestant list
  alert('This would download the contestant list. The functionality is not yet implemented.');
};

// Computed values
const participationPercentage = computed(() => {
  // Calculate percentage based on 30 contestants as "average" participation
  const percentage = (props.pageant.contestants / 30) * 100;
  return Math.min(100, Math.round(percentage)); // Cap at 100%
});
</script>

<style scoped>
.text-2xs {
  font-size: 0.65rem;
  line-height: 1rem;
}

/* Gradient background for archived elements */
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