<template>
  <teleport to="body">
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Panel -->
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:max-w-md sm:w-full"
            >
              <!-- Header -->
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                  Verify Your Mobile Number
                </h3>
              </div>

              <!-- Content -->
              <div class="px-6 py-6">
                <!-- Success Message -->
                <div class="mb-6">
                  <p class="text-sm text-gray-700">
                    We've sent you a code. Please enter it below.
                  </p>
                  <p v-if="timeRemaining" class="text-xs text-gray-500 mt-1">
                    Code expires in: <span class="font-semibold" :class="timeRemaining <= 60 ? 'text-red-600' : 'text-gray-700'">{{ formatTimeRemaining(timeRemaining) }}</span>
                  </p>
                </div>

                <!-- OTP Inputs -->
                <div class="flex justify-center gap-3 mb-4">
                  <input
                    v-for="(digit, index) in 4"
                    :key="index"
                    :ref="el => otpInputs[index] = el"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]"
                    maxlength="1"
                    :value="otpDigits[index] || ''"
                    @input="handleOtpInput($event, index)"
                    @keydown="handleOtpKeydown($event, index)"
                    @paste="handleOtpPaste"
                    class="otp-input"
                    autocomplete="off"
                  />
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                  {{ error }}
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="mt-4 text-center">
                  <p class="text-sm text-gray-600">Verifying...</p>
                </div>
              </div>

              <!-- Footer -->
              <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                <button
                  @click="handleCancel"
                  type="button"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ciba-green"
                >
                  Cancel
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  },
  otpDigits: {
    type: Array,
    default: () => ['', '', '', '']
  },
  expiryTime: {
    type: [Date, String, Number],
    default: null
  }
})

const emit = defineEmits(['update:otpDigits', 'verify', 'input', 'cancel', 'expired'])

const timeRemaining = ref(null)
let countdownInterval = null

const otpInputs = ref([])

function formatTimeRemaining(seconds) {
  if (seconds <= 0) return 'Expired'
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

function calculateTimeRemaining() {
  if (!props.expiryTime) {
    // Default to 10 minutes if no expiry time provided
    return 600
  }
  
  let expiryDate
  if (props.expiryTime instanceof Date) {
    expiryDate = props.expiryTime
  } else if (typeof props.expiryTime === 'string') {
    expiryDate = new Date(props.expiryTime)
  } else if (typeof props.expiryTime === 'number') {
    expiryDate = new Date(props.expiryTime * 1000) // Assume Unix timestamp
  } else {
    return 600
  }
  
  const now = new Date()
  const diff = Math.floor((expiryDate - now) / 1000)
  return Math.max(0, diff)
}

function startCountdown() {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
  
  timeRemaining.value = calculateTimeRemaining()
  
  countdownInterval = setInterval(() => {
    timeRemaining.value = calculateTimeRemaining()
    
    if (timeRemaining.value <= 0) {
      clearInterval(countdownInterval)
      emit('expired')
    }
  }, 1000)
}

function stopCountdown() {
  if (countdownInterval) {
    clearInterval(countdownInterval)
    countdownInterval = null
  }
  timeRemaining.value = null
}

function handleCancel() {
  stopCountdown()
  emit('cancel')
}

// Watch for modal show to focus first input and start countdown
watch(() => props.show, (isShown) => {
  if (isShown) {
    nextTick(() => {
      otpInputs.value[0]?.focus()
    })
    startCountdown()
  } else {
    stopCountdown()
  }
})

// Watch for expiry time changes
watch(() => props.expiryTime, () => {
  if (props.show) {
    startCountdown()
  }
})

onBeforeUnmount(() => {
  stopCountdown()
})

// Watch for error to clear on new input
watch(() => props.error, () => {
  // Error will be cleared by parent
})

// OTP input handlers
function handleOtpInput(event, index) {
  const value = event.target.value
  
  // Only allow digits
  if (!/^\d*$/.test(value)) {
    event.target.value = props.otpDigits[index] || ''
    return
  }
  
  // Update the digit
  const newDigits = [...props.otpDigits]
  newDigits[index] = value
  emit('update:otpDigits', newDigits)
  emit('input', { index, value })
  
  // Auto-advance to next input if value entered
  if (value && index < 3) {
    nextTick(() => {
      otpInputs.value[index + 1]?.focus()
    })
  }
  
  // Auto-verify when 4th digit is entered
  if (value && index === 3 && newDigits.filter(d => d).length === 4) {
    nextTick(() => {
      emit('verify', newDigits.join(''))
    })
  }
}

function handleOtpKeydown(event, index) {
  // Handle backspace
  if (event.key === 'Backspace') {
    if (!props.otpDigits[index] && index > 0) {
      // If current input is empty, move to previous and clear it
      const newDigits = [...props.otpDigits]
      newDigits[index - 1] = ''
      emit('update:otpDigits', newDigits)
      nextTick(() => {
        otpInputs.value[index - 1]?.focus()
      })
    } else {
      // Clear current input
      const newDigits = [...props.otpDigits]
      newDigits[index] = ''
      emit('update:otpDigits', newDigits)
    }
    event.preventDefault()
  }
  
  // Handle arrow keys
  if (event.key === 'ArrowLeft' && index > 0) {
    otpInputs.value[index - 1]?.focus()
  }
  if (event.key === 'ArrowRight' && index < 3) {
    otpInputs.value[index + 1]?.focus()
  }
}

function handleOtpPaste(event) {
  event.preventDefault()
  const pastedData = event.clipboardData.getData('text').trim()
  
  // Only process if it's exactly 4 digits
  if (/^\d{4}$/.test(pastedData)) {
    const digits = pastedData.split('')
    emit('update:otpDigits', digits)
    
    // Focus the last input
    nextTick(() => {
      otpInputs.value[3]?.focus()
      // Auto-verify after paste
      emit('verify', pastedData)
    })
  }
}
</script>

<style scoped>
.otp-input {
  width: 3.5rem;
  height: 3.5rem;
  text-align: center;
  font-size: 1.5rem;
  font-weight: 600;
  border: 2px solid rgb(209 213 219);
  border-radius: 0.5rem;
  background-color: white;
  color: rgb(17 24 39);
  transition: all 0.15s;
  font-family: inherit;
}

.otp-input:focus {
  outline: none;
  border-color: #BAF504;
  box-shadow: 0 0 0 3px rgba(186, 245, 4, 0.1);
  background-color: #F1FDCD;
}

.otp-input:not(:placeholder-shown) {
  border-color: #BAF504;
  background-color: #F1FDCD;
}
</style>

