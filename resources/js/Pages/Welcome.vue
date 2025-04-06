<template>
  <MainLayout title="Welcome">
    <div class="bg-white p-8 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-4">Welcome to Laravel 12 with Inertia and Vue</h2>
      <p class="mb-4">Your application has been successfully converted!</p>
      <div class="animate-fade-in bg-green-50 p-4 rounded-md border border-green-200">
        <p class="text-green-800">Your Vue components will be rendered here.</p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
</script> 