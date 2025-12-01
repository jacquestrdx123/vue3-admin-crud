<template>
  <Teleport to="body">
    <div class="toast-container">
      <TransitionGroup name="toast">
        <Toast
          v-for="toast in toasts"
          :key="toast.id"
          :message="toast.message"
          :type="toast.type"
          :duration="toast.duration"
          @close="removeToast(toast.id)"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast.js'
import Toast from './Toast.vue'

const page = usePage()
const { toasts, removeToast, success, danger, warning, info } = useToast()

// Watch for flash messages from Inertia
watch(
  () => [
    page.props.flash?.success,
    page.props.flash?.error,
    page.props.flash?.warning,
    page.props.flash?.info
  ],
  ([successMsg, errorMsg, warningMsg, infoMsg]) => {
    if (successMsg) {
      success(successMsg)
    }
    if (errorMsg) {
      danger(errorMsg)
    }
    if (warningMsg) {
      warning(warningMsg)
    }
    if (infoMsg) {
      info(infoMsg)
    }
  },
  { immediate: true }
)
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  pointer-events: none;
}

.toast-container > * {
  pointer-events: auto;
}

/* Toast transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100px) scale(0.8);
}

.toast-move {
  transition: transform 0.3s ease;
}

/* Responsive */
@media (max-width: 640px) {
  .toast-container {
    top: 60px;
    right: 10px;
    left: 10px;
    align-items: stretch;
  }
}
</style>

