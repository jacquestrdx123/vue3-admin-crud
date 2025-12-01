<template>
  <div class="member-form-field" :class="containerClass">
    <label
      v-if="label"
      :for="id"
      class="block text-sm font-medium text-gray-700 mb-2"
      :class="labelClass"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
      <span v-if="optional" class="text-gray-400 ml-1 font-normal">(Optional)</span>
    </label>

    <div class="relative" :class="inputWrapperClass">
      <!-- Prefix Icon/Text -->
      <div v-if="prefixIcon || prefixText" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <component v-if="prefixIcon" :is="prefixIcon" class="h-5 w-5 text-gray-400" />
        <span v-else-if="prefixText" class="text-gray-500 sm:text-sm">{{ prefixText }}</span>
      </div>

      <!-- Input Field -->
      <input
        v-if="type !== 'textarea' && type !== 'select'"
        :id="id"
        :type="type"
        :name="name"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :maxlength="maxlength"
        :autocomplete="autocomplete"
        class="block w-full rounded-lg border-gray-300 shadow-sm focus:shadow-md transition-all duration-200 focus:border-ciba-green focus:ring focus:ring-ciba-green focus:ring-opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
        :class="[
          inputClass,
          prefixIcon || prefixText ? 'pl-10' : '',
          suffixIcon || suffixText ? 'pr-10' : '',
          hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <!-- Textarea -->
      <textarea
        v-else-if="type === 'textarea'"
        :id="id"
        :name="name"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :rows="rows"
        class="block w-full rounded-lg border-gray-300 shadow-sm focus:shadow-md transition-all duration-200 focus:border-ciba-green focus:ring focus:ring-ciba-green focus:ring-opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
        :class="[
          inputClass,
          hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <!-- Select -->
      <select
        v-else-if="type === 'select'"
        :id="id"
        :name="name"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        class="block w-full rounded-lg border-gray-300 shadow-sm focus:shadow-md transition-all duration-200 focus:border-ciba-green focus:ring focus:ring-ciba-green focus:ring-opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
        :class="[
          inputClass,
          hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
        ]"
        @change="handleChange"
        @blur="handleBlur"
        @focus="handleFocus"
      >
        <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>

      <!-- Suffix Icon/Text -->
      <div v-if="suffixIcon || suffixText" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <component v-if="suffixIcon" :is="suffixIcon" class="h-5 w-5 text-gray-400" />
        <span v-else-if="suffixText" class="text-gray-500 sm:text-sm">{{ suffixText }}</span>
      </div>
    </div>

    <!-- Helper Text / Error Message -->
    <p v-if="hasError && errorMessage" class="mt-2 text-sm text-red-600">
      {{ errorMessage }}
    </p>
    <p v-else-if="helperText" class="mt-2 text-sm text-gray-500">
      {{ helperText }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: {
    type: String,
    default: () => `field-${Math.random().toString(36).substr(2, 9)}`
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'number', 'tel', 'url', 'date',
      'datetime-local', 'time', 'textarea', 'select'
    ].includes(value)
  },
  name: {
    type: String,
    default: null
  },
  label: {
    type: String,
    default: null
  },
  placeholder: {
    type: String,
    default: null
  },
  modelValue: {
    type: [String, Number],
    default: null
  },
  required: {
    type: Boolean,
    default: false
  },
  optional: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  },
  helperText: {
    type: String,
    default: null
  },
  prefixIcon: {
    type: [Object, String],
    default: null
  },
  prefixText: {
    type: String,
    default: null
  },
  suffixIcon: {
    type: [Object, String],
    default: null
  },
  suffixText: {
    type: String,
    default: null
  },
  // For number inputs
  min: {
    type: [Number, String],
    default: null
  },
  max: {
    type: [Number, String],
    default: null
  },
  step: {
    type: [Number, String],
    default: null
  },
  maxlength: {
    type: [Number, String],
    default: null
  },
  // For textarea
  rows: {
    type: Number,
    default: 3
  },
  // For select
  options: {
    type: Array,
    default: () => [],
    validator: (options) => {
      return options.every(opt => 
        typeof opt === 'object' && 
        'value' in opt && 
        'label' in opt
      )
    }
  },
  // Styling
  containerClass: {
    type: String,
    default: ''
  },
  labelClass: {
    type: String,
    default: ''
  },
  inputWrapperClass: {
    type: String,
    default: ''
  },
  inputClass: {
    type: String,
    default: ''
  },
  autocomplete: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'change'])

const hasError = computed(() => !!props.error)
const errorMessage = computed(() => {
  if (Array.isArray(props.error)) {
    return props.error[0]
  }
  return props.error
})

function handleInput(event) {
  let value = event.target.value
  
  if (props.type === 'number') {
    value = value === '' ? null : Number(value)
  }
  
  emit('update:modelValue', value)
}

function handleChange(event) {
  let value = event.target.value
  
  if (props.type === 'number') {
    value = value === '' ? null : Number(value)
  }
  
  emit('update:modelValue', value)
  emit('change', value)
}

function handleBlur(event) {
  emit('blur', event)
}

function handleFocus(event) {
  emit('focus', event)
}
</script>

<style scoped>
.focus\:border-ciba-green:focus {
  border-color: #BAF504;
}

.focus\:ring-ciba-green:focus {
  --tw-ring-color: #BAF504;
}
</style>

