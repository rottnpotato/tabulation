<template>
  <div class="min-h-screen bg-slate-200 overflow-auto">
    <Head title="Event Organizer Portal" />
    <SideNav>
      <div class="p-6">
        <!-- Main content with transition group to handle multiple elements -->
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

    <!-- Settings Modal -->
    <OrganizerSettingsModal 
      :is-visible="isSettingsModalVisible" 
      @close="CloseSettingsModal"
      @update="UpdateSettings" 
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import SideNav from '@/Components/SideNav.vue';
import { Calendar, Settings, Crown } from 'lucide-vue-next';
import OrganizerSettingsModal from '@/Components/modals/OrganizerSettingsModal.vue';
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

// Settings modal - these will be handled by Dashboard.vue now
const isSettingsModalVisible = ref(false);

// Settings modal functions
const OpenSettingsModal = () => {
  isSettingsModalVisible.value = true;
};

const CloseSettingsModal = () => {
  isSettingsModalVisible.value = false;
};

const UpdateSettings = (settings) => {
  console.log('Updating settings:', settings);
  // Show success message (would be a toast notification in a real app)
  alert('Settings updated successfully!');
};
</script>

<style>
/* Custom shimmer animation with teal color for loading skeletons */
.shimmer-teal {
  background-image: linear-gradient(
    to right,
    #f9f9f9 0%,
    #f5f5f5 20%,
    rgba(20, 184, 166, 0.2) 40%, /* teal-500 */
    #f5f5f5 60%,
    #f9f9f9 80%,
    #f9f9f9 100%
  );
  background-size: 200% 100%;
  animation: shimmer 2s infinite linear;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>
