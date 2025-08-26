<template>
  <div class="print-container">
    <!-- Screen View -->
    <div class="screen-only bg-gray-50 min-h-screen py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- No Pageant Assigned -->
        <div v-if="!pageant" class="text-center py-16">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 mb-6">
            <Printer class="h-12 w-12 text-blue-500" />
          </div>
          <h3 class="text-xl font-medium text-gray-900 mb-2">No Pageant Assignment</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            You haven't been assigned to any pageants yet. Once an organizer assigns you to a pageant with completed scoring, you'll be able to generate and print the final results report.
          </p>
          <Link 
            href="/tabulator/dashboard"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
          >
            <LayoutDashboard class="w-4 h-4 mr-2" />
            Go to Dashboard
          </Link>
        </div>

        <div v-else>
          <div class="mb-8 flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Print Results</h1>
              <p class="text-gray-600 mt-2">{{ pageant.name }} - Final Results Report</p>
            </div>
            <button
              @click="printResults"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
            >
              <Printer class="w-4 h-4 mr-2" />
              Print Report
            </button>
          </div>

          <!-- Print Preview -->
          <div class="bg-white rounded-lg shadow-lg print-content" ref="printArea">
            <PrintableResults 
              :pageant="pageant"
              :results="results"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Print-Only Content -->
    <div v-if="pageant" class="print-only">
      <PrintableResults 
        :pageant="pageant"
        :results="results"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Printer, LayoutDashboard } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import PrintableResults from '../../Components/tabulator/PrintableResults.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Result {
  id: number
  number: number
  name: string
  image: string
  scores: Record<string, number>
  final_score: number
}

interface Pageant {
  id: number
  name: string
  date?: string
  venue?: string
  location?: string
}

interface Props {
  pageant?: Pageant
  results: Result[]
}

defineProps<Props>()

const printArea = ref<HTMLElement | null>(null)

const printResults = () => {
  window.print()
}
</script>

<style scoped>
@media print {
  .screen-only {
    display: none !important;
  }
  
  .print-only {
    display: block !important;
  }
  
  .print-container {
    margin: 0;
    padding: 0;
  }
}

@media screen {
  .print-only {
    display: none !important;
  }
}
</style>