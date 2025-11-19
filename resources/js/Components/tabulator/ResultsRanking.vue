<template>
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4">
      <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
      <div v-if="slots.actions" class="flex items-center space-x-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Results Table -->
    <div v-if="contestants.length" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Rank
            </th>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Contestant
            </th>
            <th
              v-for="round in rounds"
              :key="round.id"
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              {{ round.name }}
            </th>
            <th
              scope="col"
              class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Total
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr
            v-for="(contestant, index) in rankedContestants"
            :key="contestant.id"
            class="hover:bg-gray-50/80"
          >
            <!-- Rank -->
            <td class="whitespace-nowrap px-4 py-3 text-center">
              <span
                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                :class="getRankBadgeClass(index + 1)"
              >
                <span class="mr-1 tabular-nums">{{ index + 1 }}</span>
                <span v-if="index < 3">{{ getRankDisplay(index + 1) }}</span>
              </span>
            </td>

            <!-- Contestant Info -->
            <td class="whitespace-nowrap px-4 py-3">
              <div class="flex items-center gap-3">
                <img
                  :src="contestant.image"
                  :alt="contestant.name"
                  class="h-10 w-10 flex-shrink-0 rounded-full border border-gray-200 object-cover"
                />
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-gray-900">
                    {{ contestant.name }}
                    <span
                      v-if="contestant.is_pair && contestant.member_genders && contestant.member_genders.length > 0"
                      class="text-xs font-normal text-gray-500"
                    >
                      ({{ contestant.member_genders.map(g => g === 'male' ? 'Mr' : 'Ms').join(' & ') }})
                    </span>
                  </p>
                  <p class="mt-0.5 text-xs text-gray-500">
                    #{{ contestant.number }}
                    <span v-if="contestant.region"> • {{ contestant.region }}</span>
                  </p>
                </div>
              </div>
            </td>

            <!-- Round Scores -->
            <td
              v-for="round in rounds"
              :key="round.id"
              class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium tabular-nums text-gray-900"
            >
              {{ formatScore(contestant.scores[round.name] || 0) }}
            </td>

            <!-- Total Score -->
            <td class="whitespace-nowrap px-4 py-3 text-right">
              <span
                class="text-sm font-semibold tabular-nums"
                :class="getScoreClass(contestant.totalScore)"
              >
                {{ formatScore(contestant.totalScore) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="py-12 text-center">
      <div class="text-gray-500">
        <Trophy class="mx-auto h-12 w-12 text-gray-400" />
        <p class="mt-2 text-lg font-medium">No results available</p>
        <p class="text-sm">Results will appear here once scoring is complete.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useSlots } from 'vue'
import { Trophy } from 'lucide-vue-next'

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
  gender?: string
  is_pair?: boolean
  member_names?: string[]
  member_genders?: string[]
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
      return 'bg-indigo-100 text-indigo-800 border-2 border-indigo-300'
    case 2:
      return 'bg-blue-100 text-blue-800 border-2 border-blue-300'
    case 3:
      return 'bg-sky-100 text-sky-800 border-2 border-sky-300'
    default:
      return 'bg-slate-100 text-slate-800 border-2 border-slate-200'
  }
}

const getScoreClass = (score: number | null | undefined): string => {
  const n = typeof score === 'number' && Number.isFinite(score) ? score : 0
  if (n >= 95) {
    return 'text-indigo-700'
  }
  if (n >= 90) {
    return 'text-indigo-600'
  }
  if (n >= 85) {
    return 'text-blue-600'
  }
  return 'text-slate-700'
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
