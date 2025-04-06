<template>
  <Head title="Analytics & Reports" />
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-xl shadow-md overflow-hidden">
      <div class="p-4 sm:p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
          <div class="text-white">
            <h1 class="text-2xl sm:text-3xl font-bold">Analytics & Reports</h1>
            <p class="mt-1 text-sm sm:text-base opacity-90">Comprehensive system statistics and insights</p>
          </div>
          <div class="flex flex-wrap gap-2 sm:space-x-3">
            <button 
              @click="exportReportsPDF" 
              class="bg-white text-teal-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-teal-50 flex items-center shadow-sm transition-all"
              :disabled="isExporting"
            >
              <FileDown :class="['h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-teal-600', {'animate-spin': isExporting}]" />
              <span>{{ isExporting ? 'Exporting...' : 'Export Report' }}</span>
            </button>
            <button 
              @click="refreshData" 
              class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white hover:bg-white/30 flex items-center shadow-sm transition-all"
              :disabled="isLoading"
            >
              <RefreshCw :class="['h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-white', {'animate-spin': isLoading}]" />
              <span>{{ isLoading ? 'Refreshing...' : 'Refresh Data' }}</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Filter Bar -->
      <div class="bg-teal-700/30 backdrop-blur-sm px-3 sm:px-6 py-3 sm:py-4">
        <div class="flex flex-wrap gap-2 sm:gap-3 items-center">
          <div class="text-white text-xs sm:text-sm">
            <span class="font-medium">Time Period:</span>
          </div>
          <select 
            v-model="selectedPeriod" 
            @change="updatePeriod"
            class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-2 py-1 sm:px-3 sm:py-1.5 text-xs sm:text-sm"
          >
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="year">This Year</option>
            <option value="all">All Time</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Key Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <!-- Completion Rate -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-teal-100 rounded-full">
            <CheckCircle class="h-5 w-5 text-teal-600" />
          </div>
          <h3 class="ml-3 text-sm font-medium text-gray-900">Completion Rate</h3>
        </div>
        <div class="mt-2 flex items-baseline justify-between">
          <div>
            <div class="text-2xl font-bold text-gray-900">{{ pageantStats.completionRate }}%</div>
            <div class="text-xs text-gray-500">of pageants completed</div>
          </div>
          <div class="h-10 w-10">
            <div class="radial-progress" :style="`--value: ${pageantStats.completionRate}; --size: 40px; --thickness: 4px;`">
              <div class="absolute inset-0 flex items-center justify-center text-[8px] font-bold text-teal-600">
                {{ pageantStats.completionRate }}%
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Average Duration -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-teal-100 rounded-full">
            <Calendar class="h-5 w-5 text-teal-600" />
          </div>
          <h3 class="ml-3 text-sm font-medium text-gray-900">Average Duration</h3>
        </div>
        <div class="mt-2">
          <div class="text-2xl font-bold text-gray-900">{{ pageantStats.avgDuration }} days</div>
          <div class="text-xs text-gray-500">per pageant event</div>
        </div>
      </div>
      
      <!-- Total Users -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-teal-100 rounded-full">
            <Users class="h-5 w-5 text-teal-600" />
          </div>
          <h3 class="ml-3 text-sm font-medium text-gray-900">Total Users</h3>
        </div>
        <div class="mt-2">
          <div class="text-2xl font-bold text-gray-900">{{ totalUsers }}</div>
          <div class="text-xs text-gray-500">across all roles</div>
        </div>
      </div>
      
      <!-- Total Pageants -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-teal-100 rounded-full">
            <Trophy class="h-5 w-5 text-teal-600" />
          </div>
          <h3 class="ml-3 text-sm font-medium text-gray-900">Total Pageants</h3>
        </div>
        <div class="mt-2">
          <div class="text-2xl font-bold text-gray-900">{{ totalPageants }}</div>
          <div class="text-xs text-gray-500">created in the system</div>
        </div>
      </div>
    </div>
    
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <!-- Pageant Creation Trend -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Pageant Creation Trend</h2>
            <div class="p-1.5 bg-teal-100 rounded-full">
              <TrendingUp class="h-4 w-4 text-teal-600" />
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">Monthly pageant creation activity</p>
        </div>
        <div class="p-4 sm:p-6">
          <div class="h-64">
            <Bar 
              :data="pageantCreationData" 
              :options="barChartOptions" 
            />
          </div>
        </div>
      </div>
      
      <!-- User Distribution by Role -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">User Distribution</h2>
            <div class="p-1.5 bg-teal-100 rounded-full">
              <PieChart class="h-4 w-4 text-teal-600" />
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">Users by role in the system</p>
        </div>
        <div class="p-4 sm:p-6">
          <div class="h-64">
            <Doughnut 
              :data="userDistributionData" 
              :options="doughnutChartOptions" 
            />
          </div>
        </div>
      </div>
      
      <!-- System Activity by Day -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">System Activity</h2>
            <div class="p-1.5 bg-teal-100 rounded-full">
              <Activity class="h-4 w-4 text-teal-600" />
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">Activity distribution by day of week</p>
        </div>
        <div class="p-4 sm:p-6">
          <div class="h-64">
            <Bar 
              :data="activityByDayData" 
              :options="barChartOptions" 
            />
          </div>
        </div>
      </div>
      
      <!-- Scoring System Distribution -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Scoring Systems</h2>
            <div class="p-1.5 bg-teal-100 rounded-full">
              <BarChart class="h-4 w-4 text-teal-600" />
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">Distribution of scoring systems used</p>
        </div>
        <div class="p-4 sm:p-6">
          <div class="h-64">
            <Pie 
              :data="scoringDistributionData" 
              :options="doughnutChartOptions" 
            />
          </div>
        </div>
      </div>
    </div>
    
    <!-- Export Options -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h2 class="text-base sm:text-lg font-semibold text-gray-900">Export Options</h2>
          <div class="p-1.5 bg-teal-100 rounded-full">
            <Download class="h-4 w-4 text-teal-600" />
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Generate and download detailed reports</p>
      </div>
      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <button @click="exportReportCSV('pageants')" class="flex items-center justify-center p-4 rounded-lg border border-gray-200 hover:bg-teal-50 hover:border-teal-200 transition-colors group">
            <FileText class="h-5 w-5 text-teal-600 mr-3" />
            <div class="text-left">
              <div class="font-medium text-gray-900 group-hover:text-teal-700">Pageant Report</div>
              <div class="text-xs text-gray-500">Export pageant data as CSV</div>
            </div>
          </button>
          
          <button @click="exportReportCSV('users')" class="flex items-center justify-center p-4 rounded-lg border border-gray-200 hover:bg-teal-50 hover:border-teal-200 transition-colors group">
            <Users class="h-5 w-5 text-teal-600 mr-3" />
            <div class="text-left">
              <div class="font-medium text-gray-900 group-hover:text-teal-700">User Report</div>
              <div class="text-xs text-gray-500">Export user statistics as CSV</div>
            </div>
          </button>
          
          <button @click="exportReportCSV('system')" class="flex items-center justify-center p-4 rounded-lg border border-gray-200 hover:bg-teal-50 hover:border-teal-200 transition-colors group">
            <BarChart3 class="h-5 w-5 text-teal-600 mr-3" />
            <div class="text-left">
              <div class="font-medium text-gray-900 group-hover:text-teal-700">System Report</div>
              <div class="text-xs text-gray-500">Export system usage data as CSV</div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Bar, Doughnut, Pie } from 'vue-chartjs'
