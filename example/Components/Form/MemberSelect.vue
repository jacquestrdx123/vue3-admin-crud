<template>
  <SearchableSelect
    :model-value="modelValue"
    :name="name"
    :label="label"
    :placeholder="placeholder"
    :search-placeholder="searchPlaceholder"
    :search-url="computedSearchUrl"
    :search-params="searchParams"
    :required="required"
    :disabled="disabled"
    :column-span="columnSpan"
    :error-messages="errorMessages"
    :hint="hint"
    :searchable="searchable"
    :debounce-ms="debounceMs"
    :no-results-text="noResultsText"
    @update:modelValue="handleUpdate"
    @change="handleChange"
    @search="handleSearch"
  />
</template>

<script setup>
import { computed } from 'vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: null
  },
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Search for a member...'
  },
  searchPlaceholder: {
    type: String,
    default: 'Search by member code or name...'
  },
  searchUrl: {
    type: String,
    default: null
  },
  ajax_url: {
    type: String,
    default: null
  },
  searchParams: {
    type: Object,
    default: () => ({})
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  columnSpan: {
    type: Number,
    default: 12
  },
  errorMessages: {
    type: [String, Array],
    default: () => []
  },
  hint: {
    type: String,
    default: ''
  },
  searchable: {
    type: Boolean,
    default: true
  },
  debounceMs: {
    type: Number,
    default: 300
  },
  noResultsText: {
    type: String,
    default: 'No members found'
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'search'])

const computedSearchUrl = computed(() => {
  return props.searchUrl || props.ajax_url || route('vue.members.search')
})

const handleUpdate = (value) => {
  emit('update:modelValue', value)
}

const handleChange = (option) => {
  emit('change', option)
}

const handleSearch = (results) => {
  emit('search', results)
}
</script>

