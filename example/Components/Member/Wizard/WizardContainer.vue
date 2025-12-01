<template>
  <div class="wizard-container">
    <!-- Wizard Header -->
    <div v-if="showHeader" class="wizard-header bg-white rounded-lg shadow-lg p-6 mb-6">
      <div v-if="title" class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
        <p v-if="description" class="mt-2 text-sm text-gray-600">{{ description }}</p>
      </div>
      
      <WizardProgress
        :steps="steps"
        :current-step-index="currentStepIndex"
        :completed-steps="completedSteps"
        :show-labels="showStepLabels"
        @step-click="handleStepClick"
      />
    </div>

    <!-- Wizard Content -->
    <div class="wizard-content bg-white rounded-lg shadow-xl p-6">
      <slot
        :current-step="currentStep"
        :current-step-index="currentStepIndex"
        :is-first-step="isFirstStep"
        :is-last-step="isLastStep"
        :step-data="stepData"
        :set-step-data="setStepData"
        :get-step-data="getStepData"
        :errors="currentStepErrors"
        :set-errors="setCurrentStepErrors"
      />
    </div>

    <!-- Wizard Footer / Navigation -->
    <div v-if="showNavigation" class="wizard-footer mt-6 flex justify-between items-center">
      <div>
        <button
          v-if="!isFirstStep && showBackButton"
          type="button"
          @click="handlePrevious"
          :disabled="isSubmitting"
          class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-full hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            {{ backButtonText }}
          </span>
        </button>
      </div>

      <div class="flex gap-3">
        <slot name="footer-actions" />
        
        <button
          v-if="!isLastStep"
          type="button"
          @click="handleNext"
          :disabled="!canGoNext || isSubmitting"
          class="px-6 py-2 text-black bg-ciba-green rounded-full hover:bg-ciba-green/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
        >
          <span class="flex items-center gap-2">
            {{ nextButtonText }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
          </span>
        </button>

        <button
          v-else
          type="button"
          @click="handleComplete"
          :disabled="isSubmitting"
          class="px-6 py-2 text-black bg-ciba-green rounded-full hover:bg-ciba-green/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
        >
          <span v-if="!isSubmitting" class="flex items-center gap-2">
            {{ completeButtonText }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </span>
          <span v-else class="flex items-center gap-2">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useWizard } from '@/Composables/useWizard'
import WizardProgress from './WizardProgress.vue'

const props = defineProps({
  steps: {
    type: Array,
    required: true,
    validator: (steps) => {
      return steps.every(step => step.key && step.title)
    }
  },
  beforeNext: {
    type: Function,
    default: null
  },
  title: {
    type: String,
    default: null
  },
  description: {
    type: String,
    default: null
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showNavigation: {
    type: Boolean,
    default: true
  },
  showBackButton: {
    type: Boolean,
    default: true
  },
  showStepLabels: {
    type: Boolean,
    default: true
  },
  allowStepClick: {
    type: Boolean,
    default: false
  },
  validateBeforeNext: {
    type: Boolean,
    default: true
  },
  persistState: {
    type: Boolean,
    default: false
  },
  storageKey: {
    type: String,
    default: 'wizard-state'
  },
  nextButtonText: {
    type: String,
    default: 'Next'
  },
  backButtonText: {
    type: String,
    default: 'Back'
  },
  completeButtonText: {
    type: String,
    default: 'Submit'
  },
  initialData: {
    type: Object,
    default: () => ({})
  },
  initialStep: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['step-change', 'complete', 'next', 'previous'])

const {
  currentStepIndex,
  currentStep,
  stepData,
  stepErrors,
  isSubmitting,
  isFirstStep,
  isLastStep,
  canGoNext,
  completedSteps,
  next,
  previous,
  goToStep,
  complete,
  setStepData,
  getStepData,
  setStepErrors,
  getStepErrors
} = useWizard(props.steps, {
  validateBeforeNext: props.validateBeforeNext,
  persistState: props.persistState,
  storageKey: props.storageKey,
  initialData: props.initialData,
  initialStep: props.initialStep,
  onStepChange: (event) => emit('step-change', event),
  onComplete: async (event) => {
    emit('complete', event)
  }
})

const currentStepErrors = computed(() => {
  return getStepErrors(currentStep.value?.key) || {}
})

function setCurrentStepErrors(errors) {
  setStepErrors(currentStep.value?.key, errors)
}

function validateCurrentStep() {
  const step = currentStep.value
  if (!step || !step.validationRules) return true
  
  const errors = {}
  const data = stepData.value
  
  Object.entries(step.validationRules).forEach(([field, rules]) => {
    // Extract field value using dot notation
    const value = field.split('.').reduce((obj, key) => obj?.[key], data)
    
    // Check required
    if (rules.required && (!value || (typeof value === 'string' && value.trim() === ''))) {
      errors[field.split('.').pop()] = rules.message || `${field} is required`
    }
    
    // Check email
    if (rules.email && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(value)) {
        errors[field.split('.').pop()] = 'Please enter a valid email address'
      }
    }
  })
  
  if (Object.keys(errors).length > 0) {
    setCurrentStepErrors(errors)
    return false
  }
  
  setCurrentStepErrors({})
  return true
}

async function handleNext() {
  // Validate current step before proceeding
  if (!validateCurrentStep()) {
    return
  }

  if (props.beforeNext && currentStep.value) {
    console.log('Calling beforeNext', { step: currentStep.value, stepIndex: currentStepIndex.value, data: stepData.value })
    const shouldProceed = await props.beforeNext({
      step: currentStep.value,
      stepIndex: currentStepIndex.value,
      data: stepData.value
    })

    console.log('beforeNext returned', shouldProceed)

    if (shouldProceed === false) {
      return
    }
  }
  
  const success = await next()
  if (success) {
    emit('next', {
      stepIndex: currentStepIndex.value,
      step: currentStep.value
    })
  }
}

async function handlePrevious() {
  const success = await previous()
  if (success) {
    emit('previous', {
      stepIndex: currentStepIndex.value,
      step: currentStep.value
    })
  }
}

async function handleComplete() {
  try {
    await complete()
  } catch (error) {
    console.error('Error completing wizard:', error)
  }
}

async function handleStepClick(stepIndex) {
  if (!props.allowStepClick) return
  
  // Only allow going to previous steps or current step
  if (stepIndex <= currentStepIndex.value) {
    await goToStep(stepIndex)
  }
}

onMounted(() => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
})

defineExpose({
  currentStepIndex,
  currentStep,
  stepData,
  next,
  previous,
  goToStep,
  complete,
  setStepData,
  getStepData,
  setStepErrors
})
</script>

<style scoped>
.bg-ciba-green {
  background-color: #BAF504;
}

.bg-ciba-green:hover {
  background-color: #a8dd04;
}
</style>

