<template>
  <div class="min-h-screen bg-gradient-to-br from-teal-900 via-emerald-900 to-teal-800 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1571624436279-b272aff752b5?auto=format&fit=crop&q=80')] bg-cover bg-center opacity-10"></div>
    
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
            class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-full transition-all transform hover:scale-105 font-medium"
          >
            Sign In
          </Link>
        </div>
      </nav>

      <!-- Hero Section -->
      <div class="relative container mx-auto px-6 pt-24 pb-32">
        <div class="max-w-3xl animate-fade-in">
          <h1 class="text-5xl md:text-6xl font-bold text-white mb-8 leading-tight">
            Pageant Scoring Made <span class="bg-gradient-to-r from-teal-400 to-emerald-400 bg-clip-text text-transparent">Elegant</span>
          </h1>
          <p class="text-xl text-gray-300 mb-12 font-light">
            Transform your pageant experience with our advanced tabulation system. Precise, transparent, and effortless scoring for competitions of any scale.
          </p>
          <div class="flex flex-col sm:flex-row gap-4">
            <Link 
              href="/login"
              class="flex items-center justify-center bg-teal-500 hover:bg-teal-600 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105"
            >
              Get Started
              <ChevronRight class="ml-2 h-5 w-5" />
            </Link>
            <button @click="scrollToSection('how-it-works')" class="flex items-center justify-center bg-transparent border-2 border-teal-400 text-teal-400 hover:text-white hover:bg-teal-400 px-8 py-4 rounded-full text-lg font-semibold transition-all">
              Learn More
            </button>
          </div>
        </div>
      </div>

      <!-- Features Section -->
      <div id="features" class="relative bg-black bg-opacity-50 py-24">
        <div class="container mx-auto px-6">
          <h2 class="text-3xl font-bold text-white text-center mb-16">Powerful Features</h2>
          <div class="grid md:grid-cols-3 gap-12">
            <div class="p-6 rounded-xl bg-gradient-to-br from-teal-900/50 to-transparent backdrop-blur-sm transform hover:scale-105 transition-all">
              <div class="bg-teal-500 p-3 rounded-full w-fit mb-6">
                <Trophy class="h-6 w-6 text-white" />
              </div>
              <h3 class="text-xl font-semibold text-white mb-4">Real-time Scoring</h3>
              <p class="text-gray-300 font-light">Instant calculations and updates ensure smooth judging process with zero delays.</p>
            </div>
            <div class="p-6 rounded-xl bg-gradient-to-br from-emerald-900/50 to-transparent backdrop-blur-sm transform hover:scale-105 transition-all">
              <div class="bg-emerald-500 p-3 rounded-full w-fit mb-6">
                <Users class="h-6 w-6 text-white" />
              </div>
              <h3 class="text-xl font-semibold text-white mb-4">Multi-judge Support</h3>
              <p class="text-gray-300 font-light">Seamlessly coordinate multiple judges with our intuitive interface.</p>
            </div>
            <div class="p-6 rounded-xl bg-gradient-to-br from-teal-900/50 to-transparent backdrop-blur-sm transform hover:scale-105 transition-all">
              <div class="bg-teal-500 p-3 rounded-full w-fit mb-6">
                <Star class="h-6 w-6 text-white" />
              </div>
              <h3 class="text-xl font-semibold text-white mb-4">Custom Criteria</h3>
              <p class="text-gray-300 font-light">Fully customizable scoring criteria and weightage system for any pageant format.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- How It Works Section -->
      <div id="how-it-works" class="relative py-24">
        <div class="container mx-auto px-6">
          <h2 class="text-3xl font-bold text-white text-center mb-16">How It Works</h2>
          <div class="grid md:grid-cols-4 gap-8">
            <div class="text-center">
              <div class="bg-teal-500/20 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-6 border-2 border-teal-400">
                <span class="text-2xl font-bold text-white">1</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Setup</h3>
              <p class="text-gray-300 font-light">Create your pageant, add contestants, and define scoring criteria</p>
            </div>
            <div class="text-center">
              <div class="bg-teal-500/20 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-6 border-2 border-teal-400">
                <span class="text-2xl font-bold text-white">2</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Invite</h3>
              <p class="text-gray-300 font-light">Add judges and tabulators with specific access permissions</p>
            </div>
            <div class="text-center">
              <div class="bg-teal-500/20 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-6 border-2 border-teal-400">
                <span class="text-2xl font-bold text-white">3</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Score</h3>
              <p class="text-gray-300 font-light">Judges score contestants across multiple rounds and categories</p>
            </div>
            <div class="text-center">
              <div class="bg-teal-500/20 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-6 border-2 border-teal-400">
                <span class="text-2xl font-bold text-white">4</span>
              </div>
              <h3 class="text-xl font-semibold text-white mb-3">Results</h3>
              <p class="text-gray-300 font-light">View detailed rankings and export professional result reports</p>
            </div>
          </div>
          <div class="mt-16 text-center">
            <Link 
              href="/login" 
              class="bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-10 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 hover:from-teal-600 hover:to-emerald-600"
            >
              Start Your Pageant
            </Link>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="relative py-24 bg-black bg-opacity-50">
        <div class="container mx-auto px-6">
          <h2 class="text-3xl font-bold text-white text-center mb-16">Trusted by Organizers</h2>
          <div class="grid md:grid-cols-3 gap-12 text-center">
            <div class="animate-float">
              <div class="text-4xl font-bold text-white mb-2">500+</div>
              <div class="text-teal-400 font-medium">Pageants Powered</div>
            </div>
            <div class="animate-float delay-100">
              <div class="text-4xl font-bold text-white mb-2">10k+</div>
              <div class="text-teal-400 font-medium">Contestants Scored</div>
            </div>
            <div class="animate-float delay-200">
              <div class="text-4xl font-bold text-white mb-2">98%</div>
              <div class="text-teal-400 font-medium">Client Satisfaction</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Section -->
      <div id="contact" class="relative py-24">
        <div class="container mx-auto px-6">
          <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-white mb-8">Ready to Elevate Your Pageant?</h2>
            <p class="text-xl text-gray-300 mb-12">
              Join hundreds of pageant organizers who trust our system for their competitions.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
              <Link 
                href="/login" 
                class="bg-teal-500 hover:bg-teal-600 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105"
              >
                Get Started Now
              </Link>
              <button @click="contactUs" class="bg-transparent border-2 border-teal-400 text-teal-400 hover:text-white hover:bg-teal-400 px-8 py-4 rounded-full text-lg font-semibold transition-all">
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