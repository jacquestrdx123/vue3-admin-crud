<template>
  <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
    <div v-if="title" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              {{ column.label }}
            </th>
            <th v-if="hasActions" class="sticky right-0 z-10 px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="!data || data.length === 0">
            <td :colspan="columns.length + (hasActions ? 1 : 0)" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
              {{ emptyMessage || 'No data available' }}
            </td>
          </tr>
          <tr
            v-for="(row, index) in data"
            :key="index"
            :class="[
              'group hover:bg-gray-50 dark:hover:bg-gray-700',
              rowClickHandler ? 'cursor-pointer' : ''
            ]"
            @click="handleRowClick(row)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 text-sm text-gray-900 dark:text-gray-300',
                wrapText || column.wrap ? 'whitespace-normal break-words' : 'whitespace-nowrap'
              ]"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="getCellValue(row, column.key)">
                {{ formatCellValue(row, column) }}
              </slot>
            </td>
            <td 
              v-if="hasActions" 
              :class="[
                'sticky right-0 px-6 py-4 whitespace-nowrap text-right text-sm font-medium bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 z-10',
                rowClickHandler ? 'group-hover:bg-gray-50 dark:group-hover:bg-gray-700' : ''
              ]"
              @click.stop
            >
              <slot name="actions" :row="row"></slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex flex-col items-center gap-4">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex items-center gap-2">
          <!-- Previous Button -->
          <button
            @click="$emit('page-change', pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            :class="[
              'px-3 py-1.5 rounded text-sm font-medium transition-colors',
              pagination.current_page === 1
                ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            Previous
          </button>
          
          <!-- Page Numbers -->
          <div class="flex gap-1">
            <!-- First page -->
            <button
              v-if="pagination.current_page > 3 && pagination.last_page > 5"
              @click="$emit('page-change', 1)"
              class="px-3 py-1.5 rounded text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              1
            </button>
            <span
              v-if="pagination.current_page > 3 && pagination.last_page > 5"
              class="px-2 text-gray-500 dark:text-gray-400"
            >
              ...
            </span>
            
            <!-- Visible page numbers -->
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="$emit('page-change', page)"
              :class="[
                'px-3 py-1.5 rounded text-sm font-medium transition-colors',
                page === pagination.current_page
                  ? 'bg-ciba-green text-black font-semibold'
                  : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
              ]"
            >
              {{ page }}
            </button>
            
            <!-- Last page -->
            <span
              v-if="pagination.current_page < pagination.last_page - 2 && pagination.last_page > 5"
              class="px-2 text-gray-500 dark:text-gray-400"
            >
              ...
            </span>
            <button
              v-if="pagination.current_page < pagination.last_page - 2 && pagination.last_page > 5"
              @click="$emit('page-change', pagination.last_page)"
              class="px-3 py-1.5 rounded text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              {{ pagination.last_page }}
            </button>
          </div>
          
          <!-- Next Button -->
          <button
            @click="$emit('page-change', pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            :class="[
              'px-3 py-1.5 rounded text-sm font-medium transition-colors',
              pagination.current_page === pagination.last_page
                ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    default: () => []
  },
  pagination: {
    type: Object,
    default: null
  },
  hasActions: {
    type: Boolean,
    default: false
  },
  emptyMessage: String,
  rowClickHandler: {
    type: Function,
    default: null
  },
  wrapText: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['page-change', 'row-click'])

const getCellValue = (row, key) => {
  return key.split('.').reduce((obj, k) => obj?.[k], row)
}

const formatCellValue = (row, column) => {
  const value = getCellValue(row, column.key)
  
  if (value === null || value === undefined) return '-'
  
  if (column.type === 'date') {
    return new Date(value).toLocaleDateString()
  }
  
  if (column.type === 'datetime') {
    return new Date(value).toLocaleString()
  }
  
  if (column.type === 'currency') {
    return 'R' + parseFloat(value).toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }
  
  if (column.type === 'boolean') {
    return value ? 'Yes' : 'No'
  }
  
  return value
}

const visiblePages = computed(() => {
  if (!props.pagination) return []
  
  const { current_page, last_page } = props.pagination
  const pages = []
  const delta = 1 // Show 1 page on each side of current page
  
  let start = Math.max(1, current_page - delta)
  let end = Math.min(last_page, current_page + delta)
  
  // Adjust if we're near the start or end
  if (current_page <= 2) {
    end = Math.min(last_page, 3)
  }
  if (current_page >= last_page - 1) {
    start = Math.max(1, last_page - 2)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const handleRowClick = (row) => {
  if (props.rowClickHandler) {
    props.rowClickHandler(row)
  } else {
    emit('row-click', row)
  }
}
</script>

