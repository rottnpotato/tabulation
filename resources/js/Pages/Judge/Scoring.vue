<template>
  <div class="h-screen bg-[#F8FAFC] flex flex-col overflow-hidden selection:bg-indigo-500 selection:text-white font-sans">
    <!-- Real-time Loading Overlay -->
    <div v-if="realtimeLoading" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center space-y-4 animate-in fade-in zoom-in duration-200">
        <div class="relative">
          <div class="w-12 h-12 border-4 border-indigo-100 rounded-full"></div>
          <div class="absolute top-0 left-0 w-12 h-12 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
        </div>
        <span class="text-slate-700 font-medium">Updating round information...</span>
      </div>
    </div>

    <!-- Header Section -->
    <div class="bg-white/80 backdrop-blur-xl border-b border-slate-200 z-30 shrink-0 sticky top-0 supports-[backdrop-filter]:bg-white/60">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-5">
        <!-- Left: Navigation & Context -->
        <div class="flex items-center gap-5 min-w-0">
          <Link :href="route('judge.dashboard')" 
            class="group flex items-center justify-center w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm shrink-0"
          >
            <ChevronLeft class="w-6 h-6 transition-transform group-hover:-translate-x-0.5" />
          </Link>
          
          <div class="flex flex-col min-w-0 justify-center">
            <div class="flex items-center gap-2 text-xs font-bold tracking-wider text-indigo-500 uppercase leading-none mb-1.5">
              <span>Judge Panel</span>
              <span class="w-1 h-1 rounded-full bg-indigo-300"></span>
              <span class="truncate max-w-[200px]">{{ pageant?.name }}</span>
            </div>
            <div class="flex items-center gap-3 min-w-0 leading-none">
              <h1 class="text-2xl font-black text-slate-900 truncate tracking-tight">
                {{ currentRound?.name || 'Loading...' }}
              </h1>
              <span v-if="currentRound?.identifier" class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-xs font-mono text-slate-600 font-bold">
                {{ currentRound.identifier }}
              </span>
            </div>
          </div>
        </div>

        <!-- Right: Status & Tools -->
        <div class="flex items-center gap-4 shrink-0">
          <!-- Round Status -->
          <div v-if="currentRound" class="hidden md:flex items-center">
            <div v-if="pageant.current_round_id === currentRound.id && !currentRound.is_locked" 
              class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 text-sm font-bold uppercase tracking-wider border border-emerald-100 shadow-sm">
              <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
              </span>
              Live
            </div>
            <div v-if="currentRound.is_locked" 
              class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-600 text-sm font-bold uppercase tracking-wider border border-slate-200 shadow-sm">
              <Lock class="w-4 h-4" />
              Locked
            </div>
          </div>

          <!-- Round Switcher -->
          <div v-if="rounds.length > 0" class="w-56 hidden md:block">
            <CustomSelect
              v-model="currentRoundId"
              :options="roundOptions"
              :disabled="isLoading || !isChannelReady"
              variant="indigo"
              placeholder="Switch Round"
              @change="handleRoundChange"
              class="shadow-sm"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Area (Split View) -->
    <div class="flex-1 flex overflow-hidden relative">
      
      <!-- Error State -->
      <div v-if="error" class="absolute inset-0 z-50 bg-slate-50 flex items-center justify-center p-8">
        <div class="bg-white border border-red-100 rounded-3xl p-8 text-center shadow-xl max-w-md">
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <AlertCircle class="h-8 w-8 text-red-600" />
          </div>
          <h3 class="text-lg font-bold text-red-900 mb-2">Unable to Load Scoring Interface</h3>
          <p class="text-red-600 mb-6">{{ error }}</p>
          <Link :href="route('judge.dashboard')" class="btn-primary">Return to Dashboard</Link>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="contestants.length === 0" class="absolute inset-0 z-50 bg-slate-50 flex items-center justify-center p-8">
        <div class="text-center">
          <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <Users class="w-10 h-10 text-slate-400" />
          </div>
          <h3 class="text-xl font-bold text-slate-900 mb-2">No Contestants Found</h3>
          <p class="text-slate-500">There are no contestants assigned to this round yet.</p>
        </div>
      </div>

      <!-- Content Split -->
      <template v-else>
        <!-- Left Panel: Contestant Visuals (Immersive) -->
        <div class="hidden lg:flex w-5/12 bg-slate-900 relative flex-col justify-end overflow-hidden group">
          <!-- Background Image with Blur -->
          <div class="absolute inset-0 transition-all duration-700 ease-in-out transform scale-105 group-hover:scale-100">
            <img :src="activeContestant.image" class="w-full h-full object-cover opacity-40 blur-sm" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/30"></div>
          </div>

          <!-- Main Content Container -->
          <div class="relative z-10 w-full h-full flex flex-col items-center justify-center p-4">
            
            <!-- Contestant Card -->
            <div class="relative w-full max-w-sm aspect-[3/4] rounded-[1.5rem] overflow-hidden shadow-2xl ring-1 ring-white/10 transition-all duration-500 transform hover:-translate-y-2 hover:shadow-indigo-500/20 group-hover:ring-white/20">
              <img :src="activeContestant.image" class="w-full h-full object-cover" />
              
              <!-- Glassmorphism Overlay -->
              <div class="absolute bottom-0 inset-x-0 p-4 bg-slate-900/60 backdrop-blur-xl border-t border-white/10">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <h2 class="text-xl font-bold text-white tracking-tight leading-tight mb-0.5 truncate">{{ activeContestant.name }}</h2>
                    <p class="text-indigo-100 font-medium flex items-center gap-1.5 text-xs truncate shadow-sm">
                      <MapPin class="w-3 h-3 shrink-0" />
                      {{ activeContestant.origin || 'Unknown Origin' }}
                    </p>
                  </div>
                  <div class="flex flex-col items-center justify-center bg-white/10 backdrop-blur-md rounded-lg p-2 min-w-[3rem] border border-white/10 shrink-0">
                    <span class="text-[9px] font-bold text-white/60 uppercase tracking-wider">No.</span>
                    <span class="text-lg font-black text-white leading-none">{{ activeContestant.number }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation Hints -->
            <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4 pointer-events-none">
              <button 
                @click="prevContestant" 
                :disabled="currentIndex === 0"
                class="p-3 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 text-white/50 hover:text-white hover:bg-white/10 hover:scale-110 transition-all pointer-events-auto disabled:opacity-0 cursor-pointer"
              >
                <ChevronLeft class="w-6 h-6" />
              </button>
              <button 
                @click="nextContestant" 
                :disabled="currentIndex === contestants.length - 1"
                class="p-3 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 text-white/50 hover:text-white hover:bg-white/10 hover:scale-110 transition-all pointer-events-auto disabled:opacity-0 cursor-pointer"
              >
                <ChevronRight class="w-6 h-6" />
              </button>
            </div>

          </div>

          <!-- Bottom Progress/Dots -->
          <div class="relative z-20 pb-8 px-12 w-full">
            <div class="flex items-center gap-2 justify-center">
              <button 
                v-for="(c, idx) in contestants" 
                :key="c.id"
                @click="currentIndex = idx"
                class="h-1.5 rounded-full transition-all duration-300"
                :class="idx === currentIndex ? 'bg-indigo-500 w-8' : 'bg-white/20 w-2 hover:bg-white/40'"
              ></button>
            </div>
          </div>
        </div>

        <!-- Right Panel: Scoring Mechanism -->
        <div class="w-full lg:w-7/12 flex flex-col bg-[#F8FAFC] h-full relative">
          <!-- Mobile Contestant Header -->
          <div class="lg:hidden bg-white border-b border-slate-200 p-4 flex items-center gap-4 shadow-sm z-20">
            <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-100 shrink-0 ring-2 ring-slate-100">
              <img :src="activeContestant.image" class="w-full h-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex justify-between items-start">
                <h2 class="text-lg font-bold text-slate-900 truncate">{{ activeContestant.name }}</h2>
                <span class="text-lg font-black text-indigo-600">#{{ activeContestant.number }}</span>
              </div>
              <p class="text-sm text-slate-500 truncate">{{ activeContestant.origin }}</p>
            </div>
          </div>

          <!-- Scrollable Scoring Area -->
          <div class="flex-1 overflow-y-auto scroll-smooth" id="scoring-container">
            <div class="max-w-3xl mx-auto p-6 md:p-8 lg:p-10 space-y-8">
              
              <!-- Locked Warning -->
              <div v-if="!canEditScores && currentRound" class="bg-amber-50 border border-amber-100 rounded-2xl p-4 flex items-start gap-3 animate-in fade-in slide-in-from-top-2">
                <Lock class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                <div>
                  <h3 class="text-sm font-bold text-amber-900">Round Locked</h3>
                  <p class="text-xs text-amber-700 mt-0.5">Scoring is disabled for this round.</p>
                </div>
              </div>

              <!-- Score Sheet Header -->
              <div class="flex items-end justify-between border-b border-slate-200 pb-6">
                <div>
                  <h3 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    Score Sheet
                  </h3>
                  <p class="text-slate-500 mt-1">Rate based on the criteria below.</p>
                </div>
                <div class="text-right bg-white px-5 py-3 rounded-2xl shadow-sm border border-slate-100">
                  <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Score</div>
                  <div class="text-4xl font-black tracking-tight tabular-nums leading-none" :class="getAverageScoreColor(currentAverage)">
                    {{ currentAverage }}
                  </div>
                </div>
              </div>

              <!-- Criteria Cards -->
              <div class="space-y-6">
                <div 
                  v-for="criterion in criteria" 
                  :key="criterion.id" 
                  class="group bg-white rounded-2xl p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-slate-100 transition-all hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)] hover:border-indigo-100 relative overflow-hidden"
                >
                  <!-- Progress Bar Background -->
                  <div class="absolute bottom-0 left-0 h-1 bg-indigo-500/10 w-full">
                    <div 
                      class="h-full bg-indigo-500 transition-all duration-500 ease-out"
                      :style="{ width: `${((scores[`${activeContestant.id}-${criterion.id}`] || 0) / criterion.max_score) * 100}%` }"
                    ></div>
                  </div>

                  <div class="flex flex-col gap-6 relative z-10">
                    <!-- Header -->
                    <div class="flex items-start justify-between">
                      <div>
                        <label class="font-bold text-slate-800 text-lg block group-hover:text-indigo-700 transition-colors">{{ criterion.name }}</label>
                        <div class="flex items-center gap-3 mt-1">
                          <span class="text-xs font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-500">Weight: {{ criterion.weight }}%</span>
                          <span class="text-xs font-medium text-slate-400">Range: {{ criterion.min_score }} - {{ criterion.max_score }}</span>
                        </div>
                      </div>
                      <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-indigo-600 tabular-nums tracking-tight">
                          {{ scores[`${activeContestant.id}-${criterion.id}`] || 0 }}
                        </span>
                        <span class="text-slate-400 font-medium text-sm">/ {{ criterion.max_score }}</span>
                      </div>
                    </div>
                    
                    <!-- Input Area -->
                    <div class="bg-slate-50 rounded-xl p-2">
                      <ScoreInput
                        :min="Number(criterion.min_score)"
                        :max="Number(criterion.max_score)"
                        :step="criterion.allow_decimals ? 0.1 : 1"
                        :allow-decimals="criterion.allow_decimals"
                        :decimal-places="criterion.decimal_places || 1"
                        :disabled="!canEditScores"
                        v-model="scores[`${activeContestant.id}-${criterion.id}`]"
                        @change="(val) => handleScoreChange(val, activeContestant.id, criterion.id, criterion)"
                        class="w-full"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notes Section -->
              <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <label class="flex items-center gap-2 text-sm font-bold text-slate-700 mb-3">
                  <span class="p-1 bg-indigo-50 rounded text-indigo-600"><Target class="w-4 h-4" /></span>
                  Judge's Notes
                  <span class="font-normal text-slate-400 ml-auto text-xs">Optional</span>
                </label>
                <textarea 
                  v-model="notes[activeContestant.id]" 
                  rows="3" 
                  :disabled="!canEditScores"
                  class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm resize-none transition-all shadow-sm placeholder:text-slate-400 p-4"
                  placeholder="Add private comments about this performance..."
                ></textarea>
              </div>

              <!-- Spacer -->
              <div class="h-32"></div>
            </div>
          </div>

          <!-- Sticky Bottom Action Bar -->
          <div class="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-xl border-t border-slate-200 py-4 px-6 z-30 shadow-[0_-4px_20px_-4px_rgba(0,0,0,0.05)]">
            <div class="max-w-3xl mx-auto flex items-center justify-between gap-4">
              <!-- Navigation -->
              <div class="flex items-center gap-3">
                <button 
                  @click="prevContestant" 
                  :disabled="currentIndex === 0"
                  class="p-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95 shadow-sm"
                  title="Previous Contestant"
                >
                  <ChevronLeft class="w-5 h-5" />
                </button>
                <span class="text-sm font-bold text-slate-400 tabular-nums hidden sm:inline-block min-w-[50px] text-center">
                  <span class="text-slate-900">{{ currentIndex + 1 }}</span> / {{ contestants.length }}
                </span>
                <button 
                  @click="nextContestant" 
                  :disabled="currentIndex === contestants.length - 1"
                  class="p-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95 shadow-sm"
                  title="Next Contestant"
                >
                  <ChevronRight class="w-5 h-5" />
                </button>
              </div>

              <!-- Submit Actions -->
              <div class="flex items-center gap-3">
                <button
                  @click="submitScores(activeContestant.id, true)"
                  class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-xl shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/30 transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none flex items-center gap-2 active:scale-95"
                  :disabled="!canEditScores || !isContestantScoreComplete(activeContestant.id) || submitLoading[activeContestant.id]"
                >
                  <span v-if="submitLoading[activeContestant.id]" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                  <span v-else>Submit Score</span>
                  <ChevronRight v-if="!submitLoading[activeContestant.id]" class="w-4 h-4 opacity-60" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Star, Save, CheckCircle, AlertCircle, Lock, MapPin, Target, Users, Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import CustomSelect from '../../Components/CustomSelect.vue'
import ScoreInput from '../../Components/ScoreInput.vue'
import JudgeLayout from '../../Layouts/JudgeLayout.vue'
import NotificationSystem from '../../Components/NotificationSystem.vue'
import axios from 'axios'

defineOptions({
  layout: JudgeLayout
})

const props = defineProps({
  pageant: { type: Object, default: null },
  rounds: { type: Array, default: () => [] },
  currentRound: { type: Object, default: null },
  contestants: { type: Array, default: () => [] },
  criteria: { type: Array, default: () => [] },
  existingScores: { type: Object, default: () => ({}) },
  existingNotes: { type: Object, default: () => ({}) },
  canEditScores: { type: Boolean, default: true },
  error: { type: String, default: null }
})

// State
const currentRoundId = ref(props.currentRound?.id?.toString())
const currentIndex = ref(0)
const submitLoading = ref({})
const notificationSystem = ref(null)
const realtimeLoading = ref(false)
const isChannelReady = ref(false)
let pageantChannel = null

const scores = ref({ ...props.existingScores })
const notes = ref({ ...props.existingNotes })

// Computed
const activeContestant = computed(() => props.contestants[currentIndex.value] || {})

const currentAverage = computed(() => {
  if (!activeContestant.value.id) return '-'
  return calculateAverage(activeContestant.value.id)
})

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name + (round.is_locked ? ' (Locked)' : '') + (props.pageant.current_round_id === round.id ? ' (Live)' : '')
  }))
})

