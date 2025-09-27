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
            
            <!-- For pairs-only pageants, show pair creation as primary action -->
            <template v-if="isPairsOnly">
              <Tooltip text="Create a new pair entry for this pairs-only pageant" position="bottom">
                <button
                  @click="openPairCreationModal"
                  class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105 flex items-center"
                >
                  <Users class="h-4 w-4 mr-2" />
                  Add Pair
                </button>
              </Tooltip>
            </template>
            
            <!-- For solo-only pageants, show individual creation -->
            <template v-else-if="isSoloOnly">
              <Tooltip text="Add a new individual contestant for this solo pageant" position="bottom">
                <button
                  @click="openIndividualCreationModal"
                  class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105 flex items-center"
                >
                  <Plus class="h-4 w-4 mr-2" />
                  Add Contestant
                </button>
              </Tooltip>
            </template>
            
            <!-- For mixed pageants, show toggle options -->
            <template v-else-if="allowsBothTypes">
              <div class="flex rounded-lg border border-gray-300 bg-white shadow-sm">
                <Tooltip text="Add an individual contestant" position="bottom">
                  <button
                    @click="openIndividualCreationModal"
                    class="px-4 py-2 text-sm font-medium transition-all flex items-center rounded-l-lg border-r border-gray-300"
                    :class="selectedCreationType === 'individual' ? 'text-white bg-orange-500 hover:bg-orange-600' : 'text-gray-700 hover:bg-gray-50'"
                  >
                    <Plus class="h-4 w-4 mr-2" />
                    Add Individual
                  </button>
                </Tooltip>
                <Tooltip text="Create a pair entry" position="bottom">
                  <button
                    @click="openPairCreationModal"
                    class="px-4 py-2 text-sm font-medium transition-all flex items-center rounded-r-lg"
                    :class="selectedCreationType === 'pair' ? 'text-white bg-emerald-500 hover:bg-emerald-600' : 'text-gray-700 hover:bg-gray-50'"
                  >
                    <Users class="h-4 w-4 mr-2" />
                    Add Pair
                  </button>
                </Tooltip>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Pageant Type Info Message -->
    <div v-if="!allowsBothTypes" class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg shadow-sm">
      <div class="flex items-start">
        <Users v-if="isPairsOnly" class="h-5 w-5 mr-3 text-blue-500 mt-0.5 flex-shrink-0" />
        <Plus v-else class="h-5 w-5 mr-3 text-blue-500 mt-0.5 flex-shrink-0" />
        <div>
          <p class="font-semibold">{{ isPairsOnly ? 'Pairs Only Pageant' : 'Solo Only Pageant' }}</p>
          <p v-if="isPairsOnly" class="mt-1 text-sm text-blue-600">
            This pageant only accepts paired contestants (Mr & Ms style). Create pair entries directly using the pair creation form.
          </p>
          <p v-else class="mt-1 text-sm text-blue-600">
            This pageant only accepts individual solo contestants. Each contestant competes independently.
          </p>
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
          <h3 class="text-lg font-medium text-gray-900 mb-1">
            {{ isPairsOnly ? 'No contestants or pairs yet' : 'No contestants in this pageant yet' }}
          </h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            <span v-if="isPairsOnly">
              Start building your pairs competition by adding individual contestants to <strong>{{ pageant.name }}</strong>, 
              then create pairs from them. Each pair will compete as a unit (Mr & Ms style).
            </span>
            <span v-else-if="isSoloOnly">
              Start building your solo competition by adding individual contestants to <strong>{{ pageant.name }}</strong>. 
              Each contestant will compete independently.
            </span>
            <span v-else>
              Start building your competition by adding contestants to <strong>{{ pageant.name }}</strong>. 
              You can add both individual contestants and pairs for this mixed pageant.
            </span>
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <!-- For pairs-only pageants -->
            <template v-if="isPairsOnly">
              <Tooltip text="Create your first pair entry for this pairs-only pageant" position="top">
                <button
                  @click="openPairCreationModal"
                  class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105"
                >
                  <Users class="h-4 w-4 inline mr-2" />
                  Add First Pair
                </button>
              </Tooltip>
            </template>
            
            <!-- For solo-only pageants -->
            <template v-else-if="isSoloOnly">
              <Tooltip text="Add your first contestant to this solo pageant" position="top">
                <button
                  @click="openIndividualCreationModal"
                  class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105"
                >
                  <Plus class="h-4 w-4 inline mr-2" />
                  Add First Contestant
                </button>
              </Tooltip>
            </template>
            
            <!-- For mixed pageants -->
            <template v-else-if="allowsBothTypes">
              <div class="flex flex-col sm:flex-row gap-3">
                <Tooltip text="Add an individual contestant" position="top">
                  <button
                    @click="openIndividualCreationModal"
                    class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105"
                  >
                    <Plus class="h-4 w-4 inline mr-2" />
                    Add Individual
                  </button>
                </Tooltip>
                <Tooltip text="Create a pair entry" position="top">
                  <button
                    @click="openPairCreationModal"
                    class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 rounded-lg shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-0.5 hover:scale-105"
                  >
                    <Users class="h-4 w-4 inline mr-2" />
                    Add Pair
                  </button>
                </Tooltip>
              </div>
            </template>
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
        <ContestantsGrid
          v-else
          :contestants="filteredContestants"
          @view="openContestantDetail"
          @edit="editContestant"
          @delete="deleteContestant"
        />
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
      :pageant="pageant"
      :contestant="contestantToEdit"
      :mode="contestantModalMode"
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
    <!-- Remove the old PairFormModal as we're now using ContestantFormModal for pairs -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, Plus, Users, Search, MapPin, Calendar, Edit, Trash2, Eye, CheckCircle, X, AlertCircle } from 'lucide-vue-next'
