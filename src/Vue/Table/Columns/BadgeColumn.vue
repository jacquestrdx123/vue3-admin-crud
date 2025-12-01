<template>
  <span :class="badgeClass">
    {{ displayValue }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: [String, Number],
    default: null
  },
  row: {
    type: Object,
    default: () => ({})
  },
  colors: {
    type: Object,
    default: () => ({})
  },
  defaultColor: {
    type: String,
    default: 'gray'
  },
  format: {
    type: Function,
    default: null
  }
})

const displayValue = computed(() => {
  return props.format ? props.format(props.value, props.row) : props.value
})

const color = computed(() => {
  return props.colors[props.value] || props.defaultColor
})

const badgeClass = computed(() => {
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${color.value}-100 text-${color.value}-800 dark:bg-${color.value}-900 dark:text-${color.value}-200`
})
</script>

