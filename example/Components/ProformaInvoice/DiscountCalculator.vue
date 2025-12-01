<template>
  <Modal :show="show" @close="close" max-width="2xl">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Discount Calculator</h2>
        <button
          @click="close"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="space-y-6">
        <!-- Line Items to Apply Discount To -->
        <div>
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Select items to discount:
          </h3>
          <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-3">
            <label
              v-for="(item, index) in positiveLineItems"
              :key="index"
              class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-800 rounded cursor-pointer"
            >
              <input
                type="checkbox"
                :checked="selectedItems.includes(index)"
                @change="toggleItem(index)"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <span class="flex-1 text-sm text-gray-900 dark:text-gray-100">
                {{ item.old_description || 'Unnamed Item' }}
              </span>
              <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ formatCurrency(item.amount) }} (excl. VAT)
              </span>
            </label>
          </div>
        </div>

        <!-- Discount Type Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Discount Type:
          </label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="radio"
                v-model="discountType"
                value="percentage"
                class="text-indigo-600 focus:ring-indigo-500"
              />
              <span class="text-sm text-gray-900 dark:text-gray-100">Percentage</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="radio"
                v-model="discountType"
                value="fixed"
                class="text-indigo-600 focus:ring-indigo-500"
              />
              <span class="text-sm text-gray-900 dark:text-gray-100">Fixed Amount</span>
            </label>
          </div>
        </div>

        <!-- Discount Value Input -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Discount {{ discountType === 'percentage' ? 'Percentage' : 'Amount' }}:
          </label>
          <input
            v-model.number="discountValue"
            type="number"
            step="0.01"
            min="0"
            :max="discountType === 'percentage' ? 100 : undefined"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
            :placeholder="discountType === 'percentage' ? 'Enter percentage (0-100)' : 'Enter amount'"
          />
        </div>

        <!-- Discount Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Discount Description:
          </label>
          <input
            v-model="discountDescription"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
            placeholder="e.g., Early payment discount"
          />
        </div>

        <!-- Calculated Discount Preview -->
        <div v-if="calculatedDiscount > 0" class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-md p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Selected Items Total (excl. VAT):</span>
            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(selectedItemsTotal) }}</span>
          </div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Discount Amount (excl. VAT):</span>
            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">-{{ formatCurrency(calculatedDiscount) }}</span>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-indigo-200 dark:border-indigo-700 mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Discount VAT (15%):</span>
            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">-{{ formatCurrency(calculatedDiscount * 0.15) }}</span>
          </div>
          <div class="flex items-center justify-between mt-2 pt-2 border-t border-indigo-200 dark:border-indigo-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Discount (incl. VAT):</span>
            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">-{{ formatCurrency(calculatedDiscount * 1.15) }}</span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="close"
          type="button"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
        >
          Cancel
        </button>
        <button
          @click="applyDiscount"
          type="button"
          :disabled="!canApplyDiscount"
          :class="[
            'px-4 py-2 text-sm font-medium text-white rounded-md',
            canApplyDiscount
              ? 'bg-indigo-600 hover:bg-indigo-700'
              : 'bg-gray-300 dark:bg-gray-600 cursor-not-allowed'
          ]"
        >
          Apply Discount
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Modal from '@/Components/UI/Modal.vue'
import { useProformaCalculations } from '@/Composables/useProformaCalculations'

const props = defineProps({
  show: { type: Boolean, required: true },
  lineItems: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'discount-applied'])

const { formatCurrency } = useProformaCalculations()

// State
const discountType = ref('percentage')
const discountValue = ref(0)
const discountDescription = ref('Discount')
const selectedItems = ref([])

// Compute positive line items (exclude existing discounts)
const positiveLineItems = computed(() => {
  return props.lineItems
    .map((item, index) => ({ ...item, originalIndex: index }))
    .filter(item => parseFloat(item.amount || 0) > 0)
})

// Computed values - calculate on pre-VAT amounts
const selectedItemsTotal = computed(() => {
  return selectedItems.value.reduce((total, index) => {
    const item = positiveLineItems.value.find(item => item.originalIndex === index)
    // Use amount (pre-VAT) instead of total for discount calculation
    return total + parseFloat(item?.amount || 0)
  }, 0)
})

const calculatedDiscount = computed(() => {
  if (selectedItems.value.length === 0 || discountValue.value <= 0) {
    return 0
  }

  if (discountType.value === 'percentage') {
    return (selectedItemsTotal.value * discountValue.value) / 100
  } else {
    return Math.min(discountValue.value, selectedItemsTotal.value)
  }
})

const canApplyDiscount = computed(() => {
  return selectedItems.value.length > 0 &&
         discountValue.value > 0 &&
         calculatedDiscount.value > 0
})

// Methods
const toggleItem = (index) => {
  const itemIndex = selectedItems.value.indexOf(index)
  if (itemIndex > -1) {
    selectedItems.value.splice(itemIndex, 1)
  } else {
    selectedItems.value.push(index)
  }
}

const applyDiscount = () => {
  if (!canApplyDiscount.value) return

  emit('discount-applied', {
    amount: calculatedDiscount.value,
    description: discountDescription.value || 'Discount'
  })

  close()
}

const close = () => {
  emit('close')
  resetForm()
}

const resetForm = () => {
  discountType.value = 'percentage'
  discountValue.value = 0
  discountDescription.value = 'Discount'
  selectedItems.value = []
}

// Watch for modal opening to select all items by default
watch(() => props.show, (isShowing) => {
  if (isShowing && positiveLineItems.value.length > 0) {
    // Auto-select all positive items
    selectedItems.value = positiveLineItems.value.map(item => item.originalIndex)
  }
})
</script>

