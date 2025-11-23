<template>
  <div class="min-h-screen bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50 flex items-center justify-center px-4">
    <Head title="Access Denied - 403" />
    
    <div class="max-w-2xl w-full">
      <!-- Animated Icon -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-red-100 mb-6 animate-bounce">
          <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        
        <h1 class="text-6xl font-bold text-gray-900 mb-4">403</h1>
        <h2 class="text-3xl font-semibold text-gray-800 mb-4">Access Denied</h2>
        <p class="text-xl text-gray-600 mb-8">{{ message || "You don't have permission to access this resource." }}</p>
      </div>

      <!-- Error Details Card -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <div class="flex items-start space-x-4">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Why am I seeing this?</h3>
            <ul class="text-gray-600 space-y-2">
              <li class="flex items-start">
                <span class="text-red-500 mr-2">•</span>
                <span>You may not have the required permissions for this action</span>
              </li>
              <li class="flex items-start">
                <span class="text-red-500 mr-2">•</span>
                <span>Your role may not have access to this feature</span>
              </li>
              <li class="flex items-start">
                <span class="text-red-500 mr-2">•</span>
                <span>Contact your administrator if you believe this is an error</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button
          @click="goBack"
          class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all duration-200 shadow-lg hover:shadow-xl"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Go Back
        </button>
        
        <Link
          :href="dashboardRoute"
          class="inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all duration-200"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Go to Dashboard
        </Link>
      </div>

      <!-- Help Text -->
      <div class="mt-8 text-center">
        <p class="text-sm text-gray-500">
          Need help? Contact your system administrator or 
          <a href="mailto:support@example.com" class="text-teal-600 hover:text-teal-700 font-medium">email support</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  message: {
    type: String,
    default: "You don't have permission to access this resource."
  }
})

const page = usePage()

const dashboardRoute = computed(() => {
  const user = page.props.auth?.user
  if (!user) return '/'
  
  switch (user.role) {
    case 'admin':
      return '/admin/dashboard'
    case 'organizer':
      return '/organizer/dashboard'
    case 'tabulator':
      return '/tabulator/dashboard'
    case 'judge':
      return '/judge/dashboard'
    default:
      return '/'
  }
})

const goBack = () => {
  router.visit(window.history.length > 1 ? -1 : dashboardRoute.value)
}
</script>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-bounce {
  animation: bounce 2s infinite;
}
</style>
