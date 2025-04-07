<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" @click="closeModal">
        <div class="absolute inset-0 bg-gray-800 opacity-75"></div>
      </div>

      <!-- Modal panel -->
      <div class="relative inline-block w-full max-w-md p-6 mx-auto mt-8 text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:align-middle">
        <!-- Modal header -->
        <div class="mb-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Create New Organizer</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <p class="mt-1 text-sm text-gray-500">Fill in the details to create a new organizer account. An email will be sent for verification.</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
              <input 
                id="name" 
                v-model="form.name" 
                type="text" 
                class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.name }"
              />
              <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
              <input 
                id="email" 
                v-model="form.email" 
                type="email" 
                class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.email }"
              />
              <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
            </div>

            <!-- Username -->
            <div>
              <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
              <div class="relative">
                <input 
                  id="username" 
                  v-model="form.username" 
                  type="text" 
                  class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500"
                  :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.username }"
                />
                <div v-if="usernameIsGenerating" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                  <svg class="w-5 h-5 text-gray-400 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
                <button 
                  v-else-if="form.name" 
                  type="button" 
                  @click="generateUsername"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-teal-600 hover:text-teal-800"
                >
                  Generate
                </button>
              </div>
              <p v-if="errors.username" class="mt-1 text-xs text-red-600">{{ errors.username }}</p>
            </div>

            <!-- Temporary Password -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">
                Temporary Password
                <span class="text-xs text-gray-500 ml-1">(Will be reset after verification)</span>
              </label>
              <div class="relative">
                <input 
                  id="password" 
                  v-model="form.password" 
                  :type="showPassword ? 'text' : 'password'" 
                  class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500"
                  :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': errors.password }"
                />
                <button 
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
              <div class="flex items-center justify-between mt-1">
                <p class="text-xs text-gray-500">Must be at least 8 characters</p>
                <button 
                  type="button" 
                  @click="generatePassword"
                  class="text-xs text-teal-600 hover:text-teal-800"
                >
                  Generate Random
                </button>
              </div>
            </div>
          </div>

          <!-- Form actions -->
          <div class="mt-6 flex justify-end space-x-3">
            <button 
              type="button" 
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
              Cancel
            </button>
            <button 
              type="submit"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-teal-600 border border-transparent rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
              :disabled="isSubmitting"
            >
              <svg v-if="isSubmitting" class="w-4 h-4 mr-2 -ml-1 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="isSubmitting">Creating...</span>
              <span v-else>Create Organizer</span>
            </button>
          </div>
        </form>

        <!-- Success message -->
        <div v-if="success" class="mt-4 p-4 bg-green-50 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">
                Organizer created successfully! A verification email has been sent.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'created']);

// Form state
const form = reactive({
  name: '',
  email: '',
  username: '',
  password: '',
});

const errors = ref({});
const isSubmitting = ref(false);
const success = ref(false);
const showPassword = ref(false);
const usernameIsGenerating = ref(false);

// Generate username from name
watch(() => form.name, (newValue) => {
  if (newValue && !form.username) {
    // Auto-suggest username when name is entered and username is empty
    generateUsername();
  }
});

const generateUsername = () => {
  if (!form.name) return;
  
  usernameIsGenerating.value = true;
  
  // Simple username generation algorithm
  const nameParts = form.name.toLowerCase().split(' ');
  const firstName = nameParts[0] || '';
  const lastName = nameParts[nameParts.length - 1] || '';
  
  // Create base username
  let username = '';
  if (firstName === lastName) {
    username = firstName;
  } else {
    username = firstName + (lastName ? '.' + lastName : '');
  }
  
  // Remove special characters
  username = username.replace(/[^a-z0-9.]/g, '');
  
  // Add random number if too short
  if (username.length < 5) {
    username += Math.floor(Math.random() * 1000);
  }
  
  // Check if username exists
  router.post('/admin/check-username', { username }, {
    preserveScroll: true,
    onSuccess: (response) => {
      // Check if response comes in different formats
      let usernameExists = false;
      
      if (response.props && response.props.usernameExists !== undefined) {
        usernameExists = response.props.usernameExists;
      } else if (response.props && response.props.flash && response.props.flash.usernameExists !== undefined) {
        usernameExists = response.props.flash.usernameExists;
      }
      
      if (usernameExists) {
        // If username exists, add random number
        form.username = username + Math.floor(Math.random() * 1000);
      } else {
        form.username = username;
      }
      usernameIsGenerating.value = false;
    },
    onError: () => {
      // Fallback: just use the generated username with a random number
      form.username = username + Math.floor(Math.random() * 10000);
      usernameIsGenerating.value = false;
    }
  });
};

// Generate random password
const generatePassword = () => {
  const length = 12;
  const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
  let password = '';
  
  for (let i = 0; i < length; i++) {
    const randomIndex = Math.floor(Math.random() * charset.length);
    password += charset[randomIndex];
  }
  
  form.password = password;
  showPassword.value = true;
};

// Submit form to create organizer
const submitForm = () => {
  errors.value = {};
  
  // Validate form
  if (!form.name) {
    errors.value.name = 'Name is required';
  }
  
  if (!form.email) {
    errors.value.email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Invalid email format';
  }
  
  if (!form.username) {
    errors.value.username = 'Username is required';
  } else if (form.username.length < 3) {
    errors.value.username = 'Username must be at least 3 characters';
  }
  
  if (!form.password) {
    errors.value.password = 'Password is required';
  } else if (form.password.length < 8) {
    errors.value.password = 'Password must be at least 8 characters';
  }
  
  // If there are validation errors, stop submission
  if (Object.keys(errors.value).length > 0) {
    return;
  }
  
  isSubmitting.value = true;
  
  // Submit the form to create an organizer
  router.post('/admin/organizers', form, {
    preserveScroll: true,
    onSuccess: (page) => {
      isSubmitting.value = false;
      success.value = true;
      
      // Check if the response contains organizer data
      const response = page.props.flash || {};
      if (response.organizer) {
        // Emit created event with the new organizer data
        emit('created', {
          id: response.organizer.id,
          name: form.name,
          email: form.email
        });
      } else if (page.props.organizer) {
        // Alternative location of organizer data
        emit('created', {
          id: page.props.organizer.id,
          name: form.name,
          email: form.email
        });
      }
      
      // Close modal after delay
      setTimeout(() => {
        closeModal();
      }, 3000);
    },
    onError: (responseErrors) => {
      isSubmitting.value = false;
      errors.value = responseErrors;
    }
  });
};

// Close modal
const closeModal = () => {
  // Reset form state
  Object.keys(form).forEach(key => form[key] = '');
  errors.value = {};
  success.value = false;
  isSubmitting.value = false;
  showPassword.value = false;
  
  // Emit close event
  emit('close');
};
</script> 