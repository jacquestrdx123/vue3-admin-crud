import { ref } from 'vue'

const toasts = ref([])
let toastId = 0

export function useToast() {
  const addToast = (message, type = 'success', duration = 5000) => {
    const id = toastId++
    const toast = {
      id,
      message,
      type, // 'success', 'danger', 'warning', 'info'
      duration,
      visible: true,
    }

    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }

    return id
  }

  const removeToast = (id) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }

  const success = (message, duration = 5000) => {
    return addToast(message, 'success', duration)
  }

  const danger = (message, duration = 5000) => {
    return addToast(message, 'danger', duration)
  }

  const warning = (message, duration = 5000) => {
    return addToast(message, 'warning', duration)
  }

  const info = (message, duration = 5000) => {
    return addToast(message, 'info', duration)
  }

  const clear = () => {
    toasts.value = []
  }

  return {
    toasts,
    addToast,
    removeToast,
    success,
    danger,
    warning,
    info,
    clear,
  }
}

