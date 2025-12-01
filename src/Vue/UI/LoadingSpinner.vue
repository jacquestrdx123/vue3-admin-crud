<template>
  <div :class="containerClasses">
    <svg
      :class="spinnerClasses"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      ></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <p v-if="text" :class="textClasses">
      {{ text }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md' // sm, md, lg
  },
  color: {
    type: String,
    default: 'indigo'
  },
  text: {
    type: String,
    default: null
  },
  centered: {
    type: Boolean,
    default: true
  }
})

const containerClasses = computed(() => {
  const classes = []
  
  if (props.centered) {
    classes.push('flex flex-col items-center justify-center')
  }
  
  return classes.join(' ')
})

const spinnerClasses = computed(() => {
  const classes = ['animate-spin']
  
  // Size
  if (props.size === 'sm') {
    classes.push('h-4 w-4')
  } else if (props.size === 'lg') {
    classes.push('h-12 w-12')
  } else {
    classes.push('h-8 w-8')
  }
  
  // Color
  classes.push(`text-${props.color}-600`)
  
  return classes.join(' ')
})

const textClasses = computed(() => {
  const classes = ['mt-2 text-sm text-gray-600 dark:text-gray-400']
  return classes.join(' ')
})
</script>

