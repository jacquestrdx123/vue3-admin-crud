<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        @blur="emit('blur', $event)"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="inputClasses"
      />
      
      <div v-if="$slots.suffix" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <slot name="suffix" />
      </div>
    </div>
    
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
    
    <p v-else-if="hint" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: null
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: null
  },
  error: {
    type: String,
    default: null
  },
  hint: {
    type: String,
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'blur'])

const inputClasses = computed(() => {
  const baseClasses = 'block w-full rounded-md shadow-sm focus:shadow-md sm:text-sm transition-all duration-200'
  const stateClasses = props.error
    ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500'
    : 'border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-member-primary focus:border-member-primary'
  const disabledClasses = props.disabled
    ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed'
    : ''
  
  return [baseClasses, stateClasses, disabledClasses].join(' ')
})
</script>

<style scoped>
:root {
  --member-primary: #BAF504;
}

.focus\:ring-member-primary:focus {
  --tw-ring-color: var(--member-primary);
}

.focus\:border-member-primary:focus {
  border-color: var(--member-primary);
}
</style>