// Methods
const handleRoundChange = (option) => {
  const roundId = parseInt(option.value)
  router.visit(route('judge.scoring', [props.pageant.id, roundId]))
}

const nextContestant = () => {
  if (currentIndex.value < props.contestants.length - 1) {
    currentIndex.value++
    scrollToTop()
  }
}

const prevContestant = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
    scrollToTop()
  }
}

const scrollToTop = () => {
  const container = document.getElementById('scoring-container')
  if (container) container.scrollTop = 0
}

const handleScoreChange = (value, contestantId, criterionId, criterion) => {
  try {
    let v = Number(value);
    if (Number.isNaN(v) || !Number.isFinite(v)) v = Number(criterion.min_score) || 0;
    
    const minScore = Number(criterion.min_score) || 0;
    const maxScore = Number(criterion.max_score) || 100;
    
    if (v < minScore) v = minScore;
    if (v > maxScore) v = maxScore;
    
    if (!criterion.allow_decimals) {
      v = Math.round(v);
    } else if (criterion.decimal_places > 0) {
      try {
        v = Number(v.toFixed(Math.min(criterion.decimal_places, 10)));
      } catch (error) {
        v = Math.round(v);
      }
    }
    scores.value[`${contestantId}-${criterionId}`] = v;
  } catch (error) {
    console.error('Error in handleScoreChange:', error);
    scores.value[`${contestantId}-${criterionId}`] = Number(criterion.min_score) || 0;
  }
}

