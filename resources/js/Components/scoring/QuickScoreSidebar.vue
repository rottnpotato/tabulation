<template>
  <aside class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 lg:p-5 sticky top-24">
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center gap-3">
        <div v-if="contestant" class="relative h-12 w-12 rounded-lg overflow-hidden border border-gray-200">
          <img :src="contestant.image" :alt="contestant.name" class="h-full w-full object-cover" />
        </div>
        <div>
          <div class="text-sm text-gray-500">Focused Contestant</div>
          <div class="font-semibold text-gray-900" v-if="contestant">{{ contestant.name }}</div>
          <div class="text-xs text-gray-500" v-if="contestant">#{{ contestant.number }}</div>
        </div>
      </div>
      <button
        v-if="contestant"
        type="button"
        class="px-2.5 py-1.5 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
        @click="$emit('clear-focus')"
      >
        Clear
      </button>
    </div>

    <div v-if="!contestant" class="text-sm text-gray-600">
      Select a contestant to start quick scoring.
    </div>

    <template v-else>
      <!-- Average -->
      <div class="mb-4">
        <div class="text-xs text-gray-500">Weighted Average</div>
        <div class="mt-1 inline-flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-md px-3 py-2">
          <span class="text-lg font-semibold" :class="getAverageScoreColor(average)">{{ average }}</span>
          <span class="text-xs text-gray-500">pts</span>
        </div>
      </div>

      <!-- Criteria list -->
      <div class="space-y-3">
        <div
          v-for="criterion in criteria"
          :key="criterion.id"
          class="p-3 rounded-lg border border-gray-200 hover:bg-teal-50/40 transition-colors"
        >
          <div class="flex items-center justify-between mb-2">
            <div>
              <div class="text-sm font-medium text-gray-900">{{ criterion.name }}</div>
              <div class="text-xs text-gray-500">{{ criterion.min_score }}-{{ criterion.max_score }} pts</div>
            </div>
          </div>
          <ScoreInput
            :min="Number(criterion.min_score)"
            :max="Number(criterion.max_score)"
            :step="criterion.allow_decimals ? 0.1 : 1"
            :allow-decimals="criterion.allow_decimals"
            :decimal-places="criterion.decimal_places || 1"
            :disabled="!canEdit"
            :model-value="getScore(contestant!.id, criterion.id)"
            @change="onChangeScore($event, contestant!.id, criterion.id, criterion)"
          />
        </div>
      </div>

      <!-- Notes -->
      <div class="mt-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
        <textarea
          :value="note"
          @input="$emit('update-note', ($event.target as HTMLTextAreaElement).value)"
          rows="2"
          :disabled="!canEdit"
          class="w-full rounded-lg border border-gray-300 focus:border-teal-500 focus:ring-teal-500 text-sm resize-none disabled:opacity-50 disabled:bg-gray-100"
          placeholder="Comments or observations for this contestant..."
        ></textarea>
      </div>

      <!-- Submit -->
      <div class="mt-4">
        <button
          type="button"
          class="w-full bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!canEdit || !isComplete"
          @click="$emit('submit')"
        >
          Submit Scores
        </button>
        <div class="mt-2 text-xs text-gray-500" v-if="!isComplete">Fill all criteria to enable submit.</div>
      </div>
    </template>
  </aside>
  
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ScoreInput from '../ScoreInput.vue'

interface Criterion {
  id: number
  name: string
  min_score: number
  max_score: number
  weight: number
  allow_decimals?: boolean
  decimal_places?: number
}

interface Contestant {
  id: number
  name: string
  number: number
  image?: string
}

const props = defineProps<{
  contestant: Contestant | null
  criteria: Criterion[]
  // Scores object keyed as `${contestantId}-${criterionId}` -> number
  scores: Record<string, number | undefined>
  canEdit: boolean
  note?: string
}>()

const emit = defineEmits<{
  (e: 'update-score', value: number, contestantId: number, criterionId: number, criterion: Criterion): void
  (e: 'update-note', value: string): void
  (e: 'submit'): void
  (e: 'clear-focus'): void
}>()

const keyFor = (contestantId: number, criterionId: number): string => `${contestantId}-${criterionId}`

const getScore = (contestantId: number, criterionId: number): number | undefined => {
  const v = props.scores[keyFor(contestantId, criterionId)]
  return typeof v === 'number' ? v : undefined
}

const onScoreChange = (value: number, contestantId: number, criterionId: number, criterion: Criterion): void => {
  emit('update-score', value, contestantId, criterionId, criterion)
}

const onChangeScore = (value: number, contestantId: number, criterionId: number, criterion: Criterion): void => {
  onScoreChange(value, contestantId, criterionId, criterion)
}

const isComplete = computed<boolean>(() => {
  if (!props.contestant) {
    return false
  }
  return props.criteria.every(c => props.scores[keyFor(props.contestant!.id, c.id)] !== undefined && props.scores[keyFor(props.contestant!.id, c.id)] !== null)
})

const average = computed<string>(() => {
  if (!props.contestant) {
    return '-'
  }
  const vals = props.criteria.map(c => props.scores[keyFor(props.contestant!.id, c.id)] || 0)
  if (vals.some(x => x === 0)) {
    return '-'
  }
  const weighted = vals.reduce((sum, score, i) => sum + score * (props.criteria[i].weight / 100), 0)
  return weighted.toFixed(1)
})

const getAverageScoreColor = (score: string): string => {
  if (score === '-') return 'text-gray-500'
  const n = parseFloat(score)
  if (n >= 90) return 'text-emerald-600'
  if (n >= 80) return 'text-teal-600'
  if (n >= 70) return 'text-amber-600'
  if (n >= 60) return 'text-orange-600'
  return 'text-red-600'
}
</script>

<style scoped>
/* Light theme only per request */
</style>
