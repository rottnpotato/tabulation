<template>
  <div class="min-h-screen bg-slate-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-10 border border-teal-100">
        <!-- Abstract Background Pattern -->
        <div class="absolute inset-0">
          <div class="absolute inset-0 bg-gradient-to-br from-teal-50 via-teal-50/50 to-white opacity-90"></div>
          <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
          <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        </div>

        <div class="relative z-10 p-8 sm:p-10">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
              <h1 class="text-3xl sm:text-4xl font-bold tracking-tight font-display text-slate-900">
                {{ pageant ? capitalize(pageant.name) : 'Minor Awards' }}
              </h1>
              <p class="text-slate-600 text-lg max-w-2xl font-light flex items-center gap-2">
                <Award class="w-5 h-5 text-teal-500" />
                Special Recognitions & Awards
              </p>
            </div>
            
            <div v-if="pageant" class="flex flex-wrap gap-3">
              <Link :href="route('tabulator.minor-awards.print', pageant.id)"
                class="px-4 py-2 rounded-xl bg-white border border-teal-200 text-teal-700 text-sm font-semibold hover:bg-teal-50 transition-all flex items-center gap-2 shadow-sm hover:shadow-md">
                <Printer class="w-4 h-4" />
                <span>Print Awards</span>
              </Link>
              <Link :href="route('tabulator.results', pageant.id)"
                class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <Trophy class="w-4 h-4 text-teal-400" />
                <span>Back to Results</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Awards List -->
      <div class="space-y-8 animate-fade-in">
        <div v-for="(entry, idx) in roundEntries" :key="entry.round.id + '-' + idx" 
          class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative group hover:shadow-lg transition-all duration-500">
          
          <!-- Decorative background element -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-teal-50 to-transparent rounded-bl-full opacity-50 group-hover:scale-110 transition-transform duration-700"></div>

          <div class="relative px-8 py-6 border-b border-slate-50 bg-white/50 backdrop-blur-sm">
            <div class="flex items-center justify-center gap-3">
              <div class="h-px w-12 bg-gradient-to-r from-transparent to-teal-200"></div>
              <h2 class="text-xl font-bold text-center text-slate-800 tracking-wide font-display">
                Best in {{ entry.round.name }}
              </h2>
              <div class="h-px w-12 bg-gradient-to-l from-transparent to-teal-200"></div>
            </div>
          </div>

          <div class="p-8 sm:p-10 relative">
            <!-- For pair pageants, show separate male and female winners side by side -->
            <template v-if="entry.is_pair_pageant || isPairPageant">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                <!-- Male Winners Section -->
                <div v-if="entry.male_winners && entry.male_winners.length > 0" class="flex flex-col">
                  <div class="text-center mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-bold border border-blue-200">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100">♂</span>
                      Male Category
                    </div>
                  </div>
                  <div class="flex flex-col items-center gap-8">
                    <div v-for="winner in entry.male_winners" :key="winner.id" 
                      class="flex flex-col items-center text-center group/winner relative w-full">
                      
                      <!-- Winner Image -->
                      <div class="relative mb-6">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-200 to-blue-200 rounded-full blur-lg opacity-0 group-hover/winner:opacity-40 transition-opacity duration-500"></div>
                        <div class="relative">
                          <div class="p-1 rounded-full bg-gradient-to-br from-blue-100 to-blue-100 shadow-inner">
                            <img :src="winner.image" :alt="winner.name" 
                              class="h-28 w-28 rounded-full object-cover ring-4 ring-white shadow-md group-hover/winner:scale-105 transition-transform duration-500" />
                          </div>
                          <div class="absolute -bottom-2 -right-2 bg-slate-900 text-white text-sm rounded-full w-8 h-8 flex items-center justify-center font-bold shadow-lg border-2 border-white">
                            {{ winner.number }}
                          </div>
                        </div>
                      </div>
                      
                      <!-- Winner Details -->
                      <div class="space-y-2 min-w-[200px] relative z-10">
                        <h3 class="text-xl font-bold text-slate-900 leading-tight font-display">
                          <span class="text-blue-500 text-sm font-medium block mb-1 uppercase tracking-wider">{{ getTitle(winner) }}</span>
                          {{ capitalize(winner.name) }}
                        </h3>
                        <p v-if="winner.is_pair && winner.member_names && winner.member_names.length > 0" class="text-sm text-slate-500 italic">
                          {{ winner.member_names.map(name => capitalize(name)).join(' & ') }}
                        </p>
                        
                        <div class="pt-3 mt-2 border-t border-slate-100 w-24 mx-auto">
                          <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Score</div>
                          <div class="text-2xl font-light text-slate-900 font-display">{{ formatScore(winner.score) }}</div>
                        </div>
                      </div>
                  </div>
                </div>
              </div>                <!-- Female Winners Section -->
                <div v-if="entry.female_winners && entry.female_winners.length > 0" class="flex flex-col">
                  <div class="text-center mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-pink-50 text-pink-700 rounded-full text-sm font-bold border border-pink-200">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-pink-100">♀</span>
                      Female Category
                    </div>
                  </div>
                  <div class="flex flex-col items-center gap-8">
                    <div v-for="winner in entry.female_winners" :key="winner.id" 
                      class="flex flex-col items-center text-center group/winner relative w-full">
                      
                      <!-- Winner Image -->
                      <div class="relative mb-6">
                        <div class="absolute inset-0 bg-gradient-to-br from-pink-200 to-pink-200 rounded-full blur-lg opacity-0 group-hover/winner:opacity-40 transition-opacity duration-500"></div>
                        <div class="relative">
                          <div class="p-1 rounded-full bg-gradient-to-br from-pink-100 to-pink-100 shadow-inner">
                            <img :src="winner.image" :alt="winner.name" 
                              class="h-28 w-28 rounded-full object-cover ring-4 ring-white shadow-md group-hover/winner:scale-105 transition-transform duration-500" />
                          </div>
                          <div class="absolute -bottom-2 -right-2 bg-slate-900 text-white text-sm rounded-full w-8 h-8 flex items-center justify-center font-bold shadow-lg border-2 border-white">
                            {{ winner.number }}
                          </div>
                        </div>
                      </div>
                      
                      <!-- Winner Details -->
                      <div class="space-y-2 min-w-[200px] relative z-10">
                        <h3 class="text-xl font-bold text-slate-900 leading-tight font-display">
                          <span class="text-pink-500 text-sm font-medium block mb-1 uppercase tracking-wider">{{ getTitle(winner) }}</span>
                          {{ capitalize(winner.name) }}
                        </h3>
                        <p v-if="winner.is_pair && winner.member_names && winner.member_names.length > 0" class="text-sm text-slate-500 italic">
                          {{ winner.member_names.map(name => capitalize(name)).join(' & ') }}
                        </p>
                        
                        <div class="pt-3 mt-2 border-t border-slate-100 w-24 mx-auto">
                          <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Score</div>
                          <div class="text-2xl font-light text-slate-900 font-display">{{ formatScore(winner.score) }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Standard single winner display (non-pair pageants) -->
            <template v-else>
              <!-- Centered winners with elegant spacing -->
              <div class="flex flex-wrap justify-center gap-12">
                <div v-for="winner in entry.winners" :key="winner.id" 
                  class="flex flex-col items-center text-center group/winner relative">
                  
                  <!-- Winner Image -->
                  <div class="relative mb-6">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-200 to-teal-200 rounded-full blur-lg opacity-0 group-hover/winner:opacity-40 transition-opacity duration-500"></div>
                    <div class="relative">
                      <div class="p-1 rounded-full bg-gradient-to-br from-teal-100 to-teal-100 shadow-inner">
                        <img :src="winner.image" :alt="winner.name" 
                          class="h-28 w-28 rounded-full object-cover ring-4 ring-white shadow-md group-hover/winner:scale-105 transition-transform duration-500" />
                      </div>
                      <div class="absolute -bottom-2 -right-2 bg-slate-900 text-white text-sm rounded-full w-8 h-8 flex items-center justify-center font-bold shadow-lg border-2 border-white">
                        {{ winner.number }}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Winner Details -->
                  <div class="space-y-2 min-w-[200px] relative z-10">
                    <h3 class="text-xl font-bold text-slate-900 leading-tight font-display">
                      <span class="text-teal-500 text-sm font-medium block mb-1 uppercase tracking-wider">{{ getTitle(winner) }}</span>
                      {{ capitalize(winner.name) }}
                    </h3>
                    <p v-if="winner.is_pair && winner.member_names && winner.member_names.length > 0" class="text-sm text-slate-500 italic">
                      {{ winner.member_names.map(name => capitalize(name)).join(' & ') }}
                    </p>
                    
                    <div class="pt-3 mt-2 border-t border-slate-100 w-24 mx-auto">
                      <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Score</div>
                      <div class="text-2xl font-light text-slate-900 font-display">{{ formatScore(winner.score) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div v-if="roundEntries.length === 0" class="text-center py-20">
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-12 max-w-xl mx-auto">
            <div class="w-16 h-16 bg-teal-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Award class="h-8 w-8 text-teal-300" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No Minor Awards Yet</h3>
            <p class="text-slate-500">
              Awards will appear here automatically once semi-final round scoring is complete.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Printer, Trophy, Award } from 'lucide-vue-next'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface PageantInfo {
  id: number
  name: string
  contestant_type?: string
}

interface RoundInfo {
  id: number
  name: string
  type?: string
  weight?: number
}

interface WinnerInfo {
  id: number
  number: number
  name: string
  gender?: string
  is_pair?: boolean
  member_names?: string[]
  member_genders?: string[]
  image: string
  score: number
}

interface RoundEntry {
  round: RoundInfo
  winners: WinnerInfo[]
  is_pair_pageant?: boolean
  male_winners?: WinnerInfo[]
  female_winners?: WinnerInfo[]
}

interface Props {
  pageant?: PageantInfo
  awardsByRound: Record<string, RoundEntry>
}

const props = defineProps<Props>()

const roundEntries = computed(() => {
  if (!props.awardsByRound) return []
  return Object.values(props.awardsByRound)
})

const isPairPageant = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

const capitalize = (text: string): string => {
  if (!text) return ''
  return text.toLowerCase().split(' ').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatScore = (score: number): string => {
  return score.toFixed(2)
}

const getTitle = (winner: WinnerInfo): string => {
  if (winner.is_pair && winner.member_genders && winner.member_genders.length > 0) {
    return winner.member_genders.map(g => g === 'male' ? 'Mr' : 'Miss').join(' & ')
  }
  if (winner.gender === 'male') return 'Mr'
  if (winner.gender === 'female') return 'Miss'
  return ''
}

// WebSocket handling for real-time updates
let echoChannel: any = null

onMounted(() => {
  if (!props.pageant) {
    console.log('⚠️ No pageant selected, skipping WebSocket subscription')
    return
  }

  if (typeof window === 'undefined' || !window.Echo) {
    console.error('❌ Laravel Echo not available')
    return
  }

  const channelName = `pageant.${props.pageant.id}`
  console.log('🔌 MinorAwards page subscribing to channel:', channelName)

  // Subscribe to the pageant channel for real-time updates
  echoChannel = window.Echo.private(channelName)
    .listen('ScoreUpdated', (e: any) => {
      console.log('🔔 ScoreUpdated event received on MinorAwards:', e)
      // Refresh awards when scores are updated
      router.reload({ only: ['awardsByRound'] })
    })
    .listen('RankingsUpdated', (e: any) => {
      console.log('🏆 RankingsUpdated event received on MinorAwards:', e)
      // Refresh awards when rankings change
      router.reload({ only: ['awardsByRound'] })
    })
  
  console.log('✅ Successfully subscribed to minor awards updates')
})

onUnmounted(() => {
  if (echoChannel && props.pageant) {
    const channelName = `pageant.${props.pageant.id}`
    console.log('🔌 Unsubscribing from channel:', channelName)
    window.Echo.leave(channelName)
    echoChannel = null
  }
})
</script>

<style scoped>
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
