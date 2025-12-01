<template>
  <div class="mb-4">
    <div class="flex items-center justify-between">
      <div class="flex-grow">
        <label :for="fieldId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ label }}
          <span v-if="required" class="text-red-500">*</span>
        </label>
        <p v-if="hint && !error" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ hint }}
        </p>
        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </p>
      </div>
      
      <button
        :id="fieldId"
        type="button"
        @click="toggle"
        :disabled="disabled"
        :class="[
          modelValue ? 'bg-ciba-green' : 'bg-gray-200 dark:bg-gray-700',
          'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-ciba-green focus:ring-offset-2',
          disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
      >
        <span
          :class="[
            modelValue ? 'translate-x-5' : 'translate-x-0',
            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
          ]"
        ></span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
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

const fieldId = computed(() => `toggle-${Math.random().toString(36).substr(2, 9)}`)

const toggle = () => {
  if (!props.disabled) {
    emit('update:modelValue', !props.modelValue)
  }
}
</script>

