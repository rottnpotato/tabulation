<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="$emit('close')">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                        >
                            <form @submit.prevent="submitRequest">
                                <div>
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                        <LockClosedIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" aria-hidden="true" />
                                    </div>
                                    <div class="mt-3 text-center sm:mt-5">
                                        <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
                                            Request Edit Access
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                This pageant is currently ongoing and locked for editing. Please provide a reason why you need edit access.
                                            </p>
                                        </div>
                                        <div class="mt-4">
                                            <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">
                                                Reason for Edit Access
                                            </label>
                                            <textarea
                                                v-model="form.reason"
                                                id="reason"
                                                rows="4"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                placeholder="Please explain why you need to edit this pageant..."
                                                required
                                            ></textarea>
                                            <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600 dark:text-red-400 text-left">
                                                {{ form.errors.reason }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-left">
                                                Minimum 10 characters, maximum 500 characters
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                    <button
                                        type="submit"
                                        :disabled="form.processing || form.reason.length < 10"
                                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:col-start-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span v-if="!form.processing">Submit Request</span>
                                        <span v-else>Submitting...</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="$emit('close')"
                                        :disabled="form.processing"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:col-start-1 sm:mt-0 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { LockClosedIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    open: Boolean,
    pageantId: Number,
});

const emit = defineEmits(['close']);

const form = useForm({
    reason: '',
});

const submitRequest = () => {
    form.post(route('organizer.pageant.request-edit-access', props.pageantId), {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
};
</script>
