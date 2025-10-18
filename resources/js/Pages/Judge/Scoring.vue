<template>
  <div class="space-y-8 relative">
    <!-- Real-time Loading Overlay -->
    <div v-if="realtimeLoading" class="fixed inset-0 bg-black bg-opacity-20 flex items-center justify-center z-40">
      <div class="bg-white rounded-lg shadow-xl p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-amber-600"></div>
        <span class="text-gray-900 font-medium">Updating round information...</span>
      </div>
    </div>

    <!-- Page Header with Round Selection (sticky) -->
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 shadow-md rounded-xl overflow-visible sticky top-3 z-30">
      <div class="p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white">Judge Scoring Panel</h1>
            <p class="text-amber-100 mt-1" v-if="pageant">{{ pageant.name }}</p>
            <div v-if="currentRound" class="flex items-center gap-2 mt-1">
              <p class="text-amber-200 text-sm">
                {{ currentRound.name }}
                <span v-if="currentRound.identifier" class="font-mono ml-1">[{{ currentRound.identifier }}]</span>
              </p>
              <div v-if="pageant.current_round_id === currentRound.id && !currentRound.is_locked" class="inline-flex items-center px-2 py-0.5 bg-amber-400 text-amber-900 text-xs font-medium rounded-full">
                Current Round
              </div>
              <div v-if="currentRound.is_locked" class="inline-flex items-center px-2 py-0.5 bg-red-500 text-white text-xs font-medium rounded-full">
                🔒 Locked
              </div>
            </div>
          </div>
          <div v-if="rounds.length > 0" class="flex items-center bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
            <span class="text-amber-50 font-medium px-3">Switch Round:</span>
            <div class="min-w-[200px] relative">
              <CustomSelect
                v-model="currentRoundId"
                :options="roundOptions"
                :disabled="isLoading || !isChannelReady"
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

    <!-- Round Locked Warning -->
    <div v-else-if="!canEditScores && currentRound" class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
      <div class="flex items-center">
        <AlertCircle class="h-6 w-6 text-yellow-600 mr-3" />
        <div>
          <h3 class="text-lg font-medium text-yellow-900">Round Locked for Editing</h3>
          <p class="text-yellow-700 mt-1">
            The "{{ currentRound.name }}" round has been locked by the tabulator. 
            You can view existing scores but cannot make changes.
            <span v-if="currentRound.locked_by">
              Locked by {{ currentRound.locked_by.name }}.
            </span>
          </p>
        </div>
      </div>
    </div>

    <!-- Scoring Criteria Overview Cards -->
    <div v-if="criteria.length > 0 && contestants.length === 0" class="bg-white shadow-md rounded-xl border border-gray-100 p-6">
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
      <!-- Show criteria overview at the top when we have both criteria and contestants -->
      <div v-if="criteria.length > 0" class="bg-white shadow-md rounded-xl border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <Star class="h-5 w-5 text-amber-500 mr-2" />
          Scoring Criteria
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
  <div v-for="contestant in contestants" :key="contestant.id" class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg transition-all">
        <!-- Contestant Header -->
        <div class="p-6 border-b border-gray-100 cursor-pointer hover:bg-amber-50/40 transition-colors" @click="openContestantDetails(contestant)">
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
              <p v-if="contestant.is_pair && contestant.members_text" class="text-xs text-gray-500">{{ contestant.members_text }}</p>
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
              class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-lg hover:bg-amber-50 transition-colors border border-transparent hover:border-amber-100"
            >
              <div>
                <div class="font-medium text-gray-900">{{ criterion.name }}</div>
                <div class="text-xs text-gray-500">Score {{ criterion.min_score }}-{{ criterion.max_score }}</div>
              </div>
              <ScoreInput
                :min="Number(criterion.min_score)"
                :max="Number(criterion.max_score)"
                :step="criterion.allow_decimals ? 0.1 : 1"
                :allow-decimals="criterion.allow_decimals"
                :decimal-places="criterion.decimal_places || 1"
                :disabled="!canEditScores"
                v-model="scores[`${contestant.id}-${criterion.id}`]"
                @change="(val) => handleScoreChange(val, contestant.id, criterion.id, criterion)"
              />
            </div>
          </div>

          <!-- Submit Button & Notes -->
          <div class="mt-8 flex flex-col sm:flex-row gap-4">
            <div class="flex-grow">
              <label class="block text-sm font-medium text-gray-700 mb-1">Scoring Notes (Optional)</label>
              <textarea 
                v-model="notes[contestant.id]" 
                rows="2" 
                :disabled="!canEditScores"
                class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm resize-none disabled:opacity-50 disabled:bg-gray-100"
                placeholder="Add any comments or observations about this contestant's performance..."
              ></textarea>
            </div>
            <div class="flex items-end">
              <button
                @click="submitScores(contestant.id)"
                class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 shadow-sm hover:shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="!canEditScores || !isContestantScoreComplete(contestant.id) || submitLoading[contestant.id]"
              >
                <Save class="h-5 w-5" />
                <span v-if="!canEditScores">Round Locked</span>
                <span v-else>{{ submitLoading[contestant.id] ? 'Submitting...' : 'Submit Scores' }}</span>
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

    <!-- Contestant Details Modal -->
    <ContestantDetailModal
      v-if="showDetailModal"
      :show="showDetailModal"
      :contestant="selectedContestant"
      @close="showDetailModal = false"
    >
      <template #extra>
        <div class="bg-white rounded-xl border border-amber-100 p-4">
          <div class="flex items-center mb-3">
            <GitCompare class="h-5 w-5 text-amber-600 mr-2" />
            <h4 class="text-sm font-semibold text-gray-800">Round Comparison</h4>
          </div>
          <div v-if="detailLoading" class="text-sm text-gray-500">Loading comparison...</div>
          <div v-else>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="rounded-lg border border-gray-200 p-3">
                <div class="text-xs text-gray-500 mb-1">Current Round</div>
                <div class="text-sm font-medium text-gray-900">{{ currentRound?.name }}</div>
                <div class="mt-2 text-sm" v-if="comparison.current">
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Your Score</span>
                    <span class="font-semibold">{{ formatScore(comparison.current.subject.score) }}</span>
                  </div>
                  <div class="flex items-center justify-between mt-1">
                    <span class="text-gray-600">Position</span>
                    <span class="font-semibold">{{ comparison.current.subject.position ? `#${comparison.current.subject.position}` : '-' }}</span>
                  </div>
                </div>
                <div v-else class="text-xs text-gray-500">No scores yet.</div>
              </div>
              <div class="rounded-lg border border-gray-200 p-3">
                <div class="text-xs text-gray-500 mb-1">Previous Round</div>
                <div class="text-sm font-medium text-gray-900">{{ comparison.previous?.round?.name || 'None' }}</div>
                <div class="mt-2 text-sm" v-if="comparison.previous">
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Your Score</span>
                    <span class="font-semibold">{{ formatScore(comparison.previous.subject.score) }}</span>
                  </div>
                  <div class="flex items-center justify-between mt-1">
                    <span class="text-gray-600">Position</span>
                    <span class="font-semibold">{{ comparison.previous.subject.position ? `#${comparison.previous.subject.position}` : '-' }}</span>
                  </div>
                </div>
                <div v-else class="text-xs text-gray-500">No previous round.</div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </ContestantDetailModal>

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Star, Save, CheckCircle, AlertCircle, GitCompare } from 'lucide-vue-next'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import CustomSelect from '../../Components/CustomSelect.vue'
import ScoreInput from '../../Components/ScoreInput.vue'
import '../../components/skeletons/skeleton.css'
import JudgeLayout from '../../Layouts/JudgeLayout.vue'
import ContestantDetailModal from '../../Components/ContestantDetailModal.vue'
import NotificationSystem from '../../Components/NotificationSystem.vue'
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
  canEditScores: {
    type: Boolean,
    default: true
  },
  error: {
    type: String,
    default: null
  }
})

