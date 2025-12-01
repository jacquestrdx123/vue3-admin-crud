import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useInvoices() {
  const invoices = ref([])
  const selectedInvoices = ref([])
  const loading = ref(false)
  
  const totalOutstanding = computed(() => {
    return invoices.value
      .filter(inv => inv.status !== 'paid')
      .reduce((sum, inv) => sum + inv.total_amount, 0)
  })
  
  const selectedTotal = computed(() => {
    return invoices.value
      .filter(inv => selectedInvoices.value.includes(inv.id))
      .reduce((sum, inv) => sum + inv.total_amount, 0)
  })
  
  const fetchInvoices = async (filters = {}) => {
    loading.value = true
    try {
      router.get(route('vue.member.account.invoices'), filters, {
        preserveState: true,
        onSuccess: (page) => {
          invoices.value = page.props.invoices.data
        },
        onFinish: () => {
          loading.value = false
        }
      })
    } catch (error) {
      loading.value = false
      console.error('Error fetching invoices:', error)
    }
  }
  
  const payInvoice = (invoiceId) => {
    router.visit(route('vue.member.payments.pay-invoice', invoiceId))
  }
  
  const downloadInvoice = (invoiceId) => {
    window.open(route('vue.member.account.invoices.download', invoiceId), '_blank')
  }
  
  const toggleInvoiceSelection = (invoiceId) => {
    const index = selectedInvoices.value.indexOf(invoiceId)
    if (index > -1) {
      selectedInvoices.value.splice(index, 1)
    } else {
      selectedInvoices.value.push(invoiceId)
    }
  }
  
  const selectAll = (allInvoices) => {
    if (selectedInvoices.value.length === allInvoices.length) {
      selectedInvoices.value = []
    } else {
      selectedInvoices.value = allInvoices.map(inv => inv.id)
    }
  }
  
  return {
    invoices,
    selectedInvoices,
    loading,
    totalOutstanding,
    selectedTotal,
    fetchInvoices,
    payInvoice,
    downloadInvoice,
    toggleInvoiceSelection,
    selectAll
  }
}

