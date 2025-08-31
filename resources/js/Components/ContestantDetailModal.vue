<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-30">
      <TransitionChild
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-5xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
              <div class="relative grid grid-cols-1 md:grid-cols-5 min-h-[650px]">
                <!-- Close button -->
                <button 
                  @click="closeModal" 
                  class="absolute top-5 right-5 z-10 text-white hover:text-gray-200 transition-colors bg-black/40 hover:bg-black/50 rounded-full p-1.5 shadow-lg"
                >
                  <XCircle class="h-6 w-6" />
                </button>

                <!-- Left Column - Images Carousel (2 cols on md screens) -->
                <div class="relative md:col-span-2 lg:col-span-2 bg-gray-100 overflow-hidden">
                  <!-- Gradient overlay to add a bit of visual interest --> 
                  <div class="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-pink-500/20 mix-blend-multiply"/>
                  
                  <!-- Contestant number badge -->
                  <div class="absolute top-5 left-5 z-10">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-full h-14 w-14 flex items-center justify-center shadow-lg">
                      <div class="text-center">
                        <div class="text-xs uppercase tracking-wide font-medium opacity-80">No.</div>
                        <div class="text-xl font-bold leading-tight">{{ contestant?.number || '?' }}</div>
                      </div>
                    </div>
                  </div>
                    
                    <!-- Image Carousel -->
                    <div class="relative h-full w-full overflow-hidden" 
                      @touchstart="handleTouchStart" 
                      @touchmove="handleTouchMove" 
                      @touchend="handleTouchEnd">
                      <TransitionGroup 
                        tag="div"
                        class="h-full w-full"
                        enter-active-class="transition duration-500 ease-out"
                        enter-from-class="transform translate-x-full opacity-0"
                        enter-to-class="transform translate-x-0 opacity-100"
                        leave-active-class="transition duration-500 ease-in absolute"
                        leave-from-class="transform translate-x-0 opacity-100"
                        leave-to-class="transform -translate-x-full opacity-0"
                      >
                        <div 
                          v-for="(image, index) in contestant?.images" 
                          :key="image.id"
                          v-show="currentImageIndex === index" 
                          class="h-full w-full"
                        >
                          <img 
                            :src="image.path" 
                          :alt="`${contestant?.name || 'Contestant'} - Photo ${index + 1}`" 
                            class="h-full w-full object-cover"
                          />
                        </div>
                      </TransitionGroup>
                    
                    <!-- If no images, show a placeholder -->
                    <div v-if="!contestant?.images?.length" class="h-full flex items-center justify-center">
                      <div class="text-center p-6">
                        <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                          <User class="h-12 w-12 text-purple-300" />
                        </div>
                        <p class="text-gray-500">No photos available</p>
                      </div>
                    </div>
                      
                      <!-- Navigation Arrows -->
                      <div v-if="contestant?.images?.length > 1" class="absolute inset-0 flex items-center justify-between px-4">
                        <button 
                          @click="prevImage" 
                        class="rounded-full bg-black/30 p-2 text-white hover:bg-black/50 transition-colors hover:scale-105 transform"
                        >
                          <ChevronLeft class="h-6 w-6" />
                        </button>
                        <button 
                          @click="nextImage" 
                        class="rounded-full bg-black/30 p-2 text-white hover:bg-black/50 transition-colors hover:scale-105 transform"
                        >
                          <ChevronRight class="h-6 w-6" />
                        </button>
                      </div>
                      
                      <!-- Pagination Dots -->
                      <div 
                        v-if="contestant?.images?.length > 1" 
                      class="absolute bottom-5 left-0 right-0 flex justify-center gap-2"
                      >
                        <button 
                          v-for="(_, index) in contestant?.images" 
                          :key="index"
                          @click="currentImageIndex = index"
                          :class="[
                          'w-2.5 h-2.5 rounded-full transition-all duration-300 ease-in-out transform', 
                            currentImageIndex === index ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'
                          ]"
                        ></button>
                      </div>
                    </div>
                  </div>
                  
                <!-- Right Column - Contestant Details (3 cols on md screens) -->
                <div class="md:col-span-3 p-8 flex flex-col overflow-hidden">
                    <div class="flex-grow">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2 font-serif">
                      {{ contestant?.name || 'Contestant Details' }}
                      </h2>
                      
                    <div class="flex flex-wrap items-center gap-3 mb-6 text-gray-600">
                      <div v-if="contestant?.origin" class="flex items-center gap-1.5 bg-purple-50 px-3 py-1.5 rounded-full">
                          <MapPin class="h-4 w-4 text-purple-500" />
                        <span class="font-medium text-sm text-purple-700">{{ contestant?.origin }}</span>
                        </div>
                      <div v-if="contestant?.age" class="flex items-center gap-1.5 bg-purple-50 px-3 py-1.5 rounded-full">
                          <Calendar class="h-4 w-4 text-purple-500" />
                        <span class="font-medium text-sm text-purple-700">{{ contestant?.age }} years</span>
                      </div>
                      <div v-if="contestant?.rank" class="flex items-center gap-1.5 bg-pink-50 px-3 py-1.5 rounded-full">
                        <Award class="h-4 w-4 text-pink-500" />
                        <span class="font-medium text-sm text-pink-700">{{ contestant?.rank }}</span>
                        </div>
                      </div>
                      
                    <!-- Stats cards on the top (if available) -->
                    <div v-if="hasStats" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                      <div 
                        v-for="(stat, statName) in contestantStats" 
                        :key="statName" 
                        class="bg-gradient-to-br from-white to-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm"
                      >
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">{{ formatKey(statName) }}</h3>
                        <div class="text-2xl font-bold text-gray-900">{{ stat }}</div>
                      </div>
                    </div>
                      
                      <!-- Bio -->
                      <div class="mb-6">
                      <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <User class="h-5 w-5 mr-2 text-purple-500" />
                        Biography
                      </h3>
                      <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <p v-if="contestant?.bio" class="text-gray-700 leading-relaxed whitespace-pre-line">
                          {{ contestant?.bio }}
                        </p>
                        <p v-else class="text-gray-500 italic">
                          No biography provided.
                        </p>
                      </div>
                      </div>
                      
                      <!-- Additional Details if we have metadata -->
                    <div v-if="contestant?.metadata && Object.keys(contestant.metadata || {}).length > 0" class="mb-6">
                      <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <ClipboardList class="h-5 w-5 mr-2 text-purple-500" />
                        Additional Details
                      </h3>
                      <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          <div v-for="(value, key) in contestant?.metadata || {}" :key="key" class="text-sm">
                            <span class="font-medium text-gray-800">{{ formatKey(key) }}:</span> 
                            <span class="text-gray-700">{{ value }}</span>
                          </div>
                          </div>
                        </div>
                      </div>

                      <!-- Scores Section -->
                      <div v-if="hasScores" class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                          <Award class="h-5 w-5 mr-2 text-purple-500" />
                          Performance Scores
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Score Cards -->
                            <div v-for="(score, category) in contestantScores" :key="category" 
                              class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">
                                {{ formatKey(category) }}
                              </h4>
                              <div class="text-2xl font-bold text-gray-900">
                                {{ typeof score === 'number' ? score.toFixed(2) : score }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Extra slot for contextual content (e.g., comparisons) -->
                      <div class="mb-6">
                        <slot name="extra" :contestant="contestant"></slot>
                      </div>
                    </div>
                    
                    <!-- Action Buttons -->
                  <div class="border-t border-gray-200 pt-5 mt-auto flex justify-end gap-3">
                      <button
                        @click="closeModal"
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors"
                      >
                        Close
                      </button>
                      <button
                        @click="$emit('edit', contestant)"
                      class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 rounded-lg shadow-sm hover:shadow transition-all transform hover:-translate-y-0.5"
                      >
                      <Edit class="h-4 w-4 inline-block mr-1.5" />
                        Edit Contestant
                      </button>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, computed, TransitionGroup } from 'vue'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { XCircle, Award, MapPin, Calendar, ChevronLeft, ChevronRight, User, Edit, ClipboardList } from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  contestant: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'edit'])
