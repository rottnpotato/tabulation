<template>
  <Head title="Create New Pageant" />
  <div class="container mx-auto py-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-teal-50 to-teal-100">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-800">Create New Pageant</h1>
            <p class="mt-1 text-sm text-gray-600">Add a new pageant to the system and assign organizers</p>
          </div>
          <div class="hidden sm:block">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-teal-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              New Pageant
            </span>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- Left column -->
          <div class="space-y-6">
            <!-- Pageant name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Pageant Name <span class="text-red-500">*</span></label>
              <input
                id="name"
                type="text"
                v-model="form.name"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                placeholder="e.g. Miss Universe 2025"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.name }"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              <p v-else class="mt-1 text-xs text-gray-500">Enter the official name of the pageant</p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="5"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                placeholder="Provide a brief description of the pageant..."
              ></textarea>
              <p class="mt-1 text-xs text-gray-500">Optional. Include any relevant details about the pageant</p>
            </div>

            <!-- Initial Status -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Initial Status <span class="text-red-500">*</span></label>
              <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                  <input 
                    type="radio" 
                    v-model="form.status" 
                    value="Draft"
                    class="border-gray-300 text-teal-600 focus:ring-teal-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">Draft</span>
                </label>
              </div>
              <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
              <p v-else class="mt-1 text-xs text-gray-500">
                Pageant will be created as a draft and only visible to admins
              </p>
            </div>
            
            <!-- Scoring System -->
            <div>
              <label for="scoring_system" class="block text-sm font-medium text-gray-700 mb-1">Scoring System <span class="text-red-500">*</span></label>
              <div class="grid grid-cols-2 gap-3">
                <label 
                  v-for="(system, index) in scoringSystems" 
                  :key="index"
                  class="relative flex items-start p-3 border rounded-md cursor-pointer hover:bg-gray-50 transition-colors"
                  :class="form.scoring_system === system.type ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200' : 'border-gray-300'"
                >
                  <div class="min-w-0 flex-1 text-sm">
                    <input
                      type="radio"
                      :name="system.type"
                      :id="system.type"
                      :value="system.type"
                      v-model="form.scoring_system"
                      class="sr-only"
                    />
                    <p class="font-medium text-gray-700">{{ system.name }}</p>
                    <p class="text-xs text-gray-500">{{ system.description }}</p>
                  </div>
                  <div class="ml-3 flex h-5 items-center">
                    <svg v-if="form.scoring_system === system.type" class="h-5 w-5 text-teal-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </label>
              </div>
              <p v-if="errors.scoring_system" class="mt-1 text-sm text-red-600">{{ errors.scoring_system }}</p>
              <p v-else class="mt-1 text-xs text-gray-500">
                Select the scoring system that will be used throughout the pageant. Organizers can modify this until 1 day before the pageant. After that, changes require an admin request.
              </p>
            </div>

            <!-- Contestant Type -->
            <div>
              <label for="contestant_type" class="block text-sm font-medium text-gray-700 mb-1">Contestant Type <span class="text-red-500">*</span></label>
              <div class="grid grid-cols-1 gap-3">
                <label 
                  v-for="(type, index) in contestantTypes" 
                  :key="index"
                  class="relative flex items-start p-3 border rounded-md cursor-pointer hover:bg-gray-50 transition-colors"
                  :class="form.contestant_type === type.value ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200' : 'border-gray-300'"
                >
                  <div class="min-w-0 flex-1 text-sm">
                    <input
                      type="radio"
                      :name="type.value"
                      :id="type.value"
                      :value="type.value"
                      v-model="form.contestant_type"
                      class="sr-only"
                    />
                    <p class="font-medium text-gray-700">{{ type.name }}</p>
                    <p class="text-xs text-gray-500">{{ type.description }}</p>
                  </div>
                  <div class="ml-3 flex h-5 items-center">
                    <svg v-if="form.contestant_type === type.value" class="h-5 w-5 text-teal-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </label>
              </div>
              <p v-if="errors.contestant_type" class="mt-1 text-sm text-red-600">{{ errors.contestant_type }}</p>
              <p v-else class="mt-1 text-xs text-gray-500">
                Select what type of contestants are allowed in this pageant. For Mr & Ms pageants, choose "Pairs Only".
              </p>
            </div>
          </div>

          <!-- Right column -->
          <div class="space-y-6">
            <!-- Date fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input
                  id="start_date"
                  type="date"
                  v-model="form.start_date"
                  :min="todayDate"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                />
                <p class="mt-1 text-xs text-gray-500">Cannot select a date before today</p>
              </div>
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input
                  id="end_date"
                  type="date"
                  v-model="form.end_date"
                  :min="form.start_date"
                  :disabled="!form.start_date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
                  :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.end_date }"
                />
                <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</p>
                <p v-else-if="!form.start_date" class="mt-1 text-xs text-gray-500">Please select a start date first</p>
              </div>
            </div>

            <!-- Location fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                <input
                  id="venue"
                  type="text"
                  v-model="form.venue"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                  placeholder="e.g. Grand Convention Center"
                />
              </div>
              <div class="relative">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location (Bohol Only)</label>
                <input
                  id="location"
                  type="text"
                  v-model="locationInput"
                  @input="handleLocationInput"
                  @blur="handleLocationBlur"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                  :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.location }"
                  placeholder="Start typing a municipality/city in Bohol..."
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
                <p v-else class="mt-1 text-xs text-gray-500">Only locations within Bohol province are accepted</p>
              </div>
            </div>

            <!-- Organizers -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label for="organizers" class="block text-sm font-medium text-gray-700">Assign Organizers</label>
                <span v-if="form.start_date && form.end_date && organizerConflicts.size > 0" class="text-xs text-amber-600 flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                  </svg>
                  {{ organizerConflicts.size }} organizer{{ organizerConflicts.size > 1 ? 's' : '' }} unavailable
                </span>
              </div>
              <div 
                class="bg-white w-full rounded-md border border-gray-300 shadow-sm p-2 focus-within:border-teal-500 focus-within:ring focus-within:ring-teal-200 focus-within:ring-opacity-50 transition-colors"
              >
                <div v-if="!props.organizers || props.organizers.length === 0" class="text-center py-2 text-sm text-gray-500">
                  No organizers available
                </div>
                <div v-else class="space-y-2 max-h-48 overflow-y-auto">
                  <label 
                    v-for="organizer in props.organizers" 
                    :key="organizer.id" 
                    class="flex items-center p-2 rounded-md transition-colors"
                    :class="{
                      'hover:bg-gray-50 cursor-pointer': !hasConflict(organizer.id),
                      'bg-red-50 cursor-not-allowed opacity-75': hasConflict(organizer.id)
                    }"
                  >
                    <input 
                      type="checkbox" 
                      :value="organizer.id" 
                      v-model="form.organizer_ids"
                      :disabled="hasConflict(organizer.id)"
                      class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                      :class="{ 'cursor-not-allowed': hasConflict(organizer.id) }"
                    />
                    <div class="ml-3 flex-1">
                      <div class="flex items-center gap-2">
                        <div class="text-sm font-medium" :class="hasConflict(organizer.id) ? 'text-gray-500' : 'text-gray-700'">
                          {{ organizer.name }}
                        </div>
                        <span v-if="hasConflict(organizer.id)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                          </svg>
                          Conflict
                        </span>
                      </div>
                      <div class="text-xs" :class="hasConflict(organizer.id) ? 'text-gray-400' : 'text-gray-500'">
                        {{ organizer.email }}
                      </div>
                      <div v-if="hasConflict(organizer.id)" class="text-xs text-red-600 mt-1">
                        Already assigned to: {{ getConflictDetails(organizer.id)?.pageant_name }}<br>
                        ({{ getConflictDetails(organizer.id)?.start_date }} - {{ getConflictDetails(organizer.id)?.end_date }})
                      </div>
                    </div>
                  </label>
                </div>
              </div>
              <p v-if="errors.organizer_ids" class="mt-2 text-sm text-red-600">{{ errors.organizer_ids }}</p>
              <div class="mt-2 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                  <span v-if="!form.start_date || !form.end_date" class="text-amber-600 font-medium">
                    💡 Set start and end dates above to check organizer availability
                  </span>
                  <span v-else>
                    Optional. Selected organizers will be able to manage this pageant
                  </span>
                </p>
                <button 
                  type="button"
                  @click="showNewOrganizerModal = true"
                  class="text-sm text-teal-600 hover:text-teal-800 flex items-center"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                  </svg>
                  Create New Organizer
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Form actions -->
        <div class="border-t border-gray-200 pt-5 flex justify-between items-center">
          <Link 
            href="/admin/pageants" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Cancel
          </Link>
          <div class="flex space-x-3">
            <button
              type="button"
              @click="saveAsDraft"
              class="inline-flex items-center px-4 py-2 border border-teal-300 shadow-sm text-sm font-medium rounded-md text-teal-700 bg-teal-50 hover:bg-teal-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-teal-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Save as Draft
            </button>
            <button
              type="submit"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
              :disabled="isSubmitting"
            >
              <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Create Pageant
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- New Organizer Modal -->
  <CreateOrganizerModal 
    :show="showNewOrganizerModal" 
    @close="showNewOrganizerModal = false"
    @created="handleOrganizerCreated"
  />
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CreateOrganizerModal from '@/Components/CreateOrganizerModal.vue';
import { useNotification } from '@/Composables/useNotification';

