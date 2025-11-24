<template>
  <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <History class="h-6 w-6 text-white" />
          <h3 class="text-lg font-semibold text-white">Score Edit History</h3>
          <span v-if="pagination.total" class="text-sm text-teal-100">
            ({{ pagination.total }} {{ pagination.total === 1 ? 'entry' : 'entries' }})
          </span>
        </div>
        <button
          @click="refreshLogs"
          :disabled="loading"
          class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg text-sm font-medium transition-all disabled:opacity-50"
        >
          <RefreshCw :class="['h-4 w-4 mr-1.5', { 'animate-spin': loading }]" />
          Refresh
        </button>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <div class="flex items-center gap-2 mb-3">
        <Filter class="h-4 w-4 text-gray-600" />
        <span class="text-sm font-medium text-gray-700">Filters</span>
        <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="ml-auto text-xs text-teal-600 hover:text-teal-700 font-medium"
        >
          Clear All
        </button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Search -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search in details..."
              class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              @input="debouncedFetch"
            />
          </div>
        </div>

        <!-- Action Type -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Action Type</label>
          <select
            v-model="filters.action_type"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            @change="fetchLogs"
          >
            <option value="">All Actions</option>
            <option value="SCORE_CREATED">Created</option>
            <option value="SCORE_UPDATED">Updated</option>
          </select>
        </div>

        <!-- User Filter -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">User</label>
          <select
            v-model="filters.user_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            @change="fetchLogs"
          >
            <option value="">All Users</option>
            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>

        <!-- Date Range -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Date From</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            @change="fetchLogs"
          />
        </div>

        <div class="md:col-start-4">
          <label class="block text-xs font-medium text-gray-700 mb-1">Date To</label>
          <input
            v-model="filters.date_to"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            @change="fetchLogs"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !logs.length" class="px-6 py-12 text-center">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-teal-100 mb-4">
        <RefreshCw class="h-6 w-6 text-teal-600 animate-spin" />
      </div>
      <p class="text-gray-600">Loading audit logs...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!logs.length" class="px-6 py-12 text-center">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
        <FileText class="h-6 w-6 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Edit History</h3>
      <p class="text-gray-600">No score edits have been recorded for this round yet.</p>
    </div>

    <!-- Logs List -->
    <div v-else class="divide-y divide-gray-100">
      <div
        v-for="log in logs"
        :key="log.id"
        class="px-6 py-4 hover:bg-gray-50 transition-colors"
      >
        <div class="flex items-start space-x-3">
          <!-- Icon -->
          <div class="flex-shrink-0 mt-1">
            <div
              :class="[
                'w-8 h-8 rounded-full flex items-center justify-center',
                log.action_type === 'SCORE_UPDATED' ? 'bg-amber-100' : 'bg-green-100'
              ]"
            >
              <Edit2
                v-if="log.action_type === 'SCORE_UPDATED'"
                class="h-4 w-4 text-amber-600"
              />
              <Plus
                v-else
                class="h-4 w-4 text-green-600"
              />
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <p class="text-sm text-gray-900">{{ log.details }}</p>
                <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
                  <span class="flex items-center">
                    <User class="h-3 w-3 mr-1" />
                    {{ log.user?.name || 'System' }}
                  </span>
                  <span class="flex items-center">
                    <Clock class="h-3 w-3 mr-1" />
                    {{ log.created_at }}
                  </span>
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                      log.action_type === 'SCORE_UPDATED' 
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ log.action_type === 'SCORE_UPDATED' ? 'Updated' : 'Created' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-100">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing 
          <span class="font-medium">{{ pagination.from }}</span>
          to 
          <span class="font-medium">{{ pagination.to }}</span>
          of 
          <span class="font-medium">{{ pagination.total }}</span>
          results
        </div>
        
        <div class="flex items-center gap-2">
          <!-- Per Page Selector -->
          <select
            v-model="filters.per_page"
            class="px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            @change="fetchLogs"
          >
            <option :value="10">10 per page</option>
            <option :value="20">20 per page</option>
            <option :value="50">50 per page</option>
            <option :value="100">100 per page</option>
          </select>

          <!-- Pagination Buttons -->
          <div class="flex items-center gap-1">
            <button
              @click="goToPage(1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <ChevronsLeft class="h-4 w-4" />
            </button>
            <button
              @click="goToPage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <ChevronLeft class="h-4 w-4" />
            </button>
            
            <span class="px-4 py-1 text-sm font-medium text-gray-700">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>
            
            <button
              @click="goToPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <ChevronRight class="h-4 w-4" />
            </button>
            <button
              @click="goToPage(pagination.last_page)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <ChevronsRight class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { History, RefreshCw, FileText, Edit2, Plus, User, Clock, Filter, Search, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next'
