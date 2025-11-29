<template>
  <div class="space-y-6">
    <!-- Back Button and Page Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center space-y-3 md:space-y-0">
      <Link 
        :href="route('organizer.pageant.view', pageant.id)"
        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition-colors btn-transition w-max"
      >
        <ChevronLeft class="h-4 w-4 mr-1.5" />
        Back to Pageant View
      </Link>
      
      <div class="flex items-center space-x-2">
        <h1 class="text-xl font-bold text-gray-900">Edit Pageant</h1>
      </div>
    </div>

    <!-- Locked Pageant Banner -->
    <PageantLockedBanner 
      :pageant="pageant" 
      :has-pending-request="hasPendingEditRequest"
      @request-edit-access="showEditAccessDialog = true"
    />

    <!-- Edit Access Request Dialog -->
    <EditAccessRequestDialog 
      :open="showEditAccessDialog"
      :pageant-id="pageant.id"
      @close="showEditAccessDialog = false"
    />
    
    <!-- Main Content -->
    <div class="bg-white rounded-xl shadow-md">
      <div class="p-6">
        <form @submit.prevent="submitForm" enctype="multipart/form-data">
          <div class="space-y-6">
            <!-- Pageant Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Pageant Name</label>
              <input 
                id="name" 
                v-model="form.name" 
                type="text" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                :disabled="isEditingLocked"
                required
              />
            </div>
            
            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
              <textarea 
                id="description" 
                v-model="form.description" 
                rows="3" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                :disabled="isEditingLocked"
              ></textarea>
            </div>
            
            <!-- Images -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Cover Image -->
              <div>
                <label for="cover_image" class="block text-sm font-medium text-gray-700">Cover Image</label>
                <div class="mt-2 flex flex-col space-y-2">
                  <div v-if="coverImagePreview || pageant.coverImage" class="relative w-full h-48 overflow-hidden rounded-lg">
                    <img 
                      :src="coverImagePreview || pageant.coverImage" 
                      alt="Cover image preview" 
                      class="w-full h-full object-cover"
                    />
                    <button 
                      v-if="coverImagePreview || form.cover_image" 
                      type="button" 
                      @click="removeCoverImage"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition"
                    >
                      <XIcon class="h-4 w-4" />
                    </button>
                  </div>
                  <input
                    id="cover_image"
                    ref="coverImageInput"
                    type="file"
                    @change="handleCoverImageChange"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                    accept="image/*"
                    :disabled="isEditingLocked"
                  />
                  <p class="text-xs text-gray-500">Recommended size: 1200x400px. Max 2MB. JPG, PNG, or GIF format.</p>
                </div>
              </div>
              
              <!-- Logo -->
              <div>
                <label for="logo" class="block text-sm font-medium text-gray-700">Pageant Logo</label>
                <div class="mt-2 flex flex-col space-y-2">
                  <div v-if="logoPreview || pageant.logo" class="relative w-48 h-48 overflow-hidden rounded-lg bg-gray-50 p-2 mx-auto">
                    <img 
                      :src="logoPreview || pageant.logo" 
                      alt="Logo preview" 
                      class="w-full h-full object-contain"
                    />
                    <button 
                      v-if="logoPreview || form.logo" 
                      type="button" 
                      @click="removeLogo"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition"
                    >
                      <XIcon class="h-4 w-4" />
                    </button>
                  </div>
                  <input
                    id="logo"
                    ref="logoInput"
                    type="file"
                    @change="handleLogoChange"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                    accept="image/*"
                    :disabled="isEditingLocked"
                  />
                  <p class="text-xs text-gray-500">Recommended size: 400x400px. Max 2MB. JPG, PNG, or GIF format.</p>
                </div>
              </div>
            </div>
            
            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input 
                  id="start_date" 
                  v-model="form.start_date" 
                  type="date" 
                  :min="today"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                  :disabled="isEditingLocked"
                />
              </div>
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input 
                  id="end_date" 
                  v-model="form.end_date" 
                  type="date"
                  :min="form.start_date || today"
                  :disabled="!form.start_date || isEditingLocked"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                />
                <p v-if="!form.start_date" class="mt-1 text-sm text-gray-500">Please select a start date first</p>
              </div>
            </div>

            <!-- Time Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time (Optional)</label>
                <input 
                  id="start_time" 
                  v-model="form.start_time" 
                  type="time" 
                  :disabled="!form.start_date || isEditingLocked"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                />
                <p class="mt-1 text-sm text-gray-500">Scoring starts at this time</p>
              </div>
              <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time (Optional)</label>
                <input 
                  id="end_time" 
                  v-model="form.end_time" 
                  type="time"
                  :disabled="!form.end_date || isEditingLocked"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                />
                <p class="mt-1 text-sm text-gray-500">Scoring ends at this time</p>
              </div>
            </div>
            
            <!-- Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="relative">
                <label for="venue" class="block text-sm font-medium text-gray-700">Venue</label>
                <input 
                  id="venue" 
                  v-model="form.venue" 
                  type="text" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                  :disabled="isEditingLocked"
                  @focus="showVenueSuggestions = true"
                  @blur="hideVenueSuggestions"
                />
                <div 
                  v-if="showVenueSuggestions && filteredVenues.length > 0" 
                  class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                >
                  <div
                    v-for="venue in filteredVenues"
                    :key="venue"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-teal-50"
                    @mousedown.prevent="selectVenue(venue)"
                  >
                    {{ venue }}
                  </div>
                </div>
              </div>
              <div class="relative">
                <label for="location" class="block text-sm font-medium text-gray-700">Location/City</label>
                <input 
                  id="location" 
                  v-model="form.location" 
                  type="text" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                  :disabled="isEditingLocked"
                  @focus="showLocationSuggestions = true"
                  @blur="hideLocationSuggestions"
                />
                <div 
                  v-if="showLocationSuggestions && filteredLocations.length > 0" 
                  class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                >
                  <div
                    v-for="location in filteredLocations"
                    :key="location"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-teal-50"
                    @mousedown.prevent="selectLocation(location)"
                  >
                    {{ location }}
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Form Actions -->
            <div class="mt-6 flex justify-end space-x-3">
              <Link
                :href="route('organizer.pageant.view', pageant.id)"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
              >
                Cancel
              </Link>
              <button
                type="submit"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="processing || isEditingLocked"
              >
                <div v-if="processing" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </div>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ChevronLeft, X as XIcon } from 'lucide-vue-next'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import PageantLockedBanner from '@/Components/PageantLockedBanner.vue'
