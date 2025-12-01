<template>
  <div :class="cardClasses">
    <!-- Header -->
    <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <slot name="header">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ title }}
          </h3>
          <slot name="headerActions" />
        </div>
      </slot>
    </div>

    <!-- Body -->
    <div :class="bodyClasses">
      <slot />
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
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
    type: Boolean,
    default: true
  },
  shadow: {
    type: Boolean,
    default: true
  }
})

const cardClasses = computed(() => {
  const classes = ['bg-white dark:bg-gray-800 rounded-lg overflow-hidden']
  
  if (props.shadow) {
    classes.push('shadow-sm')
  }
  
  return classes.join(' ')
})

const bodyClasses = computed(() => {
  return props.padding ? 'px-6 py-4' : ''
})
</script>

