
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
                Tabulation Dashboard
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                <LayoutDashboard class="w-5 h-5 text-teal-500" />
                Overview & Control Center
              </p>
            </div>
            
            <div v-if="pageant" class="flex items-center bg-white/60 backdrop-blur-md rounded-2xl p-4 border border-teal-100 shadow-sm">
              <div class="mr-4 p-3 bg-teal-50 text-teal-600 rounded-xl">
                <Crown class="w-6 h-6" />
              </div>
              <div>
                <div class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-0.5">Active Pageant</div>
                <div class="text-lg font-bold text-slate-900 leading-none">{{ pageant.name }}</div>
                <div class="text-xs text-slate-500 mt-1">
                  <span v-if="pageant.start_date">
                    {{ pageant.start_date }}
                    <span v-if="pageant.start_time" class="text-teal-600"> @ {{ pageant.start_time }}</span>
                  </span>
                  <span v-else>No date set</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pageant Selection (if no specific pageant) -->
      <div v-if="!pageant && pageants && pageants.length > 0" class="mb-12 animate-fade-in">
        <div class="flex items-center gap-4 mb-8">
          <div class="p-3 rounded-2xl bg-white shadow-md text-teal-600 ring-1 ring-slate-200">
            <Crown class="h-6 w-6" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-slate-900">Assigned Pageants</h2>
            <p class="text-slate-500">Select a competition to manage</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="pageantItem in pageants"
            :key="pageantItem.id"
            class="group relative bg-white rounded-3xl p-1 shadow-sm hover:shadow-2xl transition-all duration-500 cursor-pointer"
            @click="$inertia.visit(route('tabulator.dashboard', pageantItem.id))"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-slate-200 via-teal-100 to-slate-200 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl -z-10"></div>
            
            <div class="h-full bg-white rounded-[22px] p-6 border border-slate-100 group-hover:border-teal-100 relative overflow-hidden">
              <!-- Background decoration -->
              <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-slate-50 rounded-full blur-2xl opacity-50 group-hover:bg-teal-50 transition-colors"></div>

              <div class="relative">
                <div class="flex items-start justify-between mb-6">
                  <div class="flex-1 min-w-0 pr-4">
                    <div class="flex items-center gap-2 mb-2">
                      <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 group-hover:bg-teal-50 group-hover:text-teal-600 transition-colors">
                        Pageant
                      </span>
                      <span 
                        class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider"
                        :class="pageantItem.status === 'active' ? 'text-emerald-600' : 'text-amber-600'"
                      >
                        <span class="w-1.5 h-1.5 rounded-full" :class="pageantItem.status === 'active' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                        {{ pageantItem.status }}
                      </span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 group-hover:text-teal-600 transition-colors truncate">
                      {{ pageantItem.name }}
                    </h3>
                    <p v-if="pageantItem.start_date" class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                      <Calendar class="w-3 h-3" />
                      {{ pageantItem.start_date }}
                    </p>
                  </div>
                  <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-teal-50 group-hover:text-teal-600 transition-colors">
                    <ChevronRight class="w-5 h-5 text-slate-400 group-hover:text-teal-600" />
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div class="p-3 rounded-xl bg-slate-50 group-hover:bg-white group-hover:shadow-sm transition-all border border-transparent group-hover:border-slate-100">
                    <div class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-1">Contestants</div>
                    <div class="text-lg font-bold text-slate-900 flex items-center gap-2">
                      <Users class="w-4 h-4 text-teal-500" />
                      {{ pageantItem.contestants_count }}
                    </div>
                  </div>
                  <div class="p-3 rounded-xl bg-slate-50 group-hover:bg-white group-hover:shadow-sm transition-all border border-transparent group-hover:border-slate-100">
                    <div class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-1">Judges</div>
                    <div class="text-lg font-bold text-slate-900 flex items-center gap-2">
                      <Award class="w-4 h-4 text-teal-500" />
                      {{ pageantItem.judges_count }}
                    </div>
                  </div>
                  <div class="p-3 rounded-xl bg-slate-50 group-hover:bg-white group-hover:shadow-sm transition-all border border-transparent group-hover:border-slate-100">
                    <div class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-1">Rounds</div>
                    <div class="text-lg font-bold text-slate-900 flex items-center gap-2">
                      <Clock class="w-4 h-4 text-teal-500" />
                      {{ pageantItem.rounds_count }}
                    </div>
                  </div>
                  <div class="p-3 rounded-xl bg-slate-50 group-hover:bg-white group-hover:shadow-sm transition-all border border-transparent group-hover:border-slate-100">
                    <div class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-1">Criteria</div>
                    <div class="text-lg font-bold text-slate-900 flex items-center gap-2">
                      <Target class="w-4 h-4 text-teal-500" />
                      {{ pageantItem.criteria_count }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assignments -->
      <div v-if="!pageant && (!pageants || pageants.length === 0)" class="text-center py-20 animate-fade-in">
        <div class="relative mx-auto w-40 h-40 mb-8">
          <div class="absolute inset-0 bg-teal-100 rounded-full animate-ping opacity-20"></div>
          <div class="relative w-full h-full bg-gradient-to-br from-teal-50 to-white rounded-full flex items-center justify-center shadow-xl border border-teal-100">
            <Calculator class="h-16 w-16 text-teal-500" />
          </div>
        </div>
        <h3 class="text-3xl font-bold text-slate-900 mb-4">No Assignments Yet</h3>
        <p class="text-slate-500 mb-10 max-w-lg mx-auto text-lg leading-relaxed">
          You haven't been assigned to any pageants. Once assigned, you'll see your dashboard light up with competition data.
        </p>
        
        <div class="max-w-3xl mx-auto bg-white rounded-3xl p-8 shadow-xl border border-slate-100">
          <h4 class="font-bold text-slate-900 mb-6 text-lg flex items-center justify-center gap-2">
            <Activity class="w-5 h-5 text-teal-500" />
            Tabulator Workflow
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center group">
              <div class="w-12 h-12 mx-auto bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-500 transition-colors duration-300">
                <span class="text-teal-600 font-bold group-hover:text-white transition-colors">1</span>
              </div>
              <p class="text-sm text-slate-600 font-medium">Receive Assignment</p>
            </div>
            <div class="hidden md:block absolute left-1/4 top-1/2 w-12 border-t-2 border-dashed border-slate-200"></div>
            <div class="text-center group">
              <div class="w-12 h-12 mx-auto bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-500 transition-colors duration-300">
                <span class="text-teal-600 font-bold group-hover:text-white transition-colors">2</span>
              </div>
              <p class="text-sm text-slate-600 font-medium">Monitor Scoring</p>
            </div>
            <div class="text-center group">
              <div class="w-12 h-12 mx-auto bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-500 transition-colors duration-300">
                <span class="text-teal-600 font-bold group-hover:text-white transition-colors">3</span>
              </div>
              <p class="text-sm text-slate-600 font-medium">Verify Data</p>
            </div>
            <div class="text-center group">
              <div class="w-12 h-12 mx-auto bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-500 transition-colors duration-300">
                <span class="text-teal-600 font-bold group-hover:text-white transition-colors">4</span>
              </div>
              <p class="text-sm text-slate-600 font-medium">Generate Results</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Pageant Dashboard -->
      <div v-if="pageant" class="space-y-10 animate-fade-in">
        
        <!-- Stats Overview -->
        <div v-if="summary">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-900">Overview</h2>
            <span class="text-sm text-slate-500">Last updated just now</span>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Contestants Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
              <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-teal-50 rounded-full blur-xl group-hover:bg-teal-100 transition-colors"></div>
              <div class="relative">
                <div class="flex items-center justify-between mb-4">
                  <div class="p-2.5 bg-teal-50 rounded-xl text-teal-600 group-hover:scale-110 transition-transform">
                    <Users class="w-6 h-6" />
                  </div>
                  <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-1 rounded-lg">Total</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1">{{ summary.contestants }}</div>
                <div class="text-sm text-slate-500 font-medium">Contestants Registered</div>
              </div>
            </div>

            <!-- Judges Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
              <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-teal-50 rounded-full blur-xl group-hover:bg-teal-100 transition-colors"></div>
              <div class="relative">
                <div class="flex items-center justify-between mb-4">
                  <div class="p-2.5 bg-teal-50 rounded-xl text-teal-600 group-hover:scale-110 transition-transform">
                    <Award class="w-6 h-6" />
                  </div>
                  <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-1 rounded-lg">Active</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1">{{ summary.judges }}</div>
                <div class="text-sm text-slate-500 font-medium">Judges Assigned</div>
              </div>
            </div>

            <!-- Rounds Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
              <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-teal-50 rounded-full blur-xl group-hover:bg-teal-100 transition-colors"></div>
              <div class="relative">
                <div class="flex items-center justify-between mb-4">
                  <div class="p-2.5 bg-teal-50 rounded-xl text-teal-600 group-hover:scale-110 transition-transform">
                    <Clock class="w-6 h-6" />
                  </div>
                  <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-1 rounded-lg">Segments</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1">{{ summary.rounds }}</div>
                <div class="text-sm text-slate-500 font-medium">Competition Rounds</div>
              </div>
            </div>

            <!-- Criteria Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
              <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-teal-50 rounded-full blur-xl group-hover:bg-teal-100 transition-colors"></div>
              <div class="relative">
                <div class="flex items-center justify-between mb-4">
                  <div class="p-2.5 bg-teal-50 rounded-xl text-teal-600 group-hover:scale-110 transition-transform">
                    <Target class="w-6 h-6" />
                  </div>
                  <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-1 rounded-lg">Metrics</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1">{{ summary.criteria }}</div>
                <div class="text-sm text-slate-500 font-medium">Scoring Criteria</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Management Tools -->
        <div>
          <h2 class="text-xl font-bold text-slate-900 mb-6">Management Tools</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Link :href="route('tabulator.judges', pageant.id)" class="group">
              <div class="h-full bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:border-teal-200 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-teal-50/50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                  <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-600 transition-colors duration-300">
                    <Users class="w-6 h-6 text-teal-600 group-hover:text-white transition-colors" />
                  </div>
                  <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-teal-700 transition-colors">Manage Judges</h3>
                  <p class="text-sm text-slate-500 leading-relaxed">Configure judge profiles, assign pins, and manage access.</p>
                </div>
              </div>
            </Link>

            <Link :href="route('tabulator.scores', pageant.id)" class="group">
              <div class="h-full bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:border-teal-200 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-teal-50/50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                  <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-600 transition-colors duration-300">
                    <FileText class="w-6 h-6 text-teal-600 group-hover:text-white transition-colors" />
                  </div>
                  <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-teal-700 transition-colors">View Scores</h3>
                  <p class="text-sm text-slate-500 leading-relaxed">Monitor incoming scores in real-time and audit submissions.</p>
                </div>
              </div>
            </Link>

            <Link :href="route('tabulator.results', pageant.id)" class="group">
              <div class="h-full bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:border-teal-200 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-teal-50/50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                  <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-600 transition-colors duration-300">
                    <Trophy class="w-6 h-6 text-teal-600 group-hover:text-white transition-colors" />
                  </div>
                  <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-teal-700 transition-colors">Final Results</h3>
                  <p class="text-sm text-slate-500 leading-relaxed">Calculate rankings, view winners, and finalize outcomes.</p>
                </div>
              </div>
            </Link>

            <Link :href="route('tabulator.print', pageant.id)" class="group">
              <div class="h-full bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:border-teal-200 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-transparent to-teal-50/50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                  <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-teal-600 transition-colors duration-300">
                    <Printer class="w-6 h-6 text-teal-600 group-hover:text-white transition-colors" />
                  </div>
                  <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-teal-700 transition-colors">Print Reports</h3>
                  <p class="text-sm text-slate-500 leading-relaxed">Generate PDF reports for archiving and distribution.</p>
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- Recent Activity -->
        <div v-if="recentActivity && recentActivity.length > 0">
          <h2 class="text-xl font-bold text-slate-900 mb-6">Recent Activity</h2>
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="divide-y divide-slate-100">
              <div 
                v-for="(activity, index) in recentActivity.slice(0, 5)" 
                :key="index"
                class="p-4 hover:bg-slate-50 transition-colors flex items-center gap-4"
              >
                <div class="w-2 h-2 rounded-full bg-teal-500 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-slate-900 truncate">{{ activity.description }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ activity.timestamp }}</p>
                </div>
                <ChevronRight class="w-4 h-4 text-slate-300" />
              </div>
            </div>
            <div class="bg-slate-50 p-3 text-center border-t border-slate-100">
              <button class="text-sm font-medium text-teal-600 hover:text-teal-700 transition-colors">View All Activity</button>
            </div>
          </div>
        </div>

        <!-- Empty State for Activity -->
        <div v-else class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <Activity class="w-8 h-8 text-slate-400" />
          </div>
          <h3 class="text-lg font-bold text-slate-900 mb-2">System Ready</h3>
          <p class="text-slate-500 max-w-sm mx-auto">
            Your competition environment is set up. Activity will appear here once the event begins.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { 
  Users, 
  Award, 
  Clock, 
  Target, 
  Trophy, 
  FileText, 
  Printer, 
  ChevronRight,
  Activity,
  Calculator,
  Crown,
  Calendar
} from 'lucide-vue-next'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Pageant {
  id: number
  name: string
  status: string
  scoring_system: string
  start_date: string | null
}

