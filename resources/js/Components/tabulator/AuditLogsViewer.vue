<template>
  <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <History class="h-6 w-6 text-white" />
          <h3 class="text-lg font-semibold text-white">Score Edit History</h3>
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

    <!-- Loading State -->
    <div v-if="loading && !logs.length" class="px-6 py-12 text-center">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-4">
        <RefreshCw class="h-6 w-6 text-blue-600 animate-spin" />
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

    <!-- Show More Button -->
    <div v-if="logs.length >= 50" class="px-6 py-4 bg-gray-50 border-t border-gray-100 text-center">
      <p class="text-sm text-gray-600">
        Showing most recent 50 entries. 
        <span class="text-blue-600 font-medium">Older entries are archived.</span>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { History, RefreshCw, FileText, Edit2, Plus, User, Clock } from 'lucide-vue-next'
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

const fetchLogs = async () => {
  if (!props.pageantId || !props.roundId) {
    return
  }

  loading.value = true
  error.value = null

  try {
    const response = await fetch(
      route('tabulator.scores.audit-logs', {
        pageantId: props.pageantId,
        roundId: props.roundId
      }),
      {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      }
    )

    if (!response.ok) {
      throw new Error('Failed to fetch audit logs')
    }

    const data = await response.json()
    logs.value = data.audit_logs || []
  } catch (err) {
    console.error('Error fetching audit logs:', err)
    error.value = 'Failed to load audit logs'
  } finally {
    loading.value = false
  }
}

const refreshLogs = () => {
  fetchLogs()
}

// Watch for prop changes
watch(() => [props.pageantId, props.roundId], () => {
  fetchLogs()
}, { immediate: false })

onMounted(() => {
  fetchLogs()
})
</script>
