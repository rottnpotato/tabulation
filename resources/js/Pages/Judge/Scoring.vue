<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-slate-200 text-slate-800 font-sans selection:bg-teal-500 selection:text-white overflow-x-hidden relative">
    
    <!-- Dynamic Background -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-teal-500/20 blur-[120px] animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/20 blur-[120px] animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-10%] left-[20%] w-[40%] h-[40%] rounded-full bg-sky-500/20 blur-[120px] animate-blob animation-delay-4000"></div>
        <!-- CSS Noise Pattern -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>
    </div>

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

    <!-- Header (Glassmorphism) -->
    <header class="fixed top-0 inset-x-0 z-40 h-auto min-h-[5rem] pt-2 pb-2 px-4 lg:px-8 flex flex-col md:flex-row items-center justify-between bg-white/70 backdrop-blur-xl border-b border-white/40 shadow-sm transition-all duration-300 supports-[backdrop-filter]:bg-white/60 gap-4">
        <!-- Left: Back & Title -->
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-4">
                <Link :href="route('judge.dashboard')" class="p-2 rounded-full hover:bg-slate-100 text-slate-500 hover:text-teal-600 transition-colors shrink-0">
                    <ChevronLeft class="w-6 h-6" />
                </Link>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 text-xs font-bold tracking-wider text-teal-600 uppercase truncate">
                        <span>{{ pageant?.name }}</span>
                        <span class="w-1 h-1 rounded-full bg-teal-400 shrink-0"></span>
                        <span>Judge Panel</span>
                        <span v-if="pageant?.scoring_system" class="w-1 h-1 rounded-full bg-purple-400 shrink-0"></span>
                        <span v-if="pageant?.scoring_system" class="text-purple-600">{{ formatScoringSystem(pageant.scoring_system) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-black text-slate-800 tracking-tight truncate">{{ currentRound?.name }}</h1>
                        <span v-if="currentRound?.type" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                            :class="[
                                currentRound.type.toLowerCase().includes('final') && !currentRound.type.toLowerCase().includes('semi') ? 'bg-purple-100 text-purple-800' :
                                currentRound.type.toLowerCase().includes('semi') ? 'bg-blue-100 text-blue-800' :
                                'bg-teal-100 text-teal-800'
                            ]">
                            {{ getRoundTypeDisplay(currentRound) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center: Round Navigation (Pills) -->
        <div class="w-full md:w-auto overflow-x-auto scrollbar-hide pb-1 md:pb-0">
            <div class="flex items-center gap-2 px-1">
                <button 
                    v-for="round in rounds" 
                    :key="round.id"
                    @click="handleRoundChange(round.id)"
                    class="relative px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all duration-200 flex items-center gap-2 ring-1 ring-inset"
                    :class="[
                        currentRoundId === round.id.toString() 
                            ? 'bg-slate-800 text-white ring-slate-800 shadow-md transform scale-105' 
                            : 'bg-white/50 text-slate-600 ring-slate-200 hover:bg-white hover:text-teal-600 hover:ring-teal-200'
                    ]"
                >
                    <span v-if="pageant.current_round_id === round.id && !round.is_locked" class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <Lock v-if="round.is_locked" class="w-3 h-3" />
                    <CheckCircle v-else-if="round.is_complete" class="w-3 h-3 text-emerald-500" />
                    {{ round.name }}
                    <span v-if="round.scoring_progress && !round.is_complete" class="text-[10px] opacity-70">
                      ({{ Math.round(round.scoring_progress) }}%)
                    </span>
                </button>
            </div>
        </div>

        <!-- Right: Active Contestant Progress -->
        <div v-if="activeContestant && criteria.length > 0" class="w-full md:w-auto flex items-center justify-center md:justify-end gap-3 bg-white/50 md:bg-transparent p-2 md:p-0 rounded-lg md:rounded-none border md:border-none border-white/50">
             <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-teal-700 hidden sm:inline">{{ activeContestant.name }}</span>
                <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">{{ getCompletedCriteriaCount(activeContestant.id) }}/{{ criteria.length }}</span>
             </div>
             
             <div class="w-24 h-2 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-teal-500 rounded-full transition-all duration-500"
                    :style="{ width: `${(getCompletedCriteriaCount(activeContestant.id) / criteria.length) * 100}%` }">
                </div>
            </div>
             <CheckCircle v-if="isContestantScoreComplete(activeContestant.id)" class="w-4 h-4 text-emerald-500" />
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 pt-32 md:pt-28 pb-24 min-h-screen flex flex-col lg:flex-row items-stretch justify-center gap-6 px-4 lg:px-8 max-w-[1920px] mx-auto">
        

        
        <!-- Error State -->
        <div v-if="error" class="absolute inset-0 z-50 bg-slate-50 flex items-center justify-center p-8">
            <div class="bg-white border border-red-100 rounded-3xl p-8 text-center shadow-xl max-w-md">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <AlertCircle class="h-8 w-8 text-red-600" />
                </div>
                <h3 class="text-lg font-bold text-red-900 mb-2">Unable to Load Scoring Interface</h3>
                <p class="text-red-600 mb-6 whitespace-pre-line">{{ error }}</p>
                <Link :href="route('judge.dashboard')" class="inline-block px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl transition-colors shadow-lg">
                    Return to Dashboard
                </Link>
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

        <template v-else>
            <!-- Left Scoring Panel -->
            <div class="hidden lg:flex w-1/4 flex-col gap-3 py-4 overflow-y-auto max-h-[calc(100vh-6rem)] scrollbar-hide pb-20">
                <div v-for="criterion in criteria" :key="criterion.id" class="bg-white/60 backdrop-blur-md rounded-2xl p-5 shadow-sm border border-white/60 hover:shadow-md hover:bg-white/80 transition-all duration-300 group">
                    <div class="flex justify-between items-start mb-3">
                        <label class="font-bold text-slate-700 group-hover:text-teal-700 transition-colors text-sm capitalize">{{ criterion.name }}</label>
                        <span class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-600 font-bold">{{ formatScore(scores[`${activeContestant.id}-${criterion.id}`] || 0) }} / {{ formatScore(criterion.max_score) }}</span>
                    </div>
                    <ScoreInput
                        :min="Number(criterion.min_score)"
                        :max="Number(criterion.max_score)"
                        :step="criterion.allow_decimals ? 0.1 : 1"
                        :allow-decimals="criterion.allow_decimals"
                        :decimal-places="criterion.decimal_places || 1"
                        :disabled="!canEditScores"
                        :show-slider="false"
                        v-model="scores[`${activeContestant.id}-${criterion.id}`]"
                        @change="(val) => handleScoreChange(val, activeContestant.id, criterion.id, criterion)"
                        class="w-full"
                    />
                    <p class="text-[10px] text-slate-400 mt-2 line-clamp-2" :title="criterion.description">{{ criterion.description }}</p>
                </div>
            </div>

            <!-- Center Stage (Carousel) -->
            <div class="flex-1 flex flex-col items-center justify-center relative min-h-[400px] lg:min-h-[500px]">
                <!-- Background Text Effect -->
                <!-- Background Text Effect -->
                <h2 class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[15vw] font-black text-slate-900/5 whitespace-nowrap select-none pointer-events-none z-0">
                    {{ activeContestant.number }}
                </h2>

                <!-- Carousel Navigation Buttons -->
                <button @click="prevContestant" :disabled="currentIndex === 0" class="absolute left-0 lg:-left-4 top-1/2 -translate-y-1/2 z-20 p-3 lg:p-4 rounded-full bg-white/80 backdrop-blur shadow-lg text-slate-600 hover:text-teal-600 hover:scale-110 transition-all disabled:opacity-0 disabled:pointer-events-none cursor-pointer border border-white/50">
                    <ChevronLeft class="w-6 h-6 lg:w-8 lg:h-8" />
                </button>
                <button @click="nextContestant" :disabled="currentIndex === sortedContestants.length - 1" class="absolute right-0 lg:-right-4 top-1/2 -translate-y-1/2 z-20 p-3 lg:p-4 rounded-full bg-white/80 backdrop-blur shadow-lg text-slate-600 hover:text-teal-600 hover:scale-110 transition-all disabled:opacity-0 disabled:pointer-events-none cursor-pointer border border-white/50">
                    <ChevronRight class="w-6 h-6 lg:w-8 lg:h-8" />
                </button>

                <!-- Active Card -->
                <div class="relative z-10 w-full max-w-[300px] sm:max-w-sm lg:max-w-md aspect-[3/4] group cursor-pointer perspective-1000" @click="showDetails = true">
                    <div class="w-full h-full relative preserve-3d transition-transform duration-500 hover:rotate-y-6 hover:scale-105">
                        <!-- Image -->
                        <div class="absolute inset-0 rounded-[2rem] lg:rounded-[2.5rem] overflow-hidden shadow-2xl shadow-slate-400/20 border-4 border-white bg-white">
                            <img :src="activeContestant.image" class="w-full h-full object-cover" />
                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-60"></div>
                        </div>
                        
                        <!-- Floating Info -->
                        <div class="absolute bottom-0 inset-x-0 p-6 flex flex-col items-center text-center">
                            <h3 class="text-2xl lg:text-3xl font-black text-white leading-none mb-2 drop-shadow-md">{{ activeContestant.name }}</h3>
                            <p class="text-sm font-bold text-teal-200 flex items-center gap-1 bg-slate-900/40 backdrop-blur-md px-3 py-1 rounded-full border border-white/10">
                                <MapPin class="w-3 h-3" /> {{ activeContestant.origin || 'Unknown' }}
                            </p>
                        </div>

                        <!-- Number Badge -->
                        <div class="absolute top-6 right-6 w-12 h-12 lg:w-14 lg:h-14 rounded-2xl bg-white/90 backdrop-blur-md text-slate-900 flex items-center justify-center font-black text-xl lg:text-2xl shadow-lg border border-white/50">
                            {{ activeContestant.number }}
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Scoring (Visible only on small screens) -->
                <div class="lg:hidden w-full mt-12 space-y-4 pb-20">
                    <div class="bg-white/80 backdrop-blur-md rounded-2xl p-4 shadow-sm border border-white/50">
                        <h3 class="font-bold text-slate-900 mb-4">Scoring Criteria</h3>
                        <div class="space-y-6">
                            <div v-for="criterion in criteria" :key="criterion.id" class="space-y-2">
                                <div class="flex justify-between">
                                    <label class="text-sm font-medium text-slate-700 capitalize">{{ criterion.name }}</label>
                                    <span class="text-xs font-bold text-slate-500">{{ formatScore(scores[`${activeContestant.id}-${criterion.id}`] || 0) }} / {{ formatScore(criterion.max_score) }}</span>
                                </div>
                                <ScoreInput
                                    :min="Number(criterion.min_score)"
                                    :max="Number(criterion.max_score)"
                                    :step="criterion.allow_decimals ? 0.1 : 1"
                                    :allow-decimals="criterion.allow_decimals"
                                    :decimal-places="criterion.decimal_places || 1"
                                    :disabled="!canEditScores"
                                    :show-slider="false"
                                    v-model="scores[`${activeContestant.id}-${criterion.id}`]"
                                    @change="(val) => handleScoreChange(val, activeContestant.id, criterion.id, criterion)"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Notes & Submit -->
                    <div class="bg-white/80 backdrop-blur-md rounded-2xl p-4 shadow-sm border border-white/50">
                        <label class="text-xs font-bold text-slate-400 uppercase mb-2 block">Notes</label>
                        <textarea 
                            v-model="notes[activeContestant.id]"
                            rows="2"
                            class="w-full bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-teal-500 mb-4 resize-none p-3 text-slate-800 placeholder-slate-400"
                            placeholder="Optional comments..."
                        ></textarea>
                        
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-bold text-slate-500">Total Score</span>
                            <span class="text-2xl font-black text-teal-600">{{ formatScore(currentAverage) }}</span>
                        </div>

                        <button 
                            @click="submitScores(activeContestant.id)"
                            :disabled="!canEditScores || !isContestantScoreComplete(activeContestant.id) || submitLoading[activeContestant.id]"
                            class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-teal-600 transition-all shadow-lg disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <span v-if="submitLoading[activeContestant.id]" class="animate-spin">...</span>
                            <span v-else>Submit Score</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Scoring Panel -->
            <div class="hidden lg:flex w-1/4 flex-col gap-3 py-4 overflow-y-auto max-h-[calc(100vh-6rem)] scrollbar-hide pb-20">
                <!-- Notes & Submit -->
                <div class="bg-white/80 backdrop-blur-md rounded-xl p-4 shadow-lg border border-teal-100">
                    <label class="text-xs font-bold text-slate-400 uppercase mb-2 block">Judge's Notes</label>
                    <textarea 
                        v-model="notes[activeContestant.id]"
                        rows="3"
                        class="w-full bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-teal-500 mb-4 resize-none p-3 text-slate-800 placeholder-slate-400"
                        placeholder="Optional comments..."
                    ></textarea>
                    
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-bold text-slate-500">Total Score</span>
                        <span class="text-3xl font-black text-teal-600">{{ formatScore(currentAverage) }}</span>
                    </div>

                    <button 
                        @click="submitScores(activeContestant.id)"
                        :disabled="!canEditScores || !isContestantScoreComplete(activeContestant.id) || submitLoading[activeContestant.id]"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-teal-600 transition-all shadow-lg hover:shadow-teal-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <span v-if="submitLoading[activeContestant.id]" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span v-else>Submit Score</span>
                    </button>
                </div>
            </div>
        </template>
    </main>

    <!-- Details Modal -->
    <TransitionRoot appear :show="showDetails" as="template">
        <Dialog as="div" @close="showDetails = false" class="relative z-[60]">
            <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                        <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-3xl bg-white p-0 text-left align-middle shadow-2xl transition-all">
                            <div class="flex flex-col md:flex-row h-[600px]">
                                <div class="w-full md:w-1/2 h-full relative bg-slate-100">
                                    <img :src="activeContestant.image" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex flex-col justify-end p-8">
                                        <h2 class="text-4xl font-black text-white mb-1">{{ activeContestant.name }}</h2>
                                        <p class="text-teal-300 font-bold text-lg flex items-center gap-2">
                                            <MapPin class="w-5 h-5" /> {{ activeContestant.origin }}
                                        </p>
                                    </div>
                                </div>
                                <div class="w-full md:w-1/2 p-8 flex flex-col bg-white">
                                    <div class="flex items-center justify-between mb-8">
                                        <div class="flex items-center gap-3">
                                            <span class="px-3 py-1 rounded-lg bg-slate-900 text-white font-black text-lg">#{{ activeContestant.number }}</span>
                                            <h3 class="text-xl font-bold text-slate-900">Contestant Details</h3>
                                        </div>
                                        <button @click="showDetails = false" class="p-2 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                                            <X class="w-6 h-6" />
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-8 flex-1 overflow-y-auto pr-2">
                                        <div>
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Description</label>
                                            <p class="text-slate-600 leading-relaxed text-lg">
                                                {{ activeContestant.description || 'No description provided for this contestant.' }}
                                            </p>
                                        </div>
                                        
                                        <div v-if="activeContestant.gallery && activeContestant.gallery.length > 0">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 block">Gallery</label>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div v-for="(img, idx) in activeContestant.gallery" :key="idx" class="aspect-square rounded-lg overflow-hidden bg-slate-100">
                                                    <img :src="img" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-auto pt-6 border-t border-slate-100">
                                        <button @click="showDetails = false" class="w-full py-4 bg-slate-50 text-slate-700 font-bold rounded-xl hover:bg-slate-100 transition-colors">
                                            Close Details
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
import { Star, Save, CheckCircle, AlertCircle, Lock, MapPin, Target, Users, Calendar, ChevronLeft, ChevronRight, X } from 'lucide-vue-next'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import ScoreInput from '../../Components/ScoreInput.vue'
import NotificationSystem from '../../Components/NotificationSystem.vue'

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
  error: { type: String, default: null }
})

