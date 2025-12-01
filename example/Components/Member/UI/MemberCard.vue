<template>
  <div
    class="member-card bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden transition-shadow duration-200"
    :class="[
      cardClass,
      { 'hover:shadow-xl': hoverable },
      { 'cursor-pointer': clickable }
    ]"
    @click="handleClick"
  >
    <!-- Card Header -->
    <div
      v-if="$slots.header || title"
      class="card-header px-6 py-4 border-b border-gray-200"
      :class="headerClass"
    >
      <slot name="header">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div v-if="icon" class="flex-shrink-0">
              <component :is="icon" class="h-6 w-6 text-gray-600" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
              <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
            </div>
          </div>
          <div v-if="$slots.actions" class="flex items-center gap-2">
            <slot name="actions" />
          </div>
        </div>
      </slot>
    </div>

    <!-- Card Body -->
    <div
      class="card-body"
      :class="[noPadding ? '' : 'p-6', bodyClass]"
    >
      <slot />
    </div>

    <!-- Card Footer -->
    <div
      v-if="$slots.footer"
      class="card-footer px-6 py-4 bg-gray-50 border-t border-gray-200"
      :class="footerClass"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: null
  },
  subtitle: {
    type: String,
    default: null
  },
  icon: {
    type: [Object, String],
    default: null
  },
  hoverable: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  noPadding: {
    type: Boolean,
    default: false
  },
  cardClass: {
    type: String,
    default: ''
  },
  headerClass: {
    type: String,
    default: ''
  },
  bodyClass: {
    type: String,
    default: ''
  },
  footerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['click'])

function handleClick() {
  if (props.clickable) {
    emit('click')
  }
}
</script>
