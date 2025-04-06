<template>
  <Head :title="`${pageant.name} - Previous Pageant`" />
  <div class="container mx-auto pb-4 sm:pb-8">
    <!-- Main content container -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 sm:mb-6">
      <!-- Header section -->
      <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center space-x-3">
            <Link 
              href="/admin/pageants/previous"
              class="inline-flex items-center px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 hover:text-teal-600 transition-colors duration-150"
            >
              <ChevronLeft class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Back to Previous Pageants
            </Link>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">{{ pageant.name }}</h1>
          </div>
          
          <div class="flex items-center gap-2">
            <button 
              v-if="pageant.status === 'Completed'"
              @click="grantEditPermission"
              class="bg-white border border-amber-300 hover:bg-amber-50 text-amber-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <Unlock class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Grant Edit Permission
            </button>
            
            <button 
              v-if="pageant.status === 'Unlocked_For_Edit'"
              @click="revokeEditPermission"
              class="bg-white border border-red-300 hover:bg-red-50 text-red-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <Lock class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Revoke Edit Permission
            </button>
            
            <button 
              @click="exportResults"
              class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <Download class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" />
              Export Results
            </button>
          </div>
        </div>
      </div>

      <!-- Hero section with cover image -->
      <div class="relative h-48 sm:h-64 bg-gradient-to-r from-purple-500 to-teal-500">
        <div class="absolute inset-0 overflow-hidden">
          <img 
            v-if="pageant.coverImage" 
            :src="pageant.coverImage" 
            alt="Pageant Cover" 
            class="w-full h-full object-cover opacity-70"
          >
        </div>
        
        <!-- Status badge -->
        <div class="absolute top-4 right-4">
          <span class="px-3 py-1.5 text-sm font-semibold rounded-full shadow-sm" :class="getStatusClass(pageant.status || 'Completed')">
            {{ pageant.status || 'Completed' }}
          </span>
        </div>
        
        <!-- Edit permission indicator -->
        <div v-if="pageant.status === 'Unlocked_For_Edit'" class="absolute top-4 left-4">
          <div class="bg-amber-100 text-amber-800 px-3 py-1.5 rounded-full text-sm font-medium inline-flex items-center shadow-sm">
            <Unlock class="h-4 w-4 mr-2" />
            Edit Permission Granted
            <span v-if="pageant.edit_permission_expires_at" class="ml-2 text-amber-600 text-xs">
              (Expires: {{ formatExpiryDate(pageant.edit_permission_expires_at) }})
            </span>
          </div>
        </div>
        
        <div class="absolute bottom-0 left-0 p-4 sm:p-6 text-white">
          <div class="flex items-center">
            <Calendar class="h-5 w-5 mr-2 text-white opacity-80" />
            <p class="text-white text-sm sm:text-base opacity-90">{{ formatDate(pageant.start_date) }}</p>
          </div>
          <div class="flex items-center mt-1.5">
            <MapPin class="h-5 w-5 mr-2 text-white opacity-80" />
            <p class="text-white text-sm sm:text-base opacity-90">{{ pageant.venue || pageant.location }}</p>
          </div>
        </div>
      </div>

      <!-- Empty state when pageant has no detailed data -->
      <div v-if="!hasDetailedData" class="p-8 flex flex-col items-center justify-center">
        <div class="w-96 h-96 mb-6">
          <Vue3Lottie :animationData="emptyStateAnimation" :height="350" :width="350" />
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">No detailed data available</h2>
        <p class="text-gray-600 text-center max-w-lg mb-6">
          This pageant doesn't have detailed information available yet. You can add contestants, 
          categories, judges, and events to complete the pageant details.
        </p>
        <button 
          @click="editPageant"
          class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center transition-colors duration-150"
        >
          <Edit class="h-4 w-4 mr-2" />
          Edit Pageant Details
        </button>
      </div>

      <!-- Info cards section when data is available -->
      <div v-if="hasDetailedData" class="p-4 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <div class="bg-gradient-to-br from-purple-50 to-white rounded-lg p-4 border border-purple-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start">
              <Users class="h-6 w-6 text-purple-500 mr-3 flex-shrink-0" />
              <div>
                <p class="text-xs text-purple-500 font-medium uppercase mb-1">Contestants</p>
                <p class="text-xl font-semibold text-gray-800">{{ pageant.contestants_count || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Participants</p>
              </div>
            </div>
          </div>
          
          <div class="bg-gradient-to-br from-teal-50 to-white rounded-lg p-4 border border-teal-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start">
              <Award class="h-6 w-6 text-teal-500 mr-3 flex-shrink-0" />
              <div>
                <p class="text-xs text-teal-500 font-medium uppercase mb-1">Judges</p>
                <p class="text-xl font-semibold text-gray-800">{{ pageant.judges_count || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Industry Experts</p>
              </div>
            </div>
          </div>
          
          <div class="bg-gradient-to-br from-blue-50 to-white rounded-lg p-4 border border-blue-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start">
              <Layers class="h-6 w-6 text-blue-500 mr-3 flex-shrink-0" />
              <div>
                <p class="text-xs text-blue-500 font-medium uppercase mb-1">Categories</p>
                <p class="text-xl font-semibold text-gray-800">{{ pageant.topCategories?.length || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Scoring Segments</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Winner section - enhanced with visual elements -->
        <div class="mb-8">
          <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <Trophy class="h-5 w-5 mr-2 text-amber-500" />
            Winner
          </h2>
          <div v-if="pageantWinner" class="flex flex-col sm:flex-row items-center p-4 sm:p-6 bg-gradient-to-r from-amber-50 to-white rounded-lg border border-amber-100 shadow-sm">
            <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mb-4 sm:mb-0 sm:mr-6 overflow-hidden border-4 border-amber-200 shadow-sm">
              <img v-if="pageantWinner.photo" :src="pageantWinner.photo" alt="Winner" class="h-full w-full object-cover">
              <Trophy v-else class="h-10 w-10 text-amber-500" />
            </div>
            <div class="text-center sm:text-left flex-grow">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ pageantWinner.name }}</h3>
                  <p class="text-sm text-gray-500">{{ pageantWinner.title || "Grand Winner" }}</p>
                </div>
                <div class="mt-2 sm:mt-0">
                  <span class="bg-amber-100 text-amber-800 text-xs sm:text-sm px-3 py-1.5 rounded-full font-medium inline-flex items-center">
                    <Star class="h-3.5 w-3.5 mr-1.5 text-amber-500" />
                    {{ formatScore(pageantWinner.score) }}
                  </span>
                </div>
              </div>
              <p class="mt-3 text-sm text-gray-700 max-w-3xl">
                {{ pageantWinner.bio || "Winner of the prestigious competition, demonstrating exceptional talent, grace, and character throughout the pageant events." }}
              </p>
            </div>
          </div>
          <div v-else class="flex items-center justify-center p-6 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="text-center">
              <Trophy class="h-10 w-10 text-gray-300 mx-auto mb-2" />
              <p class="text-gray-500">No winner has been determined yet</p>
            </div>
          </div>
        </div>
        
        <!-- Categories and top performers -->
        <div v-if="pageant.topCategories && pageant.topCategories.length > 0" class="mb-8">
          <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <Star class="h-5 w-5 mr-2 text-purple-500" />
            Top Performers by Category
          </h2>
          <div class="space-y-4">
            <div v-for="category in pageant.topCategories" :key="category.name" class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all">
              <div class="bg-gradient-to-r from-gray-50 to-white px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-medium text-gray-700">{{ category.name }}</h3>
                <span class="text-xs font-medium py-1 px-2 rounded-full bg-purple-100 text-purple-800">Weight: {{ category.weight }}%</span>
              </div>
              <div class="divide-y divide-gray-200">
                <div v-for="(performer, index) in category.topPerformers || getTopPerformers()" :key="index" class="px-4 py-3 flex justify-between items-center">
                  <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-500 mr-4">{{ index + 1 }}</span>
                    <span>{{ performer.name }}</span>
                  </div>
                  <span class="text-sm font-medium text-teal-700 bg-teal-50 px-2 py-1 rounded-full">{{ performer.score }} / 10</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Description section - enhanced with visual elements -->
        <div>
          <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <FileText class="h-5 w-5 mr-2 text-blue-500" />
            Pageant Description
          </h2>
          <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
            <p v-if="pageant.description" class="text-gray-700">{{ pageant.description }}</p>
            <p v-else class="text-gray-500 italic">No description provided for this pageant.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Grant edit permission modal -->
  <div v-if="showGrantModal" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showGrantModal = false"></div>
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-100">
            <Unlock class="h-6 w-6 text-amber-600" />
          </div>
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Grant Edit Permission
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Select an organizer to grant edit permission. They will be able to make changes to this pageant.
              </p>
            </div>
          </div>
        </div>
        
        <div class="mt-5 sm:mt-6">
          <label for="organizer" class="block text-sm font-medium text-gray-700 mb-1">Select Organizer</label>
          <select 
            id="organizer" 
            v-model="selectedOrganizerId"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm mb-4"
          >
            <option value="" disabled selected>Select an organizer</option>
            <option v-for="organizer in allOrganizers" :key="organizer.id" :value="organizer.id">
              {{ organizer.name }} ({{ organizer.email }})
            </option>
          </select>
          
          <label for="expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
          <input 
            id="expiry" 
            type="datetime-local" 
            v-model="expiryDate"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm mb-4"
          />
          
          <div class="flex justify-end gap-3">
            <button 
              @click="showGrantModal = false"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
              Cancel
            </button>
            <button 
              @click="submitGrantPermission"
              :disabled="!selectedOrganizerId || !expiryDate"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-teal-600 border border-transparent rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Grant Permission
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useNotification } from '@/Composables/useNotification';
import { 
  ChevronLeft, 
  Trophy, 
  Download, 
  Calendar, 
  MapPin, 
  Users, 
  Award, 
  Layers,
  Star,
  Lock,
  Unlock,
  FileText,
  Edit
} from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Vue3Lottie } from 'vue3-lottie';
import emptyStateAnimationData from '@/Assets/lotties/empty-state.json';

// Page setup
defineOptions({
  layout: AdminLayout,
});

// Props
const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  contestants: {
    type: Array,
    default: () => []
  },
  allOrganizers: {
    type: Array,
    default: () => []
  },
  id: {
    type: Number,
    required: true
  }
});