// Define scoring systems
const scoringSystems = [
  {
    type: 'percentage',
    name: 'Percentage (0-100%)',
    description: 'Traditional percentage-based scoring from 0 to 100%'
  },
  {
    type: '1-10',
    name: 'Scale (1-10)',
    description: 'Standard 1 to 10 scale commonly used in pageants'
  },
  {
    type: '1-5',
    name: 'Scale (1-5)',
    description: 'Simplified 1 to 5 scale for easier scoring'
  },
  {
    type: 'points',
    name: 'Points (0-50)',
    description: 'Point-based scoring with a maximum of 50 points'
  }
];

// Define contestant types
const contestantTypes = [
  {
    value: 'solo',
    name: 'Solo Contestants Only',
    description: 'Only individual contestants are allowed to participate'
  },
  {
    value: 'pairs',
    name: 'Pairs Only',
    description: 'Only paired contestants (Mr & Ms) are allowed to participate'
  },
  {
    value: 'both',
    name: 'Both Solo and Pairs',
    description: 'Both individual contestants and pairs can participate'
  }
];

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
];

// Define props received from the controller
const props = defineProps({
  organizers: {
    type: Array,
    default: () => []
  },
  conflicts: {
    type: Object,
    default: () => ({})
  }
});

// Page layout
defineOptions({
  layout: AdminLayout,
});

