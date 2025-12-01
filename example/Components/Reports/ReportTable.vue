<template>
  <div class="overflow-x-auto">
    <!-- Search Bar -->
    <div v-if="searchable" class="p-4 border-b border-gray-200 dark:border-gray-700">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search..."
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      />
    </div>

    <!-- Table -->
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            @click="column.sortable && sort(column.key)"
            :class="[
              'px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
              column.sortable ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800' : ''
            ]"
          >
            <div class="flex items-center gap-2">
              {{ column.label }}
              <span v-if="column.sortable && sortKey === column.key">
                <svg
                  v-if="sortDirection === 'asc'"
                  class="h-4 w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
                <svg
                  v-else
                  class="h-4 w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </span>
            </div>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <tr
          v-for="(row, index) in paginatedData"
          :key="index"
          :class="rowClass ? rowClass(row) : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
        >
          <td
            v-for="column in columns"
            :key="column.key"
            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"
          >
            <slot :name="`cell-${column.key}`" :row="row" :value="getValue(row, column.key)">
              {{ formatValue(getValue(row, column.key), column) }}
            </slot>
          </td>
        </tr>
        <tr v-if="paginatedData.length === 0">
          <td
            :colspan="columns.length"
            class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"
          >
            No data available
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1"
      class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6"
    >
      <!-- Mobile -->
      <div class="flex flex-1 justify-between sm:hidden">
        <button
          v-if="hasPrev"
          type="button"
          @click="goToPage(currentPage - 1)"
          class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          Previous
        </button>
        <button
          v-if="hasNext"
          type="button"
          @click="goToPage(currentPage + 1)"
          class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          Next
        </button>
      </div>

      <!-- Desktop -->
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            Showing
            <span class="font-medium">{{ displayFrom }}</span>
            to
            <span class="font-medium">{{ displayTo }}</span>
            of
            <span class="font-medium">{{ filteredData.length }}</span>
            results
          </p>
        </div>

        <div>
          <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
            <!-- Previous -->
            <button
              v-if="hasPrev"
              type="button"
              @click="goToPage(currentPage - 1)"
              class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
              </svg>
            </button>

            <!-- Page Numbers -->
            <template v-for="(page, index) in pageButtons" :key="index">
              <button
                v-if="page.type === 'page'"
                type="button"
                @click="goToPage(page.value)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                  page.active 
                    ? 'z-10 bg-ciba-green text-black font-semibold focus:z-20'
                    : 'text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                {{ page.label }}
              </button>
              <span
                v-else
                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 dark:text-gray-600 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 cursor-default"
              >
                {{ page.label }}
              </span>
            </template>

            <!-- Next -->
            <button
              v-if="hasNext"
              type="button"
              @click="goToPage(currentPage + 1)"
              class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  perPage: {
    type: Number,
    default: 50
  },
  searchable: {
    type: Boolean,
    default: true
  },
  rowClass: {
    type: Function,
    default: null
  }
})

const searchQuery = ref('')
const sortKey = ref(null)
const sortDirection = ref('asc')
const currentPage = ref(1)

// Reset pagination when search query changes
watch(searchQuery, () => {
  currentPage.value = 1
})

const filteredData = computed(() => {
  let result = [...props.data]

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(row =>
      props.columns.some(col => {
        const value = getValue(row, col.key)
        return value && String(value).toLowerCase().includes(query)
      })
    )
  }

  // Apply sorting
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = getValue(a, sortKey.value)
      const bVal = getValue(b, sortKey.value)
      
      if (aVal === bVal) return 0
      
      const comparison = aVal > bVal ? 1 : -1
      return sortDirection.value === 'asc' ? comparison : -comparison
    })
  }

  return result
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredData.value.length / props.perPage)))

const startIndex = computed(() => (currentPage.value - 1) * props.perPage)
const endIndex = computed(() => Math.min(startIndex.value + props.perPage, filteredData.value.length))

const paginatedData = computed(() => {
  return filteredData.value.slice(startIndex.value, endIndex.value)
})

const displayFrom = computed(() => (filteredData.value.length === 0 ? 0 : startIndex.value + 1))
const displayTo = computed(() => endIndex.value)

watch(totalPages, (newTotal) => {
  if (currentPage.value > newTotal) {
    currentPage.value = newTotal
  }
})

const hasPrev = computed(() => currentPage.value > 1)
const hasNext = computed(() => currentPage.value < totalPages.value)

const goToPage = (page) => {
  if (page < 1 || page > totalPages.value || page === currentPage.value) {
    return
  }

  currentPage.value = page
}

const pageButtons = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push({
        type: 'page',
        value: i,
        label: i,
        active: i === current
      })
    }
    return pages
  }

  const addPage = (value) => {
    pages.push({
      type: 'page',
      value,
      label: value,
      active: value === current
    })
  }

  const addEllipsis = () => {
    pages.push({
      type: 'ellipsis',
      label: '...'
    })
  }

  addPage(1)

  const windowSize = 10
  const halfWindow = Math.floor(windowSize / 2)

  let windowStart = current - halfWindow
  let windowEnd = current + halfWindow

  if (windowStart < 2) {
    windowEnd += 2 - windowStart
    windowStart = 2
  }

  if (windowEnd > total - 1) {
    windowStart -= windowEnd - (total - 1)
    windowEnd = total - 1
  }

  windowStart = Math.max(2, windowStart)
  windowEnd = Math.min(total - 1, windowEnd)

  if (windowStart > 2) {
    addEllipsis()
  }

  for (let i = windowStart; i <= windowEnd; i++) {
    addPage(i)
  }

  if (windowEnd < total - 1) {
    addEllipsis()
  }

  addPage(total)

  return pages
})

const getValue = (row, key) => {
  const keys = key.split('.')
  let value = row
  for (const k of keys) {
    value = value?.[k]
  }
  return value
}

const formatValue = (value, column) => {
  if (value === null || value === undefined) return '-'
  
  if (column.format === 'date' && value) {
    return new Date(value).toLocaleDateString()
  }
  
  if (column.format === 'currency' && typeof value === 'number') {
    return 'R' + value.toLocaleString('en-ZA', { minimumFractionDigits: 2 })
  }
  
  return value
}

const sort = (key) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDirection.value = 'asc'
  }
  currentPage.value = 1
}
</script>

