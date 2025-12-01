<template>
  <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ title }}</h3>
    
    <dl class="grid grid-cols-1 gap-x-8 gap-y-6" :class="columns === 2 ? 'sm:grid-cols-2' : ''">
      <div v-for="(item, index) in items" :key="index">
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
          {{ item.label }}
        </dt>
        <dd class="text-sm text-gray-900 dark:text-white">
          <slot :name="`item-${item.key}`" :value="item.value">
            {{ formatValue(item.value, item.type) }}
          </slot>
        </dd>
      </div>
    </dl>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  items: {
    type: Array,
    required: true
  },
  columns: {
    type: Number,
    default: 2
  }
})

const formatValue = (value, type) => {
  if (value === null || value === undefined || value === '') return '-'
  
  if (type === 'date') {
    return new Date(value).toLocaleDateString()
  }
  
  if (type === 'datetime') {
    return new Date(value).toLocaleString()
  }
  
  if (type === 'currency') {
    return 'R' + parseFloat(value).toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }
  
  if (type === 'boolean') {
    return value ? 'Yes' : 'No'
  }
  
  return value
}
</script>

