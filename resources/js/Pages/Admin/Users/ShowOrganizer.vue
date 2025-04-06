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
                <Link :href="route('admin.users.organizers')" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Organizers</Link>
              </div>
            </li>
            <li class="flex">
              <div class="flex items-center">
                <ChevronRight class="flex-shrink-0 h-5 w-5 text-gray-400" />
                <span class="ml-4 text-sm font-medium text-teal-600">{{ organizer.name }}</span>
              </div>
            </li>
          </ol>
        </nav>
      </div>

      <!-- Organizer Profile -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-teal-500 to-teal-600">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="h-16 w-16 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-4">
                <Users class="h-10 w-10 text-white" />
              </div>
              <div>
                <h3 class="text-xl font-bold leading-6 text-white">{{ organizer.name }}</h3>
                <p class="mt-1 max-w-2xl text-sm text-teal-100">Pageant Organizer</p>
              </div>
            </div>
            <div class="flex space-x-3">
              <Link
                :href="route('admin.users.organizers.edit', organizer.id)"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-teal-600 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-teal-600 focus:ring-white transition-colors duration-150"
              >
                <Edit class="mr-2 h-4 w-4" />
                Edit Profile
              </Link>
              <button
                v-if="organizer.pageants_count === 0"
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
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ organizer.name }}</dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Email address</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 flex items-center">
                {{ organizer.email }}
                <span 
                  v-if="!organizer.email_verified_at" 
                  class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                >
                  Unverified
                  <button 
                    @click="confirmResendVerification" 
                    class="ml-1 p-1 hover:bg-yellow-200 rounded"
                    title="Resend verification email"
                  >
                    <Mail class="h-3 w-3" />
                  </button>
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
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ organizer.username }}</dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Account status</dt>
              <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                <span 
                  class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                  :class="organizer.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ organizer.is_active ? 'Active' : 'Inactive' }}
                </span>
                <button 
                  @click="toggleStatus" 
                  class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                >
                  {{ organizer.is_active ? 'Deactivate' : 'Activate' }}
                </button>
              </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Registered on</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ organizer.created_at }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Pageant List -->
      <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-indigo-500 to-purple-600">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="h-12 w-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-4">
                <Crown class="h-7 w-7 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-bold leading-6 text-white">Managed Pageants</h3>
                <p class="mt-1 max-w-2xl text-sm text-indigo-100">
                  {{ 
                    organizer.pageants && organizer.pageants.length > 0 
                      ? `${organizer.pageants.length} pageant${organizer.pageants.length !== 1 ? 's' : ''} assigned to this organizer` 
                      : 'No pageants assigned yet' 
                  }}
                </p>
              </div>
            </div>
            <div>
              <Link
                href="/admin/pageants/create"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-600 focus:ring-white transition-colors duration-150"
              >
                <Plus class="mr-2 h-4 w-4" />
                Assign New Pageant
              </Link>
            </div>
          </div>
        </div>
        <div class="border-t border-gray-200">
          <div v-if="organizer.pageants && organizer.pageants.length > 0" class="divide-y divide-gray-200">
            <div v-for="pageant in organizer.pageants" :key="pageant.id" class="hover:bg-gray-50">
              <Link :href="route('admin.pageants.detail', pageant.id)" class="block">
                <div class="px-4 py-4 sm:px-6">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-indigo-600 truncate">{{ pageant.name }}</p>
                    <div class="ml-2 flex-shrink-0 flex">
                      <p 
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                        :class="getStatusClass(pageant.status)"
                      >
                        {{ pageant.status }}
                      </p>
                    </div>
                  </div>
                  <div class="mt-2 flex justify-between">
                    <div class="sm:flex">
                      <p class="flex items-center text-sm text-gray-500">
                        <Calendar class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" />
                        {{ pageant.start_date || 'Date not set' }}
                      </p>
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                      <ChevronRight class="flex-shrink-0 ml-1.5 h-4 w-4 text-gray-400" />
                    </div>
                  </div>
                </div>
              </Link>
            </div>
          </div>
          <div v-else class="px-4 py-5 text-center text-gray-500">
            <Crown class="mx-auto h-12 w-12 text-gray-400 mb-2" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">No pageants assigned</h3>
            <p class="text-gray-500 max-w-lg mx-auto mb-4">
              This organizer doesn't have any pageants assigned yet. Assign a pageant to allow them to manage it.
            </p>
            <Link
              href="/admin/pageants/create"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <Plus class="mr-2 h-4 w-4" />
              Create New Pageant
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      title="Delete Organizer Account"
      confirm-text="Delete"
      type="danger"
      @close="showDeleteModal = false"
      @confirm="deleteOrganizer"
    >
      Are you sure you want to delete <span class="font-medium">{{ organizer.name }}</span>'s account? This action cannot be undone.
    </ConfirmationModal>

    <!-- Resend Verification Modal -->
    <ConfirmationModal
      :show="showResendModal"
      title="Resend Verification Email"
      confirm-text="Resend"
      type="info"
      @close="showResendModal = false"
      @confirm="resendVerification"
    >
      Send a new verification email to <span class="font-medium">{{ organizer.email }}</span>?
    </ConfirmationModal>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmationModal from '@/Components/users/ConfirmationModal.vue'
import { 
  ChevronRight,
  Users,
  Mail,
  Edit,
  Trash2,
  Crown,
  Plus,
  Calendar
} from 'lucide-vue-next'

const props = defineProps({
  organizer: {
    type: Object,
    required: true
  }
})

// Local state
const showDeleteModal = ref(false)
const showResendModal = ref(false)

// Methods
const confirmDelete = () => {
  showDeleteModal.value = true
}

const deleteOrganizer = () => {
  router.delete(route('admin.users.organizers.delete', props.organizer.id), {
    onSuccess: () => {
      showDeleteModal.value = false
    }
  })
}

const confirmResendVerification = () => {
  showResendModal.value = true
}

const resendVerification = () => {
  router.post(route('admin.users.organizers.resend-verification', props.organizer.id), {}, {
    onSuccess: () => {
      showResendModal.value = false
    }
  })
}

const toggleStatus = () => {
  const newStatus = !props.organizer.is_active
  
  router.put(route('admin.users.organizers.update', props.organizer.id), {
    name: props.organizer.name,
    email: props.organizer.email,
    username: props.organizer.username,
    is_active: newStatus
  })
}

const getStatusClass = (status) => {
  switch (status) {
    case 'Draft':
      return 'bg-gray-100 text-gray-800'
    case 'Setup':
      return 'bg-blue-100 text-blue-800'
    case 'Active':
      return 'bg-green-100 text-green-800'
    case 'Completed':
      return 'bg-purple-100 text-purple-800'
    case 'Unlocked_For_Edit':
      return 'bg-yellow-100 text-yellow-800'
    case 'Archived':
      return 'bg-gray-100 text-gray-800'
    case 'Cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}
</script> 