<template>
  <div class="print-container">
    <!-- Screen View -->
    <div class="screen-only bg-gray-50 min-h-screen py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-semibold text-gray-900">Print Minor Awards</h1>
            <p class="text-gray-600 mt-2">{{ pageant.name }} — Semi-Final Minor Awards</p>
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
              <div v-if="showPaperSizeDropdown" class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                <div class="py-1">
                  <button v-for="(paper, key) in paperSizes" :key="key"
                    @click="selectedPaperSize = key; showPaperSizeDropdown = false"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors duration-150"
                    :class="{ 'bg-blue-50 text-blue-700': selectedPaperSize === key, 'text-gray-700': selectedPaperSize !== key }"
                  >{{ paper.name }}</button>
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

        <div class="bg-white rounded-lg shadow-lg print-content" ref="printArea">
          <PrintableMinorAwards :pageant="pageant" :awards-by-round="awardsByRound" :judges="judges" />
        </div>
      </div>
    </div>

    <!-- Print-Only Content -->
    <div class="print-only">
      <PrintableMinorAwards :pageant="pageant" :awards-by-round="awardsByRound" :judges="judges" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Printer, ChevronDown } from 'lucide-vue-next'
import PrintableMinorAwards from '../../Components/tabulator/PrintableMinorAwards.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({ layout: TabulatorLayout })

interface PageantInfo {
  id: number
  name: string
  date?: string
  venue?: string
  location?: string
  logo?: string
}

interface JudgeInfo {
  id: number
  name: string
  role: string
}

interface RoundInfo {
  id: number
  name: string
  type?: string
  weight?: number
}

interface WinnerInfo {
  id: number
  number: number
  name: string
  image: string
  score: number
}

interface RoundEntry {
  round: RoundInfo
  winners: WinnerInfo[]
}

interface Props {
  pageant: PageantInfo
  awardsByRound: Record<string, RoundEntry>
  judges: JudgeInfo[]
}

const props = defineProps<Props>()

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
  const printContent = document.querySelector('.print-only')
  if (!printContent) return

  const paperConfig = paperSizes[selectedPaperSize.value]
  const stylesheets = Array.from(document.styleSheets)
    .map(sheet => {
      try {
        if (sheet.href) {
          return `<link rel="stylesheet" href="${sheet.href}">`
        } else if ((sheet as CSSStyleSheet).cssRules) {
          const rules = Array.from((sheet as CSSStyleSheet).cssRules).map(rule => rule.cssText).join('\n')
          return `<style>${rules}</style>`
        }
      } catch (e) {
        return ''
      }
      return ''
    })
    .filter(Boolean)
    .join('\n')

  const printWindow = window.open('', '_blank')
  if (!printWindow) return

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Minor Awards - ${paperConfig.name}</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      ${stylesheets}
      <style>
        @media print {
          @page { size: ${paperConfig.size}; margin: ${paperConfig.margin}; }
          .screen-only { display: none !important; }
          .print-only { display: block !important; width: 100%; max-width: none; }
        }
      </style>
    </head>
    <body>${(printContent as HTMLElement).innerHTML}</body>
    </html>
  `)
  printWindow.document.close()
  printWindow.onload = () => { printWindow.focus(); printWindow.print(); printWindow.close() }
}
</script>

<style scoped>
@media print {
  .screen-only { display: none !important; }
  .print-only { display: block !important; }
  .print-container { margin: 0; padding: 0; }
}
@media screen {
  .print-only { display: none !important; }
}
</style>


