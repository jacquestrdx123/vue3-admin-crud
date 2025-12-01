<template>
  <span :class="badgeClasses">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  color: {
    type: String,
    default: 'gray' // gray, red, yellow, green, blue, indigo, purple
  },
  size: {
    type: String,
    default: 'md' // sm, md, lg
  },
  rounded: {
    type: Boolean,
    default: true
  }
})

const badgeClasses = computed(() => {
  const classes = ['inline-flex items-center font-medium']

  // Size
  if (props.size === 'sm') {
    classes.push('px-2 py-0.5 text-xs')
  } else if (props.size === 'lg') {
    classes.push('px-3 py-1 text-base')
  } else {
    classes.push('px-2.5 py-0.5 text-sm')
  }

  // Shape
  if (props.rounded) {
    classes.push('rounded-full')
  } else {
    classes.push('rounded')
  }

  // Color
  const colorClasses = {
    gray: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    green: 'bg-ciba-green/20 text-gray-900 dark:bg-ciba-green/30 dark:text-gray-100',
    orange: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
  }

  classes.push(colorClasses[props.color] || colorClasses.gray)

  return classes.join(' ')
})
</script>