const calculateAverage = (contestantId) => {
  const contestantScores = props.criteria.map(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] || 0
  )
  
  if (contestantScores.some(score => score === 0)) return '-'
  
  const totalWeight = props.criteria.reduce((sum, criterion) => {
    const weight = criterion.weight || 1;
    return sum + Math.max(weight, 0);
  }, 0);
  
  if (totalWeight === 0) return '-';
  
  const weightedSum = contestantScores.reduce((sum, score, index) => {
    const weight = props.criteria[index].weight || 1;
    const safeWeight = Math.max(weight, 0);
    return sum + (score * safeWeight / Math.max(totalWeight, 1));
  }, 0);
  
  try {
    return Number(weightedSum).toFixed(1);
  } catch (error) {
    return '-';
  }
}

const getAverageScoreColor = (score) => {
  if (score === '-') return 'text-slate-300'
  const numScore = parseFloat(score)
  if (numScore >= 90) return 'text-emerald-600'
  if (numScore >= 80) return 'text-indigo-600'
  if (numScore >= 70) return 'text-blue-600'
  if (numScore >= 60) return 'text-amber-600'
  return 'text-red-600'
}

const isContestantScoreComplete = (contestantId) => {
  return props.criteria.every(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] !== undefined && scores.value[`${contestantId}-${criterion.id}`] !== null
  )
}

