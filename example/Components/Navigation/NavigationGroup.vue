<template>
  <div class="mb-6">
    <!-- Group Label -->
    <div v-if="group.label && sidebarOpen" class="px-4 mb-2">
      <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
        {{ group.label }}
      </h3>
    </div>

    <!-- Navigation Items -->
    <div class="space-y-1 px-2">
      <NavigationItem
        v-for="item in group.items"
        :key="item.name"
        :item="item"
        :sidebar-open="sidebarOpen"
      />
    </div>
  </div>
</template>

<script setup>
import NavigationItem from './NavigationItem.vue'

defineProps({
  group: {
    type: Object,
    required: true
  },
  sidebarOpen: {
    type: Boolean,
    default: true
  }
})
</script>

