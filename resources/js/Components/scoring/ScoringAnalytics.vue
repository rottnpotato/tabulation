<template>
  <div id="analyticsContainer" class="bg-white shadow-sm rounded-lg p-6 printAnalytics" ref="analyticsContainer">
    <div class="mb-6">
      <h3 class="text-xl font-semibold text-gray-900 flex items-center">
        <BarChart3 class="h-5 w-5 text-purple-600 mr-2" />
        Scoring Analytics
      </h3>
      <p class="text-gray-600">Advanced visualization of contestant scores and judge patterns</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Overall Score Distribution -->
      <div class="border rounded-lg p-4 h-80">
        <h4 class="text-sm font-medium text-gray-700 mb-4">Overall Score Distribution</h4>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
          <!-- Placeholder for chart -->
          <div class="text-center p-6">
            <PieChart class="h-10 w-10 text-purple-400 mx-auto mb-2" />
            <p class="text-sm text-gray-500">Score distribution visualization will appear here</p>
            <button class="mt-2 text-xs text-purple-600 hover:text-purple-800 font-medium">
              Generate Chart
            </button>
          </div>
        </div>
      </div>

      <!-- Judge Comparison -->
      <div class="border rounded-lg p-4 h-80">
        <h4 class="text-sm font-medium text-gray-700 mb-4">Judge Scoring Comparison</h4>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
          <!-- Placeholder for chart -->
          <div class="text-center p-6">
            <GitCompare class="h-10 w-10 text-purple-400 mx-auto mb-2" />
            <p class="text-sm text-gray-500">Judge comparison chart will appear here</p>
            <button class="mt-2 text-xs text-purple-600 hover:text-purple-800 font-medium">
              Generate Chart
            </button>
          </div>
        </div>
      </div>

      <!-- Contestant Performance by Round -->
      <div class="border rounded-lg p-4 h-80">
        <h4 class="text-sm font-medium text-gray-700 mb-4">Contestant Performance by Round</h4>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
          <!-- Placeholder for chart -->
          <div class="text-center p-6">
            <LineChart class="h-10 w-10 text-purple-400 mx-auto mb-2" />
            <p class="text-sm text-gray-500">Round performance chart will appear here</p>
            <button class="mt-2 text-xs text-purple-600 hover:text-purple-800 font-medium">
              Generate Chart
            </button>
          </div>
        </div>
      </div>

      <!-- Criteria Breakdown -->
      <div class="border rounded-lg p-4 h-80">
        <h4 class="text-sm font-medium text-gray-700 mb-4">Criteria Score Breakdown</h4>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
          <!-- Placeholder for chart -->
          <div class="text-center p-6">
            <PieChart class="h-10 w-10 text-purple-400 mx-auto mb-2" />
            <p class="text-sm text-gray-500">Criteria breakdown chart will appear here</p>
            <button class="mt-2 text-xs text-purple-600 hover:text-purple-800 font-medium">
              Generate Chart
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Performers Table -->
    <div class="mt-6 border rounded-lg overflow-hidden">
      <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
        <h4 class="text-sm font-medium text-gray-700">Top Performing Contestants</h4>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rank
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Contestant
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Overall Score
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Strongest Category
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Score Trend
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(contestant, index) in topContestants" :key="contestant.id" class="hover:bg-purple-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="text-sm font-medium text-gray-900 flex items-center justify-center h-6 w-6 rounded-full" 
                    :class="getRankClass(index)">
                    {{ index + 1 }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-full overflow-hidden mr-3">
                    <img :src="contestant.image" :alt="contestant.name" class="h-full w-full object-cover">
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ contestant.name }}
                    </div>
                    <div class="text-xs text-gray-500">
                      #{{ contestant.number }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ contestant.score.toFixed(2) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getCategoryClass(contestant.strongestCategory)">
                  {{ contestant.strongestCategory }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <component :is="getTrendIcon(contestant.trend)" 
                    class="h-4 w-4 mr-1" 
                    :class="getTrendColor(contestant.trend)" />
                  <span class="text-xs" :class="getTrendColor(contestant.trend)">
                    {{ getTrendLabel(contestant.trend) }}
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Export Options -->
    <div class="mt-6 flex justify-end">
      <div class="flex space-x-3">
        <button class="px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
          <FileText class="h-4 w-4 mr-2" />
          Export CSV
        </button>
        <button 
          @click="PrintReport"
          class="px-3 py-2 text-sm text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors flex items-center">
          <Printer class="h-4 w-4 mr-2" />
          Print Report
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { 
  BarChart3, PieChart, LineChart, GitCompare, 
  TrendingUp, TrendingDown, Minus,  Printer, FileText
} from 'lucide-vue-next'

interface Contestant {
  id: number
  name: string
  number: number
  image: string
  score: number
  strongestCategory: string
  trend: 'up' | 'down' | 'stable'
}

const analyticsContainer = ref<HTMLElement | null>(null)

// Sample data for the top contestants
const topContestants = ref<Contestant[]>([
  {
    id: 1,
    name: 'Jessica Miller',
    number: 5,
    image: 'https://randomuser.me/api/portraits/women/1.jpg',
    score: 92.45,
    strongestCategory: 'Evening Gown',
    trend: 'up'
  },
  {
    id: 2,
    name: 'Amanda Johnson',
    number: 12,
    image: 'https://randomuser.me/api/portraits/women/2.jpg',
    score: 91.30,
    strongestCategory: 'Talent',
    trend: 'up'
  },
  {
    id: 3,
    name: 'Sophia Garcia',
    number: 8,
    image: 'https://randomuser.me/api/portraits/women/3.jpg',
    score: 89.75,
    strongestCategory: 'Q&A',
    trend: 'stable'
  },
  {
    id: 4,
    name: 'Emily Chen',
    number: 3,
    image: 'https://randomuser.me/api/portraits/women/4.jpg',
    score: 88.60,
    strongestCategory: 'Swimsuit',
    trend: 'down'
  },
  {
    id: 5,
    name: 'Olivia Thompson',
    number: 15,
    image: 'https://randomuser.me/api/portraits/women/5.jpg',
    score: 87.20,
    strongestCategory: 'Evening Gown',
    trend: 'up'
  },
])

// Print Report function
const PrintReport = () => {
  window.print()
}

// Helper functions for styling
const getRankClass = (index: number) => {
  switch (index) {
    case 0: return 'bg-amber-100 text-amber-800'
    case 1: return 'bg-gray-100 text-gray-800'
    case 2: return 'bg-amber-50 text-amber-700'
    default: return 'bg-gray-50 text-gray-600'
  }
}

const getCategoryClass = (category: string) => {
  switch (category) {
    case 'Evening Gown': return 'bg-purple-100 text-purple-800'
    case 'Swimsuit': return 'bg-blue-100 text-blue-800'
    case 'Talent': return 'bg-green-100 text-green-800'
    case 'Q&A': return 'bg-amber-100 text-amber-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getTrendIcon = (trend: string) => {
  switch (trend) {
    case 'up': return TrendingUp
    case 'down': return TrendingDown
    default: return Minus
  }
}

const getTrendColor = (trend: string) => {
  switch (trend) {
    case 'up': return 'text-green-600'
    case 'down': return 'text-red-600'
    default: return 'text-gray-600'
  }
}

const getTrendLabel = (trend: string) => {
  switch (trend) {
    case 'up': return 'Improving'
    case 'down': return 'Declining'
    default: return 'Stable'
  }
}
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  .printAnalytics, .printAnalytics * {
    visibility: visible;
  }
  .printAnalytics {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.5cm;
    background-color: white !important;
    color: black !important;
    transform-origin: top left;
    transform: scale(0.9);
    page-break-inside: avoid;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
  
  @page {
    size: A4;
    margin: 0.5cm;
  }
  
  table {
    font-size: 11pt;
  }
  
  img {
    max-width: 100%;
    height: auto;
  }
  
  /* Make charts fit better */
  .h-80 {
    height: auto !important;
    max-height: 200px !important;
  }
  
  /* Hide buttons in print view */
  button {
    display: none !important;
  }
}
</style> 