const currentRoundId = ref(props.currentRound?.id?.toString())
const showConfirmation = ref(false)
const submittedContestantId = ref(null)
const isLoading = ref(false)
const submitLoading = ref({})
const showDetailModal = ref(false)
const selectedContestant = ref(null)
const detailLoading = ref(false)
const comparison = ref({ current: null, previous: null })
const notificationSystem = ref(null)
const realtimeLoading = ref(false)
const isChannelReady = ref(false)
let pageantChannel = null

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name + (round.is_locked ? ' (Locked)' : '') + (props.pageant.current_round_id === round.id ? ' (Current)' : '')
  }))
})

const scores = ref({ ...props.existingScores })
const notes = ref({ ...props.existingNotes })

const handleRoundChange = (option) => {
  const roundId = parseInt(option.value)
  router.visit(route('judge.scoring', [props.pageant.id, roundId]))
}

const handleScoreChange = (value, contestantId, criterionId, criterion) => {
  try {
    let v = Number(value);
    
    // Handle invalid numbers
    if (Number.isNaN(v) || !Number.isFinite(v)) {
      console.warn('Invalid score value provided:', value);
      v = Number(criterion.min_score) || 0;
    }
    
    // Range validation with safety checks
    const minScore = Number(criterion.min_score) || 0;
    const maxScore = Number(criterion.max_score) || 100;
    
    if (v < minScore) v = minScore;
    if (v > maxScore) v = maxScore;
    
    // Decimal handling with error protection
    if (!criterion.allow_decimals) {
      v = Math.round(v);
    } else if (criterion.decimal_places > 0) {
      try {
        v = Number(v.toFixed(Math.min(criterion.decimal_places, 10))); // Cap at 10 decimal places for safety
      } catch (error) {
        console.error('Error formatting decimal places:', error);
        v = Math.round(v); // Fallback to integer
      }
    }
    
    // Final safety check
    if (!Number.isFinite(v)) {
      console.error('Final score value is not finite:', v);
      v = Number(minScore);
    }
    
    scores.value[`${contestantId}-${criterionId}`] = v;
    
  } catch (error) {
    console.error('Error in handleScoreChange:', error, { 
      value, 
      contestantId, 
      criterionId, 
      criterion 
    });
    
    // Fallback to minimum score on any error
    scores.value[`${contestantId}-${criterionId}`] = Number(criterion.min_score) || 0;
  }
}

