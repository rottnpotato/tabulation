<template>
  <div class="min-h-screen bg-gray-50">
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

    <!-- Notification System -->
    <NotificationSystem />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import SideNav from '@/Components/SideNav.vue';
import { Calendar, Settings, Crown } from 'lucide-vue-next';
import OrganizerSettingsModal from '@/Components/modals/OrganizerSettingsModal.vue';
import NotificationSystem from '@/Components/NotificationSystem.vue';

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
/* Custom shimmer animation with orange color for loading skeletons */
.shimmer-orange {
  background-image: linear-gradient(
    to right,
    #f9f9f9 0%,
    #f5f5f5 20%,
    rgba(249, 115, 22, 0.2) 40%,
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
