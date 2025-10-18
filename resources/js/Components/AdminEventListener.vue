<template>
  <!-- This is a non-visual component that handles event listening -->
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useNotification } from '@/Composables/useNotification';

const notify = useNotification();
const page = usePage();
const userId = page.props.auth.user?.id;

// Store event channel subscriptions
let eventChannels = [];

// Create notification sound using HTML5 Audio API
const notificationSound = ref(null);

onMounted(() => {
  // First check if Echo is available (Laravel Echo and Pusher should be installed)
  if (typeof window.Echo === 'undefined') {
    console.error('Laravel Echo is not available. Real-time events will not work.');
    return;
  }
  
  // Initialize notification sound (using a data URI for a subtle notification beep)
  // This is a short, pleasant notification tone
  initializeNotificationSound();
  
  // Listen for admin notifications (pageant creation)
  const adminNotificationChannel = window.Echo.private('admin-notifications')
    .listen('.pageant.created', (data) => {
      handlePageantCreated(data);
    });
  
  eventChannels.push(adminNotificationChannel);
  
  // Listen for global pageant events
  const globalChannel = window.Echo.private('pageant.all')
    .listen('.pageant.event.updated', (data) => {
      handleEventUpdate(data);
    });
  
  eventChannels.push(globalChannel);
  
  // If we're viewing a specific pageant, listen to that channel too
  const pageantId = page.props.pageant?.id;
  if (pageantId) {
    const pageantChannel = window.Echo.private(`pageant.${pageantId}`)
      .listen('.pageant.event.updated', (data) => {
        handleEventUpdate(data);
      });
      
    eventChannels.push(pageantChannel);
  }
});

onBeforeUnmount(() => {
  // Clean up all event listeners
  eventChannels.forEach(channel => {
    channel.stopListening('.pageant.event.updated');
    channel.stopListening('.pageant.created');
  });
  eventChannels = [];
});

// Initialize notification sound
const initializeNotificationSound = () => {
  // Create a pleasant notification beep sound using Web Audio API
  try {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    notificationSound.value = () => {
      const oscillator = audioContext.createOscillator();
      const gainNode = audioContext.createGain();
      
      oscillator.connect(gainNode);
      gainNode.connect(audioContext.destination);
      
      // Configure a pleasant notification tone
      oscillator.frequency.value = 800; // Frequency in Hz
      oscillator.type = 'sine';
      
      // Fade in and out for a smooth sound
      gainNode.gain.setValueAtTime(0, audioContext.currentTime);
      gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + 0.05);
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
      
      oscillator.start(audioContext.currentTime);
      oscillator.stop(audioContext.currentTime + 0.3);
    };
  } catch (e) {
    console.warn('Could not initialize notification sound:', e);
  }
};

// Play notification sound
const playNotificationSound = () => {
  if (notificationSound.value) {
    try {
      notificationSound.value();
    } catch (e) {
      console.warn('Could not play notification sound:', e);
    }
  }
};

// Handle pageant creation notification
const handlePageantCreated = (data) => {
  console.log('New pageant created:', data);
  
  // Play notification sound
  playNotificationSound();
  
  // Show notification toast
  notify.success(
    `${data.organizer_name} has submitted a new pageant "${data.pageant_name}" for approval!`,
    {
      title: '🎉 New Pageant Submission',
      timeout: 10000,
    }
  );
  
  // Emit event for other components
  window.dispatchEvent(new CustomEvent('pageant-created', { 
    detail: data 
  }));
};

// Handle different types of event updates
const handleEventUpdate = (data) => {
  console.log('Received event update:', data);
  
  // Create notifications based on event type
  switch (data.type) {
    case 'event_created':
      notify.info(`New event "${data.event_name}" created for pageant "${data.pageant_name}"`, {
        title: 'Event Created',
        timeout: 8000,
      });
      break;
      
    case 'event_updated':
      notify.info(`Event "${data.event_name}" was updated`, {
        title: 'Event Updated',
        timeout: 6000,
      });
      break;
      
    case 'event_status_changed':
      // Status changes get higher visibility
      notify.warning(`Event "${data.event_name}" status changed from "${data.previous_status}" to "${data.status}"`, {
        title: 'Event Status Changed',
        timeout: 10000,
      });
      break;
      
    case 'event_deleted':
      notify.warning(`Event "${data.event_name}" was deleted from pageant "${data.pageant_name}"`, {
        title: 'Event Deleted',
        timeout: 8000,
      });
      break;
  }
  
  // If this is a milestone event, show with higher priority
  if (data.is_milestone) {
    notify.success(`Milestone event update: "${data.message}"`, {
      title: 'Milestone Update',
      timeout: 12000,
    });
  }
  
  // Emit an event that other components can listen for
  window.dispatchEvent(new CustomEvent('pageant-event-updated', { 
    detail: data 
  }));
};
</script> 