<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Ranking Toggle Header -->
    <div class="flex items-center justify-between px-4 py-2 bg-slate-50 border-b border-slate-200">
      <div class="flex items-center gap-3">
        <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">View Mode:</span>
        <div class="flex items-center bg-slate-100 rounded-lg p-0.5">
          <button
            @click="showRanking = false"
            class="px-3 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5"
            :class="!showRanking ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            Default Order
          </button>
          <button
            @click="showRanking = true"
            class="px-3 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5"
            :class="showRanking ? 'bg-white text-amber-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
            Live Ranking
          </button>
        </div>
      </div>
      <div v-if="showRanking" class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
          </svg>
          Preview Only - Does not affect official results
        </span>
      </div>
    </div>
    
    <!-- Table Container with horizontal scroll for mobile -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[800px]">
        <!-- Table Header -->
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <!-- Candidate Column -->
            <!-- Rank Column (only in ranking mode) -->
            <th v-if="showRanking" class="text-center py-3 px-3 font-bold text-xs uppercase tracking-wider text-amber-600 min-w-[60px] bg-amber-50/50">
              Rank
            </th>
            <th class="text-left py-3 px-4 font-bold text-xs uppercase tracking-wider text-slate-600 sticky left-0 bg-slate-50 z-10 min-w-[180px]" :class="showRanking ? 'left-[60px]' : ''">
              Candidate No.
            </th>
            <!-- Criteria Columns -->
            <th 
              v-for="criterion in criteria" 
              :key="criterion.id"
              class="text-center py-3 px-3 min-w-[120px]"
            >
              <div class="font-bold text-xs uppercase tracking-wider text-slate-600">{{ criterion.name }}</div>
              <div class="text-[10px] text-slate-400 font-medium mt-0.5">{{ criterion.weight || 100 }}%</div>
            </th>
            <!-- Total Points Column -->
            <th class="text-center py-3 px-4 font-bold text-xs uppercase tracking-wider text-slate-600 min-w-[100px] bg-slate-100/50">
              Total Points
            </th>
            <!-- Status Column -->
            <th class="text-center py-3 px-4 font-bold text-xs uppercase tracking-wider text-slate-600 min-w-[80px]">
              Status
            </th>
          </tr>
        </thead>
        
        <!-- Table Body -->
        <tbody>
          <tr 
            v-for="(contestant, index) in sortedContestants" 
            :key="contestant.id"
            class="border-b border-slate-100 transition-colors group"
            :class="[
              contestant.backed_out ? 'opacity-60 bg-red-50/30' : '',
              !contestant.backed_out && isContestantComplete(contestant.id) ? 'bg-emerald-50/30' : '',
              !contestant.backed_out && index % 2 === 0 ? 'bg-white' : 'bg-slate-50/30',
              !contestant.backed_out ? 'hover:bg-slate-50/50' : ''
            ]"
          >
            <!-- Rank Cell (only in ranking mode) -->
            <td v-if="showRanking" class="py-3 px-3 text-center bg-amber-50/30">
              <div class="flex items-center justify-center">
                <span 
                  v-if="getContestantRank(contestant.id) !== '-'"
                  class="inline-flex items-center justify-center w-8 h-8 rounded-full font-black text-sm"
                  :class="getRankBadgeClass(getContestantRank(contestant.id))"
                >
                  {{ getContestantRank(contestant.id) }}
                </span>
                <span v-else class="text-slate-300 font-medium text-sm">—</span>
              </div>
            </td>
            
            <!-- Candidate Info -->
            <td class="py-3 px-4 sticky z-10 transition-colors"
                :class="[
                  showRanking ? 'left-[60px]' : 'left-0',
                  isContestantComplete(contestant.id) ? 'bg-emerald-50/30 group-hover:bg-emerald-50/50' : index % 2 === 0 ? 'bg-white group-hover:bg-slate-50/50' : 'bg-slate-50/30 group-hover:bg-slate-100/50'
                ]">
              <div 
                class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition-opacity"
                @click="$emit('view-details', contestant)"
              >
                <!-- Contestant Image/Avatar -->
                <div class="relative flex-shrink-0">
                  <div v-if="contestant.image" class="w-10 h-10 rounded-lg overflow-hidden border-2 border-white shadow-sm">
                    <img :src="contestant.image" :alt="contestant.name" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm shadow-sm"
                    :class="gender === 'female' ? 'bg-pink-100 text-pink-700' : gender === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-teal-100 text-teal-700'">
                    {{ contestant.number }}
                  </div>
                  <!-- Gender indicator dot -->
                  <div v-if="contestant.gender && !gender" 
                    class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white"
                    :class="contestant.gender === 'female' ? 'bg-pink-500' : 'bg-blue-500'">
                  </div>
                </div>
                
                <!-- Contestant Details -->
                <div class="min-w-0">
                  <div class="flex items-center gap-2">
                    <span class="font-black text-sm px-1.5 py-0.5 rounded"
                      :class="gender === 'female' ? 'bg-pink-100 text-pink-800' : gender === 'male' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800'">
                      #{{ contestant.number }}
                    </span>
                    <span class="font-medium text-sm truncate max-w-[100px]" 
                      :class="contestant.backed_out ? 'text-red-600 line-through' : 'text-slate-800'"
                      :title="contestant.name">
                      {{ contestant.name }}
                    </span>
                    <!-- Backed Out Badge -->
                    <span 
                      v-if="contestant.backed_out" 
                      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide bg-red-100 text-red-700"
                      :title="contestant.backed_out_reason || 'No reason provided'"
                    >
                      <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                      Backed Out
                    </span>
                  </div>
                  <p v-if="contestant.origin" class="text-[10px] text-slate-400 truncate max-w-[150px]" :title="contestant.origin">
                    {{ contestant.origin }}
                  </p>
                </div>
              </div>
            </td>
            
            <!-- Score Input Cells -->
            <td 
              v-for="criterion in criteria" 
              :key="criterion.id"
              class="py-2 px-2"
            >
              <div class="flex justify-center">
                <input
                  type="number"
                  :value="scores[`${contestant.id}-${criterion.id}`]"
                  :min="criterion.min_score"
                  :max="criterion.max_score"
                  :step="criterion.allow_decimals ? 0.1 : 1"
                  :disabled="!canEditScores || contestant.backed_out"
                  @input="handleInput($event, contestant.id, criterion.id, criterion)"
                  @blur="handleBlur($event, contestant.id, criterion.id, criterion)"
                  class="w-20 text-center py-2 px-1 rounded-lg border-2 font-bold text-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="contestant.backed_out ? 'border-red-200 bg-red-50 text-red-400 cursor-not-allowed' : getInputClass(contestant.id, criterion)"
                  :placeholder="contestant.backed_out ? 'N/A' : `${criterion.min_score}-${criterion.max_score}`"
                />
              </div>
            </td>
            
            <!-- Total Points -->
            <td class="py-3 px-4 text-center bg-slate-50/50">
              <div class="font-black text-lg"
                :class="getTotalScore(contestant.id) !== '-' ? (gender === 'female' ? 'text-pink-600' : gender === 'male' ? 'text-blue-600' : 'text-teal-600') : 'text-slate-300'">
                {{ formatScore(getTotalScore(contestant.id)) }}
              </div>
            </td>
            
            <!-- Status & Actions -->
            <td class="py-3 px-4">
              <div class="flex flex-col items-center gap-1.5">
                <!-- Backed Out Status Badge -->
                <span 
                  v-if="contestant.backed_out"
                  class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-700"
                  :title="contestant.backed_out_reason || 'No reason provided'"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                  Backed Out
                </span>
                <!-- Normal Status Badge -->
                <span 
                  v-else
                  class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide"
                  :class="isContestantComplete(contestant.id) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="isContestantComplete(contestant.id) ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                  {{ isContestantComplete(contestant.id) ? 'Complete' : 'Pending' }}
                </span>
                
                <!-- Save Button (hidden for backed out) -->
                <button 
                  v-if="canEditScores && isContestantComplete(contestant.id) && !contestant.backed_out"
                  @click.stop="$emit('submit-scores', contestant.id)"
                  :disabled="submitLoading[contestant.id]"
                  class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide transition-all flex items-center gap-1"
                  :class="gender === 'female' ? 'bg-pink-600 hover:bg-pink-700 text-white' : gender === 'male' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-teal-600 hover:bg-teal-700 text-white'"
                >
                  <span v-if="submitLoading[contestant.id]" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                  <template v-else>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save
                  </template>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        
        <!-- Table Footer with Summary -->
        <tfoot v-if="contestants.length > 0">
          <tr class="bg-slate-100 border-t-2 border-slate-200">
            <td class="py-3 px-4 sticky left-0 bg-slate-100 z-10">
              <div class="flex items-center gap-2">
                <span class="font-bold text-xs uppercase tracking-wider text-slate-600">Summary</span>
                <span class="text-xs text-slate-500">({{ completedCount }}/{{ contestants.length }} scored)</span>
              </div>
            </td>
            <td v-for="criterion in criteria" :key="criterion.id" class="py-3 px-3 text-center">
              <div class="text-[10px] text-slate-500 font-medium">
                Range: {{ criterion.min_score }} - {{ criterion.max_score }}
              </div>
            </td>
            <td class="py-3 px-4 text-center bg-slate-100/50">
              <div class="text-[10px] text-slate-500 font-medium uppercase">100%</div>
            </td>
            <td class="py-3 px-4 text-center">
              <div class="text-[10px] font-bold uppercase tracking-wider" 
                :class="completedCount === contestants.length ? 'text-emerald-600' : 'text-amber-600'">
                {{ completedCount === contestants.length ? 'All Done!' : 'In Progress' }}
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  contestants: { type: Array, required: true },
  criteria: { type: Array, required: true },
  scores: { type: Object, required: true },
  notes: { type: Object, default: () => ({}) },
  canEditScores: { type: Boolean, default: true },
  submitLoading: { type: Object, default: () => ({}) },
  formatScore: { type: Function, required: true },
  isPercentageScoring: { type: Boolean, default: false },
  gender: { type: String, default: null } // 'female', 'male', or null for mixed
})

