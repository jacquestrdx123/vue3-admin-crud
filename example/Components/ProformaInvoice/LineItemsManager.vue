<template>
  <div class="space-y-4">
    <!-- Line Items Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Type
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Item / Description
            </th>
            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Amount
            </th>
            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              VAT %
            </th>
            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              VAT
            </th>
            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Total
            </th>
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="(item, index) in modelValue" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800">
            <!-- Type Selection -->
            <td class="px-3 py-4 whitespace-nowrap">
              <select
                :value="item.line_item_type"
                @change="updateLineItemType(index, $event.target.value)"
                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
              >
                <option v-for="type in availableTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
            </td>

            <!-- Item/Description Selection -->
            <td class="px-3 py-4">
              <!-- Product Selection -->
              <InlineSearchableSelect
                v-if="item.line_item_type === 'product'"
                :model-value="item.lineable_id"
                :options="products"
                placeholder="Select Product..."
                @update:model-value="updateProduct(index, $event)"
              />

              <!-- Designation Selection -->
              <InlineSearchableSelect
                v-else-if="item.line_item_type === 'designation'"
                :model-value="item.lineable_id"
                :options="designations"
                placeholder="Select Designation..."
                @update:model-value="updateDesignation(index, $event)"
              />

              <!-- Manual Description -->
              <input
                v-else
                type="text"
                :value="item.old_description"
                @input="updateDescription(index, $event.target.value)"
                placeholder="Enter description..."
                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
              />
            </td>

            <!-- Amount -->
            <td class="px-3 py-4 whitespace-nowrap">
              <input
                type="number"
                step="0.01"
                :value="item.amount"
                @input="updateAmount(index, $event.target.value)"
                :readonly="!canEditAmount(item)"
                :class="[
                  'w-24 px-2 py-1 text-sm text-right border border-gray-300 dark:border-gray-600 rounded-md',
                  canEditAmount(item) ? 'bg-white dark:bg-gray-800' : 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed',
                  'text-gray-900 dark:text-gray-100'
                ]"
              />
            </td>

            <!-- VAT Rate -->
            <td class="px-3 py-4 whitespace-nowrap">
              <input
                type="number"
                step="0.01"
                :value="item.vat_rate"
                @input="updateVatRate(index, $event.target.value)"
                class="w-20 px-2 py-1 text-sm text-right border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
              />
            </td>

            <!-- VAT Amount (calculated, readonly) -->
            <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-gray-100">
              {{ formatCurrency(item.vat) }}
            </td>

            <!-- Total (calculated, readonly) -->
            <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ formatCurrency(item.total) }}
            </td>

            <!-- Actions -->
            <td class="px-3 py-4 whitespace-nowrap text-center">
              <button
                @click="removeLineItem(index)"
                type="button"
                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                title="Remove line item"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-if="modelValue.length === 0">
            <td colspan="7" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
              No line items. Click "Add Line Item" to get started.
            </td>
          </tr>
        </tbody>

        <!-- Totals Footer -->
        <tfoot class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <td colspan="4" class="px-3 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
              Subtotal:
            </td>
            <td colspan="2" class="px-3 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ formatCurrency(totals.subtotal) }}
            </td>
            <td></td>
          </tr>
          <tr>
            <td colspan="4" class="px-3 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
              Total VAT:
            </td>
            <td colspan="2" class="px-3 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ formatCurrency(totals.total_vat) }}
            </td>
            <td></td>
          </tr>
          <tr>
            <td colspan="4" class="px-3 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
              Total Amount:
            </td>
            <td colspan="2" class="px-3 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
              {{ formatCurrency(totals.total_amount) }}
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-3">
      <button
        @click="addLineItem"
        type="button"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
      >
        Add Line Item
      </button>

      <button
        @click="$emit('open-discount-calculator')"
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
      >
        Add Discount
      </button>

      <button
        @click="recalculate"
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
      >
        Recalculate Totals
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useProformaCalculations } from '@/Composables/useProformaCalculations'
import InlineSearchableSelect from '@/Components/Form/InlineSearchableSelect.vue'

