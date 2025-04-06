<template>
  <div class="bg-white shadow overflow-hidden rounded-lg">
    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
      <h3 class="text-lg font-medium leading-6 text-gray-900">{{ title }}</h3>
      <p class="mt-1 text-sm text-gray-500">{{ description }}</p>
    </div>
    
    <form @submit.prevent="submit">
      <div class="px-6 py-5 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
        <!-- User icon and type indicator -->
        <div class="sm:col-span-6 flex justify-center sm:justify-start">
          <div class="flex items-center space-x-3">
            <div class="h-16 w-16 rounded-full flex items-center justify-center" :class="[avatarBgColor]">
              <component :is="userIcon" class="h-10 w-10" :class="[avatarIconColor]" />
            </div>
            <div>
              <h4 class="text-lg font-medium text-gray-900">{{ formTitle }}</h4>
              <p class="text-sm text-gray-500">{{ formDescription }}</p>
            </div>
          </div>
        </div>

        <!-- Name field -->
        <div class="sm:col-span-3">
          <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
          <div class="mt-1">
            <input 
              type="text" 
              name="name" 
              id="name" 
              v-model="form.name"
              class="shadow-sm focus:ring-teal-500 focus:border-teal-500 block w-full sm:text-sm border-gray-300 rounded-md"
              :class="{ 'border-red-300': errors.name }"
            />
          </div>
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
        </div>

        <!-- Email field -->
        <div class="sm:col-span-3">
          <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
          <div class="mt-1">
            <input 
              type="email" 
              name="email" 
              id="email" 
              v-model="form.email"
              class="shadow-sm focus:ring-teal-500 focus:border-teal-500 block w-full sm:text-sm border-gray-300 rounded-md"
              :class="{ 'border-red-300': errors.email }"
            />
          </div>
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <!-- Username field -->
        <div class="sm:col-span-3">
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <div class="mt-1">
            <input 
              type="text" 
              name="username" 
              id="username" 
              v-model="form.username"
              class="shadow-sm focus:ring-teal-500 focus:border-teal-500 block w-full sm:text-sm border-gray-300 rounded-md"
              :class="{ 'border-red-300': errors.username }"
              :placeholder="editing ? '' : 'Auto-generated if left blank'"
            />
          </div>
          <p v-if="errors.username" class="mt-1 text-sm text-red-600">{{ errors.username }}</p>
          <p v-else-if="!editing" class="mt-1 text-sm text-gray-500">Leave blank to auto-generate from name</p>
        </div>

        <!-- Status toggle -->
        <div class="sm:col-span-3">
          <label class="block text-sm font-medium text-gray-700">Account Status</label>
          <div class="mt-2">
            <div class="flex items-center">
              <button 
                type="button"
                @click="form.is_active = true"
                class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                :class="form.is_active ? 'bg-teal-600' : 'bg-gray-200'"
                role="switch"
                aria-checked="false"
                aria-labelledby="active-label"
              >
                <span 
                  aria-hidden="true" 
                  class="inline-block w-5 h-5 transition duration-200 ease-in-out transform translate-x-0 bg-white rounded-full shadow ring-0"
                  :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"
                ></span>
              </button>
              <span class="ml-3" id="active-label">
                <span class="text-sm font-medium text-gray-900">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                <span class="text-sm text-gray-500">{{ form.is_active ? 'Account is enabled' : 'Account is disabled' }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Password fields (only for admins or new users) -->
        <template v-if="showPasswordFields">
          <div class="sm:col-span-3">
            <label for="password" class="block text-sm font-medium text-gray-700">
              {{ editing ? 'New Password' : 'Password' }}
            </label>
            <div class="mt-1">
              <input 
                type="password" 
                name="password" 
                id="password" 
                v-model="form.password"
                class="shadow-sm focus:ring-teal-500 focus:border-teal-500 block w-full sm:text-sm border-gray-300 rounded-md"
                :class="{ 'border-red-300': errors.password }"
                :placeholder="editing ? 'Leave blank to keep current password' : ''"
              />
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            <p v-else-if="editing" class="mt-1 text-sm text-gray-500">Leave blank to keep current password</p>
          </div>

          <div class="sm:col-span-3">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              {{ editing ? 'Confirm New Password' : 'Confirm Password' }}
            </label>
            <div class="mt-1">
              <input 
                type="password" 
                name="password_confirmation" 
                id="password_confirmation" 
                v-model="form.password_confirmation"
                class="shadow-sm focus:ring-teal-500 focus:border-teal-500 block w-full sm:text-sm border-gray-300 rounded-md"
                :disabled="!form.password"
              />
            </div>
          </div>
        </template>

        <!-- Notes field for additional information -->
        <div class="sm:col-span-6">
          <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
          <div class="mt-1">
            <textarea 
              id="notes" 
              name="notes" 
              rows="3"
              v-model="form.notes"
              class="shadow-sm focus:ring-teal-500 focus:border-teal-500 block w-full sm:text-sm border-gray-300 rounded-md"
              :class="{ 'border-red-300': errors.notes }"
              placeholder="Optional: Add any notes about this user"
            ></textarea>
          </div>
          <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
        </div>
      </div>

      <div class="px-6 py-3 bg-gray-50 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-200">
        <button
          type="submit"
          class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:ml-3 sm:w-auto sm:text-sm"
          :disabled="processing"
        >
          <span v-if="processing" class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
          </span>
          <span v-else>{{ submitButtonText }}</span>
        </button>
        <Link
          :href="cancelRoute"
          class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
        >
          Cancel
        </Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { 
  User2,
  Users,
  Shield,
  Calculator,
  Gavel 
} from 'lucide-vue-next'

const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      id: null,
      name: '',
      email: '',
      username: '',
      is_active: true,
      notes: ''
    })
  },
  userType: {
    type: String,
    required: true,
    validator: (value) => ['organizer', 'admin', 'tabulator', 'judge'].includes(value)
  },
  editing: {
    type: Boolean,
    default: false
  },
  submitRoute: {
    type: String,
    required: true
  },
  cancelRoute: {
    type: String,
    required: true
  },
  showPassword: {
    type: Boolean,
    default: true
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  processing: {
    type: Boolean,
    default: false
  }
})

