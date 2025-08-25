<template>
  <div class="space-y-6">
    <!-- Header Section with Pageant Info -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-md rounded-xl p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <div class="flex items-center">
            <h2 class="text-2xl font-bold text-white">Miss Universe 2025</h2>
            <span class="ml-3 px-3 py-1 text-xs font-bold bg-blue-500/30 text-blue-100 backdrop-blur-sm border border-blue-500/50 rounded-full flex items-center">
              <span class="h-2 w-2 rounded-full bg-green-400 mr-1.5 animate-pulse"></span>
              LIVE
            </span>
          </div>
          <p class="text-blue-100 mt-1">Tabulation Dashboard</p>
        </div>
        <div class="flex mt-4 md:mt-0 space-x-3">
          <div class="relative min-w-[180px]">
            <CustomSelect
              v-model="CurrentRound"
              :options="roundOptions"
              variant="blue"
              placeholder="Select Round"
            />
          </div>
          <button class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg flex items-center gap-2 border border-white/20 transition-colors">
            <CalendarClock class="h-4 w-4" />
            <span>Change Round</span>
          </button>
          <button 
            @click="RefreshDashboard" 
            class="bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors"
            :disabled="isLoading"
          >
            <RefreshCw :class="['h-4 w-4', {'animate-spin': isLoading}]" />
            <span>{{ isLoading ? 'Refreshing...' : 'Refresh Data' }}</span>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <template v-if="isLoading">
        <div v-for="i in 3" :key="i" class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <div class="h-6 w-32 bg-gray-200 rounded shimmer shimmer-blue"></div>
            <div class="p-2 bg-gray-200 rounded-full shimmer shimmer-blue h-9 w-9"></div>
          </div>
          <div class="h-8 w-24 bg-gray-200 rounded shimmer shimmer-blue"></div>
          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <div class="h-4 w-24 bg-gray-200 rounded shimmer shimmer-blue"></div>
              <div class="h-4 w-16 bg-gray-200 rounded shimmer shimmer-blue"></div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 shimmer shimmer-blue"></div>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Active Judges</h3>
            <div class="p-2 bg-blue-100 rounded-full">
              <Users class="h-5 w-5 text-blue-600" />
            </div>
          </div>
          <p class="text-3xl font-bold text-gray-900">5/5</p>
          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <div class="text-sm text-gray-600">Online Status</div>
              <div class="text-sm text-gray-600">100%</div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
            </div>
          </div>
        </div>
        
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Scores Submitted</h3>
            <div class="p-2 bg-indigo-100 rounded-full">
              <ClipboardCheck class="h-5 w-5 text-indigo-600" />
            </div>
          </div>
          <p class="text-3xl font-bold text-gray-900">85%</p>
          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <div class="text-sm text-gray-600">Submission Progress</div>
              <div class="text-sm text-gray-600">64/75 entries</div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-indigo-600 h-2 rounded-full" style="width: 85%"></div>
            </div>
          </div>
        </div>
        
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Current Round</h3>
            <div class="p-2 bg-purple-100 rounded-full">
              <Timer class="h-5 w-5 text-purple-600" />
            </div>
          </div>
          <p class="text-3xl font-bold text-gray-900">Evening Gown</p>
          <div class="flex flex-wrap gap-2 mt-4">
            <button class="px-3 py-1 rounded-lg text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
              Evening Gown
            </button>
            <button class="px-3 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 hover:bg-gray-200">
              Swimsuit
            </button>
            <button class="px-3 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 hover:bg-gray-200">
              Q&A
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Recent Activities -->
      <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <Activity class="h-5 w-5 text-blue-600 mr-2" />
            Recent Activities
          </h3>
          <button class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
            View All <ChevronRight class="h-4 w-4 ml-1" />
          </button>
        </div>
        
        <div class="space-y-4">
          <template v-if="isLoading">
            <div v-for="i in 3" :key="i" class="flex items-start">
              <div class="h-9 w-9 rounded-full bg-gray-200 shimmer shimmer-blue"></div>
              <div class="ml-3 border-b border-gray-100 pb-4 flex-grow">
                <div class="h-4 w-3/4 bg-gray-200 rounded shimmer shimmer-blue mb-2"></div>
                <div class="flex items-center mt-1">
                  <div class="h-3.5 w-3.5 rounded-full bg-gray-200 shimmer shimmer-blue mr-1"></div>
                  <div class="h-3 w-24 bg-gray-200 rounded shimmer shimmer-blue"></div>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="flex items-start">
              <div class="h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center">
                <UserCheck class="h-5 w-5 text-blue-600" />
              </div>
              <div class="ml-3 border-b border-gray-100 pb-4 flex-grow">
                <p class="text-sm font-medium text-gray-900">Judge 3 (Maria Rodriguez) submitted scores for Contestant #5</p>
                <div class="flex items-center mt-1">
                  <Clock class="h-3.5 w-3.5 text-gray-400 mr-1" />
                  <p class="text-xs text-gray-500">2 minutes ago</p>
                </div>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="h-9 w-9 rounded-full bg-green-100 flex items-center justify-center">
                <CheckCircle class="h-5 w-5 text-green-600" />
              </div>
              <div class="ml-3 border-b border-gray-100 pb-4 flex-grow">
                <p class="text-sm font-medium text-gray-900">Round 2 (Swimsuit) scoring completed</p>
                <div class="flex items-center mt-1">
                  <Clock class="h-3.5 w-3.5 text-gray-400 mr-1" />
                  <p class="text-xs text-gray-500">15 minutes ago</p>
                </div>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="h-9 w-9 rounded-full bg-amber-100 flex items-center justify-center">
                <ClipboardCheck class="h-5 w-5 text-amber-600" />
              </div>
              <div class="ml-3 border-b border-gray-100 pb-4 flex-grow">
                <p class="text-sm font-medium text-gray-900">Judge 1 updated scores for Contestant #2</p>
                <div class="flex items-center mt-1">
                  <Clock class="h-3.5 w-3.5 text-gray-400 mr-1" />
                  <p class="text-xs text-gray-500">28 minutes ago</p>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <Zap class="h-5 w-5 text-blue-600 mr-2" />
            Quick Actions
          </h3>
        </div>
        
        <div class="space-y-3">
          <template v-if="isLoading">
            <div v-for="i in 3" :key="i" class="flex items-center justify-between p-4 rounded-lg border border-gray-100">
              <div class="flex items-center">
                <div class="p-2 rounded-lg bg-gray-200 shimmer shimmer-blue mr-3 h-9 w-9"></div>
                <div>
                  <div class="h-5 w-32 bg-gray-200 rounded shimmer shimmer-blue mb-1"></div>
                  <div class="h-3 w-40 bg-gray-200 rounded shimmer shimmer-blue"></div>
                </div>
              </div>
              <div class="h-5 w-5 bg-gray-200 rounded shimmer shimmer-blue"></div>
            </div>
          </template>
          <template v-else>
            <Link
              :href="route('tabulator.scores')"
              class="flex items-center justify-between p-4 rounded-lg border border-gray-100 hover:bg-blue-50 hover:border-blue-200 transition-all"
            >
              <div class="flex items-center">
                <div class="p-2 rounded-lg bg-blue-100 mr-3">
                  <ClipboardList class="h-5 w-5 text-blue-600" />
                </div>
                <div>
                  <div class="font-medium text-gray-900">View Score Entries</div>
                  <div class="text-xs text-gray-500">Check all submitted scores</div>
                </div>
              </div>
              <ChevronRight class="h-5 w-5 text-gray-400" />
            </Link>
            
            <Link
              :href="route('tabulator.judges')"
              class="flex items-center justify-between p-4 rounded-lg border border-gray-100 hover:bg-indigo-50 hover:border-indigo-200 transition-all"
            >
              <div class="flex items-center">
                <div class="p-2 rounded-lg bg-indigo-100 mr-3">
                  <Users class="h-5 w-5 text-indigo-600" />
                </div>
                <div>
                  <div class="font-medium text-gray-900">Judge Management</div>
                  <div class="text-xs text-gray-500">Monitor and assist judges</div>
                </div>
              </div>
              <ChevronRight class="h-5 w-5 text-gray-400" />
            </Link>
            
            <Link
              :href="route('tabulator.results')"
              class="flex items-center justify-between p-4 rounded-lg border border-gray-100 hover:bg-purple-50 hover:border-purple-200 transition-all"
            >
              <div class="flex items-center">
                <div class="p-2 rounded-lg bg-purple-100 mr-3">
                  <BarChart3 class="h-5 w-5 text-purple-600" />
                </div>
                <div>
                  <div class="font-medium text-gray-900">Generate Results</div>
                  <div class="text-xs text-gray-500">Calculate final rankings</div>
                </div>
              </div>
              <ChevronRight class="h-5 w-5 text-gray-400" />
            </Link>
            
            <Link
              :href="route('tabulator.print')"
              class="flex items-center justify-between p-4 rounded-lg border border-gray-100 hover:bg-green-50 hover:border-green-200 transition-all"
            >
              <div class="flex items-center">
                <div class="p-2 rounded-lg bg-green-100 mr-3">
                  <Printer class="h-5 w-5 text-green-600" />
                </div>
                <div>
                  <div class="font-medium text-gray-900">Print Results</div>
                  <div class="text-xs text-gray-500">Generate printable reports</div>
                </div>
              </div>
              <ChevronRight class="h-5 w-5 text-gray-400" />
            </Link>
          </template>
        </div>
      </div>
    </div>
    
    <!-- Top Contestants -->
    <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <Trophy class="h-5 w-5 text-blue-600 mr-2" />
          Top Contestants
        </h3>
        <div class="flex items-center space-x-2">
          <div class="min-w-[130px]">
            <CustomSelect
              v-model="topContestantsFilter"
              :options="topContestantsOptions"
              variant="blue"
              placeholder="Select Round"
            />
          </div>
          <button class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
            View All <ChevronRight class="h-4 w-4 ml-1" />
          </button>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contestant</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Score</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template v-if="isLoading">
              <tr v-for="i in 3" :key="i" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full shimmer shimmer-blue"></div>
                    <div class="ml-4 space-y-2">
                      <div class="h-4 w-32 bg-gray-200 rounded shimmer shimmer-blue"></div>
                      <div class="h-3 w-24 bg-gray-200 rounded shimmer shimmer-blue"></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="h-4 w-8 bg-gray-200 rounded shimmer shimmer-blue"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="h-4 w-12 bg-gray-200 rounded shimmer shimmer-blue"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="h-7 w-7 bg-gray-200 rounded-full shimmer shimmer-blue"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="h-5 w-5 bg-gray-200 rounded shimmer shimmer-blue"></div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img class="h-10 w-10 rounded-full object-cover" src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&q=80" alt="Maria Garcia" />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">Maria Garcia</div>
                      <div class="text-sm text-gray-500">Miss Florida</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">#3</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-green-600">94.8</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-300 inline-flex items-center justify-center w-7 h-7">
                    1
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900">
                      <FileText class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img class="h-10 w-10 rounded-full object-cover" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80" alt="Sarah Johnson" />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">Sarah Johnson</div>
                      <div class="text-sm text-gray-500">Miss California</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">#1</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-green-600">92.5</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-300 inline-flex items-center justify-center w-7 h-7">
                    2
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900">
                      <FileText class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img class="h-10 w-10 rounded-full object-cover" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80" alt="Emily Davis" />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">Emily Davis</div>
                      <div class="text-sm text-gray-500">Miss New York</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">#2</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-teal-600">88.7</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center justify-center w-7 h-7">
                    3
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900">
                      <FileText class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { 
  Users, 
  ClipboardCheck, 
  Timer, 
  Activity, 
  ChevronRight, 
  UserCheck, 
  Clock, 
  CheckCircle, 
  Zap, 
  ClipboardList, 
  BarChart3, 
  Printer, 
  CalendarClock, 
  RefreshCw, 
  Trophy, 
  FileText
} from 'lucide-vue-next';
import CustomSelect from '@/Components/CustomSelect.vue';
import '@/Components/skeletons/skeleton.css';
import TabulatorLayout from '@/Layouts/TabulatorLayout.vue';

defineOptions({
  layout: TabulatorLayout
});

const isLoading = ref(false);
const CurrentRound = ref('evening_gown');

const roundOptions = ref([
  { value: 'evening_gown', label: 'Evening Gown' },
  { value: 'swimsuit', label: 'Swimsuit' },
  { value: 'qa', label: 'Q&A Round' },
  { value: 'talent', label: 'Talent' }
]);

// Top contestants filter
const topContestantsFilter = ref('all');
const topContestantsOptions = [
  { value: 'all', label: 'All Rounds' },
  { value: 'evening_gown', label: 'Evening Gown' },
  { value: 'swimsuit', label: 'Swimsuit' },
  { value: 'qa', label: 'Q&A' }
];

// Refresh dashboard data
const RefreshDashboard = () => {
  isLoading.value = true;
  
  // Simulate API call with a timeout
  setTimeout(() => {
    isLoading.value = false;
  }, 1500);
};
</script>