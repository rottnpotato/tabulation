<template>
  <Head title="Create Administrator" />
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
              <span class="ml-4 text-sm font-medium text-teal-600">Create New Administrator</span>
            </div>
          </li>
        </ol>
      </nav>
    </div>
    
    <!-- User Form Component -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="px-4 py-5 sm:p-6">
        <UserForm
          submit-route="admin.users.admins.store"
          cancel-route="admin.users.admins"
          is-admin-form="true"
          submit-label="Create Administrator"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UserForm from '@/Components/users/UserForm.vue'
import { ChevronRight } from 'lucide-vue-next'

// Layout configuration
defineOptions({
  layout: AdminLayout,
})
</script> 