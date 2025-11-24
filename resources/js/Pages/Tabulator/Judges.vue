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
              <button 
                v-if="availableJudges && availableJudges.length > 0"
                @click="showAddJudgeModal = true"
                class="bg-teal-600 text-white rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-teal-700 flex items-center shadow-sm transition-all"
              >
                <UserPlus class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
                <span>Add Judge</span>
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
              No judges are currently assigned to this pageant.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Judge Modal -->
    <Teleport to="body">
      <div 
        v-if="showAddJudgeModal" 
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showAddJudgeModal = false"
      >
        <div class="flex min-h-screen items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="showAddJudgeModal = false"></div>
          
          <!-- Modal -->
          <div class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
              <h3 class="text-xl font-bold text-slate-900">Add Judge to Pageant</h3>
              <button 
                @click="showAddJudgeModal = false"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
              <div v-if="availableJudges && availableJudges.length > 0" class="space-y-3">
                <div 
                  v-for="judge in availableJudges" 
                  :key="judge.id"
                  class="flex items-center justify-between p-4 border border-slate-200 rounded-xl hover:border-teal-300 hover:bg-teal-50/50 transition-all group cursor-pointer"
                  @click="selectJudge(judge.id)"
                >
                  <div class="flex items-center gap-4">
                    <div class="h-12 w-12 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center text-slate-500 font-bold text-lg shadow-inner group-hover:from-teal-100 group-hover:to-teal-200 transition-all">
                      {{ judge.name.charAt(0) }}
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 group-hover:text-teal-600 transition-colors">{{ judge.name }}</h4>
                      <p class="text-sm text-slate-500">{{ judge.email }}</p>
                      <p class="text-xs text-slate-400 mt-0.5">@{{ judge.username }}</p>
                    </div>
                  </div>
                  <button
                    @click.stop="assignJudge(judge.id)"
                    :disabled="assignForm.processing"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 disabled:bg-slate-300 text-white rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow-md disabled:cursor-not-allowed"
                  >
                    <span v-if="assignForm.processing && assignForm.judge_id === judge.id">Adding...</span>
                    <span v-else>Add</span>
                  </button>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                  <Users class="h-8 w-8 text-slate-400" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">No Available Judges</h3>
                <p class="text-slate-500">All judges have been assigned to this pageant.</p>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
              <button 
                @click="showAddJudgeModal = false"
                class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg text-sm font-medium transition-all"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

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
  Key, 
  Power,
  LayoutDashboard,
  Target,
  Trash2,
  UserPlus,
  X
} from 'lucide-vue-next'
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

const showAddJudgeModal = ref(false)
const assignForm = useForm({
  judge_id: null as number | null,
  role: 'Judge'
})

const activeJudgesCount = computed(() => {
  return props.judges.filter(judge => judge.isActive).length
})

const averageSubmissions = computed(() => {
  if (props.judges.length === 0) return 0
  const total = props.judges.reduce((sum, judge) => sum + judge.scoresSubmitted, 0)
  return Math.round(total / props.judges.length)
})



const refreshData = () => {
  router.reload()
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

const selectJudge = (judgeId: number) => {
  assignForm.judge_id = judgeId
}

const assignJudge = (judgeId: number) => {
  assignForm.judge_id = judgeId
  assignForm.post(route('tabulator.judges.assign', props.pageant?.id), {
    preserveScroll: true,
    onSuccess: () => {
      showAddJudgeModal.value = false
      assignForm.reset()
    },
    onError: () => {
      // Error handling is automatic via Inertia
    }
  })
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