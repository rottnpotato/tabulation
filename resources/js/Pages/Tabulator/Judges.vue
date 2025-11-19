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
                {{ pageant ? pageant.name : 'Judges Management' }}
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                <Users class="w-5 h-5 text-teal-500" />
                Manage Panel & Access
              </p>
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
                  class="bg-white text-teal-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-teal-50 flex items-center shadow-sm transition-all"
                >
                  <Plus class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-teal-600" />
                  <span>Add Existing Judge</span>
                </button>
                <button 
                  @click="refreshData"
                  class="bg-white text-teal-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-teal-50 flex items-center shadow-sm transition-all"
                >
                  <RefreshCw class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-teal-600" />
                  <span>Refresh</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assigned -->
      <div v-if="!pageant" class="text-center py-20 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 max-w-2xl mx-auto">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-slate-50 mb-6">
            <Users class="h-12 w-12 text-slate-400" />
          </div>
          <h3 class="text-2xl font-bold text-slate-900 mb-4">No Pageant Selected</h3>
          <p class="text-slate-500 mb-8 text-lg">
            Please select a pageant from the dashboard to manage judges.
          </p>
          <Link 
            :href="route('tabulator.dashboard')"
            class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <LayoutDashboard class="w-5 h-5 mr-2" />
            Return to Dashboard
          </Link>
        </div>
      </div>

      <div v-else class="space-y-8 animate-fade-in">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Judges</p>
                <h4 class="text-3xl font-bold text-slate-900">{{ judges.length }}</h4>
                <p class="text-xs text-teal-600 font-medium mt-2">Assigned Panel</p>
              </div>
              <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
                <Users class="w-6 h-6" />
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Required</p>
                <h4 class="text-3xl font-bold text-slate-900">{{ pageant.required_judges || '∞' }}</h4>
                <p class="text-xs text-teal-600 font-medium mt-2">Target Count</p>
              </div>
              <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
                <Target class="w-6 h-6" />
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Active</p>
                <h4 class="text-3xl font-bold text-slate-900">{{ activeJudgesCount }}</h4>
                <p class="text-xs text-teal-600 font-medium mt-2">Currently Online</p>
              </div>
              <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
                <CheckCircle class="w-6 h-6" />
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Avg. Scores</p>
                <h4 class="text-3xl font-bold text-slate-900">{{ averageSubmissions }}</h4>
                <p class="text-xs text-teal-600 font-medium mt-2">Per Judge</p>
              </div>
              <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
                <FileText class="w-6 h-6" />
              </div>
            </div>
          </div>
        </div>

        <!-- Judges List -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-900">Judge List</h3>
            <span class="text-sm text-slate-500">{{ judges.length }} members</span>
          </div>

          <div v-if="judges.length > 0" class="divide-y divide-slate-100">
            <div 
              v-for="judge in judges" 
              :key="judge.id"
              class="px-6 py-4 hover:bg-slate-50 transition-colors duration-200 group"
            >
              <div class="flex items-center justify-between">
                <!-- Judge Info -->
                <div class="flex items-center gap-4">
                  <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center text-slate-500 font-bold text-lg shadow-inner">
                      {{ judge.name.charAt(0) }}
                    </div>
                  </div>
                  <div>
                    <h4 class="font-bold text-slate-900 group-hover:text-teal-600 transition-colors">{{ judge.name }}</h4>
                    <p class="text-sm text-slate-500">{{ judge.email }}</p>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-xs font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                        {{ judge.title || 'Judge' }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Judge Status and Stats -->
                <div class="flex items-center gap-8">
                  <!-- Scores Submitted -->
                  <div class="text-center hidden sm:block">
                    <p class="text-lg font-bold text-slate-900">{{ judge.scoresSubmitted }}</p>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Scores</p>
                  </div>

                  <!-- Status Badge -->
                  <div class="flex items-center">
                    <span 
                      class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border"
                      :class="judge.isActive 
                        ? 'bg-teal-50 text-teal-700 border-teal-200' 
                        : 'bg-slate-100 text-slate-600 border-slate-200'"
                    >
                      <span 
                        class="w-1.5 h-1.5 rounded-full mr-1.5" 
                        :class="judge.isActive ? 'bg-teal-500' : 'bg-slate-400'"
                      ></span>
                      {{ judge.isActive ? 'Active' : 'Inactive' }}
                    </span>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center gap-1">
                    <button
                      @click="resetPassword(judge.id)"
                      class="p-2 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-all"
                      title="Reset Password"
                    >
                      <Key class="w-4 h-4" />
                    </button>
                    <button
                      @click="toggleStatus(judge.id)"
                      class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-all"
                      :title="judge.isActive ? 'Deactivate' : 'Activate'"
                    >
                      <Power class="w-4 h-4" />
                    </button>
                    <button
                      @click="removeJudge(judge.id)"
                      class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
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
          <div v-else class="text-center py-16">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Users class="h-8 w-8 text-slate-400" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">No Judges Assigned</h3>
            <p class="text-slate-500 max-w-sm mx-auto">
              Start by creating a new judge account or adding an existing judge to this pageant.
            </p>
          </div>
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
              class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500"
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
              class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500"
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
              class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-teal-500 to-teal-700 rounded-lg hover:from-teal-600 hover:to-teal-800 shadow-sm transition-all disabled:opacity-50"
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

          <div class="bg-teal-50 border border-teal-200 rounded-lg p-3">
            <p class="text-xs text-teal-800">
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