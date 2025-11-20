<template>
  <div class="min-h-screen bg-slate-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-teal-100">
        <!-- Abstract Background Pattern -->
        <div class="absolute inset-0">
          <div class="absolute inset-0 bg-gradient-to-br from-teal-50 via-teal-50/50 to-white opacity-90"></div>
          <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
          <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div class="relative z-10 p-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
              <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                Round Management
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                <Circle class="w-5 h-5 text-teal-500" />
                {{ pageant.name }}
              </p>
            </div>
            
            <div v-if="pageant.current_round" class="flex items-center bg-white/60 backdrop-blur-md rounded-2xl p-4 border border-teal-100 shadow-sm">
              <div class="text-teal-600 mr-3">
                <div class="p-2 bg-teal-50 rounded-lg">
                  <Activity class="w-5 h-5" />
                </div>
              </div>
              <div>
                <div class="text-xs font-bold text-teal-500 uppercase tracking-wider mb-0.5">Current Active Round</div>
                <div class="text-lg font-bold text-slate-900 leading-none">{{ pageant.current_round.name }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 animate-fade-in relative z-20">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative z-30 group hover:shadow-md transition-all">
          <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
            <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
          </div>
          <div class="relative">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Set Current Round</h3>
            <p class="text-sm text-slate-500 mb-4">Direct judges to score a specific round</p>
            <div class="max-w-xs">
              <CustomSelect
                v-model="selectedRoundId"
                :options="roundOptions"
                placeholder="Select Round"
                :disabled="!isChannelReady || actionLoading"
                @change="setCurrentRound"
              />
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden group hover:shadow-md transition-all">
          <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
          <div class="relative z-10 flex justify-between items-center h-full">
            <div>
              <h3 class="text-lg font-bold text-slate-900 mb-1">Round Status</h3>
              <p class="text-sm text-slate-500">Monitor round completion status</p>
            </div>
            <button class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors shadow-lg shadow-teal-200">
              View Progress
            </button>
          </div>
        </div>
      </div>

      <!-- Rounds Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8 animate-fade-in">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h2 class="text-lg font-bold text-slate-900">All Rounds</h2>
            <p class="text-sm text-slate-500">Manage individual round settings and lock status</p>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Round Details</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Weight</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Lock Status</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="round in rounds" 
                :key="round.id"
                class="hover:bg-slate-50/80 transition-colors"
                :class="{ 'bg-teal-50/30': pageant.current_round_id === round.id }"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col">
                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                      {{ round.name }}
                      <span v-if="round.identifier" class="font-mono text-[10px] px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded border border-slate-200">{{ round.identifier }}</span>
                      <span v-if="pageant.current_round_id === round.id" class="inline-flex items-center px-2 py-0.5 bg-teal-100 text-teal-700 text-[10px] font-bold uppercase tracking-wide rounded-full border border-teal-200">
                        Current
                      </span>
                    </div>
                    <div v-if="round.description" class="text-xs text-slate-500 mt-0.5">{{ round.description }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 bg-teal-50 text-teal-700 text-xs font-medium rounded-lg border border-teal-100">
                    {{ round.type || 'Standard' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-slate-700">{{ round.weight }}%</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border"
                    :class="round.is_active ? 'bg-teal-50 text-teal-700 border-teal-200' : 'bg-slate-100 text-slate-600 border-slate-200'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="round.is_active ? 'bg-teal-500' : 'bg-slate-400'"></span>
                    {{ round.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <span 
                      class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border w-fit"
                      :class="round.is_locked ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-teal-50 text-teal-700 border-teal-200'"
                    >
                      <component :is="round.is_locked ? Lock : CheckCircle" class="w-3 h-3 mr-1.5" />
                      {{ round.is_locked ? 'Locked' : 'Open' }}
                    </span>
                    <div v-if="round.locked_by" class="text-[10px] text-slate-400 pl-1">
                      by {{ round.locked_by.name }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <button
                      v-if="pageant.current_round_id !== round.id"
                      @click="setCurrentRoundDirect(round.id)"
                      :disabled="actionLoading || !isChannelReady"
                      class="px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ actionLoading ? 'Setting...' : 'Set Current' }}
                    </button>
                    
                    <button
                      v-if="!round.is_locked"
                      @click="lockRound(round.id)"
                      :disabled="actionLoading || !isChannelReady"
                      class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ actionLoading ? 'Locking...' : 'Lock' }}
                    </button>
                    <button
                      v-else
                      @click="unlockRound(round.id)"
                      :disabled="actionLoading || !isChannelReady"
                      class="px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ actionLoading ? 'Unlocking...' : 'Unlock' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Round Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
          <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
            <Circle class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Total Rounds</p>
            <p class="text-2xl font-bold text-slate-900">{{ rounds.length }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
          <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
            <CheckCircle class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Active Rounds</p>
            <p class="text-2xl font-bold text-slate-900">{{ activeRoundsCount }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
          <div class="p-3 bg-slate-100 text-slate-600 rounded-xl">
            <Lock class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Locked Rounds</p>
            <p class="text-2xl font-bold text-slate-900">{{ lockedRoundsCount }}</p>
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
const isChannelReady = ref(false)
let pageantChannel = null

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
    pageantChannel = window.Echo.private(`pageant.${props.pageant.id}`)
      .subscribed(() => {
        isChannelReady.value = true
      })
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

<style scoped>
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
