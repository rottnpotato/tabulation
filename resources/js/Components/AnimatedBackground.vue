<template>
  <div class="fixed inset-0 -z-10 overflow-hidden bg-slate-900">
    <!-- Base Gradient - Darker, more dramatic for stage effect -->
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-teal-950 to-slate-900"></div>
    
    <!-- Spotlight Effects -->
    <div class="absolute top-0 left-1/4 w-1/2 h-full bg-gradient-to-b from-teal-500/10 via-transparent to-transparent transform -skew-x-12 blur-3xl"></div>
    <div class="absolute top-0 right-1/4 w-1/2 h-full bg-gradient-to-b from-emerald-500/10 via-transparent to-transparent transform skew-x-12 blur-3xl"></div>

    <!-- Animated Blobs - Slower, more elegant movement -->
    <div class="absolute top-1/4 -left-20 w-[40rem] h-[40rem] bg-teal-600/20 rounded-full mix-blend-screen filter blur-[100px] animate-float-slow"></div>
    <div class="absolute bottom-0 right-0 w-[50rem] h-[50rem] bg-emerald-600/10 rounded-full mix-blend-screen filter blur-[120px] animate-float-slower"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[60rem] h-[60rem] bg-slate-800/30 rounded-full mix-blend-overlay filter blur-[100px] animate-pulse-slow"></div>
    
    <!-- Sparkle/Dust Particles Effect (CSS only) -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPC9zdmc+')] opacity-20 mask-image-gradient"></div>
    
    <!-- Glass Overlay -->
    <div class="absolute inset-0 backdrop-blur-[1px]"></div>
  </div>
</template>

<style scoped>
.animate-float-slow {
  animation: float 25s ease-in-out infinite;
}

.animate-float-slower {
  animation: float 35s ease-in-out infinite reverse;
}

.animate-pulse-slow {
  animation: pulse 15s ease-in-out infinite;
}

.mask-image-gradient {
  mask-image: radial-gradient(circle at center, black, transparent 80%);
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
  }
  33% {
    transform: translate(30px, -50px) rotate(5deg);
  }
  66% {
    transform: translate(-20px, 20px) rotate(-5deg);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.3;
    transform: translate(-50%, -50%) scale(1);
  }
  50% {
    opacity: 0.5;
    transform: translate(-50%, -50%) scale(1.1);
  }
}
</style>
