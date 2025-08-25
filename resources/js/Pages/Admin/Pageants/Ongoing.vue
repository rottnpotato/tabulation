<template>
  <Head title="Ongoing Pageants" />
  <div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6">Ongoing Pageants</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link 
          v-for="pageant in props.pageants" 
          :key="pageant.id" 
          :href="route('admin.pageants.detail', { id: pageant.id })"
          class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full relative overflow-hidden group cursor-pointer"
        >
          <!-- Cover image with gradient overlay -->
          <div class="h-48 w-full overflow-hidden relative">
            <img 
              :src="pageant.coverImage || '/images/placeholders/pageant-cover.jpg'" 
              :alt="pageant.name || 'Pageant'" 
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <div class="absolute bottom-3 left-4 right-4 flex justify-between items-center">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                <Clock class="h-3 w-3 mr-1" />
                {{ pageant.status || 'Draft' }}
              </span>
              <span class="text-white text-xs bg-teal-600/70 px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                View Details
              </span>
            </div>
          </div>
          
          <div class="p-5 flex-grow flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ pageant.name || 'Untitled Pageant' }}</h3>
            <p class="text-gray-600 mb-4 text-sm line-clamp-3">{{ pageant.description || 'No description provided. Click to view and update pageant details.' }}</p>
            
            <div class="space-y-3 mt-auto">
              <div class="flex items-center text-sm text-gray-500">
                <Calendar class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.start_date ? pageant.start_date : 'Date not set' }}
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <MapPin class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.location || pageant.venue || 'Venue not specified' }}
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <Users class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.contestants_count || 0 }} Contestants
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <Timer class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.currentRound || pageant.status || 'Setup Phase' }}
              </div>
            </div>

            <!-- Progress indicator -->
            <div class="mt-5">
              <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span>Progress</span>
                <span>{{ pageant.progress || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-teal-600 h-2 rounded-full" :style="{ width: `${pageant.progress || 0}%` }"></div>
              </div>
            </div>
          </div>
          
          <!-- Arrow indicator for clickable card -->
          <div class="absolute top-1/2 right-3 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="bg-teal-600 text-white rounded-full p-1.5 shadow-md">
              <ChevronRight class="h-4 w-4" />
            </div>
          </div>
        </Link>
      </div>
      
      <!-- Empty state -->
      <div v-if="props.pageants.length === 0" class="text-center py-12">
        <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <Calendar class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No ongoing pageants</h3>
        <p class="text-sm text-gray-500 max-w-md mx-auto">
          There are currently no ongoing pageants. Create a new pageant to get started.
        </p>
        <div class="mt-6">
          <Link 
            :href="route('admin.pageants.create')" 
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded-md font-medium transition-colors duration-300"
          >
            <Plus class="h-4 w-4 mr-2" />
            Create New Pageant
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, MapPin, Clock, Users, Timer, Eye, Plus, ChevronRight } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Page setup
defineOptions({
  layout: AdminLayout,
});

// Props
const props = defineProps({
  pageants: {
    type: Array,
    default: () => [],
  }
});

// Helper function to format date in a more readable format
const FormatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};


</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 