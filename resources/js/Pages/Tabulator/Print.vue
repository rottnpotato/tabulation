<template>
  <div class="print-container">
    <!-- Screen View -->
    <div class="screen-only bg-gray-50 min-h-screen py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- No Pageant Assigned -->
        <div v-if="!pageant" class="text-center py-16">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-100 to-blue-200 mb-6">
            <Printer class="h-12 w-12 text-blue-600" />
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
            <div class="flex items-center gap-4">
              <!-- Paper Size Selector -->
              <div class="relative">
                <button
                  @click="showPaperSizeDropdown = !showPaperSizeDropdown"
                  class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  {{ paperSizes[selectedPaperSize].name }}
                  <ChevronDown class="w-4 h-4 ml-2" />
                </button>
                
                <div 
                  v-if="showPaperSizeDropdown"
                  class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-10"
                >
                  <div class="py-1">
                    <button
                      v-for="(paper, key) in paperSizes"
                      :key="key"
                      @click="selectedPaperSize = key; showPaperSizeDropdown = false"
                      class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors duration-150"
                      :class="{
                        'bg-blue-50 text-blue-700': selectedPaperSize === key,
                        'text-gray-700': selectedPaperSize !== key
                      }"
                    >
                      {{ paper.name }}
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Print Button -->
              <button
                @click="printResults"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
              >
                <Printer class="w-4 h-4 mr-2" />
                Print Report
              </button>
            </div>
          </div>

          <!-- Print Preview -->
          <div class="bg-white rounded-lg shadow-lg print-content" ref="printArea">
            <PrintableResults 
              :pageant="pageant"
              :results="results"
              :judges="judges"
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
        :judges="judges"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Printer, LayoutDashboard, ChevronDown } from 'lucide-vue-next'
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

interface Judge {
  id: number
  name: string
  role: string
}

interface Props {
  pageant?: Pageant
  results: Result[]
  judges: Judge[]
}

defineProps<Props>()

const printArea = ref<HTMLElement | null>(null)
const selectedPaperSize = ref<keyof typeof paperSizes>('letter')
const showPaperSizeDropdown = ref(false)

const paperSizes = {
  letter: { name: 'Letter (8.5" × 11")', size: 'letter', margin: '0.5in' },
  a4: { name: 'A4 (210mm × 297mm)', size: 'A4', margin: '12mm' },
  legal: { name: 'Legal (8.5" × 14")', size: 'legal', margin: '0.5in' },
  oficio: { name: 'Oficio (8.5" × 13")', size: '8.5in 13in', margin: '0.5in' }
} as const

const printResults = () => {
  // Get the printable content
  const printContent = document.querySelector('.print-only');
  
  if (!printContent) {
    console.error('Print content not found');
    return;
  }
  
  // Get selected paper size configuration
  const paperConfig = paperSizes[selectedPaperSize.value];
  
  // Get all stylesheets from the current page
  const stylesheets = Array.from(document.styleSheets)
    .map(sheet => {
      try {
        // For external stylesheets, get the href
        if (sheet.href) {
          return `<link rel="stylesheet" href="${sheet.href}">`;
        }
        // For inline styles, get the CSS rules
        else if (sheet.cssRules) {
          const rules = Array.from(sheet.cssRules)
            .map(rule => rule.cssText)
            .join('\n');
          return `<style>${rules}</style>`;
        }
      } catch (e) {
        // Handle CORS issues with external stylesheets
        console.warn('Could not access stylesheet:', e);
        return '';
      }
      return '';
    })
    .filter(Boolean)
    .join('\n');
  
  // Create a new window for printing
  const printWindow = window.open('', '_blank');
  
  if (!printWindow) {
    console.error('Could not open print window');
    return;
  }
  
  // Copy the content to the new window with all styles
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Results - ${paperConfig.name}</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      ${stylesheets}
      <style>
        body {
          margin: 0;
          padding: 0;
          font-family: system-ui, -apple-system, sans-serif;
        }
        @media print {
          @page {
            size: ${paperConfig.size};
            margin: ${paperConfig.margin};
          }
          
          body {
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
          }
          
          /* Ensure only printable content shows */
          .screen-only {
            display: none !important;
          }
          
          .print-only {
            display: block !important;
            width: 100%;
            max-width: none;
          }
          
          /* Optimize text sizes for selected paper */
          h1 { font-size: 18px; margin: 0 0 8px 0; }
          h2 { font-size: 16px; margin: 0 0 6px 0; }
          h3 { font-size: 14px; margin: 0 0 4px 0; }
          h4, h5, h6 { font-size: 12px; margin: 0 0 4px 0; }
          
          p, div, span {
            font-size: 11px;
            line-height: 1.3;
          }
          
          /* Table optimizations */
          table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            page-break-inside: auto;
          }
          
          th, td {
            padding: 4px 6px;
            border: 1px solid #ccc;
            font-size: 10px;
            line-height: 1.2;
          }
          
          th {
            background-color: #f5f5f5 !important;
            font-weight: bold;
          }
          
          /* Page break controls */
          .page-break-before {
            page-break-before: always;
          }
          
          .page-break-after {
            page-break-after: always;
          }
          
          .no-page-break {
            page-break-inside: avoid;
          }
          
          /* Reduce excessive spacing */
          .space-y-4 > * + * {
            margin-top: 8px !important;
          }
          
          .space-y-6 > * + * {
            margin-top: 12px !important;
          }
          
          .mb-4, .my-4 {
            margin-bottom: 8px !important;
          }
          
          .mb-6, .my-6 {
            margin-bottom: 12px !important;
          }
          
          .p-4 {
            padding: 8px !important;
          }
          
          .p-6 {
            padding: 12px !important;
          }
          
          /* Image sizing */
          img {
            max-width: 60px;
            max-height: 60px;
            object-fit: cover;
          }
          
          /* Remove shadows and rounded corners for print */
          .shadow, .shadow-lg, .shadow-xl {
            box-shadow: none !important;
          }
          
          .rounded, .rounded-lg, .rounded-xl {
            border-radius: 0 !important;
          }
        }
      </style>
    </head>
    <body>
      ${printContent.innerHTML}
    </body>
    </html>
  `);
  
  printWindow.document.close();
  
  // Wait for content to load, then print
  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
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

<style>
/* Global print styles to hide layout elements */
@media print {
  /* Hide sidebar and navigation */
  .sidebar,
  .side-nav,
  nav,
  .navigation,
  .nav-container,
  [class*="nav"],
  [class*="sidebar"] {
    display: none !important;
  }
  
  /* Hide header elements */
  header,
  .header,
  [class*="header"] {
    display: none !important;
  }
  
  /* Hide buttons and interactive elements */
  button,
  .btn,
  [class*="button"] {
    display: none !important;
  }
  
  /* Ensure full width for print content */
  body,
  #app,
  .min-h-screen,
  .bg-gray-50 {
    margin: 0 !important;
    padding: 0 !important;
    background: white !important;
  }
  
  /* Override layout padding */
  .p-6 {
    padding: 0 !important;
  }
  
  /* Show only print content */
  .print-only {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }
}
</style>