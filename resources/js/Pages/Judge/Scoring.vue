<template>
  <div class="space-y-8">
    <!-- Page Header with Round Selection -->
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 shadow-md rounded-xl overflow-hidden">
      <div class="p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white">Judge Scoring Panel</h1>
            <p class="text-amber-100 mt-1">Score contestants for the current event</p>
          </div>
          <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
            <span class="text-amber-50 font-medium px-3">Current Round:</span>
            <select
              v-model="currentRound"
              class="bg-white/20 text-white border-0 rounded-lg py-2 px-3 focus:bg-white/30 focus:ring-amber-400 focus:border-amber-400"
              :disabled="isLoading"
            >
              <option value="evening_gown">Evening Gown</option>
              <option value="swimsuit">Swimsuit</option>
              <option value="talent">Talent</option>
              <option value="qa">Q&A</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Scoring Criteria Overview Cards -->
    <div class="bg-white shadow-md rounded-xl border border-gray-100 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <Star class="h-5 w-5 text-amber-500 mr-2" />
        Scoring Criteria
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <template v-if="isLoading">
          <div v-for="i in 3" :key="i" class="relative overflow-hidden rounded-xl border border-amber-100 transition-all">
            <div class="absolute top-0 left-0 h-full w-1 bg-amber-200 shimmer shimmer-amber"></div>
            <div class="p-4 pl-5">
              <div class="h-5 w-32 bg-gray-200 rounded shimmer shimmer-amber mb-2"></div>
              <div class="h-4 w-full bg-gray-200 rounded shimmer shimmer-amber mb-3"></div>
              <div class="h-6 w-20 bg-gray-200 rounded-full shimmer shimmer-amber"></div>
            </div>
          </div>
        </template>
        <template v-else>
          <div 
            v-for="criterion in criteria" 
            :key="criterion.id" 
            class="relative overflow-hidden rounded-xl border border-amber-100 transition-all hover:shadow-md group"
          >
            <div class="absolute top-0 left-0 h-full w-1 bg-amber-500"></div>
            <div class="p-4 pl-5">
              <h3 class="font-medium text-gray-900 group-hover:text-amber-700 transition-colors">{{ criterion.name }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ criterion.description }}</p>
              <div class="mt-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                Weight: {{ criterion.weight }}%
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Contestant Scoring Cards -->
    <div class="space-y-6">
      <template v-if="isLoading">
        <div v-for="i in 3" :key="i" class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden">
          <!-- Skeleton Contestant Header -->
          <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
              <div class="relative">
                <div class="h-20 w-20 rounded-xl bg-gray-200 shimmer shimmer-amber"></div>
                <div class="absolute -bottom-3 -right-3 h-8 w-8 rounded-full bg-gray-200 shimmer shimmer-amber"></div>
              </div>
              <div class="space-y-2">
                <div class="h-6 w-40 bg-gray-200 rounded shimmer shimmer-amber"></div>
                <div class="h-4 w-32 bg-gray-200 rounded shimmer shimmer-amber"></div>
              </div>
              <div class="sm:ml-auto">
                <div class="bg-gray-100 rounded-lg px-4 py-2">
                  <div class="h-3 w-16 bg-gray-200 rounded shimmer shimmer-amber mb-1"></div>
                  <div class="h-7 w-10 bg-gray-200 rounded shimmer shimmer-amber"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Skeleton Scoring Form -->
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
              <div v-for="j in 6" :key="j" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 rounded-lg">
                <div class="space-y-1">
                  <div class="h-5 w-32 bg-gray-200 rounded shimmer shimmer-amber"></div>
                  <div class="h-3 w-20 bg-gray-200 rounded shimmer shimmer-amber"></div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="h-4 w-36 bg-gray-200 rounded shimmer shimmer-amber"></div>
                  <div class="h-8 w-16 bg-gray-200 rounded shimmer shimmer-amber"></div>
                </div>
              </div>
            </div>

            <!-- Skeleton Submit Button & Notes -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
              <div class="flex-grow">
                <div class="h-4 w-32 bg-gray-200 rounded shimmer shimmer-amber mb-2"></div>
                <div class="h-16 w-full bg-gray-200 rounded shimmer shimmer-amber"></div>
              </div>
              <div class="flex items-end">
                <div class="h-12 w-32 bg-gray-200 rounded shimmer shimmer-amber"></div>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template v-else>
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
                <p class="text-sm text-gray-500">{{ getContestantTitle(contestant.id) }}</p>
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
                  <div class="text-xs text-gray-500">Score 1-100</div>
                </div>
                <div class="flex items-center gap-3">
                  <input
                    type="range"
                    v-model="scores[`${contestant.id}-${criterion.id}`]"
                    min="1"
                    max="100"
                    class="w-28 sm:w-36 accent-amber-500"
                    @input="validateScore($event, contestant.id, criterion.id)"
                  />
                  <input
                    type="number"
                    v-model="scores[`${contestant.id}-${criterion.id}`]"
                    min="1"
                    max="100"
                    class="w-16 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-center"
                    @change="validateScore($event, contestant.id, criterion.id)"
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
                  :disabled="!isContestantScoreComplete(contestant.id)"
                  :class="{ 'opacity-50 cursor-not-allowed': !isContestantScoreComplete(contestant.id) }"
                >
                  <Save class="h-5 w-5" />
                  <span>Submit Scores</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>
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
import { Star, Save, CheckCircle } from 'lucide-vue-next'
import { useAuthStore } from '../../stores/auth'
import '../../components/skeletons/skeleton.css'
import JudgeLayout from '../../Layouts/JudgeLayout.vue'

