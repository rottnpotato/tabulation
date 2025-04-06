<template>
  <Teleport to="body">
    <TransitionGroup
      tag="div"
      class="fixed right-4 top-4 sm:right-6 sm:top-6 z-50 flex flex-col items-end space-y-3 pointer-events-none max-w-md w-full"
      enter-active-class="transform transition duration-400 ease-out"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transform transition duration-300 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
    >
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="pointer-events-auto w-full flex transform transition-all duration-500 shadow-lg"
        :class="[
          'rounded-lg overflow-hidden',
          { 'hover:shadow-xl hover:-translate-y-0.5': !notification.isLeaving },
        ]"
        @mouseenter="pauseNotification(notification.id)"
        @mouseleave="resumeNotification(notification.id)"
      >
        <div class="w-1.5" :class="getAccentClass(notification.type)"></div>
        <div class="flex-1 p-4 bg-white backdrop-blur-sm bg-opacity-95 dark:bg-gray-800 dark:bg-opacity-95">
          <div class="flex items-start">
            <div class="flex-shrink-0 p-1">
              <component :is="getIcon(notification.type)" class="h-5 w-5" :class="getIconClass(notification.type)" />
            </div>
            <div class="ml-3 w-0 flex-1">
              <p v-if="notification.title" class="text-sm font-medium" :class="getTitleClass(notification.type)">
                {{ notification.title }}
              </p>
              <p class="text-sm" :class="getMessageClass(notification.type)">
                {{ notification.message }}
              </p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeNotification(notification.id)"
                class="bg-transparent rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
              >
                <span class="sr-only">Close</span>
                <X class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
        
        <div 
          v-if="notification.progress !== false" 
          class="absolute bottom-0 left-0 right-0 h-1 bg-white bg-opacity-20"
        >
          <div 
            class="h-1 transition-all duration-200 ease-linear" 
            :class="getProgressClass(notification.type)"
            :style="{ width: `${notification.progress || 100}%` }"
          ></div>
        </div>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  CheckCircle, 
  AlertTriangle, 
  AlertCircle, 
  Info, 
  X,
  Clock
} from 'lucide-vue-next';

// Store for notifications
const notifications = ref([]);
let nextId = 0;
const pausedNotifications = new Set();

// Add notification with limit enforcement
const add = (options) => {
  const id = nextId++;
  const notification = {
    id,
    message: '',
    type: 'success', // success, error, warning, info
    timeout: 5000,
    progress: 100,
    isLeaving: false,
    ...options
  };
  
  // Enforce maximum visible notifications
  const maxVisible = 3; // We'll get this from plugin settings in a real implementation
  if (notifications.value.length >= maxVisible) {
    // Remove the oldest notification
    const oldestId = notifications.value[0].id;
    removeNotification(oldestId);
  }
  
  notifications.value.push(notification);
  
  if (notification.timeout > 0) {
    startNotificationTimer(id, notification.timeout);
  }
  
  return id;
};

// Start notification timer
const startNotificationTimer = (id, timeout) => {
  const startTime = Date.now();
  const endTime = startTime + timeout;
  
  const updateProgress = () => {
    if (pausedNotifications.has(id)) {
      requestAnimationFrame(() => updateProgress());
      return;
    }
    
    const now = Date.now();
    if (now >= endTime) {
      removeNotification(id);
      return;
    }
    
    const elapsed = now - startTime;
    const remaining = timeout - elapsed;
    const progress = (remaining / timeout) * 100;
    
    // Find and update the notification
    const index = notifications.value.findIndex(n => n.id === id);
    if (index !== -1) {
      notifications.value[index].progress = progress;
    }
    
    requestAnimationFrame(updateProgress);
  };
  
  requestAnimationFrame(updateProgress);
};

// Pause notification timer
const pauseNotification = (id) => {
  pausedNotifications.add(id);
};

// Resume notification timer
const resumeNotification = (id) => {
  pausedNotifications.delete(id);
};

// Success notification
const success = (message, options = {}) => {
  return add({
    type: 'success',
    message,
    ...options
  });
};

// Error notification
const error = (message, options = {}) => {
  return add({
    type: 'error',
    message,
    ...options
  });
};

// Warning notification
const warning = (message, options = {}) => {
  return add({
    type: 'warning',
    message,
    ...options
  });
};

// Info notification
const info = (message, options = {}) => {
  return add({
    type: 'info',
    message,
    ...options
  });
};

// Remove notification
const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index !== -1) {
    // Mark as leaving to disable hover effects during exit animation
    notifications.value[index].isLeaving = true;
    
    // Wait a brief moment before removing to let the leaving styles take effect
    setTimeout(() => {
      const newIndex = notifications.value.findIndex(n => n.id === id);
      if (newIndex !== -1) {
        notifications.value.splice(newIndex, 1);
      }
    }, 100);
  }
};

// Clear all notifications
const clear = () => {
  notifications.value = [];
};

// Get appropriate icon
const getIcon = (type) => {
  switch (type) {
    case 'success':
      return CheckCircle;
    case 'error':
      return AlertCircle;
    case 'warning':
      return AlertTriangle;
    case 'info':
    default:
      return Info;
  }
};

// Get background class based on notification type
const getBackgroundClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-white';
    case 'error':
      return 'bg-white';
    case 'warning':
      return 'bg-white';
    case 'info':
    default:
      return 'bg-white';
  }
};

// Get accent class based on notification type
const getAccentClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-gradient-to-b from-teal-400 to-teal-600';
    case 'error':
      return 'bg-gradient-to-b from-red-400 to-red-600';
    case 'warning':
      return 'bg-gradient-to-b from-amber-400 to-amber-600';
    case 'info':
    default:
      return 'bg-gradient-to-b from-blue-400 to-blue-600';
  }
};

// Get progress bar class
const getProgressClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-teal-500';
    case 'error':
      return 'bg-red-500';
    case 'warning':
      return 'bg-amber-500';
    case 'info':
    default:
      return 'bg-blue-500';
  }
};

// Get icon class based on notification type
const getIconClass = (type) => {
  switch (type) {
    case 'success':
      return 'text-teal-500';
    case 'error':
      return 'text-red-500';
    case 'warning':
      return 'text-amber-500';
    case 'info':
    default:
      return 'text-blue-500';
  }
};

// Get title class based on notification type
const getTitleClass = (type) => {
  switch (type) {
    case 'success':
      return 'text-gray-900 dark:text-white';
    case 'error':
      return 'text-gray-900 dark:text-white';
    case 'warning':
      return 'text-gray-900 dark:text-white';
    case 'info':
    default:
      return 'text-gray-900 dark:text-white';
  }
};

// Get message class based on notification type
const getMessageClass = (type) => {
  switch (type) {
    case 'success':
      return 'text-gray-600 dark:text-gray-300';
    case 'error':
      return 'text-gray-600 dark:text-gray-300';
    case 'warning':
      return 'text-gray-600 dark:text-gray-300';
    case 'info':
    default:
      return 'text-gray-600 dark:text-gray-300';
  }
};

// Expose methods for use in other components
defineExpose({
  success,
  error,
  warning,
  info,
  clear
});
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style> 