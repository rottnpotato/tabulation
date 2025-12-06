<template>
  <div class="fixed inset-0 -z-10 overflow-hidden">
    <!-- Background Image -->
    <div 
      class="absolute inset-0 bg-cover bg-center bg-no-repeat"
      style="background-image: url('/images/background_emerald.png');"
    ></div>
    
    <!-- Subtle Dark Overlay for better text readability -->
    <div class="absolute inset-0 bg-black/20"></div>
    
    <!-- Animated Glow Effects - Subtle overlay animations -->
    <div class="absolute top-1/4 -left-20 w-[40rem] h-[40rem] bg-teal-500/10 rounded-full mix-blend-screen filter blur-[100px] animate-float-slow"></div>
    <div class="absolute bottom-0 right-0 w-[50rem] h-[50rem] bg-emerald-500/5 rounded-full mix-blend-screen filter blur-[120px] animate-float-slower"></div>
  </div>
</template>

<style scoped>
.animate-float-slow {
  animation: float 25s ease-in-out infinite;
}

.animate-float-slower {
  animation: float 35s ease-in-out infinite reverse;
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0);
  }
  33% {
    transform: translate(30px, -50px);
  }
  66% {
    transform: translate(-20px, 20px);
  }
}
</style>
