<template>
  <div class="space-y-6">
    <!-- Header Section with Gradient Background -->
    <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-teal-100">
      <!-- Abstract Background Pattern -->
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-teal-50 via-teal-50/50 to-white opacity-90"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
      </div>

      <div class="relative z-10 p-8">
        <div class="flex items-center justify-between">
          <div class="space-y-2">
            <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">Contestants</h1>
            <p class="text-slate-500 text-lg font-light">Manage contestants across all your pageants</p>
          </div>
          <button
            v-if="PageantSummaries && PageantSummaries.length > 0"
            @click="openAddContestantModal"
            class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-lg shadow-teal-200 hover:shadow-teal-300 transition-all transform hover:-translate-y-0.5 flex items-center"
          >
            <Plus class="h-4 w-4 mr-2" />
            Add Contestant
          </button>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="bg-teal-100 border-l-4 border-teal-500 text-teal-700 p-4 rounded-lg shadow-sm flex items-center justify-between transition-all duration-500 ease-in-out">
      <div class="flex items-center">
        <svg class="h-5 w-5 mr-2 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <p>{{ successMessage }}</p>
      </div>
      <button @click="successMessage = ''" class="text-teal-700 hover:text-teal-900">
        <XCircle class="h-5 w-5" />
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="formErrors.general" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm flex items-center justify-between transition-all duration-500 ease-in-out">
      <div class="flex items-center">
        <svg class="h-5 w-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <p>{{ formErrors.general }}</p>
      </div>
      <button @click="delete formErrors.general" class="text-red-700 hover:text-red-900">
        <XCircle class="h-5 w-5" />
      </button>
    </div>

    <!-- Contestant List -->
    <div class="bg-white shadow-sm rounded-2xl overflow-hidden border border-slate-100">
      <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-slate-800 flex items-center">
            <Users class="h-5 w-5 mr-2 text-teal-500" />
            All Contestants 
            <span class="ml-2 text-gray-500 text-sm font-normal">({{ TotalContestants || 0 }})</span>
          </h2>
          
          <!-- Filter by Pageant -->
          <div class="flex items-center space-x-4">
            <div class="min-w-[200px]">
              <CustomSelect
                v-model="selectedPageant"
                :options="pageantOptions"
                placeholder="All Pageants"
                variant="teal"
                @change="filterContestants"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="p-6">
        <!-- No contestants message -->
        <div v-if="filteredContestants.length === 0" class="text-center py-12">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-teal-50 mb-4">
            <Users class="h-12 w-12 text-teal-500" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-1">
            {{ PageantSummaries && PageantSummaries.length === 0 ? 'No pageants created yet' : 'No contestants found' }}
          </h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            {{ PageantSummaries && PageantSummaries.length === 0 
                ? 'Create your first pageant to start adding contestants and managing competitions'
                : selectedPageant 
                  ? 'No contestants found for the selected pageant. Add contestants to get started.'
                  : 'Add contestants to your pageants to build your competition roster' 
            }}
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <button
              v-if="PageantSummaries && PageantSummaries.length > 0"
              @click="ShowAddModal = true"
              class="px-6 py-3 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-lg shadow-teal-200 hover:shadow-teal-300 transition-all transform hover:-translate-y-0.5"
            >
              <Plus class="h-4 w-4 inline mr-2" />
              Add Contestant
            </button>
            <Link
              :href="route('organizer.pageants.create')"
              class="px-6 py-3 text-sm font-medium text-teal-700 hover:text-teal-900 bg-teal-50 border border-teal-200 hover:border-teal-300 rounded-xl transition-all shadow-sm hover:shadow-md flex items-center"
            >
              <Plus class="h-4 w-4 mr-2" />
              {{ PageantSummaries && PageantSummaries.length === 0 ? 'Create First Pageant' : 'Create New Pageant' }}
            </Link>
          </div>
        </div>

        <!-- Contestants Grid -->
        <ContestantsGrid
          v-else
          :contestants="filteredContestants"
          @view="ViewContestantDetails"
          @edit="EditContestant"
          @delete="(c) => DeleteContestant(c.id)"
        />
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <TransitionRoot appear :show="ShowAddModal" as="template">
      <Dialog as="div" @close="ShowAddModal = false" class="relative z-30">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
                <div class="relative border-b border-gray-200 p-6">
                  <DialogTitle as="h3" class="text-2xl font-bold text-gray-800 leading-6">
                    {{ EditingContestant ? 'Edit Contestant' : 'Add New Contestant' }}
                  </DialogTitle>
                  <p class="mt-2 text-gray-600 max-w-2xl">
                    {{ EditingContestant ? 'Update contestant information' : 'Add a new contestant to your pageant' }}
                  </p>
                  <button 
                    @click="ShowAddModal = false" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                  >
                    <XCircle class="h-6 w-6" />
                  </button>
                </div>

                <form @submit.prevent="HandleSubmit" class="p-6">
                  <!-- Pageant Selection (only show when creating new) -->
                  <div v-if="!EditingContestant" class="mb-6 pb-6 border-b border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Select Pageant <span class="text-red-500">*</span>
                    </label>
                    <CustomSelect
                      v-model="selectedPageant"
                      :options="pageantOptions"
                      placeholder="Choose a pageant"
                      variant="teal"
                      required
                    />
                    <p class="mt-1 text-xs text-gray-500">Select which pageant this contestant belongs to</p>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="contestNumber" class="block text-sm font-medium text-gray-700 mb-1">
                          Contestant Number <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="contestNumber"
                          v-model="Form.contestNumber"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 001"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's competition number</p>
                      </div>

                      <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                          Full Name <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="name"
                          v-model="Form.name"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. Jane Smith"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's full name</p>
                      </div>

                      <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-1">
                          Age <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="age"
                          v-model="Form.age"
                          type="number"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 24"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's age</p>
                      </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                          Location <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="city"
                          v-model="Form.city"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. New York, USA"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's hometown or representation</p>
                      </div>

                      <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                          Gender <span class="text-red-500">*</span>
                        </label>
                        <select
                          id="gender"
                          v-model="Form.gender"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                          required
                        >
                          <option value="" disabled>Select gender</option>
                          <option value="male">Male</option>
                          <option value="female">Female</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Used for pairing rules.</p>
                      </div>

                      <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                          Biography <span class="text-red-500">*</span>
                        </label>
                        <textarea
                          id="bio"
                          v-model="Form.bio"
                          rows="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 transition-colors"
                          placeholder="Share the contestant's background, achievements, interests..."
                          required
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Add a brief biography for the contestant</p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Photos Section (Full Width) -->
                  <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Photos <span class="text-red-500">*</span>
                    </label>
                    
                    <!-- Current Photos Preview (if editing) -->
                    <div v-if="Form.photos && Form.photos.length > 0" class="mb-4">
                      <div class="flex flex-wrap gap-3">
                        <div 
                          v-for="(photo, index) in Form.photos" 
                          :key="index" 
                          class="relative w-24 h-24 rounded-lg overflow-hidden group"
                        >
                          <img :src="photo" class="w-full h-full object-cover" />
                          <button 
                            @click.prevent="removePhoto(index)"
                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white"
                          >
                            <Trash2 class="h-5 w-5" />
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Upload New Photos -->
                    <div class="flex flex-col w-full">
                      <label
                        class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg border-gray-300 hover:border-teal-400 hover:bg-teal-50 transition-colors cursor-pointer"
                      >
                        <div class="flex flex-col items-center justify-center pt-7">
                          <Camera class="w-8 h-8 text-teal-400 group-hover:text-teal-600" />
                          <p class="pt-1 text-sm tracking-wider text-gray-600 group-hover:text-gray-600">
                            Upload contestant photos
                          </p>
                          <p class="text-xs text-gray-500 mt-1">
                            Drag & drop files here or click to browse
                          </p>
                        </div>
                        <input
                          type="file"
                          @change="handlePhotoChange"
                          class="opacity-0 absolute"
                          accept="image/*"
                          multiple
                        />
                      </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Upload photos of the contestant. First photo will be used as the main profile image.</p>
                  </div>

                  <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="ShowAddModal = false"
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="isSubmitting"
                      class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <span v-if="isSubmitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ EditingContestant ? 'Saving...' : 'Adding...' }}
                      </span>
                      <span v-else>
                        {{ EditingContestant ? 'Save Changes' : 'Add Contestant' }}
                      </span>
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- View Contestant Details Modal -->
    <TransitionRoot appear :show="ShowDetailsModal" as="template">
      <Dialog as="div" @close="ShowDetailsModal = false" class="relative z-30">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-7xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
                <div v-if="SelectedContestant" class="flex flex-col md:flex-row w-full">
                  <!-- Photo Carousel Section - Left side on larger screens, top on mobile -->
                  <div class="md:w-[60%] relative">
                    <div class="relative h-full md:h-[600px]">
                      <!-- Main Photo with Animation -->
                      <motion.div
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ duration: 0.3 }"
                        class="w-full h-full absolute inset-0"
                        :key="currentPhotoIndex"
                      >
                        <img 
                          :src="activePhoto || SelectedContestant.photo" 
                          :alt="SelectedContestant.name" 
                          class="w-full h-full object-cover"
                        />
                      </motion.div>
                      
                      <!-- Gradient overlay -->
                      <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-70"></div>
                      
                      <!-- Contestant number badge -->
                      <div class="absolute top-4 right-4">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-teal-500 text-white text-lg font-bold shadow-md">
                          #{{ SelectedContestant.contestNumber }}
                        </span>
                      </div>
                      
                      <!-- Photo Navigation Controls -->
                      <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4">
                        <button 
                          @click="prevPhoto" 
                          class="bg-black/20 backdrop-blur-sm text-white rounded-full p-2 hover:bg-black/40 transition-colors"
                          :class="{'opacity-50 cursor-not-allowed': currentPhotoIndex <= 0}"
                          :disabled="currentPhotoIndex <= 0"
                        >
                          <ChevronLeft class="h-6 w-6" />
                        </button>
                        <button 
                          @click="nextPhoto" 
                          class="bg-black/20 backdrop-blur-sm text-white rounded-full p-2 hover:bg-black/40 transition-colors"
                          :class="{'opacity-50 cursor-not-allowed': currentPhotoIndex >= contestantPhotos.length - 1}"
                          :disabled="currentPhotoIndex >= contestantPhotos.length - 1"
                        >
                          <ChevronRight class="h-6 w-6" />
                        </button>
                      </div>
                      
                      <!-- Thumbnails row at bottom -->
                      <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
                        <div class="flex gap-2 overflow-x-auto pb-2 snap-x">
                          <motion.div
                            v-for="(photo, index) in contestantPhotos" 
                            :key="index"
                            @click="setActivePhoto(photo, index)"
                            class="h-14 w-14 flex-shrink-0 rounded-md overflow-hidden cursor-pointer snap-start transition-all"
                            :class="{ 'ring-2 ring-white': currentPhotoIndex === index }"
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ duration: 0.3, delay: index * 0.1 }"
                          >
                            <img :src="photo" class="h-full w-full object-cover" />
                          </motion.div>
                        </div>
                      </div>
                      
                      <!-- Name overlay at bottom of image on mobile only -->
                      <div class="absolute bottom-16 left-0 right-0 p-6 text-white md:hidden">
                        <h2 class="text-2xl font-bold">{{ SelectedContestant.name }}</h2>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Details Section - Right side on larger screens, bottom on mobile -->
                  <div class="md:w-[40%] p-6 relative">
                    <!-- Close button -->
                    <button 
                      @click="ShowDetailsModal = false" 
                      class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors"
                    >
                      <XCircle class="h-6 w-6" />
                    </button>
                    
                    <!-- Details content -->
                    <div class="space-y-6">
                      <!-- Name - hidden on mobile (shown over image) -->
                      <div class="hidden md:block">
                        <h2 class="text-3xl font-bold text-gray-900">{{ SelectedContestant.name }}</h2>
                      </div>
                      
                      <!-- Info cards -->
                      <div class="grid grid-cols-2 gap-4">
                        <div class="bg-teal-50 rounded-lg p-4">
                          <div class="flex items-center">
                            <Calendar class="h-5 w-5 text-teal-600 mr-2" />
                            <span class="text-sm font-medium text-gray-600">Age</span>
                          </div>
                          <p class="mt-1 text-lg font-semibold text-gray-900">{{ SelectedContestant.age }} years</p>
                        </div>
                        
                        <div class="bg-teal-50 rounded-lg p-4">
                          <div class="flex items-center">
                            <MapPin class="h-5 w-5 text-teal-600 mr-2" />
                            <span class="text-sm font-medium text-gray-600">Location</span>
                          </div>
                          <p class="mt-1 text-lg font-semibold text-gray-900">{{ SelectedContestant.city }}</p>
                        </div>
                      </div>
                      
                      <!-- Biography -->
                      <div class="bg-white border border-gray-200 rounded-lg p-5">
                        <h3 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                          <FileText class="h-5 w-5 text-teal-500 mr-2" />
                          Biography
                        </h3>
                        <p class="text-gray-700 leading-relaxed">{{ SelectedContestant.bio }}</p>
                      </div>
                      
                      <!-- Actions -->
                      <div class="flex justify-end space-x-3 pt-4">
                        <button
                          @click="EditContestant(SelectedContestant); ShowDetailsModal = false"
                          class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 rounded-lg shadow-sm hover:shadow transition-all flex items-center"
                        >
                          <Edit2 class="h-4 w-4 mr-2" />
                          Edit
                        </button>
                        <button
                          @click="DeleteContestant(SelectedContestant.id); ShowDetailsModal = false"
                          class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-lg shadow-sm hover:shadow transition-all flex items-center"
                        >
                          <Trash2 class="h-4 w-4 mr-2" />
                          Delete
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { 
  Plus, 
  MapPin, 
  Calendar, 
  Edit2, 
  Trash2, 
  XCircle, 
  Camera, 
  Users, 
  Eye, 
  FileText, 
  ChevronLeft, 
  ChevronRight,
  Award
} from 'lucide-vue-next'
import ContestantsGrid from '@/Components/ContestantsGrid.vue'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import CustomSelect from '@/Components/CustomSelect.vue'
import { motion } from 'motion-v'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  Contestants: Array,
  PageantSummaries: Array,
  TotalContestants: Number,
})

