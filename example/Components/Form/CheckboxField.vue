<template>
  <div v-show="isVisible" :class="columnClass">
    <div class="flex items-start">
      <div class="flex items-center h-5">
        <input
          :id="fieldId"
          type="checkbox"
          :checked="modelValue"
          @change="$emit('update:modelValue', $event.target.checked)"
          :required="required"
          :disabled="disabled"
          class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-800"
          :class="{'border-red-300': hasError}"
        />
      </div>
      <div class="ml-3">
        <label :for="fieldId" class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ label }}
          <span v-if="required" class="text-red-500">*</span>
        </label>
        <p v-if="hint && !hasError" class="text-sm text-gray-500 dark:text-gray-400">
          {{ hint }}
        </p>
        <p v-if="hasError" class="text-sm text-red-600 dark:text-red-400">
          {{ errorMessage }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useFieldVisibility } from '@/Composables/useFieldVisibility'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
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

