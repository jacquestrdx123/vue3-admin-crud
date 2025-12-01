<template>
  <div v-show="isVisible" :class="columnClass">
    <label v-if="label" :for="fieldId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <input
        :id="fieldId"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :required="required && isVisible"
        :disabled="disabled"
        :readonly="readonly"
        :min="min"
        :max="max"
        :step="step"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white"
        :class="{
          'border-red-300 focus:border-red-500 focus:ring-red-500': hasError,
          'pl-10': prependIcon,
          'pr-10': appendIcon || clearable
        }"
      />
      
      <!-- Prepend Icon -->
      <div v-if="prependIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <span class="text-gray-400" v-html="prependIcon"></span>
      </div>
      
      <!-- Append Icon or Clear Button -->
      <div v-if="appendIcon || (clearable && modelValue)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <button
          v-if="clearable && modelValue"
          @click="$emit('update:modelValue', null)"
          type="button"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <span v-else-if="appendIcon" class="text-gray-400" v-html="appendIcon"></span>
      </div>
    </div>
    
    <!-- Hint -->
    <p v-if="hint && !hasError" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ hint }}
    </p>
    
    <!-- Error Messages -->
    <p v-if="hasError" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useFieldVisibility } from '@/Composables/useFieldVisibility'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null
  },
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  errorMessages: {
    type: [String, Array],
    default: () => []
  },
  hint: {
    type: String,
    default: ''
  },
  prependIcon: {
    type: String,
    default: null
  },
  appendIcon: {
    type: String,
    default: null
  },
  clearable: {
    type: Boolean,
    default: false
  },
  columnSpan: {
    type: Number,
    default: 12
  },
  min: {
    type: [String, Number],
    default: null
  },
  max: {
    type: [String, Number],
    default: null
  },
  step: {
    type: [String, Number],
    default: null
  },
  // Visibility conditions
  showWhen: {
    type: Object,
    default: null
  },
  hideWhen: {
    type: Object,
    default: null
  },
  formData: {
    type: Object,
    default: () => ({})
  }
})

defineEmits(['update:modelValue'])

const fieldId = computed(() => `field-${props.name}`)

const columnClass = computed(() => {
  const span = props.columnSpan
  if (span === 12) return 'col-span-12'
  if (span === 6) return 'col-span-12 md:col-span-6'
  if (span === 4) return 'col-span-12 md:col-span-4'
  if (span === 3) return 'col-span-12 md:col-span-3'
  return `col-span-${span}`
})

const hasError = computed(() => {
  if (Array.isArray(props.errorMessages)) {
    return props.errorMessages.length > 0
  }
  return !!props.errorMessages
})

const errorMessage = computed(() => {
  if (Array.isArray(props.errorMessages)) {
    return props.errorMessages[0]
  }
  return props.errorMessages
})

// Handle visibility conditions
const fieldConfig = computed(() => ({
  show_when: props.showWhen,
  hide_when: props.hideWhen
}))

const { isVisible } = useFieldVisibility(props.formData, fieldConfig.value)
</script>

