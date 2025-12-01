import { ref } from 'vue'

/**
 * Composable for confirmation dialogs
 * @returns {Object} - Confirmation methods
 */
export function useConfirm() {
    const isOpen = ref(false)
    const config = ref({
        title: 'Confirm Action',
        message: 'Are you sure?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        dangerMode: false
    })
    const resolvePromise = ref(null)

    const confirm = (options = {}) => {
        config.value = {
            title: options.title || 'Confirm Action',
            message: options.message || 'Are you sure?',
            confirmText: options.confirmText || 'Confirm',
            cancelText: options.cancelText || 'Cancel',
            dangerMode: options.dangerMode || false
        }

        isOpen.value = true

        return new Promise((resolve) => {
            resolvePromise.value = resolve
        })
    }

    const handleConfirm = () => {
        isOpen.value = false
        if (resolvePromise.value) {
            resolvePromise.value(true)
        }
    }

    const handleCancel = () => {
        isOpen.value = false
        if (resolvePromise.value) {
            resolvePromise.value(false)
        }
    }

    return {
        isOpen,
        config,
        confirm,
        handleConfirm,
        handleCancel
    }
}

