<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-md overflow-hidden">
      <div class="p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="text-white">
            <h1 class="text-2xl md:text-3xl font-bold">Round Management</h1>
            <p class="mt-1 text-blue-100">{{ pageant.name }}</p>
            <p class="text-sm text-blue-200">Manage round status and set current scoring round</p>
          </div>
          <div v-if="pageant.current_round" class="flex items-center bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
            <div class="text-blue-50">
              <div class="text-xs font-medium">Current Round</div>
              <div class="text-sm font-bold">{{ pageant.current_round.name }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex items-center justify-between p-4 bg-amber-50 border border-amber-200 rounded-lg">
          <div>
            <h3 class="font-medium text-amber-900">Set Current Round</h3>
            <p class="text-sm text-amber-700">Direct judges to score a specific round</p>
          </div>
          <div class="min-w-[160px]">
            <CustomSelect
              v-model="selectedRoundId"
              :options="roundOptions"
              placeholder="Select Round"
              @change="setCurrentRound"
            />
          </div>
        </div>
        
        <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div>
            <h3 class="font-medium text-blue-900">Round Status</h3>
            <p class="text-sm text-blue-700">Monitor round completion status</p>
          </div>
          <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            View Progress
          </button>
        </div>
      </div>
    </div>

    <!-- Rounds Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900">All Rounds</h2>
        <p class="text-sm text-gray-600">Manage individual round settings and lock status</p>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Round</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lock Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="round in rounds" 
              :key="round.id"
              :class="{ 'bg-amber-50': pageant.current_round_id === round.id }"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div>
                    <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                      {{ round.name }}
                      <span v-if="pageant.current_round_id === round.id" class="inline-flex items-center px-2 py-0.5 bg-amber-500 text-white text-xs font-medium rounded-full">
                        Current
                      </span>
                    </div>
                    <div v-if="round.description" class="text-sm text-gray-500">{{ round.description }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                  {{ round.type || 'Standard' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ round.weight }}%
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full"
                  :class="round.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ round.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span 
                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full"
                    :class="round.is_locked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                  >
                    {{ round.is_locked ? '🔒 Locked' : '🔓 Unlocked' }}
                  </span>
                  <div v-if="round.locked_by" class="text-xs text-gray-500">
                    by {{ round.locked_by.name }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  v-if="pageant.current_round_id !== round.id"
                  @click="setCurrentRoundDirect(round.id)"
                  :disabled="actionLoading"
                  class="text-amber-600 hover:text-amber-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ actionLoading ? 'Setting...' : 'Set Current' }}
                </button>
                
                <button
                  v-if="!round.is_locked"
                  @click="lockRound(round.id)"
                  :disabled="actionLoading"
                  class="text-red-600 hover:text-red-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ actionLoading ? 'Locking...' : 'Lock' }}
                </button>
                <button
                  v-else
                  @click="unlockRound(round.id)"
                  :disabled="actionLoading"
                  class="text-green-600 hover:text-green-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ actionLoading ? 'Unlocking...' : 'Unlock' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Round Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <Circle class="h-6 w-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Rounds</p>
            <p class="text-2xl font-bold text-gray-900">{{ rounds.length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <CheckCircle class="h-6 w-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Active Rounds</p>
            <p class="text-2xl font-bold text-gray-900">{{ activeRoundsCount }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center">
          <div class="p-3 bg-red-100 rounded-lg">
            <Lock class="h-6 w-6 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Locked Rounds</p>
            <p class="text-2xl font-bold text-gray-900">{{ lockedRoundsCount }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Circle, CheckCircle, Lock } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'
import NotificationSystem from '../../Components/NotificationSystem.vue'

defineOptions({
  layout: TabulatorLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  rounds: {
    type: Array,
    default: () => []
  }
})

const selectedRoundId = ref(props.pageant.current_round_id?.toString() || '')
const notificationSystem = ref(null)
const actionLoading = ref(false)

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name + (round.is_locked ? ' 🔒' : '')
  }))
})

const activeRoundsCount = computed(() => {
  return props.rounds.filter(round => round.is_active).length
})

const lockedRoundsCount = computed(() => {
  return props.rounds.filter(round => round.is_locked).length
})

const setCurrentRound = (option) => {
  // Handle both direct roundId and option object from CustomSelect
  const roundId = typeof option === 'object' ? option.value : option
  if (!roundId) return
  
  actionLoading.value = true
  router.post(route('tabulator.set-current-round', props.pageant.id), {
    round_id: parseInt(roundId)
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via flash message and real-time event
    },
    onError: (errors) => {
      console.error('Error setting current round:', errors)
    },
    onFinish: () => {
      actionLoading.value = false
    }
  })
}

const setCurrentRoundDirect = (roundId) => {
  selectedRoundId.value = roundId.toString()
  setCurrentRound(roundId)
}

const lockRound = (roundId) => {
  actionLoading.value = true
  router.post(route('tabulator.rounds.lock', [props.pageant.id, roundId]), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via flash message and real-time event
    },
    onError: (errors) => {
      console.error('Error locking round:', errors)
    },
    onFinish: () => {
      actionLoading.value = false
    }
  })
}

const unlockRound = (roundId) => {
  actionLoading.value = true
  router.post(route('tabulator.rounds.unlock', [props.pageant.id, roundId]), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via flash message and real-time event
    },
    onError: (errors) => {
      console.error('Error unlocking round:', errors)
    },
    onFinish: () => {
      actionLoading.value = false
    }
  })
}

// Real-time event listeners
onMounted(() => {
  if (props.pageant) {
    console.log('Subscribing to pageant channel:', `pageant.${props.pageant.id}`)
    // Listen for round updates to show confirmation notifications
    window.Echo.private(`pageant.${props.pageant.id}`)
      .listen('RoundUpdated', (e) => {
        console.log('RoundUpdated event received:', e)
        handleRoundUpdate(e)
      })
  }
})

onUnmounted(() => {
  if (props.pageant) {
    window.Echo.leave(`pageant.${props.pageant.id}`)
  }
})

const handleRoundUpdate = (event) => {
  const { action, round_name, message } = event

  // Show confirmation notifications for tabulator actions
  if (notificationSystem.value) {
    switch (action) {
      case 'set_current':
        notificationSystem.value.success(`Current round set to: ${round_name}`, {
          title: 'Round Changed',
          timeout: 4000
        })
        // Refresh the page data to update UI
        setTimeout(() => {
          router.reload({ only: ['pageant', 'rounds'] })
        }, 1000)
        break
      
      case 'locked':
        notificationSystem.value.warning(`Round "${round_name}" has been locked`, {
          title: 'Round Locked',
          timeout: 4000
        })
        // Refresh the page data to update lock status
        setTimeout(() => {
          router.reload({ only: ['rounds'] })
        }, 1000)
        break
      
      case 'unlocked':
        notificationSystem.value.success(`Round "${round_name}" has been unlocked`, {
          title: 'Round Unlocked',
          timeout: 4000
        })
        // Refresh the page data to update lock status
        setTimeout(() => {
          router.reload({ only: ['rounds'] })
        }, 1000)
        break
    }
  }
}
</script>
