<template>
  <FormContainer :padding="padding" :shadow="shadow" :background="background" :rounded="rounded" :border="border" :max-width="maxWidth" :class="class">
    <div class="space-y-6">
      <!-- Header -->
      <div v-if="title" class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
          {{ title }}
        </h3>
        <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ description }}
        </p>
      </div>

      <!-- Example Download Section -->
      <div v-if="columns && columns.length > 0" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200">
              Need an example file?
            </h4>
            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
              Download a sample CSV file with the correct format
            </p>
          </div>
          <button
            type="button"
            @click="downloadExample"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600"
          >
            <svg class="inline-block h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download Example
          </button>
        </div>
      </div>

      <!-- File Upload Area -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          {{ uploadLabel }}
          <span v-if="required" class="text-red-500">*</span>
        </label>

        <div
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop"
          :class="[
            'border-2 border-dashed rounded-lg p-8 text-center transition-colors',
            isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600',
            hasError ? 'border-red-300 dark:border-red-600' : '',
            isProcessing ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
          ]"
        >
          <input
            ref="fileInput"
            type="file"
            accept=".csv,text/csv"
            @change="handleFileSelect"
            :disabled="isProcessing"
            class="hidden"
          />

          <!-- Upload Icon -->
          <svg v-if="!isProcessing" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>

          <!-- Processing Spinner -->
          <div v-else class="mx-auto h-12 w-12">
            <svg class="animate-spin h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>

          <div class="mt-4">
            <button
              v-if="!isProcessing"
              type="button"
              @click="$refs.fileInput.click()"
              class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
            >
              Select CSV file
            </button>
            <p v-else class="text-sm text-gray-600 dark:text-gray-400">
              Processing...
            </p>
            <p v-if="!isProcessing" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              or drag and drop here
            </p>
            <p v-if="maxSize" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Max size: {{ formatFileSize(maxSize * 1024) }}
            </p>
          </div>
        </div>

        <!-- Selected File Info -->
        <div v-if="selectedFile && !isProcessing" class="mt-4 flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
          <div class="flex items-center flex-1 min-w-0">
            <svg class="h-8 w-8 text-gray-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3 flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                {{ selectedFile.name }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ formatFileSize(selectedFile.size) }} • {{ parsedRows.length }} rows
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="clearFile"
            class="ml-4 text-red-600 hover:text-red-800 dark:text-red-400"
          >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Column Mapping (if columns are provided) -->
      <div v-if="columns && columns.length > 0 && parsedRows.length > 0" class="space-y-4">
        <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
          <h4 class="text-sm font-medium text-gray-900 dark:text-white">
            Column Mapping
          </h4>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Map CSV columns to your data fields
          </p>
        </div>

        <div class="space-y-3">
          <div
            v-for="column in columns"
            :key="column.key"
            class="flex items-center space-x-4"
          >
            <div class="flex-1 min-w-0">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ column.label || column.key }}
                <span v-if="column.required" class="text-red-500">*</span>
              </label>
              <p v-if="column.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ column.description }}
              </p>
            </div>
            <div class="w-64">
              <select
                v-model="columnMapping[column.key]"
                :class="[
                  'block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-white text-sm',
                  column.required && !columnMapping[column.key] ? 'border-red-300' : ''
                ]"
              >
                <option value="">-- Select CSV Column --</option>
                <option
                  v-for="csvCol in csvColumns"
                  :key="csvCol"
                  :value="csvCol"
                >
                  {{ csvCol }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Preview -->
      <div v-if="parsedRows.length > 0 && (!columns || columns.length === 0 || allColumnsMapped)" class="space-y-4">
        <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
          <h4 class="text-sm font-medium text-gray-900 dark:text-white">
            Preview ({{ parsedRows.length }} rows)
          </h4>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            First {{ Math.min(5, parsedRows.length) }} rows of your data
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th
                  v-for="header in previewHeaders"
                  :key="header"
                  class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="(row, index) in previewRows"
                :key="index"
                class="hover:bg-gray-50 dark:hover:bg-gray-800"
              >
                <td
                  v-for="header in previewHeaders"
                  :key="header"
                  class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                >
                  {{ row[header] || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Error Messages -->
      <div v-if="hasError" class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
              {{ errorMessage }}
            </h3>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div v-if="showActions" class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          v-if="showCancel"
          type="button"
          @click="handleCancel"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
        >
          {{ cancelLabel }}
        </button>
        <button
          type="button"
          @click="handleImport"
          :disabled="!canImport || isProcessing"
          class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ importLabel }}
        </button>
      </div>
    </div>
  </FormContainer>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import FormContainer from './FormContainer.vue'

const props = defineProps({
  // FormContainer props
  padding: {
    type: [Boolean, String],
    default: true
  },
  shadow: {
    type: [Boolean, String],
    default: true
  },
  background: {
    type: String,
    default: 'white'
  },
  rounded: {
    type: [Boolean, String],
    default: true
  },
  border: {
    type: [Boolean, String],
    default: false
  },
  maxWidth: {
    type: [String, Boolean],
    default: false
  },
  class: {
    type: [String, Array, Object],
    default: null
  },
  // Component specific props
  title: {
    type: String,
    default: 'CSV Import'
  },
  description: {
    type: String,
    default: null
  },
  columns: {
    type: Array,
    default: () => []
  },
  required: {
    type: Boolean,
    default: false
  },
  uploadLabel: {
    type: String,
    default: 'CSV File'
  },
  maxSize: {
    type: Number,
    default: 10240 // 10MB in KB
  },
  importLabel: {
    type: String,
    default: 'Import'
  },
  cancelLabel: {
    type: String,
    default: 'Cancel'
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showCancel: {
    type: Boolean,
    default: true
  },
  exampleFileName: {
    type: String,
    default: 'example.csv'
  },
  errorMessages: {
    type: [String, Array],
    default: () => []
  }
})

const emit = defineEmits(['import', 'cancel', 'file-selected', 'error'])

const fileInput = ref(null)
const selectedFile = ref(null)
const isDragging = ref(false)
const isProcessing = ref(false)
const parsedRows = ref([])
const csvColumns = ref([])
const columnMapping = ref({})
const internalError = ref(null)

const hasError = computed(() => {
  return !!internalError.value || (Array.isArray(props.errorMessages) && props.errorMessages.length > 0) || !!props.errorMessages
})

const errorMessage = computed(() => {
  if (internalError.value) return internalError.value
  if (Array.isArray(props.errorMessages)) {
    return props.errorMessages[0]
  }
  return props.errorMessages
})

const allColumnsMapped = computed(() => {
  if (!props.columns || props.columns.length === 0) return true
  return props.columns.every(col => {
    if (col.required && !columnMapping.value[col.key]) {
      return false
    }
    return true
  })
})

const canImport = computed(() => {
  return selectedFile.value && parsedRows.value.length > 0 && allColumnsMapped.value && !isProcessing.value
})

const previewHeaders = computed(() => {
  if (props.columns && props.columns.length > 0) {
    return props.columns.map(col => columnMapping.value[col.key] || col.label || col.key).filter(Boolean)
  }
  return csvColumns.value
})

const previewRows = computed(() => {
  if (!parsedRows.value.length) return []
  
  const rows = parsedRows.value.slice(0, 5)
  
  if (props.columns && props.columns.length > 0) {
    return rows.map(row => {
      const mappedRow = {}
      props.columns.forEach(col => {
        const csvCol = columnMapping.value[col.key]
        if (csvCol && row[csvCol] !== undefined) {
          mappedRow[columnMapping.value[col.key] || col.label || col.key] = row[csvCol]
        }
      })
      return mappedRow
    })
  }
  
  return rows
})

const handleFileSelect = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    processFile(file)
  }
}

