<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-medium leading-6 text-gray-900 mb-4"
              >
                Verify Mobile Number
              </DialogTitle>
              
              <div class="mt-2">
                <p class="text-sm text-gray-500 mb-4">
                  Please enter the 4-digit OTP sent to {{ mobileNumber }}
                </p>

                <input
                  v-model="otpInput"
                  type="text"
                  maxlength="4"
                  placeholder="Enter 4-digit OTP"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-center text-2xl tracking-widest"
                  @keypress="onlyNumbers"
                  @paste="onPaste"
                />

                <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
                <p v-if="success" class="mt-2 text-sm text-green-600">{{ success }}</p>
              </div>

              <div class="mt-6 flex gap-3">
                <button
                  type="button"
                  class="flex-1 inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                  @click="closeModal"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  class="flex-1 inline-flex justify-center rounded-md border border-transparent bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="verifyOtp"
                  :disabled="otpInput.length !== 4 || loading"
                >
                  <span v-if="loading">Verifying...</span>
                  <span v-else>Verify OTP</span>
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  mobileNumber: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['close', 'verified'])

const otpInput = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    otpInput.value = ''
    error.value = ''
    success.value = ''
  }
})

const onlyNumbers = (event) => {
  const char = String.fromCharCode(event.keyCode)
  if (!/[0-9]/.test(char)) {
    event.preventDefault()
  }
}

const onPaste = (event) => {
  const paste = (event.clipboardData || window.clipboardData).getData('text')
  if (!/^\d{4}$/.test(paste)) {
    event.preventDefault()
  }
}

const verifyOtp = async () => {
  if (otpInput.value.length !== 4) {
    error.value = 'Please enter a 4-digit OTP'
    return
  }

  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await fetch('/api/member/verify-otp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        mobile_number: props.mobileNumber,
        otp: otpInput.value
      })
    })

    const data = await response.json()

    if (data.valid) {
      success.value = data.message
      setTimeout(() => {
        emit('verified')
        closeModal()
      }, 1000)
    } else {
      error.value = data.message
    }
  } catch (err) {
    error.value = 'An error occurred. Please try again.'
    console.error('OTP verification error:', err)
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  emit('close')
}
</script>

