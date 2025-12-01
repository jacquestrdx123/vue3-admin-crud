<template>
  <div
    v-if="impersonatingUser"
    class="bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-800"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-yellow-600 dark:text-yellow-400">
              <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
              Impersonation Mode Active
            </p>
            <p class="text-xs text-yellow-700 dark:text-yellow-400">
              You are viewing this account as {{ impersonatingUser.name }}
            </p>
          </div>
        </div>
        <button
          @click="stopImpersonating"
          class="px-4 py-2 text-sm font-semibold text-yellow-800 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-900/60 transition-colors"
        >
          Stop Impersonating
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

const impersonatingUser = computed(() => page.props.impersonating_user)

const stopImpersonating = () => {
  router.post(route('admin-member.stop-impersonating'), {}, {
    preserveScroll: false,
    preserveState: false,
  })
}
</script>

