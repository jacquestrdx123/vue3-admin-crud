<template>
  <div class="json-display">
    <div
      class="cursor-pointer"
      @click="toggleExpand"
    >
      <div v-if="!expanded" class="flex items-center gap-2 group">
        <span class="text-xs text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
          {{ displayText }}
        </span>
      </div>
      
      <div v-else class="json-content">
        <pre class="text-xs font-mono bg-gray-50 dark:bg-gray-900 p-3 rounded overflow-auto max-h-64 border border-gray-200 dark:border-gray-700">{{ formattedJson }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  value: {
    type: [String, Object, Array],
    default: null
  },
  row: {
    type: Object,
    default: () => ({})
  },
  maxDepth: {
    type: Number,
    default: 3
  },
  characterLimit: {
    type: Number,
    default: null
  }
})

const expanded = ref(false)

const toggleExpand = () => {
  expanded.value = !expanded.value
}

const formattedJson = computed(() => {
  if (!props.value) return '-'
  
  try {
    // If it's already a string, try to parse it
    const jsonData = typeof props.value === 'string' 
      ? JSON.parse(props.value) 
      : props.value
    
    return JSON.stringify(jsonData, null, 2)
  } catch (error) {
    return String(props.value)
  }
})

const displayText = computed(() => {
  if (!props.value) return '-'
  
  try {
    // Convert to string representation
    let textValue = typeof props.value === 'string' 
      ? props.value 
      : JSON.stringify(props.value)
    
    // Apply character limit if specified
    if (props.characterLimit && textValue.length > props.characterLimit) {
      return textValue.substring(0, props.characterLimit) + '...'
    }
    
    // Default to 100 characters if no limit specified
    if (textValue.length > 100) {
      return textValue.substring(0, 100) + '...'
    }
    
    return textValue
  } catch (error) {
    return String(props.value)
  }
})
</script>

<style scoped>
.json-display {
  font-size: 12px;
}
</style>

