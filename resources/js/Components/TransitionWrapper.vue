<template>
  <transition 
    :name="name" 
    :mode="mode" 
    :appear="appear"
    :duration="duration"
  >
    <slot></slot>
  </transition>
</template>

<script setup>
defineProps({
  name: {
    type: String,
    default: 'fade'
  },
  mode: {
    type: String,
    default: 'out-in'
  },
  appear: {
    type: Boolean,
    default: false
  },
  duration: {
    type: [Number, Object],
    default: null
  }
});
</script> 