const emit = defineEmits(['score-change', 'submit-scores', 'view-details', 'update-notes'])

// State
const showRanking = ref(false)

// Calculate numeric total score for a contestant (returns number or null)
const getNumericTotalScore = (contestantId) => {
  const contestantScores = props.criteria.map(criterion => 
    props.scores[`${contestantId}-${criterion.id}`] || 0
  )
  
  if (contestantScores.some(score => score === 0 || score === null || score === undefined)) return null
  
  const totalWeight = props.criteria.reduce((sum, criterion) => {
    const weight = criterion.weight || 1
    return sum + Math.max(weight, 0)
  }, 0)
  
  if (totalWeight === 0) return null
  
  const weightedSum = contestantScores.reduce((sum, score, index) => {
    const weight = props.criteria[index].weight || 1
    const safeWeight = Math.max(weight, 0)
    return sum + (score * safeWeight / Math.max(totalWeight, 1))
  }, 0)
  
  return weightedSum
}

// Computed: contestants with ranking info, sorted if showRanking is true
const sortedContestants = computed(() => {
  // Get all contestants with their scores
  const contestantsWithScores = props.contestants.map(c => ({
    ...c,
    numericScore: getNumericTotalScore(c.id)
  }))

  if (!showRanking.value) {
    return contestantsWithScores
  }

  // Sort by score (highest first), contestants without scores go to bottom
  return [...contestantsWithScores].sort((a, b) => {
    // Backed out contestants always go to the end
    if (a.backed_out && !b.backed_out) return 1
    if (!a.backed_out && b.backed_out) return -1
    
    // Both have scores
    if (a.numericScore !== null && b.numericScore !== null) {
      return b.numericScore - a.numericScore
    }
    // Only b has score
    if (a.numericScore === null && b.numericScore !== null) return 1
    // Only a has score
    if (a.numericScore !== null && b.numericScore === null) return -1
    // Both no score - maintain original order by number
    return parseInt(a.number || 0) - parseInt(b.number || 0)
  })
})

