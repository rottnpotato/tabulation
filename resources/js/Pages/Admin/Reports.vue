<template>
  <Head title="Analytics & Reports" />
  <div class="space-y-8 bg-gray-50 min-h-screen">
    <!-- Document Header -->
    <div class="max-w-7xl mx-auto bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="max-w-7xl mx-auto">
          <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">System Analytics Report</h1>
              <p class="mt-2 text-gray-600">Comprehensive analysis of system performance and statistics</p>
              <p class="mt-1 text-sm text-gray-500">Generated on {{ new Date().toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
              }) }}</p>
            </div>
            <div class="flex items-center space-x-4">
              <div class="flex items-center text-sm text-gray-600">
                <span class="font-medium mr-2">Report Period:</span>
                <select 
                  v-model="selectedPeriod" 
                  @change="updatePeriod"
                  class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                >
                  <option value="7">Last 7 days</option>
                  <option value="30">Last 30 days</option>
                  <option value="90">Last 90 days</option>
                  <option value="year">This Year</option>
                  <option value="all">All Time</option>
                </select>
              </div>
              <div class="flex items-center space-x-2">
                <button 
                  @click="refreshData" 
                  class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  :disabled="isLoading"
                >
                  <RefreshCw :class="['h-4 w-4 mr-2 text-gray-500', {'animate-spin': isLoading}]" />
                  {{ isLoading ? 'Refreshing...' : 'Refresh' }}
                </button>
                <button 
                  @click="previewReport('comprehensive')" 
                  class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                  <Eye class="h-4 w-4 mr-2 text-blue-600" />
                  Preview Full Report
                </button>
                <button 
                  @click="exportReportsPDF" 
                  class="inline-flex items-center px-4 py-2 border border-blue-600 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  :disabled="isExporting"
                >
                  <FileDown :class="['h-4 w-4 mr-2', {'animate-spin': isExporting}]" />
                  {{ isExporting ? 'Generating...' : 'Export PDF' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 pb-8">
    
      <!-- Executive Summary -->
      <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Executive Summary</h2>
          <p class="text-sm text-gray-600 mt-1">Key performance indicators for the reporting period</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Completion Rate -->
            <div class="text-center border-r border-gray-200 last:border-r-0 pr-6 last:pr-0">
              <div class="text-3xl font-bold text-black-600">{{ pageantStats.completionRate }}%</div>
              <div class="text-sm text-gray-600 mt-1">Completion Rate</div>
              <div class="text-xs text-gray-500 mt-1">of pageants completed</div>
            </div>
            
            <!-- Average Duration -->
            <div class="text-center border-r border-gray-200 last:border-r-0 pr-6 last:pr-0">
              <div class="text-3xl font-bold text-black-600">{{ pageantStats.avgDuration }}</div>
              <div class="text-sm text-gray-600 mt-1">Average Duration</div>
              <div class="text-xs text-gray-500 mt-1">days per pageant</div>
            </div>
            
            <!-- Total Users -->
            <div class="text-center border-r border-gray-200 last:border-r-0 pr-6 last:pr-0">
              <div class="text-3xl font-bold text-black-600">{{ totalUsers }}</div>
              <div class="text-sm text-gray-600 mt-1">Total Users</div>
              <div class="text-xs text-gray-500 mt-1">across all roles</div>
            </div>
            
            <!-- Total Pageants -->
            <div class="text-center">
              <div class="text-3xl font-bold text-black-600">{{ totalPageants }}</div>
              <div class="text-sm text-gray-600 mt-1">Total Pageants</div>
              <div class="text-xs text-gray-500 mt-1">in the system</div>
            </div>
          </div>
        </div>
      </div>
    
      <!-- Data Analysis -->
      <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Data Analysis</h2>
          <p class="text-sm text-gray-600 mt-1">Visual representation of system metrics and trends</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Pageant Creation Trend -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-900">Pageant Creation Trend</h3>
                <p class="text-sm text-gray-600 mt-1">Monthly creation activity</p>
              </div>
              <div class="p-4">
                <div class="h-64">
                  <Bar 
                    :data="pageantCreationData" 
                    :options="barChartOptions" 
                  />
                </div>
              </div>
            </div>
            
            <!-- User Distribution by Role -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-900">User Distribution</h3>
                <p class="text-sm text-gray-600 mt-1">Users by role in the system</p>
              </div>
              <div class="p-4">
                <div class="h-64">
                  <Doughnut 
                    :data="userDistributionData" 
                    :options="doughnutChartOptions" 
                  />
                </div>
              </div>
            </div>
            
            <!-- System Activity by Day -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-900">System Activity</h3>
                <p class="text-sm text-gray-600 mt-1">Activity by day of week</p>
              </div>
              <div class="p-4">
                <div class="h-64">
                  <Bar 
                    :data="activityByDayData" 
                    :options="barChartOptions" 
                  />
                </div>
              </div>
            </div>
            
            <!-- Scoring System Distribution -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-900">Scoring Systems</h3>
                <p class="text-sm text-gray-600 mt-1">Distribution of scoring systems used</p>
              </div>
              <div class="p-4">
                <div class="h-64">
                  <Pie 
                    :data="scoringDistributionData" 
                    :options="doughnutChartOptions" 
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    
      <!-- Report Generation -->
      <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Report Generation</h2>
          <p class="text-sm text-gray-600 mt-1">Generate and export detailed reports in various formats</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Pageant Report -->
            <div class="border border-gray-200 rounded-lg p-4">
              <div class="text-center">
                <FileText class="h-8 w-8 text-blue-500 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 mb-2">Pageant Report</h3>
                <p class="text-xs text-gray-600 mb-4">Comprehensive pageant statistics and data</p>
                <div class="space-y-2">
                  <button 
                    @click="previewReport('pageants')" 
                    class="w-full px-3 py-2 text-xs border border-blue-300 rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  >
                    Preview Report
                  </button>
                  <button 
                    @click="exportReportCSV('pageants')" 
                    class="w-full px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  >
                    Export CSV
                  </button>
                </div>
              </div>
            </div>
            
            <!-- User Report -->
            <div class="border border-gray-200 rounded-lg p-4">
              <div class="text-center">
                <Users class="h-8 w-8 text-indigo-500 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 mb-2">User Report</h3>
                <p class="text-xs text-gray-600 mb-4">User management and role distribution data</p>
                <div class="space-y-2">
                  <button 
                    @click="previewReport('users')" 
                    class="w-full px-3 py-2 text-xs border border-indigo-300 rounded text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  >
                    Preview Report
                  </button>
                  <button 
                    @click="exportReportCSV('users')" 
                    class="w-full px-3 py-2 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  >
                    Export CSV
                  </button>
                </div>
              </div>
            </div>
            
            <!-- System Report -->
            <div class="border border-gray-200 rounded-lg p-4">
              <div class="text-center">
                <BarChart3 class="h-8 w-8 text-purple-500 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 mb-2">System Report</h3>
                <p class="text-xs text-gray-600 mb-4">System analytics and usage metrics</p>
                <div class="space-y-2">
                  <button 
                    @click="previewReport('system')" 
                    class="w-full px-3 py-2 text-xs border border-purple-300 rounded text-purple-700 bg-purple-50 hover:bg-purple-100 focus:outline-none focus:ring-1 focus:ring-purple-500"
                  >
                    Preview Report
                  </button>
                  <button 
                    @click="exportReportCSV('system')" 
                    class="w-full px-3 py-2 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 focus:outline-none focus:ring-1 focus:ring-purple-500"
                  >
                    Export CSV
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Preview Modal -->
    <ReportPreview 
      :show="showPreview"
      :reportData="currentReportData"
      @close="closePreview"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ReportPreview from '@/Components/ReportPreview.vue'
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
  Download,
  Eye
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
const showPreview = ref(false)
const currentReportData = ref({})

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
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(0, 0, 0, 1)',
      borderWidth: 1
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: '#6b7280'
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: '#6b7280'
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
        boxWidth: 10,
        color: '#6b7280'
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(0, 0, 0, 1)',
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
        backgroundColor: '#3b82f6',
        data: props.pageantStats.monthlyCreation,
        borderRadius: 3,
        barThickness: 16
      }
    ]
  }
})

