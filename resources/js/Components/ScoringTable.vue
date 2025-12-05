<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Table Container with horizontal scroll for mobile -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[800px]">
        <!-- Table Header -->
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <!-- Candidate Column -->
            <th class="text-left py-3 px-4 font-bold text-xs uppercase tracking-wider text-slate-600 sticky left-0 bg-slate-50 z-10 min-w-[180px]">
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
            v-for="(contestant, index) in contestants" 
            :key="contestant.id"
            class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors group"
            :class="[
              isContestantComplete(contestant.id) ? 'bg-emerald-50/30' : '',
              index % 2 === 0 ? 'bg-white' : 'bg-slate-50/30'
            ]"
          >
            <!-- Candidate Info -->
            <td class="py-3 px-4 sticky left-0 z-10 transition-colors"
                :class="[
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
                    <span class="font-medium text-slate-800 text-sm truncate max-w-[100px]" :title="contestant.name">
                      {{ contestant.name }}
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
                  :disabled="!canEditScores"
                  @input="handleInput($event, contestant.id, criterion.id, criterion)"
                  @blur="handleBlur($event, contestant.id, criterion.id, criterion)"
                  class="w-20 text-center py-2 px-1 rounded-lg border-2 font-bold text-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="getInputClass(contestant.id, criterion)"
                  :placeholder="`${criterion.min_score}-${criterion.max_score}`"
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
                <!-- Status Badge -->
                <span 
                  class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide"
                  :class="isContestantComplete(contestant.id) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="isContestantComplete(contestant.id) ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                  {{ isContestantComplete(contestant.id) ? 'Complete' : 'Pending' }}
                </span>
                
                <!-- Save Button -->
                <button 
                  v-if="canEditScores && isContestantComplete(contestant.id)"
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
import { computed } from 'vue'

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
