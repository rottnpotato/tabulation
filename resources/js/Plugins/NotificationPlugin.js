import NotificationSystem from '@/Components/NotificationSystem.vue';
import { h, render, ref, reactive } from 'vue';

export const notificationInstance = ref(null);

// Track notification messages to prevent duplicates
const recentMessages = reactive({
  messages: new Set(),
  
  // Add a message to the recent set with automatic expiration
  add(message, expiryMs = 3000) {
    if (this.messages.has(message)) {
      return false; // Already exists
    }
    
    this.messages.add(message);
    
    // Auto-expire after specified time
    setTimeout(() => {
      this.messages.delete(message);
    }, expiryMs);
    
    return true; // Added successfully
  }
});

// Default notification settings
const defaultSettings = {
  defaultTimeout: 5000,           // Default timeout in ms
  preventDuplicates: true,        // Prevent duplicate notifications
  duplicatePreventionWindow: 3000, // How long to prevent duplicates (ms)
  maxVisible: 3,                  // Maximum number of visible notifications
};

export default {
  install(app, options = {}) {
    // Merge default settings with user options
    const settings = { ...defaultSettings, ...options };
    
    // Create a notification container div
    const notificationContainer = document.createElement('div');
    notificationContainer.id = 'notification-container';
    document.body.appendChild(notificationContainer);

    // Create the notification component instance
    const notificationVNode = h(NotificationSystem);
    render(notificationVNode, notificationContainer);

    // Store the component instance for later reference
    notificationInstance.value = notificationVNode.component.exposed;

    // Add notification methods to the global app instance with duplicate prevention
    app.config.globalProperties.$notify = {
      success(message, options = {}) {
        if (settings.preventDuplicates && !recentMessages.add(message, settings.duplicatePreventionWindow)) {
          return null; // Prevent duplicate
        }
        
        return notificationInstance.value.success(message, {
          timeout: options.timeout || settings.defaultTimeout,
          ...options
        });
      },
      error(message, options = {}) {
        if (settings.preventDuplicates && !recentMessages.add(message, settings.duplicatePreventionWindow)) {
          return null; // Prevent duplicate
        }
        
        return notificationInstance.value.error(message, {
          timeout: options.timeout || settings.defaultTimeout,
          ...options
        });
      },
      warning(message, options = {}) {
        if (settings.preventDuplicates && !recentMessages.add(message, settings.duplicatePreventionWindow)) {
          return null; // Prevent duplicate
        }
        
        return notificationInstance.value.warning(message, {
          timeout: options.timeout || settings.defaultTimeout,
          ...options
        });
      },
      info(message, options = {}) {
        if (settings.preventDuplicates && !recentMessages.add(message, settings.duplicatePreventionWindow)) {
          return null; // Prevent duplicate
        }
        
        return notificationInstance.value.info(message, {
          timeout: options.timeout || settings.defaultTimeout,
          ...options
        });
      },
      clear() {
        notificationInstance.value.clear();
      }
    };

    // Also add the notification system to the global app
    app.provide('notify', app.config.globalProperties.$notify);
  }
}; 