import { 
  Calendar, 
  CheckCircle, 
  RefreshCw, 
  FileDown, 
  Users, 
  Trophy, 
  TrendingUp,
  Activity, 
  BarChart, 
  BarChart3, 
  FileText, 
  PieChart,
  Download
} from 'lucide-vue-next'

// Define the layout to use
defineOptions({
  layout: AdminLayout,
})

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

// Props from the controller
const props = defineProps({
  pageantStats: Object,
  userStats: Object,
  systemStats: Object
})

// UI state
const isLoading = ref(false)
const isExporting = ref(false)
const selectedPeriod = ref('30')

// Computed values
const totalUsers = computed(() => {
  const roles = props.userStats.byRole || {}
  return Object.values(roles).reduce((sum, count) => sum + count, 0)
})

const totalPageants = computed(() => {
  // Calculate from monthly data to ensure it's accurate based on the selected period
  return props.pageantStats.monthlyCreation.reduce((sum, count) => sum + count, 0)
})

// Chart Options
const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(13, 148, 136, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(13, 148, 136, 1)',
      borderWidth: 1
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        usePointStyle: true,
        boxWidth: 10
      }
    },
    tooltip: {
      backgroundColor: 'rgba(13, 148, 136, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(13, 148, 136, 1)',
      borderWidth: 1
    }
  }
}