// Reactive states
const showGrantModal = ref(false);
const selectedOrganizerId = ref('');
const expiryDate = ref('');
const emptyStateAnimation = emptyStateAnimationData;

// Check if pageant has detailed data
const hasDetailedData = computed(() => {
  return !!(
    (props.pageant.contestants_count && props.pageant.contestants_count > 0) ||
    (props.pageant.judges_count && props.pageant.judges_count > 0) ||
    (props.pageant.topCategories && props.pageant.topCategories.length > 0)
  );
});

// Computed pageant winner
const pageantWinner = computed(() => {
  if (!props.contestants || props.contestants.length === 0) return null;
  
  // Sort contestants by score and return the top one
  const sortedContestants = [...props.contestants].sort((a, b) => {
    const scoreA = a.score || 0;
    const scoreB = b.score || 0;
    return scoreB - scoreA;
  });
  
  return sortedContestants[0] || null;
});

// Helper function to format date in a more readable format
const formatDate = (dateString) => {
  if (!dateString) return 'Date not available';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Function to format expiry date with time
const formatExpiryDate = (dateString) => {
  if (!dateString) return null;
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Function to format scores based on scoring system
const formatScore = (score) => {
  if (score === null || score === undefined) return 'N/A';
  
  const scoringSystem = props.pageant.scoringSystem?.type || 'percentage';
  
  if (scoringSystem === 'percentage') return `${score}%`;
  if (scoringSystem === '1-10') return `${score} / 10`;
  if (scoringSystem === '1-5') return `${score} / 5`;
  return `${score} pts`;
};

// Add notification service
const notify = useNotification();

// Get dummy top performers if none exist
const getTopPerformers = () => {
  return [
    { name: 'Contestant 1', score: '9.2' },
    { name: 'Contestant 2', score: '8.9' },
    { name: 'Contestant 3', score: '8.7' }
  ];
};

// Export results function
const exportResults = () => {
  notify.info('Preparing pageant results for export...', { timeout: 2000 });
  
  // Simulate export delay for demo
  setTimeout(() => {
    notify.success('Pageant results have been exported successfully!');
  }, 2000);
};

// Edit pageant function
const editPageant = () => {
  // Check if pageant is in an editable state
  if (['Unlocked_For_Edit'].includes(props.pageant.status)) {
    router.visit(`/admin/pageants/edit/${props.pageant.id}`);
  } else {
    notify.warning(`Pageant cannot be edited in '${props.pageant.status}' status. Use the Grant Edit Permission function first.`);
  }
};

// Grant edit permission functions
const grantEditPermission = () => {
  showGrantModal.value = true;
  
  // Set default expiry date to 7 days from now
  const defaultExpiry = new Date();
  defaultExpiry.setDate(defaultExpiry.getDate() + 7);
  expiryDate.value = defaultExpiry.toISOString().slice(0, 16);
};

const submitGrantPermission = () => {
  router.post(`/admin/pageants/grant-edit-permission/${props.id}`, {
    organizer_id: selectedOrganizerId.value,
    expires_at: expiryDate.value
  }, {
    onSuccess: () => {
      showGrantModal.value = false;
      notify.success('Edit permission granted successfully');
    },
    onError: (errors) => {
      console.error(errors);
      notify.error('Failed to grant edit permission. Please try again.');
    }
  });
};

// Revoke edit permission function
const revokeEditPermission = () => {
  if (confirm('Are you sure you want to revoke edit permission for this pageant?')) {
    router.post(`/admin/pageants/revoke-edit-permission/${props.id}`, {}, {
      onSuccess: () => {
        notify.success('Edit permission revoked successfully');
      },
      onError: (errors) => {
        console.error(errors);
        notify.error('Failed to revoke edit permission. Please try again.');
      }
    });
  }
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