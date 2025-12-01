<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ title }}
              </h1>
              <p v-if="description" class="mt-2 text-gray-600 dark:text-gray-400">
                {{ description }}
              </p>
            </div>
            <div v-if="$slots.headerActions" class="flex gap-3">
              <slot name="headerActions" />
            </div>
          </div>
        </div>

        <!-- Filters Section (Optional) -->
        <div v-if="$slots.filters" class="mb-6">
          <slot name="filters" />
        </div>

        <!-- Stats Overview (Optional) -->
        <div v-if="$slots.stats" class="mb-8">
          <slot name="stats" />
        </div>

        <!-- Main Content -->
        <div class="space-y-6">
          <slot />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: null
  }
})
</script>