const submitScores = async (contestantId, autoAdvance = true) => {
  if (submitLoading.value[contestantId]) return
  submitLoading.value[contestantId] = true
  
  try {
    const contestantScores = {}
    let hasInvalidScores = false;
    
    props.criteria.forEach(criterion => {
      const score = scores.value[`${contestantId}-${criterion.id}`];
      if (score === undefined || score === null) {
        hasInvalidScores = true;
        return;
      }
      const minScore = Number(criterion.min_score);
      const maxScore = Number(criterion.max_score);
      if (score < minScore || score > maxScore) {
        hasInvalidScores = true;
        return;
      }
      contestantScores[criterion.id] = score;
    });
    
    if (hasInvalidScores) throw new Error('Some scores are invalid. Please check your inputs.');
    
    const response = await axios.post(route('judge.scores.submit', [props.pageant.id, props.currentRound.id]), {
      contestant_id: contestantId,
      scores: contestantScores,
      notes: notes.value[contestantId] || ''
    })
    
    if (response.data.success) {
      if (notificationSystem.value) {
        notificationSystem.value.success(`Scores for ${activeContestant.value.name} saved!`, {
          title: 'Success',
          timeout: 2000
        })
      }
      
      if (autoAdvance && currentIndex.value < props.contestants.length - 1) {
        setTimeout(() => {
          nextContestant()
        }, 500)
      }
    } else {
      throw new Error(response.data.message || 'Failed to submit scores');
    }
  } catch (error) {
    console.error('Error submitting scores:', error);
    let errorMessage = 'Error submitting scores. Please try again.';
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || {};
      const firstError = Object.values(errors)[0];
      if (firstError) errorMessage = firstError[0] || errorMessage;
    } else if (error.response?.status === 403) {
      errorMessage = 'This round has been locked for editing.';
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    if (notificationSystem.value) {
      notificationSystem.value.error(errorMessage, { title: 'Submission Failed', timeout: 5000 });
    } else {
      alert(errorMessage);
    }
  } finally {
    submitLoading.value[contestantId] = false
  }
}