const userDistributionData = computed(() => {
  const roleData = props.userStats.byRole || {}
  const labels = []
  const data = []
  const backgroundColors = [
    '#6366f1', // indigo-500
    '#3b82f6', // blue-500
    '#06b6d4', // cyan-500
    '#10b981', // emerald-500
    '#8b5cf6'  // violet-500
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
        backgroundColor: '#06b6d4',
        data: props.systemStats.activityByDay,
        borderRadius: 3,
        barThickness: 16
      }
    ]
  }
})

const scoringDistributionData = computed(() => {
  const scoringData = props.systemStats.scoringDistribution || {}
  const labels = []
  const data = []
  const backgroundColors = [
    '#8b5cf6', // violet-500
    '#ec4899', // pink-500
    '#f59e0b', // amber-500
    '#10b981', // emerald-500
    '#3b82f6'  // blue-500
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

// Generate report data
const generateReportData = (type = 'comprehensive') => {
  const now = new Date()
  const periodText = selectedPeriod.value === 'all' ? 'All Time' : 
                   selectedPeriod.value === 'year' ? 'This Year' : 
                   `Last ${selectedPeriod.value} days`

  // Sample recent pageants data - in real app this would come from props or API
  const samplePageants = [
    { id: 1, title: 'Miss Universe 2024', status: 'Completed', contestants_count: 25, created_at: '2024-01-15' },
    { id: 2, title: 'Miss World 2024', status: 'Ongoing', contestants_count: 18, created_at: '2024-01-10' },
    { id: 3, title: 'Miss Earth 2024', status: 'Upcoming', contestants_count: 22, created_at: '2024-01-05' },
    { id: 4, title: 'Miss International 2024', status: 'Completed', contestants_count: 30, created_at: '2024-01-01' }
  ]

  const baseData = {
    title: type === 'comprehensive' ? 'Comprehensive System Report' : 
           type === 'pageants' ? 'Pageant Activity Report' :
           type === 'users' ? 'User Management Report' : 'System Analytics Report',
    generatedDate: now.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }),
    period: periodText,
    summary: {
      totalPageants: totalPageants.value,
      totalUsers: totalUsers.value,
      completionRate: props.pageantStats.completionRate,
      avgDuration: props.pageantStats.avgDuration
    },
    userDistribution: props.userStats.byRole || {},
    recentPageants: samplePageants,
    monthlyActivity: {
      'January': 12,
      'February': 8,
      'March': 15,
      'April': 10
    },
    scoringSystems: props.systemStats.scoringDistribution || {}
  }

  // Customize data based on report type
  if (type === 'pageants') {
    baseData.title = 'Pageant Activity Report'
    // Focus on pageant-specific data
  } else if (type === 'users') {
    baseData.title = 'User Management Report'
    // Focus on user-specific data
  } else if (type === 'system') {
    baseData.title = 'System Analytics Report'
    // Focus on system metrics
  }

  return baseData
}

const exportReportsPDF = () => {
  currentReportData.value = generateReportData('comprehensive')
  showPreview.value = true
}

const previewReport = (type) => {
  currentReportData.value = generateReportData(type)
  showPreview.value = true
}

const closePreview = () => {
  showPreview.value = false
  currentReportData.value = {}
}

const exportReportCSV = (type) => {
  try {
    let csvContent = ''
    let filename = ''

    if (type === 'pageants') {
      csvContent = generatePageantCSV()
      filename = 'pageant_report'
    } else if (type === 'users') {
      csvContent = generateUserCSV()
      filename = 'user_report'
    } else if (type === 'system') {
      csvContent = generateSystemCSV()
      filename = 'system_report'
    }

    // Create and download CSV file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.csv`)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }
  } catch (error) {
    console.error('Error generating CSV:', error)
    alert('Error generating CSV file. Please try again.')
  }
}

const generatePageantCSV = () => {
  const headers = ['Title', 'Status', 'Contestants', 'Created Date', 'Completion Rate']
  const data = [
    ['Miss Universe 2024', 'Completed', '25', '2024-01-15', '100%'],
    ['Miss World 2024', 'Ongoing', '18', '2024-01-10', '75%'],
    ['Miss Earth 2024', 'Upcoming', '22', '2024-01-05', '0%'],
    ['Miss International 2024', 'Completed', '30', '2024-01-01', '100%']
  ]
  
  return [headers, ...data].map(row => row.join(',')).join('\n')
}

const generateUserCSV = () => {
  const headers = ['Role', 'Count', 'Percentage']
  const totalUsers = Object.values(props.userStats.byRole || {}).reduce((sum, count) => sum + count, 0)
  const data = Object.entries(props.userStats.byRole || {}).map(([role, count]) => [
    role.charAt(0).toUpperCase() + role.slice(1),
    count,
    `${((count / totalUsers) * 100).toFixed(1)}%`
  ])
  
  return [headers, ...data].map(row => row.join(',')).join('\n')
}

const generateSystemCSV = () => {
  const headers = ['Metric', 'Value']
  const data = [
    ['Total Pageants', totalPageants.value],
    ['Total Users', totalUsers.value],
    ['Completion Rate', `${props.pageantStats.completionRate}%`],
    ['Average Duration', `${props.pageantStats.avgDuration} days`],
    ...Object.entries(props.systemStats.scoringDistribution || {}).map(([system, count]) => 
      [`Scoring System: ${system}`, count]
    )
  ]
  
  return [headers, ...data].map(row => row.join(',')).join('\n')
}
</script>

<style scoped>
/* Document-style report page */
.report-page {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style> 