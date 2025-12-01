<template>
  <div class="toast" :class="typeClass" @click="$emit('close')">
    <div class="toast-content">
      <div class="toast-icon">
        <MaterialIcon :icon="iconName" />
      </div>
      <div class="toast-message">
        {{ message }}
      </div>
      <button @click.stop="$emit('close')" class="toast-close">
        <MaterialIcon icon="close" size="sm" />
      </button>
    </div>
    <div v-if="duration > 0" class="toast-progress" :style="{ animationDuration: duration + 'ms' }"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import MaterialIcon from '@/Components/Navigation/MaterialIcon.vue'

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'danger', 'warning', 'info'].includes(value)
  },
  duration: {
    type: Number,
    default: 5000
  }
})

const emit = defineEmits(['close'])

const typeClass = computed(() => {
  const classes = {
    success: 'toast-success',
    danger: 'toast-danger',
    warning: 'toast-warning',
    info: 'toast-info'
  }
  return classes[props.type] || classes.success
})

const iconName = computed(() => {
  const icons = {
    success: 'check_circle',
    danger: 'error',
    warning: 'warning',
    info: 'info'
  }
  return icons[props.type] || icons.success
})
</script>

<style scoped>
.toast {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 400px;
  margin-bottom: 12px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 4px solid;
}

.toast:hover {
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
  transform: translateY(-2px);
}

.toast-content {
  display: flex;
  align-items: center;
  padding: 16px;
  gap: 12px;
}

.toast-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
}

.toast-message {
  flex: 1;
  font-size: 14px;
  line-height: 1.5;
  color: #1f2937;
}

.toast-close {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: none;
  background: none;
  color: #6b7280;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.toast-close:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #1f2937;
}

.toast-progress {
  height: 3px;
  background: currentColor;
  opacity: 0.3;
  animation: progress linear forwards;
}

@keyframes progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Success */
.toast-success {
  border-left-color: #10b981;
}

.toast-success .toast-icon {
  color: #10b981;
}

.toast-success .toast-progress {
  color: #10b981;
}

/* Danger */
.toast-danger {
  border-left-color: #ef4444;
}

.toast-danger .toast-icon {
  color: #ef4444;
}

.toast-danger .toast-progress {
  color: #ef4444;
}

/* Warning */
.toast-warning {
  border-left-color: #f59e0b;
}

.toast-warning .toast-icon {
  color: #f59e0b;
}

.toast-warning .toast-progress {
  color: #f59e0b;
}

/* Info */
.toast-info {
  border-left-color: #3b82f6;
}

.toast-info .toast-icon {
  color: #3b82f6;
}

.toast-info .toast-progress {
  color: #3b82f6;
}

/* Dark mode */
.dark .toast {
  background: #1f2937;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.dark .toast-message {
  color: #f3f4f6;
}

.dark .toast-close {
  color: #9ca3af;
}

.dark .toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #f3f4f6;
}
</style>

