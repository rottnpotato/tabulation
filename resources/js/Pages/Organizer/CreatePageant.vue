<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <!-- Page Header -->
    <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-teal-100">
      <!-- Abstract Background Pattern -->
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-teal-50 via-teal-50/50 to-white opacity-90"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
      </div>

      <div class="relative z-10 p-8">
        <div class="flex items-center justify-between">
          <div class="space-y-2">
            <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">Create New Pageant</h1>
            <p class="text-slate-500 text-lg font-light">Submit a new pageant for admin approval</p>
          </div>
          <div class="bg-white/60 backdrop-blur-md border border-teal-100 rounded-2xl p-4 shadow-sm">
            <Crown class="h-8 w-8 text-teal-600" />
          </div>
        </div>
      </div>
    </div>

    <!-- Creation Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="p-6 border-b border-gray-100">
        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
          <FileText class="h-5 w-5 mr-2 text-teal-500" />
          Pageant Information
        </h2>
        <p class="mt-1 text-sm text-gray-600">
          Provide details about your pageant. It will be submitted for admin approval before you can start managing it.
        </p>
      </div>

      <form @submit.prevent="submitForm" class="p-6 space-y-6">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Pageant Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Pageant Name *
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
              placeholder="Enter pageant name"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Scoring System -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Scoring System *
            </label>
            <CustomSelect
              v-model="form.scoring_system"
              :options="scoringSystemOptions"
              placeholder="Select scoring system"
              variant="teal"
            />
            <p v-if="errors.scoring_system" class="mt-1 text-sm text-red-600">{{ errors.scoring_system }}</p>
          </div>

          <!-- Contestant Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Contestant Type *
            </label>
            <CustomSelect
              v-model="form.contestant_type"
              :options="contestantTypeOptions"
              placeholder="Select contestant type"
              variant="teal"
            />
            <p v-if="errors.contestant_type" class="mt-1 text-sm text-red-600">{{ errors.contestant_type }}</p>
            <p class="mt-1 text-sm text-gray-500">Choose what type of contestants can participate in this pageant</p>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
            placeholder="Describe your pageant, its theme, and objectives..."
            :class="{ 'border-red-500': errors.description }"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
        </div>

        <!-- Pageant Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Start Date -->
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
              Start Date
            </label>
            <input
              id="start_date"
              v-model="form.start_date"
              type="date"
              :min="todayDate"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
              :class="{ 'border-red-500': errors.start_date }"
            />
            <p v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</p>
            <p v-else class="mt-1 text-sm text-gray-500">Cannot select a date before today</p>
          </div>

          <!-- End Date -->
          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
              End Date
            </label>
            <input
              id="end_date"
              v-model="form.end_date"
              type="date"
              :min="form.start_date || todayDate"
              :disabled="!form.start_date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
              :class="{ 'border-red-500': errors.end_date }"
            />
            <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</p>
            <p v-else-if="!form.start_date" class="mt-1 text-sm text-gray-500">Please select a start date first</p>
          </div>
        </div>

          <!-- Pageant Date -->
        <div>
          <label for="pageant_date" class="block text-sm font-medium text-gray-700 mb-2">
            Main Pageant Date *
          </label>
          <input
            id="pageant_date"
            v-model="form.pageant_date"
            type="date"
            :min="todayDate"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
            :class="{ 'border-red-500': errors.pageant_date }"
            required
          />
          <p v-if="errors.pageant_date" class="mt-1 text-sm text-red-600">{{ errors.pageant_date }}</p>
          <p class="mt-1 text-sm text-gray-500">The main competition date for this pageant</p>
        </div>        <!-- Location Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Venue -->
          <div>
            <label for="venue" class="block text-sm font-medium text-gray-700 mb-2">
              Venue
            </label>
            <input
              id="venue"
              v-model="form.venue"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
              placeholder="Pageant venue name"
              :class="{ 'border-red-500': errors.venue }"
            />
            <p v-if="errors.venue" class="mt-1 text-sm text-red-600">{{ errors.venue }}</p>
          </div>

          <!-- Location -->
          <div class="relative">
            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
              Location (Bohol Only) *
            </label>
            <input
              id="location"
              v-model="locationInput"
              @input="handleLocationInput"
              @blur="handleLocationBlur"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
              placeholder="Start typing a municipality/city in Bohol..."
              :class="{ 'border-red-500': errors.location }"
              autocomplete="off"
            />
            
            <!-- Suggestions Dropdown -->
            <div
              v-if="showLocationSuggestions && filteredLocations.length > 0"
              class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
            >
              <button
                v-for="(location, index) in filteredLocations"
                :key="index"
                type="button"
                @click="selectLocation(location)"
                class="w-full px-3 py-2 text-left hover:bg-teal-50 transition-colors border-b border-gray-100 last:border-b-0"
              >
                <div class="font-medium text-gray-900">{{ location }}</div>
                <div class="text-xs text-gray-500">Bohol, Philippines</div>
              </button>
            </div>
            
            <p v-if="errors.location" class="mt-1 text-sm text-red-600">{{ errors.location }}</p>
            <p v-else class="mt-1 text-sm text-gray-500">Only locations within Bohol province are accepted</p>
          </div>
        </div>

        <!-- Approval Notice -->
        <div class="bg-teal-50 border border-teal-200 rounded-lg p-4">
          <div class="flex items-start">
            <AlertCircle class="h-5 w-5 text-teal-500 mr-3 mt-0.5 flex-shrink-0" />
            <div>
              <h3 class="text-sm font-medium text-teal-800">Approval Required</h3>
              <p class="mt-1 text-sm text-teal-700">
                Your pageant will be submitted for admin approval. Once approved, you'll be able to manage 
                contestants, criteria, judges, and other pageant details. You'll receive a notification when 
                the status changes.
              </p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-100">
          <button
            type="submit"
            :disabled="isSubmitting"
            class="flex-1 bg-teal-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-teal-700 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
          >
            <template v-if="isSubmitting">
              <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
              Submitting for Approval...
            </template>
            <template v-else>
              <Send class="h-4 w-4 mr-2" />
              Submit for Approval
            </template>
          </button>
          
          <Link
            :href="route('organizer.dashboard')"
            class="flex-1 sm:flex-none bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors text-center"
          >
            Cancel
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import CustomSelect from '@/Components/CustomSelect.vue'
import { useNotification } from '@/Composables/useNotification'
import { 
  Crown, FileText, AlertCircle, Send, 
  Calendar, MapPin 
} from 'lucide-vue-next'

