<template>
  <Head title="Audit Log" />
  <div class="space-y-4 sm:space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Audit Log</h1>
        <p class="text-sm sm:text-base text-gray-600 mt-1">Complete record of system activity</p>
      </div>
      <div class="flex flex-wrap gap-2 sm:space-x-3">
        <button 
          @click="applyFilters" 
          class="bg-teal-600 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white hover:bg-teal-700 flex items-center shadow-sm"
        >
          <Search class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
          <span>Apply Filters</span>
        </button>
        <button 
          @click="resetFilters" 
          class="bg-gray-200 border border-gray-300 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-300 flex items-center shadow-sm"
        >
          <RefreshCw class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-gray-500" />
          <span>Reset</span>
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-xl border border-gray-100 p-4 sm:p-6">
      <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div>
          <label for="user_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">User</label>
          <select
            id="user_id"
            v-model="filterForm.user_id"
            class="w-full rounded-md border border-gray-300 py-1.5 sm:py-2 pl-2 sm:pl-3 pr-6 sm:pr-10 text-xs sm:text-sm"
          >
            <option value="">All Users</option>
            <option value="null">System</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.role }})
            </option>
          </select>
        </div>
        <div>
          <label for="action_type" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Action Type</label>
          <select
            id="action_type"
            v-model="filterForm.action_type"
            class="w-full rounded-md border border-gray-300 py-1.5 sm:py-2 pl-2 sm:pl-3 pr-6 sm:pr-10 text-xs sm:text-sm"
          >
            <option value="">All Action Types</option>
            <option v-for="actionType in actionTypes" :key="actionType" :value="actionType">
              {{ formatActionType(actionType) }}
            </option>
          </select>
        </div>
        <div>
          <label for="target_entity" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Target Entity</label>
          <select
            id="target_entity"
            v-model="filterForm.target_entity"
            class="w-full rounded-md border border-gray-300 py-1.5 sm:py-2 pl-2 sm:pl-3 pr-6 sm:pr-10 text-xs sm:text-sm"
          >
            <option value="">All Entities</option>
            <option v-for="entity in targetEntities" :key="entity" :value="entity">
              {{ entity }}
            </option>
          </select>
        </div>
        <div>
          <label for="date_from" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Date Range</label>
          <div class="flex space-x-2">
            <input
              id="date_from"
              type="date"
              v-model="filterForm.date_from"
              class="w-full rounded-md border border-gray-300 py-1.5 sm:py-2 px-2 sm:px-3 text-xs sm:text-sm"
              placeholder="From"
            />
            <input
              id="date_to"
              type="date"
              v-model="filterForm.date_to"
              class="w-full rounded-md border border-gray-300 py-1.5 sm:py-2 px-2 sm:px-3 text-xs sm:text-sm"
              placeholder="To"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <!-- Mobile view: Cards -->
        <div class="block sm:hidden px-4 py-3 space-y-3">
          <div v-for="log in logs.data" :key="log.id" class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm">
            <div class="flex justify-between items-center mb-2">
              <span :class="getActionTypeClass(log.action_type)" class="px-2 py-0.5 inline-flex text-2xs leading-5 font-semibold rounded-full">
                {{ formatActionType(log.action_type) }}
              </span>
              <span class="text-2xs text-gray-500">{{ formatDate(log.created_at) }}</span>
            </div>
            <p class="text-xs text-gray-700 mb-2">{{ log.details }}</p>
            <div class="flex items-center mt-2">
              <div v-if="log.user" class="flex items-center">
                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gradient-to-r from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-2xs">
                  {{ log.user.name.charAt(0) }}
                </div>
                <div class="ml-2">
                  <div class="text-2xs font-medium text-gray-900">{{ log.user.name }}</div>
                  <div class="text-2xs text-gray-500">{{ log.user_role }}</div>
                </div>
              </div>
              <div v-else class="flex items-center">
                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gradient-to-r from-gray-400 to-gray-600 flex items-center justify-center text-white font-bold text-2xs">
                  S
                </div>
                <div class="ml-2">
                  <div class="text-2xs font-medium text-gray-900">SYSTEM</div>
                  <div class="text-2xs text-gray-500">Automated action</div>
                </div>
              </div>
              <div class="ml-auto text-2xs text-gray-500">
                {{ log.target_entity || 'N/A' }}
                <span v-if="log.target_id" class="text-gray-400">#{{ log.target_id }}</span>
              </div>
            </div>
          </div>
          <div v-if="logs.data.length === 0" class="text-center py-6 text-xs text-gray-500">
            No audit log entries found matching the current filters.
          </div>
        </div>

        <!-- Desktop view: Table -->
        <table class="min-w-full divide-y divide-gray-200 hidden sm:table">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(log.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="log.user" class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold">
                    {{ log.user.name.charAt(0) }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ log.user.name }}</div>
                    <div class="text-xs text-gray-500">{{ log.user_role }}</div>
                  </div>
                </div>
                <div v-else class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-r from-gray-400 to-gray-600 flex items-center justify-center text-white font-bold">
                    S
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">SYSTEM</div>
                    <div class="text-xs text-gray-500">Automated action</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getActionTypeClass(log.action_type)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatActionType(log.action_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ log.target_entity || 'N/A' }}
                <span v-if="log.target_id" class="text-gray-400">#{{ log.target_id }}</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 max-w-md">
                {{ log.details }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ log.ip_address }}
              </td>
            </tr>
            <tr v-if="logs.data.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                No audit log entries found matching the current filters.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            @click="goToPage(logs.current_page - 1)"
            :disabled="logs.current_page === 1"
            :class="[
              'relative inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md',
              logs.current_page === 1 
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                : 'bg-white text-gray-700 hover:bg-gray-50'
            ]"
          >
            Previous
          </button>
          <div class="mx-2 text-xs text-gray-700">
            {{ logs.current_page }} / {{ logs.last_page }}
          </div>
          <button
            @click="goToPage(logs.current_page + 1)"
            :disabled="logs.current_page === logs.last_page"
            :class="[
              'relative inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md',
              logs.current_page === logs.last_page
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                : 'bg-white text-gray-700 hover:bg-gray-50'
            ]"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ logs.from || 0 }}</span>
              to
              <span class="font-medium">{{ logs.to || 0 }}</span>
              of
              <span class="font-medium">{{ logs.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <button
                @click="goToPage(logs.current_page - 1)"
                :disabled="logs.current_page === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <span class="sr-only">Previous</span>
                <ChevronLeft class="h-5 w-5" />
              </button>
              
              <button
                v-for="page in paginationRange"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium',
                  logs.current_page === page
                    ? 'z-10 bg-teal-50 border-teal-500 text-teal-600'
                    : 'bg-white text-gray-500 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
              
              <button
                @click="goToPage(logs.current_page + 1)"
                :disabled="logs.current_page === logs.last_page"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <span class="sr-only">Next</span>
                <ChevronRight class="h-5 w-5" />
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { 
  Search, 
  RefreshCw, 
  ChevronLeft, 
  ChevronRight 
} from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  logs: Object,
  filters: Object,
  actionTypes: Array,
  targetEntities: Array,
  users: Array
});

