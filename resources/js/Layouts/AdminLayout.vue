<template>
  <div class="min-h-screen bg-gray-50 overflow-auto">
    <Head title="Admin Dashboard" />
    <SideNav>
      <div class="p-2 sm:p-4 md:p-6">
        <!-- Page transition wrapper with enhanced animations -->
        <div class="page-content-wrapper">
          <!-- Decorative animated elements -->
          <div class="animated-elements">
            <div class="animated-dot bg-teal-500"></div>
            <div class="animated-dot bg-blue-500 delay-150"></div>
            <div class="animated-dot bg-purple-500 delay-300"></div>
            <div class="animated-line bg-gradient-to-r from-teal-400 to-emerald-500"></div>
          </div>
          
          <!-- Main content with enhanced transition -->
          <TransitionGroup
            tag="div"
            mode="out-in"
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="opacity-0 translate-y-6 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 -translate-y-6 scale-95"
          >
            <div key="admin-content" class="content-container">
              <slot />
            </div>
          </TransitionGroup>
        </div>
      </div>
    </SideNav>
    <NotificationSystem />
    <AdminEventListener />
  </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import SideNav from '@/Components/SideNav.vue'
import NotificationSystem from '@/Components/NotificationSystem.vue';
import AdminEventListener from '@/Components/AdminEventListener.vue';
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

// Add console log for debugging
console.log('AdminLayout mounted');

// Animation timing control
onMounted(() => {
  // Trigger animations when component is mounted
  const animatedElements = document.querySelectorAll('.animated-elements > div');
  animatedElements.forEach((el, index) => {
    setTimeout(() => {
      el.classList.add('animate-in');
    }, index * 100);
  });
});
</script>

<style scoped>
.page-content-wrapper {
  position: relative;
  overflow: hidden;
  width: 100%;
}

.animated-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 8px;
  z-index: 1;
  overflow: hidden;
}

.animated-dot {
  position: absolute;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  opacity: 0;
  transform: translateX(-20px);
  transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.animated-dot.animate-in {
  opacity: 0.8;
  transform: translateX(calc(100vw - 20px));
}

.animated-dot:nth-child(1) {
  top: 0;
}

.animated-dot:nth-child(2) {
  top: 4px;
}

.animated-dot:nth-child(3) {
  top: 8px;
}

.delay-150 {
  transition-delay: 0.15s;
}

.delay-300 {
  transition-delay: 0.3s;
}

.animated-line {
  position: absolute;
  height: 2px;
  width: 0;
  top: 12px;
  left: 0;
  transition: width 1s cubic-bezier(0.4, 0, 0.2, 1) 0.4s;
}

.animated-line.animate-in {
  width: 100%;
}

.content-container {
  position: relative;
  z-index: 2;
  padding-top: 8px;
  transform-origin: top center;
  width: 100%;
}

@media (max-width: 640px) {
  .content-container {
    padding-top: 4px;
  }
}
</style>