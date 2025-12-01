<template>
  <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- About -->
        <div>
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
            About CIBA
          </h3>
          <ul class="space-y-2">
            <li>
              <Link href="/vue-member/info/about" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                About Us
              </Link>
            </li>
            <li>
              <Link href="/vue-member/info/membership" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Membership
              </Link>
            </li>
            <li>
              <a href="https://ciba.org.za" target="_blank" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Main Website
              </a>
            </li>
          </ul>
        </div>

        <!-- Resources -->
        <div>
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
            Resources
          </h3>
          <ul class="space-y-2">
            <li>
              <Link :href="route('vue.member.pages.educational-guides')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Educational Guides
              </Link>
            </li>
            <li>
              <Link :href="route('vue.member.pages.practice-tools')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Practice Tools
              </Link>
            </li>
            <li>
              <Link :href="route('vue.member.pages.practice-support')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Practice Support
              </Link>
            </li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
            Support
          </h3>
          <ul class="space-y-2">
            <li>
              <Link :href="route('vue.member.support.tickets')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Support Tickets
              </Link>
            </li>
            <li>
              <a href="mailto:support@ciba.org.za" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Email Support
              </a>
            </li>
            <li>
              <a href="tel:+27123456789" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Call Us
              </a>
            </li>
          </ul>
        </div>

        <!-- Legal -->
        <div>
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
            Legal
          </h3>
          <ul class="space-y-2">
            <li>
              <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Privacy Policy
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Terms of Service
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-member-primary">
                Cookie Policy
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
        <p class="text-sm text-center text-gray-500 dark:text-gray-400">
          © {{ currentYear }} Chartered Institute of Business Accountants (CIBA). All rights reserved.
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.hover\:text-member-primary:hover {
  color: #BAF504;
}
</style>

