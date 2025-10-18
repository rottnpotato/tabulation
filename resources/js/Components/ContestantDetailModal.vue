<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-30">
      <TransitionChild
        enter="ease-out duration-400"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            enter="ease-out duration-500"
            enter-from="opacity-0 scale-90 translate-y-8"
            enter-to="opacity-100 scale-100 translate-y-0"
            leave="ease-in duration-300"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="modal-panel">
              <div class="relative grid grid-cols-1 md:grid-cols-5 min-h-[650px]">
                <!-- Close button -->
                <button 
                  @click="closeModal" 
                  class="close-button"
                >
                  <XCircle class="h-6 w-6" />
                </button>

                <!-- Left Column - Images Carousel (2 cols on md screens) -->
                <div class="image-carousel-container">
                  <!-- Elegant gradient overlay --> 
                  <div class="carousel-gradient-overlay"/>
                  
                  <!-- Contestant number badge -->
                  <div class="absolute top-5 left-5 z-10">
                    <div class="contestant-badge-modal">
                      <div class="text-center">
                        <div class="text-xs uppercase tracking-wide font-medium opacity-90">No.</div>
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
                      <div v-if="contestant?.images?.length > 1" class="absolute inset-0 flex items-center justify-between px-4 pointer-events-none">
                        <button 
                          @click="prevImage" 
                          class="carousel-nav-button pointer-events-auto"
                        >
                          <ChevronLeft class="h-6 w-6" />
                        </button>
                        <button 
                          @click="nextImage" 
                          class="carousel-nav-button pointer-events-auto"
                        >
                          <ChevronRight class="h-6 w-6" />
                        </button>
                      </div>
                      
                      <!-- Pagination Dots -->
                      <div 
                        v-if="contestant?.images?.length > 1" 
                        class="absolute bottom-6 left-0 right-0 flex justify-center gap-2.5"
                      >
                        <button 
                          v-for="(_, index) in contestant?.images" 
                          :key="index"
                          @click="currentImageIndex = index"
                          class="carousel-dot"
                          :class="currentImageIndex === index ? 'active' : ''"
                        ></button>
                      </div>
                    </div>
                  </div>
                  
                <!-- Right Column - Contestant Details (3 cols on md screens) -->
                <div class="details-container">
                    <div class="flex-grow">
                    <!-- Header Section with Name and Key Info -->
                    <div class="mb-8">
                      <h2 class="contestant-name-modal">
                        {{ contestant?.name || 'Contestant Details' }}
                      </h2>
                      
                      <!-- Key Info Grid -->
                      <div class="info-grid">
                        <div v-if="contestant?.origin" class="info-item">
                          <div class="info-icon-wrapper info-icon-location">
                            <MapPin class="h-5 w-5" />
                          </div>
                          <div class="info-content">
                            <span class="info-label">Location</span>
                            <span class="info-value">{{ contestant?.origin }}</span>
                          </div>
                        </div>
                        
                        <div v-if="contestant?.age" class="info-item">
                          <div class="info-icon-wrapper info-icon-age">
                            <Calendar class="h-5 w-5" />
                          </div>
                          <div class="info-content">
                            <span class="info-label">Age</span>
                            <span class="info-value">{{ contestant?.age }} years</span>
                          </div>
                        </div>
                        
                        <div v-if="contestant?.rank" class="info-item">
                          <div class="info-icon-wrapper info-icon-rank">
                            <Award class="h-5 w-5" />
                          </div>
                          <div class="info-content">
                            <span class="info-label">Rank</span>
                            <span class="info-value">{{ contestant?.rank }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                      
                    <!-- Stats cards on the top (if available) -->
                    <div v-if="hasStats" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                      <div 
                        v-for="(stat, statName) in contestantStats" 
                        :key="statName" 
                        class="stat-card"
                      >
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">{{ formatKey(statName) }}</h3>
                        <div class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent">{{ stat }}</div>
                      </div>
                    </div>
                      
                      <!-- Bio -->
                      <div class="mb-6">
                      <h3 class="section-title">
                        <User class="h-5 w-5 mr-2 text-orange-500" />
                        Biography
                      </h3>
                      <div class="content-card">
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
                      <h3 class="section-title">
                        <ClipboardList class="h-5 w-5 mr-2 text-orange-500" />
                        Additional Details
                      </h3>
                      <div class="content-card">
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
                        <h3 class="section-title">
                          <Award class="h-5 w-5 mr-2 text-orange-500" />
                          Performance Scores
                        </h3>
                        <div class="content-card">
                          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Score Cards -->
                            <div v-for="(score, category) in contestantScores" :key="category" 
                              class="score-card">
                              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">
                                {{ formatKey(category) }}
                              </h4>
                              <div class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent">
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
                  <div class="action-buttons-footer">
                      <button
                        @click="closeModal"
                        class="modal-button modal-button-secondary"
                      >
                        Close
                      </button>
                      <button
                        @click="$emit('edit', contestant)"
                        class="modal-button modal-button-primary"
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

<style scoped>
.modal-panel {
  @apply w-full max-w-6xl transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.close-button {
  @apply absolute top-5 right-5 z-10 text-white transition-all;
  @apply bg-black/40 hover:bg-black/60 rounded-full p-2 shadow-xl;
  backdrop-filter: blur(8px);
  transform: scale(1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.close-button:hover {
  transform: scale(1.1) rotate(90deg);
}

.image-carousel-container {
  @apply relative md:col-span-2 lg:col-span-2 overflow-hidden;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

.carousel-gradient-overlay {
  @apply absolute inset-0;
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.12) 0%, rgba(236, 72, 153, 0.12) 100%);
  mix-blend-mode: overlay;
  pointer-events: none;
}

.contestant-badge-modal {
  @apply text-white rounded-2xl h-16 w-16 flex items-center justify-center shadow-2xl;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  border: 2.5px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(8px);
  animation: badgePulse 3s ease-in-out infinite;
}

@keyframes badgePulse {
  0%, 100% { 
    transform: scale(1);
    box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
  }
  50% { 
    transform: scale(1.05);
    box-shadow: 0 15px 35px rgba(249, 115, 22, 0.6);
  }
}

.carousel-nav-button {
  @apply rounded-full p-3 text-white transition-all duration-300;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(12px);
  border: 1.5px solid rgba(255, 255, 255, 0.2);
}

.carousel-nav-button:hover {
  background: rgba(249, 115, 22, 0.7);
  border-color: rgba(255, 255, 255, 0.4);
  transform: scale(1.15);
  box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
}

.carousel-dot {
  @apply w-2.5 h-2.5 rounded-full transition-all duration-300;
  background: rgba(255, 255, 255, 0.4);
  border: 1.5px solid rgba(255, 255, 255, 0.3);
}

.carousel-dot:hover {
  background: rgba(255, 255, 255, 0.7);
  transform: scale(1.2);
}

.carousel-dot.active {
  @apply w-8;
  background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
  border-color: rgba(255, 255, 255, 0.5);
  box-shadow: 0 0 12px rgba(249, 115, 22, 0.6);
}

.details-container {
  @apply md:col-span-3 p-8 flex flex-col overflow-hidden;
  background: linear-gradient(to bottom, #ffffff 0%, #fafafa 100%);
}

.contestant-name-modal {
  @apply text-4xl font-bold mb-6;
  background: linear-gradient(135deg, #1f2937 0%, #4b5563 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.info-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-5;
}

.info-item {
  @apply flex items-center gap-4 p-4 rounded-xl;
  background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
  border: 1.5px solid rgba(249, 115, 22, 0.15);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.info-item:hover {
  border-color: rgba(249, 115, 22, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(249, 115, 22, 0.1);
}

.info-icon-wrapper {
  @apply flex items-center justify-center rounded-xl;
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.info-item:hover .info-icon-wrapper {
  transform: scale(1.1) rotate(5deg);
}

.info-icon-location {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.15) 0%, rgba(251, 146, 60, 0.2) 100%);
  color: #ea580c;
  border: 1.5px solid rgba(249, 115, 22, 0.25);
}

.info-icon-age {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(96, 165, 250, 0.2) 100%);
  color: #2563eb;
  border: 1.5px solid rgba(59, 130, 246, 0.25);
}

.info-icon-rank {
  background: linear-gradient(135deg, rgba(236, 72, 153, 0.15) 0%, rgba(244, 114, 182, 0.2) 100%);
  color: #db2777;
  border: 1.5px solid rgba(236, 72, 153, 0.25);
}

.info-content {
  @apply flex flex-col gap-1 flex-1 min-w-0;
}

.info-label {
  @apply text-xs font-semibold uppercase tracking-wider text-gray-500;
  letter-spacing: 0.05em;
}

.info-value {
  @apply text-base font-bold text-gray-900 truncate;
  letter-spacing: -0.01em;
}

.stat-card {
  @apply p-5 rounded-xl border shadow-sm;
  background: linear-gradient(135deg, #ffffff 0%, #fef3f2 100%);
  border-color: rgba(249, 115, 22, 0.2);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(249, 115, 22, 0.15);
  border-color: rgba(249, 115, 22, 0.4);
}

.section-title {
  @apply text-lg font-semibold text-gray-800 mb-3 flex items-center;
  letter-spacing: -0.01em;
}

.content-card {
  @apply rounded-xl p-6 border;
  background: linear-gradient(to bottom right, #ffffff 0%, #f9fafb 100%);
  border-color: rgba(249, 115, 22, 0.15);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.content-card:hover {
  border-color: rgba(249, 115, 22, 0.25);
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.08);
}

.score-card {
  @apply p-4 rounded-xl border shadow-sm;
  background: linear-gradient(135deg, #ffffff 0%, #fef3f2 100%);
  border-color: rgba(249, 115, 22, 0.2);
  transition: all 0.3s ease;
}

.score-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(249, 115, 22, 0.12);
  border-color: rgba(249, 115, 22, 0.35);
}

.action-buttons-footer {
  @apply border-t pt-6 mt-auto flex justify-end gap-3;
  border-color: rgba(249, 115, 22, 0.15);
}

.modal-button {
  @apply px-6 py-3 text-sm font-semibold rounded-xl shadow-md transition-all duration-300;
  @apply flex items-center gap-2;
}

.modal-button-secondary {
  @apply text-gray-700 bg-white border-2;
  border-color: rgba(156, 163, 175, 0.3);
}

.modal-button-secondary:hover {
  @apply bg-gray-50;
  border-color: rgba(156, 163, 175, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.modal-button-primary {
  @apply text-white;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  border: 2px solid transparent;
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.modal-button-primary:hover {
  background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(249, 115, 22, 0.5);
}

/* Image transitions */
:deep(.h-full.w-full img) {
  transition: transform 0.5s ease;
}

:deep(.h-full.w-full:hover img) {
  transform: scale(1.02);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .modal-panel {
    @apply rounded-2xl;
  }
  
  .contestant-name-modal {
    @apply text-3xl;
  }
  
  .details-container {
    @apply p-6;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .details-container {
    background: linear-gradient(to bottom, #1f2937 0%, #111827 100%);
  }
  
  .contestant-name-modal {
    background: linear-gradient(135deg, #f3f4f6 0%, #d1d5db 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
}
</style> 