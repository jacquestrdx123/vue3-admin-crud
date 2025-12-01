import { ref } from 'vue'

const isOpen = ref(false)

export function useGlobalSearch() {
  const openModal = () => {
    isOpen.value = true
  }

  const closeModal = () => {
    isOpen.value = false
  }

  const toggleModal = () => {
    isOpen.value = !isOpen.value
  }

  return {
    isOpen,
    openModal,
    closeModal,
    toggleModal
  }
}

