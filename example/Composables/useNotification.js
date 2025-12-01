import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'

/**
 * Composable for handling flash notifications
 * Integrates with Tailwind toast notifications
 */
export function useNotification() {
    const page = usePage()

    const showNotification = (message, type = 'success') => {
        // This can be integrated with a toast library like vue-toastification
        // For now, using native browser notifications
        console.log(`[${type.toUpperCase()}]`, message)
    }

    // Watch for flash messages
    watch(() => page.props.flash, (flash) => {
        if (flash.success) {
            showNotification(flash.success, 'success')
        }
        if (flash.error) {
            showNotification(flash.error, 'error')
        }
        if (flash.warning) {
            showNotification(flash.warning, 'warning')
        }
        if (flash.info) {
            showNotification(flash.info, 'info')
        }
    }, { deep: true })

    return {
        showNotification
    }
}