interface PageantListItem {
  id: number
  name: string
  status: string
  contestants_count: number
  judges_count: number
  rounds_count: number
  criteria_count: number
}

interface Summary {
  contestants: number
  judges: number
  rounds: number
  criteria: number
}

interface RecentActivity {
  description: string
  timestamp: string
}

interface Props {
  pageant?: Pageant
  pageants?: PageantListItem[]
  summary?: Summary
  recentActivity?: RecentActivity[]
}

const props = defineProps<Props>()

// WebSocket handling for real-time updates
let echoChannel: any = null

onMounted(() => {
  if (!props.pageant) {
    console.log('⚠️ No specific pageant selected, skipping WebSocket subscription')
    return
  }

  if (typeof window === 'undefined' || !window.Echo) {
    console.error('❌ Laravel Echo not available')
    return
  }

  const channelName = `pageant.${props.pageant.id}`
  console.log('🔌 Tabulator Dashboard subscribing to channel:', channelName)

  // Subscribe to the pageant channel for real-time updates
  echoChannel = window.Echo.private(channelName)
    .listen('ScoreUpdated', (e: any) => {
      console.log('🔔 ScoreUpdated event received on Dashboard:', e)
      // Refresh summary statistics and recent activity
      router.reload({ only: ['summary', 'recentActivity'] })
    })
    .listen('RoundUpdated', (e: any) => {
      console.log('🔔 RoundUpdated event received on Dashboard:', e)
      // Refresh pageant info if current round changed
      router.reload({ only: ['pageant', 'summary'] })
    })
  
  console.log('✅ Successfully subscribed to dashboard updates')
})

onUnmounted(() => {
  if (echoChannel && props.pageant) {
    const channelName = `pageant.${props.pageant.id}`
    console.log('🔌 Unsubscribing from channel:', channelName)
    window.Echo.leave(channelName)
    echoChannel = null
  }
})
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