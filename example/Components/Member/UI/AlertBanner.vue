<template>
  <div
    v-if="show"
    :class="[
      'rounded-md p-4',
      typeClasses,
      className
    ]"
  >
    <div class="flex">
      <div class="flex-shrink-0">
        <!-- Success Icon -->
        <svg v-if="type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-green-400">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
        <!-- Warning Icon -->
        <svg v-else-if="type === 'warning'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-yellow-400">
          <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
        <!-- Danger Icon -->
        <svg v-else-if="type === 'danger'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-red-400">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
        <!-- Info Icon -->
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-blue-400">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
        </svg>
      </div>
      <div class="ml-3 flex-1">
        <h3 v-if="title" :class="['text-sm font-medium', titleColorClass]">
          {{ title }}
        </h3>
        <div :class="['text-sm', contentColorClass, title ? 'mt-2' : '']">
          <slot>
            <p>{{ message }}</p>
          </slot>
        </div>
        <div v-if="$slots.actions" class="mt-4">
          <slot name="actions" />
        </div>
      </div>
      <div v-if="dismissible" class="ml-auto pl-3">
        <div class="-mx-1.5 -my-1.5">
          <button
            @click="dismiss"
            :class="['inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2', buttonColorClass]"
          >
            <span class="sr-only">Dismiss</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'warning', 'danger', 'info'].includes(value)
  },
  title: {
    type: String,
    default: null
  },
  message: {
    type: String,
    default: null
  },
  dismissible: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['dismiss'])

const show = ref(true)

const typeClasses = computed(() => {
  const classes = {
    success: 'bg-green-50 dark:bg-green-900/20',
    warning: 'bg-yellow-50 dark:bg-yellow-900/20',
    danger: 'bg-red-50 dark:bg-red-900/20',
    info: 'bg-blue-50 dark:bg-blue-900/20'
  }
  return classes[props.type]
})

const titleColorClass = computed(() => {
  const classes = {
    success: 'text-green-800 dark:text-green-300',
    warning: 'text-yellow-800 dark:text-yellow-300',
    danger: 'text-red-800 dark:text-red-300',
    info: 'text-blue-800 dark:text-blue-300'
  }
  return classes[props.type]
})

const contentColorClass = computed(() => {
  const classes = {
    success: 'text-green-700 dark:text-green-400',
    warning: 'text-yellow-700 dark:text-yellow-400',
    danger: 'text-red-700 dark:text-red-400',
    info: 'text-blue-700 dark:text-blue-400'
  }
  return classes[props.type]
})

const buttonColorClass = computed(() => {
  const classes = {
    success: 'text-green-500 hover:bg-green-100 dark:hover:bg-green-800 focus:ring-green-600',
    warning: 'text-yellow-500 hover:bg-yellow-100 dark:hover:bg-yellow-800 focus:ring-yellow-600',
    danger: 'text-red-500 hover:bg-red-100 dark:hover:bg-red-800 focus:ring-red-600',
    info: 'text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-800 focus:ring-blue-600'
  }
  return classes[props.type]
})

const dismiss = () => {
  show.value = false
  emit('dismiss')
}
</script>

