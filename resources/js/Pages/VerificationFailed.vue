<template>
  <Head title="Verification Failed" />
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="bg-gradient-to-r from-red-500 to-red-600 py-6 px-8">
        <div class="flex justify-center">
          <svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h1 class="mt-4 text-center text-2xl font-bold text-white">Verification Failed</h1>
      </div>
      
      <div class="py-8 px-8">
        <div class="text-center">
          <p class="text-gray-700 text-lg mb-6">
            We're sorry, but there was a problem verifying your email address.
          </p>
          
          <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700 text-left">
                  {{ message }}
                </p>
              </div>
            </div>
          </div>
          
          <p class="mb-6 text-gray-600">
            Possible reasons:
          </p>
          
          <ul class="text-gray-600 text-sm text-left list-disc pl-6 mb-6 space-y-2">
            <li>The verification link has expired</li>
            <li>The verification link was already used</li>
            <li>The verification token is invalid</li>
          </ul>
          
          <div class="space-y-4">
            <Link 
              href="/login" 
              class="block w-full py-3 px-4 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Return to Login
            </Link>
            
            <a 
              href="mailto:admin@pageantmanagement.com"
              class="block w-full py-3 px-4 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Contact Administrator
            </a>
          </div>
          
          <p class="mt-6 text-sm text-gray-500">
            If you're an organizer trying to verify your account, please ask the administrator to resend your verification email.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  message: {
    type: String,
    default: 'The verification link is invalid or has expired.'
  }
});
</script> 