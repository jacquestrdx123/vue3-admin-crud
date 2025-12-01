<template>
  <!-- Modal Overlay using Teleport -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4">
        <!-- Backdrop -->
        <div
          @click="closeModal"
          class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"
        ></div>

        <!-- Modal Container -->
        <div
          ref="searchContainer"
          class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <!-- Search Input -->
          <div class="relative border-b border-gray-200 dark:border-gray-700">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              ref="searchInput"
              v-model="searchQuery"
              @input="onSearchInput"
              @keydown.down.prevent="navigateResults('down')"
              @keydown.up.prevent="navigateResults('up')"
              @keydown.enter.prevent="selectResult"
              @keydown.esc="closeModal"
              type="text"
              placeholder="Search members, invoices, transactions..."
              class="w-full pl-14 pr-14 py-4 text-lg bg-transparent border-0 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-0"
              style="padding-left: 3.5rem !important;"
            />
            <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <button
                @click="clearSearch"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Search Results Container -->
          <div class="max-h-96 overflow-y-auto">
            <!-- Loading State -->
            <div v-if="isLoading" class="p-8 text-center">
              <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
              <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Searching...</p>
            </div>

            <!-- No Results -->
            <div v-else-if="!hasResults && searchQuery.length >= 2" class="p-8 text-center text-gray-600 dark:text-gray-400">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="mt-2">No results found for "{{ searchQuery }}"</p>
            </div>

            <!-- Empty State (Initial) -->
            <div v-else-if="!searchQuery" class="p-12 text-center">
              <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <p class="mt-4 text-base text-gray-600 dark:text-gray-400">Type to search members, invoices, and transactions</p>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">Press <kbd class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600">ESC</kbd> to close</p>
            </div>

            <!-- Results -->
            <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
        <!-- Members Section -->
        <div v-if="results.members && results.members.length > 0">
          <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900">
            <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Members</h3>
          </div>
          <div
            v-for="(member, index) in results.members"
            :key="`member-${member.id}`"
            @click="navigateToMember(member)"
            @mouseenter="selectedIndex = getMemberIndex(index)"
            :class="[
              'px-4 py-3 cursor-pointer transition-colors',
              selectedIndex === getMemberIndex(index)
                ? 'bg-blue-50 dark:bg-blue-900/20'
                : 'hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ member.full_name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ member.m_code }} • {{ member.email }}
                </p>
                <div class="flex gap-2 mt-1">
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                      getStatusBadgeClass(member.status)
                    ]"
                  >
                    {{ member.status }}
                  </span>
                  <span v-if="member.balance" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    Balance: R{{ formatCurrency(member.balance) }}
                  </span>
                </div>
              </div>
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Proforma Invoices Section -->
        <div v-if="results.proforma_invoices && results.proforma_invoices.length > 0">
          <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900">
            <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Proforma Invoices</h3>
          </div>
          <div
            v-for="(invoice, index) in results.proforma_invoices"
            :key="`invoice-${invoice.id}`"
            @click="navigateToProformaInvoice(invoice)"
            @mouseenter="selectedIndex = getProformaInvoiceIndex(index)"
            :class="[
              'px-4 py-3 cursor-pointer transition-colors',
              selectedIndex === getProformaInvoiceIndex(index)
                ? 'bg-blue-50 dark:bg-blue-900/20'
                : 'hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ invoice.invoice_number }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ invoice.member_name }} • {{ formatDate(invoice.issue_date) }}
                </p>
                <div class="flex gap-2 mt-1">
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                      getInvoiceStatusBadgeClass(invoice.status)
                    ]"
                  >
                    {{ invoice.status }}
                  </span>
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    R{{ formatCurrency(invoice.total_amount) }}
                  </span>
                </div>
              </div>
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Transactions Section -->
        <div v-if="results.transactions && results.transactions.length > 0">
          <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900">
            <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Transactions</h3>
          </div>
          <div
            v-for="(transaction, index) in results.transactions"
            :key="`transaction-${transaction.id}`"
            @click="navigateToTransaction(transaction)"
            @mouseenter="selectedIndex = getTransactionIndex(index)"
            :class="[
              'px-4 py-3 cursor-pointer transition-colors',
              selectedIndex === getTransactionIndex(index)
                ? 'bg-blue-50 dark:bg-blue-900/20'
                : 'hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ transaction.type }} - {{ transaction.reference_number || 'N/A' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ transaction.member_name }} • {{ formatDate(transaction.transaction_date) }}
                </p>
                <div class="flex gap-2 mt-1">
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                      transaction.debit ? 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400' : 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400'
                    ]"
                  >
                    {{ transaction.debit ? 'Debit' : 'Credit' }}
                  </span>
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    R{{ formatCurrency(transaction.amount) }}
                  </span>
                </div>
              </div>
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { useGlobalSearch } from '@/Composables/useGlobalSearch.js'
import axios from 'axios'

