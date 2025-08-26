<template>
  <div class="space-y-6">
    <!-- Header Section with Gradient Background -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-orange-100">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-800">{{ pageant.name }}</h1>
            <p class="mt-1 text-sm text-gray-600">Manage contestants for this pageant</p>
          </div>
          <div class="flex gap-3">
            <Tooltip text="Return to pageant overview and management" position="bottom">
              <Link
                :href="route('organizer.pageant.view', pageant.id)"
                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-lg transition-all shadow-sm hover:shadow-md transform hover:-translate-y-0.5 flex items-center"
              >
                <ArrowLeft class="h-4 w-4 mr-2 text-orange-500" />
                Back to Pageant
              </Link>
            </Tooltip>
            <Tooltip text="Register a new contestant for this pageant" position="bottom">
              <button
                @click="showAddModal = true"
                class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105 flex items-center"
              >
                <Plus class="h-4 w-4 mr-2" />
                Add Contestant
              </button>
            </Tooltip>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm flex items-center justify-between transition-all duration-500 ease-in-out">
      <div class="flex items-center">
        <CheckCircle class="h-5 w-5 mr-2 text-green-500" />
        <p>{{ successMessage }}</p>
      </div>
      <button @click="successMessage = ''" class="text-green-700 hover:text-green-900">
        <X class="h-5 w-5" />
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm flex items-center justify-between transition-all duration-500 ease-in-out">
      <div class="flex items-center">
        <AlertCircle class="h-5 w-5 mr-2 text-red-500" />
        <p>{{ errorMessage }}</p>
      </div>
      <button @click="errorMessage = ''" class="text-red-700 hover:text-red-900">
        <X class="h-5 w-5" />
      </button>
    </div>

    <!-- Contestant List -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
      <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <Users class="h-5 w-5 mr-2 text-orange-500" />
            Contestants 
            <span class="ml-2 text-gray-500 text-sm font-normal">({{ contestants.length }})</span>
          </h2>
          
          <!-- Search and filter controls -->
          <Tooltip text="Search by name, location, or contestant number" position="bottom">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size="16" />
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search contestants..." 
                class="pl-10 pr-4 py-2 rounded-lg border-gray-300 focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-all shadow-sm hover:shadow-md"
              />
            </div>
          </Tooltip>
        </div>
      </div>

      <div class="p-6">
        <!-- No contestants message -->
        <div v-if="contestants.length === 0" class="text-center py-12">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-orange-100 to-orange-200 mb-4">
            <Users class="h-12 w-12 text-orange-500" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-1">No contestants in this pageant yet</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Start building your competition by adding contestants to <strong>{{ pageant.name }}</strong>. 
            Each contestant will be registered specifically for this pageant.
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <Tooltip text="Add contestants to this specific pageant" position="top">
              <button
                @click="showAddModal = true"
                class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105"
              >
                <Plus class="h-4 w-4 inline mr-2" />
                Add First Contestant
              </button>
            </Tooltip>
            <Tooltip text="Return to pageant overview to manage other settings" position="top">
              <Link
                :href="route('organizer.pageant.view', pageant.id)"
                class="px-4 py-2 text-sm font-medium text-orange-700 hover:text-orange-900 bg-orange-50 border border-orange-200 hover:border-orange-300 rounded-lg transition-all shadow-sm hover:shadow-md flex items-center"
              >
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to Pageant Setup
              </Link>
            </Tooltip>
          </div>
        </div>

        <!-- Contestants Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div 
            v-for="contestant in filteredContestants" 
            :key="contestant.id" 
            class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group"
          >
            <div class="relative h-64">
              <img 
                :src="contestant.photo || '/placeholder-contestant.jpg'" 
                :alt="contestant.name" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <!-- Gradient overlay -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-70"></div>
              
              <!-- Contestant number badge -->
              <div class="absolute top-3 right-3">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white text-sm font-bold shadow-md">
                  #{{ contestant.number }}
                </span>
              </div>
              
              <!-- Action buttons overlay - visible on hover -->
              <div class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                <Tooltip text="View detailed information, photos, and bio" position="top">
                  <button 
                    @click="openContestantDetail(contestant)" 
                    class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-600 transition-colors flex items-center gap-2 shadow-lg transform hover:scale-105"
                  >
                    <Eye class="h-4 w-4" />
                    View Details
                  </button>
                </Tooltip>
                <Tooltip text="Edit contestant information and photos" position="top">
                  <button 
                    @click="editContestant(contestant)" 
                    class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-white/30 transition-colors flex items-center gap-2 border border-white/30 transform hover:scale-105"
                  >
                    <Edit class="h-4 w-4" />
                    Edit
                  </button>
                </Tooltip>
                <Tooltip text="Permanently remove contestant from pageant" position="top">
                  <button 
                    @click="deleteContestant(contestant)" 
                    class="bg-red-500/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-500/30 transition-colors flex items-center gap-2 border border-red-500/30 transform hover:scale-105"
                  >
                    <Trash2 class="h-4 w-4" />
                    Delete
                  </button>
                </Tooltip>
              </div>
              
              <!-- Contestant info at bottom -->
              <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                <h3 class="text-lg font-bold truncate">{{ contestant.name }}</h3>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                  <div class="flex items-center text-sm text-orange-100">
                    <MapPin class="h-3.5 w-3.5 mr-1 text-orange-300" />
                    <span class="truncate">{{ contestant.origin || 'No location' }}</span>
                  </div>
                  <div class="flex items-center text-sm text-orange-100">
                    <Calendar class="h-3.5 w-3.5 mr-1 text-orange-300" />
                    <span>{{ contestant.age ? `${contestant.age} years` : 'Age not specified' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmDeleteModal 
      :show="showDeleteModal" 
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
      :title="`Delete ${contestantToDelete?.name}`"
      :message="`Are you sure you want to delete this contestant? This action cannot be undone and will remove all photos and data associated with ${contestantToDelete?.name}.`"
      :is-loading="isDeleting"
    />

    <!-- Contestant Form Modal -->
    <ContestantFormModal
      :show="showAddModal"
      :pageant-id="pageant.id"
      :contestant="contestantToEdit"
      @close="closeAddModal"
      @saved="handleContestantSaved"
    />

    <!-- Contestant Detail Modal -->
    <ContestantDetailModal
      :show="showDetailModal"
      :contestant="selectedContestant"
      @close="showDetailModal = false"
      @edit="editContestant"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, Plus, Users, Search, MapPin, Calendar, Edit, Trash2, Eye, CheckCircle, X, AlertCircle } from 'lucide-vue-next'
import ContestantFormModal from '@/Components/ContestantFormModal.vue'
import ContestantDetailModal from '@/Components/ContestantDetailModal.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import Tooltip from '@/Components/Tooltip.vue'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import axios from 'axios'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  }
})

