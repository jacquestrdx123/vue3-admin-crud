<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <div v-if="title || $slots.header" class="px-4 py-5 border-b border-gray-200 dark:border-gray-700 sm:px-6">
      <div v-if="$slots.header">
        <slot name="header" />
      </div>
      <div v-else class="flex items-center justify-between">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
          {{ title }}
        </h3>
        <div v-if="$slots.actions" class="flex gap-2">
          <slot name="actions" />
        </div>
      </div>
    </div>
    <div :class="paddingClass">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 sm:px-6">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: null
  },
  padding: {
    type: String,
    default: 'normal', // none, sm, normal, lg
    validator: (value) => ['none', 'sm', 'normal', 'lg'].includes(value)
  }
})

const paddingClass = computed(() => {
  const paddingMap = {
    none: '',
    sm: 'px-4 py-3',
    normal: 'px-4 py-5 sm:p-6',
    lg: 'px-6 py-8 sm:p-8'
  }
  return paddingMap[props.padding]
})
</script>

