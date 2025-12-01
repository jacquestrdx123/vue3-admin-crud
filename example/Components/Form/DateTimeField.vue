<template>
  <div v-show="isVisible" :class="columnClass">
    <label v-if="label" :for="fieldId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <input
      :id="fieldId"
      type="datetime-local"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :required="required"
      :disabled="disabled"
      :readonly="readonly"
      :min="min"
      :max="max"
      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white"
      :class="{'border-red-300 focus:border-red-500 focus:ring-red-500': hasError}"
    />
    
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
import { computed } from 'vue'
import { useFieldVisibility } from '@/Composables/useFieldVisibility'

const props = defineProps({
  modelValue: {
    type: String,
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
  min: {
    type: String,
    default: null
  },
  max: {
    type: String,
    default: null
  },
  errorMessages: {
    type: [String, Array],
    default: () => []
  },
  hint: {
    type: String,
    default: ''
  },
  columnSpan: {
    type: Number,
    default: 12
  },
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

const fieldConfig = computed(() => ({
  show_when: props.showWhen,
  hide_when: props.hideWhen
}))

const { isVisible } = useFieldVisibility(props.formData, fieldConfig.value)
</script>

