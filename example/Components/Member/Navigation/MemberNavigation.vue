<template>
  <nav class="px-6 py-4 text-white bg-gray-900">
    <div class="flex items-center justify-between">
      <!-- Logo Section -->
      
      <!-- Hamburger Menu Button -->
      <a href="https://myciba.org/" class="flex items-center md:hidden lg:hidden hover:text-ciba-green">
        <img src="/asset/ciba-logo-white.png" class="h-8 mr-3" alt="CIBA Logo" />
        <span class="self-center text-2xl whitespace-nowrap dark:text-white"></span>
      </a>
      <button
        @click="showMobileMenu = !showMobileMenu"
        class="inline-flex items-center p-2 text-sm rounded-lg md:hidden hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-600"
        aria-expanded="false"
      >
        <span class="sr-only">Open main menu</span>
        <svg class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Desktop Menu -->
      <div class="items-center hidden ml-1 space-x-8 md:flex">
        <a href="https://myciba.org/" class="flex items-center hover:text-ciba-green">
          <img src="/asset/ciba-logo-white.png" class="h-8 mr-3" alt="CIBA Logo" />
          <span class="self-center text-2xl whitespace-nowrap dark:text-white"></span>
        </a>
        <Link :href="route('vue.member.dashboard')" class="font-extralight hover:text-ciba-green hover:underline">HOME</Link>
        <a target="_blank" href="https://myciba.org/about/who-we-are" class="font-extralight hover:text-ciba-green hover:underline">ABOUT</a>
        <Link :href="route('vue.member.pages.rewards')" class="font-extralight hover:text-ciba-green hover:underline">CHANNELS</Link>
        <a href="#" class="font-extralight hover:text-ciba-green hover:underline">SUPPORT</a>
        <a href="https://www.accountingweekly.com/" class="font-extralight hover:text-ciba-green hover:underline">AW</a>
      </div>

      <!-- User Section -->
      <div class="items-center hidden md:flex">
        <a id="cpd-myciba-logo" target="_blank" href="https://cpd.myciba.org/" class="flex mr-6">
          <img src="/asset/cpd-myciba-logo.png" class="h-8 mr-3" alt="CIBA Logo" />
          <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"></span>
        </a>
        
        <!-- Notifications Dropdown -->
        <NotificationDropdown :store="memberNotificationStore" />
        
        <div class="relative" v-click-outside="() => showUserMenu = false">
          <button
            type="button"
            @click="showUserMenu = !showUserMenu"
            class="flex mx-3 text-sm rounded-full md:mr-0 focus:ring-4 focus:ring-black-300 dark:focus:ring-black-600"
          >
            <span class="sr-only">Open user menu</span>
            <svg class="w-[35px] h-[35px] text-black-800 dark:text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.1" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </button>
          
          <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-if="showUserMenu"
              class="absolute right-0 z-50 my-4 text-base list-none bg-white divide-y rounded shadow divide-black-100 dark:bg-black-700 dark:divide-black-600"
              style="min-width: 14rem;"
            >
            <div class="px-4 py-3">
              <span class="block text-sm font-semibold text-black dark:text-white">
                {{ member.first_name }} {{ member.last_name }}
              </span>
              <span class="block text-sm text-black truncate dark:text-black-400">
                {{ member.email }}
              </span>
            </div>
            <ul class="py-1 text-black dark:text-black-400">
              <li>
                <Link :href="route('vue.member.profile.contact-details')" class="block px-4 py-2 text-sm hover:bg-black-100 dark:hover:bg-black-600 dark:text-black-400 dark:hover:text-white">My profile</Link>
              </li>
              <li>
                <Link :href="route('vue.member.profile.change-password')" class="block px-4 py-2 text-sm hover:bg-black-100 dark:hover:bg-black-600 dark:text-black-400 dark:hover:text-white">Account settings</Link>
              </li>
            </ul>
            <ul class="py-1 text-black dark:text-black-400">
              <li>
                <Link :href="route('vue.member.profile.documents')" class="flex items-center px-4 py-2 text-sm hover:bg-black-100 dark:hover:bg-black-600 dark:hover:text-white">
                  <svg class="w-4 h-4 mr-2 text-black-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="m1.56 6.245 8 3.924a1 1 0 0 0 .88 0l8-3.924a1 1 0 0 0 0-1.8l-8-3.925a1 1 0 0 0-.88 0l-8 3.925a1 1 0 0 0 0 1.8Z" />
                    <path d="M18 8.376a1 1 0 0 0-1 1v.163l-7 3.434-7-3.434v-.163a1 1 0 0 0-2 0v.786a1 1 0 0 0 .56.9l8 3.925a1 1 0 0 0 .88 0l8-3.925a1 1 0 0 0 .56-.9v-.786a1 1 0 0 0-1-1Z" />
                    <path d="M17.993 13.191a1 1 0 0 0-1 1v.163l-7 3.435-7-3.435v-.163a1 1 0 1 0-2 0v.787a1 1 0 0 0 .56.9l8 3.925a1 1 0 0 0 .88 0l8-3.925a1 1 0 0 0 .56-.9v-.787a1 1 0 0 0-1-1Z" />
                  </svg>
                  Documents
                </Link>
              </li>
            </ul>
            <ul v-if="impersonatingUser" class="py-1 text-black dark:text-black-400 border-t border-black-200 dark:border-black-600">
              <li>
                <button
                  @click="stopImpersonating"
                  class="flex items-center w-full text-left px-4 py-2 text-sm hover:bg-yellow-100 dark:hover:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400"
                >
                  <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                  </svg>
                  Stop Impersonating
                </button>
              </li>
            </ul>
            <ul class="py-1 text-black dark:text-black-400">
              <li>
                <Link :href="route('vue.member.logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm hover:bg-black-100 dark:hover:bg-black-600 dark:text-black-400 dark:hover:text-white">Sign out</Link>
              </li>
            </ul>
          </div>
        </transition>
        </div>
      </div>
    </div>

        <!-- Mobile Menu -->
    <div v-if="showMobileMenu" class="flex flex-col mt-4 space-y-2 md:hidden">
      <Link :href="route('vue.member.dashboard')" class="block hover:text-ciba-green hover:underline">HOME</Link>
      <a target="_blank" href="https://myciba.org/about/who-we-are" class="block hover:text-ciba-green hover:underline">ABOUT</a>
      <Link :href="route('vue.member.pages.rewards')" class="block hover:text-ciba-green hover:underline">CHANNELS</Link>
      <a href="#" class="block hover:text-ciba-green hover:underline">SUPPORT</a>
      <a href="https://www.accountingweekly.com/" class="block hover:text-ciba-green hover:underline">AW</a>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import NotificationDropdown from '@/Components/Navigation/NotificationDropdown.vue'
import { useMemberNotificationStore } from '@/Stores/memberNotificationStore.js'

const props = defineProps({
  member: {
    type: Object,
    required: true
  }
})

const page = usePage()
const impersonatingUser = computed(() => page.props.impersonating_user)

const showUserMenu = ref(false)
const showMobileMenu = ref(false)

// Initialize member notification store
const memberNotificationStore = useMemberNotificationStore()

onMounted(() => {
  memberNotificationStore.initialize()
  memberNotificationStore.startPolling(30000) // Poll every 30 seconds
})

onBeforeUnmount(() => {
  memberNotificationStore.stopPolling()
})

const stopImpersonating = () => {
  router.post(route('admin-member.stop-impersonating'), {}, {
    preserveScroll: false,
    preserveState: false,
  })
}

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = function(event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.body.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.body.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

<style scoped>
.hover\:text-ciba-green:hover {
  color: #BAF504;
}
</style>

