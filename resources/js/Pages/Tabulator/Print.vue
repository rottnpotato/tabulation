<template>
  <div class="print-container min-h-screen bg-slate-50/50">
    <!-- Screen View -->
    <div class="screen-only py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-slate-200">
          <!-- Abstract Background Pattern -->
          <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-blue-50/30 to-white opacity-90"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
          </div>

          <div class="relative z-10 p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
              <div class="space-y-2">
                <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                  Print Results
                </h1>
                <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                  <FileText class="w-5 h-5 text-indigo-500" />
                  {{ pageant ? pageant.name : 'Report Generation' }}
                </p>
              </div>
              
              <div v-if="pageant" class="flex flex-wrap gap-3">
                <Link 
                  :href="route('tabulator.dashboard')"
                  class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm hover:shadow-md"
                >
                  <LayoutDashboard class="w-4 h-4" />
                  <span>Dashboard</span>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- No Pageant Assigned -->
        <div v-if="!pageant" class="text-center py-20 animate-fade-in">
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-12 max-w-xl mx-auto">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Printer class="h-8 w-8 text-slate-400" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No Pageant Selected</h3>
            <p class="text-slate-500 mb-8">
              Please select a pageant to generate reports.
            </p>
            <Link 
              :href="route('tabulator.dashboard')"
              class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <LayoutDashboard class="w-5 h-5 mr-2" />
              Return to Dashboard
            </Link>
          </div>
        </div>

        <div v-else class="animate-fade-in">
          <!-- Controls Toolbar -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-8 sticky top-4 z-20">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex items-center gap-4 flex-1">
                <!-- Stage Selector -->
                <div class="relative group">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Layers class="h-4 w-4 text-slate-400" />
                  </div>
                  <select
                    v-model="selectedStage"
                    class="pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full transition-shadow cursor-pointer hover:bg-slate-100 appearance-none font-medium"
                  >
                    <option v-for="(label, key) in stageLabels" :key="key" :value="key">
                      {{ label }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <ChevronDown class="h-4 w-4 text-slate-400" />
                  </div>
                </div>

                <!-- Paper Size Selector -->
                <div class="relative group">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Maximize class="h-4 w-4 text-slate-400" />
                  </div>
                  <select
                    v-model="selectedPaperSize"
                    class="pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full transition-shadow cursor-pointer hover:bg-slate-100 appearance-none font-medium"
                  >
                    <option v-for="(paper, key) in paperSizes" :key="key" :value="key">
                      {{ paper.name }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <ChevronDown class="h-4 w-4 text-slate-400" />
                  </div>
                </div>
              </div>
              
              <!-- Print Button -->
              <button
                @click="printResults"
                class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg shadow-blue-200 transform active:scale-95"
              >
                <Printer class="w-4 h-4 mr-2" />
                Print Report
              </button>
            </div>
          </div>

          <!-- Print Preview Container -->
          <div class="bg-slate-200/50 rounded-3xl p-8 border border-slate-200 overflow-hidden flex justify-center">
            <div class="bg-white shadow-2xl transition-transform duration-300 origin-top" 
                 :style="{ width: getPreviewWidth(), minHeight: '11in' }">
              <div class="print-content p-8" ref="printArea">
                <PrintableResults 
                  :pageant="pageant"
                  :results="resultsToShow"
                  :judges="judges"
                  :report-title="reportTitle"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Print-Only Content -->
    <div v-if="pageant" class="print-only">
      <PrintableResults 
        :pageant="pageant"
        :results="resultsToShow"
        :judges="judges"
        :report-title="reportTitle"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Printer, LayoutDashboard, ChevronDown, FileText, Layers, Maximize } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
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
  resultsOverall: Result[]
  resultsSemiFinal: Result[]
  resultsFinal: Result[]
  judges: Judge[]
}

const props = defineProps<Props>()

const printArea = ref<HTMLElement | null>(null)
const selectedPaperSize = ref<keyof typeof paperSizes>('letter')
type StageKey = 'overall' | 'semi-final' | 'final' | 'final-top3'
const selectedStage = ref<StageKey>('overall')

const paperSizes = {
  letter: { name: 'Letter (8.5" × 11")', size: 'letter', margin: '0.5in', width: '8.5in' },
  a4: { name: 'A4 (210mm × 297mm)', size: 'A4', margin: '12mm', width: '210mm' },
  legal: { name: 'Legal (8.5" × 14")', size: 'legal', margin: '0.5in', width: '8.5in' },
  oficio: { name: 'Oficio (8.5" × 13")', size: '8.5in 13in', margin: '0.5in', width: '8.5in' }
} as const

const stageLabels: Record<StageKey, string> = {
  overall: 'Overall Results',
  'semi-final': 'Semi-Final Results',
  final: 'Final Results',
  'final-top3': 'Final - Top 3 Only',
}

const resultsToShow = computed<Result[]>(() => {
  switch (selectedStage.value) {
    case 'semi-final':
      return props.resultsSemiFinal
    case 'final':
      return props.resultsFinal
    case 'final-top3':
      return props.resultsFinal.slice(0, 3)
    default:
      return props.resultsOverall
  }
})

const reportTitle = computed(() => stageLabels[selectedStage.value])

const getPreviewWidth = () => {
  return paperSizes[selectedPaperSize.value].width
}

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
            padding-bottom: 0px !important;
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
          
          .rounded, .rounded-lg, .rounded-xl, .rounded-3xl {
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
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

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
    background: white !important;
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