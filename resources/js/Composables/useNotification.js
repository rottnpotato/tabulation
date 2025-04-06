import { inject } from 'vue';
import { notificationInstance } from '@/Plugins/NotificationPlugin';

export function useNotification() {
  // Try to get the notification service from injection
  const notifyFromInjection = inject('notify');
  
  // If injection isn't available, use the global instance directly
  const notify = notifyFromInjection || {
    success: (message, options = {}) => notificationInstance.value?.success(message, options),
    error: (message, options = {}) => notificationInstance.value?.error(message, options),
    warning: (message, options = {}) => notificationInstance.value?.warning(message, options),
    info: (message, options = {}) => notificationInstance.value?.info(message, options),
    clear: () => notificationInstance.value?.clear()
  };
  
  return notify;
} 