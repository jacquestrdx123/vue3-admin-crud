<template>
  <nav aria-label="Progress">
    <ol class="flex items-center justify-between">
      <li
        v-for="(step, index) in steps"
        :key="step.id"
        class="relative flex-1"
        :class="index !== steps.length - 1 ? 'pr-8' : ''"
      >
        <div class="flex flex-col items-center">
          <!-- Step Circle -->
          <div
            :class="[
              'flex h-10 w-10 items-center justify-center rounded-full transition-colors z-10',
              index + 1 < currentStep
                ? 'bg-member-primary text-gray-900'
                : index + 1 === currentStep
                ? 'border-2 border-member-primary bg-white text-member-primary-dark'
                : 'border-2 border-gray-300 bg-white text-gray-500'
            ]"
          >
            <svg v-if="index + 1 < currentStep" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span v-else class="font-semibold">{{ index + 1 }}</span>
          </div>
          
          <!-- Step Label -->
          <span
            :class="[
              'mt-2 text-xs sm:text-sm font-medium text-center',
              index + 1 === currentStep
                ? 'text-member-primary-dark'
                : index + 1 < currentStep
                ? 'text-gray-900 dark:text-white'
                : 'text-gray-500'
            ]"
          >
            {{ step.name }}
          </span>
        </div>
        
        <!-- Connector line -->
        <div
          v-if="index !== steps.length - 1"
          :class="[
            'absolute top-5 left-1/2 w-full h-0.5 -z-10',
            index + 1 < currentStep ? 'bg-member-primary' : 'bg-gray-200'
          ]"
        ></div>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  steps: {
    type: Array,
    required: true
  },
  currentStep: {
    type: Number,
    required: true
  }
})
</script>

<style scoped>
:root {
  --member-primary: #BAF504;
  --member-primary-dark: #5A7302;
}

.bg-member-primary {
  background-color: var(--member-primary);
}

.border-member-primary {
  border-color: var(--member-primary);
}

.text-member-primary-dark {
  color: var(--member-primary-dark);
}
</style>

