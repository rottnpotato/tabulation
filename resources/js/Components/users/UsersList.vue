<template>
  <div>
    <!-- Filters and Search -->
    <div class="mb-6 bg-white shadow rounded-lg p-4 border-t-4 border-teal-500">
      <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
        <div class="flex-1">
          <label for="search" class="sr-only">Search Users</label>
          <div class="relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search class="h-5 w-5 text-gray-400" />
            </div>
            <input
              type="text"
              id="search"
              v-model="search"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
              :placeholder="`Search by name, email, or username`"
            />
          </div>
        </div>
        <div class="w-full sm:w-auto">
          <select
            v-model="statusFilter"
            class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
          >
            <option value="all">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option v-if="showUnverified" value="unverified">Unverified</option>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <button
            @click="resetFilters"
            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
          >
            <RefreshCw class="mr-2 h-4 w-4" />
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow overflow-hidden rounded-lg">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-teal-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                User
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                Username
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                Registered
              </th>
              <th v-if="hasPageantsColumn" scope="col" class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                {{ pageantColumnTitle }}
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-teal-800 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody v-if="filteredUsers.length" class="bg-white divide-y divide-gray-200">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50 transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 rounded-full" :class="[avatarBgColor]">
                    <component :is="userIcon" class="h-10 w-10 p-2" :class="[avatarIconColor]" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ user.username }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                  :class="getStatusClasses(user)"
                >
                  {{ user.status }}
                  <span v-if="showUnverified && !user.email_verified" class="ml-1">(Unverified)</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ user.created_at }}
              </td>
              <td v-if="hasPageantsColumn" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <Link 
                  v-if="user.pageants_count > 0"
                  :href="route(detailRoute, user.id)" 
                  class="text-teal-600 hover:text-teal-900 font-medium"
                >
                  {{ user.pageants_count }} {{ getPageantLabel(user.pageants_count) }}
                </Link>
                <span v-else>None</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <!-- View button -->
                  <Link
                    :href="route(detailRoute, user.id)"
                    class="text-teal-600 hover:text-teal-900 bg-teal-50 p-1.5 rounded-md transition-colors duration-150"
                    title="View Details"
                  >
                    <Eye class="h-4 w-4" />
                  </Link>
                  
                  <!-- Resend verification button (if applicable) -->
                  <button
                    v-if="showUnverified && !user.email_verified && hasResendVerification"
                    @click="$emit('resend-verification', user)"
                    class="text-amber-600 hover:text-amber-900 bg-amber-50 p-1.5 rounded-md transition-colors duration-150"
                    title="Resend Verification Email"
                  >
                    <Mail class="h-4 w-4" />
                  </button>

                  <!-- User status toggle button -->
                  <button
                    v-if="hasToggleStatus"
                    @click="$emit('toggle-status', user)"
                    class="text-blue-600 hover:text-blue-900 bg-blue-50 p-1.5 rounded-md transition-colors duration-150"
                    :title="user.status === 'Active' ? 'Deactivate Account' : 'Activate Account'"
                  >
                    <Power class="h-4 w-4" />
                  </button>

                  <!-- Edit button -->
                  <Link
                    v-if="hasEdit && canEdit(user)"
                    :href="route(editRoute, user.id)"
                    class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1.5 rounded-md transition-colors duration-150"
                    title="Edit User"
                  >
                    <Edit2 class="h-4 w-4" />
                  </Link>

                  <!-- Delete button -->
                  <button
                    v-if="hasDelete && canDelete(user)"
                    @click="$emit('confirm-delete', user)"
                    class="text-red-600 hover:text-red-900 bg-red-50 p-1.5 rounded-md transition-colors duration-150"
                    title="Delete User"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td :colspan="hasPageantsColumn ? 6 : 5" class="px-6 py-10 text-center text-gray-500 bg-gray-50 border-b">
                <div class="flex flex-col items-center justify-center">
                  <component :is="emptyStateIcon" class="h-12 w-12 text-gray-400 mb-2" />
                  <h3 class="text-lg font-medium text-gray-900 mb-1">No {{ userTypeLabel }} found</h3>
                  <p class="text-gray-500 max-w-md">
                    {{ 
                      search || statusFilter !== 'all' 
                        ? 'Try adjusting your search or filter to find what you\'re looking for.' 
                        : `Get started by creating your first ${userTypeSingular} account.` 
                    }}
                  </p>
                  <Link
                    v-if="!search && statusFilter === 'all' && createRoute"
                    :href="route(createRoute)"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700"
                  >
                    <UserPlus class="mr-2 h-4 w-4" />
                    Create First {{ userTypeSingular }}
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="hidden sm:block">
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">1</span> to <span class="font-medium">{{ Math.min(10, filteredUsers.length) }}</span> of <span class="font-medium">{{ filteredUsers.length }}</span> results
            </p>
          </div>
          <div class="flex-1 flex justify-between sm:justify-end space-x-3">
            <button 
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150"
              :disabled="true"
            >
              Previous
            </button>
            <button 
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150"
              :disabled="true"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { 
  Search, 
  RefreshCw, 
  Eye, 
  Mail, 
  Trash2, 
  Edit2,
  User2,
  UserPlus,
  UserX,
  UserCog,
  Users,
  Gavel,
  Calculator,
  Shield,
  Power
} from 'lucide-vue-next'