const calculateAverage = (contestantId) => {
  const contestantScores = props.criteria.map(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] || 0
  )
  
  if (contestantScores.some(score => score === 0)) return '-'
  
  // Defensive programming for weight calculation
  const totalWeight = props.criteria.reduce((sum, criterion) => {
    const weight = criterion.weight || 1; // Default weight
    return sum + Math.max(weight, 0); // Ensure positive weight
  }, 0);
  
  if (totalWeight === 0) {
    console.warn('Total criteria weight is zero for contestant', contestantId);
    return '-';
  }
  
  const weightedSum = contestantScores.reduce((sum, score, index) => {
    const weight = props.criteria[index].weight || 1;
    const safeWeight = Math.max(weight, 0); // Ensure positive weight
    return sum + (score * safeWeight / Math.max(totalWeight, 1));
  }, 0);
  
  // Defensive rounding
  try {
    return Number(weightedSum).toFixed(1);
  } catch (error) {
    console.error('Error calculating weighted average:', error, { contestantId, weightedSum });
    return '-';
  }
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
  if (submitLoading.value[contestantId]) return

  submitLoading.value[contestantId] = true
  
  try {
    const contestantScores = {}
    
    // Validate scores before submission
    let hasInvalidScores = false;
    props.criteria.forEach(criterion => {
      const score = scores.value[`${contestantId}-${criterion.id}`];
      
      // Validate score exists and is valid
      if (score === undefined || score === null) {
        console.error(`Invalid score for criterion ${criterion.id}:`, score);
        hasInvalidScores = true;
        return;
      }
      
      // Validate score is within range
      const minScore = Number(criterion.min_score);
      const maxScore = Number(criterion.max_score);
      if (score < minScore || score > maxScore) {
        console.error(`Score ${score} out of range for criterion ${criterion.id} (${minScore}-${maxScore})`);
        hasInvalidScores = true;
        return;
      }
      
      contestantScores[criterion.id] = score;
    });
    
    if (hasInvalidScores) {
      throw new Error('Some scores are invalid. Please check your inputs.');
    }
    
    const response = await axios.post(route('judge.scores.submit', [props.pageant.id, props.currentRound.id]), {
      contestant_id: contestantId,
      scores: contestantScores,
      notes: notes.value[contestantId] || ''
    })
    
    if (response.data.success) {
      submittedContestantId.value = contestantId
      showConfirmation.value = true
      
      // Show real-time update notification
      if (notificationSystem.value) {
        notificationSystem.value.success('Scores submitted successfully! Other judges and tabulators will see your update in real-time.', {
          title: 'Scores Submitted',
          timeout: 5000
        })
      }
    } else {
      throw new Error(response.data.message || 'Failed to submit scores');
    }
  } catch (error) {
    console.error('Error submitting scores:', error);
    
    let errorMessage = 'Error submitting scores. Please try again.';
    
    // Handle specific error types
    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors || {};
      const firstError = Object.values(errors)[0];
      if (firstError) {
        errorMessage = firstError[0] || error.response.data.message || errorMessage;
      }
    } else if (error.response?.status === 403) {
      errorMessage = 'This round has been locked for editing.';
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    // Show error notification
    if (notificationSystem.value) {
      notificationSystem.value.error(errorMessage, {
        title: 'Submission Failed',
        timeout: 8000
      });
    } else {
      alert(errorMessage);
    }
  } finally {
    submitLoading.value[contestantId] = false
  }
}

