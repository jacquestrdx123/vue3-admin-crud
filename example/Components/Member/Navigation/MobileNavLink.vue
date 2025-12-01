<template>
  <Link
    :href="href"
    :class="[
      'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors',
      active
        ? 'border-member-primary bg-member-primary-light text-member-primary-dark'
        : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 hover:text-gray-800 dark:hover:text-gray-200'
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
  --member-primary-light: #F0FFB3;
  --member-primary-dark: #5A7302;
}

.border-member-primary {
  border-color: var(--member-primary);
}

.bg-member-primary-light {
  background-color: var(--member-primary-light);
}

.text-member-primary-dark {
  color: var(--member-primary-dark);
}
</style>