const ShowAddModal = ref(false)
const ShowDetailsModal = ref(false)
const EditingContestant = ref(null)
const SelectedContestant = ref(null)
const currentPhotoIndex = ref(0)
const activePhoto = ref(null)
const selectedPageant = ref('')

// Form submission states
const isSubmitting = ref(false)
const formErrors = ref({})
const successMessage = ref('')
const imageFiles = ref([])

// Image loading states
const imageLoadingStates = ref({})

// Create options for pageant filter
const pageantOptions = computed(() => [
  { value: '', label: 'All Pageants' },
  ...props.PageantSummaries?.map(pageant => ({
    value: pageant.id,
    label: `${pageant.name} (${pageant.contestant_count})`
  })) || []
])

// Filter contestants based on selected pageant
const filteredContestants = computed(() => {
  if (!selectedPageant.value) {
    return props.Contestants || []
  }
  return (props.Contestants || []).filter(contestant => 
    contestant.pageant.id == selectedPageant.value
  )
})

const Form = ref({
  name: '',
  age: '',
  gender: '',
  city: '',
  bio: '',
  photos: [], // Array to store multiple photos
  contestNumber: ''
})



// Computed property to get all photos for current selected contestant
const contestantPhotos = computed(() => {
  if (!SelectedContestant.value) return []
  return SelectedContestant.value.photos || [SelectedContestant.value.photo]
})

