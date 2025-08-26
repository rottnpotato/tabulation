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
      :has-toggle-status="false"
      :has-edit="true"
      :has-delete="false"
      :show-unverified="false"
      :has-resend-verification="false"
    />
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UsersList from '@/Components/users/UsersList.vue'

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

// Safely handle the tabulators data
const tabulatorsData = computed(() => {
  console.log('Props received:', props)
  console.log('Tabulators prop:', props.tabulators)
  console.log('Is tabulators array?', Array.isArray(props.tabulators))
  return Array.isArray(props.tabulators) ? props.tabulators : []
})

</script> 