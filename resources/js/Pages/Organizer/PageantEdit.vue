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
    
    <!-- Main Content -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
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
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                />
              </div>
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input 
                  id="end_date" 
                  v-model="form.end_date" 
                  type="date"
                  :min="form.start_date"
                  :disabled="!form.start_date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                />
                <p v-if="!form.start_date" class="mt-1 text-sm text-gray-500">Please select a start date first</p>
              </div>
            </div>
            
            <!-- Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="venue" class="block text-sm font-medium text-gray-700">Venue</label>
                <input 
                  id="venue" 
                  v-model="form.venue" 
                  type="text" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                />
              </div>
              <div>
                <label for="location" class="block text-sm font-medium text-gray-700">Location/City</label>
                <input 
                  id="location" 
                  v-model="form.location" 
                  type="text" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                />
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
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                :disabled="processing"
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
import { ref, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ChevronLeft, X as XIcon } from 'lucide-vue-next'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  }
})

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
  start_date: props.pageant.start_date ? new Date(props.pageant.start_date).toISOString().substr(0, 10) : '',
  end_date: props.pageant.end_date ? new Date(props.pageant.end_date).toISOString().substr(0, 10) : '',
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
