<template>
  <div class="print-container min-h-screen bg-slate-50/50">
    <!-- Screen View -->
    <div class="screen-only min-h-screen pb-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-indigo-100">
          <!-- Abstract Background Pattern -->
          <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-blue-50/50 to-white opacity-90"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
          </div>

          <div class="relative z-10 p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
              <div class="space-y-2">
                <div class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 uppercase tracking-wide border border-indigo-100 mb-2">
                  <Printer class="mr-1.5 h-3.5 w-3.5" />
                  <span>Print Center</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                  Print Final Results
                </h1>
                <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                  {{ pageant ? pageant.name : 'Select a pageant' }}
                  <span v-if="reportTitle" class="text-slate-400 mx-2">|</span>
                  <span v-if="reportTitle" class="text-indigo-600 font-medium">{{ reportTitle }}</span>
                </p>
                
                <div v-if="pageant" class="flex flex-wrap gap-2 mt-4">
                  <span v-if="pageant.date" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                    {{ pageant.date }}
                  </span>
                  <span v-if="pageant.venue" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 mr-1.5"></span>
                    {{ pageant.venue }}
                  </span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>
                    {{ judges.length }} Judges
                  </span>
                </div>
              </div>
              
              <div v-if="pageant" class="flex items-center">
                <button
                  @click="printResults"
                  class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 group"
                >
                  <Printer class="mr-2 h-5 w-5 group-hover:scale-110 transition-transform" />
                  <span>Print Report</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- No Pageant Assigned -->
        <div v-if="!pageant" class="text-center py-20 animate-fade-in">
          <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 max-w-2xl mx-auto">
            <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-slate-50 mb-6">
              <Printer class="h-12 w-12 text-slate-400" />
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-4">No Pageant Selected</h3>
            <p class="text-slate-500 mb-8 text-lg">
              Once an organizer assigns you to a pageant, you will be able to generate and print final results here.
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

        <div v-else class="space-y-8 animate-fade-in">
          <!-- Print Settings Card -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full -mr-8 -mt-8 opacity-50"></div>
            <div class="relative z-10">
              <h3 class="text-lg font-bold text-slate-900 mb-4">Print Settings</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Stage Selector -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-slate-700">Result Stage</label>
                  <div class="relative">
                    <button
                      @click="showStageDropdown = !showStageDropdown"
                      class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:border-indigo-300 hover:ring-2 hover:ring-indigo-50 transition-all focus:outline-none"
                    >
                      <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span>{{ stageLabels[selectedStage] }}</span>
                      </div>
                      <ChevronDown class="h-4 w-4 text-slate-400" />
                    </button>
                    
                    <div
                      v-if="showStageDropdown"
                      class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5"
                    >
                      <div class="p-1">
                        <button
                          v-for="(label, key) in stageLabels"
                          :key="key"
                          @click="selectedStage = key as StageKey; showStageDropdown = false"
                          class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm rounded-lg transition-colors"
                          :class="selectedStage === key ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'"
                        >
                          <span class="w-1.5 h-1.5 rounded-full" :class="selectedStage === key ? 'bg-indigo-500' : 'bg-slate-300'"></span>
                          {{ label }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Paper Size Selector -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-slate-700">Paper Size</label>
                  <div class="relative">
                    <button
                      @click="showPaperSizeDropdown = !showPaperSizeDropdown"
                      class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:border-indigo-300 hover:ring-2 hover:ring-indigo-50 transition-all focus:outline-none"
                    >
                      <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>{{ paperSizes[selectedPaperSize].name }}</span>
                      </div>
                      <ChevronDown class="h-4 w-4 text-slate-400" />
                    </button>
                    
                    <div
                      v-if="showPaperSizeDropdown"
                      class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5"
                    >
                      <div class="p-1">
                        <button
                          v-for="(paper, key) in paperSizes"
                          :key="key"
                          @click="selectedPaperSize = key; showPaperSizeDropdown = false"
                          class="flex w-full items-center justify-between px-3 py-2 text-left text-sm rounded-lg transition-colors"
                          :class="selectedPaperSize === key ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50'"
                        >
                          <span>{{ paper.name }}</span>
                          <span class="w-1.5 h-1.5 rounded-full" :class="selectedPaperSize === key ? 'bg-blue-500' : 'bg-slate-300'"></span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Preview Card -->
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Print Preview</h2>
                <p class="text-sm text-slate-500">Preview how the document will look when printed</p>
              </div>
              <div class="text-right hidden sm:block">
                <div class="text-sm font-medium text-slate-900">{{ pageant.name }}</div>
                <div class="text-xs text-slate-500">{{ reportTitle }}</div>
              </div>
            </div>
            
            <div class="p-8 bg-slate-100/50 overflow-x-auto">
              <div class="bg-white shadow-lg mx-auto transition-all duration-300" :style="{ width: getPreviewWidth(), minHeight: '11in' }">
                <div class="p-8" ref="printArea">
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
const showStageDropdown = ref(false)
const showPaperSizeDropdown = ref(false)
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
  const pageantInfo = props.pageant;
  const judgeCount = props.judges?.length ?? 0;
  const stageLabel = reportTitle.value;
  const paperName = paperConfig.name;
  const generatedAt = new Date().toLocaleString();
  
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
          background-color: #f3f4f6;
        }

        .print-shell {
          min-height: 100vh;
          box-sizing: border-box;
          padding: 24px;
        }

        .print-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 24px;
          padding-bottom: 12px;
          margin-bottom: 16px;
          border-bottom: 1px solid #e5e7eb;
        }

        .print-title {
          margin: 0;
          font-size: 20px;
          font-weight: 600;
          letter-spacing: -0.02em;
          color: #111827;
        }

        .print-subtitle {
          margin: 4px 0 0 0;
          font-size: 12px;
          color: #4b5563;
        }

        .print-meta {
          font-size: 11px;
          color: #4b5563;
        }

        .print-meta-row {
          display: flex;
          justify-content: flex-end;
          gap: 4px;
          margin-bottom: 2px;
        }

        .print-meta-label {
          font-weight: 500;
          color: #374151;
        }

        .print-main {
          margin-top: 12px;
        }

        .print-content-card {
          background-color: #ffffff;
          border-radius: 12px;
          padding: 16px 20px;
          box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
          border: 1px solid #e5e7eb;
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

          .print-shell {
            padding: 0;
          }

          .print-content-card {
            box-shadow: none;
            border-radius: 0;
            border: none;
            padding: 0;
          }
        }
      </style>
    </head>
    <body>
      <div class="print-shell">
        <header class="print-header">
          <div>
            <h1 class="print-title">${pageantInfo?.name ?? 'Final Results Report'}</h1>
            <p class="print-subtitle">
              ${stageLabel ? stageLabel : ''}
            </p>
          </div>
          <div class="print-meta">
            ${pageantInfo?.date ? `<div class="print-meta-row"><span class="print-meta-label">Date:</span><span>${pageantInfo.date}</span></div>` : ''}
            ${pageantInfo?.venue ? `<div class="print-meta-row"><span class="print-meta-label">Venue:</span><span>${pageantInfo.venue}</span></div>` : ''}
            ${pageantInfo?.location ? `<div class="print-meta-row"><span class="print-meta-label">Location:</span><span>${pageantInfo.location}</span></div>` : ''}
            <div class="print-meta-row"><span class="print-meta-label">Stage:</span><span>${stageLabel}</span></div>
            <div class="print-meta-row"><span class="print-meta-label">Paper:</span><span>${paperName}</span></div>
            <div class="print-meta-row"><span class="print-meta-label">Judges:</span><span>${judgeCount}</span></div>
            <div class="print-meta-row"><span class="print-meta-label">Generated:</span><span>${generatedAt}</span></div>
          </div>
        </header>
        <main class="print-main">
          <div class="print-content-card">
            ${printContent.innerHTML}
          </div>
        </main>
      </div>
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