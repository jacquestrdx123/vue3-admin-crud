<template>
  <div class="mb-4">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="min-h-[42px] w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500 dark:bg-gray-800 p-2"
         :class="{'border-red-300 dark:border-red-600': error}">
      <div class="flex flex-wrap gap-2 items-center">
        <span
          v-for="(tag, index) in tags"
          :key="index"
          class="inline-flex items-center gap-1 px-2 py-1 text-sm bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded"
        >
          {{ tag }}
          <button
            type="button"
            @click="removeTag(index)"
            class="hover:text-indigo-600 dark:hover:text-indigo-400"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </span>
        <input
          ref="inputRef"
          type="text"
          v-model="inputValue"
          @keydown.enter.prevent="addTag"
          @keydown.backspace="handleBackspace"
          :placeholder="tags.length === 0 ? placeholder : 'Add another tag...'"
          class="flex-1 min-w-[120px] outline-none bg-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
        />
      </div>
    </div>
    
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    <p v-if="hint" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Array],
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Type and press Enter to add tags'
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  },
  hint: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const inputRef = ref(null)
const inputValue = ref('')

const tags = computed({
  get() {
    if (Array.isArray(props.modelValue)) {
      return props.modelValue
    }
    if (typeof props.modelValue === 'string' && props.modelValue.trim()) {
      // If it's a string, try to parse as JSON array, otherwise split by comma
      try {
        const parsed = JSON.parse(props.modelValue)
        return Array.isArray(parsed) ? parsed : []
      } catch {
        return props.modelValue.split(',').map(t => t.trim()).filter(t => t)
      }
    }
    return []
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

const addTag = () => {
  const trimmed = inputValue.value.trim()
  if (trimmed && !tags.value.includes(trimmed)) {
    tags.value = [...tags.value, trimmed]
    inputValue.value = ''
  }
}

const removeTag = (index) => {
  tags.value = tags.value.filter((_, i) => i !== index)
}

const handleBackspace = (event) => {
  if (inputValue.value === '' && tags.value.length > 0) {
    removeTag(tags.value.length - 1)
  }
}

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  // Sync tags if modelValue changes externally
  if (Array.isArray(newValue)) {
    tags.value = newValue
  }
}, { deep: true })
</script>

