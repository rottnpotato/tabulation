<template>
  <div
    :data-contestant-id="contestant.id"
    class="card-contestant group"
  >
    <div class="relative aspect-[4/5] overflow-hidden rounded-2xl">
      <img 
        :src="contestant.photo || '/images/placeholders/placeholder-contestant.jpg'" 
        :alt="contestant.name" 
        class="w-full h-full object-cover transition-all duration-700 ease-out group-hover:scale-110 group-hover:brightness-90"
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
      
      <!-- Elegant gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/20 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
      
      <!-- Decorative corner accent -->
      <div class="corner-accent"></div>
      
      <!-- Top badges container -->
      <div class="absolute top-4 left-4 flex flex-col gap-2">
        <!-- Pair badge for paired contestants -->
        <span v-if="contestant.is_paired" class="pair-badge">
          <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
          </svg>
          Paired
        </span>
        
        <!-- Ongoing pageant badge -->
        <span v-if="isOngoing" class="ongoing-badge">
          <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
          </svg>
          Read-Only
        </span>
      </div>

      <!-- Action buttons overlay - visible on hover -->
      <div class="action-buttons-overlay">
        <div class="action-buttons-container">
          <Tooltip text="View detailed information, photos, and bio" position="top">
            <button 
              @click="$emit('view', contestant)" 
              class="action-btn action-btn-primary"
            >
              <Eye class="h-4 w-4 z-10" />
              <span class="hidden sm:inline">View</span>
            </button>
          </Tooltip>
          <Tooltip :text="isOngoing ? 'Cannot edit - Pageant is locked' : 'Edit contestant information and photos'" position="top">
            <button 
              @click="!isOngoing && $emit('edit', contestant)" 
              class="action-btn action-btn-secondary"
              :class="{ 'opacity-50 cursor-not-allowed': isOngoing }"
              :disabled="isOngoing"
            >
              <Edit class="h-4 w-4 z-10" />
              <span class="hidden sm:inline">Edit</span>
            </button>
          </Tooltip>
          <Tooltip :text="isOngoing ? 'Cannot delete - Pageant is locked' : 'Permanently remove contestant from pageant'" position="top">
            <button 
              @click="!isOngoing && $emit('delete', contestant)" 
              class="action-btn action-btn-danger"
              :class="{ 'opacity-50 cursor-not-allowed': isOngoing }"
              :disabled="isOngoing"
            >
              <Trash2 class="h-4 w-4 z-10" />
              <span class="hidden sm:inline">Delete</span>
            </button>
          </Tooltip>
        </div>
      </div>

      <!-- Contestant info at bottom -->
      <div class="contestant-info">
        <div class="relative">
          <!-- Display gender-specific label for paired contestants -->
          <h3 class="contestant-name">
            {{ getContestantDisplayName(contestant) }}
          </h3>
          <p class="contestant-subtitle">{{ contestant.name }}</p>
          <p v-if="contestant.is_paired && contestant.partner" class="contestant-partner">
            Partner: {{ contestant.partner.name }}
          </p>
          <div class="contestant-details">
            <div class="contestant-detail-item" v-if="(contestant.origin || contestant.city)">
              <MapPin class="detail-icon" />
              <span class="truncate">{{ contestant.origin || contestant.city }}</span>
            </div>
            <div class="contestant-detail-item" v-if="contestant.age">
              <Calendar class="detail-icon" />
              <span>{{ contestant.age }} yrs</span>
            </div>
          </div>
        </div>
        <!-- Decorative shine effect -->
        <div class="info-shine"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Users, Eye, Edit, Trash2, MapPin, Calendar } from 'lucide-vue-next'
import Tooltip from '@/Components/Tooltip.vue'

