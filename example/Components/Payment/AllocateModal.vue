<template>
  <Modal :show="show" title="Allocate Payment" max-width="2xl" @close="handleClose">
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
          <div>
            <span class="text-gray-500 dark:text-gray-400">Remaining:</span>
            <span class="ml-2 font-medium text-gray-900 dark:text-white">R{{ formatAmount(payment.remaining_amount) }}</span>
          </div>
          <div v-if="payment.member">
            <span class="text-gray-500 dark:text-gray-400">Current Member:</span>
            <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ payment.member.m_code }}</span>
          </div>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <SearchableSelect
          v-model="form.member_id"
          name="member_id"
          label="Member"
          placeholder="Search for a member..."
          search-placeholder="Type member code or name..."
          :search-url="route(props.searchMembersRoute)"
          :required="true"
          :error-messages="errors.member_id"
          @change="handleMemberChange"
        />

        <SearchableSelect
          v-if="form.member_id"
          v-model="form.proforma_invoice_id"
          name="proforma_invoice_id"
          label="Proforma Invoice (Optional)"
          placeholder="Select an invoice..."
          :options="invoiceOptions"
          :required="false"
          :error-messages="errors.proforma_invoice_id"
        />

        <div v-if="form.member_id && invoiceOptions.length === 0 && !loadingInvoices" class="text-sm text-gray-500 dark:text-gray-400 italic">
          No outstanding invoices found for this member.
        </div>
      </form>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <Button variant="outline" @click="handleClose" :disabled="processing">
          Cancel
        </Button>
        <Button variant="primary" @click="handleSubmit" :disabled="processing || !form.member_id">
          <span v-if="processing">Allocating...</span>
          <span v-else>Allocate</span>
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
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    default: null
  },
  searchMembersRoute: {
    type: String,
    default: 'vue.payments.search-members'
  },
  searchInvoicesRoute: {
    type: String,
    default: 'vue.payments.search-invoices'
  },
  storeAllocateRoute: {
    type: String,
    default: 'vue.payments.store-allocate'
  }
})

const emit = defineEmits(['close'])

const form = ref({
  member_id: null,
  proforma_invoice_id: null
})

const errors = ref({})
const processing = ref(false)
const invoiceOptions = ref([])
const loadingInvoices = ref(false)

const formatAmount = (amount) => {
  return Number(amount || 0).toFixed(2)
}

const handleMemberChange = async (member) => {
  form.value.proforma_invoice_id = null
  invoiceOptions.value = []

  if (!member || !member.value) return

  loadingInvoices.value = true
  try {
    const response = await axios.get(route(props.searchInvoicesRoute, member.value))
    invoiceOptions.value = response.data.map(invoice => ({
      value: invoice.id,
      label: `${invoice.invoice_number} - R${formatAmount(invoice.balance_due)} (Due: ${invoice.due_date})`
    }))
  } catch (error) {
    console.error('Error loading invoices:', error)
  } finally {
    loadingInvoices.value = false
  }
}

const handleSubmit = () => {
  if (!form.value.member_id || !props.payment) return

  processing.value = true
  errors.value = {}

  router.post(route(props.storeAllocateRoute, props.payment.id), form.value, {
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
      member_id: null,
      proforma_invoice_id: null
    }
    errors.value = {}
    invoiceOptions.value = []
    emit('close')
  }
}

watch(() => props.show, (newVal) => {
  if (newVal && props.payment) {
    form.value.member_id = props.payment.member_id || null
    if (form.value.member_id) {
      handleMemberChange({ value: form.value.member_id })
    }
  }
})
</script>