defineOptions({
  layout: OrganizerLayout
})

// Composables
const notify = useNotification()

// Get today's date in YYYY-MM-DD format
const todayDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

// Bohol municipalities and cities
const boholLocations = [
  'Tagbilaran City',
  'Alburquerque',
  'Alicia',
  'Anda',
  'Antequera',
  'Baclayon',
  'Balilihan',
  'Batuan',
  'Bien Unido',
  'Bilar',
  'Buenavista',
  'Calape',
  'Candijay',
  'Carmen',
  'Catigbian',
  'Clarin',
  'Corella',
  'Cortes',
  'Dagohoy',
  'Danao',
  'Dauis',
  'Dimiao',
  'Duero',
  'Garcia Hernandez',
  'Getafe',
  'Guindulman',
  'Inabanga',
  'Jagna',
  'Lila',
  'Loay',
  'Loboc',
  'Loon',
  'Mabini',
  'Maribojoc',
  'Panglao',
  'Pilar',
  'President Carlos P. Garcia',
  'Sagbayan',
  'San Isidro',
  'San Miguel',
  'Sevilla',
  'Sierra Bullones',
  'Sikatuna',
  'Talibon',
  'Trinidad',
  'Tubigon',
  'Ubay',
  'Valencia'
]

// Location input handling
const locationInput = ref('')
const showLocationSuggestions = ref(false)

