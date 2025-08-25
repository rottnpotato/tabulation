<template>
  <Head title="Admin Management" />
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header with add button -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Administrators</h1>
        <p class="mt-1 text-sm text-gray-600">Manage system administrators with full access rights.</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <Link
          :href="route('admin.users.admins.create')"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
        >
          <Plus class="mr-2 h-4 w-4" />
          Add Administrator
        </Link>
      </div>
    </div>

    <!-- User List Component -->
    <UsersList
      :users="admins"
      user-type="admin"
      detail-route="admin.users.admins.show"
      create-route="admin.users.admins.create"
      edit-route="admin.users.admins.edit"
      user-type-label="Administrators"
      user-type-singular="Administrator"
      pageant-column-title="Roles & Permissions"
      :has-pageants-column="false"
      :has-toggle-status="true"
      :has-edit="true"
      :has-delete="true"
      :show-unverified="false"
      :has-resend-verification="false"
      @confirm-delete="confirmDelete"
      @toggle-status="toggleStatus"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      title="Delete Administrator Account"
      confirm-text="Delete"
      type="danger"
      @close="showDeleteModal = false"
      @confirm="deleteAdmin"
    >
      Are you sure you want to delete <span class="font-medium">{{ adminToDelete?.name || 'this administrator' }}</span>'s account? This action cannot be undone.
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UsersList from '@/Components/users/UsersList.vue'
import ConfirmationModal from '@/Components/users/ConfirmationModal.vue'
import { Plus } from 'lucide-vue-next'

// Layout configuration
defineOptions({
  layout: AdminLayout,
})

// Props from controller
const props = defineProps({
  admins: {
    type: Array,
    required: true
  }
})

// Local state
const showDeleteModal = ref(false)
const adminToDelete = ref(null)

// Methods
const confirmDelete = (admin) => {
  adminToDelete.value = admin
  showDeleteModal.value = true
}

const deleteAdmin = () => {
  if (!adminToDelete.value) return
  
  router.delete(route('admin.users.admins.delete', adminToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      adminToDelete.value = null
    }
  })
}

const toggleStatus = (admin) => {
  const newStatus = !admin.is_active
  
  router.put(route('admin.users.admins.update', admin.id), {
    name: admin.name,
    email: admin.email,
    username: admin.username,
    is_active: newStatus
  })
}
</script> 