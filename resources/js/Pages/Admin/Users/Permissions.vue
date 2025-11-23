<template>
  <Head title="User Permissions" />
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header with title -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">User Permissions</h1>
      <p class="mt-1 text-sm text-gray-600">Manage role-based permissions for different user types.</p>
    </div>

    <!-- Permissions Management Interface -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-teal-500 to-emerald-600">
        <h3 class="text-lg leading-6 font-medium text-white">Permission Management</h3>
        <p class="mt-1 max-w-2xl text-sm text-teal-100">
          Define access levels for each user role in the system
        </p>
      </div>

      <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
        <!-- Tabs for Different User Types -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-emerald-500 text-emerald-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Permission Grid for Selected Role -->
        <div class="mt-6">
          <div v-if="activeTab === 'admins'" class="space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-base font-medium text-gray-900">Administrator Permissions</h4>
              <p class="text-sm text-gray-500 mb-4">Administrators have full access to the system by default.</p>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="perm in adminPermissions" :key="perm.id" class="flex items-center">
                  <input
                    type="checkbox"
                    :id="perm.id"
                    :checked="perm.granted"
                    disabled
                    class="h-4 w-4 text-emerald-600 border-gray-300 rounded"
                  />
                  <label :for="perm.id" class="ml-3 text-sm text-gray-700">{{ perm.name }}</label>
                </div>
              </div>
              
              <div class="mt-4 p-3 bg-yellow-50 rounded-md">
                <p class="text-sm text-yellow-700">Administrator permissions are system-defined and cannot be modified for security reasons.</p>
              </div>
            </div>
          </div>
          
          <div v-else-if="activeTab === 'organizers'" class="space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-base font-medium text-gray-900">Organizer Permissions</h4>
              <p class="text-sm text-gray-500 mb-4">Configure what organizers can access and modify.</p>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="perm in organizerPermissions" :key="perm.id" class="flex items-center">
                  <input
                    type="checkbox"
                    :id="perm.id"
                    v-model="perm.granted"
                    class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                  />
                  <label :for="perm.id" class="ml-3 text-sm text-gray-700">{{ perm.name }}</label>
                </div>
              </div>
              
              <div class="mt-6 flex justify-end">
                <button
                  @click="savePermissions('organizer')"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                >
                  Save Organizer Permissions
                </button>
              </div>
            </div>
          </div>
          
          <div v-else-if="activeTab === 'tabulators'" class="space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-base font-medium text-gray-900">Tabulator Permissions</h4>
              <p class="text-sm text-gray-500 mb-4">Configure what tabulators can access and modify.</p>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="perm in tabulatorPermissions" :key="perm.id" class="flex items-center">
                  <input
                    type="checkbox"
                    :id="perm.id"
                    v-model="perm.granted"
                    class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                  />
                  <label :for="perm.id" class="ml-3 text-sm text-gray-700">{{ perm.name }}</label>
                </div>
              </div>
              
              <div class="mt-6 flex justify-end">
                <button
                  @click="savePermissions('tabulator')"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                >
                  Save Tabulator Permissions
                </button>
              </div>
            </div>
          </div>
          
          <div v-else-if="activeTab === 'judges'" class="space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-base font-medium text-gray-900">Judge Permissions</h4>
              <p class="text-sm text-gray-500 mb-4">Configure what judges can access and modify.</p>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="perm in judgePermissions" :key="perm.id" class="flex items-center">
                  <input
                    type="checkbox"
                    :id="perm.id"
                    v-model="perm.granted"
                    class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                  />
                  <label :for="perm.id" class="ml-3 text-sm text-gray-700">{{ perm.name }}</label>
                </div>
              </div>
              
              <div class="mt-6 flex justify-end">
                <button
                  @click="savePermissions('judge')"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                >
                  Save Judge Permissions
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// Layout configuration
defineOptions({
  layout: AdminLayout,
})

// Props from controller
const props = defineProps({
  adminPermissions: {
    type: Array,
    default: () => []
  },
  organizerPermissions: {
    type: Array,
    default: () => []
  },
  tabulatorPermissions: {
    type: Array,
    default: () => []
  },
  judgePermissions: {
    type: Array,
    default: () => []
  }
})

// Local state
const activeTab = ref('admins')
const tabs = [
  { id: 'admins', name: 'Administrators' },
  { id: 'organizers', name: 'Organizers' },
  { id: 'tabulators', name: 'Tabulators' },
  { id: 'judges', name: 'Judges' }
]

// Permission lists - initialize from props
const adminPermissions = ref(props.adminPermissions || [])
const organizerPermissions = ref(props.organizerPermissions || [])
const tabulatorPermissions = ref(props.tabulatorPermissions || [])
const judgePermissions = ref(props.judgePermissions || [])

// Save permissions
const savePermissions = (roleType) => {
  let rolePermissions = null;
  
  switch (roleType) {
    case 'organizer':
      rolePermissions = organizerPermissions.value;
      break;
    case 'tabulator':
      rolePermissions = tabulatorPermissions.value;
      break;
    case 'judge':
      rolePermissions = judgePermissions.value;
      break;
    default:
      return;
  }
  
  router.put(route('admin.users.permissions.update', roleType), {
    permissions: rolePermissions
  }, {
    preserveScroll: true
  });
}
</script> 