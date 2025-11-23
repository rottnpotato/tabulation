<template>
  <Head title="Tabulator Management" />
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header with title -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Tabulators</h1>
        <p class="mt-1 text-sm text-gray-600">View all tabulator accounts in the system.</p>
      </div>
    </div>

    <!-- User List Component -->
    <UsersList
      :users="tabulatorsData"
      user-type="tabulator"
      detail-route="admin.users.tabulators.show"
      edit-route="admin.users.tabulators.edit"
      user-type-label="Tabulators"
      user-type-singular="Tabulator"
      pageant-column-title="Assigned Pageants"
      :has-pageants-column="true"
      :has-toggle-status="true"
      :has-edit="true"
      :has-delete="true"
      :show-unverified="false"
      :has-resend-verification="false"
      @toggle-status="toggleStatus"
      @confirm-delete="confirmDelete"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      title="Delete Tabulator Account"
      confirm-text="Delete"
      type="danger"
      @close="showDeleteModal = false"
      @confirm="deleteTabulator"
    >
      Are you sure you want to delete <span class="font-medium">{{ tabulatorToDelete?.name || 'this tabulator' }}</span>'s account? This action cannot be undone.
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UsersList from '@/Components/users/UsersList.vue'
import ConfirmationModal from '@/Components/users/ConfirmationModal.vue'

// Layout configuration
defineOptions({
  layout: AdminLayout,
})

// Props from controller
const props = defineProps({
  tabulators: {
    type: Array,
    required: false,
    default: () => []
  }
})

// Local state
const showDeleteModal = ref(false)
const tabulatorToDelete = ref(null)

// Safely handle the tabulators data
const tabulatorsData = computed(() => {
  return Array.isArray(props.tabulators) ? props.tabulators : []
})

// Methods
const confirmDelete = (tabulator) => {
  tabulatorToDelete.value = tabulator
  showDeleteModal.value = true
}

const deleteTabulator = () => {
  if (!tabulatorToDelete.value) return
  
  router.delete(route('admin.users.tabulators.delete', tabulatorToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      tabulatorToDelete.value = null
    }
  })
}

const toggleStatus = (tabulator) => {
  const newStatus = !tabulator.is_active
  
  router.put(route('admin.users.tabulators.update', tabulator.id), {
    name: tabulator.name,
    email: tabulator.email,
    username: tabulator.username,
    is_active: newStatus
  })
}
</script>