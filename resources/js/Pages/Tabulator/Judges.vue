<template>
  <div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Judge Management</h2>
        <button
          @click="showAddModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <Plus class="h-5 w-5 mr-2" />
          Create Judge Account
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="judge in judges" :key="judge.id" class="bg-white border rounded-lg p-6">
          <div class="flex items-center space-x-4">
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
              <User2 class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <h3 class="text-lg font-medium text-gray-900">{{ judge.name }}</h3>
              <p class="text-sm text-gray-500">{{ judge.title }}</p>
            </div>
          </div>
          
          <div class="mt-4 pt-4 border-t">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Status</span>
              <span
                :class="judge.isActive ? 'text-green-600' : 'text-gray-500'"
                class="font-medium"
              >
                {{ judge.isActive ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <div class="flex items-center justify-between text-sm mt-2">
              <span class="text-gray-500">Scores Submitted</span>
              <span class="font-medium text-gray-900">{{ judge.scoresSubmitted }}/24</span>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-2">
            <button
              @click="resetPassword(judge.id)"
              class="text-blue-600 hover:text-blue-700"
            >
              <Key class="h-5 w-5" />
            </button>
            <button
              @click="toggleStatus(judge.id)"
              :class="judge.isActive ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700'"
            >
              <Power class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Judge Modal -->
    <TransitionRoot appear :show="showAddModal" as="template">
      <Dialog as="div" @close="showAddModal = false" class="relative z-10">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
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
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                  Create Judge Account
                </DialogTitle>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input
                      type="text"
                      v-model="form.name"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                      type="email"
                      v-model="form.email"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title/Position</label>
                    <input
                      type="text"
                      v-model="form.title"
                      class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      required
                    />
                  </div>

                  <div class="mt-6 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="showAddModal = false"
                      class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                    >
                      Create Account
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { Plus, User2, Key, Power } from 'lucide-vue-next'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface Judge {
  id: number
  name: string
  title: string
  email: string
  isActive: boolean
  scoresSubmitted: number
}

const showAddModal = ref(false)

const form = ref({
  name: '',
  email: '',
  title: ''
})

const judges = ref<Judge[]>([
  {
    id: 1,
    name: 'Dr. Sarah Wilson',
    title: 'Fashion Industry Expert',
    email: 'sarah.wilson@example.com',
    isActive: true,
    scoresSubmitted: 24
  },
  {
    id: 2,
    name: 'Michael Chen',
    title: 'Celebrity Judge',
    email: 'michael.chen@example.com',
    isActive: true,
    scoresSubmitted: 18
  },
  {
    id: 3,
    name: 'Amanda Rodriguez',
    title: 'Former Miss Universe',
    email: 'amanda.rodriguez@example.com',
    isActive: true,
    scoresSubmitted: 20
  }
])

const handleSubmit = () => {
  judges.value.push({
    id: judges.value.length + 1,
    ...form.value,
    isActive: true,
    scoresSubmitted: 0
  })
  showAddModal.value = false
  form.value = { name: '', email: '', title: '' }
}

const resetPassword = (id: number) => {
  // Simulate password reset
  console.log('Resetting password for judge:', id)
}

const toggleStatus = (id: number) => {
  const judge = judges.value.find(j => j.id === id)
  if (judge) {
    judge.isActive = !judge.isActive
  }
}
</script>