import { route } from 'ziggy-js'

interface AuditLog {
  id: number
  user?: {
    id: number
    name: string
  }
  user_role: string
  action_type: 'SCORE_UPDATED' | 'SCORE_CREATED'
  details: string
  created_at: string
  timestamp: number
}

interface Props {
  pageantId: number
  roundId: number
}

const props = defineProps<Props>()

const logs = ref<AuditLog[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const availableUsers = ref<Array<{ id: number; name: string }>>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  search: '',
  action_type: '',
  user_id: '',
  date_from: '',
  date_to: '',
  per_page: 20,
  page: 1
})

const hasActiveFilters = computed(() => {
  return filters.value.search !== '' ||
    filters.value.action_type !== '' ||
    filters.value.user_id !== '' ||
    filters.value.date_from !== '' ||
    filters.value.date_to !== ''
})

let debounceTimer: ReturnType<typeof setTimeout> | null = null

const debouncedFetch = () => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  debounceTimer = setTimeout(() => {
    fetchLogs()
  }, 500)
}

const fetchLogs = async () => {
  if (!props.pageantId || !props.roundId) {
    return
  }

  loading.value = true
  error.value = null

  try {
    // Build query parameters
    const params = new URLSearchParams()
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.action_type) params.append('action_type', filters.value.action_type)
    if (filters.value.user_id) params.append('user_id', filters.value.user_id.toString())
    if (filters.value.date_from) params.append('date_from', filters.value.date_from)
    if (filters.value.date_to) params.append('date_to', filters.value.date_to)
    params.append('per_page', filters.value.per_page.toString())
    params.append('page', filters.value.page.toString())

    const url = route('tabulator.scores.audit-logs', {
      pageantId: props.pageantId,
      roundId: props.roundId
    }) + '?' + params.toString()

    const response = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Failed to fetch audit logs')
    }

    const data = await response.json()
    logs.value = data.audit_logs || []
    pagination.value = data.pagination || pagination.value
    
    // Update available users for filter (only on first load or when filters are cleared)
    if (data.filters?.users) {
      availableUsers.value = data.filters.users
    }
  } catch (err) {
    console.error('Error fetching audit logs:', err)
    error.value = 'Failed to load audit logs'
  } finally {
    loading.value = false
  }
}

const refreshLogs = () => {
  filters.value.page = 1
  fetchLogs()
}

const clearFilters = () => {
  filters.value = {
    search: '',
    action_type: '',
    user_id: '',
    date_from: '',
    date_to: '',
    per_page: 20,
    page: 1
  }
  fetchLogs()
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.value.page = page
    fetchLogs()
  }
}

// Watch for prop changes
watch(() => [props.pageantId, props.roundId], () => {
  fetchLogs()
}, { immediate: false })

onMounted(() => {
  fetchLogs()

  // Real-time updates
  if (props.pageantId) {
    const channelName = `pageant.${props.pageantId}`
    window.Echo.private(channelName)
      .listen('ScoreUpdated', refreshLogs)
  }
})

onUnmounted(() => {
  if (props.pageantId) {
    const channelName = `pageant.${props.pageantId}`
    // Stop listening to the specific handler, but DO NOT leave the channel
    window.Echo.private(channelName).stopListening('ScoreUpdated', refreshLogs)
  }
})
</script>