// Calculate ranks based on scores
const contestantRanks = computed(() => {
  const ranks = {}
  const scored = sortedContestants.value
    .filter(c => getNumericTotalScore(c.id) !== null && !c.backed_out)
    .sort((a, b) => getNumericTotalScore(b.id) - getNumericTotalScore(a.id))
  
  let currentRank = 1
  let prevScore = null
  
  scored.forEach((c, index) => {
    const score = getNumericTotalScore(c.id)
    if (prevScore !== null && score < prevScore) {
      currentRank = index + 1
    }
    ranks[c.id] = currentRank
    prevScore = score
  })
  
  return ranks
})

const getContestantRank = (contestantId) => {
  const rank = contestantRanks.value[contestantId]
  return rank !== undefined ? rank : '-'
}

const getRankBadgeClass = (rank) => {
  if (rank === 1) return 'bg-amber-400 text-amber-900'
  if (rank === 2) return 'bg-slate-300 text-slate-700'
  if (rank === 3) return 'bg-amber-600/70 text-amber-100'
  return 'bg-slate-100 text-slate-600'
}

// Computed
const completedCount = computed(() => {
  return props.contestants.filter(c => isContestantComplete(c.id)).length
})

// Methods
const isContestantComplete = (contestantId) => {
  return props.criteria.every(criterion => {
    const score = props.scores[`${contestantId}-${criterion.id}`]
    return score !== undefined && score !== null
  })
}

