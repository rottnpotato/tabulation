<template>
  <Head title="Login">
    <meta name="page-transition" content="true" />
  </Head>
  <div class="min-h-screen bg-gradient-to-br from-teal-900 via-emerald-900 to-teal-800 relative overflow-hidden flex items-center justify-center p-6 animate-fade-in">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1571624436279-b272aff752b5?auto=format&fit=crop&q=80')] bg-cover bg-center opacity-10"></div>
    
    <div class="w-full max-w-md relative animate-float">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-xl border border-white/20">
        <div class="flex justify-center mb-8 animate-bounce-subtle">
          <Crown class="h-12 w-12 text-teal-400" />
        </div>
        <h2 class="text-3xl font-bold text-white text-center mb-8">Welcome to Pageant Tabulation System</h2>
        
        <form @submit.prevent="submit" class="space-y-6">
          <div v-if="errors.email" class="bg-red-400/20 border border-red-400/50 rounded-lg p-4 mb-6">
            <div class="flex items-start">
              <AlertCircle class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" />
              <p class="ml-3 text-sm text-red-300">{{ errors.email }}</p>
            </div>
          </div>

          <div class="transition-all duration-300 ease-in-out transform hover:scale-[1.02] focus-within:scale-[1.02]">
            <label for="email" class="block text-sm font-medium text-gray-200 mb-2">Email</label>
            <input 
              id="email" 
              v-model="form.email" 
              type="email" 
              autocomplete="email" 
              required 
              class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition-all duration-300"
              placeholder="Enter your email"
            />
          </div>

          <div class="transition-all duration-300 ease-in-out transform hover:scale-[1.02] focus-within:scale-[1.02]">
            <label for="password" class="block text-sm font-medium text-gray-200 mb-2">Password</label>
            <input 
              id="password" 
              v-model="form.password" 
              type="password" 
              autocomplete="current-password" 
              required 
              class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition-all duration-300"
              placeholder="Enter your password"
            />
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center">
              <input 
                id="remember-me" 
                v-model="form.remember" 
                type="checkbox" 
                class="rounded border-gray-300 text-teal-500 focus:ring-teal-400"
              />
              <span class="ml-2 text-gray-300">Remember me</span>
            </label>
            <a href="#" class="text-teal-400 hover:text-teal-300 transition-colors duration-300">Forgot password?</a>
          </div>

          <button 
            type="submit" 
            class="w-full bg-gradient-to-r from-teal-500 to-emerald-500 text-white py-3 px-4 rounded-lg font-medium hover:from-teal-600 hover:to-emerald-600 transition-all duration-300 transform hover:scale-[1.05] focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 focus:ring-offset-gray-900 flex items-center justify-center"
            :disabled="processing"
          >
            <Loader2 v-if="processing" class="h-5 w-5 animate-spin mr-2" />
            Sign In
          </button>
        </form>

        <div class="mt-8 text-center">
          <Link 
            href="/" 
            class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center justify-center group"
          >
            <ChevronLeft class="h-4 w-4 mr-1 transition-transform duration-300 group-hover:-translate-x-1" />
            Back to home
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Crown, AlertCircle, Loader2, ChevronLeft } from 'lucide-vue-next';

const processing = ref(false);

// Pre-fill credentials for easier testing
const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  processing.value = true;
  form.post('/login', {
    onFinish: () => {
      processing.value = false;
    },
  });
};

const props = defineProps({
  errors: {
    type: Object,
    default: () => ({}),
  },
});
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
  animation: float 8s ease-in-out infinite;
}

.animate-fade-in {
  animation: fadeIn 0.8s ease-out;
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

.animate-bounce-subtle {
  animation: bounceSlight 2s infinite;
}

@keyframes bounceSlight {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

.delay-100 {
  animation-delay: 0.1s;
}

.delay-200 {
  animation-delay: 0.2s;
}
</style> 