<template>
  <span :class="badgeClass">
    <svg v-if="value" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
    </svg>
    <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
    </svg>
    <span v-if="label">{{ value ? trueLabel : falseLabel }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: Boolean,
    default: false
  },
  row: {
    type: Object,
    default: () => ({})
  },
  trueLabel: {
    type: String,
    default: 'Yes'
  },
  falseLabel: {
    type: String,
    default: 'No'
  },
  label: {
    type: Boolean,
    default: false
  },
  trueColor: {
    type: String,
    default: 'green'
  },
  falseColor: {
    type: String,
    default: 'red'
  }
})

const badgeClass = computed(() => {
  const color = props.value ? props.trueColor : props.falseColor
  return `inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-${color}-100 text-${color}-800 dark:bg-${color}-900 dark:text-${color}-200`
})
</script>