const currentImageIndex = ref(0)

watch(() => props.show, (newValue) => {
  if (newValue) {
    currentImageIndex.value = 0
  }
}, { immediate: true })

// Helper computed properties
const hasStats = computed(() => {
  return props.contestant?.scores || props.contestant?.averageScore || props.contestant?.totalScore
})

const contestantStats = computed(() => {
  const stats = {}
  
  if (props.contestant?.averageScore) {
    stats.averageScore = typeof props.contestant.averageScore === 'number' 
      ? props.contestant.averageScore.toFixed(2) 
      : props.contestant.averageScore
  }
  
  if (props.contestant?.totalScore) {
    stats.totalScore = typeof props.contestant.totalScore === 'number' 
      ? props.contestant.totalScore.toFixed(2) 
      : props.contestant.totalScore
  }
  
  return stats
})

// New computed property for scores
const hasScores = computed(() => {
  return props.contestant?.scores && Object.keys(props.contestant.scores || {}).length > 0
})

const contestantScores = computed(() => {
  if (!props.contestant?.scores) return {}
  
  const scores = {...props.contestant.scores}
  
  // Add calculated fields if they exist
  if (props.contestant.averageScore && !scores.average) {
    scores.average = props.contestant.averageScore
  }
  
  if (props.contestant.totalScore && !scores.total) {
    scores.total = props.contestant.totalScore
  }
  
  return scores
})

// Format a key from camelCase or snake_case to Title Case
const formatKey = (key) => {
  if (!key) return ''
  return key
    .replace(/_/g, ' ')
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, str => str.toUpperCase())
    .trim()
}

// Navigation methods for the image carousel
const nextImage = () => {
  if (!props.contestant?.images?.length) return
  currentImageIndex.value = (currentImageIndex.value + 1) % props.contestant.images.length
}

const prevImage = () => {
  if (!props.contestant?.images?.length) return
  currentImageIndex.value = (currentImageIndex.value - 1 + props.contestant.images.length) % props.contestant.images.length
}

const closeModal = () => {
  emit('close')
}

// Touch handling for image carousel
const touchStartX = ref(0)
const touchEndX = ref(0)

const handleTouchStart = (e) => {
  touchStartX.value = e.changedTouches[0].screenX
}

const handleTouchMove = (e) => {
  // Prevent default scroll behavior when swiping horizontally
  if (Math.abs(e.changedTouches[0].screenX - touchStartX.value) > 10) {
    e.preventDefault()
  }
}

const handleTouchEnd = (e) => {
  touchEndX.value = e.changedTouches[0].screenX
  
  // Check if we have multiple images
  if (!props.contestant?.images?.length || props.contestant.images.length <= 1) return
  
  // Calculate swipe distance
  const swipeDistance = touchEndX.value - touchStartX.value
  
  // If swipe distance is significant enough, navigate images
  if (Math.abs(swipeDistance) > 50) {
    if (swipeDistance > 0) {
      prevImage() // Swipe right, show previous image
    } else {
      nextImage() // Swipe left, show next image
    }
  }
}
</script> 