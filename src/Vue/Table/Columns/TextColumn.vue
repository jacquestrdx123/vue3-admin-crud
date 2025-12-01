<template>
  <span :class="textClass">{{ formattedValue }}</span>
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
  format: {
    type: Function,
    default: null
  },
  searchable: {
    type: Boolean,
    default: true
  },
  copyable: {
    type: Boolean,
    default: false
  },
  limit: {
    type: Number,
    default: null
  },
  color: {
    type: String,
    default: null
  },
  weight: {
    type: String,
    default: 'normal'
  }
})

const formattedValue = computed(() => {
  if (props.value === null || props.value === undefined) return '-'
  
  let val = props.format ? props.format(props.value, props.row) : String(props.value)
  
  if (props.limit && val.length > props.limit) {
    return val.substring(0, props.limit) + '...'
  }
  
  return val
})

const textClass = computed(() => {
  const classes = []
  
  if (props.color) {
    classes.push(`text-${props.color}-600 dark:text-${props.color}-400`)
  }
  
  if (props.weight === 'bold') {
    classes.push('font-bold')
  } else if (props.weight === 'semibold') {
    classes.push('font-semibold')
  } else if (props.weight === 'medium') {
    classes.push('font-medium')
  }
  
  return classes.join(' ')
})
</script>

