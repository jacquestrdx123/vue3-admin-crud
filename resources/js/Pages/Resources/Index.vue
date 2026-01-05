<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ title || 'Resources' }}</h1>
        <p v-if="description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          {{ description }}
        </p>
      </div>

      <!-- Data Table -->
      <BaseDataTable
        :data="data.data"
        :columns="columns"
        :all-columns="allColumns"
        :actions="actions"
        :bulk-actions="bulkActions"
        :filters="filters"
        :custom-filters="customFilters"
        :filter-values="filterValues"
        :preset-views="presetViews"
        :active-preset="activePreset"
        :active-presets="activePresets"
        :resource-slug="resourceSlug"
        :raw-sql="rawSql"
        :loading="loading"
        :paginated="false"
        :server-side="true"
        @action="handleAction"
        @bulk-action="handleBulkAction"
        @sort="handleSort"
        @filter="handleFilter"
      >
        <template #header-actions>
          <Link
            :href="getRouteName('create') ? route(getRouteName('create')) : '#'"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
          >
            Add {{ title || 'Item' }}
          </Link>
        </template>
      </BaseDataTable>

      <!-- Pagination -->
      <Pagination
        v-if="data.links"
        :links="{ prev: data.prev_page_url, next: data.next_page_url }"
        :meta="{
          from: data.from,
          to: data.to,
          total: data.total,
          links: data.links
        }"
      />
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import BaseDataTable from '@/Components/Table/BaseDataTable.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import { usePreserveQueryParams } from '@/Composables/usePreserveQueryParams'

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  actions: {
    type: Array,
    default: () => []
  },
  bulkActions: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Array,
    default: () => []
  },
  customFilters: {
    type: Array,
    default: () => []
  },
  filterValues: {
    type: Object,
    default: () => ({})
  },
  presetViews: {
    type: Array,
    default: () => []
  },
  activePreset: {
    type: String,
    default: null
  },
  activePresets: {
    type: Array,
    default: () => []
  },
  allColumns: {
    type: Array,
    default: () => []
  },
  resourceSlug: {
    type: String,
    default: null
  },
  rawSql: {
    type: String,
    default: null
  },
  title: {
    type: String,
    default: null
  },
  description: {
    type: String,
    default: null
  }
})

const loading = ref(false)
const { getCurrentParams } = usePreserveQueryParams()

// Construct route name from resource slug
const getRouteName = (action) => {
  if (!props.resourceSlug) {
    // Fallback: try to extract from current route
    const currentRoute = route().current()
    if (currentRoute) {
      const parts = currentRoute.split('.')
      if (parts.length >= 2) {
        const resource = parts.slice(0, -1).join('.')
        return `${resource}.${action}`
      }
    }
    return null
  }
  
  // Convert slug to route name (e.g., 'members' -> 'vue.members')
  const routePrefix = 'vue.'
  return `${routePrefix}${props.resourceSlug}.${action}`
}

const handleAction = ({ action, row }) => {
  // Row should always have an id property (normalized by BaseDataTable)
  if (!row?.id) {
    console.error('Row ID not found', row)
    return
  }

  const routeName = getRouteName(action)
  if (!routeName || !route(routeName)) {
    return
  }

  switch (action) {
    case 'view':
      router.visit(route(getRouteName('show'), row.id))
      break
    case 'edit':
      router.visit(route(routeName, row.id))
      break
    case 'delete':
      if (confirm('Are you sure you want to delete this item?')) {
        router.delete(route(getRouteName('destroy'), row.id), {
          preserveScroll: true,
          onSuccess: () => {
            // Success notification handled by flash messages
          }
        })
    }
      break
  }
}

const handleBulkAction = ({ action, rows }) => {
  // Rows should always have an id property (normalized by BaseDataTable)
  const ids = rows.map(r => r.id).filter(id => id !== null && id !== undefined)
  const routeName = getRouteName('bulk-action')
  if (!routeName || !route(routeName)) {
    return
  }

  switch (action) {
    case 'delete':
      if (confirm(`Are you sure you want to delete ${ids.length} items?`)) {
        router.post(route(routeName), {
          action: 'delete',
          ids
        }, {
          preserveScroll: true
        })
      }
      break
    case 'export':
    router.post(route(routeName), {
        action: 'export',
        ids
    })
      break
  }
}

const handleSort = ({ column, direction }) => {
  const routeName = getRouteName('index')
  if (!routeName || !route(routeName)) {
    return
  }

  // Get current URL params to preserve filters and presets
  const currentParams = getCurrentParams()
  const params = {
    ...currentParams,
      sort_column: column,
      sort_direction: direction
  }
  
  router.get(route(routeName), params, {
      preserveState: true,
    preserveScroll: true,
    replace: true
    })
}

const handleFilter = (filters) => {
  const routeName = getRouteName('index')
  if (!routeName || !route(routeName)) {
    return
  }

    router.get(route(routeName), filters, {
      preserveState: true,
      preserveScroll: true
    })
}
</script>

