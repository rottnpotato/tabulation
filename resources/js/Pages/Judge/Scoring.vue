<template>
  <div class="space-y-8">
    <!-- Page Header with Round Selection -->
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 shadow-md rounded-xl overflow-hidden">
      <div class="p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white">Judge Scoring Panel</h1>
            <p class="text-amber-100 mt-1" v-if="pageant">{{ pageant.name }}</p>
            <p class="text-amber-200 text-sm mt-1" v-if="currentRound">{{ currentRound.name }}</p>
          </div>
          <div v-if="rounds.length > 1" class="flex items-center bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
            <span class="text-amber-50 font-medium px-3">Current Round:</span>
            <div class="min-w-[160px]">
              <CustomSelect
                v-model="currentRoundId"
                :options="roundOptions"
                :disabled="isLoading"
                variant="amber"
                placeholder="Select Round"
                @change="handleRoundChange"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6">
      <div class="flex items-center">
        <AlertCircle class="h-6 w-6 text-red-600 mr-3" />
        <div>
          <h3 class="text-lg font-medium text-red-900">Unable to Load Scoring Interface</h3>
          <p class="text-red-700 mt-1">{{ error }}</p>
        </div>
      </div>
      <div class="mt-4">
        <Link 
          :href="route('judge.dashboard')"
          class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
        >
          Return to Dashboard
        </Link>
      </div>
    </div>

    <!-- Scoring Criteria Overview Cards -->
    <div v-else-if="criteria.length > 0" class="bg-white shadow-md rounded-xl border border-gray-100 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <Star class="h-5 w-5 text-amber-500 mr-2" />
        Scoring Criteria
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div 
          v-for="criterion in criteria" 
          :key="criterion.id" 
          class="relative overflow-hidden rounded-xl border border-amber-100 transition-all hover:shadow-md group"
        >
          <div class="absolute top-0 left-0 h-full w-1 bg-amber-500"></div>
          <div class="p-4 pl-5">
            <h3 class="font-medium text-gray-900 group-hover:text-amber-700 transition-colors">{{ criterion.name }}</h3>
            <p class="text-sm text-gray-600 mt-1">{{ criterion.description }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
              <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                Weight: {{ criterion.weight }}%
              </div>
              <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ criterion.min_score }}-{{ criterion.max_score }} pts
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contestant Scoring Cards -->
    <div v-else-if="contestants.length > 0" class="space-y-6">
      <div v-for="contestant in contestants" :key="contestant.id" class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg transition-all">
        <!-- Contestant Header -->
        <div class="p-6 border-b border-gray-100">
          <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="relative">
              <img
                :src="contestant.image"
                :alt="contestant.name"
                class="h-20 w-20 rounded-xl object-cover"
              />
              <div class="absolute -bottom-3 -right-3 bg-amber-500 text-white text-xs font-bold h-8 w-8 rounded-full flex items-center justify-center shadow-md">
                #{{ contestant.number }}
              </div>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-gray-900">{{ contestant.name }}</h3>
              <p class="text-sm text-gray-500" v-if="contestant.origin">{{ contestant.origin }}</p>
            </div>
            <div class="sm:ml-auto flex items-center">
              <div class="bg-gray-100 rounded-lg px-4 py-2">
                <div class="text-xs text-gray-500">Average Score</div>
                <div class="text-xl font-bold" :class="getAverageScoreColor(calculateAverage(contestant.id))">
                  {{ calculateAverage(contestant.id) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Scoring Form -->
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
            <div 
              v-for="criterion in criteria" 
              :key="criterion.id" 
              class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 rounded-lg hover:bg-amber-50 transition-colors"
            >
              <div>
                <div class="font-medium text-gray-900">{{ criterion.name }}</div>
                <div class="text-xs text-gray-500">Score {{ criterion.min_score }}-{{ criterion.max_score }}</div>
              </div>
              <div class="flex items-center gap-3">
                <input
                  type="range"
                  v-model="scores[`${contestant.id}-${criterion.id}`]"
                  :min="criterion.min_score"
                  :max="criterion.max_score"
                  :step="criterion.allow_decimals ? 0.1 : 1"
                  class="w-28 sm:w-36 accent-amber-500"
                  @input="validateScore($event, contestant.id, criterion.id, criterion)"
                />
                <input
                  type="number"
                  v-model="scores[`${contestant.id}-${criterion.id}`]"
                  :min="criterion.min_score"
                  :max="criterion.max_score"
                  :step="criterion.allow_decimals ? 0.1 : 1"
                  class="w-16 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-center"
                  @change="validateScore($event, contestant.id, criterion.id, criterion)"
                />
              </div>
            </div>
          </div>

          <!-- Submit Button & Notes -->
          <div class="mt-8 flex flex-col sm:flex-row gap-4">
            <div class="flex-grow">
              <label class="block text-sm font-medium text-gray-700 mb-1">Scoring Notes (Optional)</label>
              <textarea 
                v-model="notes[contestant.id]" 
                rows="2" 
                class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm resize-none"
                placeholder="Add any comments or observations about this contestant's performance..."
              ></textarea>
            </div>
            <div class="flex items-end">
              <button
                @click="submitScores(contestant.id)"
                class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 shadow-sm hover:shadow transition-all"
                :disabled="!isContestantScoreComplete(contestant.id) || submitLoading"
                :class="{ 'opacity-50 cursor-not-allowed': !isContestantScoreComplete(contestant.id) || submitLoading }"
              >
                <Save class="h-5 w-5" />
                <span>{{ submitLoading ? 'Submitting...' : 'Submit Scores' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showConfirmation" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 transform transition-all">
          <div class="text-center mb-6">
            <div class="bg-amber-100 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
              <CheckCircle class="h-8 w-8 text-amber-600" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Scores Submitted Successfully</h3>
            <p class="text-sm text-gray-500 mt-2">Your scores for {{ getSubmittedContestantName() }} have been recorded.</p>
          </div>
          <div class="flex justify-center">
            <button 
              @click="showConfirmation = false" 
              class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg transition-colors"
            >
              Continue Scoring
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Star, Save, CheckCircle, AlertCircle } from 'lucide-vue-next'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import CustomSelect from '../../Components/CustomSelect.vue'
import '../../components/skeletons/skeleton.css'
import JudgeLayout from '../../Layouts/JudgeLayout.vue'
import axios from 'axios'

defineOptions({
  layout: JudgeLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    default: null
  },
  rounds: {
    type: Array,
    default: () => []
  },
  currentRound: {
    type: Object,
    default: null
  },
  contestants: {
    type: Array,
    default: () => []
  },
  criteria: {
    type: Array,
    default: () => []
  },
  existingScores: {
    type: Object,
    default: () => ({})
  },
  existingNotes: {
    type: Object,
    default: () => ({})
  },
  error: {
    type: String,
    default: null
  }
})

const currentRoundId = ref(props.currentRound?.id)
const showConfirmation = ref(false)
const submittedContestantId = ref(null)
const isLoading = ref(false)
const submitLoading = ref(false)

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name
  }))
})