import EditAccessRequestDialog from '@/Components/EditAccessRequestDialog.vue'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  hasPendingEditRequest: {
    type: Boolean,
    default: false
  }
})

// Dialog state
const showEditAccessDialog = ref(false)

// Check if editing is locked
const isEditingLocked = computed(() => {
  // Completed or Archived pageants cannot be edited
  if (props.pageant.status === 'Completed' || props.pageant.status === 'Archived') {
    return true
  }
  // Ongoing pageants can only be edited if temporarily enabled
  return props.pageant.status === 'Ongoing' && !props.pageant.is_temporarily_editable
})

// Today's date for min date validation
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Location and venue suggestions
const boholLocations = [
  'Tagbilaran City',
  'Baclayon',
  'Panglao',
  'Dauis',
  'Cortes',
  'Corella',
  'Balilihan',
  'Loon',
  'Maribojoc',
  'Antequera',
  'Loboc',
  'Loay',
  'Sikatuna',
  'Alburquerque',
  'Sevilla',
  'Catigbian',
  'Batuan',
  'Carmen',
  'Sagbayan',
  'Tubigon',
  'Clarin',
  'Calape',
  'Inabanga',
  'Buenavista',
  'Getafe',
  'Trinidad',
  'Talibon',
  'Bien Unido',
  'San Miguel',
  'Ubay',
  'Alicia',
  'Mabini',
  'Candijay',
  'Anda',
  'Guindulman',
  'Duero',
  'Jagna',
  'Garcia Hernandez',
  'Valencia',
  'Dimiao',
  'Lila',
  'Bilar',
  'Sierra Bullones',
  'Pilar',
  'San Isidro',
  'Danao',
  'Dagohoy'
]

