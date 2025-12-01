<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ title || 'Resources' }}</h1>
        </div>
        <BaseDataTable
          :data="data.data"
          :columns="columns"
          :actions="actions"
          :bulk-actions="bulkActions"
          :filters="filters"
          :custom-filters="customFilters"
          :filter-values="filterValues"
          :preset-views="presetViews"
          :active-presets="activePresets"
          :resource-slug="resourceSlug"
          :loading="loading"
          :paginated="false"
          :server-side="true"
          @action="handleAction"
          @bulk-action="handleBulkAction"
          @sort="handleSort"
          @filter="handleFilter"
        />
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
  </AdminLayout>
</template>
<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import BaseDataTable from '@/Components/Table/BaseDataTable.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const props = defineProps({
  data: { type: Object, required: true },
  columns: { type: Array, required: true },
  actions: { type: Array, default: () => [] },
  bulkActions: { type: Array, default: () => [] },
  filters: { type: Array, default: () => [] },
  customFilters: { type: Array, default: () => [] },
  filterValues: { type: Object, default: () => ({}) },
  presetViews: { type: Array, default: () => [] },
  activePresets: { type: Array, default: () => [] },
  resourceSlug: { type: String, default: null },
  title: { type: String, default: null }
})

const loading = ref(false)

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
  
  // Convert slug to route name (e.g., 'menu-items' -> 'vue.menu-items')
  const routePrefix = 'vue.'
  return `${routePrefix}${props.resourceSlug}.${action}`
}

const handleAction = ({ action, row }) => {
  const routeName = getRouteName(action)
  if (routeName && route(action)) {
    if (action === 'view' || action === 'show') {
      router.visit(route(routeName.replace('.view', '.show').replace('.show', '.show'), row.id))
    } else if (action === 'edit') {
      router.visit(route(routeName, row.id))
    } else {
      router.visit(route(routeName, row.id))
    }
  }
}

const handleBulkAction = ({ action, rows }) => {
  const routeName = getRouteName('bulk-action')
  if (routeName && route(routeName)) {
    router.post(route(routeName), {
      action,
      ids: rows.map(r => r.id)
    })
  }
}

const handleSort = ({ column, direction }) => {
  const routeName = getRouteName('index')
  if (routeName && route(routeName)) {
    router.get(route(routeName), {
      sort_column: column,
      sort_direction: direction
    }, {
      preserveState: true,
      preserveScroll: true
    })
  }
}

const handleFilter = (filters) => {
  const routeName = getRouteName('index')
  if (routeName && route(routeName)) {
    router.get(route(routeName), filters, {
      preserveState: true,
      preserveScroll: true
    })
  }
}
</script>