const scores = ref({ ...props.existingScores })
const notes = ref({ ...props.existingNotes })

const handleRoundChange = (value) => {
  const roundId = parseInt(value)
  router.visit(route('judge.scoring', [props.pageant.id, roundId]))
}

const validateScore = (event, contestantId, criterionId, criterion) => {
  const input = event.target
  let value = parseFloat(input.value)
  
  if (value < criterion.min_score) value = criterion.min_score
  if (value > criterion.max_score) value = criterion.max_score
  
  // Handle decimal places
  if (!criterion.allow_decimals) {
    value = Math.round(value)
  } else if (criterion.decimal_places) {
    value = parseFloat(value.toFixed(criterion.decimal_places))
  }
  
  scores.value[`${contestantId}-${criterionId}`] = value
}

const calculateAverage = (contestantId) => {
  const contestantScores = props.criteria.map(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] || 0
  )
  
  if (contestantScores.some(score => score === 0)) return '-'
  
  const weightedSum = contestantScores.reduce((sum, score, index) => 
    sum + (score * props.criteria[index].weight / 100), 0
  )
  
  return weightedSum.toFixed(1)
}

const getAverageScoreColor = (score) => {
  if (score === '-') return 'text-gray-400'
  
  const numScore = parseFloat(score)
  if (numScore >= 90) return 'text-emerald-600'
  if (numScore >= 80) return 'text-teal-600'
  if (numScore >= 70) return 'text-amber-600'
  if (numScore >= 60) return 'text-orange-600'
  return 'text-red-600'
}

const isContestantScoreComplete = (contestantId) => {
  return props.criteria.every(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] !== undefined && scores.value[`${contestantId}-${criterion.id}`] !== null
  )
}

const submitScores = async (contestantId) => {
  if (submitLoading.value) return
  
  submitLoading.value = true
  
  try {
    const contestantScores = {}
    props.criteria.forEach(criterion => {
      contestantScores[criterion.id] = scores.value[`${contestantId}-${criterion.id}`]
    })
    
    const response = await axios.post(route('judge.scores.submit', [props.pageant.id, props.currentRound.id]), {
      contestant_id: contestantId,
      scores: contestantScores,
      notes: notes.value[contestantId] || ''
    })
    
    if (response.data.success) {
      submittedContestantId.value = contestantId
      showConfirmation.value = true
    }
  } catch (error) {
    console.error('Error submitting scores:', error)
    // Handle error (show toast notification, etc.)
    alert('Error submitting scores. Please try again.')
  } finally {
    submitLoading.value = false
  }
}

const getSubmittedContestantName = () => {
  if (!submittedContestantId.value) return ''
  const contestant = props.contestants.find(c => c.id === submittedContestantId.value)
  return contestant?.name || ''
}
</script>