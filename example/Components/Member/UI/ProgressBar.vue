<template>
  <div>
    <div v-if="showLabel" class="flex justify-between items-center mb-2">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ label }}</span>
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ percentage }}%</span>
    </div>
    <div :class="['w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden', heightClass]">
      <div
        :class="['h-full transition-all duration-500 ease-out rounded-full', colorClass]"
        :style="{ width: `${percentage}%` }"
      >
        <div v-if="animated" class="h-full w-full animate-pulse"></div>
      </div>
    </div>
    <p v-if="description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
      {{ description }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  percentage: {
    type: Number,
    required: true,
    validator: (value) => value >= 0 && value <= 100
  },
  label: {
    type: String,
    default: null
  },
  description: {
    type: String,
    default: null
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger', 'info'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  showLabel: {
    type: Boolean,
    default: true
  },
  animated: {
    type: Boolean,
    default: false
  }
})

const heightClass = computed(() => {
  const sizes = {
    sm: 'h-2',
    md: 'h-4',
    lg: 'h-6'
  }
  return sizes[props.size]
})

const colorClass = computed(() => {
  const colors = {
    primary: 'bg-member-primary',
    success: 'bg-green-600',
    warning: 'bg-yellow-500',
    danger: 'bg-red-600',
    info: 'bg-blue-600'
  }
  return colors[props.color]
})
</script>

<style scoped>
:root {
  --member-primary: #BAF504;
}

.bg-member-primary {
  background-color: var(--member-primary);
}
</style>