import ContestantFormModal from '@/Components/ContestantFormModal.vue'
import ContestantsGrid from '@/Components/ContestantsGrid.vue'
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
const contestantModalMode = ref('individual') // 'individual' or 'pair'
const selectedCreationType = ref('individual')
const showDetailModal = ref(false)
const showDeleteModal = ref(false)
const contestantToEdit = ref(null)
const selectedContestant = ref(null)
const contestantToDelete = ref(null)
const isDeleting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Image loading states
const imageLoadingStates = ref({})

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

// Computed properties for contestant type restrictions
const allowsSoloContestants = computed(() => {
  // Individual contestants can be added to all pageant types:
  // - solo: they compete individually
  // - pairs: they're needed to create pairs later
  // - both: they can compete solo or be used for pairs
  return ['solo', 'pairs', 'both'].includes(props.pageant.contestant_type)
})

const allowsPairContestants = computed(() => {
  return ['pairs', 'both'].includes(props.pageant.contestant_type)
})

const allowsBothTypes = computed(() => {
  return props.pageant.contestant_type === 'both'
})

const isPairsOnly = computed(() => {
  return props.pageant.contestant_type === 'pairs'
})

const isSoloOnly = computed(() => {
  return props.pageant.contestant_type === 'solo'
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
  contestantModalMode.value = 'individual' // Always individual for editing existing contestants
  selectedCreationType.value = 'individual'
  showAddModal.value = true
  
  // If the detail modal is open, close it
  if (showDetailModal.value) {
    showDetailModal.value = false
  }
}

const closeAddModal = () => {
  showAddModal.value = false
  contestantToEdit.value = null
  contestantModalMode.value = 'individual'
  selectedCreationType.value = 'individual'
}

const openIndividualCreationModal = () => {
  contestantModalMode.value = 'individual'
  selectedCreationType.value = 'individual'
  contestantToEdit.value = null
  showAddModal.value = true
}

const openPairCreationModal = () => {
  contestantModalMode.value = 'pair'
  selectedCreationType.value = 'pair'
  contestantToEdit.value = null
  showAddModal.value = true
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

// Image handling functions
const handleImageError = (event) => {
  const img = event.target
  const contestantId = img.closest('[data-contestant-id]')?.dataset.contestantId
  if (contestantId) {
    imageLoadingStates.value[contestantId] = 'error'
  }
  // Set a fallback image
  img.src = '/images/placeholders/placeholder-contestant.jpg'
}

const handleImageLoad = (event) => {
  const img = event.target
  const contestantId = img.closest('[data-contestant-id]')?.dataset.contestantId
  if (contestantId) {
    imageLoadingStates.value[contestantId] = 'loaded'
  }
}
</script> 