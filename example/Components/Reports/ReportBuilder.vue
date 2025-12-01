<template>
  <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
    <!-- Report Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
      <p v-if="description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ description }}
      </p>
    </div>

    <!-- Report Parameters Form -->
    <div class="px-6 py-4">
      <form @submit.prevent="generateReport">
        <div class="grid grid-cols-12 gap-4 mb-6">
          <component
            v-for="field in parameters"
            :key="field.name"
            :is="getFieldComponent(field.type)"
            v-model="formData[field.name]"
            v-bind="field"
            :error-messages="errors[field.name]"
            :form-data="formData"
          />
        </div>

        <!-- Generate Buttons -->
        <div class="flex items-center gap-3">
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="loading" class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Generating...
            </span>
            <span v-else>Generate Report</span>
          </button>

          <button
            v-if="allowPdf"
            type="button"
            @click="generatePdf"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 disabled:opacity-50"
          >
            Export as PDF
          </button>

          <button
            v-if="allowExcel"
            type="button"
            @click="generateExcel"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 disabled:opacity-50"
          >
            Export as Excel
          </button>
        </div>
      </form>
    </div>

    <!-- Report Results -->
    <div v-if="results" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
      <slot name="results" :data="results">
        <BaseDataTable
          v-if="Array.isArray(results)"
          :data="results"
          :columns="resultColumns"
          :searchable="false"
          :paginated="false"
        />
        <div v-else class="text-sm text-gray-900 dark:text-white">
          <pre class="bg-gray-50 dark:bg-gray-900 p-4 rounded overflow-auto">{{ results }}</pre>
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import TextField from '@/Components/Form/TextField.vue'
import SelectField from '@/Components/Form/SelectField.vue'
import DateField from '@/Components/Form/DateField.vue'
import NumberField from '@/Components/Form/NumberField.vue'
import BaseDataTable from '@/Components/Table/BaseDataTable.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: null
  },
  parameters: {
    type: Array,
    default: () => []
  },
  resultColumns: {
    type: Array,
    default: () => []
  },
  generateUrl: {
    type: String,
    required: true
  },
  pdfUrl: {
    type: String,
    default: null
  },
  excelUrl: {
    type: String,
    default: null
  },
  allowPdf: {
    type: Boolean,
    default: true
  },
  allowExcel: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['generated'])

const formData = reactive({})
const errors = ref({})
const loading = ref(false)
const results = ref(null)

// Initialize form data
props.parameters.forEach(param => {
  formData[param.name] = param.default || param.value || null
})

const getFieldComponent = (type) => {
  const components = {
    text: TextField,
    select: SelectField,
    date: DateField,
    number: NumberField
  }
  return components[type] || TextField
}

const generateReport = () => {
  loading.value = true
  errors.value = {}

  router.post(props.generateUrl, formData, {
    preserveScroll: true,
    onSuccess: (page) => {
      results.value = page.props.results
      emit('generated', results.value)
      loading.value = false
    },
    onError: (pageErrors) => {
      errors.value = pageErrors
      loading.value = false
    }
  })
}

const generatePdf = () => {
  if (props.pdfUrl) {
    window.open(`${props.pdfUrl}?${new URLSearchParams(formData).toString()}`, '_blank')
  }
}

const generateExcel = () => {
  if (props.excelUrl) {
    window.location.href = `${props.excelUrl}?${new URLSearchParams(formData).toString()}`
  }
}
</script>

