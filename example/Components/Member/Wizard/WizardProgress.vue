<template>
  <div class="wizard-progress">
    <!-- Progress Bar -->
    <div class="relative">
      <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-gray-200">
        <div
          class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center transition-all duration-500"
          :style="{ width: progressPercentage + '%', backgroundColor: '#BAF504' }"
        />
      </div>
    </div>

    <!-- Steps -->
    <div class="flex justify-between items-start">
      <div
        v-for="(step, index) in steps"
        :key="step.key"
        class="flex flex-col items-center flex-1"
        :class="{ 'cursor-pointer': isStepClickable(index) }"
        @click="handleStepClick(index)"
      >
        <!-- Step Circle -->
        <div class="relative flex items-center justify-center">
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-all duration-300"
            :class="getStepCircleClasses(index)"
          >
            <span v-if="isStepCompleted(index)" class="flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </span>
            <span v-else>{{ index + 1 }}</span>
          </div>
        </div>

        <!-- Step Label -->
        <div v-if="showLabels" class="mt-3 text-center max-w-[120px]">
          <div
            class="text-xs font-medium transition-colors duration-300"
            :class="getStepLabelClasses(index)"
          >
            {{ step.title }}
          </div>
          <div v-if="step.subtitle" class="text-xs text-gray-500 mt-1">
            {{ step.subtitle }}
          </div>
        </div>
      </div>
    </div>

    <!-- Step Counter (Mobile) -->
    <div v-if="showMobileCounter" class="md:hidden mt-4 text-center text-sm text-gray-600">
      Step {{ currentStepIndex + 1 }} of {{ steps.length }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  steps: {
    type: Array,
    required: true
  },
  currentStepIndex: {
    type: Number,
    required: true
  },
  completedSteps: {
    type: Number,
    default: 0
  },
  showLabels: {
    type: Boolean,
    default: true
  },
  showMobileCounter: {
    type: Boolean,
    default: true
  },
  allowStepClick: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['step-click'])

const progressPercentage = computed(() => {
  return ((props.currentStepIndex + 1) / props.steps.length) * 100
})

function isStepCompleted(index) {
  return index < props.currentStepIndex
}

function isStepCurrent(index) {
  return index === props.currentStepIndex
}

function isStepClickable(index) {
  if (!props.allowStepClick) return false
  // Only allow clicking on completed steps or current step
  return index <= props.currentStepIndex
}

function getStepCircleClasses(index) {
  if (isStepCompleted(index)) {
    return 'bg-ciba-green text-black border-2 border-ciba-green'
  } else if (isStepCurrent(index)) {
    return 'bg-white text-ciba-green border-2 border-ciba-green ring-4 ring-ciba-green ring-opacity-20'
  } else {
    return 'bg-gray-200 text-gray-600 border-2 border-gray-200'
  }
}

function getStepLabelClasses(index) {
  if (isStepCurrent(index)) {
    return 'text-gray-900 font-semibold'
  } else if (isStepCompleted(index)) {
    return 'text-gray-700'
  } else {
    return 'text-gray-500'
  }
}

function handleStepClick(index) {
  if (isStepClickable(index)) {
    emit('step-click', index)
  }
}
</script>

<style scoped>
.bg-ciba-green {
  background-color: #BAF504;
}

.text-ciba-green {
  color: #BAF504;
}

.border-ciba-green {
  border-color: #BAF504;
}

.ring-ciba-green {
  --tw-ring-color: #BAF504;
}
</style>

