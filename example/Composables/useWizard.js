import { ref, computed, watch } from 'vue'

export function useWizard(initialSteps = [], options = {}) {
  const {
    onStepChange = null,
    onComplete = null,
    validateBeforeNext = true,
    persistState = false,
    storageKey = 'wizard-state',
    initialData = {},
    initialStep = 0
  } = options

  const currentStepIndex = ref(initialStep)
  const steps = ref(initialSteps)
  const stepData = ref(initialData)
  const stepErrors = ref({})
  const isSubmitting = ref(false)

  // Load persisted state if enabled
  if (persistState && typeof window !== 'undefined') {
    const saved = sessionStorage.getItem(storageKey)
    if (saved) {
      try {
        const parsed = JSON.parse(saved)
        currentStepIndex.value = parsed.currentStepIndex || initialStep
        stepData.value = parsed.stepData || {}
      } catch (e) {
        console.warn('Failed to parse wizard state:', e)
      }
    }
  }

  // Persist state on changes
  if (persistState && typeof window !== 'undefined') {
    watch([currentStepIndex, stepData], () => {
      sessionStorage.setItem(storageKey, JSON.stringify({
        currentStepIndex: currentStepIndex.value,
        stepData: stepData.value
      }))
    }, { deep: true })
  }

  const currentStep = computed(() => steps.value[currentStepIndex.value])
  const isFirstStep = computed(() => currentStepIndex.value === 0)
  const isLastStep = computed(() => currentStepIndex.value === steps.value.length - 1)
  const progress = computed(() => ((currentStepIndex.value + 1) / steps.value.length) * 100)
  const completedSteps = computed(() => currentStepIndex.value)

  const canGoNext = computed(() => {
    if (isLastStep.value) return false
    if (!validateBeforeNext) return true
    
    const currentStepKey = currentStep.value?.key
    if (!currentStepKey) return true
    
    // Check if current step has errors
    return !stepErrors.value[currentStepKey] || Object.keys(stepErrors.value[currentStepKey]).length === 0
  })

  const canGoPrevious = computed(() => !isFirstStep.value)

  function setStepData(stepKey, data) {
    stepData.value[stepKey] = {
      ...stepData.value[stepKey],
      ...data
    }
  }

  function getStepData(stepKey) {
    return stepData.value[stepKey] || {}
  }

  function setStepErrors(stepKey, errors) {
    if (!errors || Object.keys(errors).length === 0) {
      delete stepErrors.value[stepKey]
    } else {
      stepErrors.value[stepKey] = errors
    }
  }

  function getStepErrors(stepKey) {
    return stepErrors.value[stepKey] || {}
  }

  function clearStepErrors(stepKey) {
    delete stepErrors.value[stepKey]
  }

  async function goToStep(index) {
    if (index < 0 || index >= steps.value.length) return false
    
    const previousIndex = currentStepIndex.value
    currentStepIndex.value = index
    
    if (onStepChange) {
      await onStepChange({
        from: previousIndex,
        to: index,
        step: currentStep.value,
        data: stepData.value
      })
    }
    
    return true
  }

  async function next() {
    if (!canGoNext.value) return false
    return await goToStep(currentStepIndex.value + 1)
  }

  async function previous() {
    if (!canGoPrevious.value) return false
    return await goToStep(currentStepIndex.value - 1)
  }

  async function goToStepByKey(stepKey) {
    const index = steps.value.findIndex(s => s.key === stepKey)
    if (index === -1) return false
    return await goToStep(index)
  }

  async function complete() {
    if (!isLastStep.value) return false
    
    isSubmitting.value = true
    
    try {
      if (onComplete) {
        await onComplete({
          data: stepData.value,
          steps: steps.value
        })
      }
      
      // Clear persisted state on successful completion
      if (persistState && typeof window !== 'undefined') {
        sessionStorage.removeItem(storageKey)
      }
      
      return true
    } catch (error) {
      console.error('Wizard completion error:', error)
      throw error
    } finally {
      isSubmitting.value = false
    }
  }

  function reset() {
    currentStepIndex.value = 0
    stepData.value = {}
    stepErrors.value = {}
    isSubmitting.value = false
    
    if (persistState && typeof window !== 'undefined') {
      sessionStorage.removeItem(storageKey)
    }
  }

  function updateSteps(newSteps) {
    steps.value = newSteps
  }

  function getAllData() {
    return { ...stepData.value }
  }

  function isStepComplete(stepKey) {
    const data = getStepData(stepKey)
    const errors = getStepErrors(stepKey)
    return data && Object.keys(data).length > 0 && Object.keys(errors).length === 0
  }

  return {
    // State
    currentStepIndex,
    currentStep,
    steps,
    stepData,
    stepErrors,
    isSubmitting,
    
    // Computed
    isFirstStep,
    isLastStep,
    canGoNext,
    canGoPrevious,
    progress,
    completedSteps,
    
    // Methods
    next,
    previous,
    goToStep,
    goToStepByKey,
    complete,
    reset,
    setStepData,
    getStepData,
    setStepErrors,
    getStepErrors,
    clearStepErrors,
    updateSteps,
    getAllData,
    isStepComplete
  }
}
