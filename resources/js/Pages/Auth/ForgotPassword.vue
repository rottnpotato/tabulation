<template>
  <Head title="Forgot Password">
    <meta name="page-transition" content="true" />
  </Head>
  <div class="min-h-screen bg-gradient-to-br from-teal-900 via-emerald-900 to-teal-800 relative overflow-hidden flex items-center justify-center p-6 animate-fade-in">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1571624436279-b272aff752b5?auto=format&fit=crop&q=80')] bg-cover bg-center opacity-10"></div>
    
    <div class="w-full max-w-md relative animate-float">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-xl border border-white/20">
        <div class="flex justify-center mb-8 animate-bounce-subtle">
          <Mail class="h-12 w-12 text-teal-400" />
        </div>
        <h2 class="text-3xl font-bold text-white text-center mb-4">Forgot Password</h2>
        <p class="text-gray-300 text-center mb-8">Enter your email address and we'll send you a link to reset your password.</p>
        
        <form @submit.prevent="submit" class="space-y-6">
          <div v-if="status" class="bg-green-400/20 border border-green-400/50 rounded-lg p-4 mb-6">
            <div class="flex items-start">
              <CheckCircle class="h-5 w-5 text-green-400 mt-0.5 flex-shrink-0" />
              <p class="ml-3 text-sm text-green-300">{{ status }}</p>
            </div>
          </div>

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

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 px-4 bg-gradient-to-r from-teal-500 to-emerald-500 text-white rounded-lg font-medium hover:from-teal-600 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 focus:ring-offset-teal-900 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95"
          >
            <span v-if="form.processing">Sending...</span>
            <span v-else>Send Reset Link</span>
          </button>

          <div class="text-center">
            <Link
              href="/login"
              class="text-teal-400 hover:text-teal-300 text-sm font-medium transition-colors duration-300"
            >
              Back to Login
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, AlertCircle, CheckCircle } from 'lucide-vue-next';

defineProps({
  status: String,
  errors: Object,
});

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password');
};
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

@keyframes bounce-subtle {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.animate-bounce-subtle {
  animation: bounce-subtle 2s ease-in-out infinite;
}
</style>
