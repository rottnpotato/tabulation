<template>
    <div v-if="pageant.status === 'Ongoing' && !pageant.is_temporarily_editable" class="mb-6">
        <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <LockClosedIcon class="h-5 w-5 text-yellow-400" aria-hidden="true" />
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Pageant Locked - Editing Restricted
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>
                            This pageant is currently ongoing. Most editing features are locked to maintain data integrity during the event.
                            Only judges can submit scores and tabulators can navigate between pageants.
                        </p>
                    </div>
                    <div v-if="!hasPendingRequest" class="mt-4">
                        <button
                            @click="$emit('request-edit-access')"
                            type="button"
                            class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600"
                        >
                            <KeyIcon class="-ml-0.5 mr-1.5 h-4 w-4" aria-hidden="true" />
                            Request Edit Access
                        </button>
                    </div>
                    <div v-else class="mt-4">
                        <div class="flex items-center text-sm text-yellow-700 dark:text-yellow-300">
                            <ClockIcon class="h-4 w-4 mr-1.5" aria-hidden="true" />
                            <span class="font-medium">Edit access request pending approval</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else-if="pageant.status === 'Ongoing' && pageant.is_temporarily_editable" class="mb-6">
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <CheckCircleIcon class="h-5 w-5 text-green-400" aria-hidden="true" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                        Temporary Edit Access Granted
                    </h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        <p>
                            You have been granted temporary edit access for this ongoing pageant. Please make your changes carefully.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { LockClosedIcon, KeyIcon, ClockIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

defineProps({
    pageant: Object,
    hasPendingRequest: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['request-edit-access']);
</script>
