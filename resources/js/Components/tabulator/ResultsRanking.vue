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

    <!-- Results List -->
    <div class="divide-y divide-gray-200">
      <div 
        v-for="(contestant, index) in rankedContestants" 
        :key="contestant.id"
        class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200"
      >
        <div class="flex items-center justify-between">
          <!-- Rank and Contestant Info -->
          <div class="flex items-center space-x-4">
            <!-- Rank Badge -->
            <div class="flex-shrink-0">
              <div 
                class="flex items-center justify-center w-12 h-12 rounded-full font-bold text-lg"
                :class="getRankBadgeClass(index + 1)"
              >
                {{ getRankDisplay(index + 1) }}
              </div>
            </div>

            <!-- Contestant Photo and Details -->
            <div class="flex items-center space-x-3">
              <img 
                :src="contestant.image" 
                :alt="contestant.name"
                class="h-12 w-12 rounded-full object-cover border-2 border-gray-200"
              />
              <div>
                <p class="font-semibold text-gray-900">{{ contestant.name }}</p>
                <p class="text-sm text-gray-500">#{{ contestant.number }} • {{ contestant.region }}</p>
              </div>
            </div>
          </div>

          <!-- Scores -->
          <div class="flex items-center space-x-6">
            <!-- Individual Round Scores -->
            <div class="hidden md:flex items-center space-x-3">
              <div 
                v-for="round in rounds" 
                :key="round.id"
                class="text-center"
              >
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ round.name }}</p>
                <p class="font-semibold text-gray-900">
                  {{ formatScore(contestant.scores[round.name] || 0) }}
                </p>
              </div>
            </div>

            <!-- Total Score -->
            <div class="text-center">
              <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
              <p class="text-2xl font-bold" :class="getScoreClass(contestant.totalScore)">
                {{ formatScore(contestant.totalScore) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Mobile Round Scores -->
        <div class="mt-4 md:hidden">
          <div class="grid grid-cols-2 gap-3">
            <div 
              v-for="round in rounds" 
              :key="round.id"
              class="text-center bg-gray-50 rounded-lg py-2"
            >
              <p class="text-xs text-gray-500 uppercase tracking-wide">{{ round.name }}</p>
              <p class="font-semibold text-gray-900">
                {{ formatScore(contestant.scores[round.name] || 0) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="contestants.length === 0" class="text-center py-12">
      <div class="text-gray-500">
        <Trophy class="mx-auto h-12 w-12 text-gray-400" />
        <p class="mt-2 text-lg font-medium">No Results Available</p>
        <p class="text-sm">Results will appear here once scoring is complete.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useSlots } from 'vue'
import { Trophy, Crown, Medal, Award } from 'lucide-vue-next'

const slots = useSlots()

interface Round {
  id: number
  name: string
  weight: number
}

interface Contestant {
  id: number
  name: string
  number: number
  region?: string
  image: string
  scores: Record<string, number>
  totalScore: number
}

interface Props {
  title: string
  contestants: Contestant[]
  rounds: Round[]
}

const props = defineProps<Props>()

const rankedContestants = computed(() => {
  return [...props.contestants].sort((a, b) => {
    const aTotal = toNumber(a.totalScore) ?? 0
    const bTotal = toNumber(b.totalScore) ?? 0
    return bTotal - aTotal
  })
})

const getRankDisplay = (rank: number): string => {
  if (rank <= 3) {
    return ['👑', '🥈', '🥉'][rank - 1]
  }
  return rank.toString()
}

const getRankBadgeClass = (rank: number): string => {
  switch (rank) {
    case 1:
      return 'bg-yellow-100 text-yellow-800 border-2 border-yellow-300'
    case 2:
      return 'bg-gray-100 text-gray-800 border-2 border-gray-300'
    case 3:
      return 'bg-orange-100 text-orange-800 border-2 border-orange-300'
    default:
      return 'bg-blue-100 text-blue-800 border-2 border-blue-200'
  }
}

const getScoreClass = (score: number | null | undefined): string => {
  const n = typeof score === 'number' && Number.isFinite(score) ? score : 0
  if (n >= 95) {
    return 'text-green-600'
  }
  if (n >= 90) {
    return 'text-blue-600'
  }
  if (n >= 85) {
    return 'text-yellow-600'
  }
  return 'text-gray-900'
}

const toNumber = (value: unknown): number | null => {
  if (value === null || value === undefined) {
    return null
  }
  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : null
  }
  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (trimmed.length === 0) {
      return null
    }
    const parsed = Number(trimmed)
    return Number.isFinite(parsed) ? parsed : null
  }
  return null
}

const formatScore = (score: unknown, decimals = 2, empty = '-'): string => {
  const n = toNumber(score)
  if (n === null) {
    return empty
  }
  return n.toFixed(decimals)
}
</script>
