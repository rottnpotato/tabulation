<template>
  <Head title="Judge Details" />
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
              <Link :href="route('admin.users.judges')" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Judges</Link>
            </div>
          </li>
          <li class="flex">
            <div class="flex items-center">
              <ChevronRight class="flex-shrink-0 h-5 w-5 text-gray-400" />
              <span class="ml-4 text-sm font-medium text-teal-600">{{ judge.name }}</span>
            </div>
          </li>
        </ol>
      </nav>
    </div>

    <!-- Judge Profile Card -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
      <div class="px-4 py-5 sm:px-6 bg-amber-50 border-b border-amber-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="h-16 w-16 rounded-full bg-amber-100 flex items-center justify-center">
              <Gavel class="h-10 w-10 text-amber-600" />
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900">{{ judge.name }}</h2>
              <p class="text-sm text-gray-600">Judge</p>
              <div class="flex items-center mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                      :class="judge.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ judge.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>
          <div class="flex space-x-3">
            <Link 
              v-if="judge.id"
              :href="route('admin.users.judges.edit', judge.id)"
              class="inline-flex items-center px-4 py-2 border border-teal-300 rounded-md shadow-sm text-sm font-medium text-teal-700 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
              <Edit class="h-4 w-4 mr-2" />
              Edit Judge
            </Link>
          </div>
        </div>
      </div>
      
      <div class="border-t border-gray-200">
        <dl>
          <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Username</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ judge.username }}</dd>
          </div>
          <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Email Address</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ judge.email }}</dd>
          </div>
          <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Account Status</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="judge.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                {{ judge.is_active ? 'Active' : 'Inactive' }}
              </span>
            </dd>
          </div>
          <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Date Created</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ judge.created_at }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Assigned Pageants Section -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Assigned Pageants</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Pageants where this judge is assigned to evaluate contestants.</p>
      </div>
      
      <div class="border-t border-gray-200">
        <div v-if="judge.pageants && judge.pageants.length > 0" class="divide-y divide-gray-200">
          <div v-for="pageant in judge.pageants" :key="pageant.id" 
               class="px-4 py-4 sm:px-6 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-900">{{ pageant.name }}</h4>
                <div class="mt-1 flex items-center text-xs text-gray-500">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2"
                        :class="getStatusBadgeClass(pageant.status)">
                    {{ pageant.status }}
                  </span>
                  <span v-if="pageant.start_date">{{ pageant.start_date }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <Gavel class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No assigned pageants</h3>
          <p class="mt-1 text-sm text-gray-500">This judge is not currently assigned to any pageants.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ChevronRight, Gavel, Edit } from 'lucide-vue-next'

// Layout configuration
defineOptions({
  layout: AdminLayout,
})

// Props from controller
const props = defineProps({
  judge: {
    type: Object,
    required: true
  }
})

// Helper function for status badge styling
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'upcoming':
      return 'bg-blue-100 text-blue-800'
    case 'ongoing':
      return 'bg-green-100 text-green-800'
    case 'completed':
      return 'bg-gray-100 text-gray-800'
    case 'archived':
      return 'bg-purple-100 text-purple-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}
</script>
