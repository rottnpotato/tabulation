<template>
  <Head title="Email Verified" />
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="bg-gradient-to-r from-teal-500 to-teal-600 py-6 px-8">
        <div class="flex justify-center">
          <svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h1 class="mt-4 text-center text-2xl font-bold text-white">Email Verified Successfully!</h1>
      </div>
      
      <div class="py-8 px-8">
        <div class="text-center">
          <p class="text-gray-700 text-lg mb-6">
            Thank you, <span class="font-semibold">{{ user.name }}</span>! Your organizer account has been verified.
          </p>
          
          <div class="bg-teal-50 border-l-4 border-teal-500 p-4 mb-6">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-teal-700 text-left">
                  For security reasons, we recommend setting a new password for your account.
                </p>
              </div>
            </div>
          </div>
          
          <div class="space-y-4">
            <a 
              :href="resetPasswordUrl" 
              class="block w-full py-3 px-4 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
              Set Your Password
            </a>
            
            <Link 
              href="/login" 
              class="block w-full py-3 px-4 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
              Continue to Login
            </Link>
          </div>
          
          <p class="mt-6 text-sm text-gray-500">
            Need help? Contact the system administrator.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  resetPasswordUrl: {
    type: String,
    required: true
  }
});
</script> 