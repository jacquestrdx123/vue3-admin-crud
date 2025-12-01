<template>
  <Link
    :href="href"
    :class="[
      'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors',
      active
        ? 'border-member-primary text-gray-900 dark:text-white'
        : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'
    ]"
  >
    <slot />
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: {
    type: String,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
:root {
  --member-primary: #BAF504;
}

.border-member-primary {
  border-color: var(--member-primary);
}
</style>