const getSubmittedContestantName = () => {
  if (!submittedContestantId.value) return ''
  const contestant = props.contestants.find(c => c.id === submittedContestantId.value)
  return contestant?.name || ''
}

const openContestantDetails = async (contestant) => {
  try {
    detailLoading.value = true
    showDetailModal.value = true
    // Fetch full contestant details
    const detailRes = await axios.get(route('judge.contestants.details', [props.pageant.id, contestant.id]))
    selectedContestant.value = detailRes.data
    // Fetch comparison for current vs previous round
    const compRes = await axios.get(route('judge.rounds.comparison', [props.pageant.id, props.currentRound.id]), {
      params: { contestant_id: contestant.id }
    })
    comparison.value = compRes.data
  } catch (e) {
    // Fallback to minimal data
    selectedContestant.value = contestant
  } finally {
    detailLoading.value = false
  }
}

const formatScore = (val) => {
  if (val === null || val === undefined) return '-'
  const num = Number(val)
  return isNaN(num) ? '-' : num.toFixed(2)
}

// Real-time event listeners
onMounted(() => {
  if (props.pageant) {
    console.log('Judge subscribing to pageant channel:', `pageant.${props.pageant.id}`)
    // Listen for round updates
    pageantChannel = window.Echo.private(`pageant.${props.pageant.id}`)
      .subscribed(() => {
        isChannelReady.value = true
      })
      .listen('RoundUpdated', (e) => {
        console.log('Judge received RoundUpdated event:', e)
        handleRoundUpdate(e)
      })
  }
})

onUnmounted(() => {
  if (props.pageant) {
    window.Echo.leave(`pageant.${props.pageant.id}`)
  }
})

const handleRoundUpdate = (event) => {
  console.log('RoundUpdated event received:', event)
  const { action, round_name, is_locked, is_current, message } = event

  // Show notification based on action
  if (notificationSystem.value) {
    switch (action) {
      case 'set_current':
        if (is_current) {
          notificationSystem.value.info(`Current round changed to: ${round_name}`, {
            title: 'Round Changed',
            timeout: 6000
          })
          // Show loading state and use Inertia visit to refresh data
          realtimeLoading.value = true
          setTimeout(() => {
            router.visit(route('judge.scoring', [props.pageant.id, event.round_id]), {
              preserveState: false,
              preserveScroll: true,
              only: ['currentRound', 'rounds', 'contestants', 'criteria', 'existingScores', 'existingNotes', 'canEditScores'],
              onFinish: () => {
                realtimeLoading.value = false
              }
            })
          }, 1000)
        }
        break
      
      case 'locked':
        notificationSystem.value.warning(`Round "${round_name}" has been locked for editing`, {
          title: 'Round Locked',
          timeout: 8000
        })
        // If this is the current round being viewed, refresh to update UI
        if (props.currentRound && props.currentRound.id === event.round_id) {
          realtimeLoading.value = true
          setTimeout(() => {
            router.visit(route('judge.scoring', [props.pageant.id, props.currentRound.id]), {
              preserveState: false,
              preserveScroll: true,
              only: ['currentRound', 'rounds', 'canEditScores'],
              onFinish: () => {
                realtimeLoading.value = false
              }
            })
          }, 2000)
        }
        break
      
      case 'unlocked':
        notificationSystem.value.success(`Round "${round_name}" has been unlocked for editing`, {
          title: 'Round Unlocked',
          timeout: 6000
        })
        // If this is the current round being viewed, refresh to update UI
        if (props.currentRound && props.currentRound.id === event.round_id) {
          realtimeLoading.value = true
          setTimeout(() => {
            router.visit(route('judge.scoring', [props.pageant.id, props.currentRound.id]), {
              preserveState: false,
              preserveScroll: true,
              only: ['currentRound', 'rounds', 'canEditScores'],
              onFinish: () => {
                realtimeLoading.value = false
              }
            })
          }, 2000)
        }
        break
    }
  }
}
</script>