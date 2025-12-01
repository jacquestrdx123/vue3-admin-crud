<template>
  <span class="text-sm text-gray-900 dark:text-gray-100">
    {{ displayValue }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: [String, Array],
    default: null
  },
  row: {
    type: Object,
    default: () => ({})
  },
  labelKey: {
    type: String,
    required: true
  }
})

const displayValue = computed(() => {
  if (!props.value) return '-'
  
  try {
    // Parse JSON if value is a string
    let arrayData = typeof props.value === 'string' 
      ? JSON.parse(props.value) 
      : props.value
    
    // Ensure it's an array
    if (!Array.isArray(arrayData)) {
      return '-'
    }
    
    // Handle empty array
    if (arrayData.length === 0) {
      return '-'
    }
    
    // Extract the specified property from each object
    const values = arrayData
      .map(item => {
        if (typeof item === 'object' && item !== null) {
          return item[props.labelKey] ?? null
        }
        return null
      })
      .filter(value => value !== null && value !== undefined)
    
    // Return comma-separated list
    if (values.length === 0) {
      return '-'
    }
    
    return values.join(', ')
  } catch (error) {
    // If parsing fails, return '-'
    return '-'
  }
})
</script>

