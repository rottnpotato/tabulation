<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100 text-slate-800 font-sans selection:bg-teal-500 selection:text-white">
    
    <!-- Real-time Loading Overlay -->
    <div v-if="realtimeLoading" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm flex items-center justify-center z-[100]">
      <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center space-y-4 animate-in fade-in zoom-in duration-200">
        <div class="relative">
          <div class="w-12 h-12 border-4 border-teal-100 rounded-full"></div>
          <div class="absolute top-0 left-0 w-12 h-12 border-4 border-teal-600 rounded-full border-t-transparent animate-spin"></div>
        </div>
        <span class="text-slate-700 font-medium">Updating round information...</span>
      </div>
    </div>

    <!-- Completed Pageant Banner -->
    <div v-if="pageant?.is_completed" class="fixed top-0 inset-x-0 z-50 bg-amber-50 border-b border-amber-200 px-4 py-2">
      <div class="max-w-7xl mx-auto flex items-center justify-center gap-3">
        <div class="flex items-center gap-2 text-amber-800">
          <AlertCircle class="h-4 w-4" />
          <span class="font-semibold text-sm">This pageant has been completed.</span>
        </div>
        <span class="text-amber-700 text-xs">View only mode.</span>
      </div>
    </div>

    <!-- Scoring Not Yet Open Banner -->
    <div v-else-if="pageant?.scoring_status === 'not_started' && pageant?.start_date" class="fixed top-0 inset-x-0 z-50 bg-sky-50 border-b border-sky-200 px-4 py-2">
      <div class="max-w-7xl mx-auto flex items-center justify-center gap-3">
        <div class="flex items-center gap-2 text-sky-800">
          <Calendar class="h-4 w-4" />
          <span class="font-semibold text-sm">Scoring opens on {{ pageant.start_date }}{{ pageant.start_time ? ` at ${formatTime(pageant.start_time)}` : '' }}</span>
        </div>
      </div>
    </div>

    <!-- Scoring Period Ended Banner -->
    <div v-else-if="pageant?.scoring_status === 'ended'" class="fixed top-0 inset-x-0 z-50 bg-amber-50 border-b border-amber-200 px-4 py-2">
      <div class="max-w-7xl mx-auto flex items-center justify-center gap-3">
        <div class="flex items-center gap-2 text-amber-800">
          <Calendar class="h-4 w-4" />
          <span class="font-semibold text-sm">Scoring period has ended</span>
        </div>
      </div>
    </div>

    <!-- Compact Header -->
    <header class="fixed inset-x-0 z-40 bg-white border-b border-slate-200 shadow-sm"
        :class="hasBanner ? 'top-10' : 'top-0'">
        <div class="max-w-[1800px] mx-auto px-4 lg:px-6">
          <!-- Top Row: Title and Actions -->
          <div class="flex items-center justify-between h-14">
            <!-- Left: Back & Title -->
            <div class="flex items-center gap-3">
              <Link :href="route('judge.dashboard')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-teal-600 transition-colors">
                <ChevronLeft class="w-5 h-5" />
              </Link>
              <div>
                <div class="flex items-center gap-2">
                  <h1 class="text-lg font-bold text-slate-800">{{ currentRound?.name }}</h1>
                  <span v-if="currentRound?.type" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                      :class="[
                          currentRound.type.toLowerCase().includes('final') && !currentRound.type.toLowerCase().includes('semi') ? 'bg-purple-100 text-purple-700' :
                          currentRound.type.toLowerCase().includes('semi') ? 'bg-blue-100 text-blue-700' :
                          'bg-teal-100 text-teal-700'
                      ]">
                      {{ getRoundTypeDisplay(currentRound) }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] font-medium text-slate-500">
                  <span>{{ pageant?.name }}</span>
                  <span v-if="pageant?.scoring_system" class="text-slate-300">•</span>
                  <span v-if="pageant?.scoring_system" class="text-purple-600">{{ formatScoringSystem(pageant.scoring_system) }}</span>
                </div>
              </div>
            </div>

            <!-- Right: Stats & Actions -->
            <div class="flex items-center gap-3">
              <!-- Progress Stats -->
              <div class="hidden sm:flex items-center gap-4 px-4 py-1.5 bg-slate-50 rounded-lg border border-slate-200">
                <div class="text-center">
                  <div class="text-lg font-black text-teal-600">{{ scoredContestantsCount }}</div>
                  <div class="text-[9px] uppercase tracking-wide text-slate-500 font-semibold">Scored</div>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="text-center">
                  <div class="text-lg font-black text-slate-400">{{ sortedContestants.length - scoredContestantsCount }}</div>
                  <div class="text-[9px] uppercase tracking-wide text-slate-500 font-semibold">Remaining</div>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="text-center">
                  <div class="text-lg font-black text-slate-800">{{ sortedContestants.length }}</div>
                  <div class="text-[9px] uppercase tracking-wide text-slate-500 font-semibold">Total</div>
                </div>
              </div>
              
              <!-- Gender Filter -->
              <div v-if="isPairCompetition" class="flex items-center bg-slate-100 rounded-lg p-0.5">
                <button 
                    @click="handleGenderFilterChange('all')"
                    class="px-3 py-1.5 rounded-md text-xs font-bold transition-all"
                    :class="genderFilter === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                >All</button>
                <button 
                    @click="handleGenderFilterChange('female')"
                    class="px-3 py-1.5 rounded-md text-xs font-bold transition-all"
                    :class="genderFilter === 'female' ? 'bg-pink-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                >Female</button>
                <button 
                    @click="handleGenderFilterChange('male')"
                    class="px-3 py-1.5 rounded-md text-xs font-bold transition-all"
                    :class="genderFilter === 'male' ? 'bg-blue-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                >Male</button>
              </div>
            </div>
          </div>
          
          <!-- Bottom Row: Round Navigation -->
          <div class="flex items-center gap-2 pb-2 overflow-x-auto scrollbar-hide">
            <button 
                v-for="round in rounds" 
                :key="round.id"
                @click="handleRoundChange(round.id)"
                class="relative px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5"
                :class="[
                    currentRoundId === round.id.toString() 
                        ? 'bg-slate-800 text-white shadow-md' 
                        : 'bg-white text-slate-600 border border-slate-200 hover:border-teal-300 hover:text-teal-600'
                ]"
            >
                <span v-if="pageant.current_round_id === round.id && !round.is_locked" class="relative flex h-1.5 w-1.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                </span>
                <Lock v-if="round.is_locked" class="w-3 h-3" />
                <CheckCircle v-else-if="round.is_complete" class="w-3 h-3 text-emerald-500" />
                {{ round.name }}
            </button>
          </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 min-h-screen px-4 lg:px-6 max-w-[1800px] mx-auto"
        :class="hasBanner ? 'pt-32' : 'pt-24'">
        
        <!-- Error State -->
        <div v-if="error" class="flex items-center justify-center min-h-[60vh]">
            <div class="bg-white border border-red-100 rounded-2xl p-8 text-center shadow-xl max-w-md">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <AlertCircle class="h-7 w-7 text-red-600" />
                </div>
                <h3 class="text-lg font-bold text-red-900 mb-2">Unable to Load Scoring Interface</h3>
                <p class="text-red-600 mb-6 text-sm">{{ error }}</p>
                <Link :href="route('judge.dashboard')" class="inline-block px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-lg transition-colors text-sm">
                    Return to Dashboard
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="contestants.length === 0" class="flex items-center justify-center min-h-[60vh]">
            <div class="text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Users class="w-8 h-8 text-slate-400" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">No Contestants Found</h3>
                <p class="text-slate-500 text-sm">There are no contestants assigned to this round yet.</p>
            </div>
        </div>

        <!-- Tabular Scoring Interface -->
        <template v-else>
          <!-- Pair Competition: Separate Tables -->
          <template v-if="isPairCompetition && genderFilter === 'all'">
            <!-- Female Candidates Table -->
            <div v-if="femaleContestants.length > 0" class="mb-8">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-pink-500"></div>
                <h2 class="text-lg font-bold text-slate-800">Female Candidates</h2>
                <span class="text-sm text-slate-500">({{ femaleContestants.length }})</span>
              </div>
              <ScoringTable 
                :contestants="femaleContestants" 
                :criteria="criteria"
                :scores="scores"
                :saved-scores="savedScores"
                :notes="notes"
                :can-edit-scores="canEditScores"
                :submit-loading="submitLoading"
                :format-score="formatScore"
                :is-percentage-scoring="isPercentageScoring"
                gender="female"
                @score-change="handleScoreChange"
                @submit-scores="submitScores"
                @view-details="openContestantDetails"
                @update-notes="updateNotes"
              />
            </div>
            
            <!-- Male Candidates Table -->
            <div v-if="maleContestants.length > 0" class="mb-8">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                <h2 class="text-lg font-bold text-slate-800">Male Candidates</h2>
                <span class="text-sm text-slate-500">({{ maleContestants.length }})</span>
              </div>
              <ScoringTable 
                :contestants="maleContestants" 
                :criteria="criteria"
                :scores="scores"
                :saved-scores="savedScores"
                :notes="notes"
                :can-edit-scores="canEditScores"
                :submit-loading="submitLoading"
                :format-score="formatScore"
                :is-percentage-scoring="isPercentageScoring"
                gender="male"
                @score-change="handleScoreChange"
                @submit-scores="submitScores"
                @view-details="openContestantDetails"
                @update-notes="updateNotes"
              />
            </div>
          </template>
          
          <!-- Single Table (non-pair or filtered) -->
          <template v-else>
            <ScoringTable 
              :contestants="sortedContestants" 
              :criteria="criteria"
              :scores="scores"
              :saved-scores="savedScores"
              :notes="notes"
              :can-edit-scores="canEditScores"
              :submit-loading="submitLoading"
              :format-score="formatScore"
              :is-percentage-scoring="isPercentageScoring"
              :gender="genderFilter !== 'all' ? genderFilter : null"
              @score-change="handleScoreChange"
              @submit-scores="submitScores"
              @view-details="openContestantDetails"
              @update-notes="updateNotes"
            />
          </template>
          
          <!-- Floating Submit All Button -->
          <div class="fixed bottom-6 right-6 z-30">
            <button 
              @click="submitAllScores"
              :disabled="!canEditScores || !hasAnyCompleteScores || isSubmittingAll"
              class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-lg shadow-teal-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <span v-if="isSubmittingAll" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              <Save v-else class="w-4 h-4" />
              <span>Submit All Scores</span>
            </button>
          </div>
        </template>
    </main>

    <!-- Contestant Details Modal -->
    <TransitionRoot appear :show="showDetails" as="template">
        <Dialog as="div" @close="showDetails = false" class="relative z-[60]">
            <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                        <DialogPanel class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all">
                            <div class="flex flex-col md:flex-row max-h-[80vh]">
                                <!-- Image Section -->
                                <div class="w-full md:w-2/5 h-64 md:h-auto relative bg-slate-100 flex-shrink-0">
                                    <img v-if="selectedContestant?.image" :src="selectedContestant.image" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                        <Users class="w-16 h-16 text-slate-300" />
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex flex-col justify-end p-6">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="px-2.5 py-1 rounded-lg bg-white/90 text-slate-900 font-black text-lg">#{{ selectedContestant?.number }}</span>
                                            <span v-if="isPairCompetition && selectedContestant?.gender" 
                                                class="px-2 py-1 rounded-lg text-xs font-bold"
                                                :class="selectedContestant.gender === 'female' ? 'bg-pink-500 text-white' : 'bg-blue-500 text-white'">
                                                {{ selectedContestant.gender === 'female' ? 'Female' : 'Male' }}
                                            </span>
                                        </div>
                                        <h2 class="text-2xl font-black text-white">{{ selectedContestant?.name }}</h2>
                                        <p v-if="selectedContestant?.origin" class="text-teal-200 font-medium text-sm flex items-center gap-1.5 mt-1">
                                            <MapPin class="w-3.5 h-3.5" /> {{ selectedContestant.origin }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Details Section -->
                                <div class="w-full md:w-3/5 p-6 flex flex-col overflow-y-auto">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-lg font-bold text-slate-900">Contestant Details</h3>
                                        <button @click="showDetails = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                                            <X class="w-5 h-5" />
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-6 flex-1">
                                        <!-- Description -->
                                        <div>
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Description</label>
                                            <p class="text-slate-600 leading-relaxed">
                                                {{ selectedContestant?.description || 'No description provided for this contestant.' }}
                                            </p>
                                        </div>
                                        
                                        <!-- Current Scores (if any) -->
                                        <div v-if="selectedContestant && getContestantTotalScore(selectedContestant.id) !== '-'">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Your Scores</label>
                                            <div class="bg-slate-50 rounded-xl p-4 space-y-2">
                                                <div v-for="criterion in criteria" :key="criterion.id" class="flex justify-between text-sm">
                                                    <span class="text-slate-600 capitalize">{{ criterion.name }}</span>
                                                    <span class="font-bold text-slate-900">{{ formatScore(scores[`${selectedContestant.id}-${criterion.id}`]) }}</span>
                                                </div>
                                                <div class="border-t border-slate-200 pt-2 mt-2 flex justify-between">
                                                    <span class="font-bold text-slate-700">Total Points</span>
                                                    <span class="font-black text-teal-600 text-lg">{{ formatScore(getContestantTotalScore(selectedContestant.id)) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Gallery -->
                                        <div v-if="selectedContestant?.gallery && selectedContestant.gallery.length > 0">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Gallery</label>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div v-for="(img, idx) in selectedContestant.gallery" :key="idx" class="aspect-square rounded-lg overflow-hidden bg-slate-100">
                                                    <img :src="img" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-slate-100">
                                        <button @click="showDetails = false" class="w-full py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-colors text-sm">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Save, CheckCircle, AlertCircle, Lock, MapPin, Users, Calendar, ChevronLeft, X } from 'lucide-vue-next'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import NotificationSystem from '../../Components/NotificationSystem.vue'
import ScoringTable from '../../Components/ScoringTable.vue'

// Get the page props for auth user
const page = usePage()

// No layout used for full-screen immersive experience
defineOptions({
  layout: null
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
  error: { type: String, default: null },
  allContestantScores: { type: Array, default: () => [] }
})

// State
const currentRoundId = ref(props.currentRound?.id?.toString())
const submitLoading = ref({})
const notificationSystem = ref(null)
const realtimeLoading = ref(false)
const isChannelReady = ref(false)
const showDetails = ref(false)
const selectedContestant = ref(null)
const genderFilter = ref('all')
const isSubmittingAll = ref(false)
let pageantChannel = null

const scores = ref({ ...props.existingScores })
const savedScores = ref({ ...props.existingScores }) // Tracks saved scores for ranking
const notes = ref({ ...props.existingNotes })

// Check if pageant is pair competition
const isPairCompetition = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

// Sort contestants with gender grouping for pair competitions  
const sortedContestants = computed(() => {
  let contestants = [...props.contestants]
  
  // Apply gender filter for pair competitions
  if (isPairCompetition.value && genderFilter.value !== 'all') {
    contestants = contestants.filter(c => c.gender === genderFilter.value)
  }
  
  if (isPairCompetition.value) {
    const females = contestants.filter(c => c.gender === 'female').sort((a, b) => {
      const numA = parseInt(a.number || 0)
      const numB = parseInt(b.number || 0)
      return numA - numB
    })
    const males = contestants.filter(c => c.gender === 'male').sort((a, b) => {
      const numA = parseInt(a.number || 0)
      const numB = parseInt(b.number || 0)
      return numA - numB
    })
    const others = contestants.filter(c => !c.gender || (c.gender !== 'male' && c.gender !== 'female')).sort((a, b) => {
      const numA = parseInt(a.number || 0)
      const numB = parseInt(b.number || 0)
      return numA - numB
    })
    return [...females, ...males, ...others]
  }
  
  return contestants.sort((a, b) => {
    const numA = parseInt(a.number || 0)
    const numB = parseInt(b.number || 0)
    return numA - numB
  })
})

// Get female contestants
const femaleContestants = computed(() => {
  return props.contestants
    .filter(c => c.gender === 'female')
    .sort((a, b) => parseInt(a.number || 0) - parseInt(b.number || 0))
})

// Get male contestants
const maleContestants = computed(() => {
  return props.contestants
    .filter(c => c.gender === 'male')
    .sort((a, b) => parseInt(a.number || 0) - parseInt(b.number || 0))
})

// Count scored contestants
const scoredContestantsCount = computed(() => {
  return sortedContestants.value.filter(c => isContestantScoreComplete(c.id)).length
})

// Check if there are any complete scores to submit
const hasAnyCompleteScores = computed(() => {
  return sortedContestants.value.some(c => isContestantScoreComplete(c.id))
})

// Check if a banner should be displayed
const hasBanner = computed(() => {
  const status = props.pageant?.scoring_status
  return props.pageant?.is_completed || status === 'not_started' || status === 'ended'
})

// Methods
const formatTime = (time) => {
  if (!time) return ''
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const displayHour = hour % 12 || 12
  return `${displayHour}:${minutes} ${ampm}`
}

const getRoundTypeDisplay = (round) => {
  if (!round || !round.type) return ''
  if (round.top_n_proceed) {
    return `${round.type} (Top ${round.top_n_proceed})`
  }
  return round.type
}

const getCompletedCriteriaCount = (contestantId) => {
  if (!contestantId) return 0
  return props.criteria.filter(criterion => {
    const score = scores.value[`${contestantId}-${criterion.id}`]
    return score !== undefined && score !== null && score !== 0
  }).length
}

const handleRoundChange = (roundId) => {
  if (roundId === props.currentRound.id) return
  router.visit(route('judge.scoring', [props.pageant.id, roundId]))
}

const handleGenderFilterChange = (gender) => {
  genderFilter.value = gender
}

const openContestantDetails = (contestant) => {
  selectedContestant.value = contestant
  showDetails.value = true
}

const updateNotes = (contestantId, noteValue) => {
  notes.value[contestantId] = noteValue
}

const handleScoreChange = (value, contestantId, criterionId, criterion) => {
  try {
    let v = Number(value);
    const minScore = Number(criterion.min_score) || 0;
    const maxScore = Number(criterion.max_score) || 100;
    const scoreKey = `${contestantId}-${criterionId}`;
    const previousValue = scores.value[scoreKey];
    
    if (Number.isNaN(v) || !Number.isFinite(v)) {
      if (notificationSystem.value) {
        notificationSystem.value.warning(`Please enter a valid number for "${criterion.name}"`, {
          title: 'Invalid Score',
          timeout: 4000
        });
      }
      scores.value[scoreKey] = previousValue !== undefined ? previousValue : null;
      return;
    }
    
    if (v < minScore) {
      if (notificationSystem.value) {
        notificationSystem.value.warning(`Score for "${criterion.name}" must be at least ${formatScore(minScore)}. You entered ${formatScore(v)}.`, {
          title: 'Score Too Low',
          timeout: 5000
        });
      }
      scores.value[scoreKey] = previousValue !== undefined && previousValue !== null ? previousValue : null;
      return;
    }
    
    if (v > maxScore) {
      if (notificationSystem.value) {
        notificationSystem.value.warning(`Score for "${criterion.name}" cannot exceed ${formatScore(maxScore)}. You entered ${formatScore(v)}.`, {
          title: 'Score Too High',
          timeout: 5000
        });
      }
      scores.value[scoreKey] = previousValue !== undefined && previousValue !== null ? previousValue : null;
      return;
    }
    
    if (!criterion.allow_decimals) {
      v = Math.round(v);
    } else if (criterion.decimal_places > 0) {
      try {
        v = Number(v.toFixed(Math.min(criterion.decimal_places, 10)));
      } catch (error) {
        v = Math.round(v);
      }
    }
    
    scores.value[scoreKey] = v;
  } catch (error) {
    console.error('Error in handleScoreChange:', error);
    if (notificationSystem.value) {
      notificationSystem.value.error('An error occurred while processing the score. Please try again.', {
        title: 'Error',
        timeout: 4000
      });
    }
  }
}

const getContestantTotalScore = (contestantId) => {
  const contestantScores = props.criteria.map(criterion => 
    scores.value[`${contestantId}-${criterion.id}`] || 0
  )
  
  if (contestantScores.some(score => score === 0 || score === null || score === undefined)) return '-'
  
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

const isContestantScoreComplete = (contestantId) => {
  return props.criteria.every(criterion => {
    const score = scores.value[`${contestantId}-${criterion.id}`]
    return score !== undefined && score !== null
  })
}

const submitScores = async (contestantId, autoAdvance = false) => {
  if (submitLoading.value[contestantId]) return
  submitLoading.value[contestantId] = true
  
  try {
    const contestantScores = {}
    const invalidScores = [];
    const contestant = props.contestants.find(c => c.id === contestantId)
    
    props.criteria.forEach(criterion => {
      const score = scores.value[`${contestantId}-${criterion.id}`];
      if (score === undefined || score === null) {
        invalidScores.push({ name: criterion.name, reason: 'missing' });
        return;
      }
      const minScore = Number(criterion.min_score);
      const maxScore = Number(criterion.max_score);
      if (score < minScore) {
        invalidScores.push({ name: criterion.name, reason: 'too_low', value: score, min: minScore, max: maxScore });
        return;
      }
      if (score > maxScore) {
        invalidScores.push({ name: criterion.name, reason: 'too_high', value: score, min: minScore, max: maxScore });
        return;
      }
      contestantScores[criterion.id] = score;
    });
    
    if (invalidScores.length > 0) {
      const errorMessages = invalidScores.map(inv => {
        if (inv.reason === 'missing') return `"${inv.name}" - score is required`;
        else if (inv.reason === 'too_low') return `"${inv.name}" - ${formatScore(inv.value)} is below minimum (${formatScore(inv.min)})`;
        else if (inv.reason === 'too_high') return `"${inv.name}" - ${formatScore(inv.value)} exceeds maximum (${formatScore(inv.max)})`;
        return `"${inv.name}" - invalid score`;
      });
      
      if (notificationSystem.value) {
        notificationSystem.value.error(
          `Please correct the following scores:\n${errorMessages.join('\n')}`,
          { title: 'Invalid Scores', timeout: 8000 }
        );
      }
      return;
    }
    
    const response = await window.axios.post(route('judge.scores.submit', [props.pageant.id, props.currentRound.id]), {
      contestant_id: contestantId,
      scores: contestantScores,
      notes: notes.value[contestantId] || ''
    })
    
    if (response.data.success) {
      // Update saved scores to trigger ranking update
      // Create a new object reference to ensure Vue reactivity triggers in child components
      const newSavedScores = { ...savedScores.value }
      props.criteria.forEach(criterion => {
        const scoreKey = `${contestantId}-${criterion.id}`
        newSavedScores[scoreKey] = contestantScores[criterion.id]
      })
      savedScores.value = newSavedScores
      
      if (notificationSystem.value) {
        notificationSystem.value.success(`Scores for ${contestant?.name || 'contestant'} saved!`, {
          title: 'Success',
          timeout: 2000
        })
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

const submitAllScores = async () => {
  if (isSubmittingAll.value) return
  isSubmittingAll.value = true
  
  const completeContestants = sortedContestants.value.filter(c => isContestantScoreComplete(c.id))
  let successCount = 0
  let errorCount = 0
  
  for (const contestant of completeContestants) {
    try {
      await submitScores(contestant.id, false)
      successCount++
    } catch (error) {
      errorCount++
    }
  }
  
  if (notificationSystem.value) {
    if (errorCount === 0) {
      notificationSystem.value.success(`All ${successCount} contestant scores submitted successfully!`, {
        title: 'All Scores Submitted',
        timeout: 3000
      })
    } else {
      notificationSystem.value.warning(`${successCount} scores submitted, ${errorCount} failed.`, {
        title: 'Partial Success',
        timeout: 5000
      })
    }
  }
  
  isSubmittingAll.value = false
}

// Real-time updates
onMounted(() => {
  if (props.pageant) {
    const judgeId = page.props.auth?.user?.id
    console.log('🔔 Scoring: Setting up notification listeners for judge:', judgeId)
    
    pageantChannel = window.Echo.private(`pageant.${props.pageant.id}`)
      .subscribed(() => { 
        console.log('✅ Scoring: Subscribed to pageant channel')
        isChannelReady.value = true 
      })
      .listen('RoundUpdated', (e) => { handleRoundUpdate(e) })
      .listen('ScoreUpdated', (e) => { handleScoreUpdate(e) })
      .listen('ContestantBackedOut', (e) => { handleContestantBackedOut(e) })
    
    if (judgeId) {
      console.log('🔔 Scoring: Subscribing to judge channel:', `judge.${judgeId}`)
      window.Echo.private(`judge.${judgeId}`)
        .subscribed(() => {
          console.log('✅ Scoring: Successfully subscribed to judge channel')
        })
        .listen('JudgeNotified', (e) => { handleJudgeNotification(e) })
        .error((error) => {
          console.error('❌ Scoring: Error subscribing to judge channel:', error)
        })
    }
  }
})

onUnmounted(() => {
  if (props.pageant) {
    console.log('🔌 Scoring: Leaving channels')
    window.Echo.leave(`pageant.${props.pageant.id}`)
    const judgeId = page.props.auth?.user?.id
    if (judgeId) {
      window.Echo.leave(`judge.${judgeId}`)
    }
  }
})

const handleJudgeNotification = (event) => {
  console.log('🔔 Scoring: JudgeNotified event received:', event)
  const { title, message, round_name, action } = event
  
  playNotificationSound()

  if (notificationSystem.value) {
    const displayMessage = round_name ? `${message} (${round_name})` : message
    
    if (action === 'score_request') {
      notificationSystem.value.info(displayMessage, { title: title || 'Scoring Alert', timeout: 10000 })
    } else {
      notificationSystem.value.success(displayMessage, { title: title || 'Notification', timeout: 10000 })
    }
  } else {
    console.warn('⚠️ NotificationSystem ref not available, using browser alert')
    alert(`${title || 'Notification'}: ${message}`)
  }
}

const playNotificationSound = () => {
  try {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)()
    const oscillator = audioContext.createOscillator()
    const gainNode = audioContext.createGain()
    
    oscillator.connect(gainNode)
    gainNode.connect(audioContext.destination)
    
    oscillator.frequency.value = 880
    oscillator.type = 'sine'
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5)
    
    oscillator.start(audioContext.currentTime)
    oscillator.stop(audioContext.currentTime + 0.5)
  } catch (e) {
    console.log('Could not play notification sound:', e)
  }
}

const handleScoreUpdate = async (event) => {
  const judgeId = page.props.auth?.user?.id
  if (event.score?.judge_id === judgeId && event.score?.round_id === props.currentRound?.id) {
    router.reload({
      only: ['allContestantScores', 'existingScores'],
      preserveState: true,
      preserveScroll: true
    })
  }
}

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

const handleContestantBackedOut = (event) => {
  console.log('🚫 Scoring: ContestantBackedOut event received:', event)
  const { contestant_id, contestant_name, contestant_number, action, reason, backed_out } = event
  
  // Play notification sound
  playNotificationSound()
  
  if (notificationSystem.value) {
    if (action === 'backed_out') {
      notificationSystem.value.warning(
        `Contestant #${contestant_number} (${contestant_name}) has been marked as backed out${reason ? `: ${reason}` : ''}`,
        { title: 'Contestant Backed Out', timeout: 8000 }
      )
    } else if (action === 'restored') {
      notificationSystem.value.success(
        `Contestant #${contestant_number} (${contestant_name}) has been restored and can now be scored.`,
        { title: 'Contestant Restored', timeout: 8000 }
      )
    }
  }
  
  // Reload page to get updated contestant data
  router.reload({
    only: ['contestants'],
    preserveState: true,
    preserveScroll: true
  })
}

const isPercentageScoring = computed(() => {
  return props.pageant?.scoring_system === 'percentage'
})

const formatScore = (value) => {
  if (value === '-' || value === undefined || value === null) return '-'
  const numValue = Number(value)
  if (isNaN(numValue)) return '-'
  return isPercentageScoring.value ? `${numValue}%` : numValue.toString()
}

const formatScoringSystem = (system) => {
  const systemMap = {
    'percentage': 'Percentage',
    '1-10': 'Scale 1-10',
    '1-5': 'Scale 1-5',
    'points': 'Points'
  }
  return systemMap[system] || system
}
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>