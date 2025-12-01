<template>
  <span :class="textClass">{{ formattedMoney }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: [Number, String],
    default: 0
  },
  row: {
    type: Object,
    default: () => ({})
  },
  currency: {
    type: String,
    default: 'R'
  },
  decimals: {
    type: Number,
    default: 2
  },
  colorize: {
    type: Boolean,
    default: false
  }
})

const numericValue = computed(() => {
  return typeof props.value === 'string' ? parseFloat(props.value) : props.value
})

const formattedMoney = computed(() => {
  if (props.value === null || props.value === undefined) return '-'
  
  const formatted = numericValue.value.toFixed(props.decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
  return `${props.currency} ${formatted}`
})

const textClass = computed(() => {
  if (!props.colorize) return 'text-gray-900 dark:text-gray-100'
  
  if (numericValue.value > 0) return 'text-green-600 dark:text-green-400'
  if (numericValue.value < 0) return 'text-red-600 dark:text-red-400'
  return 'text-gray-600 dark:text-gray-400'
})
</script>

