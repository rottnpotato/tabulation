<template>
  <div class="min-h-screen bg-gray-50 overflow-auto">
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
import { ref, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import SideNav from '@/Components/SideNav.vue';
import { Activity } from 'lucide-vue-next';
import { useNotification } from '@/Composables/useNotification';

const page = usePage();
const notify = useNotification();

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (!flash) return;
  
  if (flash.success) {
    notify.success(flash.success);
  }
  if (flash.error) {
    notify.error(flash.error);
  }
  if (flash.warning) {
    notify.warning(flash.warning);
  }
  if (flash.info) {
    notify.info(flash.info);
  }
}, { deep: true, immediate: true });

// This would normally come from a store or API
const PageantName = ref('Miss Universe 2025');
const CurrentRound = ref('evening_gown');
</script> 