const boholVenues = [
  'Bohol Cultural Center',
  'Island City Mall',
  'BQ Mall',
  'Alturas Mall',
  'Tagbilaran City Plaza',
  'Holy Name University Gym',
  'University of Bohol Gym',
  'Blessed Trinity School Gym',
  'Panglao Sports Complex',
  'Alona Beach',
  'Dumaluan Beach',
  'Henann Resort Alona Beach',
  'Amorita Resort',
  'Bellevue Resort Bohol',
  'South Palms Resort',
  'The Bellevue Resort',
  'Bohol Beach Club',
  'Eskaya Beach Resort',
  'The Peacock Garden',
  'Mithi Resort and Spa',
  'Loboc River Cruise',
  'Chocolate Hills Complex',
  'Man-made Forest Park',
  'Baclayon Church',
  'Dauis Church',
  'Maribojoc Church',
  'Loon Church',
  'Balilihan Gymnasium',
  'Provincial Capitol Ground',
  'Tagbilaran City Auditorium'
]

const showLocationSuggestions = ref(false)
const showVenueSuggestions = ref(false)

const filteredLocations = computed(() => {
  if (!form.location) return boholLocations
  const search = form.location.toLowerCase()
  return boholLocations.filter(loc => loc.toLowerCase().includes(search))
})

const filteredVenues = computed(() => {
  if (!form.venue) return boholVenues
  const search = form.venue.toLowerCase()
  return boholVenues.filter(venue => venue.toLowerCase().includes(search))
})

const selectLocation = (location) => {
  form.location = location
  showLocationSuggestions.value = false
}

const selectVenue = (venue) => {
  form.venue = venue
  showVenueSuggestions.value = false
}

const hideLocationSuggestions = () => {
  setTimeout(() => {
    showLocationSuggestions.value = false
  }, 200)
}

const hideVenueSuggestions = () => {
  setTimeout(() => {
    showVenueSuggestions.value = false
  }, 200)
}

// Form state
const processing = ref(false)
const coverImagePreview = ref(null)
const logoPreview = ref(null)
const coverImageInput = ref(null)
const logoInput = ref(null)

// Initialize form with pageant data
const form = useForm({
  name: props.pageant.name,
  description: props.pageant.description || '',
  start_date: props.pageant.start_date || '',
  start_time: props.pageant.start_time || '',
  end_date: props.pageant.end_date || '',
  end_time: props.pageant.end_time || '',
  venue: props.pageant.venue || '',
  location: props.pageant.location || '',
  cover_image: null,
  logo: null,
})

// Handle cover image change
const handleCoverImageChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  
  form.cover_image = file
  createCoverImagePreview(file)
}

// Handle logo change
const handleLogoChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  
  form.logo = file
  createLogoPreview(file)
}

// Create image previews
const createCoverImagePreview = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    coverImagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const createLogoPreview = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    logoPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

// Remove images
const removeCoverImage = () => {
  form.cover_image = null
  coverImagePreview.value = null
  if (coverImageInput.value) {
    coverImageInput.value.value = ''
  }
}

const removeLogo = () => {
  form.logo = null
  logoPreview.value = null
  if (logoInput.value) {
    logoInput.value.value = ''
  }
}

// Submit form
const submitForm = () => {
  processing.value = true
  
  form.post(route('organizer.pageant.update', props.pageant.id), {
    onSuccess: () => {
      processing.value = false
    },
    onError: () => {
      processing.value = false
    },
    forceFormData: true
  })
}
</script> 
