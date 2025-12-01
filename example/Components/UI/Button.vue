<template>
  <component
    :is="componentType"
    :href="href"
    :type="!href ? type : null"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <svg v-if="loading" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span v-if="icon && !loading" class="mr-2" v-html="icon"></span>
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary' // primary, secondary, danger, success, outline
  },
  size: {
    type: String,
    default: 'md' // sm, md, lg
  },
  type: {
    type: String,
    default: 'button'
  },
  href: {
    type: String,
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    default: null
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const componentType = computed(() => {
  return props.href ? Link : 'button'
})

const buttonClasses = computed(() => {
  const classes = [
    'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
  ]

  // Size
  if (props.size === 'sm') {
    classes.push('px-3 py-1.5 text-sm')
  } else if (props.size === 'lg') {
    classes.push('px-6 py-3 text-base')
  } else {
    classes.push('px-4 py-2 text-sm')
  }

  // Variant
  if (props.variant === 'primary') {
    classes.push('bg-ciba-green text-black hover:bg-ciba-green/90 focus:ring-ciba-green font-semibold')
  } else if (props.variant === 'secondary') {
    classes.push('bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600')
  } else if (props.variant === 'danger') {
    classes.push('bg-red-600 text-white hover:bg-red-700 focus:ring-red-500')
  } else if (props.variant === 'success') {
    classes.push('bg-ciba-green text-black hover:bg-ciba-green/90 focus:ring-ciba-green font-semibold')
  } else if (props.variant === 'outline') {
    classes.push('border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700')
  }

  // State
  if (props.disabled || props.loading) {
    classes.push('opacity-50 cursor-not-allowed')
  }

  // Width
  if (props.fullWidth) {
    classes.push('w-full')
  }

  return classes.join(' ')
})

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

