<template>
  <div class="print-container">
    <!-- Screen View -->
    <div class="screen-only min-h-screen bg-slate-950/5 py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- No Pageant Assigned -->
        <div v-if="!pageant" class="flex items-center justify-center py-20">
          <div class="max-w-xl w-full rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 px-8 py-10 text-center">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 text-white shadow-lg">
              <Printer class="h-10 w-10" />
            </div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">No Pageant Assignment Yet</h1>
            <p class="mt-3 text-sm text-gray-600">
              You haven't been assigned to any pageants yet. Once an organizer assigns you to a pageant with completed scoring,
              you'll be able to generate and print a polished final results report from here.
            </p>
            <div class="mt-6 flex justify-center">
              <Link
                :href="route('tabulator.dashboard')"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors duration-150 hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
              >
                <LayoutDashboard class="h-4 w-4" />
                <span>Back to Tabulator Dashboard</span>
              </Link>
            </div>
          </div>
        </div>

        <div v-else class="space-y-6">
          <!-- Header & Controls Card -->
          <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 sm:px-8">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                  <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 text-white shadow-md">
                    <Printer class="h-6 w-6" />
                  </div>
                  <div>
                    <h1 class="text-xl font-semibold tracking-tight text-white sm:text-2xl">
                      Print Final Results
                    </h1>
                    <p class="mt-1 text-sm text-blue-100">
                      {{ pageant.name }}
                      <span v-if="reportTitle" class="hidden sm:inline">&mdash; {{ reportTitle }}</span>
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-blue-50/90">
                      <span v-if="pageant.date" class="inline-flex items-center gap-1 rounded-full bg-black/10 px-2.5 py-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        <span>Date: {{ pageant.date }}</span>
                      </span>
                      <span v-if="pageant.venue" class="inline-flex items-center gap-1 rounded-full bg-black/10 px-2.5 py-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-sky-300"></span>
                        <span>Venue: {{ pageant.venue }}</span>
                      </span>
                      <span v-if="pageant.location" class="inline-flex items-center gap-1 rounded-full bg-black/10 px-2.5 py-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-violet-300"></span>
                        <span>Location: {{ pageant.location }}</span>
                      </span>
                      <span class="inline-flex items-center gap-1 rounded-full bg-black/10 px-2.5 py-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                        <span>Judges: {{ judges.length }}</span>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                  <!-- Stage Selector -->
                  <div class="relative">
                    <button
                      @click="showStageDropdown = !showStageDropdown"
                      class="inline-flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-xs font-medium text-white shadow-sm backdrop-blur-sm transition-colors duration-150 hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-200"
                    >
                      <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                      <span>{{ stageLabels[selectedStage] }}</span>
                      <ChevronDown class="h-3.5 w-3.5" />
                    </button>
                    <div
                      v-if="showStageDropdown"
                      class="absolute right-0 z-10 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
                    >
                      <div class="py-1">
                        <button
                          v-for="(label, key) in stageLabels"
                          :key="key"
                          @click="selectedStage = key as StageKey; showStageDropdown = false"
                          class="flex w-full items-start gap-2 px-4 py-2 text-left text-xs hover:bg-gray-50"
                          :class="{
                            'bg-blue-50 text-blue-700': selectedStage === (key as StageKey),
                            'text-gray-700': selectedStage !== (key as StageKey)
                          }"
                        >
                          <span
                            class="mt-1 inline-flex h-1.5 w-1.5 flex-shrink-0 rounded-full"
                            :class="selectedStage === (key as StageKey) ? 'bg-blue-500' : 'bg-gray-300'"
                          ></span>
                          <span>
                            <span class="block font-medium">{{ label }}</span>
                            <span class="block text-[11px] text-gray-500" v-if="key === 'final-top3'">
                              Show only the Top 3 finalists
                            </span>
                          </span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Paper Size Selector -->
                  <div class="relative">
                    <button
                      @click="showPaperSizeDropdown = !showPaperSizeDropdown"
                      class="inline-flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-xs font-medium text-white shadow-sm backdrop-blur-sm transition-colors duration-150 hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-200"
                    >
                      <span class="inline-flex h-1.5 w-1.5 rounded-full bg-sky-300"></span>
                      <span>{{ paperSizes[selectedPaperSize].name }}</span>
                      <ChevronDown class="h-3.5 w-3.5" />
                    </button>

                    <div
                      v-if="showPaperSizeDropdown"
                      class="absolute right-0 z-10 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
                    >
                      <div class="py-1">
                        <button
                          v-for="(paper, key) in paperSizes"
                          :key="key"
                          @click="selectedPaperSize = key; showPaperSizeDropdown = false"
                          class="flex w-full items-center justify-between px-4 py-2 text-left text-xs hover:bg-gray-50"
                          :class="{
                            'bg-blue-50 text-blue-700': selectedPaperSize === key,
                            'text-gray-700': selectedPaperSize !== key
                          }"
                        >
                          <span>{{ paper.name }}</span>
                          <span
                            class="inline-flex h-1.5 w-1.5 rounded-full"
                            :class="selectedPaperSize === key ? 'bg-blue-500' : 'bg-gray-300'"
                          ></span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Print Button -->
                  <button
                    @click="printResults"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-xs font-semibold text-blue-700 shadow-sm transition-colors duration-150 hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                  >
                    <Printer class="h-4 w-4" />
                    <span>Print Report</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Quick stats row -->
            <div class="border-t border-gray-100 bg-gray-50/60 px-6 py-3 text-xs text-gray-600 sm:px-8">
              <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex items-center gap-2">
                  <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                  <span class="font-medium">Stage:</span>
                  <span>{{ stageLabels[selectedStage] }}</span>
                </div>
                <div class="inline-flex items-center gap-2">
                  <span class="inline-flex h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                  <span class="font-medium">Contestants:</span>
                  <span>{{ resultsToShow.length }}</span>
                </div>
                <div class="inline-flex items-center gap-2">
                  <span class="inline-flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                  <span class="font-medium">Paper:</span>
                  <span>{{ paperSizes[selectedPaperSize].name }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Print Preview Card -->
          <div
            class="print-content overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200"
            ref="printArea"
          >
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Print preview</p>
                <p class="mt-0.5 text-sm font-medium text-gray-900">{{ reportTitle }}</p>
              </div>
              <div class="text-right text-xs text-gray-500">
                <p class="font-medium">{{ pageant.name }}</p>
                <p v-if="pageant.date">{{ pageant.date }}</p>
              </div>
            </div>
            <div class="bg-white px-3 pb-4 pt-3 sm:px-6 sm:pb-6 sm:pt-4">
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
import { Printer, LayoutDashboard, ChevronDown } from 'lucide-vue-next'
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
const showPaperSizeDropdown = ref(false)
type StageKey = 'overall' | 'semi-final' | 'final' | 'final-top3'
const selectedStage = ref<StageKey>('overall')
const showStageDropdown = ref(false)

const paperSizes = {
  letter: { name: 'Letter (8.5" × 11")', size: 'letter', margin: '0.5in' },
  a4: { name: 'A4 (210mm × 297mm)', size: 'A4', margin: '12mm' },
  legal: { name: 'Legal (8.5" × 14")', size: 'legal', margin: '0.5in' },
  oficio: { name: 'Oficio (8.5" × 13")', size: '8.5in 13in', margin: '0.5in' }
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