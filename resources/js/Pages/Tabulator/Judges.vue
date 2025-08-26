<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          {{ pageant ? `${pageant.name} - Judges Management` : 'Judges Management' }}
        </h1>
        <p class="text-gray-600 mt-2">Monitor judge activity and scoring progress</p>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-16">
        <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 mb-6">
          <Users class="h-12 w-12 text-blue-500" />
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No Pageant Selected</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          You haven't been assigned to any pageants yet, or you need to select a pageant to manage judges.
        </p>
        <Link 
          :href="route('tabulator.dashboard')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
        >
          <LayoutDashboard class="w-4 h-4 mr-2" />
          Go to Dashboard
        </Link>
      </div>

      <!-- Summary Cards -->
      <div v-if="pageant" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <TabulatorCard 
          title="Total Judges"
          :value="judges.length"
          description="Assigned judges"
          :icon="Users"
          color="blue"
        />

        <TabulatorCard 
          title="Active Judges"
          :value="activeJudgesCount"
          description="Currently online"
          :icon="CheckCircle"
          color="green"
        />

        <TabulatorCard 
          title="Avg. Submissions"
          :value="averageSubmissions"
          description="Scores per judge"
          :icon="FileText"
          color="purple"
        />
      </div>

      <!-- Judges Table -->
      <div v-if="pageant" class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Judge List</h3>
            <button 
              @click="refreshData"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out"
            >
              <RefreshCw class="w-4 h-4 mr-2" />
              Refresh
            </button>
          </div>
        </div>

        <div v-if="judges.length > 0" class="divide-y divide-gray-200">
          <div 
            v-for="judge in judges" 
            :key="judge.id"
            class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200"
          >
            <div class="flex items-center justify-between">
              <!-- Judge Info -->
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                    <User class="h-6 w-6 text-gray-500" />
                  </div>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">{{ judge.name }}</h4>
                  <p class="text-sm text-gray-500">{{ judge.email }}</p>
                  <p class="text-xs text-gray-400">{{ judge.title }}</p>
                </div>
              </div>

              <!-- Judge Status and Stats -->
              <div class="flex items-center space-x-6">
                <!-- Scores Submitted -->
                <div class="text-center">
                  <p class="text-lg font-semibold text-gray-900">{{ judge.scoresSubmitted }}</p>
                  <p class="text-xs text-gray-500">Scores Submitted</p>
                </div>

                <!-- Status Badge -->
                <div class="flex items-center">
                  <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusClass(judge.isActive)"
                  >
                    <span 
                      class="w-1.5 h-1.5 rounded-full mr-1.5" 
                      :class="judge.isActive ? 'bg-green-400' : 'bg-gray-400'"
                    ></span>
                    {{ judge.isActive ? 'Active' : 'Inactive' }}
                  </span>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-2">
                  <button
                    @click="resetPassword(judge.id)"
                    class="inline-flex items-center p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition duration-150 ease-in-out"
                    title="Reset Password"
                  >
                    <Key class="w-4 h-4" />
                  </button>
                  <button
                    @click="toggleStatus(judge.id)"
                    class="inline-flex items-center p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded transition duration-150 ease-in-out"
                    :title="judge.isActive ? 'Deactivate' : 'Activate'"
                  >
                    <Power class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="text-gray-500">
            <Users class="mx-auto h-12 w-12 text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Judges Assigned</h3>
            <p class="text-gray-500">
              No judges have been assigned to this pageant yet.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { 
  Users, 
  CheckCircle, 
  FileText, 
  RefreshCw, 
  User, 
  Key, 
  Power,
  LayoutDashboard
} from 'lucide-vue-next'
import TabulatorCard from '../../Components/tabulator/TabulatorCard.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Judge {
  id: number
  name: string
  email: string
  title: string
  isActive: boolean
  scoresSubmitted: number
}

interface Pageant {
  id: number
  name: string
}

interface Props {
  pageant?: Pageant
  judges: Judge[]
}

const props = defineProps<Props>()

const activeJudgesCount = computed(() => {
  return props.judges.filter(judge => judge.isActive).length
})

const averageSubmissions = computed(() => {
  if (props.judges.length === 0) return 0
  const total = props.judges.reduce((sum, judge) => sum + judge.scoresSubmitted, 0)
  return Math.round(total / props.judges.length)
})

const getStatusClass = (isActive: boolean) => {
  return isActive 
    ? 'bg-green-100 text-green-800' 
    : 'bg-gray-100 text-gray-800'
}

const refreshData = () => {
  router.reload()
}

const resetPassword = (judgeId: number) => {
  // TODO: Implement password reset functionality
  console.log('Reset password for judge:', judgeId)
  // This would typically make an API call to reset the judge's password
}

const toggleStatus = (judgeId: number) => {
  // TODO: Implement judge status toggle functionality
  console.log('Toggle status for judge:', judgeId)
  // This would typically make an API call to activate/deactivate the judge
}
</script>