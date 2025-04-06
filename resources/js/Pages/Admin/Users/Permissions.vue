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
      <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-purple-500 to-indigo-600">
        <h3 class="text-lg leading-6 font-medium text-white">Permission Management</h3>
        <p class="mt-1 max-w-2xl text-sm text-purple-100">
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
                  ? 'border-indigo-500 text-indigo-600'
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
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
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
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                  />
                  <label :for="perm.id" class="ml-3 text-sm text-gray-700">{{ perm.name }}</label>
                </div>
              </div>
              
              <div class="mt-6 flex justify-end">
                <button
                  @click="savePermissions('organizer')"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                  />
                  <label :for="perm.id" class="ml-3 text-sm text-gray-700">{{ perm.name }}</label>
                </div>
              </div>
              
              <div class="mt-6 flex justify-end">
                <button
                  @click="savePermissions('tabulator')"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                  />
                  <label :for="perm.id" class="ml-3 text-sm text-gray-700">{{ perm.name }}</label>
                </div>
              </div>
              
              <div class="mt-6 flex justify-end">
                <button
                  @click="savePermissions('judge')"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// Layout configuration
defineOptions({
  layout: AdminLayout,
})

// Props from controller
const props = defineProps({
  permissions: {
    type: Object,
    required: true
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

// Permission lists (would be populated from props in a real implementation)
const adminPermissions = ref([
  { id: 'admin_create_pageant', name: 'Create Pageants', granted: true },
  { id: 'admin_edit_pageant', name: 'Edit Pageants', granted: true },
  { id: 'admin_delete_pageant', name: 'Delete Pageants', granted: true },
  { id: 'admin_manage_users', name: 'Manage All Users', granted: true },
  { id: 'admin_view_audit_log', name: 'View Audit Logs', granted: true },
  { id: 'admin_system_settings', name: 'Configure System Settings', granted: true },
  { id: 'admin_grant_permissions', name: 'Grant/Revoke Permissions', granted: true },
  { id: 'admin_view_reports', name: 'Access All Reports', granted: true }
])

const organizerPermissions = ref([
  { id: 'organizer_edit_own_pageant', name: 'Edit Own Pageants', granted: true },
  { id: 'organizer_create_contestant', name: 'Create & Edit Contestants', granted: true },
  { id: 'organizer_manage_judges', name: 'Assign Judges', granted: true },
  { id: 'organizer_manage_criteria', name: 'Configure Criteria & Scoring', granted: true },
  { id: 'organizer_view_results', name: 'View Results & Reports', granted: true },
  { id: 'organizer_publish_results', name: 'Publish Final Results', granted: true },
  { id: 'organizer_export_data', name: 'Export Pageant Data', granted: true },
  { id: 'organizer_assign_tabulators', name: 'Assign Tabulators', granted: true }
])

const tabulatorPermissions = ref([
  { id: 'tabulator_view_judges', name: 'View Judge Information', granted: true },
  { id: 'tabulator_view_scores', name: 'View Individual Scores', granted: true },
  { id: 'tabulator_tabulate_results', name: 'Tabulate & Verify Results', granted: true },
  { id: 'tabulator_print_reports', name: 'Generate Score Reports', granted: true },
  { id: 'tabulator_view_contestants', name: 'View Contestant Details', granted: true },
  { id: 'tabulator_edit_scores', name: 'Edit Submitted Scores', granted: false },
  { id: 'tabulator_export_data', name: 'Export Score Data', granted: true },
  { id: 'tabulator_publish_results', name: 'Publish Results', granted: false }
])

const judgePermissions = ref([
  { id: 'judge_view_criteria', name: 'View Scoring Criteria', granted: true },
  { id: 'judge_submit_scores', name: 'Submit Scores', granted: true },
  { id: 'judge_edit_own_scores', name: 'Edit Own Submitted Scores', granted: true },
  { id: 'judge_view_contestants', name: 'View Contestant Profiles', granted: true },
  { id: 'judge_view_other_judges', name: 'View Other Judges Profiles', granted: false },
  { id: 'judge_view_results', name: 'View Results', granted: false },
  { id: 'judge_export_scores', name: 'Export Own Scores', granted: false }
])

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