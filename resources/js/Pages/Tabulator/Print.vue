<template>
  <div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Print Results</h2>
        <button
          @click="printResults"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <Printer class="h-5 w-5 mr-2" />
          Print Now
        </button>
      </div>

      <div class="border rounded-lg p-6 printArea" ref="printArea">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Miss Universe 2025</h1>
          <p class="text-gray-600">Final Results</p>
          <p class="text-sm text-gray-500">May 15, 2025</p>
        </div>

        <div class="space-y-6">
          <div class="border-b pb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Winners</h2>
            <div class="winners-row flex justify-between items-center gap-12 px-4" style="display: flex; flex-direction: row; width: 100%;">
              <!-- 1st Runner Up (Left) -->
              <div class="text-center winner-item" style="flex: 1; margin: 0 10px;">
                <div class="relative inline-block">
                  <img
                    src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80"
                    alt="1st Runner Up"
                    class="h-36 w-36 rounded-full object-cover border-4 border-gray-300"
                  />
                  <Award class="absolute -top-2 -right-2 h-10 w-10 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold mt-4">Emily Davis</h3>
                <p class="text-gray-600">1st Runner Up</p>
                <p class="text-sm text-gray-500">Score: 92.50</p>
              </div>
              
              <!-- Winner (Center) -->
              <div class="text-center winner-item" style="flex: 1.5; margin: 0 10px;">
                <div class="relative inline-block">
                  <img
                    src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80"
                    alt="Winner"
                    class="h-48 w-48 rounded-full object-cover border-4 border-yellow-400"
                  />
                  <Crown class="absolute -top-2 -right-2 h-12 w-12 text-yellow-400" />
                </div>
                <h3 class="text-2xl font-semibold mt-4">Sarah Johnson</h3>
                <p class="text-gray-600 font-medium text-lg">Winner</p>
                <p class="text-sm text-gray-500">Score: 95.75</p>
              </div>
              
              <!-- 2nd Runner Up (Right) -->
              <div class="text-center winner-item" style="flex: 1; margin: 0 10px;">
                <div class="relative inline-block">
                  <img
                    src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&q=80"
                    alt="2nd Runner Up"
                    class="h-36 w-36 rounded-full object-cover border-4 border-amber-400"
                  />
                  <Award class="absolute -top-2 -right-2 h-10 w-10 text-amber-400" />
                </div>
                <h3 class="text-lg font-semibold mt-4">Maria Garcia</h3>
                <p class="text-gray-600">2nd Runner Up</p>
                <p class="text-sm text-gray-500">Score: 90.25</p>
              </div>
            </div>
          </div>

          <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Complete Rankings</h2>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Rank
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contestant
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Evening Gown
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Swimsuit
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Q&A
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Final Score
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(result, index) in results" :key="result.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ index + 1 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <img
                        :src="result.image"
                        :alt="result.name"
                        class="h-10 w-10 rounded-full object-cover"
                      />
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ result.name }}</div>
                        <div class="text-sm text-gray-500">#{{ result.number }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ result.scores.evening_gown }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ result.scores.swimsuit }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ result.scores.qa }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    {{ result.final_score }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-8 pt-8 border-t">
            <div class="grid grid-cols-2 gap-8">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Certified by:</h3>
                <div class="mt-8 border-b border-gray-400 w-48"></div>
                <p class="text-sm text-gray-600">Head Tabulator</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Witnessed by:</h3>
                <div class="mt-8 border-b border-gray-400 w-48"></div>
                <p class="text-sm text-gray-600">Event Organizer</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Printer, Crown, Award } from 'lucide-vue-next'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Result {
  id: number
  number: number
  name: string
  image: string
  scores: {
    evening_gown: number
    swimsuit: number
    qa: number
  }
  final_score: number
}

const printArea = ref<HTMLElement | null>(null)

const results = ref<Result[]>([
  {
    id: 1,
    number: 1,
    name: 'Sarah Johnson',
    image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 95,
      swimsuit: 96,
      qa: 96
    },
    final_score: 95.75
  },
  {
    id: 2,
    number: 2,
    name: 'Emily Davis',
    image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 92,
      swimsuit: 93,
      qa: 92
    },
    final_score: 92.50
  },
  {
    id: 3,
    number: 3,
    name: 'Maria Garcia',
    image: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 90,
      swimsuit: 91,
      qa: 89
    },
    final_score: 90.25
  }
])

const printResults = () => {
  window.print()
}
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  .printArea, .printArea * {
    visibility: visible;
  }
  .printArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 20px;
  }
  
  @page {
    size: A4;
    margin: 1cm;
  }
  
  /* Force horizontal layout in print */
  .printArea .flex-col {
    flex-direction: row !important;
  }
  
  /* Specific styles for winners row */
  .printArea .winners-row {
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  
  .printArea .winner-item {
    display: inline-block !important;
    vertical-align: top !important;
    text-align: center !important;
  }
  
  /* Ensure correct order for winners in print */
  .printArea .order-1,
  .printArea .order-2,
  .printArea .order-3 {
    order: unset !important;
  }
  
  .printArea .md\:order-1 {
    order: 1 !important;
  }
  
  .printArea .md\:order-2 {
    order: 2 !important;
  }
  
  .printArea .md\:w-1\/3 {
    width: 33.333% !important;
  }
  
  /* Increase image sizes in print */
  .printArea .h-36 {
    height: 120px !important;
    width: 120px !important;
  }
  
  .printArea .h-48 {
    height: 160px !important;
    width: 160px !important;
  }
  
  /* Ensure tables don't break across pages */
  table {
    page-break-inside: avoid;
  }
  
  /* Adjust font sizes for better print readability */
  .text-sm {
    font-size: 10pt !important;
  }
  
  /* Ensure colors are preserved in print */
  * {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
  
  /* Add more spacing between winners */
  .printArea .gap-12 {
    gap: 2rem !important;
  }
  

}
</style>