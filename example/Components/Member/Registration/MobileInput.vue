<template>
  <div class="space-y-2">
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="flex gap-2">
      <div class="relative flex-1">
        <input
          :id="id"
          v-model="localValue"
          type="tel"
          placeholder="+27 12 345 6789"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          :class="{
            'border-gray-300': !error && !verified,
            'border-green-500': verified,
            'border-red-500': error
          }"
          @input="onInput"
          @blur="onBlur"
        />

        <div v-if="verified" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
      </div>

      <button
        v-if="showOtpButton && !verified"
        type="button"
        @click="sendOtp"
        :disabled="sending || !canSendOtp"
        class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
      >
        <span v-if="sending">Sending...</span>
        <span v-else>Send OTP</span>
      </button>
    </div>

    <p v-if="hint && !error" class="text-sm text-gray-500">{{ hint }}</p>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    <p v-if="verified" class="text-sm text-green-600">Mobile number verified ✓</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: 'Mobile Number'
  },
  modelValue: {
    type: String,
    default: ''
  },
  verified: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  showOtpButton: {
    type: Boolean,
    default: true
  },
  hint: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'update:verified', 'send-otp'])

const localValue = ref(props.modelValue)
const sending = ref(false)
const localError = ref('')

watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal
})

watch(() => props.error, (newVal) => {
  localError.value = newVal
})

const canSendOtp = computed(() => {
  const isSAOrNamibia = localValue.value.includes('+27') || localValue.value.includes('+264')
  return isSAOrNamibia && localValue.value.length >= 12
})

const onInput = (event) => {
  let value = event.target.value
  
  // Auto-format phone number
  if (!value.startsWith('+')) {
    if (value.startsWith('0')) {
      value = '+27' + value.substring(1)
    } else if (value.startsWith('27')) {
      value = '+' + value
    } else if (value.startsWith('264')) {
      value = '+' + value
    }
  }
  
  localValue.value = value
  emit('update:modelValue', value)
  emit('update:verified', false)
}

const onBlur = async () => {
  if (!localValue.value) {
    if (props.required) {
      localError.value = 'Mobile number is required'
    }
    return
  }

  try {
    const response = await fetch('/api/member/validate-mobile', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        mobile_number: localValue.value
      })
    })

    const data = await response.json()

    if (!data.valid) {
      localError.value = data.message
    } else {
      localError.value = ''
    }
  } catch (err) {
    console.error('Mobile validation error:', err)
  }
}

const sendOtp = async () => {
  if (!canSendOtp.value) return

  sending.value = true
  localError.value = ''

  try {
    const response = await fetch('/api/member/send-otp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        mobile_number: localValue.value
      })
    })

    const data = await response.json()

    if (data.success) {
      emit('send-otp', localValue.value)
    } else {
      localError.value = data.message
    }
  } catch (err) {
    localError.value = 'An error occurred while sending OTP'
    console.error('Send OTP error:', err)
  } finally {
    sending.value = false
  }
}
</script>

