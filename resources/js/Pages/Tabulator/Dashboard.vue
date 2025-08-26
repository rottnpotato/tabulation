<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          {{ pageant ? `${pageant.name} - Tabulator Dashboard` : 'Tabulator Dashboard' }}
        </h1>
        <p class="text-gray-600 mt-2">
          {{ pageant ? 'Monitor scoring progress and manage results' : 'Select a pageant to manage' }}
        </p>
      </div>

      <!-- Pageant Selection (if no specific pageant) -->
      <div v-if="!pageant && pageants && pageants.length > 0" class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Assigned Pageants</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="pageantItem in pageants" 
            :key="pageantItem.id"
            class="bg-white rounded-lg shadow border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer"
            @click="$inertia.visit(route('tabulator.dashboard', pageantItem.id))"
          >
            <h3 class="text-lg font-semibold text-gray-900">{{ pageantItem.name }}</h3>
            <p class="text-sm text-gray-500 mb-4 capitalize">{{ pageantItem.status }}</p>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500">Contestants</p>
                <p class="font-semibold">{{ pageantItem.contestants_count }}</p>
              </div>
              <div>
                <p class="text-gray-500">Judges</p>
                <p class="font-semibold">{{ pageantItem.judges_count }}</p>
              </div>
              <div>
                <p class="text-gray-500">Rounds</p>
                <p class="font-semibold">{{ pageantItem.rounds_count }}</p>
              </div>
              <div>
                <p class="text-gray-500">Criteria</p>
                <p class="font-semibold">{{ pageantItem.criteria_count }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Pageant Assignments -->
      <div v-if="!pageant && (!pageants || pageants.length === 0)" class="text-center py-16">
        <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 mb-6">
          <Calculator class="h-12 w-12 text-blue-500" />
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No Pageant Assignments</h3>
        <p class="text-gray-600 mb-6 max-w-lg mx-auto">
          You haven't been assigned to any pageants yet. Organizers will assign you to pageants where you'll receive real-time score updates from judges and consolidate results for final reporting.
        </p>
        <div class="bg-blue-50 rounded-lg p-6 max-w-2xl mx-auto">
          <h4 class="font-semibold text-blue-900 mb-2">Your Role as Tabulator:</h4>
          <ul class="text-blue-800 text-sm space-y-1 text-left">
            <li>• Receive pageant assignments from organizers with criteria, scoring methods, and contestant details</li>
            <li>• Monitor real-time score submissions from judges during competitions</li>
            <li>• Consolidate and verify all scoring data for accuracy</li>
            <li>• Generate and print final results reports for pageant conclusion</li>
          </ul>
        </div>
      </div>

      <!-- Summary Cards (when pageant is selected) -->
      <div v-if="pageant && summary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <TabulatorCard 
          title="Contestants"
          :value="summary.contestants"
          description="Total contestants"
          :icon="Users"
          color="blue"
        />

        <TabulatorCard 
          title="Judges"
          :value="summary.judges"
          description="Active judges"
          :icon="Award"
          color="green"
        />

        <TabulatorCard 
          title="Rounds"
          :value="summary.rounds"
          description="Competition rounds"
          :icon="Clock"
          color="purple"
        />

        <TabulatorCard 
          title="Criteria"
          :value="summary.criteria"
          description="Scoring criteria"
          :icon="Target"
          color="orange"
        />
      </div>

      <!-- Navigation Cards (when pageant is selected) -->
      <div v-if="pageant" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Link :href="route('tabulator.judges', pageant.id)" class="block">
          <TabulatorCard 
            title="Manage Judges"
            subtitle="View and manage judge information"
            :icon="Users"
            color="blue"
          >
            <template #footer>
              <div class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center">
                View Judges
                <ChevronRight class="h-4 w-4 ml-1" />
              </div>
            </template>
          </TabulatorCard>
        </Link>

        <Link :href="route('tabulator.scores', pageant.id)" class="block">
          <TabulatorCard 
            title="View Scores"
            subtitle="Monitor scoring progress by round"
            :icon="FileText"
            color="green"
          >
            <template #footer>
              <div class="text-green-600 hover:text-green-800 font-medium text-sm flex items-center">
                View Scores
                <ChevronRight class="h-4 w-4 ml-1" />
              </div>
            </template>
          </TabulatorCard>
        </Link>

        <Link :href="route('tabulator.results', pageant.id)" class="block">
          <TabulatorCard 
            title="Results"
            subtitle="View final rankings and results"
            :icon="Trophy"
            color="purple"
          >
            <template #footer>
              <div class="text-purple-600 hover:text-purple-800 font-medium text-sm flex items-center">
                View Results
                <ChevronRight class="h-4 w-4 ml-1" />
              </div>
            </template>
          </TabulatorCard>
        </Link>

        <Link :href="route('tabulator.print', pageant.id)" class="block">
          <TabulatorCard 
            title="Print Reports"
            subtitle="Generate printable reports"
            :icon="Printer"
            color="orange"
          >
            <template #footer>
              <div class="text-orange-600 hover:text-orange-800 font-medium text-sm flex items-center">
                Print Reports
                <ChevronRight class="h-4 w-4 ml-1" />
              </div>
            </template>
          </TabulatorCard>
        </Link>
      </div>

      <!-- Recent Activity -->
      <div v-if="pageant && recentActivity && recentActivity.length > 0" class="mb-8">
        <TabulatorCard title="Recent Activity" subtitle="Latest scoring and system activities">
          <template #content>
            <div class="space-y-3">
              <div 
                v-for="(activity, index) in recentActivity.slice(0, 5)" 
                :key="index"
                class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    {{ activity.description }}
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ activity.timestamp }}
                  </p>
                </div>
              </div>
            </div>
          </template>
        </TabulatorCard>
      </div>

      <!-- Empty State for No Activity -->
      <div v-if="pageant && (!recentActivity || recentActivity.length === 0)" class="text-center py-12">
        <div class="text-gray-500">
          <Activity class="mx-auto h-12 w-12 text-gray-400 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">All Set!</h3>
          <p class="text-gray-500">
            Everything looks good. Use the navigation cards above to manage different aspects of the pageant.
          </p>
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