const filteredLocations = computed(() => {
  if (!locationInput.value) return []
  
  const searchTerm = locationInput.value.toLowerCase()
  return boholLocations.filter(location => 
    location.toLowerCase().includes(searchTerm)
  )
})

const handleLocationInput = () => {
  showLocationSuggestions.value = true
  // Clear the form location if user is typing
  if (form.location !== locationInput.value) {
    form.location = ''
  }
}

const handleLocationBlur = () => {
  // Delay to allow click on suggestion
  setTimeout(() => {
    showLocationSuggestions.value = false
    
    // Validate if the entered location is in Bohol
    if (locationInput.value && !boholLocations.includes(locationInput.value)) {
      errors.value.location = 'Please select a valid location from Bohol'
      form.location = ''
    }
  }, 200)
}

const selectLocation = (location) => {
  locationInput.value = location
  form.location = location
  showLocationSuggestions.value = false
  
  // Clear location error if exists
  if (errors.value.location) {
    delete errors.value.location
  }
}

// Form state
const form = reactive({
  name: '',
  description: '',
  start_date: '',
  end_date: '',
  pageant_date: '',
  venue: '',
  location: '',
  scoring_system: '',
  contestant_type: 'both'
})

const errors = ref({})
const isSubmitting = ref(false)

// Options for scoring system
const scoringSystemOptions = [
  { value: 'percentage', label: 'Percentage (0-100%)' },
  { value: '1-10', label: 'Scale 1-10' },
  { value: '1-5', label: 'Scale 1-5' },
  { value: 'points', label: 'Points (0-50)' }
]

// Options for contestant type
const contestantTypeOptions = [
  { value: 'solo', label: 'Solo Contestants Only' },
  { value: 'pairs', label: 'Pairs Only (Mr & Ms)' },
  { value: 'both', label: 'Both Solo and Pairs' }
]

// Submit form
const submitForm = () => {
  // Reset errors
  errors.value = {}
  
  // Validate required fields
  if (!form.name) {
    errors.value.name = 'Pageant name is required'
  }
  
  if (!form.scoring_system) {
    errors.value.scoring_system = 'Scoring system is required'
  }
  
  if (!form.contestant_type) {
    errors.value.contestant_type = 'Contestant type is required'
  }
  
  if (!form.pageant_date) {
    errors.value.pageant_date = 'Main pageant date is required'
  }
  
  // Validate pageant date is not in the past
  if (form.pageant_date && new Date(form.pageant_date) < new Date(todayDate.value)) {
    errors.value.pageant_date = 'Pageant date cannot be in the past'
  }
  
  // Validate start date is not in the past (if provided)
  if (form.start_date && new Date(form.start_date) < new Date(todayDate.value)) {
    errors.value.start_date = 'Start date cannot be in the past'
  }
  
  // Validate location is from Bohol
  if (locationInput.value && !boholLocations.includes(locationInput.value)) {
    errors.value.location = 'Please select a valid location from Bohol'
  }
  
  // Validate dates
  if (form.start_date && form.end_date && new Date(form.end_date) < new Date(form.start_date)) {
    errors.value.end_date = 'End date must be after start date'
  }
  
  // If there are validation errors, stop submission
  if (Object.keys(errors.value).length > 0) {
    notify.error('Please fix the errors in the form before submitting')
    return
  }
  
  // Submit the form
  isSubmitting.value = true
  
  router.post(route('organizer.pageants.store'), form, {
    onSuccess: () => {
      notify.success(`Pageant "${form.name}" has been submitted for approval!`)
      isSubmitting.value = false
    },
    onError: (validationErrors) => {
      errors.value = validationErrors
      isSubmitting.value = false
      notify.error('There was an error submitting your pageant. Please check the form.')
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
</script>
<style scoped>
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
