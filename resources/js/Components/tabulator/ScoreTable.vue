<template>
  <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
        <div v-if="slots.actions" class="flex items-center space-x-2">
          <slot name="actions" />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Contestant
            </th>
            <th 
              v-for="judge in judges" 
              :key="judge.id"
              scope="col" 
              class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              {{ judge.name }}
            </th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Average
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="contestant in contestants" :key="contestant.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0">
                  <img
                    :src="contestant.image"
                    :alt="contestant.name"
                    class="h-10 w-10 rounded-full object-cover"
                  />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ contestant.name }}
                  </div>
                  <div v-if="contestant.is_pair && contestant.members_text" class="text-xs text-gray-500">
                    {{ contestant.members_text }}
                  </div>
                  <div class="text-sm text-gray-500">
                    #{{ contestant.number }}
                  </div>
                </div>
              </div>
            </td>
            <td
              v-for="judge in judges"
              :key="`${contestant.id}-${judge.id}`"
              class="px-6 py-4 whitespace-nowrap text-center"
            >
              <div
                class="text-sm font-medium"
                :class="getScoreClass(getScore(contestant.id, judge.id))"
              >
                {{ getScore(contestant.id, judge.id) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <div class="text-sm font-semibold text-gray-900">
                {{ calculateAverage(contestant.id) }}
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="contestants.length === 0" class="text-center py-12">
      <div class="text-gray-500">
        <Users class="mx-auto h-12 w-12 text-gray-400" />
        <p class="mt-2 text-lg font-medium">{{ emptyTitle || 'No data available' }}</p>
        <p class="text-sm">{{ emptyMessage || 'No contestants or scores to display.' }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useSlots } from 'vue'
import { Users } from 'lucide-vue-next'

const slots = useSlots()

interface Contestant {
  id: number
  name: string
  number: number
  image: string
  is_pair?: boolean
  members_text?: string
}

interface Judge {
  id: number
  name: string
}

interface Props {
  title: string
  contestants: Contestant[]
  judges: Judge[]
  scores: Map<string, number> | Record<string, number>
  emptyTitle?: string
  emptyMessage?: string
  scoreKey?: string // for generating score keys like 'contestantId-judgeId-roundId'
}

const props = withDefaults(defineProps<Props>(), {
  scoreKey: ''
})

const getScore = (contestantId: number, judgeId: number): string => {
  const key = props.scoreKey 
    ? `${contestantId}-${judgeId}-${props.scoreKey}`
    : `${contestantId}-${judgeId}`
  
  if (props.scores instanceof Map) {
    return props.scores.get(key)?.toString() || '-'
  }
  
  return props.scores[key]?.toString() || '-'
}

const calculateAverage = (contestantId: number): string => {
  const contestantScores: number[] = []
  
  props.judges.forEach(judge => {
    const key = props.scoreKey 
      ? `${contestantId}-${judge.id}-${props.scoreKey}`
      : `${contestantId}-${judge.id}`
      
    let score: number | undefined
    if (props.scores instanceof Map) {
      score = props.scores.get(key)
    } else {
      score = props.scores[key]
    }
    
    if (score !== undefined && score !== null) {
      contestantScores.push(score)
    }
  })
  
  if (contestantScores.length === 0) return '-'
  
  const sum = contestantScores.reduce((acc, score) => acc + score, 0)
  return (sum / contestantScores.length).toFixed(2)
}

const getScoreClass = (score: string): string => {
  if (score === '-') return 'text-gray-400'
  
  const numScore = parseFloat(score)
  if (numScore >= 95) return 'text-teal-700 font-bold'
  if (numScore >= 90) return 'text-teal-600 font-semibold'
  if (numScore >= 85) return 'text-teal-600 font-medium'
  if (numScore >= 80) return 'text-teal-500'
  return 'text-slate-500'
}
</script>
