import { ref, computed, watch } from 'vue'

/**
 * Composable for ProformaInvoice calculations
 * Matches the logic from Livewire components ProformaInvoiceCreate and ProformaInvoiceEdit
 */
export function useProformaCalculations(lineItems) {
  /**
   * Calculate totals for a single line item
   * @param {Object} item - Line item with amount, vat_rate, line_item_type
   * @returns {Object} - Updated item with vat and total
   */
  const calculateLineItemTotals = (item) => {
    const amount = parseFloat(item.amount || 0)
    const vatRate = parseFloat(item.vat_rate || 15)
    const lineItemType = item.line_item_type || 'manual'

    let vat = 0
    let total = 0

    // For discount line items, ensure amount is negative and calculate VAT on discount
    if (lineItemType === 'discount') {
      const negativeAmount = amount > 0 ? -amount : amount
      // Calculate VAT on the discount amount (just like regular line items)
      vat = Math.round((negativeAmount * (vatRate / 100)) * 100) / 100
      total = Math.round((negativeAmount + vat) * 100) / 100
      
      return {
        ...item,
        amount: Math.round(negativeAmount * 100) / 100,
        vat: vat,
        total: total
      }
    } else {
      // For regular line items (manual/product/designation), calculate VAT on full amount
      vat = Math.round((amount * (vatRate / 100)) * 100) / 100
      total = Math.round((amount + vat) * 100) / 100
      
      return {
        ...item,
        vat: vat,
        total: total
      }
    }
  }

  /**
   * Calculate overall totals from all line items
   * @param {Array} items - Array of line items
   * @returns {Object} - Totals object with subtotal, total_vat, total_amount, balance_due
   */
  const calculateTotals = (items) => {
    let subtotal = 0
    let totalVat = 0

    // Sum all amounts (pre-VAT) and all VAT amounts
    // Each line item (including discounts) has VAT calculated individually
    items.forEach(item => {
      const amount = parseFloat(item.amount || 0)
      const itemVat = parseFloat(item.vat || 0)

      subtotal += amount
      totalVat += itemVat
    })

    // Total amount is the sum of all line item totals (amount + vat for each)
    subtotal = Math.round(subtotal * 100) / 100
    totalVat = Math.round(totalVat * 100) / 100
    const totalAmount = Math.round((subtotal + totalVat) * 100) / 100
    const balanceDue = Math.round(totalAmount * 100) / 100

    return {
      subtotal,
      total_vat: totalVat,
      total_amount: totalAmount,
      balance_due: balanceDue
    }
  }

  /**
   * Recalculate all line items and return updated items with totals
   * @param {Array} items - Array of line items
   * @returns {Object} - Object with updatedItems and totals
   */
  const recalculateAll = (items) => {
    // Recalculate each line item
    const updatedItems = items.map(item => calculateLineItemTotals(item))
    
    // Calculate overall totals
    const totals = calculateTotals(updatedItems)

    return {
      updatedItems,
      totals
    }
  }

  /**
   * Validate that amount can be edited based on line item type
   * Product and designation line items should not have their amounts edited
   * @param {Object} item - Line item
   * @returns {boolean}
   */
  const canEditAmount = (item) => {
    return !['product', 'designation'].includes(item.line_item_type)
  }

  /**
   * Get available line item types based on invoice type
   * @param {string} invoiceType - 'application' or 'renewal'
   * @returns {Array} - Array of available line item types
   */
  const getAvailableLineItemTypes = (invoiceType) => {
    if (invoiceType === 'application') {
      return [
        { value: 'manual', label: 'Manual Description' },
        { value: 'designation', label: 'Designation-based' },
        { value: 'discount', label: 'Discount' }
      ]
    } else {
      return [
        { value: 'manual', label: 'Manual Description' },
        { value: 'product', label: 'Product-based' },
        { value: 'discount', label: 'Discount' }
      ]
    }
  }

  /**
   * Validate line item type against invoice type
   * @param {string} lineItemType - Type of line item
   * @param {string} invoiceType - Type of invoice
   * @returns {Object} - { valid: boolean, message: string }
   */
  const validateLineItemType = (lineItemType, invoiceType) => {
    if (invoiceType === 'application' && lineItemType === 'product') {
      return {
        valid: false,
        message: 'Products are not allowed for application invoices.'
      }
    }

    if (invoiceType === 'renewal' && lineItemType === 'designation') {
      return {
        valid: false,
        message: 'Designations are not allowed for renewal invoices.'
      }
    }

    return { valid: true, message: '' }
  }

  /**
   * Create a new empty line item
   * @param {string} invoiceType - Type of invoice (application or renewal)
   * @returns {Object} - New line item object
   */
  const createNewLineItem = (invoiceType = 'renewal') => {
    const defaultType = 'manual'

    return {
      id: null,
      line_item_type: defaultType,
      amount: 0.00,
      vat_rate: 15.00,
      vat: 0.00,
      total: 0.00,
      old_description: '',
      lineable_id: null,
      lineable_type: null
    }
  }

  /**
   * Create a discount line item
   * @param {number} discountAmount - Discount amount (pre-VAT, will be made negative)
   * @param {string} description - Description for the discount
   * @param {number} vatRate - VAT rate to apply to the discount (default 15)
   * @returns {Object} - New discount line item with VAT calculated
   */
  const createDiscountLineItem = (discountAmount, description = 'Discount', vatRate = 15) => {
    const negativeAmount = Math.round(-Math.abs(discountAmount) * 100) / 100
    // Calculate VAT on the discount amount (just like regular line items)
    const vat = Math.round((negativeAmount * (vatRate / 100)) * 100) / 100
    const total = Math.round((negativeAmount + vat) * 100) / 100
    
    const item = {
      id: null,
      line_item_type: 'discount',
      amount: negativeAmount,
      vat_rate: vatRate,
      vat: vat,
      total: total,
      old_description: description,
      lineable_id: null,
      lineable_type: null
    }

    return item
  }

  /**
   * Format currency for display
   * @param {number} amount - Amount to format
   * @returns {string} - Formatted currency string
   */
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-ZA', {
      style: 'currency',
      currency: 'ZAR'
    }).format(amount || 0)
  }

  /**
   * Determine line item type from database record
   * @param {Object} lineItem - Line item from database
   * @returns {string} - Line item type
   */
  const determineLineItemType = (lineItem) => {
    if (lineItem.lineable_type === 'App\\Models\\Product' && lineItem.lineable_id) {
      return 'product'
    }

    if (lineItem.lineable_type === 'App\\Models\\BillingProducts\\Designation' && lineItem.lineable_id) {
      return 'designation'
    }

    if (parseFloat(lineItem.amount) < 0) {
      return 'discount'
    }

    return 'manual'
  }

  return {
    calculateLineItemTotals,
    calculateTotals,
    recalculateAll,
    canEditAmount,
    getAvailableLineItemTypes,
    validateLineItemType,
    createNewLineItem,
    createDiscountLineItem,
    formatCurrency,
    determineLineItemType
  }
}

