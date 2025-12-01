<template>
  <MemberLayout :showBreadcrumbs="showBreadcrumbs" :breadcrumbs="breadcrumbs">
    <div class="member-dashboard-layout">
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
              {{ title }}
            </h1>
            <p v-if="description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              {{ description }}
            </p>
          </div>
          <div v-if="$slots.headerActions" class="flex gap-3">
            <slot name="headerActions" />
          </div>
        </div>
      </div>

      <!-- Alert Banner (if any) -->
      <div v-if="$slots.alert" class="mb-6">
        <slot name="alert" />
      </div>

      <!-- Stats Section (Optional) -->
      <div v-if="$slots.stats" class="mb-8">
        <slot name="stats" />
      </div>

      <!-- Filters Section (Optional) -->
      <div v-if="$slots.filters" class="mb-6">
        <slot name="filters" />
      </div>

      <!-- Main Content -->
      <div class="space-y-6">
        <slot />
      </div>
    </div>
  </MemberLayout>
</template>

<script setup>
import MemberLayout from '@/Layouts/MemberLayout.vue'

defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: null
  },
  showBreadcrumbs: {
    type: Boolean,
    default: true
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  }
})
</script>