// State
const currentRoundId = ref(props.currentRound?.id?.toString())
const currentIndex = ref(0)
const submitLoading = ref({})
const notificationSystem = ref(null)
const realtimeLoading = ref(false)
const isChannelReady = ref(false)
const showDetails = ref(false)
let pageantChannel = null

const scores = ref({ ...props.existingScores })
const notes = ref({ ...props.existingNotes })

// Check if pageant is pair competition
const isPairCompetition = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

// Sort contestants with gender grouping for pair competitions  
const sortedContestants = computed(() => {
  const contestants = [...props.contestants]
  
  if (isPairCompetition.value) {
    // For pair competitions: group by gender (males first, then females)
    const males = contestants.filter(c => c.gender === 'male').sort((a, b) => {
      const numA = parseInt(a.number || 0)
      const numB = parseInt(b.number || 0)
      return numA - numB
    })
    const females = contestants.filter(c => c.gender === 'female').sort((a, b) => {
      const numA = parseInt(a.number || 0)
      const numB = parseInt(b.number || 0)
      return numA - numB
    })
    const others = contestants.filter(c => !c.gender || (c.gender !== 'male' && c.gender !== 'female')).sort((a, b) => {
      const numA = parseInt(a.number || 0)
      const numB = parseInt(b.number || 0)
      return numA - numB
    })
    return [...males, ...females, ...others]
  }
  
  // Default sorting by number
  return contestants.sort((a, b) => {
    const numA = parseInt(a.number || 0)
    const numB = parseInt(b.number || 0)
    return numA - numB
  })
})

