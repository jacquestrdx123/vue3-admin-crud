<template>
  <div class="space-y-2">
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <input
        :id="id"
        v-model="localValue"
        type="text"
        maxlength="13"
        placeholder="YYMMDD0000000"
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
        :class="{
          'border-gray-300': !error && !isValid,
          'border-green-500': isValid,
          'border-red-500': error
        }"
        @input="onInput"
        @blur="onBlur"
        @keypress="onlyNumbers"
      />

      <div v-if="validating" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else-if="isValid" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
    </div>

    <p v-if="hint && !error" class="text-sm text-gray-500">{{ hint }}</p>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    <p v-if="extractedDateOfBirth" class="text-sm text-green-600">
      Date of Birth: {{ formatDate(extractedDateOfBirth) }}
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: 'ID Number'
  },
  modelValue: {
    type: String,
    default: ''
  },
  dateOfBirth: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  hint: {
    type: String,
    default: 'CIBA is required by law to request ID numbers. All information is kept secure.'
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'update:dateOfBirth', 'update:valid'])

const localValue = ref(props.modelValue)
const validating = ref(false)
const isValid = ref(false)
const localError = ref('')
const extractedDateOfBirth = ref(props.dateOfBirth)

watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal
})

watch(() => props.error, (newVal) => {
  localError.value = newVal
})

const onlyNumbers = (event) => {
  const char = String.fromCharCode(event.keyCode)
  if (!/[0-9]/.test(char)) {
    event.preventDefault()
  }
}

const onInput = (event) => {
  const value = event.target.value
  emit('update:modelValue', value)
  
  if (value.length < 13) {
    isValid.value = false
    extractedDateOfBirth.value = ''
    emit('update:valid', false)
    emit('update:dateOfBirth', '')
  }
}

const onBlur = async () => {
  if (!localValue.value || localValue.value.length < 13) {
    if (props.required && !localValue.value) {
      localError.value = 'ID number is required'
      emit('update:valid', false)
    }
    return
  }

  validating.value = true
  localError.value = ''
  isValid.value = false

  try {
    const response = await fetch('/api/member/validate-id-number', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        identity_number: localValue.value
      })
    })

    const data = await response.json()

    if (data.valid) {
      isValid.value = true
      extractedDateOfBirth.value = data.date_of_birth
      emit('update:valid', true)
      emit('update:dateOfBirth', data.date_of_birth)
    } else {
      localError.value = data.message
      emit('update:valid', false)
    }
  } catch (err) {
    localError.value = 'An error occurred while validating the ID number'
    emit('update:valid', false)
    console.error('ID validation error:', err)
  } finally {
    validating.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