defineEmits(['submit'])

// Create form with initial values
const form = useForm({
  name: props.user.name,
  email: props.user.email,
  username: props.user.username || '',
  is_active: props.user.is_active !== undefined ? props.user.is_active : true,
  password: '',
  password_confirmation: '',
  notes: props.user.notes || ''
})

// Show password fields depending on user type and if we're editing
const showPasswordFields = computed(() => {
  // For admins always show
  if (props.userType === 'admin') {
    return true
  }
  
  // For new users always show
  if (!props.editing) {
    return true
  }
  
  // For existing non-admin users, show password if showPassword prop is true
  return props.showPassword
})

// Computed properties for form title and descriptions
const title = computed(() => {
  if (props.editing) {
    return `Edit ${userTypeSingular.value}`
  } else {
    return `Create New ${userTypeSingular.value}`
  }
})

const description = computed(() => {
  if (props.editing) {
    return `Update ${userTypeSingular.value.toLowerCase()} details and settings`
  } else {
    return `Add a new ${userTypeSingular.value.toLowerCase()} to the system`
  }
})

const formTitle = computed(() => {
  if (props.editing) {
    return props.user.name || `Edit ${userTypeSingular.value}`
  } else {
    return `New ${userTypeSingular.value}`
  }
})

const formDescription = computed(() => {
  return userTypeDescription.value
})

// Submit button text
const submitButtonText = computed(() => {
  if (props.editing) {
    return 'Update'
  } else {
    return 'Create'
  }
})

// User type information
const userTypeSingular = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'Organizer'
    case 'admin': return 'Administrator'
    case 'tabulator': return 'Tabulator'
    case 'judge': return 'Judge'
    default: return 'User'
  }
})

const userTypeDescription = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'Manages pageants and contestants'
    case 'admin': return 'Has full access to system settings and management'
    case 'tabulator': return 'Manages scoring and results tabulation'
    case 'judge': return 'Evaluates contestants during pageants'
    default: return 'System user'
  }
})

// Icon for user avatar based on user type
const userIcon = computed(() => {
  switch (props.userType) {
    case 'organizer': return Users
    case 'admin': return Shield
    case 'tabulator': return Calculator
    case 'judge': return Gavel
    default: return User2
  }
})

// Avatar colors based on user type
const avatarBgColor = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'bg-orange-100'
    case 'admin': return 'bg-teal-100'
    case 'tabulator': return 'bg-blue-100'
    case 'judge': return 'bg-amber-100'
    default: return 'bg-gray-100'
  }
})

const avatarIconColor = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'text-orange-600'
    case 'admin': return 'text-teal-600'
    case 'tabulator': return 'text-blue-600'
    case 'judge': return 'text-amber-600'
    default: return 'text-gray-600'
  }
})

// Submit the form
const submit = () => {
  // Remove password fields if they're empty (for edit form)
  if (props.editing && !form.password) {
    const { password, password_confirmation, ...formData } = form
    form.transform(() => formData)
  }
  
  // Use the appropriate method depending on if we're editing or creating
  if (props.editing) {
    form.put(route(props.submitRoute, props.user.id))
  } else {
    form.post(route(props.submitRoute))
  }
}
</script> 