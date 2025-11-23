<template>
  <Head title="Judge Management" />
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header with title -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Judges</h1>
        <p class="mt-1 text-sm text-gray-600">View all judge accounts in the system.</p>
      </div>
    </div>

    <!-- User List Component -->
    <UsersList
      :users="judgesData"
      user-type="judge"
      detail-route="admin.users.judges.show"
      edit-route="admin.users.judges.edit"
      user-type-label="Judges"
      user-type-singular="Judge"
      pageant-column-title="Assigned Pageants"
      :has-pageants-column="true"
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
      title="Delete Judge Account"
      confirm-text="Delete"
      type="danger"
      @close="showDeleteModal = false"
      @confirm="deleteJudge"
    >
      Are you sure you want to delete <span class="font-medium">{{ judgeToDelete?.name || 'this judge' }}</span>'s account? This action cannot be undone.
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
  judges: {
    type: Array,
    required: false,
    default: () => []
  }
})

// Local state
const showDeleteModal = ref(false)
const judgeToDelete = ref(null)

// Safely handle the judges data
const judgesData = computed(() => {
  return Array.isArray(props.judges) ? props.judges : []
})

// Methods
const confirmDelete = (judge) => {
  judgeToDelete.value = judge
  showDeleteModal.value = true
}

const deleteJudge = () => {
  if (!judgeToDelete.value) return
  
  router.delete(route('admin.users.judges.delete', judgeToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      judgeToDelete.value = null
    }
  })
}

const toggleStatus = (judge) => {
  const newStatus = !judge.is_active
  
  router.put(route('admin.users.judges.update', judge.id), {
    name: judge.name,
    email: judge.email,
    username: judge.username,
    is_active: newStatus
  })
}
</script>