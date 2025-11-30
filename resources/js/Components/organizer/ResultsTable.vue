<template>
  <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-200">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-slate-50">
          <tr>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Rank
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              #
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Photo
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Name
            </th>
            <th v-if="showAllRounds" v-for="round in rounds" :key="round.id" 
                scope="col" 
                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ round.name }}
            </th>
            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-teal-50">
              Final Score
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(contestant, index) in results" :key="contestant.id" 
              :class="[
                index < 3 ? 'bg-amber-50/30' : '',
                'hover:bg-slate-50 transition-colors'
              ]">
            <!-- Rank -->
            <td class="px-4 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <span v-if="index === 0" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-800 font-bold text-sm">
                  🥇
                </span>
                <span v-else-if="index === 1" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-800 font-bold text-sm">
                  🥈
                </span>
                <span v-else-if="index === 2" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800 font-bold text-sm">
                  🥉
                </span>
                <span v-else class="text-sm font-medium text-gray-900">
                  {{ contestant.rank || index + 1 }}
                </span>
              </div>
            </td>
            
            <!-- Number -->
            <td class="px-4 py-4 whitespace-nowrap">
              <div class="text-sm font-bold text-teal-600">
                #{{ contestant.number }}
              </div>
            </td>
            
            <!-- Photo -->
            <td class="px-4 py-4 whitespace-nowrap">
              <img :src="getImageUrl(contestant.image)" 
                   :alt="contestant.name"
                   class="h-12 w-12 rounded-full object-cover border-2 border-slate-200" />
            </td>
            
            <!-- Name -->
            <td class="px-4 py-4">
              <div class="text-sm font-medium text-gray-900">
                {{ contestant.name }}
              </div>
              <div v-if="contestant.is_pair && contestant.member_names && contestant.member_names.length > 0" 
                   class="text-xs text-gray-500 mt-1">
                <div v-for="(memberName, idx) in contestant.member_names" :key="idx">
                  {{ memberName }}
                  <span v-if="contestant.member_genders && contestant.member_genders[idx]" 
                        class="ml-1 px-1.5 py-0.5 rounded text-xs"
                        :class="contestant.member_genders[idx] === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'">
                    {{ contestant.member_genders[idx] === 'male' ? 'M' : 'F' }}
                  </span>
                </div>
              </div>
            </td>
            
            <!-- Round Scores (if showing all rounds) -->
            <td v-if="showAllRounds" v-for="round in rounds" :key="`${contestant.id}-${round.id}`" 
                class="px-4 py-4 text-center whitespace-nowrap">
              <span v-if="contestant.scores && contestant.scores[round.name] !== null && contestant.scores[round.name] !== undefined" 
                    class="text-sm text-gray-900">
                {{ contestant.scores[round.name].toFixed(2) }}
              </span>
              <span v-else class="text-xs text-gray-400">-</span>
            </td>
            
            <!-- Final Score -->
            <td class="px-4 py-4 text-center whitespace-nowrap bg-teal-50/50">
              <span class="text-sm font-bold text-teal-700">
                {{ formatScore(contestant.final_score) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Empty State -->
    <div v-if="!results || results.length === 0" class="text-center py-12">
      <p class="text-gray-500">No results available</p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  results: {
    type: Array,
    required: true
  },
  judges: {
    type: Array,
    default: () => []
  },
  rounds: {
    type: Array,
    default: () => []
  },
  isMaleCategory: {
    type: Boolean,
    default: false
  },
  isFemaleCategory: {
    type: Boolean,
    default: false
  },
  showAllRounds: {
    type: Boolean,
    default: false
  }
})

const getImageUrl = (imagePath) => {
  if (!imagePath || imagePath === '/images/placeholders/contestant-placeholder.jpg') {
    return '/images/placeholders/contestant-placeholder.jpg'
  }
  
  if (imagePath.startsWith('http') || imagePath.startsWith('//') || imagePath.startsWith('/')) {
    return imagePath
  }
  
  return `/storage/${imagePath}`
}

const formatScore = (score) => {
  if (score === null || score === undefined) return '-'
  return typeof score === 'number' ? score.toFixed(2) : score
}
</script>