const props = defineProps({
  modelValue: { type: Array, required: true },
  invoiceType: { type: String, default: 'renewal' },
  products: { type: Array, default: () => [] },
  designations: { type: Array, default: () => [] }
})

const emit = defineEmits(['update:modelValue', 'update:totals', 'open-discount-calculator'])

const {
  calculateLineItemTotals,
  calculateTotals,
  recalculateAll,
  canEditAmount,
  getAvailableLineItemTypes,
  validateLineItemType,
  createNewLineItem,
  formatCurrency
} = useProformaCalculations()

const availableTypes = computed(() => getAvailableLineItemTypes(props.invoiceType))

const totals = computed(() => calculateTotals(props.modelValue))

// Watch totals and emit changes
watch(totals, (newTotals) => {
  emit('update:totals', newTotals)
}, { deep: true })

const updateLineItems = (items) => {
  emit('update:modelValue', items)
}

const addLineItem = () => {
  const newItem = createNewLineItem(props.invoiceType)
  updateLineItems([...props.modelValue, newItem])
}

const removeLineItem = (index) => {
  const items = [...props.modelValue]
  items.splice(index, 1)
  updateLineItems(items)
}

const updateLineItemType = (index, newType) => {
  const validation = validateLineItemType(newType, props.invoiceType)
  
  if (!validation.valid) {
    alert(validation.message)
    return
  }

  const items = [...props.modelValue]
  items[index] = {
    ...items[index],
    line_item_type: newType,
    lineable_id: null,
    lineable_type: null,
    old_description: '',
    amount: 0.00
  }
  
  const updatedItem = calculateLineItemTotals(items[index])
  items[index] = updatedItem
  updateLineItems(items)
}

const updateProduct = (index, productId) => {
  const product = props.products.find(p => (p.id || p.value) == productId)
  if (!product) return

  const items = [...props.modelValue]
  items[index] = {
    ...items[index],
    lineable_id: productId,
    lineable_type: 'App\\Models\\Product',
    amount: product.price,
    vat_rate: product.vat_rate,
    old_description: product.name || product.label
  }
  
  const updatedItem = calculateLineItemTotals(items[index])
  items[index] = updatedItem
  updateLineItems(items)
}

const updateDesignation = (index, designationId) => {
  const designation = props.designations.find(d => (d.id || d.value) == designationId)
  if (!designation) return

  const items = [...props.modelValue]
  items[index] = {
    ...items[index],
    lineable_id: designationId,
    lineable_type: 'App\\Models\\BillingProducts\\Designation',
    amount: designation.price,
    vat_rate: designation.vat_rate,
    old_description: designation.name || designation.label
  }
  
  const updatedItem = calculateLineItemTotals(items[index])
  items[index] = updatedItem
  updateLineItems(items)
}

const updateDescription = (index, description) => {
  const items = [...props.modelValue]
  items[index] = {
    ...items[index],
    old_description: description
  }
  updateLineItems(items)
}

const updateAmount = (index, amount) => {
  const item = props.modelValue[index]
  
  // Prevent editing amounts for product/designation items
  if (!canEditAmount(item)) {
    return
  }

  const items = [...props.modelValue]
  items[index] = {
    ...items[index],
    amount: parseFloat(amount) || 0
  }
  
  const updatedItem = calculateLineItemTotals(items[index])
  items[index] = updatedItem
  updateLineItems(items)
}

const updateVatRate = (index, vatRate) => {
  const items = [...props.modelValue]
  items[index] = {
    ...items[index],
    vat_rate: parseFloat(vatRate) || 15
  }
  
  const updatedItem = calculateLineItemTotals(items[index])
  items[index] = updatedItem
  updateLineItems(items)
}

const recalculate = () => {
  const { updatedItems } = recalculateAll(props.modelValue)
  updateLineItems(updatedItems)
}
</script>

