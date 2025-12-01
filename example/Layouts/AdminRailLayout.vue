<template>
  <div class="admin-layout min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar in Rail Mode -->
    <SidebarMenu :rail="true" />

    <!-- Main Content Area -->
    <div class="main-content ml-14">
      <!-- Top Bar -->
      <header class="top-bar bg-primary-700 dark:bg-primary-800 border-b border-primary-800 dark:border-primary-900 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-4">
          <Breadcrumbs />
        </div>
        <TopRightMenu />
      </header>

      <!-- Page Content -->
      <main class="page-content p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import SidebarMenu from '@/Components/Navigation/SidebarMenu.vue'
import TopRightMenu from '@/Components/Navigation/TopRightMenu.vue'
import Breadcrumbs from '@/Components/Navigation/Breadcrumbs.vue'
</script>

<style scoped>
.admin-layout {
  display: flex;
}

.main-content {
  flex: 1;
  min-width: 0;
  transition: margin-left 0.2s ease;
}

.top-bar {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.page-content {
  max-width: 100%;
}
</style>

