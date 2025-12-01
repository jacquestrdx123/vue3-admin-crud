<template>
  <div class="space-y-4 bg-gray-50 p-6 rounded-lg">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Member Declarations</h3>

    <div v-for="(declaration, key) in declarations" :key="key" class="flex items-start">
      <div class="flex items-center h-5">
        <input
          :id="`declaration-${key}`"
          v-model="localValues[key]"
          type="checkbox"
          class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500"
          @change="updateValue(key)"
        />
      </div>
      <div class="ml-3">
        <label :for="`declaration-${key}`" class="text-sm text-gray-700">
          {{ declaration.label }}
          <span class="text-red-500">*</span>
        </label>
        <p v-if="declaration.description" class="text-xs text-gray-500 mt-1">
          {{ declaration.description }}
        </p>
        <p v-if="errors[key]" class="text-xs text-red-600 mt-1">{{ errors[key] }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, reactive } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  declarations: {
    type: Object,
    default: () => ({
      agree_professional_standards: {
        label: 'I agree to uphold CIBA\'s professional standards',
        description: 'Maintain high ethical and professional standards in all professional activities'
      },
      stay_informed: {
        label: 'I agree to stay informed through CIBA\'s communication channels',
        description: 'Receive important updates, news, and communications from CIBA'
      },
      accept_terms_privacy: {
        label: 'I accept CIBA\'s Terms & Conditions and Privacy Policy',
        description: 'Have read and agree to the terms of membership and privacy policy'
      },
      commit_ethics: {
        label: 'I commit to professional ethics and CIBA\'s Code of Conduct',
        description: 'Adhere to the highest standards of professional ethics and conduct'
      },
      confirm_accuracy: {
        label: 'I confirm that all information provided is true and correct',
        description: 'Declare that all information submitted is accurate and complete'
      },
      consent_processing: {
        label: 'I consent to CIBA processing my personal information',
        description: 'Allow CIBA to process personal information in accordance with POPIA'
      }
    })
  }
})

const emit = defineEmits(['update:modelValue'])

const localValues = reactive({
  agree_professional_standards: props.modelValue.agree_professional_standards || false,
  stay_informed: props.modelValue.stay_informed || false,
  accept_terms_privacy: props.modelValue.accept_terms_privacy || false,
  commit_ethics: props.modelValue.commit_ethics || false,
  confirm_accuracy: props.modelValue.confirm_accuracy || false,
  consent_processing: props.modelValue.consent_processing || false
})

watch(() => props.modelValue, (newVal) => {
  Object.keys(localValues).forEach(key => {
    if (newVal[key] !== undefined) {
      localValues[key] = newVal[key]
    }
  })
}, { deep: true })

const updateValue = (key) => {
  emit('update:modelValue', { ...localValues })
}
</script>

