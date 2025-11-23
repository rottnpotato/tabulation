<template>
  <div class="min-h-screen bg-gray-100 overflow-auto">
    <SideNav>
      <div class="p-6">
        <slot />
      </div>
    </SideNav>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import SideNav from '@/Components/SideNav.vue'
</script>