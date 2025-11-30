<template>
  <div>
    <template v-if="isPairCompetition">
      <!-- Males Section -->
      <div v-if="maleContestants.length > 0" class="mb-8">
        <h3 class="text-lg font-bold text-slate-700 mb-4 flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-blue-500"></div>
          Male Contestants
          <span class="text-sm font-normal text-slate-500">({{ maleContestants.length }})</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <ContestantCard
            v-for="contestant in maleContestants"
            :key="contestant.id"
            :contestant="contestant"
            :is-ongoing="isPageantOngoingFn(contestant)"
            @view="$emit('view', contestant)"
            @edit="$emit('edit', contestant)"
            @delete="$emit('delete', contestant)"
          />
        </div>
      </div>

      <!-- Females Section -->
      <div v-if="femaleContestants.length > 0">
        <h3 class="text-lg font-bold text-slate-700 mb-4 flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-pink-500"></div>
          Female Contestants
          <span class="text-sm font-normal text-slate-500">({{ femaleContestants.length }})</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <ContestantCard
            v-for="contestant in femaleContestants"
            :key="contestant.id"
            :contestant="contestant"
            :is-ongoing="isPageantOngoingFn(contestant)"
            @view="$emit('view', contestant)"
            @edit="$emit('edit', contestant)"
            @delete="$emit('delete', contestant)"
          />
        </div>
      </div>
    </template>

    <!-- Default Grid (no gender grouping) -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
      <ContestantCard
        v-for="contestant in contestants"
        :key="contestant.id"
        :contestant="contestant"
        :is-ongoing="isPageantOngoingFn(contestant)"
        @view="$emit('view', contestant)"
        @edit="$emit('edit', contestant)"
        @delete="$emit('delete', contestant)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import ContestantCard from '@/Components/ContestantCard.vue'

const props = defineProps({
  contestants: { type: Array, required: true },
  isPairCompetition: { type: Boolean, default: false },
  isPageantOngoingFn: { type: Function, default: () => false }, // Actually checks if locked
})

defineEmits(['view', 'edit', 'delete'])

const maleContestants = computed(() => {
  return props.contestants.filter(c => c.gender === 'male')
})

const femaleContestants = computed(() => {
  return props.contestants.filter(c => c.gender === 'female')
})
</script>

<style scoped>
/* Minimal styles - most styling is in ContestantCard */
</style>


