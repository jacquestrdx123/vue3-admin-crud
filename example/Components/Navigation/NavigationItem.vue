<template>
  <Link
    :href="item.url"
    :class="[
      'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
      isActive 
        ? 'bg-ciba-green/10 text-gray-900 dark:bg-ciba-green/20 dark:text-white font-medium' 
        : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
    ]"
    :title="!sidebarOpen ? item.label : null"
  >
    <!-- Icon -->
    <div class="flex-shrink-0">
      <component v-if="item.icon" :is="item.icon" class="w-5 h-5" />
      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>

    <!-- Label -->
    <span v-if="sidebarOpen" class="flex-1">
      {{ item.label }}
    </span>

    <!-- Badge -->
    <span v-if="sidebarOpen && item.badge" class="ml-auto px-2 py-0.5 text-xs font-medium rounded-full bg-gray-200 dark:bg-gray-700">
      {{ item.badge }}
    </span>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  sidebarOpen: {
    type: Boolean,
    default: true
  }
})

const page = usePage()

const isActive = computed(() => {
  const currentUrl = page.url
  return currentUrl === props.item.url || currentUrl.startsWith(props.item.url + '/')
})
</script>