const { isOpen, closeModal } = useGlobalSearch()

const searchQuery = ref('')
const isLoading = ref(false)
const results = ref({
  members: [],
  proforma_invoices: [],
  transactions: []
})
const selectedIndex = ref(0)
const searchContainer = ref(null)
const searchInput = ref(null)
let searchTimeout = null

const hasResults = computed(() => {
  return (results.value.members?.length > 0) ||
         (results.value.proforma_invoices?.length > 0) ||
         (results.value.transactions?.length > 0)
})

const totalResults = computed(() => {
  return (results.value.members?.length || 0) +
         (results.value.proforma_invoices?.length || 0) +
         (results.value.transactions?.length || 0)
})

const getMemberIndex = (index) => index
const getProformaInvoiceIndex = (index) => (results.value.members?.length || 0) + index
const getTransactionIndex = (index) => (results.value.members?.length || 0) + (results.value.proforma_invoices?.length || 0) + index

const onSearchInput = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  if (searchQuery.value.length < 2) {
    results.value = {
      members: [],
      proforma_invoices: [],
      transactions: []
    }
    isLoading.value = false
    return
  }

  isLoading.value = true
  searchTimeout = setTimeout(async () => {
    await performSearch()
  }, 1000)
}

const performSearch = async () => {
  try {
    const response = await axios.get('/vue-admin/api/global-search', {
      params: { q: searchQuery.value }
    })
    results.value = response.data
    selectedIndex.value = 0
    isLoading.value = false
  } catch (error) {
    console.error('Search error:', error)
    isLoading.value = false
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  results.value = {
    members: [],
    proforma_invoices: [],
    transactions: []
  }
  selectedIndex.value = 0
  isLoading.value = false
}

const navigateResults = (direction) => {
  if (!hasResults.value) return

  if (direction === 'down') {
    selectedIndex.value = Math.min(selectedIndex.value + 1, totalResults.value - 1)
  } else {
    selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
  }
}

const selectResult = () => {
  if (!hasResults.value) return

  const membersCount = results.value.members?.length || 0
  const invoicesCount = results.value.proforma_invoices?.length || 0

  if (selectedIndex.value < membersCount) {
    navigateToMember(results.value.members[selectedIndex.value])
  } else if (selectedIndex.value < membersCount + invoicesCount) {
    navigateToProformaInvoice(results.value.proforma_invoices[selectedIndex.value - membersCount])
  } else {
    navigateToTransaction(results.value.transactions[selectedIndex.value - membersCount - invoicesCount])
  }
}

const navigateToMember = (member) => {
  router.visit(`/vue-admin/members/${member.id}`)
  closeModal()
  clearSearch()
}

const navigateToProformaInvoice = (invoice) => {
  router.visit(`/vue-admin/proforma-invoices/${invoice.id}`)
  closeModal()
  clearSearch()
}

const navigateToTransaction = (transaction) => {
  router.visit(`/vue-admin/transactions/${transaction.id}`)
  closeModal()
  clearSearch()
}

const getStatusBadgeClass = (status) => {
  const statusClasses = {
    'active': 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400',
    'suspended': 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400',
    'cancelled': 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400',
    'pending': 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-400'
  }
  return statusClasses[status?.toLowerCase()] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400'
}

const getInvoiceStatusBadgeClass = (status) => {
  const statusClasses = {
    'paid': 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400',
    'due': 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-400',
    'partially_paid': 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400',
    'cancelled': 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400'
  }
  return statusClasses[status?.toLowerCase()] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400'
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return Number(amount).toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-ZA', { year: 'numeric', month: 'short', day: 'numeric' })
}

// Focus input when modal opens
watch(isOpen, async (newValue) => {
  if (newValue) {
    await nextTick()
    searchInput.value?.focus()
  } else {
    // Clear search when modal closes
    clearSearch()
  }
})

// Body scroll lock when modal is open
watch(isOpen, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

onMounted(() => {
  // Cleanup on unmount
})

onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  // Restore body scroll
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}

/* Custom scrollbar for results */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.5);
}
</style>

