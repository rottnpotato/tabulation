<template>
  <!-- This is a non-visual component that handles event listening -->
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useNotification } from '@/Composables/useNotification';

const notify = useNotification();
const page = usePage();
const userId = page.props.auth.user?.id;

// Store event channel subscriptions
let eventChannels = [];

onMounted(() => {
  // First check if Echo is available (Laravel Echo and Pusher should be installed)
  if (typeof window.Echo === 'undefined') {
    console.error('Laravel Echo is not available. Real-time events will not work.');
    return;
  }
  
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
  });
  eventChannels = [];
});

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