<template>
  <nav v-if="breadcrumbs.length > 0" class="breadcrumbs flex items-center gap-2 text-sm">
    <template v-for="(item, index) in breadcrumbs" :key="index">
      <!-- Separator -->
      <MaterialIcon
        v-if="index > 0"
        icon="chevron_right"
        size="sm"
        class="text-gray-500 dark:text-gray-400"
      />

      <!-- Breadcrumb Item -->
      <a
        v-if="!item.active && item.href !== '#'"
        :href="item.href"
        @click.prevent="navigate(item.href)"
        class="breadcrumb-link text-gray-900 dark:text-gray-100 hover:text-ciba-green transition-colors"
      >
        {{ item.title }}
      </a>
      <span
        v-else
        class="breadcrumb-current text-black dark:text-white font-medium"
      >
        {{ item.title }}
      </span>
    </template>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import MaterialIcon from './MaterialIcon.vue'

const page = usePage()

const breadcrumbs = computed(() => {
  return page?.props?.breadcrumbs || []
})

const navigate = (href) => {
  if (href && href !== '#') {
    router.visit(href)
  }
}
</script>

<style scoped>
.breadcrumb-link {
  white-space: nowrap;
  text-decoration: none;
}

.breadcrumb-current {
  white-space: nowrap;
}
</style>

