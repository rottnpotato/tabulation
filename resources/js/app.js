import '../css/app.css';
import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';
import NotificationPlugin from './Plugins/NotificationPlugin';

// Add progress indicator configuration
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// Add page transition event listeners
router.on('start', () => NProgress.start());
router.on('finish', () => NProgress.done());

// Handle 419 CSRF errors globally - let server handle redirects
router.on('error', (event) => {
  if (event.detail.response?.status === 419) {
    // Let the server handle the redirect with flash message
    window.location.reload();
    // return
  }
})

// Configure NProgress
NProgress.configure({ 
    showSpinner: false,
    minimum: 0.1,
    easing: 'ease',
    speed: 400
});

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        // Use the Ziggy configuration as-is from Laravel
        // No need to override it since Laravel generates the correct URL with port
        
        createApp({ render: () => h(App, props) })
            .use(plugin)    
            .use(pinia)
            .use(ZiggyVue, Ziggy)
            .use(NotificationPlugin, {
                defaultTimeout: 4000,
                preventDuplicates: true,
                duplicatePreventionWindow: 5000,
                maxVisible: 3
            })
            .mount(el);
    },
    progress: {
        color: '#10b981', // Teal-600 for consistent color scheme
        showSpinner: false,
    },
});
