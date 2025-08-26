<template>
  <div class="space-y-4 sm:space-y-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header Section with Better Design -->
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-md overflow-hidden">
        <div class="p-4 sm:p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="text-white">
              <h1 class="text-2xl sm:text-3xl font-bold">
                {{ pageant ? pageant.name : 'Tabulator Dashboard' }}
              </h1>
              <p class="mt-1 text-sm sm:text-base opacity-90">
                {{ pageant ? 'Monitor scoring progress and manage competition results' : 'Welcome to your tabulation center' }}
              </p>
            </div>
            <div v-if="pageant" class="flex flex-wrap gap-2 sm:space-x-3">
              <div class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white flex items-center shadow-sm">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-1 sm:mr-2 animate-pulse"></div>
                <span>Active Competition</span>
              </div>
              <div class="bg-white text-blue-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium shadow-sm">
                {{ pageant.scoring_system || 'Standard' }} Scoring
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pageant Selection (if no specific pageant) -->
      <div v-if="!pageant && pageants && pageants.length > 0" class="mb-8">
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Your Assigned Pageants</h2>
          <p class="text-gray-600">Select a pageant to manage and monitor scoring progress</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="pageantItem in pageants" 
            :key="pageantItem.id"
            class="group bg-white rounded-xl shadow-md border border-gray-100 p-6 hover:shadow-lg hover:border-blue-200 transition-all duration-300 cursor-pointer transform hover:-translate-y-1"
            @click="$inertia.visit(route('tabulator.dashboard', pageantItem.id))"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                  {{ pageantItem.name }}
                </h3>
                <div class="flex items-center mt-1">
                  <div class="w-2 h-2 rounded-full mr-2" 
                       :class="pageantItem.status === 'active' ? 'bg-green-400' : 'bg-yellow-400'"></div>
                  <p class="text-sm text-gray-500 capitalize">{{ pageantItem.status }}</p>
                </div>
              </div>
              <ChevronRight class="h-5 w-5 text-gray-400 group-hover:text-blue-600 transition-colors" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-blue-50 rounded-lg p-3">
                <div class="flex items-center">
                  <Users class="h-4 w-4 text-blue-600 mr-2" />
                  <div>
                    <p class="text-xs text-blue-600 font-medium">Contestants</p>
                    <p class="text-lg font-bold text-gray-900">{{ pageantItem.contestants_count }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-green-50 rounded-lg p-3">
                <div class="flex items-center">
                  <Award class="h-4 w-4 text-green-600 mr-2" />
                  <div>
                    <p class="text-xs text-green-600 font-medium">Judges</p>
                    <p class="text-lg font-bold text-gray-900">{{ pageantItem.judges_count }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-purple-50 rounded-lg p-3">
                <div class="flex items-center">
                  <Clock class="h-4 w-4 text-purple-600 mr-2" />
                  <div>
                    <p class="text-xs text-purple-600 font-medium">Rounds</p>
                    <p class="text-lg font-bold text-gray-900">{{ pageantItem.rounds_count }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-orange-50 rounded-lg p-3">
                <div class="flex items-center">
                  <Target class="h-4 w-4 text-orange-600 mr-2" />
                  <div>
                    <p class="text-xs text-orange-600 font-medium">Criteria</p>
                    <p class="text-lg font-bold text-gray-900">{{ pageantItem.criteria_count }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assignments -->
      <div v-if="!pageant && (!pageants || pageants.length === 0)" class="text-center py-16">
        <div class="mx-auto w-32 h-32 flex items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600 mb-8 shadow-xl">
          <Calculator class="h-16 w-16 text-white" />
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-3">No Pageant Assignments</h3>
        <p class="text-gray-600 mb-8 max-w-lg mx-auto text-lg">
          You haven't been assigned to any pageants yet. Organizers will assign you to pageants where you'll manage scoring and results.
        </p>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8 max-w-2xl mx-auto border border-blue-200 shadow-lg">
          <h4 class="font-bold text-blue-900 mb-4 text-lg">Your Role as Tabulator:</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white text-xs font-bold">1</span>
              </div>
              <p class="text-blue-800 text-sm">Receive pageant assignments with complete competition details</p>
            </div>
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white text-xs font-bold">2</span>
              </div>
              <p class="text-blue-800 text-sm">Monitor real-time score submissions from judges</p>
            </div>
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white text-xs font-bold">3</span>
              </div>
              <p class="text-blue-800 text-sm">Consolidate and verify scoring data for accuracy</p>
            </div>
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white text-xs font-bold">4</span>
              </div>
              <p class="text-blue-800 text-sm">Generate professional results reports and rankings</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Cards (when pageant is selected) -->
      <div v-if="pageant && summary" class="mb-8">
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Competition Overview</h2>
          <p class="text-gray-600">Real-time statistics and competition metrics</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-md p-6 text-white border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-100 text-sm font-medium">Total Contestants</p>
                <p class="text-3xl font-bold">{{ summary.contestants }}</p>
              </div>
              <div class="bg-white/20 rounded-lg p-3">
                <Users class="h-6 w-6" />
              </div>
            </div>
            <div class="mt-4 flex items-center text-blue-100 text-sm">
              <div class="w-2 h-2 bg-blue-200 rounded-full mr-2"></div>
              Active participants
            </div>
          </div>

          <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-md p-6 text-white border border-blue-200 opacity-90">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-100 text-sm font-medium">Active Judges</p>
                <p class="text-3xl font-bold">{{ summary.judges }}</p>
              </div>
              <div class="bg-white/20 rounded-lg p-3">
                <Award class="h-6 w-6" />
              </div>
            </div>
            <div class="mt-4 flex items-center text-blue-100 text-sm">
              <div class="w-2 h-2 bg-blue-200 rounded-full mr-2"></div>
              Scoring officials
            </div>
          </div>

          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md p-6 border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-600 text-sm font-medium">Competition Rounds</p>
                <p class="text-3xl font-bold text-gray-900">{{ summary.rounds }}</p>
              </div>
              <div class="bg-blue-100 rounded-lg p-3">
                <Clock class="h-6 w-6 text-blue-600" />
              </div>
            </div>
            <div class="mt-4 flex items-center text-blue-700 text-sm">
              <div class="w-2 h-2 bg-blue-600 rounded-full mr-2"></div>
              Scoring segments
            </div>
          </div>

          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md p-6 border border-blue-200 opacity-85">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-600 text-sm font-medium">Scoring Criteria</p>
                <p class="text-3xl font-bold text-gray-900">{{ summary.criteria }}</p>
              </div>
              <div class="bg-blue-100 rounded-lg p-3">
                <Target class="h-6 w-6 text-blue-600" />
              </div>
            </div>
            <div class="mt-4 flex items-center text-blue-700 text-sm">
              <div class="w-2 h-2 bg-blue-600 rounded-full mr-2"></div>
              Evaluation metrics
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Cards (when pageant is selected) -->
      <div v-if="pageant" class="mb-8">
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Management Tools</h2>
          <p class="text-gray-600">Access key functionalities for competition management</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Link :href="route('tabulator.judges', pageant.id)" class="group block">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 hover:shadow-lg hover:border-blue-200 transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-100 rounded-lg p-3 group-hover:bg-blue-200 transition-colors">
                  <Users class="h-6 w-6 text-blue-600" />
                </div>
                <ChevronRight class="h-5 w-5 text-gray-400 group-hover:text-blue-600 transition-colors" />
              </div>
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors mb-2">
                Manage Judges
              </h3>
              <p class="text-gray-600 text-sm">
                View and manage judge information and assignments
              </p>
            </div>
          </Link>

          <Link :href="route('tabulator.scores', pageant.id)" class="group block">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 hover:shadow-lg hover:border-blue-200 transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-green-100 rounded-lg p-3 group-hover:bg-green-200 transition-colors">
                  <FileText class="h-6 w-6 text-green-600" />
                </div>
                <ChevronRight class="h-5 w-5 text-gray-400 group-hover:text-green-600 transition-colors" />
              </div>
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-green-600 transition-colors mb-2">
                View Scores
              </h3>
              <p class="text-gray-600 text-sm">
                Monitor real-time scoring progress by round
              </p>
            </div>
          </Link>

          <Link :href="route('tabulator.results', pageant.id)" class="group block">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 hover:shadow-lg hover:border-blue-200 transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-100 rounded-lg p-3 group-hover:bg-purple-200 transition-colors">
                  <Trophy class="h-6 w-6 text-purple-600" />
                </div>
                <ChevronRight class="h-5 w-5 text-gray-400 group-hover:text-purple-600 transition-colors" />
              </div>
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors mb-2">
                Final Results
              </h3>
              <p class="text-gray-600 text-sm">
                View rankings, final scores and competition results
              </p>
            </div>
          </Link>

          <Link :href="route('tabulator.print', pageant.id)" class="group block">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 hover:shadow-lg hover:border-blue-200 transition-all duration-300 transform hover:-translate-y-1">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-orange-100 rounded-lg p-3 group-hover:bg-orange-200 transition-colors">
                  <Printer class="h-6 w-6 text-orange-600" />
                </div>
                <ChevronRight class="h-5 w-5 text-gray-400 group-hover:text-orange-600 transition-colors" />
              </div>
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-orange-600 transition-colors mb-2">
                Print Reports
              </h3>
              <p class="text-gray-600 text-sm">
                Generate and print professional competition reports
              </p>
            </div>
          </Link>
        </div>
      </div>

      <!-- Recent Activity -->
      <div v-if="pageant && recentActivity && recentActivity.length > 0" class="mb-8">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
          <div class="mb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-2">Recent Activity</h3>
            <p class="text-gray-600">Latest scoring and system activities</p>
          </div>
          <div class="space-y-4">
            <div 
              v-for="(activity, index) in recentActivity.slice(0, 5)" 
              :key="index"
              class="flex items-start space-x-4 p-4 bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg border-l-4 border-blue-500"
            >
              <div class="flex-shrink-0 mt-1">
                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ activity.timestamp }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State for No Activity -->
      <div v-if="pageant && (!recentActivity || recentActivity.length === 0)" class="text-center py-16">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12">
          <div class="mx-auto w-20 h-20 flex items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-200 mb-6">
            <Activity class="h-10 w-10 text-blue-600" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Everything is Ready!</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Your competition is set up and ready to go. Use the management tools above to monitor progress and manage the pageant.
          </p>
          <div class="flex flex-wrap justify-center gap-3">
            <div class="flex items-center bg-green-100 rounded-full px-4 py-2 text-sm font-medium text-green-800">
              <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
              System Ready
            </div>
            <div class="flex items-center bg-blue-100 rounded-full px-4 py-2 text-sm font-medium text-blue-800">
              <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
              All Systems Go
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
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
  Calculator
} from 'lucide-vue-next'
import TabulatorCard from '../../Components/tabulator/TabulatorCard.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Pageant {
  id: number
  name: string
  status: string
  scoring_system: string
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

defineProps<Props>()
</script>