<template>
  <div
    v-if="!effectiveLoading && meta"
    class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6"
  >
    <!-- Mobile -->
    <div class="flex flex-1 justify-between sm:hidden">
      <Link
        v-if="links.prev"
        :href="links.prev"
        class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Previous
      </Link>
      <Link
        v-if="links.next"
        :href="links.next"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Next
      </Link>
    </div>

    <!-- Desktop -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700 dark:text-gray-300">
          Showing
          <span class="font-medium">{{ meta.from }}</span>
          to
          <span class="font-medium">{{ meta.to }}</span>
          of
          <span class="font-medium">{{ meta.total }}</span>
          results
        </p>
      </div>
      
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
          <!-- Previous -->
          <Link
            v-if="links.prev"
            :href="links.prev"
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </Link>

          <!-- Page Numbers -->
          <template v-for="(link, index) in pageLinks" :key="index">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                link.active 
                  ? 'z-10 bg-ciba-green text-black font-semibold focus:z-20'
                  : 'text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
              ]"
            >
              {{ link.label }}
            </Link>
            <span
              v-else
              :class="[
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                link.active 
                  ? 'z-10 bg-ciba-green text-black font-semibold'
                  : 'text-gray-400 dark:text-gray-600 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 cursor-not-allowed'
              ]"
            >
              {{ link.label }}
            </span>
          </template>

          <!-- Next -->
          <Link
            v-if="links.next"
            :href="links.next"
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </Link>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  links: {
    type: Object,
    required: true
  },
  meta: {
    type: Object,
    required: true
  },
  loading: {
    type: [Boolean, null],
    default: null
  }
})

// Filter out Previous/Next from links array (we have dedicated buttons for those)
const pageLinks = computed(() => {
  if (!props.meta.links) return []
  
  return props.meta.links.filter(link => {
    const label = link.label?.toLowerCase() || ''
    return !label.includes('previous') && !label.includes('next') && !label.includes('&laquo;') && !label.includes('&raquo;')
  })
})

const internalLoading = ref(false)
let removeStartListener
let removeFinishListener

onMounted(() => {
  if (props.loading === null) {
    removeStartListener = router.on('start', () => {
      internalLoading.value = true
    })

    removeFinishListener = router.on('finish', () => {
      internalLoading.value = false
    })
  }
})

onBeforeUnmount(() => {
  removeStartListener?.()
  removeFinishListener?.()
})

watch(
  () => props.loading,
  (value, oldValue) => {
    if (oldValue === null && value !== null) {
      // stop listening when parent takes over
      removeStartListener?.()
      removeFinishListener?.()
    }

    if (oldValue !== null && value === null) {
      // re-register listeners if parent stops controlling loading
      removeStartListener = router.on('start', () => {
        internalLoading.value = true
      })

      removeFinishListener = router.on('finish', () => {
        internalLoading.value = false
      })
    }
  }
)

const effectiveLoading = computed(() => {
  if (props.loading === null) {
    return internalLoading.value
  }

  return props.loading
})
</script>

