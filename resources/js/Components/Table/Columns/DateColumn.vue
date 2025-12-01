<template>
  <span :class="textClass" :title="value">{{ formattedDate }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: String,
    default: null
  },
  row: {
    type: Object,
    default: () => ({})
  },
  format: {
    type: String,
    default: 'date' // 'date', 'datetime', 'time', 'relative'
  },
  customFormat: {
    type: Function,
    default: null
  }
})

const formattedDate = computed(() => {
  if (!props.value) return '-'
  
  if (props.customFormat) {
    return props.customFormat(props.value, props.row)
  }
  
  const date = new Date(props.value)
  
  if (isNaN(date.getTime())) return props.value
  
  switch (props.format) {
    case 'date':
      return date.toLocaleDateString()
    case 'datetime':
      return date.toLocaleString()
    case 'time':
      return date.toLocaleTimeString()
    case 'relative':
      return getRelativeTime(date)
    default:
      return date.toLocaleDateString()
  }
})

const textClass = computed(() => {
  return 'text-gray-900 dark:text-gray-100'
})

const getRelativeTime = (date) => {
  const now = new Date()
  const diffMs = now - date
  const diffSec = Math.floor(diffMs / 1000)
  const diffMin = Math.floor(diffSec / 60)
  const diffHour = Math.floor(diffMin / 60)
  const diffDay = Math.floor(diffHour / 24)
  
  if (diffSec < 60) return 'just now'
  if (diffMin < 60) return `${diffMin} min ago`
  if (diffHour < 24) return `${diffHour} hour${diffHour > 1 ? 's' : ''} ago`
  if (diffDay < 7) return `${diffDay} day${diffDay > 1 ? 's' : ''} ago`
  
  return date.toLocaleDateString()
}
</script>

