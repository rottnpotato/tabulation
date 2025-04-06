<template>
  <div class="min-h-screen bg-gray-50">
    <Head title="Judge Dashboard" />
    <SideNav>
      <div class="p-6">
        <!-- Judge-specific header -->
        <div class="mb-6">
          <div class="bg-gradient-to-r from-amber-600 to-orange-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
              <div>
                <h1 class="text-2xl font-bold">{{ PageantName }}</h1>
                <p class="text-amber-100 mt-1">Judge Dashboard</p>
              </div>
              <div class="mt-4 md:mt-0 flex items-center space-x-2">
                <span class="flex items-center bg-white/10 backdrop-blur-sm rounded-full px-3 py-1 text-sm font-medium border border-white/20">
                  <Activity class="w-4 h-4 mr-1.5" />
                  LIVE
                </span>
                <div class="relative">
                  <select
                    v-model="CurrentRound"
                    class="rounded-lg bg-white/10 backdrop-blur-sm text-white border-white/20 px-3 py-1 text-sm font-medium"
                  >
                    <option value="evening_gown">Evening Gown</option>
                    <option value="swimsuit">Swimsuit</option>
                    <option value="qa">Q&A Round</option>
                    <option value="talent">Talent</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main content with transition -->
        <TransitionGroup
          tag="div"
          mode="out-in"
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0 -translate-y-4"
        >
          <slot />
        </TransitionGroup>
      </div>
    </SideNav>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SideNav from '@/Components/SideNav.vue';
import { Activity } from 'lucide-vue-next';

// This would normally come from a store or API
const PageantName = ref('Miss Universe 2025');
const CurrentRound = ref('evening_gown');
</script> 