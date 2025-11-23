<template>
  <div class="min-h-screen relative overflow-hidden">
    <!-- Animated Background -->
    <AnimatedBackground />
    
    <!-- Content Container -->
    <div class="relative">
      <!-- Navigation -->
      <nav class="relative z-10 container mx-auto px-6 py-8">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <Crown class="h-8 w-8 text-teal-400" />
            <span class="text-2xl font-bold bg-gradient-to-r from-teal-400 to-emerald-400 bg-clip-text text-transparent">Pageant Tabulation System</span>
          </div>
          <div class="hidden md:flex space-x-8">
            <a href="#features" class="text-gray-300 hover:text-white transition-colors">Features</a>
            <a href="#how-it-works" class="text-gray-300 hover:text-white transition-colors">How It Works</a>
            <a href="#contact" class="text-gray-300 hover:text-white transition-colors">Contact</a>
          </div>
          <Link 
            href="/login"
            class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-full transition-all transform hover:scale-105 font-medium shadow-lg shadow-teal-500/20"
          >
            Sign In
          </Link>
        </div>
      </nav>

      <!-- Hero Section -->
      <div class="relative container mx-auto px-6 pt-24 pb-32">
        <div class="max-w-3xl animate-fade-in">
          <h1 class="text-5xl md:text-7xl font-bold text-white mb-8 leading-tight tracking-tight">
            Pageant Scoring Made <span class="bg-gradient-to-r from-teal-400 to-emerald-400 bg-clip-text text-transparent">Elegant</span>
          </h1>
          <p class="text-xl text-gray-300 mb-12 font-light leading-relaxed max-w-2xl">
            Transform your pageant experience with our advanced tabulation system. Precise, transparent, and effortless scoring for competitions of any scale.
          </p>
          <div class="flex flex-col sm:flex-row gap-4">
            <Link 
              href="/login"
              class="flex items-center justify-center bg-teal-500 hover:bg-teal-600 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 shadow-xl shadow-teal-500/20"
            >
              Get Started
              <ChevronRight class="ml-2 h-5 w-5" />
            </Link>
            <button @click="scrollToSection('how-it-works')" class="flex items-center justify-center bg-white/5 border border-white/10 backdrop-blur-sm text-teal-400 hover:text-white hover:bg-teal-500/10 px-8 py-4 rounded-full text-lg font-semibold transition-all">
              Learn More
            </button>
          </div>
        </div>
      </div>

      <!-- Features Section -->
      <div id="features" class="relative py-24">
        <div class="container mx-auto px-6">
          <h2 class="text-3xl font-bold text-white text-center mb-16">Powerful Features</h2>
          <div class="grid md:grid-cols-3 gap-12">
            <div class="p-8 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md transform hover:scale-105 transition-all hover:bg-white/10">
              <div class="bg-teal-500/20 p-4 rounded-xl w-fit mb-6">
                <Trophy class="h-8 w-8 text-teal-400" />
              </div>
              <h3 class="text-xl font-semibold text-white mb-4">Real-time Scoring</h3>
              <p class="text-gray-400 font-light leading-relaxed">Instant calculations and updates ensure smooth judging process with zero delays.</p>
            </div>
            <div class="p-8 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md transform hover:scale-105 transition-all hover:bg-white/10">
              <div class="bg-emerald-500/20 p-4 rounded-xl w-fit mb-6">
                <Users class="h-8 w-8 text-emerald-400" />
              </div>
              <h3 class="text-xl font-semibold text-white mb-4">Multi-judge Support</h3>
              <p class="text-gray-400 font-light leading-relaxed">Seamlessly coordinate multiple judges with our intuitive interface.</p>
            </div>
            <div class="p-8 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md transform hover:scale-105 transition-all hover:bg-white/10">
              <div class="bg-teal-500/20 p-4 rounded-xl w-fit mb-6">
                <Star class="h-8 w-8 text-teal-400" />
              </div>
              <h3 class="text-xl font-semibold text-white mb-4">Custom Criteria</h3>
              <p class="text-gray-400 font-light leading-relaxed">Fully customizable scoring criteria and weightage system for any pageant format.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- How It Works Section -->
      <div id="how-it-works" class="relative py-24">
        <div class="container mx-auto px-6">
          <h2 class="text-3xl font-bold text-white text-center mb-16">How It Works</h2>
          <div class="grid md:grid-cols-4 gap-8">
            <div class="text-center group">
              <div class="bg-white/5 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-6 border border-white/10 group-hover:border-teal-500/50 transition-colors">
                <span class="text-3xl font-bold text-teal-400">1</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Setup</h3>
              <p class="text-gray-400 font-light">Create your pageant, add contestants, and define scoring criteria</p>
            </div>
            <div class="text-center group">
              <div class="bg-white/5 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-6 border border-white/10 group-hover:border-teal-500/50 transition-colors">
                <span class="text-3xl font-bold text-teal-400">2</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Invite</h3>
              <p class="text-gray-400 font-light">Add judges and tabulators with specific access permissions</p>
            </div>
            <div class="text-center group">
              <div class="bg-white/5 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-6 border border-white/10 group-hover:border-teal-500/50 transition-colors">
                <span class="text-3xl font-bold text-teal-400">3</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Score</h3>
              <p class="text-gray-400 font-light">Judges score contestants across multiple rounds and categories</p>
            </div>
            <div class="text-center group">
              <div class="bg-white/5 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-6 border border-white/10 group-hover:border-teal-500/50 transition-colors">
                <span class="text-3xl font-bold text-teal-400">4</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Results</h3>
              <p class="text-gray-400 font-light">View detailed rankings and export professional result reports</p>
            </div>
          </div>
          <div class="mt-20 text-center">
            <Link 
              href="/login" 
              class="inline-flex items-center bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-10 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 hover:shadow-lg hover:shadow-teal-500/25"
            >
              Start Your Pageant
            </Link>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="relative py-24 border-y border-white/5 bg-white/5 backdrop-blur-sm">
        <div class="container mx-auto px-6">
          <h2 class="text-3xl font-bold text-white text-center mb-16">Trusted by Organizers</h2>
          <div class="grid md:grid-cols-3 gap-12 text-center">
            <div class="animate-float">
              <div class="text-5xl font-bold text-white mb-2">500+</div>
              <div class="text-teal-400 font-medium uppercase tracking-wider text-sm">Pageants Powered</div>
            </div>
            <div class="animate-float delay-100">
              <div class="text-5xl font-bold text-white mb-2">10k+</div>
              <div class="text-teal-400 font-medium uppercase tracking-wider text-sm">Contestants Scored</div>
            </div>
            <div class="animate-float delay-200">
              <div class="text-5xl font-bold text-white mb-2">98%</div>
              <div class="text-teal-400 font-medium uppercase tracking-wider text-sm">Client Satisfaction</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Section -->
      <div id="contact" class="relative py-24">
        <div class="container mx-auto px-6">
          <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-white mb-8">Ready to Elevate Your Pageant?</h2>
            <p class="text-xl text-gray-300 mb-12 font-light">
              Join hundreds of pageant organizers who trust our system for their competitions.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
              <Link 
                href="/login" 
                class="bg-teal-500 hover:bg-teal-600 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 shadow-lg shadow-teal-500/20"
              >
                Get Started Now
              </Link>
              <button @click="contactUs" class="bg-transparent border border-white/20 text-white hover:bg-white/10 px-8 py-4 rounded-full text-lg font-semibold transition-all">
                Contact Support
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Crown, Star, Trophy, Users, ChevronRight } from 'lucide-vue-next'
import AnimatedBackground from '@/Components/AnimatedBackground.vue'

const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' })
  }
}

const contactUs = () => {
  // Implementation for contact support functionality
  alert('Support team will contact you shortly. Thank you for your interest!')
}
</script>

<style>
@keyframes float {
  0% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
  100% {
    transform: translateY(0px);
  }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

.delay-100 {
  animation-delay: 0.1s;
}

.delay-200 {
  animation-delay: 0.2s;
}

.animate-fade-in {
  animation: fadeIn 1s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>