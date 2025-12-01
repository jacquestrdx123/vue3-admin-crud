<template>
  <Modal :show="show" title="Reverse Payment" max-width="lg" @close="handleClose">
    <div class="space-y-4">
      <div v-if="payment" class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg mb-4">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Details</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <span class="text-gray-500 dark:text-gray-400">Reference:</span>
            <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ payment.reference }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Amount:</span>
            <span class="ml-2 font-medium text-gray-900 dark:text-white">R{{ formatAmount(payment.amount) }}</span>
          </div>
          <div v-if="payment.member">
            <span class="text-gray-500 dark:text-gray-400">Member:</span>
            <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ payment.member.m_code }}</span>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400">Transaction Date:</span>
            <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ payment.transaction_date }}</span>
          </div>
        </div>
      </div>

      <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Warning</h3>
            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
              <p>Reversing this payment will create a reversal transaction and update all associated invoices. This action cannot be undone.</p>
            </div>
          </div>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <DateField
          v-model="form.reversal_date"
          name="reversal_date"
          label="Reversal Date"
          :required="true"
          :error-messages="errors.reversal_date"
        />
      </form>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <Button variant="outline" @click="handleClose" :disabled="processing">
          Cancel
        </Button>
        <Button variant="danger" @click="handleSubmit" :disabled="processing || !form.reversal_date">
          <span v-if="processing">Reversing...</span>
          <span v-else>Reverse Payment</span>
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Modal from '@/Components/UI/Modal.vue'
import Button from '@/Components/UI/Button.vue'
import DateField from '@/Components/Form/DateField.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const form = ref({
  reversal_date: null
})

const errors = ref({})
const processing = ref(false)

const formatAmount = (amount) => {
  return Number(amount || 0).toFixed(2)
}

const getTodayDate = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const handleSubmit = () => {
  if (!form.value.reversal_date || !props.payment) return

  processing.value = true
  errors.value = {}

  router.post(route('vue.payments.store-reverse', props.payment.id), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      handleClose()
    },
    onError: (pageErrors) => {
      errors.value = pageErrors
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const handleClose = () => {
  if (!processing.value) {
    form.value = {
      reversal_date: null
    }
    errors.value = {}
    emit('close')
  }
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    form.value.reversal_date = getTodayDate()
  }
})
</script>

