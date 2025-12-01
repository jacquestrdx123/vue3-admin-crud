import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Composable for data table operations
 * @param {string} resourceName - Resource name for routing
 * @param {Object} options - Configuration options
 * @returns {Object} - Table state and methods
 */
export function useDataTable(resourceName, options = {}) {
    const {
        initialSort = null,
        initialFilters = {},
        perPage = 15
    } = options

    const loading = ref(false)
    const selectedRows = ref([])
    const sortColumn = ref(initialSort?.column || null)
    const sortDirection = ref(initialSort?.direction || 'asc')
    const filters = ref({ ...initialFilters })
    const searchQuery = ref('')
    const currentPage = ref(1)

    const applyFilters = (newFilters = {}) => {
        filters.value = { ...filters.value, ...newFilters }
        currentPage.value = 1
        
        router.get(route(`vue.${resourceName}.index`), {
            ...filters.value,
            search: searchQuery.value,
            sort_column: sortColumn.value,
            sort_direction: sortDirection.value,
            page: currentPage.value
        }, {
            preserveState: true,
            preserveScroll: true,
            onStart: () => { loading.value = true },
            onFinish: () => { loading.value = false }
        })
    }

    const applySort = (column, direction = null) => {
        if (sortColumn.value === column && !direction) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
        } else {
            sortColumn.value = column
            sortDirection.value = direction || 'asc'
        }
        
        applyFilters()
    }

    const applySearch = (query) => {
        searchQuery.value = query
        currentPage.value = 1
        applyFilters()
    }

    const goToPage = (page) => {
        currentPage.value = page
        applyFilters()
    }

    const clearFilters = () => {
        filters.value = {}
        searchQuery.value = ''
        sortColumn.value = null
        sortDirection.value = 'asc'
        currentPage.value = 1
        applyFilters()
    }

    const selectRow = (id) => {
        if (selectedRows.value.includes(id)) {
            selectedRows.value = selectedRows.value.filter(rowId => rowId !== id)
        } else {
            selectedRows.value.push(id)
        }
    }

    const selectAll = (ids) => {
        if (selectedRows.value.length === ids.length) {
            selectedRows.value = []
        } else {
            selectedRows.value = [...ids]
        }
    }

    const clearSelection = () => {
        selectedRows.value = []
    }

    const isSelected = (id) => {
        return selectedRows.value.includes(id)
    }

    const hasSelection = computed(() => {
        return selectedRows.value.length > 0
    })

    const selectionCount = computed(() => {
        return selectedRows.value.length
    })

    return {
        loading,
        selectedRows,
        sortColumn,
        sortDirection,
        filters,
        searchQuery,
        currentPage,
        hasSelection,
        selectionCount,
        applyFilters,
        applySort,
        applySearch,
        goToPage,
        clearFilters,
        selectRow,
        selectAll,
        clearSelection,
        isSelected
    }
}

