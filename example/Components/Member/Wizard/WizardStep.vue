<template>
  <div v-if="isActive" class="wizard-step" :class="stepClass">
    <div v-if="title || description" class="step-header mb-6">
      <h2 v-if="title" class="text-xl font-semibold text-gray-900">{{ title }}</h2>
      <p v-if="description" class="mt-2 text-sm text-gray-600">{{ description }}</p>
    </div>

    <div class="step-content">
      <slot
        :step-data="stepData"
        :set-step-data="setStepData"
        :errors="errors"
        :set-errors="setErrors"
        :is-valid="isValid"
      />
    </div>

    <div v-if="showValidationSummary && hasErrors" class="step-validation-summary mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
        </svg>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
          <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
            <li v-for="(error, field) in errors" :key="field">
              {{ Array.isArray(error) ? error[0] : error }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  stepKey: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: null
  },
  description: {
    type: String,
    default: null
  },
  currentStep: {
    type: Object,
    default: null
  },
  stepData: {
    type: Object,
    default: () => ({})
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  showValidationSummary: {
    type: Boolean,
    default: true
  },
  validateOnChange: {
    type: Boolean,
    default: false
  },
  stepClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:step-data', 'update:errors', 'validate'])

const isActive = computed(() => {
  return props.currentStep?.key === props.stepKey
})

const hasErrors = computed(() => {
  return props.errors && Object.keys(props.errors).length > 0
})

const isValid = computed(() => {
  return !hasErrors.value
})

function setStepData(data) {
  emit('update:step-data', data)
  
  if (props.validateOnChange) {
    emit('validate', data)
  }
}

function setErrors(errors) {
  emit('update:errors', errors)
}

// Watch for step data changes and emit validation if enabled
watch(() => props.stepData, (newData) => {
  if (props.validateOnChange && isActive.value) {
    emit('validate', newData)
  }
}, { deep: true })

defineExpose({
  isActive,
  isValid,
  hasErrors,
  validate: () => emit('validate', props.stepData)
})
</script>

<style scoped>
.wizard-step {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