defineOptions({
  layout: JudgeLayout
})

const authStore = useAuthStore()
const currentRound = ref('evening_gown')
const showConfirmation = ref(false)
const submittedContestantId = ref(null)
const isLoading = ref(true)

const criteria = ref([
  {
    id: 1,
    name: 'Beauty and Poise',
    description: 'Overall physical appearance, grace, and elegance in presentation',
    weight: 30
  },
  {
    id: 2,
    name: 'Stage Presence',
    description: 'Confidence, charisma, and ability to command attention',
    weight: 40
  },
  {
    id: 3,
    name: 'Overall Impact',
    description: 'The lasting impression and overall performance quality',
    weight: 30
  }
])

const contestants = ref([
  {
    id: 1,
    number: 1,
    name: 'Sarah Johnson',
    image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80',
    title: 'Miss California'
  },
  {
    id: 2,
    number: 2,
    name: 'Emily Davis',
    image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80',
    title: 'Miss New York'
  },
  {
    id: 3,
    number: 3,
    name: 'Maria Garcia',
    image: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&q=80',
    title: 'Miss Florida'
  }
])

const scores = ref({})
const notes = ref({})

const getContestantTitle = (contestantId) => {
  const contestant = contestants.value.find(c => c.id === contestantId)
  return contestant?.title || 'Contestant'
}

const validateScore = (event, contestantId, criterionId) => {
  const input = event.target
  let value = parseInt(input.value)
  
  if (value < 1) value = 1
  if (value > 100) value = 100
  
  scores.value[`${contestantId}-${criterionId}`] = value
}

const calculateAverage = (contestantId) => {
  const contestantScores = criteria.value.map(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] || 0
  )
  
  if (contestantScores.some(score => score === 0)) return '-'
  
  const weightedSum = contestantScores.reduce((sum, score, index) => 
    sum + (score * criteria.value[index].weight / 100), 0
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
  return criteria.value.every(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] !== undefined
  )
}

const submitScores = (contestantId) => {
  // Simulate API call to submit scores
  const contestantScores = {
    judgeId: authStore.user?.id,
    contestantId,
    round: currentRound.value,
    notes: notes.value[contestantId] || '',
    scores: criteria.value.map(criterion => ({
      criterionId: criterion.id,
      score: scores.value[`${contestantId}-${criterion.id}`]
    }))
  }
  
  console.log('Submitting scores:', contestantScores)
  // Here you would typically make an API call to save the scores
  
  // Show confirmation
  submittedContestantId.value = contestantId
  showConfirmation.value = true
}

const getSubmittedContestantName = () => {
  if (!submittedContestantId.value) return ''
  const contestant = contestants.value.find(c => c.id === submittedContestantId.value)
  return contestant?.name || ''
}

onMounted(() => {
  // Simulate data loading
  setTimeout(() => {
    isLoading.value = false
  }, 1500)
})
</script>