const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files?.[0]
  if (file) {
    processFile(file)
  }
}

const processFile = async (file) => {
  internalError.value = null
  
  // Validate file type
  if (!file.name.endsWith('.csv') && file.type !== 'text/csv' && file.type !== 'application/vnd.ms-excel') {
    internalError.value = 'Please upload a valid CSV file'
    emit('error', internalError.value)
    return
  }

  // Validate file size
  if (props.maxSize && file.size > props.maxSize * 1024) {
    internalError.value = `File exceeds maximum size of ${formatFileSize(props.maxSize * 1024)}`
    emit('error', internalError.value)
    return
  }

  selectedFile.value = file
  isProcessing.value = true

  try {
    const text = await readFileAsText(file)
    const parsed = parseCSV(text)
    
    if (parsed.length === 0) {
      throw new Error('CSV file appears to be empty')
    }

    parsedRows.value = parsed
    csvColumns.value = Object.keys(parsed[0])
    
    // Auto-map columns if possible
    if (props.columns && props.columns.length > 0) {
      props.columns.forEach(col => {
        // Try to find matching column by key or label
        const match = csvColumns.value.find(csvCol => {
          const normalizedCsv = csvCol.toLowerCase().replace(/[^a-z0-9]/g, '')
          const normalizedKey = col.key.toLowerCase().replace(/[^a-z0-9]/g, '')
          const normalizedLabel = (col.label || '').toLowerCase().replace(/[^a-z0-9]/g, '')
          return normalizedCsv === normalizedKey || normalizedCsv === normalizedLabel
        })
        if (match && !columnMapping.value[col.key]) {
          columnMapping.value[col.key] = match
        }
      })
    }

    emit('file-selected', {
      file,
      rows: parsedRows.value,
      columns: csvColumns.value,
      mapping: columnMapping.value
    })
  } catch (error) {
    internalError.value = error.message || 'Failed to parse CSV file'
    emit('error', internalError.value)
    parsedRows.value = []
    csvColumns.value = []
  } finally {
    isProcessing.value = false
  }
}

