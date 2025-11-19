<template>
  <div class="space-y-4 sm:space-y-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header Section -->
              <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-md overflow-hidden">
        <div class="p-4 sm:p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="text-white">
              <h1 class="text-2xl sm:text-3xl font-bold">
                {{ pageant ? `${pageant.name} - Judges Management` : 'Judges Management' }}
              </h1>
              <p class="mt-1 text-sm sm:text-base opacity-90">Monitor judge activity and scoring progress</p>
            </div>
            <div v-if="pageant" class="flex flex-wrap gap-2">
              <div class="flex gap-2">
                <button 
                  @click="showCreateJudgeModal = true"
                  class="bg-green-500 text-white rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-green-600 flex items-center shadow-sm transition-all"
                >
                  <UserPlus class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
                  <span>Create Judge Account</span>
                </button>
                <button 
                  v-if="canAddJudges"
                  @click="showAddJudgeModal = true"
                  class="bg-white text-blue-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-blue-50 flex items-center shadow-sm transition-all"
                >
                  <Plus class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-blue-600" />
                  <span>Add Existing Judge</span>
                </button>
                <button 
                  @click="refreshData"
                  class="bg-white text-blue-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-blue-50 flex items-center shadow-sm transition-all"
                >
                  <RefreshCw class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-blue-600" />
                  <span>Refresh</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-16">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-blue-200 mb-6">
            <Users class="h-12 w-12 text-blue-600" />
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
      </div>

      <!-- Summary Cards -->
      <div v-if="pageant" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <TabulatorCard 
          title="Total Judges"
          :value="judges.length"
          description="Assigned judges"
          :icon="Users"
          color="blue"
        />

        <TabulatorCard 
          title="Required Judges"
          :value="pageant.required_judges || 'No limit'"
          description="Target number"
          :icon="Target"
          color="purple"
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
      <div v-if="pageant" class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-6 pt-4 sm:pt-6 pb-3 sm:pb-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Judge List</h3>
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
                  <button
                    @click="removeJudge(judge.id)"
                    class="inline-flex items-center p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition duration-150 ease-in-out"
                    title="Remove Judge"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <Users class="mx-auto h-12 w-12 text-gray-400 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Judges Assigned</h3>
          <p class="text-gray-500">
            No judges have been assigned to this pageant yet.
          </p>
        </div>
      </div>
    </div>

    <!-- Add Judge Modal -->
    <Modal :show="showAddJudgeModal" @close="closeAddJudgeModal">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">Add Existing Judge</h3>
          <button @click="closeAddJudgeModal" class="text-gray-400 hover:text-gray-600">
            <X class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="submitAddJudge" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Judge</label>
            <select 
              v-model="addJudgeForm.judge_id" 
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              required
            >
              <option value="">Choose a judge...</option>
              <option 
                v-for="judge in availableJudges" 
                :key="judge.id" 
                :value="judge.id"
              >
                {{ judge.name }} ({{ judge.email }})
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role (Optional)</label>
            <input
              type="text"
              v-model="addJudgeForm.role"
              placeholder="e.g. Head Judge, Guest Judge"
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="closeAddJudgeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="addJudgeForm.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg hover:from-blue-600 hover:to-blue-800 shadow-sm transition-all disabled:opacity-50"
            >
              {{ addJudgeForm.processing ? 'Adding...' : 'Add Judge' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Create New Judge Modal -->
    <Modal :show="showCreateJudgeModal" @close="closeCreateJudgeModal">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">Create New Judge Account</h3>
          <button @click="closeCreateJudgeModal" class="text-gray-400 hover:text-gray-600">
            <X class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="submitCreateJudge" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
            <input
              type="text"
              v-model="createJudgeForm.name"
              placeholder="Enter full name"
              class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
              required
            />
            <p v-if="createJudgeForm.errors.name" class="mt-1 text-sm text-red-600">{{ createJudgeForm.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
            <input
              type="text"
              v-model="createJudgeForm.username"
              placeholder="Enter username"
              class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
              required
              minlength="3"
              maxlength="30"
            />
            <p v-if="createJudgeForm.errors.username" class="mt-1 text-sm text-red-600">{{ createJudgeForm.errors.username }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
            <input
              type="email"
              v-model="createJudgeForm.email"
              placeholder="Enter email address"
              class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
            />
            <p v-if="createJudgeForm.errors.email" class="mt-1 text-sm text-red-600">{{ createJudgeForm.errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
            <input
              type="password"
              v-model="createJudgeForm.password"
              placeholder="Enter password"
              class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
              required
              minlength="6"
            />
            <p class="mt-1 text-xs text-gray-500">Minimum 6 characters</p>
            <p v-if="createJudgeForm.errors.password" class="mt-1 text-sm text-red-600">{{ createJudgeForm.errors.password }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role Title (Optional)</label>
            <input
              type="text"
              v-model="createJudgeForm.role_title"
              placeholder="e.g. Head Judge, Guest Judge"
              class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
            />
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <p class="text-xs text-blue-800">
              <strong>Note:</strong> This account will be linked to this pageant only and will be disabled when the pageant is completed.
            </p>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="closeCreateJudgeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="createJudgeForm.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-700 rounded-lg hover:from-green-600 hover:to-green-800 shadow-sm transition-all disabled:opacity-50"
            >
              {{ createJudgeForm.processing ? 'Creating...' : 'Create Judge Account' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { 
  Users, 
  CheckCircle, 
  FileText, 
  RefreshCw, 
  User, 
  Key, 
  Power,
  LayoutDashboard,
  Plus,
  Target,
  Trash2,
  X,
  UserPlus
} from 'lucide-vue-next'
import TabulatorCard from '../../Components/tabulator/TabulatorCard.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'
import Modal from '../../Components/Modal.vue'

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
  required_judges?: number
  current_judges_count: number
}

interface AvailableJudge {
  id: number
  name: string
  email: string
  username: string
}

interface Props {
  pageant?: Pageant
  judges: Judge[]
  availableJudges?: AvailableJudge[]
}

const props = defineProps<Props>()

// Modal state
const showAddJudgeModal = ref(false)
const showCreateJudgeModal = ref(false)

// Form for adding judges
const addJudgeForm = useForm({
  judge_id: '',
  role: ''
})

// Form for creating new judge accounts
const createJudgeForm = useForm({
  name: '',
  username: '',
  email: '',
  password: '',
  role_title: ''
})

const activeJudgesCount = computed(() => {
  return props.judges.filter(judge => judge.isActive).length
})

const averageSubmissions = computed(() => {
  if (props.judges.length === 0) return 0
  const total = props.judges.reduce((sum, judge) => sum + judge.scoresSubmitted, 0)
  return Math.round(total / props.judges.length)
})

const canAddJudges = computed(() => {
  if (!props.pageant) return false
  const hasAvailableJudges = props.availableJudges && props.availableJudges.length > 0
  const belowLimit = !props.pageant.required_judges || props.judges.length < props.pageant.required_judges
  return hasAvailableJudges && belowLimit
})

const getStatusClass = (isActive: boolean) => {
  return isActive 
    ? 'bg-green-100 text-green-800' 
    : 'bg-gray-100 text-gray-800'
}

const refreshData = () => {
  router.reload()
}

const closeAddJudgeModal = () => {
  showAddJudgeModal.value = false
  addJudgeForm.reset()
}

const closeCreateJudgeModal = () => {
  showCreateJudgeModal.value = false
  createJudgeForm.reset()
}

const submitAddJudge = () => {
  addJudgeForm.post(route('tabulator.judges.assign', props.pageant?.id), {
    onSuccess: () => {
      closeAddJudgeModal()
    }
  })
}

const submitCreateJudge = () => {
  createJudgeForm.post(route('tabulator.judges.create', props.pageant?.id), {
    onSuccess: () => {
      closeCreateJudgeModal()
    }
  })
}

const removeJudge = (judgeId: number) => {
  if (confirm('Are you sure you want to remove this judge from the pageant?')) {
    router.delete(route('tabulator.judges.remove', [props.pageant?.id, judgeId]))
  }
}

const resetPassword = (judgeId: number) => {
  if (confirm('This will generate a new password for the judge. Continue?')) {
    router.post(route('tabulator.judges.reset-password', [props.pageant?.id, judgeId]))
  }
}

const toggleStatus = (judgeId: number) => {
  router.post(route('tabulator.judges.toggle-status', [props.pageant?.id, judgeId]))
}
</script>