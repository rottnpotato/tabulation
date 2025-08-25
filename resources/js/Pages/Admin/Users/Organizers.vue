<template>
  <AdminLayout>
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header with fancy gradient background -->
      <div class="relative mb-8 overflow-hidden bg-gradient-to-r from-teal-500 to-emerald-600 rounded-xl shadow-lg">
        <div class="absolute inset-0 opacity-10">
          <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iLjUiPjxwYXRoIGQ9Ik0yOS41IDE4LjVoLTJ2N2gtN3YyaDd2N2gydi03aDd2LTJoLTd6Ii8+PC9nPjwvZz48L3N2Zz4=')]"></div>
        </div>
        <div class="relative p-8 flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <div class="flex items-center">
              <UsersRound class="h-8 w-8 text-white mr-3" />
              <h1 class="text-2xl font-bold text-white">User Management</h1>
            </div>
            <p class="mt-2 text-teal-100">
              Manage pageant organizers, their permissions and account settings
            </p>
          </div>
          <div class="mt-4 md:mt-0">
            <Link
              :href="route('admin.users.organizers.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-emerald-600 bg-white hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-emerald-600 focus:ring-white transition-colors duration-150"
            >
              <UserPlus class="mr-2 h-4 w-4" />
              Create Organizer
            </Link>
          </div>
        </div>

        <!-- Stats cards -->
        <div class="px-8 pb-8">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg shadow-sm p-4 flex items-center">
              <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center mr-4">
                <Users class="h-6 w-6 text-emerald-600" />
              </div>
              <div>
                <div class="text-sm text-gray-500">Total Organizers</div>
                <div class="text-2xl font-semibold">{{ organizers.length }}</div>
              </div>
            </div>
            <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg shadow-sm p-4 flex items-center">
              <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                <UserCheck class="h-6 w-6 text-green-600" />
              </div>
              <div>
                <div class="text-sm text-gray-500">Active Organizers</div>
                <div class="text-2xl font-semibold">{{ activeOrganizersCount }}</div>
              </div>
            </div>
            <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg shadow-sm p-4 flex items-center">
              <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center mr-4">
                <AlertCircle class="h-6 w-6 text-amber-600" />
              </div>
              <div>
                <div class="text-sm text-gray-500">Unverified Accounts</div>
                <div class="text-2xl font-semibold">{{ unverifiedOrganizersCount }}</div>
              </div>
            </div>
            <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg shadow-sm p-4 flex items-center">
              <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center mr-4">
                <Crown class="h-6 w-6 text-emerald-600" />
              </div>
              <div>
                <div class="text-sm text-gray-500">Managed Pageants</div>
                <div class="text-2xl font-semibold">{{ totalManagedPageants }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users list component -->
      <UsersList 
        :users="organizers" 
        user-type="organizer"
        detail-route="admin.users.organizers.show"
        create-route="admin.users.organizers.create"
        edit-route="admin.users.organizers.edit"
        has-toggle-status
        has-edit
        has-delete
        has-resend-verification
        :show-unverified="true"
        @toggle-status="toggleStatus"
        @confirm-delete="confirmDelete"
        @resend-verification="confirmResendVerification"
      />
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
      Are you sure you want to delete <span class="font-medium">{{ organizerToDelete?.name }}</span>'s account? This action cannot be undone.
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
      Send a new verification email to <span class="font-medium">{{ organizerToVerify?.email }}</span>?
    </ConfirmationModal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UsersList from '@/Components/users/UsersList.vue'
import ConfirmationModal from '@/Components/users/ConfirmationModal.vue'
import { 
  UserPlus, 
  UsersRound,
  Users,
  Crown,
  UserCheck,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps({
  organizers: {
    type: Array,
    required: true
  }
})

// Local state
const showDeleteModal = ref(false)
const showResendModal = ref(false)
const organizerToDelete = ref(null)
const organizerToVerify = ref(null)

// Computed stats
const activeOrganizersCount = computed(() => {
  return props.organizers.filter(organizer => organizer.status === 'Active').length
})

const unverifiedOrganizersCount = computed(() => {
  return props.organizers.filter(organizer => !organizer.email_verified).length
})

const totalManagedPageants = computed(() => {
  return props.organizers.reduce((total, organizer) => total + (organizer.pageants_count || 0), 0)
})

// Methods
const confirmDelete = (organizer) => {
  if (organizer.pageants_count > 0) {
    // Don't allow deletion if organizer has pageants
    return;
  }
  organizerToDelete.value = organizer;
  showDeleteModal.value = true;
}

const deleteOrganizer = () => {
  if (organizerToDelete.value) {
    router.delete(route('admin.users.organizers.delete', organizerToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteModal.value = false;
        organizerToDelete.value = null;
      },
      onError: (errors) => {
        console.error('Failed to delete organizer:', errors);
      }
    });
  }
}

const confirmResendVerification = (organizer) => {
  if (organizer.email_verified) {
    // Don't allow resending if already verified
    return;
  }
  organizerToVerify.value = organizer;
  showResendModal.value = true;
}

const resendVerification = () => {
  if (organizerToVerify.value) {
    router.post(route('admin.users.organizers.resend-verification', organizerToVerify.value.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        showResendModal.value = false;
        organizerToVerify.value = null;
      },
      onError: (errors) => {
        console.error('Failed to resend verification:', errors);
      }
    });
  }
}

const toggleStatus = (organizer) => {
  const newStatus = organizer.status === 'Active' ? false : true;
  
  router.put(route('admin.users.organizers.update', organizer.id), {
    name: organizer.name,
    email: organizer.email,
    username: organizer.username,
    is_active: newStatus
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Success notification is handled by the backend flash message
    },
    onError: (errors) => {
      console.error('Failed to update organizer status:', errors);
    }
  });
}
</script> 