const parseCSV = (text) => {
  const lines = text.split(/\r?\n/).filter(line => line.trim())
  if (lines.length === 0) return []

  // Detect delimiter
  const delimiters = [',', ';', '\t']
  let delimiter = ','
  let maxFields = 0
  
  for (const delim of delimiters) {
    const fieldCount = lines[0].split(delim).length
    if (fieldCount > maxFields) {
      maxFields = fieldCount
      delimiter = delim
    }
  }

  // Parse header
  const headers = lines[0].split(delimiter).map(h => h.trim().replace(/^"|"$/g, ''))
  
  // Parse rows
  const rows = []
  for (let i = 1; i < lines.length; i++) {
    if (!lines[i].trim()) continue
    
    const values = lines[i].split(delimiter).map(v => v.trim().replace(/^"|"$/g, ''))
    const row = {}
    headers.forEach((header, index) => {
      row[header] = values[index] || ''
    })
    rows.push(row)
  }

  return rows
}

const readFileAsText = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (e) => resolve(e.target.result)
    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.readAsText(file, 'UTF-8')
  })
}

const clearFile = () => {
  selectedFile.value = null
  parsedRows.value = []
  csvColumns.value = []
  columnMapping.value = {}
  internalError.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleImport = () => {
  if (!canImport.value) return

  const mappedData = parsedRows.value.map(row => {
    const mapped = {}
    if (props.columns && props.columns.length > 0) {
      props.columns.forEach(col => {
        const csvCol = columnMapping.value[col.key]
        if (csvCol) {
          mapped[col.key] = row[csvCol]
        }
      })
    } else {
      Object.assign(mapped, row)
    }
    return mapped
  })

  emit('import', {
    file: selectedFile.value,
    data: mappedData,
    originalRows: parsedRows.value,
    mapping: columnMapping.value
  })
}

const handleCancel = () => {
  emit('cancel')
}

const downloadExample = () => {
  if (!props.columns || props.columns.length === 0) {
    // Generate example with CSV columns if no columns defined
    const exampleData = [csvColumns.value]
    downloadCSV(exampleData, props.exampleFileName)
    return
  }

  // Generate example with column headers
  const headers = props.columns.map(col => col.label || col.key)
  const exampleRow = props.columns.map(col => {
    if (col.example) return col.example
    if (col.type === 'email') return 'example@email.com'
    if (col.type === 'date') return '2024-01-01'
    if (col.type === 'number') return '123'
    if (col.type === 'boolean') return 'true'
    return `Example ${col.label || col.key}`
  })

  const csvData = [headers, exampleRow]
  downloadCSV(csvData, props.exampleFileName)
}

const downloadCSV = (data, filename) => {
  const csvContent = data.map(row => {
    return row.map(cell => {
      const cellStr = String(cell || '')
      // Escape quotes and wrap in quotes if contains comma, newline, or quote
      if (cellStr.includes(',') || cellStr.includes('\n') || cellStr.includes('"')) {
        return `"${cellStr.replace(/"/g, '""')}"`
      }
      return cellStr
    }).join(',')
  }).join('\n')

  // Add BOM for Excel UTF-8 compatibility
  const BOM = '\uFEFF'
  const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// Watch for column mapping changes
watch(() => columnMapping.value, (newMapping) => {
  if (selectedFile.value && parsedRows.value.length > 0) {
    emit('file-selected', {
      file: selectedFile.value,
      rows: parsedRows.value,
      columns: csvColumns.value,
      mapping: newMapping
    })
  }
}, { deep: true })
</script>

