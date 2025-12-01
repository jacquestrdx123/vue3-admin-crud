<template>
  <Sidebar :navigation="navigation">
    <div class="flex flex-col min-h-screen">
      <!-- Top Navigation Bar -->
      <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                <slot name="title">Admin Panel</slot>
              </h1>
            </div>
            
            <div class="flex items-center gap-4">
              <!-- Global Search -->
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search... (Ctrl+K)"
                  class="w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  @keydown.ctrl.k.prevent="focusSearch"
                />
              </div>
              
              <!-- Notifications -->
              <button class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 bg-gray-50 dark:bg-gray-900">
        <!-- Breadcrumbs (optional) -->
        <div v-if="$slots.breadcrumbs" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <slot name="breadcrumbs" />
          </div>
        </div>

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
          <Alert v-if="$page.props.flash.success" type="success" dismissible>
            {{ $page.props.flash.success }}
          </Alert>
          <Alert v-if="$page.props.flash.error" type="error" dismissible>
            {{ $page.props.flash.error }}
          </Alert>
          <Alert v-if="$page.props.flash.warning" type="warning" dismissible>
            {{ $page.props.flash.warning }}
          </Alert>
          <Alert v-if="$page.props.flash.info" type="info" dismissible>
            {{ $page.props.flash.info }}
          </Alert>
        </div>

        <!-- Page Content -->
        <div class="py-6">
          <slot />
        </div>
      </main>
    </div>
  </Sidebar>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from '@/Components/Navigation/Sidebar.vue'
import Alert from '@/Components/UI/Alert.vue'

const props = defineProps({
  navigation: {
    type: Array,
    required: true
  }
})

const searchQuery = ref('')

const focusSearch = () => {
  // Focus search input
}
</script>