// Initialize form data
const form = reactive({
  name: '',
  description: '',
  start_date: '',
  end_date: '',
  venue: '',
  location: '',
  status: 'Draft',
  organizer_ids: [],
  scoring_system: null,
  contestant_type: 'both'
});

// State for validation errors
const errors = ref({});
const isSubmitting = ref(false);
const showNewOrganizerModal = ref(false);

// Location input handling
const locationInput = ref('');
const showLocationSuggestions = ref(false);

const filteredLocations = computed(() => {
  if (!locationInput.value) return [];
  
  const searchTerm = locationInput.value.toLowerCase();
  return boholLocations.filter(location => 
    location.toLowerCase().includes(searchTerm)
  );
});

const handleLocationInput = () => {
  showLocationSuggestions.value = true;
  // Clear the form location if user is typing
  if (form.location !== locationInput.value) {
    form.location = '';
  }
};

const handleLocationBlur = () => {
  // Delay to allow click on suggestion
  setTimeout(() => {
    showLocationSuggestions.value = false;
    
    // Validate if the entered location is in Bohol
    if (locationInput.value && !boholLocations.includes(locationInput.value)) {
      errors.value.location = 'Please select a valid location from Bohol';
      form.location = '';
    }
  }, 200);
};

const selectLocation = (location) => {
  locationInput.value = location;
  form.location = location;
  showLocationSuggestions.value = false;
  
  // Clear location error if exists
  if (errors.value.location) {
    delete errors.value.location;
  }
};

// Get the notification service
const notify = useNotification();

// Get today's date in YYYY-MM-DD format
const todayDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

