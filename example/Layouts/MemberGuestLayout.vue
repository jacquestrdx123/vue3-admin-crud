<template>
  <div class="member-guest-layout min-h-screen bg-transparent dark:bg-transparent">
    <div class="h-2 w-full bg-ciba-top-bar-blue"></div>
    <!-- Simple Header for Guest Pages -->
    <header class="bg-ciba-top-bar-blue dark:bg-gray-800 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <a href="/">
              <img src="/images/ciba_logo_white.webp" alt="CIBA Logo" class="h-12 w-auto" />
            </a>
          </div>
          <div class="text-sm text-gray-600 dark:text-gray-400">
            <slot name="header-actions" />
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Simple Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
          © {{ currentYear }} CIBA. All rights reserved.
        </p>
      </div>
    </footer>

    <!-- Toast Notifications -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.member-guest-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>

