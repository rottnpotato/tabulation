<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100 printableArea">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900 flex items-center">
            <Trophy class="h-6 w-6 text-amber-500 mr-2" />
            Final Results
          </h2>
          <p class="text-gray-600 mt-1">Miss Universe 2025 - Final Rankings and Scores</p>
        </div>
        <div class="flex space-x-2 mt-4 md:mt-0">
          <div class="relative">
            <select
              v-model="ActiveRound"
              class="rounded-lg border-gray-300 pr-10 focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="overall">Overall Results</option>
              <option value="evening_gown">Evening Gown</option>
              <option value="swimsuit">Swimsuit</option>
              <option value="qa">Q&A Round</option>
              <option value="talent">Talent</option>
            </select>
          </div>
          <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors shadow-sm"
          >
            <RefreshCw class="h-4 w-4 mr-2" />
            Recalculate
          </button>
        </div>
      </div>
      
      <div class="overflow-x-auto rounded-lg border border-gray-100 mb-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contestant</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evening Gown</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Swimsuit</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Q&A</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Talent</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Score</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(contestant, index) in SortedContestants" :key="contestant.id" class="hover:bg-blue-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center justify-center">
                  <div :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                    index === 0 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 
                    index === 1 ? 'bg-gray-100 text-gray-800 border border-gray-300' : 
                    index === 2 ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                    'bg-blue-50 text-blue-700 border border-blue-200'
                  ]">
                    {{ index + 1 }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img class="h-10 w-10 rounded-full object-cover" :src="contestant.image" :alt="contestant.name" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ contestant.name }}</div>
                    <div class="text-xs text-gray-500">{{ contestant.region }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <ScoreDisplay :score="contestant.scores.evening_gown" :active="ActiveRound === 'evening_gown'" />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <ScoreDisplay :score="contestant.scores.swimsuit" :active="ActiveRound === 'swimsuit'" />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <ScoreDisplay :score="contestant.scores.qa" :active="ActiveRound === 'qa'" />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <ScoreDisplay :score="contestant.scores.talent" :active="ActiveRound === 'talent'" />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold" :class="[
                  contestant.totalScore >= 90 ? 'text-green-600' : 
                  contestant.totalScore >= 80 ? 'text-blue-600' : 
                  'text-gray-900'
                ]">
                  {{ contestant.totalScore.toFixed(2) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="flex flex-col md:flex-row gap-4 justify-between">
        <div class="flex space-x-2">
          <button 
            @click="ExportResults('pdf')"
            class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 flex items-center"
          >
            <FileText class="h-4 w-4 mr-2" />
            Export PDF
          </button>
          <button 
            @click="ExportResults('excel')"
            class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 flex items-center"
          >
            <FileSpreadsheet class="h-4 w-4 mr-2" />
            Export Excel
          </button>
        </div>
        <button 
          @click="PrintResults"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 flex items-center justify-center"
        >
          <Printer class="h-4 w-4 mr-2" />
          Print Results
        </button>
      </div>
    </div>
    
    <!-- Results Visualization -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <BarChart3 class="h-5 w-5 text-blue-600 mr-2" />
          Score Breakdown
        </h3>
        <div class="h-80">
          <!-- This would be replaced with an actual chart component -->
          <div class="bg-gray-50 rounded-lg border border-gray-100 h-full flex items-center justify-center">
            <div class="text-center">
              <BarChart3 class="h-12 w-12 text-gray-400 mx-auto mb-2" />
              <p class="text-gray-500">Score breakdown chart would be rendered here</p>
              <p class="text-xs text-gray-400 mt-1">Implement with Chart.js or similar library</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <Medal class="h-5 w-5 text-blue-600 mr-2" />
          Top Performers by Category
        </h3>
        <div class="space-y-6">
          <div v-for="category in Categories" :key="category.id" class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-sm font-medium text-gray-700">{{ category.name }}</h4>
              <span class="text-xs text-gray-500">Top 3</span>
            </div>
            <div class="space-y-2">
              <div v-for="(leader, idx) in GetTopPerformers(category.id)" :key="leader.id" class="flex items-center justify-between">
                <div class="flex items-center">
                  <div :class="[
                    'flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold mr-2',
                    idx === 0 ? 'bg-amber-100 text-amber-800' : 
                    idx === 1 ? 'bg-gray-100 text-gray-800' : 
                    'bg-amber-50 text-amber-700'
                  ]">
                    {{ idx + 1 }}
                  </div>
                  <span class="text-sm font-medium text-gray-800">{{ leader.name }}</span>
                </div>
                <span class="text-sm font-semibold" :class="[
                  leader.score >= 90 ? 'text-green-600' : 
                  leader.score >= 80 ? 'text-blue-600' : 
                  'text-gray-900'
                ]">
                  {{ leader.score.toFixed(2) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Winner Spotlight -->
    <div v-if="SortedContestants.length > 0" class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg rounded-xl p-6 text-white">
      <div class="text-center mb-6">
        <div class="inline-block p-1 bg-white/10 backdrop-blur-sm rounded-full border border-white/20 mb-4">
          <Crown class="h-8 w-8 text-amber-300" />
        </div>
        <h2 class="text-2xl font-bold">Winner Announcement</h2>
        <p class="text-blue-100 mt-1">Miss Universe 2025</p>
      </div>
      
      <div class="flex flex-col md:flex-row items-center justify-center bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
        <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-8">
          <div class="h-32 w-32 md:h-40 md:w-40 mx-auto rounded-full border-4 border-white overflow-hidden shadow-xl">
            <img :src="SortedContestants[0].image" :alt="SortedContestants[0].name" class="h-full w-full object-cover" />
          </div>
        </div>
        <div class="text-center md:text-left">
          <h3 class="text-3xl font-bold">{{ SortedContestants[0].name }}</h3>
          <p class="text-xl text-blue-100 mt-1">{{ SortedContestants[0].region }}</p>
          <div class="mt-4 flex items-center justify-center md:justify-start">
            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-500/30 text-blue-50 border border-blue-400/30 mr-2">Total Score: {{ SortedContestants[0].totalScore.toFixed(2) }}</span>
            <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/30 text-amber-50 border border-amber-400/30">1st Place</span>
          </div>
          <p class="mt-4 text-blue-100 max-w-lg">
            Congratulations to our winner for demonstrating exceptional beauty, intelligence, talent, and grace throughout the competition!
          </p>
        </div>
      </div>
      
      <div class="flex justify-center mt-6">
        <button class="px-6 py-2 bg-white text-blue-700 rounded-lg shadow-sm hover:bg-blue-50 flex items-center transition-colors">
          <Award class="h-5 w-5 mr-2" />
          Proceed to Crowning Ceremony
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Trophy, RefreshCw, FileText, FileSpreadsheet, Printer, BarChart3, Medal, Crown, Award } from 'lucide-vue-next'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

// Define the ScoreDisplay component locally
const ScoreDisplay = {
  props: {
    score: { type: Number, required: true },
    active: { type: Boolean, default: false }
  },
  setup(props) {
    const getScoreClass = computed(() => {
      if (props.active) {
        if (props.score >= 90) return 'text-green-600 font-bold'
        if (props.score >= 80) return 'text-blue-600 font-semibold'
        return 'text-gray-900 font-semibold'
      } else {
        if (props.score >= 90) return 'text-green-600'
        if (props.score >= 80) return 'text-blue-600'
        return 'text-gray-900'
      }
    })
    
    return { getScoreClass }
  },
  template: `
    <div class="text-sm" :class="getScoreClass">
      {{ score.toFixed(2) }}
      <span v-if="active" class="ml-1 inline-block w-2 h-2 rounded-full bg-blue-500"></span>
    </div>
  `
}

// Mock data - would come from API or store in a real application
const Contestants = ref([
  {
    id: 1,
    name: 'Maria Garcia',
    region: 'Miss Florida',
    image: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 94.5,
      swimsuit: 95.2,
      qa: 92.8,
      talent: 96.7
    }
  },
  {
    id: 2,
    name: 'Sarah Johnson',
    region: 'Miss California',
    image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 93.7,
      swimsuit: 91.5,
      qa: 96.2,
      talent: 92.5
    }
  },
  {
    id: 3,
    name: 'Emily Davis',
    region: 'Miss New York',
    image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 92.1,
      swimsuit: 94.3,
      qa: 89.7,
      talent: 90.2
    }
  },
  {
    id: 4,
    name: 'Jasmine Williams',
    region: 'Miss Texas',
    image: 'https://images.unsplash.com/photo-1509967419530-da38b4704bc6?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 90.8,
      swimsuit: 92.7,
      qa: 88.4,
      talent: 91.3
    }
  },
  {
    id: 5,
    name: 'Sophia Martinez',
    region: 'Miss Illinois',
    image: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&q=80',
    scores: {
      evening_gown: 89.5,
      swimsuit: 90.1,
      qa: 93.5,
      talent: 88.9
    }
  }
])

