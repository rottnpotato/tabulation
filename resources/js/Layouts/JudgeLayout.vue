<template>
  <div class="min-h-screen bg-gray-50">
    <Head title="Judge Dashboard" />
    <SideNav>
      <div class="p-6">


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