const HandleSubmit = async () => {
  isSubmitting.value = true
  Object.keys(formErrors.value).forEach(key => delete formErrors.value[key])
  
  try {
    const formData = new FormData()
    formData.append('name', Form.value.name)
    formData.append('number', Form.value.contestNumber)
    
    if (Form.value.age) formData.append('age', Form.value.age)
    if (Form.value.gender) formData.append('gender', Form.value.gender)
    if (Form.value.city) formData.append('origin', Form.value.city)
    if (Form.value.bio) formData.append('bio', Form.value.bio)
    
    // Add images from file inputs
    imageFiles.value.forEach(file => {
      formData.append('images[]', file)
    })
    
    if (EditingContestant.value) {
      // Update existing contestant
      const response = await fetch(`/organizer/pageant/${EditingContestant.value.pageant.id}/contestants/${EditingContestant.value.id}`, {
        method: 'POST',
        headers: {
          'X-HTTP-Method-Override': 'PUT',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: formData
      })
      
      if (!response.ok) {
        throw new Error('Failed to update contestant')
      }
      
      const result = await response.json()
      if (result.success) {
        // Reload page to get updated data
        router.reload({ only: ['Contestants', 'PageantSummaries'] })
      }
    } else {
      // Create new contestant - need pageant selection
      if (!selectedPageant.value) {
        formErrors.value.general = 'Please select a pageant first'
        isSubmitting.value = false
        return
      }
      
      const response = await fetch(`/organizer/pageant/${selectedPageant.value}/contestants`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: formData
      })
      
      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to create contestant')
      }
      
      const result = await response.json()
      if (result.success) {
        // Reload page to get updated data
        router.reload({ only: ['Contestants', 'PageantSummaries'] })
      }
    }
    
  } catch (error) {
    console.error('Error submitting contestant:', error)
    
    if (error.response && error.response.data && error.response.data.errors) {
      Object.assign(formErrors.value, error.response.data.errors)
    } else {
      formErrors.value.general = error.message || 'An error occurred while saving the contestant. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

const ViewContestantDetails = (contestant) => {
  SelectedContestant.value = contestant
  currentPhotoIndex.value = 0
  activePhoto.value = contestant.photos?.[0] || contestant.photo
  ShowDetailsModal.value = true
}

const EditContestant = (contestant) => {
  EditingContestant.value = contestant
  Form.value = { 
    name: contestant.name || '',
    contestNumber: contestant.number || contestant.contestNumber || '',
    age: contestant.age || '',
    gender: contestant.gender || '',
    city: contestant.origin || contestant.city || '',
    bio: contestant.bio || '',
    photos: contestant.photos || (contestant.photo ? [contestant.photo] : [])
  }
  
  // Reset image files for editing
  imageFiles.value = []
  
  ShowAddModal.value = true
}

const DeleteContestant = async (id) => {
  if (!confirm('Are you sure you want to delete this contestant?')) {
    return
  }
  
  try {
    const contestant = filteredContestants.value.find(c => c.id === id)
    const pageantId = contestant?.pageant?.id
    
    if (!pageantId) {
      formErrors.value.general = 'Could not determine pageant for this contestant'
      return
    }
    
    const response = await fetch(`/organizer/pageant/${pageantId}/contestants/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      successMessage.value = 'Contestant deleted successfully!'
      // Refresh data - in real app this would trigger a parent component refresh
      // For now we'll just show success message
      
      // Clear success message after 5 seconds
      setTimeout(() => {
        successMessage.value = ''
      }, 5000)
    } else {
      throw new Error('Failed to delete contestant')
    }
  } catch (error) {
    console.error('Error deleting contestant:', error)
    formErrors.value.general = 'Failed to delete contestant. Please try again.'
  }
}

const handlePhotoChange = (event) => {
  const files = event.target.files
  if (!files || files.length === 0) return
  
  // Store actual File objects for upload
  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    imageFiles.value.push(file)
    
    // Create preview URLs for display
    const reader = new FileReader()
    reader.onloadend = () => {
      Form.value.photos.push(reader.result)
    }
    reader.readAsDataURL(file)
  }
}

const removePhoto = (index) => {
  Form.value.photos.splice(index, 1)
}

const nextPhoto = () => {
  if (currentPhotoIndex.value < contestantPhotos.value.length - 1) {
    currentPhotoIndex.value++
    activePhoto.value = contestantPhotos.value[currentPhotoIndex.value]
  }
}

const prevPhoto = () => {
  if (currentPhotoIndex.value > 0) {
    currentPhotoIndex.value--
    activePhoto.value = contestantPhotos.value[currentPhotoIndex.value]
  }
}

const setActivePhoto = (photo, index) => {
  activePhoto.value = photo
  currentPhotoIndex.value = index
}

const openAddContestantModal = () => {
  // If no pageant is selected and there are pageants available, pre-select the first one
  if (!selectedPageant.value && props.PageantSummaries && props.PageantSummaries.length > 0) {
    selectedPageant.value = props.PageantSummaries[0].id
  }
  
  // Validate that a pageant is selected
  if (!selectedPageant.value) {
    formErrors.value.general = 'Please create a pageant first before adding contestants'
    return
  }
  
  ShowAddModal.value = true
}

const resetForm = () => {
  Form.value = { 
    name: '', 
    age: '', 
    gender: '',
    city: '', 
    bio: '', 
    photos: [], 
    contestNumber: '' 
  }
  imageFiles.value = []
  Object.keys(formErrors.value).forEach(key => delete formErrors.value[key])
}

const filterContestants = () => {
  // This will trigger the computed property to recalculate
}

const getStatusClasses = (status) => {
  const statusMap = {
    'Draft': 'bg-gray-100 text-gray-800',
    'Setup': 'bg-teal-100 text-teal-800',
    'Active': 'bg-teal-100 text-teal-800',
    'Completed': 'bg-purple-100 text-purple-800',
    'Pending_Approval': 'bg-yellow-100 text-yellow-800',
    'Cancelled': 'bg-red-100 text-red-800',
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
  const statusMap = {
    'Draft': 'Draft',
    'Setup': 'In Setup',
    'Active': 'Active',
    'Completed': 'Completed',
    'Pending_Approval': 'Pending Approval',
    'Cancelled': 'Cancelled',
  }
  return statusMap[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return 'TBA'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Image handling functions
const handleImageError = (event) => {
  const img = event.target
  const contestantId = img.closest('[data-contestant-id]')?.dataset.contestantId
  if (contestantId) {
    imageLoadingStates.value[contestantId] = 'error'
  }
  // Set a fallback image
  img.src = '/images/placeholders/placeholder-contestant.jpg'
}

const handleImageLoad = (event) => {
  const img = event.target
  const contestantId = img.closest('[data-contestant-id]')?.dataset.contestantId
  if (contestantId) {
    imageLoadingStates.value[contestantId] = 'loaded'
  }
}
</script>

<style scoped>
/* Custom styling for the dialog panel to significantly increase width */
:deep(.max-w-7xl) {
  max-width: 60vw !important; /* Use almost full viewport width */
  width: 1920px !important; /* Set a very large base width */
}

/* Adjust image section height for the larger modal */
:deep(.md\:h-\[600px\]) {
  height: 80vh !important; /* Make image height responsive to viewport */
  max-height: 800px !important; /* But cap it at a reasonable max */
}

.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