const contestants = ref([])
const searchQuery = ref('')
const isLoading = ref(true)
const error = ref(null)

// Modal states
const showAddModal = ref(false)
const showDetailModal = ref(false)
const showDeleteModal = ref(false)
const contestantToEdit = ref(null)
const selectedContestant = ref(null)
const contestantToDelete = ref(null)
const isDeleting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Notification timeout
let messageTimeout = null

// Filtered contestants based on search query
const filteredContestants = computed(() => {
  if (!searchQuery.value) return contestants.value
  
  const query = searchQuery.value.toLowerCase()
  return contestants.value.filter(contestant => 
    contestant.name.toLowerCase().includes(query) || 
    (contestant.origin && contestant.origin.toLowerCase().includes(query)) ||
    String(contestant.number).includes(query)
  )
})

// Fetch contestants on mount
onMounted(async () => {
  await fetchContestants()
})

const fetchContestants = async () => {
  isLoading.value = true
  error.value = null
  
  try {
    const response = await axios.get(`/organizer/pageant/${props.pageant.id}/contestants`)
    contestants.value = response.data.contestants
  } catch (err) {
    console.error('Error fetching contestants:', err)
    error.value = 'Failed to load contestants. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const openContestantDetail = async (contestant) => {
  try {
    // Fetch full contestant details including images and scores
    const response = await axios.get(`/organizer/pageant/${props.pageant.id}/contestants/${contestant.id}`)
    selectedContestant.value = response.data.contestant
    showDetailModal.value = true
  } catch (err) {
    console.error('Error fetching contestant details:', err)
    // Still show modal with basic info we already have
    selectedContestant.value = contestant
    showDetailModal.value = true
  }
}

const editContestant = (contestant) => {
  contestantToEdit.value = contestant
  showAddModal.value = true
  
  // If the detail modal is open, close it
  if (showDetailModal.value) {
    showDetailModal.value = false
  }
}

const closeAddModal = () => {
  showAddModal.value = false
  contestantToEdit.value = null
}

const handleContestantSaved = async (savedContestant) => {
  await fetchContestants()
  
  // If we were editing a contestant that was also selected in the detail view,
  // update the selected contestant with the new data
  if (selectedContestant.value && selectedContestant.value.id === savedContestant.id) {
    selectedContestant.value = contestants.value.find(c => c.id === savedContestant.id)
  }
}

const deleteContestant = (contestant) => {
  contestantToDelete.value = contestant
  showDeleteModal.value = true
  
  // Clear any existing messages
  successMessage.value = ''
  errorMessage.value = ''
}

const confirmDelete = async () => {
  if (isDeleting.value) return
  
  isDeleting.value = true
  errorMessage.value = ''
  
  try {
    await axios.delete(`/organizer/pageant/${props.pageant.id}/contestants/${contestantToDelete.value.id}`)
    
    // Remove the deleted contestant from our list
    contestants.value = contestants.value.filter(c => c.id !== contestantToDelete.value.id)
    
    // If the deleted contestant was selected in the detail view, close it
    if (selectedContestant.value && selectedContestant.value.id === contestantToDelete.value.id) {
      showDetailModal.value = false
    }
    
    // Show success message
    successMessage.value = `Contestant ${contestantToDelete.value.name} was successfully deleted.`
    
    // Reset
    showDeleteModal.value = false
    contestantToDelete.value = null
    
    // Clear success message after 5 seconds
    if (messageTimeout) clearTimeout(messageTimeout)
    messageTimeout = setTimeout(() => {
      successMessage.value = ''
    }, 5000)
    
  } catch (error) {
    console.error('Error deleting contestant:', error)
    errorMessage.value = 'Failed to delete contestant. Please try again.'
    
    // Keep modal open to allow retry
  } finally {
    isDeleting.value = false
  }
}
</script> 