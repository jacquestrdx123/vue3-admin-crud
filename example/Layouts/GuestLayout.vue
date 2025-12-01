<template>
  <div class="guest-layout min-h-screen bg-gray-100 dark:bg-gray-900">
    <slot />
    
    <!-- Toast Notifications -->
    <ToastContainer />
  </div>
</template>

<script setup>
import ToastContainer from '@/Components/UI/ToastContainer.vue'
</script>

<style scoped>
.guest-layout {
  min-height: 100vh;
}
</style>

