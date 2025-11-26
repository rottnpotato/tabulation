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
            class="hover:bg-gray-50/80 transition-all duration-500 ease-out"
            :class="[
              {
                'bg-emerald-50/30': contestant.qualified && contestant.qualification_cutoff,
                'opacity-60': !contestant.qualified && contestant.qualification_cutoff !== null && contestant.qualification_cutoff !== undefined,
                'animate-rank-up': getRankChange(contestant.id, index + 1) === 'up',
                'animate-rank-down': getRankChange(contestant.id, index + 1) === 'down',
                'animate-pulse-subtle': isUpdating && getRankChange(contestant.id, index + 1) === 'same'
              }
            ]"
          >
            <!-- Rank -->
            <td class="whitespace-nowrap px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <div class="relative">
                  <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold transition-all duration-300"
                    :class="getRankBadgeClass(index + 1, contestant.qualified)"
                  >
                    <span class="mr-1 tabular-nums">{{ index + 1 }}</span>
                    <span v-if="showWinners && index < numberOfWinners">{{ getRankDisplay(index + 1) }}</span>
                    <span v-else-if="!showWinners && index < 3">{{ getRankDisplay(index + 1) }}</span>
                  </span>
                  <!-- Rank change indicator -->
                  <transition name="rank-indicator">
                    <span
                      v-if="isUpdating && getRankChange(contestant.id, index + 1) === 'up'"
                      class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-green-500 text-white text-[10px] font-bold shadow-lg"
                    >
                      ↑
                    </span>
                    <span
                      v-else-if="isUpdating && getRankChange(contestant.id, index + 1) === 'down'"
                      class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold shadow-lg"
                    >
                      ↓
                    </span>
                  </transition>
                </div>
                <span
                  v-if="contestant.qualified && contestant.qualification_cutoff"
                  class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 border border-emerald-200"
                  :title="`Qualified (Top ${contestant.qualification_cutoff})`"
                >
                  ✓
                </span>
              </div>
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
                  </p>
                  <p class="mt-0.5 text-xs text-gray-500">
                    <span v-if="contestant.is_pair && contestant.member_names && contestant.member_names.length > 0" class="italic">
                      {{ contestant.member_names.join(' & ') }} •
                    </span>
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
import { computed, useSlots, ref, watch } from 'vue'
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
  qualified?: boolean
  qualification_cutoff?: number | null
}

interface Props {
  title: string
  contestants: Contestant[]
  rounds: Round[]
  isUpdating?: boolean
  numberOfWinners?: number
  showWinners?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isUpdating: false,
  numberOfWinners: 3,
  showWinners: false
})

// Track previous rankings for animation
const previousRankMap = ref<Map<number, number>>(new Map())

// Helper function to safely convert values to numbers
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

const rankedContestants = computed(() => {
  return [...props.contestants].sort((a, b) => {
    const aTotal = toNumber(a.totalScore) ?? 0
    const bTotal = toNumber(b.totalScore) ?? 0
    return bTotal - aTotal
  })
})

// Watch for ranking changes and update previous map
watch(
  () => rankedContestants.value,
  (newRanked, oldRanked) => {
    if (oldRanked && oldRanked.length > 0) {
      // Store old rankings before update
      const oldMap = new Map<number, number>()
      oldRanked.forEach((c, idx) => {
        oldMap.set(c.id, idx + 1)
      })
      previousRankMap.value = oldMap
    }
  },
  { deep: true }
)

const getRankChange = (contestantId: number, currentRank: number): 'up' | 'down' | 'same' | 'new' => {
  const prevRank = previousRankMap.value.get(contestantId)
  
  if (prevRank === undefined) {
    return 'new'
  }
  
  if (prevRank > currentRank) {
    return 'up' // Moved to a better rank (lower number)
  } else if (prevRank < currentRank) {
    return 'down' // Moved to a worse rank (higher number)
  }
  
  return 'same'
}

const getRankDisplay = (rank: number): string => {
  if (props.showWinners && rank <= props.numberOfWinners) {
    // Show winning medals for final rounds
    if (rank === 1) return '👑'
    if (rank === 2) return '🥈'
    if (rank === 3) return '🥉'
    // For 4th place and beyond, show trophy
    return '🏆'
  }
  // For non-final rounds (advancing), show checkmark/advancing indicator
  if (rank <= 3) {
    return '✓'
  }
  return rank.toString()
}

const getRankBadgeClass = (rank: number, qualified?: boolean): string => {
  const isQualified = qualified !== false
  const isWinner = props.showWinners && rank <= props.numberOfWinners
  
  if (isWinner) {
    // Final round - show winning badges with gold/silver/bronze
    switch (rank) {
      case 1:
        return 'bg-yellow-100 text-yellow-900 border-2 border-yellow-400 shadow-lg ring-2 ring-yellow-200'
      case 2:
        return 'bg-gray-100 text-gray-800 border-2 border-gray-400 shadow-lg ring-2 ring-gray-200'
      case 3:
        return 'bg-orange-100 text-orange-800 border-2 border-orange-400 shadow-lg ring-2 ring-orange-200'
      default:
        // For 4th, 5th, etc. when numberOfWinners > 3
        return 'bg-purple-100 text-purple-800 border-2 border-purple-400 shadow-md ring-2 ring-purple-200'
    }
  }
  
  // Non-final rounds - show advancing badges with green/emerald for qualifiers
  switch (rank) {
    case 1:
      return 'bg-emerald-100 text-emerald-900 border-2 border-emerald-400 shadow-md ring-2 ring-emerald-200'
    case 2:
      return 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300 shadow-sm ring-2 ring-emerald-100'
    case 3:
      return 'bg-emerald-50 text-emerald-700 border-2 border-emerald-200 shadow-sm ring-2 ring-emerald-100'
    default:
      if (!isQualified) {
        return 'bg-slate-50 text-slate-400 border-2 border-slate-100'
      }
      return 'bg-emerald-50 text-emerald-700 border-2 border-emerald-200 shadow-sm ring-1 ring-emerald-100'
  }
}

const getScoreClass = (score: number | null | undefined): string => {
  const n = typeof score === 'number' && Number.isFinite(score) ? score : 0
  if (n >= 95) {
    return 'text-teal-700'
  }
  if (n >= 90) {
    return 'text-teal-600'
  }
  if (n >= 85) {
    return 'text-teal-500'
  }
  return 'text-slate-700'
}

const formatScore = (value: unknown, decimals = 2, empty = '-'): string => {
  const n = toNumber(value)
  if (n === null) {
    return empty
  }
  return n.toFixed(decimals)
}
</script>

<style scoped>
@keyframes rank-up {
  0% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
    background-color: rgba(34, 197, 94, 0.1);
  }
  100% {
    transform: translateY(0);
  }
}

@keyframes rank-down {
  0% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(8px);
    background-color: rgba(239, 68, 68, 0.1);
  }
  100% {
    transform: translateY(0);
  }
}

@keyframes pulse-subtle {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
    background-color: rgba(20, 184, 166, 0.05);
  }
}

.animate-rank-up {
  animation: rank-up 0.6s ease-out;
}

.animate-rank-down {
  animation: rank-down 0.6s ease-out;
}

.animate-pulse-subtle {
  animation: pulse-subtle 1s ease-in-out;
}

.rank-indicator-enter-active,
.rank-indicator-leave-active {
  transition: all 0.3s ease;
}

.rank-indicator-enter-from {
  opacity: 0;
  transform: scale(0) rotate(-180deg);
}

.rank-indicator-leave-to {
  opacity: 0;
  transform: scale(0) rotate(180deg);
}
</style>

```
