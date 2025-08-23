<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50">
    <div class="flex items-start justify-center min-h-screen p-4">
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal - Full width with max constraint -->
      <div class="relative bg-white rounded-lg shadow-xl transform transition-all w-full max-w-7xl my-8">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <FileText class="h-6 w-6 text-gray-600 mr-3" />
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Report Preview</h3>
                <p class="text-sm text-gray-600">{{ reportData.title }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <button
                @click="generatePDF"
                :disabled="isGenerating"
                class="inline-flex items-center px-4 py-2 border border-blue-600 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
              >
                <Download v-if="!isGenerating" class="h-4 w-4 mr-2" />
                <Loader2 v-else class="h-4 w-4 mr-2 animate-spin" />
                {{ isGenerating ? 'Generating...' : 'Download PDF' }}
              </button>
              <button 
                @click="$emit('close')"
                class="text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <X class="h-6 w-6" />
              </button>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="bg-white max-h-[80vh] overflow-y-auto">
          <div id="report-content" class="p-8 bg-white">
            <!-- Report Header -->
            <div class="text-center border-b border-gray-300 pb-8 mb-8">
              <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Pageant Tabulation System</h1>
                <p class="text-lg text-gray-600">Administrative Report</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900 mb-3">{{ reportData.title }}</h2>
                <div class="text-sm text-gray-600 space-y-1">
                  <p><span class="font-medium">Generated:</span> {{ reportData.generatedDate }}</p>
                  <p><span class="font-medium">Report Period:</span> {{ reportData.period }}</p>
                </div>
              </div>
            </div>

            <!-- Executive Summary -->
            <div class="mb-8">
              <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                Executive Summary
              </h3>
              <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                  <h4 class="text-sm font-medium text-gray-900">Key Performance Indicators</h4>
                </div>
                <div class="p-6">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center border-r border-gray-200 last:border-r-0 pr-6 last:pr-0">
                      <div class="text-2xl font-bold text-black-600">{{ reportData.summary.totalPageants }}</div>
                      <div class="text-sm text-gray-600 mt-1">Total Pageants</div>
                    </div>
                    <div class="text-center border-r border-gray-200 last:border-r-0 pr-6 last:pr-0">
                      <div class="text-2xl font-bold text-black-600">{{ reportData.summary.totalUsers }}</div>
                      <div class="text-sm text-gray-600 mt-1">Total Users</div>
                    </div>
                    <div class="text-center border-r border-gray-200 last:border-r-0 pr-6 last:pr-0">
                      <div class="text-2xl font-bold text-black-600">{{ reportData.summary.completionRate }}%</div>
                      <div class="text-sm text-gray-600 mt-1">Completion Rate</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-black-600">{{ reportData.summary.avgDuration }}</div>
                      <div class="text-sm text-gray-600 mt-1">Avg Duration (days)</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="mb-8">
              <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                Detailed Statistics
              </h3>
              
              <!-- User Distribution -->
              <div class="mb-6">
                <h4 class="text-base font-medium text-gray-900 mb-3">User Distribution by Role</h4>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h5 class="text-sm font-medium text-gray-700">Role Distribution</h5>
                  </div>
                  <div class="p-4">
                    <div class="grid grid-cols-2 gap-4">
                      <div v-for="(count, role) in reportData.userDistribution" :key="role" class="flex justify-between py-1">
                        <span class="text-gray-700 capitalize">{{ role }}s:</span>
                        <span class="font-medium text-gray-900">{{ count }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Pageants -->
              <div class="mb-6">
                <h4 class="text-base font-medium text-gray-900 mb-3">Recent Pageants</h4>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h5 class="text-sm font-medium text-gray-700">Activity Summary</h5>
                  </div>
                  <table class="min-w-full">
                    <thead class="bg-gray-100">
                      <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Title</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Contestants</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Date</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white">
                      <tr v-for="pageant in reportData.recentPageants" :key="pageant.id" class="border-t border-gray-200">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ pageant.title }}</td>
                        <td class="px-4 py-3 text-sm">
                          <span class="inline-flex px-2 py-1 text-xs font-medium rounded border"
                                :class="getStatusColor(pageant.status)">
                            {{ pageant.status }}
                          </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ pageant.contestants_count }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(pageant.created_at) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- System Activity -->
            <div class="mb-8">
              <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                System Activity Overview
              </h3>
              <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                  <h4 class="text-sm font-medium text-gray-700">Activity and Usage Metrics</h4>
                </div>
                <div class="p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                      <h5 class="font-medium text-gray-900 mb-3">Monthly Activity</h5>
                      <div class="space-y-2">
                        <div v-for="(activity, month) in reportData.monthlyActivity" :key="month" class="flex justify-between py-1 border-b border-gray-100 last:border-b-0">
                          <span class="text-gray-700">{{ month }}:</span>
                          <span class="font-medium text-gray-900">{{ activity }} events</span>
                        </div>
                      </div>
                    </div>
                    <div>
                      <h5 class="font-medium text-gray-900 mb-3">Scoring Systems Usage</h5>
                      <div class="space-y-2">
                        <div v-for="(count, system) in reportData.scoringSystems" :key="system" class="flex justify-between py-1 border-b border-gray-100 last:border-b-0">
                          <span class="text-gray-700">{{ system }}:</span>
                          <span class="font-medium text-gray-900">{{ count }} pageants</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-300 pt-6 text-center text-sm text-gray-500">
              <div class="space-y-1">
                <p>This report was automatically generated by the Pageant Tabulation System</p>
                <p>For questions or support, please contact the system administrator</p>
                <p class="mt-3 font-medium text-gray-600">© {{ new Date().getFullYear() }} Pageant Tabulation System. All rights reserved.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { 
  FileText, 
  Download, 
  X, 
  Crown, 
  TrendingUp, 
  BarChart3, 
  Activity,
  Loader2
} from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  reportData: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'download'])

