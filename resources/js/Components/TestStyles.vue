<template>
  <div class="w-full max-w-4xl mx-auto p-8 bg-white rounded-lg shadow-lg">
    <h2 class="text-3xl font-bold text-center text-primary-600 mb-6">Style Test Component</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="p-4 bg-primary-100 rounded-lg">
        <h3 class="text-xl font-semibold text-primary-800 mb-2">Primary Colors</h3>
        <div class="flex flex-wrap gap-2">
          <div class="h-10 w-10 rounded-md bg-primary-50"></div>
          <div class="h-10 w-10 rounded-md bg-primary-100"></div>
          <div class="h-10 w-10 rounded-md bg-primary-200"></div>
          <div class="h-10 w-10 rounded-md bg-primary-300"></div>
          <div class="h-10 w-10 rounded-md bg-primary-400"></div>
          <div class="h-10 w-10 rounded-md bg-primary-500"></div>
          <div class="h-10 w-10 rounded-md bg-primary-600"></div>
          <div class="h-10 w-10 rounded-md bg-primary-700"></div>
          <div class="h-10 w-10 rounded-md bg-primary-800"></div>
          <div class="h-10 w-10 rounded-md bg-primary-900"></div>
        </div>
      </div>
      
      <div class="p-4 bg-gray-100 rounded-lg">
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Tailwind Default Colors</h3>
        <div class="flex flex-wrap gap-2">
          <div class="h-10 w-10 rounded-md bg-red-500"></div>
          <div class="h-10 w-10 rounded-md bg-orange-500"></div>
          <div class="h-10 w-10 rounded-md bg-yellow-500"></div>
          <div class="h-10 w-10 rounded-md bg-green-500"></div>
          <div class="h-10 w-10 rounded-md bg-blue-500"></div>
          <div class="h-10 w-10 rounded-md bg-indigo-500"></div>
          <div class="h-10 w-10 rounded-md bg-purple-500"></div>
          <div class="h-10 w-10 rounded-md bg-pink-500"></div>
        </div>
      </div>
    </div>
    
    <div class="p-4 bg-gray-100 rounded-lg mb-8">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Typography</h3>
      <h1 class="text-4xl font-bold mb-2">Heading 1 (text-4xl)</h1>
      <h2 class="text-3xl font-semibold mb-2">Heading 2 (text-3xl)</h2>
      <h3 class="text-2xl font-medium mb-2">Heading 3 (text-2xl)</h3>
      <h4 class="text-xl mb-2">Heading 4 (text-xl)</h4>
      <p class="text-base mb-2">Regular paragraph text (text-base)</p>
      <p class="text-sm mb-2">Small text (text-sm)</p>
      <p class="text-xs">Extra small text (text-xs)</p>
    </div>
    
    <div class="p-4 bg-gray-100 rounded-lg mb-8">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Animations</h3>
      <div class="flex gap-4">
        <div class="animate-float p-4 bg-blue-500 text-white rounded-lg">Float Animation</div>
        <div class="animate-fade-in p-4 bg-green-500 text-white rounded-lg">Fade In Animation</div>
        <div class="shimmer h-10 w-32 rounded-lg"></div>
        <div class="shimmer-teal h-10 w-32 rounded-lg"></div>
      </div>
    </div>
    
    <div class="p-4 bg-gray-100 rounded-lg">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Buttons</h3>
      <div class="flex flex-wrap gap-4">
        <button class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg">
          Primary Button
        </button>
        <button class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
          Secondary Button
        </button>
        <button class="bg-transparent border border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white px-4 py-2 rounded-lg transition-colors">
          Outline Button
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// No script needed
</script> 