// Real-time updates
onMounted(() => {
  if (props.pageant) {
    pageantChannel = window.Echo.private(`pageant.${props.pageant.id}`)
      .subscribed(() => { isChannelReady.value = true })
      .listen('RoundUpdated', (e) => { handleRoundUpdate(e) })
  }
})

onUnmounted(() => {
  if (props.pageant) {
    window.Echo.leave(`pageant.${props.pageant.id}`)
  }
})

const handleRoundUpdate = (event) => {
  const { action, round_name, is_current } = event
  if (!notificationSystem.value) return

  switch (action) {
    case 'set_current':
      if (is_current) {
        notificationSystem.value.info(`Current round changed to: ${round_name}`, { title: 'Round Changed', timeout: 6000 })
        realtimeLoading.value = true
        setTimeout(() => {
          router.visit(route('judge.scoring', [props.pageant.id, event.round_id]), {
            preserveState: false,
            preserveScroll: true,
            onFinish: () => { realtimeLoading.value = false }
          })
        }, 1000)
      }
      break
    case 'locked':
    case 'unlocked':
      const type = action === 'locked' ? 'warning' : 'success'
      const title = action === 'locked' ? 'Round Locked' : 'Round Unlocked'
      notificationSystem.value[type](`Round "${round_name}" has been ${action}`, { title, timeout: 6000 })
      if (props.currentRound && props.currentRound.id === event.round_id) {
        realtimeLoading.value = true
        setTimeout(() => {
          router.visit(route('judge.scoring', [props.pageant.id, props.currentRound.id]), {
            preserveState: false,
            preserveScroll: true,
            only: ['currentRound', 'rounds', 'canEditScores'],
            onFinish: () => { realtimeLoading.value = false }
          })
        }, 2000)
      }
      break
  }
}
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>