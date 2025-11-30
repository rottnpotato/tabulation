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
            <div class="flex items-center space-x-4" :class="{ 'no-print': isGeneratingPDF }">
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
                  class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="isLoading"
                >
                  <RefreshCw :class="['h-4 w-4 mr-2 text-gray-500', {'animate-spin': isLoading}]" />
                  {{ isLoading ? 'Refreshing...' : 'Refresh' }}
                </button>
                <button 
                  @click="printReport" 
                  class="inline-flex items-center px-4 py-2 border border-blue-600 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="isLoading"
                >
                  <FileDown :class="['h-4 w-4 mr-2', {'animate-spin': isLoading}]" />
                  {{ isLoading ? 'Generating PDF...' : 'Print Report' }}
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
    
      <!-- Report Generation 
      <-- <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Report Generation</h2>
          <p class="text-sm text-gray-600 mt-1">Generate and export detailed reports in various formats</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <-- Pageant Report -->
            <!-- <div class="border border-gray-200 rounded-lg p-4">
              <div class="text-center">
                <FileText class="h-8 w-8 text-blue-500 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 mb-2">Pageant Report</h3>
                <p class="text-xs text-gray-600 mb-4">Comprehensive pageant statistics and data</p>
              </div>
            </div>
             
            <-- User Report
            <-- <div class="border border-gray-200 rounded-lg p-4">
              <div class="text-center">
                <Users class="h-8 w-8 text-indigo-500 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 mb-2">User Report</h3>
                <p class="text-xs text-gray-600 mb-4">User management and role distribution data</p>
              </div>
            </div> 
            
            <-- System Report -->
            <!-- <div class="border border-gray-200 rounded-lg p-4">
              <div class="text-center">
                <BarChart3 class="h-8 w-8 text-purple-500 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 mb-2">System Report</h3>
                <p class="text-xs text-gray-600 mb-4">System analytics and usage metrics</p>
              </div> 
            <-- </div>
          </div>
        </div> 
      </div> -->
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
const isGeneratingPDF = ref(false)
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

// Print report function - generates PDF and opens print dialog
const printReport = async () => {
  // Don't generate if already loading
  if (isLoading.value || isGeneratingPDF.value) return
  
  isLoading.value = true
  isGeneratingPDF.value = true
  
  try {
    // Wait for the UI to update (hide buttons)
    await new Promise(resolve => setTimeout(resolve, 100))
    
    // Dynamic import to avoid loading jsPDF unless needed
    const { jsPDF } = await import('jspdf')
    const html2canvas = await import('html2canvas')
    
    // Get all the report sections - start from the body to capture everything
    const reportContainer = document.querySelector('.space-y-8.bg-gray-50.min-h-screen')
    
    if (!reportContainer) {
      console.error('Report content not found')
      alert('Unable to generate report. Please try again.')
      isLoading.value = false
      isGeneratingPDF.value = false
      return
    }
    
    // Create canvas from the report content with better settings
    const canvas = await html2canvas.default(reportContainer, {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#f9fafb',
      logging: false,
      width: reportContainer.scrollWidth,
      height: reportContainer.scrollHeight,
      x: 0,
      y: 0,
      ignoreElements: (element) => {
        return element.classList.contains('no-print')
      }
    })
    
    const imgData = canvas.toDataURL('image/png', 1.0)
    const pdf = new jsPDF('p', 'mm', 'a4')
    
    const imgWidth = 210 // A4 width in mm
    const pageHeight = 297 // A4 height in mm
    const imgHeight = (canvas.height * imgWidth) / canvas.width
    let heightLeft = imgHeight
    
    let position = 0
    
    // Add the image to PDF
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, '', 'FAST')
    heightLeft -= pageHeight
    
    // Add new pages if content is longer than one page
    while (heightLeft >= 0) {
      position = heightLeft - imgHeight
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, '', 'FAST')
      heightLeft -= pageHeight
    }
    
    // Get PDF as blob and open print dialog
    const pdfBlob = pdf.output('blob')
    const pdfUrl = URL.createObjectURL(pdfBlob)
    
    // Open PDF in new window and trigger print dialog
    const printWindow = window.open(pdfUrl, '_blank')
    if (printWindow) {
      printWindow.addEventListener('load', () => {
        setTimeout(() => {
          printWindow.print()
          // Clean up the blob URL after print dialog
          setTimeout(() => URL.revokeObjectURL(pdfUrl), 100)
        }, 250)
      })
    } else {
      alert('Please allow pop-ups to print the report')
      URL.revokeObjectURL(pdfUrl)
    }
    
  } catch (error) {
    console.error('Error generating PDF:', error)
    alert('Error generating PDF. Please try again.')
  } finally {
    isLoading.value = false
    isGeneratingPDF.value = false
  }
}
</script>

<style scoped>
/* Document-style report page */
.report-page {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Hide elements with no-print class during PDF generation */
.no-print {
  visibility: hidden !important;
  height: 0 !important;
  overflow: hidden !important;
  margin: 0 !important;
  padding: 0 !important;
}
</style> 