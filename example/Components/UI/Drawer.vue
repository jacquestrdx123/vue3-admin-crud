<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-hidden"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <!-- Drawer Panel -->
        <Transition
          enter-active-class="transition ease-out duration-300 transform"
          enter-from-class="translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transition ease-in duration-200 transform"
          leave-from-class="translate-x-0"
          leave-to-class="translate-x-full"
        >
          <div
            v-if="show"
            :class="[
              'fixed right-0 top-0 h-full w-full max-w-md bg-white dark:bg-gray-800 shadow-xl flex flex-col',
              widthClass
            ]"
            @click.stop
          >
            <!-- Header -->
            <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
              <div class="flex items-center justify-between">
                <slot name="header">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ title }}
                  </h3>
                </slot>
                <button
                  v-if="closable"
                  @click="close"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                  aria-label="Close drawer"
                >
                  <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
              <slot />
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: null
  },
  width: {
    type: String,
    default: 'md' // sm, md, lg, xl, 2xl
  },
  closable: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close', 'update:show'])

const widthClass = computed(() => {
  const widths = {
    'sm': 'max-w-sm',
    'md': 'max-w-md',
    'lg': 'max-w-lg',
    'xl': 'max-w-xl',
    '2xl': 'max-w-2xl'
  }
  return widths[props.width] || widths['md']
})

const close = () => {
  emit('close')
  emit('update:show', false)
}

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    close()
  }
}

// Prevent body scroll when drawer is open
watch(() => props.show, (show) => {
  if (show) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
</script>

