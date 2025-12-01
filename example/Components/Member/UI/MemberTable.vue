<template>
  <div class="member-table">
    <!-- Table Header Actions -->
    <div v-if="showSearch || showFilters || $slots.actions" class="mb-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
      <div v-if="showSearch" class="flex-1 max-w-md">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ciba-green focus:border-transparent"
            @input="handleSearch"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
      
      <div v-if="$slots.actions" class="flex gap-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              :class="{ 'cursor-pointer hover:bg-gray-100': column.sortable }"
              @click="column.sortable ? handleSort(column.key) : null"
            >
              <div class="flex items-center gap-2">
                <span>{{ column.label }}</span>
                <span v-if="column.sortable && sortKey === column.key" class="text-ciba-green">
                  <svg v-if="sortOrder === 'asc'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </span>
              </div>
            </th>
            <th v-if="showActions" scope="col" class="relative px-6 py-3">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="isLoading">
            <td :colspan="columns.length + (showActions ? 1 : 0)" class="px-6 py-12 text-center">
              <div class="flex justify-center items-center gap-2 text-gray-500">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Loading...</span>
              </div>
            </td>
          </tr>
          
          <tr v-else-if="!processedData.length">
            <td :colspan="columns.length + (showActions ? 1 : 0)" class="px-6 py-12 text-center text-gray-500">
              {{ emptyMessage }}
            </td>
          </tr>
          
          <tr
            v-else
            v-for="(row, index) in processedData"
            :key="row.id || index"
            class="hover:bg-gray-50 transition-colors"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm"
              :class="column.class || 'text-gray-900'"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="getNestedValue(row, column.key)">
                {{ formatCellValue(row, column) }}
              </slot>
            </td>
            <td v-if="showActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <slot name="actions" :row="row" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="showPagination && totalPages > 1" class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Showing {{ startIndex + 1 }} to {{ Math.min(endIndex, totalItems) }} of {{ totalItems }} results
      </div>
      
      <div class="flex gap-2">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="goToPage(page)"
          :class="[
            'px-3 py-1 border rounded',
            page === currentPage
              ? 'bg-ciba-green text-black border-ciba-green'
              : 'border-gray-300 hover:bg-gray-50'
          ]"
        >
          {{ page }}
        </button>
        
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    validator: (columns) => columns.every(col => col.key && col.label)
  },
  data: {
    type: Array,
    default: () => []
  },
  showSearch: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  searchKeys: {
    type: Array,
    default: () => []
  },
  showFilters: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  perPage: {
    type: Number,
    default: 10
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  emptyMessage: {
    type: String,
    default: 'No data available'
  }
})

const emit = defineEmits(['search', 'sort', 'page-change'])

const searchQuery = ref('')
const sortKey = ref(null)
const sortOrder = ref('asc')
const currentPage = ref(1)

const filteredData = computed(() => {
  if (!searchQuery.value) return props.data

  const query = searchQuery.value.toLowerCase()
  const searchableKeys = props.searchKeys.length ? props.searchKeys : props.columns.map(col => col.key)

  return props.data.filter(row => {
    return searchableKeys.some(key => {
      const value = getNestedValue(row, key)
      return value && String(value).toLowerCase().includes(query)
    })
  })
})

const sortedData = computed(() => {
  if (!sortKey.value) return filteredData.value

  return [...filteredData.value].sort((a, b) => {
    const aVal = getNestedValue(a, sortKey.value)
    const bVal = getNestedValue(b, sortKey.value)

    if (aVal === bVal) return 0

    const comparison = aVal > bVal ? 1 : -1
    return sortOrder.value === 'asc' ? comparison : -comparison
  })
})

const totalItems = computed(() => sortedData.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / props.perPage))
const startIndex = computed(() => (currentPage.value - 1) * props.perPage)
const endIndex = computed(() => startIndex.value + props.perPage)

const processedData = computed(() => {
  if (!props.showPagination) return sortedData.value
  return sortedData.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)

  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

function getNestedValue(obj, path) {
  return path.split('.').reduce((current, prop) => current?.[prop], obj)
}

function formatCellValue(row, column) {
  const value = getNestedValue(row, column.key)
  
  if (column.format) {
    return column.format(value, row)
  }
  
  if (value === null || value === undefined) {
    return '-'
  }
  
  return value
}

function handleSearch() {
  currentPage.value = 1
  emit('search', searchQuery.value)
}

function handleSort(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
  
  emit('sort', { key: sortKey.value, order: sortOrder.value })
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  emit('page-change', page)
}

watch(() => props.data, () => {
  currentPage.value = 1
})

defineExpose({
  searchQuery,
  sortKey,
  sortOrder,
  currentPage,
  reset: () => {
    searchQuery.value = ''
    sortKey.value = null
    sortOrder.value = 'asc'
    currentPage.value = 1
  }
})
</script>

<style scoped>
.bg-ciba-green {
  background-color: #BAF504;
}

.border-ciba-green {
  border-color: #BAF504;
}

.text-ciba-green {
  color: #BAF504;
}

.focus\:ring-ciba-green:focus {
  --tw-ring-color: #BAF504;
}
</style>

