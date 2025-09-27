<template>
  <div class="min-h-screen bg-gray-50">
    <Head title="Tabulator Dashboard" />
    <SideNav>
      <div class="p-6">
        <!-- Main content with transition -->
        <TransitionGroup
          tag="div"
          mode="out-in"
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0 -translate-y-4"
        >
          <slot />
        </TransitionGroup>
      </div>
    </SideNav>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import SideNav from '@/Components/SideNav.vue'
import { TransitionGroup } from 'vue'
import { useNotification } from '@/Composables/useNotification'

// Global real-time notifications for Tabulator pages
const activeChannels = []
const page = usePage()
const notify = useNotification()

function handleScoreUpdated(e) {
    try {
        const numeric = Number(e?.score)
        const scoreText = isNaN(numeric) ? (e?.score ?? '') : numeric.toFixed(2)
        const contestantName = e?.contestant_name ?? 'Contestant'
        const judgeName = e?.judge_name ?? 'A Judge'
        const criteriaName = e?.criteria_name ?? 'Criteria'

        notify.info(`${judgeName} scored ${contestantName} (${criteriaName}): ${scoreText}`, {
            title: 'New Score Submitted',
            timeout: 4000,
        })
    } catch (err) {
        // Fallback notification on unexpected payloads
        notify.info('A new score was submitted.', {
            title: 'New Score Submitted',
            timeout: 3500,
        })
    }
}

function unsubscribeFromChannels() {
    activeChannels.forEach(({ name, channel }) => {
        try {
            channel.stopListening('ScoreUpdated')
            window.Echo.leave(name)
        } catch (e) {
            // noop
        }
    })
    activeChannels.length = 0
}

function subscribeToChannels() {
    if (typeof window === 'undefined' || typeof window.Echo === 'undefined') {
        console.error('Laravel Echo is not available. Real-time notifications are disabled.')
        return
    }

    // Ensure a clean slate before subscribing
    unsubscribeFromChannels()

    // Subscribe to the currently selected pageant, if present
    const pageantId = page.props.pageant?.id
    if (pageantId) {
        const name = `pageant.${pageantId}`
        const channel = window.Echo.private(name)
            .stopListening('ScoreUpdated')
            .listen('ScoreUpdated', handleScoreUpdated)
        activeChannels.push({ name, channel })
    }

    // Also subscribe to all assigned pageants if provided (e.g., Dashboard without a selected pageant)
    const pageants = page.props.pageants
    if (Array.isArray(pageants)) {
        pageants.forEach((p) => {
            if (!p?.id) return
            const name = `pageant.${p.id}`
            if (activeChannels.some((c) => c.name === name)) return
            const channel = window.Echo.private(name)
                .stopListening('ScoreUpdated')
                .listen('ScoreUpdated', handleScoreUpdated)
            activeChannels.push({ name, channel })
        })
    }
}

onMounted(() => {
    subscribeToChannels()
})

onBeforeUnmount(() => {
    unsubscribeFromChannels()
})

// Re-subscribe when navigating between pageants or when the assigned pageant list changes
watch(
    () => [page.props.pageant?.id, Array.isArray(page.props.pageants) ? page.props.pageants.map(p => p.id).join(',') : ''],
    () => {
        subscribeToChannels()
    }
)

</script> 