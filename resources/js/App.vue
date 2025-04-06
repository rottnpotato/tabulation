<template>
  <div class="app-container">
    <transition name="page" mode="out-in" appear>
      <component 
        :is="page.component" 
        v-bind="page.props" 
        :key="page.component"
      />
    </transition>
  </div>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useNotification } from '@/Composables/useNotification';

// Page data
const page = usePage();
const notify = useNotification();

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    notify.success(flash.success);
  }
  
  if (flash?.error) {
    notify.error(flash.error);
  }
  
  if (flash?.warning) {
    notify.warning(flash.warning);
  }
  
  if (flash?.info) {
    notify.info(flash.info);
  }
}, { immediate: true, deep: true });

// Remove the onMounted check to prevent duplicate notifications
</script>

<style>
.app-container {
  min-height: 100vh;
  width: 100%;
}

/* Transition animations */
.page-enter-active,
.page-leave-active {
  transition: opacity 0.3s ease;
}

.page-enter-from,
.page-leave-to {
  opacity: 0;
}
</style> 