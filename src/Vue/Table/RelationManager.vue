<template>
  <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white">
        {{ title }}
      </h3>
      
      <button
        v-if="canCreate"
        @click="$emit('create')"
        class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
      >
        Add {{ singularTitle }}
      </button>
    </div>

    <!-- Table -->
    <BaseDataTable
      :data="data"
      :columns="columns"
      :actions="actions"
      :bulk-actions="bulkActions"
      :filters="filters"
      :searchable="searchable"
      :paginated="paginated"
      :per-page="perPage"
      :loading="loading"
      @action="handleAction"
      @bulk-action="handleBulkAction"
    />
  </div>
</template>

<script setup>
import BaseDataTable from './BaseDataTable.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  singularTitle: {
    type: String,
    required: true
  },
  data: {
    type: Array,
    default: () => []
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
  searchable: {
    type: Boolean,
    default: true
  },
  paginated: {
    type: Boolean,
    default: false
  },
  perPage: {
    type: Number,
    default: 10
  },
  loading: {
    type: Boolean,
    default: false
  },
  canCreate: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['create', 'action', 'bulk-action'])

const handleAction = (event) => {
  emit('action', event)
}

const handleBulkAction = (event) => {
  emit('bulk-action', event)
}
</script>