// Compute organizers with date conflicts dynamically
const organizerConflicts = computed(() => {
  if (!form.start_date || !form.end_date || !props.organizers) {
    return new Map();
  }

  const conflicts = new Map();
  const formStart = new Date(form.start_date);
  const formEnd = new Date(form.end_date);

  props.organizers.forEach(organizer => {
    if (organizer.pageants && organizer.pageants.length > 0) {
      for (const pageant of organizer.pageants) {
        if (pageant.start_date && pageant.end_date) {
          const pageantStart = new Date(pageant.start_date);
          const pageantEnd = new Date(pageant.end_date);

          // Check for date overlap
          const hasOverlap = (formStart <= pageantEnd && formEnd >= pageantStart);

          if (hasOverlap) {
            conflicts.set(organizer.id, {
              pageant_id: pageant.id,
              pageant_name: pageant.name,
              start_date: pageant.start_date_formatted || pageant.start_date,
              end_date: pageant.end_date_formatted || pageant.end_date,
            });
            break; // Only need to show one conflict
          }
        }
      }
    }
  });

  return conflicts;
});

// Submit the form to create the pageant
const submitForm = () => {
  console.log('submitForm called - starting validation');
  
  // Reset errors
  errors.value = {};
  
  // Validate
  if (!form.name) {
    errors.value.name = 'Pageant name is required';
  }
  
  if (!form.status) {
    errors.value.status = 'Status is required';
  }
  
  if (!form.scoring_system) {
    errors.value.scoring_system = 'Scoring system is required';
  }
  
  if (!form.contestant_type) {
    errors.value.contestant_type = 'Contestant type is required';
  }
  
  // Validate start date is not in the past
  if (form.start_date && new Date(form.start_date) < new Date(todayDate.value)) {
    errors.value.start_date = 'Start date cannot be in the past';
  }
  
  if (form.start_date && form.end_date && new Date(form.end_date) < new Date(form.start_date)) {
    errors.value.end_date = 'End date must be after start date';
  }
  
  // Validate location is from Bohol
  if (locationInput.value && !boholLocations.includes(locationInput.value)) {
    errors.value.location = 'Please select a valid location from Bohol';
  }
  
  // Check if any selected organizers have conflicts
  if (form.organizer_ids && form.organizer_ids.length > 0) {
    const conflictedOrganizerIds = form.organizer_ids.filter(id => hasConflict(id));
    if (conflictedOrganizerIds.length > 0) {
      const conflictedNames = conflictedOrganizerIds.map(id => {
        const organizer = props.organizers.find(o => o.id === id);
        return organizer?.name;
      }).filter(Boolean);
      errors.value.organizer_ids = `Cannot assign organizers with scheduling conflicts: ${conflictedNames.join(', ')}`;
    }
  }
  
  // If there are validation errors, stop submission
  if (Object.keys(errors.value).length > 0) {
    console.log('Frontend validation failed:', errors.value);
    notify.error('Please fix the errors in the form before submitting');
    return;
  }
  
  console.log('Frontend validation passed - proceeding with submission');
  
  // Submit the form
  isSubmitting.value = true;
  
  // Debug: Log the form data being submitted
  console.log('Submitting form data:', form);
  console.log('Organizer IDs:', form.organizer_ids);
  
  router.post(route('admin.pageants.store'), form, {
    onSuccess: (page) => {
      console.log('Form submission successful:', page);
      // Show a success message even if the server doesn't provide one
      notify.success(`Pageant "${form.name}" has been created successfully!`);
      isSubmitting.value = false;
    },
    onError: (validationErrors) => {
      console.log('Form submission failed with errors:', validationErrors);
      // Display validation errors from backend
      errors.value = validationErrors;
      isSubmitting.value = false;
      notify.error('There was an error creating the pageant. Please check the form.');
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};

// Save as draft
const saveAsDraft = () => {
  console.log('saveAsDraft called');
  form.status = 'Draft';
  submitForm();
};

// Check if an organizer has a conflict
const hasConflict = (organizerId) => {
  return organizerConflicts.value.has(organizerId);
};

// Get conflict details for an organizer
const getConflictDetails = (organizerId) => {
  return organizerConflicts.value.get(organizerId);
};

// Handle newly created organizer
const handleOrganizerCreated = (organizer) => {
  // Add the new organizer to the list
  if (!props.organizers) {
    props.organizers = [];
  }
  props.organizers.push(organizer);
  
  // Select the newly created organizer
  form.organizer_ids.push(organizer.id);
};
</script> 