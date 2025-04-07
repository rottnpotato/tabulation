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
                <Link 
                  :href="route('admin.users.organizers.show', organizer.id)" 
                  class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                >
                  {{ organizer.name }}
                </Link>
              </div>
            </li>
            <li class="flex">
              <div class="flex items-center">
                <ChevronRight class="flex-shrink-0 h-5 w-5 text-gray-400" />
                <span class="ml-4 text-sm font-medium text-teal-600">Edit</span>
              </div>
            </li>
          </ol>
        </nav>
      </div>

      <!-- Edit Form -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <UserForm
          :user="organizer"
          user-type="organizer"
          :editing="true"
          submit-route="admin.users.organizers.update"
          :route-params="{ id: organizer.id }"
          :cancel-route="route('admin.users.organizers.show', { id: organizer.id })"
          :errors="errors"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UserForm from '@/Components/users/UserForm.vue'
import { ChevronRight } from 'lucide-vue-next'

defineProps({
  organizer: {
    type: Object,
    required: true
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})
</script> 