// Computed
const activeContestant = computed(() => sortedContestants.value[currentIndex.value] || {})


const currentAverage = computed(() => {
  if (!activeContestant.value.id) return '-'
  return calculateAverage(activeContestant.value.id)
})

// Methods
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

const nextContestant = () => {
  if (currentIndex.value < sortedContestants.value.length - 1) {
    currentIndex.value++
  }
}

const prevContestant = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
  }
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
    
    const response = await window.axios.post(route('judge.scores.submit', [props.pageant.id, props.currentRound.id]), {
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
      
      if (autoAdvance && currentIndex.value < sortedContestants.value.length - 1) {
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
    const judgeId = props.pageant.judge_id || window.Laravel?.user?.id
    
    pageantChannel = window.Echo.private(`pageant.${props.pageant.id}`)
      .subscribed(() => { isChannelReady.value = true })
      .listen('RoundUpdated', (e) => { handleRoundUpdate(e) })
    
    // Listen for judge-specific notifications
    if (judgeId) {
      window.Echo.private(`judge.${judgeId}`)
        .listen('JudgeNotified', (e) => { handleJudgeNotification(e) })
    }
  }
})

onUnmounted(() => {
  if (props.pageant) {
    window.Echo.leave(`pageant.${props.pageant.id}`)
    const judgeId = props.pageant.judge_id || window.Laravel?.user?.id
    if (judgeId) {
      window.Echo.leave(`judge.${judgeId}`)
    }
  }
})

const handleJudgeNotification = (event) => {
  const { title, message, round_name, action } = event
  if (!notificationSystem.value) return

  // Show notification with appropriate styling
  if (action === 'score_request') {
    notificationSystem.value.info(message, {
      title: title || 'Notification',
      timeout: 8000
    })
  } else {
    notificationSystem.value.success(message, {
      title: title || 'Notification',
      timeout: 8000
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
.perspective-1000 {
    perspective: 1000px;
}
.preserve-3d {
    transform-style: preserve-3d;
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>