const filterForm = ref({
  user_id: props.filters.user_id || '',
  action_type: props.filters.action_type || '',
  target_entity: props.filters.target_entity || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
});

// Format date to readable format
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  }).format(date);
};

// Format action type to be more readable
const formatActionType = (actionType) => {
  if (!actionType) return '';
  return actionType
    .replace(/_/g, ' ')
    .split(' ')
    .map(word => word.charAt(0) + word.slice(1).toLowerCase())
    .join(' ');
};

// Get CSS class for action type badge
const getActionTypeClass = (actionType) => {
  const baseClasses = 'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ';
  
  if (!actionType) return baseClasses + 'bg-gray-100 text-gray-800';
  
  if (actionType.includes('CREATE')) {
    return baseClasses + 'bg-green-100 text-green-800';
  } else if (actionType.includes('UPDATE') || actionType.includes('EDIT')) {
    return baseClasses + 'bg-blue-100 text-blue-800';
  } else if (actionType.includes('DELETE') || actionType.includes('REMOVE')) {
    return baseClasses + 'bg-red-100 text-red-800';
  } else if (actionType.includes('GRANT')) {
    return baseClasses + 'bg-purple-100 text-purple-800';
  } else if (actionType.includes('REVOKE')) {
    return baseClasses + 'bg-yellow-100 text-yellow-800';
  } else if (actionType.includes('LOGIN') || actionType.includes('LOGOUT')) {
    return baseClasses + 'bg-indigo-100 text-indigo-800';
  } else if (actionType.includes('STATUS')) {
    return baseClasses + 'bg-teal-100 text-teal-800';
  }
  
  return baseClasses + 'bg-gray-100 text-gray-800';
};

// Compute pagination range (show 5 pages at most)
const paginationRange = computed(() => {
  const totalPages = props.logs.last_page;
  const currentPage = props.logs.current_page;
  
  if (totalPages <= 5) {
    return Array.from({ length: totalPages }, (_, i) => i + 1);
  }
  
  let startPage = Math.max(currentPage - 2, 1);
  let endPage = startPage + 4;
  
  if (endPage > totalPages) {
    endPage = totalPages;
    startPage = Math.max(endPage - 4, 1);
  }
  
  return Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
});

// Navigate to specific page
const goToPage = (page) => {
  if (page < 1 || page > props.logs.last_page) return;
  
  router.get(route('admin.audit_log'), {
    ...filterForm.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

// Apply the current filters
const applyFilters = () => {
  router.get(route('admin.audit_log'), filterForm.value);
};

// Reset all filters
const resetFilters = () => {
  filterForm.value = {
    user_id: '',
    action_type: '',
    target_entity: '',
    date_from: '',
    date_to: ''
  };
  
  router.get(route('admin.audit_log'), filterForm.value);
};
</script>

<script>
export default {
  layout: AdminLayout
}
</script> 