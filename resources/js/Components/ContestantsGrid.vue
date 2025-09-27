<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    <div 
      v-for="contestant in contestants" 
      :key="contestant.id" 
      :data-contestant-id="contestant.id"
      class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group"
    >
      <div class="relative aspect-[4/5]">
        <img 
          :src="contestant.photo || '/images/placeholders/placeholder-contestant.jpg'" 
          :alt="contestant.name" 
          class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          @error="handleImageError"
          @load="handleImageLoad"
          loading="lazy"
        />
        <div class="absolute inset-0 bg-gray-200 flex items-center justify-center" v-if="imageLoadingStates[contestant.id] === 'error'">
          <div class="text-center text-gray-500">
            <Users class="h-8 w-8 mx-auto mb-2" />
            <span class="text-sm">No Image</span>
          </div>
        </div>
        <!-- Gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-70"></div>

        <!-- Contestant number badge -->
        <div class="absolute top-3 right-3">
          <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white text-sm font-bold shadow-md">
            #{{ displayNumber(contestant) }}
          </span>
        </div>
        <!-- Pair badge -->
        <div v-if="contestant.is_pair" class="absolute top-3 left-3">
          <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[11px] font-semibold shadow-md">
            Pair
          </span>
        </div>

        <!-- Action buttons overlay - visible on hover -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
          <Tooltip text="View detailed information, photos, and bio" position="top">
            <button 
              @click="$emit('view', contestant)" 
              class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-600 transition-colors flex items-center gap-2 shadow-lg transform hover:scale-105"
            >
              <Eye class="h-4 w-4" />
              View Details
            </button>
          </Tooltip>
          <Tooltip text="Edit contestant information and photos" position="top">
            <button 
              @click="$emit('edit', contestant)" 
              class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-white/30 transition-colors flex items-center gap-2 border border-white/30 transform hover:scale-105"
            >
              <Edit class="h-4 w-4" />
              Edit
            </button>
          </Tooltip>
          <Tooltip text="Permanently remove contestant from pageant" position="top">
            <button 
              @click="$emit('delete', contestant)" 
              class="bg-red-500/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-500/30 transition-colors flex items-center gap-2 border border-red-500/30 transform hover:scale-105"
            >
              <Trash2 class="h-4 w-4" />
              Delete
            </button>
          </Tooltip>
        </div>

        <!-- Contestant info at bottom -->
        <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
          <h3 class="text-lg font-bold truncate">{{ contestant.name }}</h3>
          <p v-if="contestant.is_pair && contestant.members_text" class="text-xs text-emerald-200 mt-0.5 truncate">{{ contestant.members_text }}</p>
          <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
            <div class="flex items-center text-sm text-orange-100">
              <MapPin class="h-3.5 w-3.5 mr-1 text-orange-300" />
              <span class="truncate">{{ (contestant.origin || contestant.city) || 'No location' }}</span>
            </div>
            <div class="flex items-center text-sm text-orange-100">
              <Calendar class="h-3.5 w-3.5 mr-1 text-orange-300" />
              <span>{{ contestant.age ? `${contestant.age} years` : 'Age not specified' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Users, Eye, Edit, Trash2, MapPin, Calendar } from 'lucide-vue-next'
import Tooltip from '@/Components/Tooltip.vue'

defineProps({
  contestants: { type: Array, required: true },
})

defineEmits(['view', 'edit', 'delete'])

const imageLoadingStates = ref({})

const handleImageError = (event) => {
  const img = event.target
  const contestantId = img.closest('[data-contestant-id]')?.dataset.contestantId
  if (contestantId) {
    imageLoadingStates.value[contestantId] = 'error'
  }
  img.src = '/images/placeholders/placeholder-contestant.jpg'
}

const handleImageLoad = (event) => {
  const img = event.target
  const contestantId = img.closest('[data-contestant-id]')?.dataset.contestantId
  if (contestantId) {
    imageLoadingStates.value[contestantId] = 'loaded'
  }
}

const displayNumber = (contestant) => contestant.number ?? contestant.contestNumber
</script>

<style scoped>
</style>


