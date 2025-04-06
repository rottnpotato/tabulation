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
                <label class="inline-flex items-center">
                  <input 
                    type="radio" 
                    v-model="form.status" 
                    value="Setup" 
                    class="border-gray-300 text-teal-600 focus:ring-teal-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">Setup</span>
                </label>
              </div>
              <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
              <p v-else class="mt-1 text-xs text-gray-500">
                Draft: Only visible to admins, Setup: Available for configuration
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
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                />
              </div>
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input
                  id="end_date"
                  type="date"
                  v-model="form.end_date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                  :min="form.start_date"
                  :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.end_date }"
                />
                <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</p>
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
              <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input
                  id="location"
                  type="text"
                  v-model="form.location"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                  placeholder="e.g. New York, USA"
                />
              </div>
            </div>

            <!-- Organizers -->
            <div>
              <label for="organizers" class="block text-sm font-medium text-gray-700 mb-1">Assign Organizers</label>
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
                    class="flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer transition-colors"
                  >
                    <input 
                      type="checkbox" 
                      :value="organizer.id" 
                      v-model="form.organizer_ids"
                      class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                    />
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-700">{{ organizer.name }}</div>
                      <div class="text-xs text-gray-500">{{ organizer.email }}</div>
                    </div>
                  </label>
                </div>
              </div>
              <div class="mt-2 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                  Optional. Selected organizers will be able to manage this pageant
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

// Define props received from the controller
const props = defineProps({
  organizers: {
    type: Array,
    default: () => []
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
  scoring_system: null
});

// State for validation errors
const errors = ref({});
const isSubmitting = ref(false);
const showNewOrganizerModal = ref(false);

// Get the notification service
const notify = useNotification();

// Submit the form to create the pageant
const submitForm = () => {
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
  
  if (form.start_date && form.end_date && new Date(form.end_date) < new Date(form.start_date)) {
    errors.value.end_date = 'End date must be after start date';
  }
  
  // If there are validation errors, stop submission
  if (Object.keys(errors.value).length > 0) {
    notify.error('Please fix the errors in the form before submitting');
    return;
  }
  
  // Submit the form
  isSubmitting.value = true;
  
  router.post('/admin/pageants/create', form, {
    onSuccess: () => {
      // Show a success message even if the server doesn't provide one
      notify.success(`Pageant "${form.name}" has been created successfully!`);
      isSubmitting.value = false;
    },
    onError: (validationErrors) => {
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
  form.status = 'Draft';
  submitForm();
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