const isGenerating = ref(false)

const getStatusColor = (status) => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'bg-emerald-100 text-emerald-800 border-emerald-300'
    case 'ongoing':
      return 'bg-blue-100 text-blue-800 border-blue-300'
    case 'upcoming':
      return 'bg-amber-100 text-amber-800 border-amber-300'
    case 'cancelled':
      return 'bg-red-100 text-red-800 border-red-300'
    default:
      return 'bg-gray-100 text-gray-800 border-gray-300'
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const generatePDF = async () => {
  isGenerating.value = true
  
  try {
    // Dynamic import to avoid loading jsPDF unless needed
    const { jsPDF } = await import('jspdf')
    const html2canvas = await import('html2canvas')
    
    const element = document.getElementById('report-content')
    
    // Create canvas from the report content
    const canvas = await html2canvas.default(element, {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff'
    })
    
    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF('p', 'mm', 'a4')
    
    const imgWidth = 210
    const pageHeight = 295
    const imgHeight = (canvas.height * imgWidth) / canvas.width
    let heightLeft = imgHeight
    
    let position = 0
    
    // Add the image to PDF
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
    heightLeft -= pageHeight
    
    // Add new pages if content is longer than one page
    while (heightLeft >= 0) {
      position = heightLeft - imgHeight
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
      heightLeft -= pageHeight
    }
    
    // Download the PDF
    const filename = `${props.reportData.title.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`
    pdf.save(filename)
    
    emit('download', filename)
  } catch (error) {
    console.error('Error generating PDF:', error)
    alert('Error generating PDF. Please try again.')
  } finally {
    isGenerating.value = false
  }
}
</script>

<style scoped>
/* Print styles for better PDF generation */
@media print {
  .fixed {
    position: static !important;
  }
  
  .bg-gradient-to-r {
    background: #0d9488 !important;
  }
}
</style>