defineProps({
  contestant: { type: Object, required: true },
  isOngoing: { type: Boolean, default: false },
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

const getContestantDisplayName = (contestant) => {
  if (contestant.is_paired) {
    // Display as "Male Contestant #1" or "Female Contestant #1"
    const genderLabel = contestant.gender === 'male' ? 'Male' : 'Female'
    const number = displayNumber(contestant)
    return `${genderLabel} Contestant #${number}`
  }
  return contestant.name
}
</script>

<style scoped>
.card-contestant {
  @apply relative cursor-pointer transition-all duration-500 ease-out;
  transform-style: preserve-3d;
  perspective: 1000px;
}

.card-contestant:hover {
  transform: translateY(-8px);
}

.card-contestant::before {
  content: '';
  @apply absolute inset-0 rounded-2xl transition-all duration-500;
  background: linear-gradient(135deg, rgba(20, 184, 166, 0.15) 0%, rgba(13, 148, 136, 0.1) 100%);
  opacity: 0;
  transform: scale(0.95);
  z-index: -1;
}

.card-contestant:hover::before {
  opacity: 1;
  transform: scale(1.05);
  box-shadow: 0 20px 40px -15px rgba(20, 184, 166, 0.3);
}

.corner-accent {
  @apply absolute top-0 right-0 w-20 h-20 pointer-events-none;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, transparent 60%);
  opacity: 0;
  transition: opacity 0.5s ease;
}

.card-contestant:hover .corner-accent {
  opacity: 1;
}

.pair-badge {
  @apply inline-flex items-center px-3 py-1 rounded-full text-white text-xs font-semibold shadow-lg;
  background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
  border: 1.5px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  transition: all 0.3s ease;
}

.card-contestant:hover .pair-badge {
  transform: scale(1.05);
}

.ongoing-badge {
  @apply inline-flex items-center px-3 py-1 rounded-full text-white text-xs font-semibold shadow-lg;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  border: 1.5px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  transition: all 0.3s ease;
}

.card-contestant:hover .ongoing-badge {
  transform: scale(1.05);
}

.action-buttons-overlay {
  @apply absolute inset-0 flex items-center justify-center;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.85) 100%);
  backdrop-filter: blur(12px);
  opacity: 0;
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  pointer-events: none;
  z-index: 20;
}

.card-contestant:hover .action-buttons-overlay {
  opacity: 1;
  pointer-events: auto;
}

.action-buttons-container {
  @apply flex flex-col gap-3 px-4;
  animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  position: relative;
  z-index: 30;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.action-btn {
  @apply px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 flex items-center justify-center gap-2 shadow-lg;
  min-width: 140px;
  border: 1.5px solid transparent;
  transform: scale(0.95);
  position: relative;
  z-index: 40;
}

.action-btn:disabled {
  @apply cursor-not-allowed;
  filter: grayscale(0.5);
}

.card-contestant:hover .action-btn {
  transform: scale(1);
}

.card-contestant:hover .action-btn:disabled {
  transform: scale(0.95);
}

.action-btn:not(:disabled):hover {
  transform: scale(1.05) !important;
}

.action-btn-primary {
  @apply text-white;
  background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
  box-shadow: 0 4px 15px rgba(20, 184, 166, 0.4);
}

.action-btn-primary:hover {
  box-shadow: 0 6px 25px rgba(20, 184, 166, 0.6);
  border-color: rgba(255, 255, 255, 0.3);
}

.action-btn-secondary {
  @apply text-white;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-color: rgba(255, 255, 255, 0.25);
}

.action-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.4);
}

.action-btn-danger {
  @apply text-white;
  background: rgba(239, 68, 68, 0.2);
  backdrop-filter: blur(10px);
  border-color: rgba(239, 68, 68, 0.3);
}

.action-btn-danger:hover {
  background: rgba(239, 68, 68, 0.35);
  border-color: rgba(239, 68, 68, 0.5);
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.contestant-info {
  @apply absolute bottom-0 left-0 right-0 p-5 text-white;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.6) 70%, transparent 100%);
  transition: all 0.4s ease;
  z-index: 10;
}

.card-contestant:hover .contestant-info {
  padding-bottom: 1.75rem;
}

.info-shine {
  @apply absolute inset-0 opacity-0 transition-opacity duration-500;
  background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.1) 50%, transparent 100%);
  transform: translateX(-100%);
}

.card-contestant:hover .info-shine {
  opacity: 1;
  animation: shine 1.5s ease-in-out;
}

@keyframes shine {
  to {
    transform: translateX(100%);
  }
}

.contestant-name {
  @apply text-xl font-bold mb-1 truncate;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
  letter-spacing: 0.02em;
}

.contestant-subtitle {
  @apply text-sm mb-1 truncate;
  color: rgba(255, 255, 255, 0.85);
  text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
}

.contestant-partner {
  @apply text-xs mb-2 truncate;
  color: rgba(204, 251, 241, 0.95);
  text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
}

.contestant-details {
  @apply flex flex-wrap items-center gap-4 mt-2;
}

.contestant-detail-item {
  @apply flex items-center text-xs gap-1.5;
  color: rgba(255, 255, 255, 0.9);
  text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
  transition: all 0.3s ease;
}

.card-contestant:hover .contestant-detail-item {
  color: rgba(204, 251, 241, 1);
}

.detail-icon {
  @apply h-3.5 w-3.5 flex-shrink-0;
  color: rgba(45, 212, 191, 0.9);
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.5));
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .action-btn {
    min-width: auto;
    padding: 0.625rem;
  }
  
  .action-buttons-container {
    flex-direction: row;
    justify-center: center;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .card-contestant::before {
    background: linear-gradient(135deg, rgba(20, 184, 166, 0.2) 0%, rgba(13, 148, 136, 0.15) 100%);
  }
}
</style>