const Categories = ref([
  { id: 'evening_gown', name: 'Evening Gown' },
  { id: 'swimsuit', name: 'Swimsuit' },
  { id: 'qa', name: 'Q&A Round' },
  { id: 'talent', name: 'Talent' }
])

const ActiveRound = ref('overall')

// Calculate total scores for each contestant
const ContestantsWithTotalScores = computed(() => {
  return Contestants.value.map(contestant => {
    const scores = contestant.scores
    const totalScore = (scores.evening_gown + scores.swimsuit + scores.qa + scores.talent) / 4
    return { ...contestant, totalScore }
  })
})

// Sort contestants by their total scores
const SortedContestants = computed(() => {
  return [...ContestantsWithTotalScores.value].sort((a, b) => b.totalScore - a.totalScore)
})

// Get top performers for a specific category
const GetTopPerformers = (categoryId: string) => {
  return [...Contestants.value]
    .sort((a, b) => b.scores[categoryId as keyof typeof b.scores] - a.scores[categoryId as keyof typeof a.scores])
    .slice(0, 3)
    .map(contestant => ({
      id: contestant.id,
      name: contestant.name,
      score: contestant.scores[categoryId as keyof typeof contestant.scores]
    }))
}

// Export results function (mock implementation)
const ExportResults = (format: 'pdf' | 'excel') => {
  console.log(`Exporting results as ${format}...`)
  // In a real application, this would trigger an API call or use a library
  alert(`Results would be exported as ${format} in a real application`)
}

// Print results function (mock implementation)
const PrintResults = () => {
  console.log('Printing results...')
  // In a real application, this would trigger the browser's print functionality
  window.print()
}
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  .printableArea, .printableArea * {
    visibility: visible;
  }
  .printableArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