const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  userType: {
    type: String,
    required: true,
    validator: (value) => ['organizer', 'admin', 'tabulator', 'judge'].includes(value)
  },
  detailRoute: {
    type: String,
    required: true
  },
  createRoute: {
    type: String,
    default: null
  },
  editRoute: {
    type: String,
    default: null
  },
  hasPageantsColumn: {
    type: Boolean,
    default: true
  },
  pageantColumnTitle: {
    type: String,
    default: 'Pageants'
  },
  hasToggleStatus: {
    type: Boolean,
    default: true
  },
  hasEdit: {
    type: Boolean,
    default: true
  },
  hasDelete: {
    type: Boolean,
    default: true
  },
  hasResendVerification: {
    type: Boolean,
    default: false
  },
  showUnverified: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle-status', 'confirm-delete', 'resend-verification'])

// Local state
const search = ref('')
const statusFilter = ref('all')

// User type labels based on props
const userTypeLabel = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'Organizers'
    case 'admin': return 'Administrators'
    case 'tabulator': return 'Tabulators'
    case 'judge': return 'Judges'
    default: return 'Users'
  }
})

const userTypeSingular = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'Organizer'
    case 'admin': return 'Administrator'
    case 'tabulator': return 'Tabulator'
    case 'judge': return 'Judge'
    default: return 'User'
  }
})

// Icon for user avatar based on user type
const userIcon = computed(() => {
  switch (props.userType) {
    case 'organizer': return Users
    case 'admin': return Shield
    case 'tabulator': return Calculator
    case 'judge': return Gavel
    default: return User2
  }
})

// Empty state icon
const emptyStateIcon = computed(() => {
  switch (props.userType) {
    case 'organizer': return UserX
    case 'admin': return UserCog
    case 'tabulator': return Calculator
    case 'judge': return Gavel
    default: return UserX
  }
})

// Avatar colors based on user type
const avatarBgColor = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'bg-orange-100'
    case 'admin': return 'bg-teal-100'
    case 'tabulator': return 'bg-blue-100'
    case 'judge': return 'bg-amber-100'
    default: return 'bg-gray-100'
  }
})

const avatarIconColor = computed(() => {
  switch (props.userType) {
    case 'organizer': return 'text-orange-600'
    case 'admin': return 'text-teal-600'
    case 'tabulator': return 'text-blue-600'
    case 'judge': return 'text-amber-600'
    default: return 'text-gray-600'
  }
})

// Computed
const filteredUsers = computed(() => {
  let result = props.users

  // Apply search filter
  if (search.value) {
    const searchTerm = search.value.toLowerCase()
    result = result.filter(user => 
      (user.name && user.name.toLowerCase().includes(searchTerm)) || 
      (user.email && user.email.toLowerCase().includes(searchTerm)) || 
      (user.username && user.username.toLowerCase().includes(searchTerm))
    )
  }

  // Apply status filter
  if (statusFilter.value !== 'all') {
    if (statusFilter.value === 'unverified' && props.showUnverified) {
      result = result.filter(user => !user.email_verified)
    } else {
      result = result.filter(user => 
        user.status && user.status.toLowerCase() === statusFilter.value.toLowerCase()
      )
    }
  }

  return result
})

// Methods
const resetFilters = () => {
  search.value = ''
  statusFilter.value = 'all'
}

const getStatusClasses = (user) => {
  if (props.showUnverified && !user.email_verified) {
    return 'bg-yellow-100 text-yellow-800'
  }
  
  return user.status === 'Active' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-red-100 text-red-800'
}

const getPageantLabel = (count) => {
  return count === 1 ? 'pageant' : 'pageants'
}

// Determine if a user can be edited (admins can't edit themselves)
const canEdit = (user) => {
  if (props.userType === 'admin' && user.is_current_user) {
    return false;
  }
  return true;
}

// Determine if a user can be deleted (admins can't delete themselves, users with pageants can't be deleted)
const canDelete = (user) => {
  if (props.userType === 'admin' && user.is_current_user) {
    return false;
  }
  if (user.pageants_count && user.pageants_count > 0) {
    return false;
  }
  return true;
}
</script> 