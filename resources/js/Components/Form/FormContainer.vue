<template>
  <div :class="containerClasses">
    <slot />
  </div>
</template>

<script setup>
import { computed, useAttrs } from 'vue'

const props = defineProps({
  // Padding options
  padding: {
    type: [Boolean, String],
    default: true,
    validator: (value) => {
      if (typeof value === 'boolean') return true
      return ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
    }
  },
  // Shadow options
  shadow: {
    type: [Boolean, String],
    default: true,
    validator: (value) => {
      if (typeof value === 'boolean') return true
      return ['none', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
    }
  },
  // Background variants
  background: {
    type: String,
    default: 'white',
    validator: (value) => ['white', 'transparent', 'gray', 'gray-50', 'gray-100', 'slate', 'zinc'].includes(value)
  },
  // Rounded corners
  rounded: {
    type: [Boolean, String],
    default: true,
    validator: (value) => {
      if (typeof value === 'boolean') return true
      return ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    }
  },
  // Border options
  border: {
    type: [Boolean, String],
    default: false,
    validator: (value) => {
      if (typeof value === 'boolean') return true
      return ['none', 'thin', 'medium', 'thick'].includes(value)
    }
  },
  // Max width
  maxWidth: {
    type: [String, Boolean],
    default: false,
    validator: (value) => {
      if (typeof value === 'boolean') return true
      return ['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', 'full', 'screen'].includes(value)
    }
  },
  // Custom classes
  class: {
    type: [String, Array, Object],
    default: null
  },
  // Dark mode background override
  darkBackground: {
    type: String,
    default: null,
    validator: (value) => value === null || ['gray-700', 'gray-800', 'gray-900', 'slate-800', 'slate-900', 'zinc-800', 'zinc-900'].includes(value)
  }
})

const attrs = useAttrs()

const containerClasses = computed(() => {
  const classes = []
  
  // Background with dark mode support
  if (props.background === 'white') {
    classes.push(`bg-white ${props.darkBackground || 'dark:bg-gray-800'}`)
  } else if (props.background === 'gray') {
    classes.push(`bg-gray-50 ${props.darkBackground || 'dark:bg-gray-900'}`)
  } else if (props.background === 'gray-50') {
    classes.push(`bg-gray-50 ${props.darkBackground || 'dark:bg-gray-900'}`)
  } else if (props.background === 'gray-100') {
    classes.push(`bg-gray-100 ${props.darkBackground || 'dark:bg-gray-800'}`)
  } else if (props.background === 'slate') {
    classes.push(`bg-slate-50 ${props.darkBackground || 'dark:bg-slate-900'}`)
  } else if (props.background === 'zinc') {
    classes.push(`bg-zinc-50 ${props.darkBackground || 'dark:bg-zinc-900'}`)
  }
  // transparent doesn't add any background class
  
  // Shadow variants
  if (props.shadow === true) {
    classes.push('shadow-sm dark:shadow-gray-900/20')
  } else if (props.shadow === 'sm') {
    classes.push('shadow-sm dark:shadow-gray-900/20')
  } else if (props.shadow === 'md') {
    classes.push('shadow-md dark:shadow-gray-900/30')
  } else if (props.shadow === 'lg') {
    classes.push('shadow-lg dark:shadow-gray-900/40')
  } else if (props.shadow === 'xl') {
    classes.push('shadow-xl dark:shadow-gray-900/50')
  } else if (props.shadow === '2xl') {
    classes.push('shadow-2xl dark:shadow-gray-900/60')
  }
  // shadow === false or 'none' doesn't add shadow
  
  // Rounded corners
  if (props.rounded === true) {
    classes.push('rounded-lg')
  } else if (props.rounded === 'sm') {
    classes.push('rounded-sm')
  } else if (props.rounded === 'md') {
    classes.push('rounded-md')
  } else if (props.rounded === 'lg') {
    classes.push('rounded-lg')
  } else if (props.rounded === 'xl') {
    classes.push('rounded-xl')
  } else if (props.rounded === 'full') {
    classes.push('rounded-full')
  }
  // rounded === false or 'none' doesn't add rounded
  
  // Border
  if (props.border === true) {
    classes.push('border border-gray-200 dark:border-gray-700')
  } else if (props.border === 'thin') {
    classes.push('border border-gray-200 dark:border-gray-700')
  } else if (props.border === 'medium') {
    classes.push('border-2 border-gray-300 dark:border-gray-600')
  } else if (props.border === 'thick') {
    classes.push('border-4 border-gray-400 dark:border-gray-500')
  }
  // border === false or 'none' doesn't add border
  
  // Padding variants
  if (props.padding === true) {
    classes.push('p-6')
  } else if (props.padding === 'xs') {
    classes.push('p-2')
  } else if (props.padding === 'sm') {
    classes.push('p-4')
  } else if (props.padding === 'md') {
    classes.push('p-6')
  } else if (props.padding === 'lg') {
    classes.push('p-8')
  } else if (props.padding === 'xl') {
    classes.push('p-10')
  } else if (props.padding === '2xl') {
    classes.push('p-12')
  }
  // padding === false or 'none' doesn't add padding
  
  // Max width
  if (props.maxWidth === true) {
    classes.push('max-w-7xl mx-auto')
  } else if (props.maxWidth === 'sm') {
    classes.push('max-w-sm mx-auto')
  } else if (props.maxWidth === 'md') {
    classes.push('max-w-md mx-auto')
  } else if (props.maxWidth === 'lg') {
    classes.push('max-w-lg mx-auto')
  } else if (props.maxWidth === 'xl') {
    classes.push('max-w-xl mx-auto')
  } else if (props.maxWidth === '2xl') {
    classes.push('max-w-2xl mx-auto')
  } else if (props.maxWidth === '3xl') {
    classes.push('max-w-3xl mx-auto')
  } else if (props.maxWidth === '4xl') {
    classes.push('max-w-4xl mx-auto')
  } else if (props.maxWidth === '5xl') {
    classes.push('max-w-5xl mx-auto')
  } else if (props.maxWidth === '6xl') {
    classes.push('max-w-6xl mx-auto')
  } else if (props.maxWidth === '7xl') {
    classes.push('max-w-7xl mx-auto')
  } else if (props.maxWidth === 'full') {
    classes.push('max-w-full')
  } else if (props.maxWidth === 'screen') {
    classes.push('max-w-screen-xl mx-auto')
  }
  
  // Custom classes from prop
  if (props.class) {
    if (typeof props.class === 'string') {
      classes.push(props.class)
    } else if (Array.isArray(props.class)) {
      classes.push(...props.class.filter(Boolean))
    } else if (typeof props.class === 'object') {
      Object.entries(props.class).forEach(([key, value]) => {
        if (value) classes.push(key)
      })
    }
  }
  
  // Custom classes from $attrs (for class binding from parent)
  if (attrs.class) {
    if (typeof attrs.class === 'string') {
      classes.push(attrs.class)
    } else if (Array.isArray(attrs.class)) {
      classes.push(...attrs.class.filter(Boolean))
    } else if (typeof attrs.class === 'object') {
      Object.entries(attrs.class).forEach(([key, value]) => {
        if (value) classes.push(key)
      })
    }
  }
  
  return classes.join(' ')
})
</script>