// Chart Data computed values
const pageantCreationData = computed(() => {
  return {
    labels: props.pageantStats.monthLabels,
    datasets: [
      {
        label: 'Pageants Created',
        backgroundColor: '#0d9488',
        data: props.pageantStats.monthlyCreation,
        borderRadius: 5,
        barThickness: 12
      }
    ]
  }
})

const userDistributionData = computed(() => {
  const roleData = props.userStats.byRole || {}
  const labels = []
  const data = []
  const backgroundColors = [
    '#0d9488', // teal-600
    '#14b8a6', // teal-500
    '#2dd4bf', // teal-400
    '#5eead4', // teal-300
    '#99f6e4'  // teal-200
  ]
  
  // Map role names to more user-friendly display names
  const roleDisplayNames = {
    'admin': 'Administrators',
    'organizer': 'Organizers',
    'tabulator': 'Tabulators',
    'judge': 'Judges'
  }
  
  Object.entries(roleData).forEach(([role, count], index) => {
    labels.push(roleDisplayNames[role] || role)
    data.push(count)
  })
  
  return {
    labels,
    datasets: [
      {
        backgroundColor: backgroundColors.slice(0, labels.length),
        data,
        borderWidth: 0
      }
    ]
  }
})

const activityByDayData = computed(() => {
  return {
    labels: props.systemStats.dayLabels,
    datasets: [
      {
        label: 'System Activity',
        backgroundColor: '#14b8a6',
        data: props.systemStats.activityByDay,
        borderRadius: 5,
        barThickness: 12
      }
    ]
  }
})

const scoringDistributionData = computed(() => {
  const scoringData = props.systemStats.scoringDistribution || {}
  const labels = []
  const data = []
  const backgroundColors = [
    '#0d9488', // teal-600
    '#14b8a6', // teal-500
    '#2dd4bf', // teal-400
    '#5eead4', // teal-300
    '#99f6e4'  // teal-200
  ]
  
  // Map scoring system names to more user-friendly display names
  const scoringDisplayNames = {
    'percentage': 'Percentage (0-100%)',
    '1-10': 'Scale (1-10)',
    '1-5': 'Scale (1-5)',
    'points': 'Points System'
  }
  
  Object.entries(scoringData).forEach(([system, count], index) => {
    labels.push(scoringDisplayNames[system] || system)
    data.push(count)
  })
  
  return {
    labels,
    datasets: [
      {
        backgroundColor: backgroundColors.slice(0, labels.length),
        data,
        borderWidth: 0
      }
    ]
  }
})

// Methods
const refreshData = () => {
  isLoading.value = true
  setTimeout(() => {
    isLoading.value = false
    // In a real implementation, you would make an API call here to refresh the data
  }, 1500)
}

const updatePeriod = () => {
  isLoading.value = true
  setTimeout(() => {
    isLoading.value = false
    // In a real implementation, you would make an API call here to get data for the selected period
  }, 1000)
}

const exportReportsPDF = () => {
  isExporting.value = true
  setTimeout(() => {
    isExporting.value = false
    // In a real implementation, this would generate and download a PDF
    alert('PDF export functionality would be implemented here')
  }, 1500)
}

const exportReportCSV = (type) => {
  // In a real implementation, this would generate and download a CSV file
  alert(`CSV export for ${type} would be implemented here`)
}
</script>

<style scoped>
/* Radial progress indicator */
.radial-progress {
  position: relative;
  width: var(--size);
  height: var(--size);
  border-radius: 50%;
  background: conic-gradient(#0d9488 calc(var(--value) * 1%), #e5e7eb 0);
}

.radial-progress::before {
  content: '';
  position: absolute;
  inset: calc(var(--thickness) + 1px);
  background: white;
  border-radius: 50%;
}
</style> 