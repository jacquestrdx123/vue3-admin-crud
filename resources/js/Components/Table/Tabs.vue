<template>
  <div>
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700">
      <nav class="-mb-px flex space-x-8 px-4">
        <button
          v-for="tab in tabs"
          :key="tab.name"
          @click="selectTab(tab.name)"
          :class="[
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
            activeTab === tab.name
              ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
          ]"
        >
          {{ tab.label }}
          <span v-if="tab.badge" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-200 dark:bg-gray-700">
            {{ tab.badge }}
          </span>
        </button>
      </nav>
    </div>

    <!-- Tab Panels -->
    <div class="mt-4">
      <slot :activeTab="activeTab" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  tabs: {
    type: Array,
    required: true
  },
  defaultTab: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['change'])

const activeTab = ref(props.defaultTab || props.tabs[0]?.name)

const selectTab = (tabName) => {
  activeTab.value = tabName
  emit('change', tabName)
}
</script>

