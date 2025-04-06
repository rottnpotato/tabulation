<template>
  <AdminLayout>
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header with breadcrumbs -->
      <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="flex items-center space-x-4">
            <li class="flex">
              <div class="flex items-center">
                <Link href="/admin/dashboard" class="text-sm font-medium text-gray-500 hover:text-gray-700">Dashboard</Link>
              </div>
            </li>
            <li class="flex">
              <div class="flex items-center">
                <ChevronRight class="flex-shrink-0 h-5 w-5 text-gray-400" />
                <Link :href="route('admin.users.admins')" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Administrators</Link>
              </div>
            </li>
            <li class="flex">
              <div class="flex items-center">
                <ChevronRight class="flex-shrink-0 h-5 w-5 text-gray-400" />
                <span class="ml-4 text-sm font-medium text-teal-600">{{ admin.name }}</span>
              </div>
            </li>
          </ol>
        </nav>
      </div>

      <!-- Administrator Profile -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-teal-500 to-teal-600">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="h-16 w-16 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-4">
                <Shield class="h-10 w-10 text-white" />
              </div>
              <div>
                <h3 class="text-xl font-bold leading-6 text-white">{{ admin.name }}</h3>
                <p class="mt-1 max-w-2xl text-sm text-teal-100">System Administrator</p>
              </div>
            </div>
            <div class="flex space-x-3">
              <Link
                v-if="!isSelf"
                :href="route('admin.users.admins.edit', admin.id)"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-teal-600 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-teal-600 focus:ring-white transition-colors duration-150"
              >
                <Edit class="mr-2 h-4 w-4" />
                Edit Profile
              </Link>
              <button
                v-if="!isSelf"
                @click="confirmDelete"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-teal-600 focus:ring-red-500 transition-colors duration-150"
              >
                <Trash2 class="mr-2 h-4 w-4" />
                Delete
              </button>
            </div>
          </div>
        </div>
        <div class="border-t border-gray-200">
          <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Full name</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ admin.name }}</dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Email address</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 flex items-center">
                {{ admin.email }}
                <span 
                  v-if="!admin.email_verified_at" 
                  class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                >
                  Unverified
                </span>
                <span 
                  v-else
                  class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                >
                  Verified
                </span>
              </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Username</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ admin.username }}</dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Account status</dt>
              <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                <span 
                  class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                  :class="admin.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ admin.is_active ? 'Active' : 'Inactive' }}
                </span>
                <button 
                  v-if="!isSelf"
                  @click="toggleStatus" 
                  class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                >
                  {{ admin.is_active ? 'Deactivate' : 'Activate' }}
                </button>
              </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Registered on</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ admin.created_at }}</dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Role</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                  System Administrator
                </span>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Permissions Info -->
      <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-indigo-500 to-purple-600">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="h-12 w-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-4">
                <Lock class="h-7 w-7 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-bold leading-6 text-white">System Permissions</h3>
                <p class="mt-1 max-w-2xl text-sm text-indigo-100">
                  Full administrative access to the system
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="border-t border-gray-200 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-start">
              <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Check class="h-4 w-4" />
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-900">Pageant Management</h4>
                <p class="mt-1 text-xs text-gray-500">Create, edit, and manage all pageants and events</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Check class="h-4 w-4" />
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-900">User Administration</h4>
                <p class="mt-1 text-xs text-gray-500">Manage all users including organizers, judges, and tabulators</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Check class="h-4 w-4" />
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-900">System Configuration</h4>
                <p class="mt-1 text-xs text-gray-500">Configure system settings and preferences</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Check class="h-4 w-4" />
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-900">Audit Log Access</h4>
                <p class="mt-1 text-xs text-gray-500">View and analyze system audit logs and activities</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Check class="h-4 w-4" />
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-900">Reports Generation</h4>
                <p class="mt-1 text-xs text-gray-500">Generate and access all system reports</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Check class="h-4 w-4" />
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-900">Edit Permission Control</h4>
                <p class="mt-1 text-xs text-gray-500">Grant or revoke edit permissions for completed pageants</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      title="Delete Administrator Account"
      confirm-text="Delete"
      type="danger"
      @close="showDeleteModal = false"
      @confirm="deleteAdmin"
    >
      Are you sure you want to delete <span class="font-medium">{{ admin.name }}</span>'s account? This action cannot be undone.
    </ConfirmationModal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmationModal from '@/Components/users/ConfirmationModal.vue'
import { 
  ChevronRight,
  Shield,
  Edit,
  Trash2,
  Lock,
  Check
} from 'lucide-vue-next'

const props = defineProps({
  admin: {
    type: Object,
    required: true
  },
  currentUser: {
    type: Object,
    required: true
  }
})

// Determine if viewing own profile
const isSelf = computed(() => {
  return props.admin.id === props.currentUser.id
})

// Local state
const showDeleteModal = ref(false)

// Methods
const confirmDelete = () => {
  if (isSelf.value) return
  showDeleteModal.value = true
}

const deleteAdmin = () => {
  if (isSelf.value) return
  
  router.delete(route('admin.users.admins.delete', props.admin.id), {
    onSuccess: () => {
      showDeleteModal.value = false
    }
  })
}

const toggleStatus = () => {
  if (isSelf.value) return
  
  const newStatus = !props.admin.is_active
  
  router.put(route('admin.users.admins.update', props.admin.id), {
    name: props.admin.name,
    email: props.admin.email,
    username: props.admin.username,
    is_active: newStatus
  })
}
</script> 