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
  judges: {
    type: Array,
    required: false,
    default: () => []
  }
})

// Safely handle the judges data
const judgesData = computed(() => {
  console.log('Props received:', props)
  console.log('Judges prop:', props.judges)
  console.log('Is judges array?', Array.isArray(props.judges))
  return Array.isArray(props.judges) ? props.judges : []
})

</script> 