const getTotalScore = (contestantId) => {
  const contestantScores = props.criteria.map(criterion => 
    props.scores[`${contestantId}-${criterion.id}`] || 0
  )
  
  if (contestantScores.some(score => score === 0 || score === null || score === undefined)) return '-'
  
  const totalWeight = props.criteria.reduce((sum, criterion) => {
    const weight = criterion.weight || 1
    return sum + Math.max(weight, 0)
  }, 0)
  
  if (totalWeight === 0) return '-'
  
  const weightedSum = contestantScores.reduce((sum, score, index) => {
    const weight = props.criteria[index].weight || 1
    const safeWeight = Math.max(weight, 0)
    return sum + (score * safeWeight / Math.max(totalWeight, 1))
  }, 0)
  
  try {
    return Number(weightedSum).toFixed(1)
  } catch (error) {
    return '-'
  }
}

const getInputClass = (contestantId, criterion) => {
  const score = props.scores[`${contestantId}-${criterion.id}`]
  const hasValue = score !== undefined && score !== null && score !== ''
  
  if (!hasValue) {
    return 'border-slate-200 bg-slate-50 text-slate-400 focus:border-teal-400 focus:ring-teal-400/20'
  }
  
  const numScore = Number(score)
  const isValid = numScore >= criterion.min_score && numScore <= criterion.max_score
  
  if (!isValid) {
    return 'border-red-300 bg-red-50 text-red-600 focus:border-red-400 focus:ring-red-400/20'
  }
  
  // Gender-specific coloring
  if (props.gender === 'female') {
    return 'border-pink-200 bg-pink-50 text-pink-700 focus:border-pink-400 focus:ring-pink-400/20'
  } else if (props.gender === 'male') {
    return 'border-blue-200 bg-blue-50 text-blue-700 focus:border-blue-400 focus:ring-blue-400/20'
  }
  
  return 'border-teal-200 bg-teal-50 text-teal-700 focus:border-teal-400 focus:ring-teal-400/20'
}

const handleInput = (event, contestantId, criterionId, criterion) => {
  const value = event.target.value
  if (value === '') return
  
  const numValue = Number(value)
  if (!isNaN(numValue)) {
    emit('score-change', numValue, contestantId, criterionId, criterion)
  }
}

const handleBlur = (event, contestantId, criterionId, criterion) => {
  const value = event.target.value
  if (value === '' || value === null) {
    return
  }
  
  const numValue = Number(value)
  if (!isNaN(numValue)) {
    emit('score-change', numValue, contestantId, criterionId, criterion)
  }
}
</script>

<style scoped>
/* Remove spinner buttons from number input */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  appearance: textfield;
  -moz-appearance: textfield;
}

/* Custom scrollbar for table container */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
