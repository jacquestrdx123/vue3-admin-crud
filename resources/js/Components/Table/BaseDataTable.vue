<template>
  <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
    <!-- Header: Search, Filters, Actions -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Search -->
        <div class="flex-1 max-w-md">
          <input
            v-if="searchable"
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
          <slot name="header-actions" />
          
          <!-- Filters Button -->
          <button
            v-if="hasAnyFilters"
            @click="showFilterDrawer = true"
            class="relative px-3 py-1.5 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2"
            title="Filters"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>Filters</span>
            <span
              v-if="activeFilterCount > 0"
              class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-semibold bg-indigo-600 text-white"
            >
              {{ activeFilterCount }}
            </span>
          </button>
          
          <!-- Export Button -->
          <button
            v-if="resourceSlug"
            @click="handleExport"
            :disabled="exportLoading"
            class="px-3 py-1.5 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            title="Export to Excel"
          >
            <svg
              v-if="!exportLoading"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <svg
              v-else
              class="animate-spin w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            <span>{{ exportLoading ? 'Exporting...' : 'Export' }}</span>
          </button>

          <!-- Column Settings Button -->
          <button
            v-if="resourceSlug"
            @click="showColumnSettings = !showColumnSettings"
            class="px-3 py-1.5 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
            title="Column Settings"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
          </button>
          
          <!-- Bulk Actions -->
          <div v-if="selectedRows.length > 0 && bulkActions.length > 0" class="flex items-center gap-2">
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ selectedRows.length }} selected
            </span>
            <button
              v-for="action in bulkActions"
              :key="action.name"
              @click="handleBulkAction(action)"
              :class="[
                'px-3 py-1.5 text-sm font-medium rounded-md',
                action.color === 'danger' 
                  ? 'bg-red-600 text-white hover:bg-red-700' 
                  : 'bg-gray-600 text-white hover:bg-gray-700'
              ]"
            >
              {{ action.label }}
            </button>
          </div>
        </div>
        </div>

        <!-- Preset Views -->
        <div v-if="hasPresetViews" class="mt-4 space-y-3">
          <!-- Selected Presets Badges -->
          <div v-if="selectedPresetsByGroup && selectedPresetsByGroup.length > 0" class="flex flex-wrap gap-2">
            <template v-for="group in selectedPresetsByGroup" :key="group.name">
              <div
                v-for="preset in group.selectedPresets"
                :key="preset.key"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 rounded-full text-sm font-medium border border-indigo-200 dark:border-indigo-800 shadow-sm hover:shadow-md transition-all duration-200"
              >
                <span>{{ preset.label }}</span>
                <button
                  type="button"
                  @click.stop="removePreset(preset.key)"
                  class="ml-0.5 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                  :aria-label="`Remove ${preset.label} preset`"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </template>
          </div>

          <!-- Preset View Dropdowns -->
          <div class="flex flex-wrap gap-2">
            <div
              v-for="group in presetViews"
              :key="group.name"
              class="relative"
            >
              <!-- Dropdown Button -->
              <button
                type="button"
                data-group-button
                @click.stop="toggleGroupDropdown(group.name)"
                :class="[
                  'px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md',
                  openGroups[group.name]
                    ? 'bg-indigo-600 border-indigo-600 text-white shadow-md'
                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700'
                ]"
              >
                <span>{{ group.label }}</span>
                <span
                  v-if="getSelectedCountForGroup(group) > 0"
                  :class="[
                    'inline-flex items-center justify-center min-w-[1.5rem] h-5 px-1.5 rounded-full text-xs font-semibold',
                    openGroups[group.name]
                      ? 'bg-white/20 text-white'
                      : 'bg-indigo-600 text-white'
                  ]"
                >
                  {{ getSelectedCountForGroup(group) }}
                </span>
                <svg
                  :class="[
                    'w-4 h-4 transition-transform duration-200',
                    openGroups[group.name] ? 'rotate-180' : ''
                  ]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-1"
              >
                <div
                  v-if="openGroups[group.name]"
                  :data-group-name="group.name"
                  class="absolute top-full left-0 mt-2 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto backdrop-blur-sm"
                >
                  <div class="p-3 flex flex-col gap-2" @click.stop>
                    <button
                      v-for="preset in group.presets"
                      :key="preset.key"
                      type="button"
                      @click="togglePresetSelection(preset.key)"
                      :class="[
                        'px-4 py-2.5 text-sm font-medium rounded-full border transition-all duration-200 cursor-pointer text-left flex items-center justify-between group',
                        isPresetSelected(preset.key)
                          ? 'bg-indigo-600 border-indigo-600 text-white shadow-md hover:bg-indigo-700 hover:shadow-lg'
                          : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-400 dark:hover:border-gray-500 hover:shadow-sm'
                      ]"
                    >
                      <span>{{ preset.label }}</span>
                      <svg
                        v-if="isPresetSelected(preset.key)"
                        class="w-4 h-4 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </div>

      <!-- Column Settings Modal -->
      <div
        v-if="showColumnSettings && resourceSlug"
        class="mt-4 p-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Column Settings</h3>
          <button
            @click="showColumnSettings = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="space-y-2 max-h-96 overflow-y-auto">
          <div
            v-for="(column, index) in allColumns"
            :key="column.key"
            :draggable="true"
            @dragstart="handleDragStart(index, $event)"
            @dragover.prevent="handleDragOver(index, $event)"
            @drop="handleDrop(index, $event)"
            @dragend="handleDragEnd"
            :class="[
              'flex items-center gap-3 p-2 rounded border cursor-move',
              isDraggingColumn && draggedColumnIndex === index
                ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <!-- Drag Handle -->
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
            </svg>
            
            <!-- Checkbox -->
            <input
              type="checkbox"
              :checked="!isColumnHidden(column.key)"
              @change="toggleColumnVisibility(column.key)"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            
            <!-- Column Label -->
            <span class="flex-1 text-sm text-gray-900 dark:text-gray-100">{{ column.title }}</span>
          </div>
        </div>
        
        <div class="mt-4 flex gap-2">
          <button
            @click="saveColumnPreferences"
            class="px-4 py-2 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700"
          >
            Save
          </button>
          <button
            @click="resetColumnPreferences"
            class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Reset to Defaults
          </button>
        </div>
      </div>

      <!-- Filter Drawer -->
      <Drawer
        :show="showFilterDrawer"
        title="Filters"
        width="md"
        @close="showFilterDrawer = false"
        @update:show="showFilterDrawer = $event"
      >
        <div class="space-y-6">
          <!-- Standard Filters -->
          <div v-if="filters.length > 0" class="space-y-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Standard Filters</h4>
            <div class="space-y-4">
              <component
                v-for="(filter, index) in filters"
                v-show="shouldShowFilter(filter)"
                :key="`${filter.name}-${index}`"
                :is="getFilterComponent(filter.type || 'select', filter)"
                v-model="filterValues[filter.name]"
                :name="filter.name"
                :label="filter.label"
                :options="filter.options || {}"
                :fields="filter.fields || {}"
                :searchable="filter.searchable !== undefined ? filter.searchable : true"
                @update:modelValue="handleFilterChange(filter)"
              />
            </div>
          </div>

          <!-- Custom Filters -->
          <div v-if="customFilters.length > 0" class="space-y-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Custom Filters</h4>
            <div class="space-y-4">
              <component
                v-for="(customFilter, index) in customFilters"
                :key="customFilter.name || index"
                :is="resolveCustomFilterComponent(customFilter.component)"
                v-model="customFilterValues[getCustomFilterKey(customFilter, index)]"
                :name="customFilter.name || getCustomFilterKey(customFilter, index)"
                :label="customFilter.label"
                :fields="customFilter.fields || {}"
                v-bind="customFilter.props || {}"
                @update:modelValue="value => handleCustomFilterChange(customFilter, index, value)"
              />
            </div>
          </div>
        </div>

        <template #footer>
          <div class="flex items-center justify-between gap-3">
            <button
              @click="clearAllFilters"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              Clear All
            </button>
            <button
              @click="applyFiltersAndClose"
              class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition-colors"
            >
              Apply Filters
            </button>
          </div>
        </template>
      </Drawer>
    </div>

    <!-- Raw SQL Display (Dev Only) -->
    <div v-if="rawSql" class="border-b border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20">
      <button
        @click="showRawSql = !showRawSql"
        class="w-full p-4 flex items-center justify-between gap-4 hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors"
        :title="showRawSql ? 'Hide SQL' : 'Show SQL'"
      >
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Raw SQL Query (Dev Mode)</h3>
        </div>
        <svg
          :class="[
            'w-5 h-5 text-yellow-600 dark:text-yellow-400 transition-transform',
            showRawSql ? 'rotate-180' : ''
          ]"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <div v-if="showRawSql" class="px-4 pb-4">
        <pre class="text-xs font-mono text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 p-3 rounded border border-yellow-300 dark:border-yellow-700 overflow-x-auto whitespace-pre-wrap break-words">{{ rawSql }}</pre>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <!-- Bulk Selection -->
            <th v-if="bulkActions.length > 0" class="w-12 px-4 py-3">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
            </th>

            <!-- Column Headers -->
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              :class="column.sortable ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800' : ''"
              @click="column.sortable ? toggleSort(column.key) : null"
            >
              <div class="flex items-center gap-2">
                <span>{{ column.title }}</span>
                <span v-if="column.sortable && sortColumn === column.key">
                  <svg v-if="sortDirection === 'asc'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 10l5-5 5 5H5z"/>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M15 10l-5 5-5-5h10z"/>
                  </svg>
                </span>
              </div>
            </th>

            <!-- Actions Column -->
            <th v-if="actions.length > 0" class="sticky right-0 z-10 px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700">
              Actions
            </th>
          </tr>
        </thead>

        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <!-- Loading State -->
          <tr v-if="isLoading">
            <td :colspan="columnCount" class="px-6 py-12">
              <div class="flex justify-center">
                <FunkyLoader />
              </div>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-else-if="processedData.length === 0">
            <td :colspan="columnCount" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
              No records found
            </td>
          </tr>

          <!-- Data Rows -->
          <tr
            v-else
            v-for="row in paginatedData"
            :key="getRowKey(row)"
            class="group hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <!-- Bulk Selection -->
            <td v-if="bulkActions.length > 0" class="px-4 py-4">
              <input
                type="checkbox"
                :checked="isRowSelected(row)"
                @change="toggleRowSelection(row)"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
            </td>

            <!-- Column Cells -->
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100"
              :class="column.type === 'json' ? '' : 'whitespace-nowrap'"
            >
              <!-- Badge Column -->
              <span
                v-if="column.type === 'badge'"
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  getBadgeColor(getNestedValue(row, column.key), column)
                ]"
              >
                {{ getNestedValue(row, column.key) ?? '-' }}
              </span>
              
              <!-- Boolean Column -->
              <span v-else-if="column.type === 'boolean'">
                {{ getNestedValue(row, column.key) ? (column.trueLabel || 'Yes') : (column.falseLabel || 'No') }}
              </span>
              
              <!-- Date Column -->
              <span v-else-if="column.type === 'date'">
                {{ formatDate(getNestedValue(row, column.key), column.format || 'date') }}
              </span>
              
              <!-- Money Column -->
              <span v-else-if="column.type === 'money'">
                {{ formatMoney(getNestedValue(row, column.key), column.currency || 'R', column.decimals || 2) }}
              </span>
              
              <!-- Link Column -->
              <span v-else-if="column.type === 'link'">
                <template v-if="getNestedValue(row, column.key)?.has_file">
                  <a
                    :href="getNestedValue(row, column.key)?.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 underline"
                  >
                    {{ getNestedValue(row, column.key)?.label || 'View' }}
                  </a>
                </template>
                <template v-else>
                  <span class="text-gray-400 dark:text-gray-500">
                    {{ getNestedValue(row, column.key)?.label || 'Not Available' }}
                  </span>
                </template>
              </span>
              
              <!-- JSON Column -->
              <JsonColumn
                v-else-if="column.type === 'json'"
                :value="getNestedValue(row, column.key)"
                :row="row"
                :collapsed="column.collapsed !== false"
                :max-depth="column.maxDepth"
                :character-limit="column.characterLimit"
              />
              
              <!-- Text Column (default) -->
              <span v-else>{{ formatCellValue(row, column) }}</span>
            </td>

            <!-- Actions Column -->
            <td v-if="actions.length > 0" :class="[
              'sticky right-0 px-6 py-4 whitespace-nowrap text-right text-sm font-medium bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 group-hover:bg-gray-50 dark:group-hover:bg-gray-700',
              openMenuRowKey === getRowKey(row) ? 'z-[100]' : 'z-10'
            ]">
              <div class="relative flex justify-end">
                <button
                  @click.stop="toggleActionMenu(getRowKey(row), $event)"
                  class="p-2 rounded-md bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                  :title="`Actions for row ${getRowKey(row)}`"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
                
                <!-- Actions Dropdown Menu -->
                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-if="openMenuRowKey === getRowKey(row)"
                    data-action-menu
                    :class="[
                      'absolute right-0 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-[100]',
                      menuPosition === 'top' ? 'bottom-full mb-1 origin-bottom-right' : 'top-full mt-1'
                    ]"
                    @click.stop
                  >
                    <div class="py-1 flex flex-col">
                      <button
                        v-for="action in actions"
                        :key="action.name"
                        @click="handleActionClick(action, row)"
                        :class="[
                          'block w-full text-left px-4 py-2 text-sm transition-all duration-150',
                          'border border-transparent rounded-md mx-1',
                          'shadow-sm hover:shadow-md active:shadow-inner',
                          'hover:scale-[1.02] active:scale-[0.98]',
                          action.color === 'danger'
                            ? 'text-red-600 hover:bg-red-50 hover:text-red-900 hover:border-red-200 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:border-red-800'
                            : 'text-gray-700 hover:bg-gray-100 hover:border-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:border-gray-600'
                        ]"
                      >
                        {{ action.label }}
                      </button>
                    </div>
                  </div>
                </transition>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="paginated && !isLoading && processedData.length > 0" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, processedData.length) }} of {{ processedData.length }} results
        </div>
        
        <div class="flex gap-2">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Previous
          </button>

          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-1 text-sm border rounded-md',
              page === currentPage 
                ? 'bg-indigo-600 text-white border-indigo-600' 
                : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, getCurrentInstance, nextTick, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import SelectFilter from './Filters/SelectFilter.vue'
import BooleanFilter from './Filters/BooleanFilter.vue'
import DependentSelectFilter from './Filters/DependentSelectFilter.vue'
import DateFilter from './Filters/DateFilter.vue'
import SubscribableTypeFilter from './Filters/SubscribableTypeFilter.vue'
import JsonColumn from './Columns/JsonColumn.vue'
import FunkyLoader from '../UI/FunkyLoader.vue'
import Drawer from '../UI/Drawer.vue'

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  actions: {
    type: Array,
    default: () => []
  },
  bulkActions: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Array,
    default: () => []
  },
  customFilters: {
    type: Array,
    default: () => []
  },
  presetViews: {
    type: Array,
    default: () => []
  },
  activePreset: {
    type: String,
    default: null
  },
  activePresets: {
    type: Array,
    default: () => []
  },
  searchable: {
    type: Boolean,
    default: true
  },
  paginated: {
    type: Boolean,
    default: true
  },
  perPage: {
    type: Number,
    default: 15
  },
  loading: {
    type: Boolean,
    default: false
  },
  rowKey: {
    type: String,
    default: 'id'
  },
  initialFilterValues: {
    type: Object,
    default: () => ({})
  },
  serverSide: {
    type: Boolean,
    default: false
  },
  resourceSlug: {
    type: String,
    default: null
  },
  allColumns: {
    type: Array,
    default: () => []
  },
  rawSql: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['action', 'bulk-action', 'sort', 'filter', 'search'])

// State
const searchQuery = ref('')
const sortColumn = ref(null)
const sortDirection = ref('asc')
const currentPage = ref(1)
const selectedRows = ref([])
const filterValues = ref({})
const customFilterValues = ref({})
const openMenuRowKey = ref(null)
const menuPosition = ref('bottom') // 'bottom' or 'top'
const selectedPresetKeys = ref(
  props.activePresets && props.activePresets.length > 0
    ? [...props.activePresets]
    : props.activePreset
      ? [props.activePreset]
      : []
)
const openGroups = ref({})
const columnPreferences = ref(null)
const showColumnSettings = ref(false)
const showFilterDrawer = ref(false)
const showRawSql = ref(true) // Show SQL by default in dev mode
const exportLoading = ref(false)
const isDraggingColumn = ref(false)
const draggedColumnIndex = ref(null)
const allColumns = ref([]) // Store all columns including hidden ones
const instance = getCurrentInstance()
const hasFilterListener = computed(() => Boolean(instance?.vnode?.props?.onFilter))
const suppressSearchEmit = ref(false)
const page = usePage()
const serverFilterValues = computed(() => page?.props?.filterValues ?? {})
const csrfToken = computed(() => page?.props?.csrf_token ?? '')
const customFilterComponentModules = import.meta.glob('./Filters/**/*.vue', { eager: true })

const resolveInitialFilterValue = (filter) => {
  const name = filter?.name

  if (name && props.initialFilterValues[name] !== undefined) {
    return props.initialFilterValues[name]
  }

  if (name && serverFilterValues.value[name] !== undefined) {
    return serverFilterValues.value[name]
  }

  if (filter?.default !== undefined) {
    return filter.default
  }

  return null
}

const getCustomFilterKey = (filter, index) => {
  return filter?.name ?? filter?.key ?? `${filter?.component || 'custom-filter'}-${index}`
}

const getCustomFilterOutputs = (filter) => {
  if (Array.isArray(filter?.outputs) && filter.outputs.length > 0) {
    return filter.outputs
  }

  if (filter?.values && typeof filter.values === 'object') {
    return Object.keys(filter.values).map(name => ({
      name,
      default: null,
      value: filter.values[name]
    }))
  }

  return []
}

const resolveOutputInitialValue = (filter, output) => {
  const name = output?.name

  if (!name) {
    return null
  }

  if (props.initialFilterValues[name] !== undefined) {
    return props.initialFilterValues[name]
  }

  if (serverFilterValues.value[name] !== undefined) {
    return serverFilterValues.value[name]
  }

  if (output?.value !== undefined) {
    return output.value
  }

  if (filter?.values && filter.values[name] !== undefined) {
    return filter.values[name]
  }

  if (output?.default !== undefined) {
    return output.default
  }

  return null
}

const initializeFilterValues = () => {
  const values = {}
  const customValues = {}

  props.filters.forEach(filter => {
    if (!filter?.name) {
      return
    }
    values[filter.name] = resolveInitialFilterValue(filter)
  })

  props.customFilters.forEach((filter, index) => {
    const key = getCustomFilterKey(filter, index)
    const outputs = getCustomFilterOutputs(filter)
    const filterValue = {}

    outputs.forEach(output => {
      if (!output?.name) {
        return
      }

      const resolved = resolveOutputInitialValue(filter, output)
      filterValue[output.name] = resolved
      values[output.name] = resolved
    })

    customValues[key] = filterValue
  })

  filterValues.value = values
  customFilterValues.value = customValues
}

initializeFilterValues()

watch(
  () => ({
    filters: props.filters,
    customFilters: props.customFilters,
    values: serverFilterValues.value,
  }),
  () => {
    initializeFilterValues()
  },
  { deep: true }
)

// Computed
const columnCount = computed(() => {
  let count = props.columns.length
  if (props.bulkActions.length > 0) count++
  if (props.actions.length > 0) count++
  return count
})

const internalLoading = ref(false)

const isLoading = computed(() => props.loading || internalLoading.value)

const hasAnyFilters = computed(() => props.filters.length > 0 || props.customFilters.length > 0)
const hasPresetViews = computed(() => {
  if (!props.presetViews || props.presetViews.length === 0) {
    return false
  }
  // Check if presetViews is grouped (has presets property) or flat
  return props.presetViews.some(group => group.presets && group.presets.length > 0) || props.presetViews.length > 0
})

const selectedPresetsByGroup = computed(() => {
  if (!props.presetViews || selectedPresetKeys.value.length === 0) {
    return []
  }

  return props.presetViews
    .map(group => {
      if (!group.presets || group.presets.length === 0) {
        return null
      }

      const selectedPresets = group.presets.filter(preset =>
        selectedPresetKeys.value.includes(preset.key)
      )

      if (selectedPresets.length === 0) {
        return null
      }

      return {
        name: group.name,
        label: group.label,
        selectedPresets
      }
    })
    .filter(Boolean)
})

const getSelectedCountForGroup = (group) => {
  if (!group.presets || group.presets.length === 0) {
    return 0
  }
  return group.presets.filter(preset => selectedPresetKeys.value.includes(preset.key)).length
}

const activeFilterCount = computed(() => {
  let count = 0
  
  // Count standard filters
  Object.entries(filterValues.value).forEach(([key, value]) => {
    const hasValue = value !== null && value !== '' && value !== undefined && (!(Array.isArray(value)) || value.length > 0)
    if (hasValue) {
      const definition = filterDefinitions.value[key]
      if (definition?.type !== 'custom_output') {
        count++
      }
    }
  })
  
  // Count custom filter outputs
  Object.entries(customFilterValues.value).forEach(([key, value]) => {
    if (value && typeof value === 'object') {
      const hasAnyValue = Object.values(value).some(v => v !== null && v !== '' && v !== undefined && (!(Array.isArray(v)) || v.length > 0))
      if (hasAnyValue) {
        count++
      }
    }
  })
  
  return count
})

const filterDefinitions = computed(() => {
  const map = {}

  props.filters.forEach(filter => {
    if (!filter?.name) {
      return
    }

    map[filter.name] = filter
  })

  props.customFilters.forEach(filter => {
    const outputs = getCustomFilterOutputs(filter)

    outputs.forEach(output => {
      if (!output?.name) {
        return
      }

      map[output.name] = {
        ...output,
        type: 'custom_output',
        parent: filter
      }
    })
  })

  return map
})

const processedData = computed(() => {
  let result = [...props.data]

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(row => {
      return props.columns.some(column => {
        const value = getNestedValue(row, column.key)
        return value && String(value).toLowerCase().includes(query)
      })
    })
  }

  // Apply filters (skip if server-side filtering is enabled)
  if (!props.serverSide) {
    Object.entries(filterValues.value).forEach(([key, value]) => {
      const hasValue = value !== null && value !== '' && value !== undefined && (!(Array.isArray(value)) || value.length > 0)

      if (!hasValue) {
        return
      }

      const definition = filterDefinitions.value[key]

      if (definition?.type === 'custom_output') {
        return
      }

      result = result.filter(row => {
        const rowValue = getNestedValue(row, key)
        if (Array.isArray(value)) {
          return value.includes(rowValue)
        }
        return rowValue == value
      })
    })
  }

  // Apply sorting
  if (sortColumn.value) {
    result.sort((a, b) => {
      const aVal = getNestedValue(a, sortColumn.value)
      const bVal = getNestedValue(b, sortColumn.value)
      
      if (aVal === bVal) return 0
      
      const comparison = aVal > bVal ? 1 : -1
      return sortDirection.value === 'asc' ? comparison : -comparison
    })
  }

  return result
})

const totalPages = computed(() => {
  if (!props.paginated) return 1
  return Math.ceil(processedData.value.length / props.perPage)
})

const paginatedData = computed(() => {
  if (!props.paginated) return processedData.value
  
  // Ensure current page doesn't exceed total pages
  if (currentPage.value > totalPages.value && totalPages.value > 0) {
    currentPage.value = 1
  }
  
  const start = (currentPage.value - 1) * props.perPage
  const end = start + props.perPage
  return processedData.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  
  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const isAllSelected = computed(() => {
  return paginatedData.value.length > 0 && 
         paginatedData.value.every(row => isRowSelected(row))
})

const buildFilterPayload = (pageOverride = null) => {
  const payload = { ...filterValues.value }
  payload.search = searchQuery.value ?? ''
  
  // Support multiple presets
  if (selectedPresetKeys.value.length > 0) {
    payload.presets = selectedPresetKeys.value
  } else {
    // Backward compatibility: remove presets if empty
    delete payload.presets
  }

  if (pageOverride !== null) {
    payload.page = pageOverride
  }

  return payload
}

const triggerFilterEvent = (pageOverride = 1) => {
  if (!hasFilterListener.value) {
    return
  }

  internalLoading.value = true
  emit('filter', buildFilterPayload(pageOverride))
}

const extractSearchParam = (url) => {
  if (!url || typeof url !== 'string') {
    return ''
  }

  const queryString = url.includes('?') ? url.split('?')[1] : ''

  if (!queryString) {
    return ''
  }

  try {
    const params = new URLSearchParams(queryString)
    return params.get('search') ?? ''
  } catch (error) {
    return ''
  }
}

const syncSearchFromUrl = (url) => {
  const urlSearch = extractSearchParam(url)

  suppressSearchEmit.value = true
  searchQuery.value = urlSearch ?? ''

  nextTick(() => {
    suppressSearchEmit.value = false
  })
}

// Methods
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((curr, key) => curr?.[key], obj)
}

const formatCellValue = (row, column) => {
  let value = getNestedValue(row, column.key)
  
  if (column.format && typeof column.format === 'function') {
    return column.format(value, row)
  }
  
  return value ?? '-'
}

const getBadgeColor = (value, column) => {
  if (!value) return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  
  const colors = column.colors || {}
  const colorName = colors[value] || column.defaultColor || 'gray'
  
  const colorMap = {
    gray: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    indigo: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    pink: 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
  }
  
  return colorMap[colorName] || colorMap.gray
}

const formatDate = (value, format) => {
  if (!value) return '-'
  
  const date = new Date(value)
  if (isNaN(date.getTime())) return value
  
  switch (format) {
    case 'datetime':
      return date.toLocaleString()
    case 'time':
      return date.toLocaleTimeString()
    case 'relative':
      return getRelativeTime(date)
    case 'date':
    default:
      return date.toLocaleDateString()
  }
}

const getRelativeTime = (date) => {
  const now = new Date()
  const diffMs = now - date
  const diffSecs = Math.floor(diffMs / 1000)
  const diffMins = Math.floor(diffSecs / 60)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)
  
  if (diffDays > 7) {
    return date.toLocaleDateString()
  } else if (diffDays > 0) {
    return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`
  } else if (diffHours > 0) {
    return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`
  } else if (diffMins > 0) {
    return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`
  } else {
    return 'Just now'
  }
}

const formatMoney = (value, currency, decimals) => {
  if (value === null || value === undefined) return '-'
  
  const num = parseFloat(value)
  if (isNaN(num)) return value
  
  return `${currency}${num.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`
}

const toggleSort = (columnKey) => {
  if (sortColumn.value === columnKey) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = columnKey
    sortDirection.value = 'asc'
  }
  internalLoading.value = true
  emit('sort', { column: columnKey, direction: sortDirection.value })
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const getRowKey = (row) => {
  return getNestedValue(row, props.rowKey)
}

const isRowSelected = (row) => {
  const key = getRowKey(row)
  return selectedRows.value.includes(key)
}

const toggleRowSelection = (row) => {
  const key = getRowKey(row)
  const index = selectedRows.value.indexOf(key)
  
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(key)
  }
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedRows.value = selectedRows.value.filter(key => 
      !paginatedData.value.some(row => getRowKey(row) === key)
    )
  } else {
    const pageKeys = paginatedData.value.map(row => getRowKey(row))
    selectedRows.value = [...new Set([...selectedRows.value, ...pageKeys])]
  }
}

const handleAction = (action, row) => {
  emit('action', { action: action.name, row })
}

const toggleActionMenu = (rowKey, event) => {
  if (openMenuRowKey.value === rowKey) {
    openMenuRowKey.value = null
    menuPosition.value = 'bottom' // Reset to default
  } else {
    openMenuRowKey.value = rowKey
    
    // Calculate if menu should open upward
    nextTick(() => {
      if (event?.target) {
        const buttonRect = event.target.getBoundingClientRect()
        // Calculate approximate menu height based on number of actions
        const itemHeight = 40 // Approximate height per menu item
        const menuPadding = 8 // Top and bottom padding
        const menuHeight = (props.actions.length * itemHeight) + menuPadding
        const spaceBelow = window.innerHeight - buttonRect.bottom
        const spaceAbove = buttonRect.top
        
        // If not enough space below but enough space above, flip upward
        if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
          menuPosition.value = 'top'
        } else {
          menuPosition.value = 'bottom'
        }
      }
    })
  }
}

const handleActionClick = (action, row) => {
  openMenuRowKey.value = null
  handleAction(action, row)
}

const handleBulkAction = (action) => {
  const rows = props.data.filter(row => selectedRows.value.includes(getRowKey(row)))
  emit('bulk-action', { action: action.name, rows })
}

const handleExport = () => {
  if (!props.resourceSlug || exportLoading.value) {
    return
  }

  exportLoading.value = true

  // Build filter payload with current filters, search, sort, and presets
  const payload = buildFilterPayload()

  // Make POST request to export endpoint
  router.post(route(`vue.${props.resourceSlug}.export`), payload, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // Show success notification if available
      if (window.$toast) {
        window.$toast.success('Export queued successfully. You will be notified when ready.')
      }
      exportLoading.value = false
    },
    onError: (errors) => {
      // Show error notification if available
      if (window.$toast) {
        window.$toast.error(errors?.message || 'Export failed. Please try again.')
      }
      exportLoading.value = false
    },
    onFinish: () => {
      exportLoading.value = false
    }
  })
}

const shouldShowFilter = (filter) => {
  // If filter has showIf condition, check if it matches
  if (filter.showIf && filter.showIfValue !== undefined) {
    return filterValues.value[filter.showIf] === filter.showIfValue
  }
  // Show by default
  return true
}

const handleFilterChange = (filter) => {
  // If this filter controls visibility of other filters, clear their values
  const dependentFilters = props.filters.filter(f => f.showIf === filter.name)
  if (dependentFilters.length > 0) {
    dependentFilters.forEach(f => {
      if (!shouldShowFilter(f)) {
        filterValues.value[f.name] = null
      }
    })
  }
  // Only auto-apply if drawer is not open (backward compatibility)
  if (!showFilterDrawer.value) {
    applyFilters()
  }
}

const handleCustomFilterChange = (filter, index, value) => {
  const key = getCustomFilterKey(filter, index)
  customFilterValues.value[key] = value || {}

  const outputs = getCustomFilterOutputs(filter)
  outputs.forEach(output => {
    if (!output?.name) {
      return
    }

    filterValues.value[output.name] = value?.[output.name] ?? null
  })

  // Only auto-apply if drawer is not open (backward compatibility)
  if (!showFilterDrawer.value) {
    applyFilters()
  }
}

const applyFilters = () => {
  currentPage.value = 1
  triggerFilterEvent(1)
}

const clearAllFilters = () => {
  // Clear standard filters
  Object.keys(filterValues.value).forEach(key => {
    filterValues.value[key] = null
  })
  
  // Clear custom filters
  Object.keys(customFilterValues.value).forEach(key => {
    customFilterValues.value[key] = {}
  })
  
  // Reset to initial values
  initializeFilterValues()
  
  // Apply cleared filters
  applyFilters()
}

const applyFiltersAndClose = () => {
  applyFilters()
  showFilterDrawer.value = false
}

const isPresetSelected = (key) => {
  return selectedPresetKeys.value.includes(key)
}

const togglePresetSelection = (key) => {
  const index = selectedPresetKeys.value.indexOf(key)
  
  if (index > -1) {
    // Remove if already selected
    selectedPresetKeys.value.splice(index, 1)
  } else {
    // Add if not selected
    selectedPresetKeys.value.push(key)
  }
  
  currentPage.value = 1
  triggerFilterEvent(1)
}

const removePreset = (key) => {
  const index = selectedPresetKeys.value.indexOf(key)
  
  if (index > -1) {
    selectedPresetKeys.value.splice(index, 1)
    currentPage.value = 1
    triggerFilterEvent(1)
  }
}

const toggleGroupDropdown = (groupName) => {
  openGroups.value[groupName] = !openGroups.value[groupName]
  
  // Close other open dropdowns
  Object.keys(openGroups.value).forEach(key => {
    if (key !== groupName) {
      openGroups.value[key] = false
    }
  })
}

const closeGroupDropdown = (groupName) => {
  openGroups.value[groupName] = false
}

// Handle click outside to close dropdowns
const handleClickOutside = (event) => {
  const target = event.target
  const isDropdownButton = target.closest('[data-group-button]')
  const isDropdownMenu = target.closest('[data-group-name]')
  const isActionMenuButton = target.closest('button[title*="Actions for row"]')
  const isActionMenu = target.closest('[data-action-menu]')
  
  if (!isDropdownButton && !isDropdownMenu) {
    // Close all open dropdowns
    Object.keys(openGroups.value).forEach(key => {
      openGroups.value[key] = false
    })
  }
  
  if (!isActionMenuButton && !isActionMenu) {
    // Close action menu
    openMenuRowKey.value = null
    menuPosition.value = 'bottom' // Reset to default
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Column Preferences Methods
const loadColumnPreferences = async () => {
  if (!props.resourceSlug) {
    return
  }

  try {
    const response = await fetch(`/api/user-column-preferences/${props.resourceSlug}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken.value,
      },
      credentials: 'same-origin',
    })

    if (response.ok) {
      const data = await response.json()
      columnPreferences.value = data.preferences
      initializeAllColumns()
    }
  } catch (error) {
    console.error('Failed to load column preferences:', error)
  }
}

const saveColumnPreferences = async () => {
  if (!props.resourceSlug) {
    return
  }

  const order = allColumns.value.map(col => col.key)
  const hidden = allColumns.value
    .filter(col => isColumnHidden(col.key))
    .map(col => col.key)

  try {
    const response = await fetch(`/api/user-column-preferences/${props.resourceSlug}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken.value,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ order, hidden }),
    })

    if (response.ok) {
      const data = await response.json()
      columnPreferences.value = data.preferences
      showColumnSettings.value = false
      // Reload page to apply new column preferences
      window.location.reload()
    } else {
      const error = await response.json()
      alert('Failed to save preferences: ' + (error.message || 'Unknown error'))
    }
  } catch (error) {
    console.error('Failed to save column preferences:', error)
    alert('Failed to save preferences. Please try again.')
  }
}

const resetColumnPreferences = async () => {
  if (!props.resourceSlug) {
    return
  }

  if (!confirm('Are you sure you want to reset column preferences to defaults?')) {
    return
  }

  try {
    const response = await fetch(`/api/user-column-preferences/${props.resourceSlug}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken.value,
      },
      credentials: 'same-origin',
    })

    if (response.ok) {
      columnPreferences.value = null
      initializeAllColumns()
      showColumnSettings.value = false
      // Reload page to apply default columns
      window.location.reload()
    } else {
      alert('Failed to reset preferences. Please try again.')
    }
  } catch (error) {
    console.error('Failed to reset column preferences:', error)
    alert('Failed to reset preferences. Please try again.')
  }
}

const isColumnHidden = (columnKey) => {
  if (!columnPreferences.value || !columnPreferences.value.hidden) {
    return false
  }
  return columnPreferences.value.hidden.includes(columnKey)
}

const toggleColumnVisibility = (columnKey) => {
  if (!columnPreferences.value) {
    columnPreferences.value = { order: [], hidden: [] }
  }
  if (!columnPreferences.value.hidden) {
    columnPreferences.value.hidden = []
  }

  const index = columnPreferences.value.hidden.indexOf(columnKey)
  if (index > -1) {
    columnPreferences.value.hidden.splice(index, 1)
  } else {
    columnPreferences.value.hidden.push(columnKey)
  }
}

const initializeAllColumns = () => {
  // Use allColumns prop if available, otherwise fall back to columns
  const baseColumns = props.allColumns.length > 0 ? props.allColumns : props.columns
  
  if (columnPreferences.value && columnPreferences.value.order && columnPreferences.value.order.length > 0) {
    // Apply user's preferred order
    const columnMap = {}
    baseColumns.forEach(col => {
      columnMap[col.key] = col
    })
    
    const ordered = []
    columnPreferences.value.order.forEach(key => {
      if (columnMap[key]) {
        ordered.push(columnMap[key])
        delete columnMap[key]
      }
    })
    
    // Add any remaining columns (new columns not in preferences)
    Object.values(columnMap).forEach(col => {
      ordered.push(col)
    })
    
    allColumns.value = ordered
  } else {
    // Use default order
    allColumns.value = [...baseColumns]
  }
}

// Drag and Drop Handlers
const handleDragStart = (index, event) => {
  isDraggingColumn.value = true
  draggedColumnIndex.value = index
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/html', index.toString())
}

const handleDragOver = (index, event) => {
  event.preventDefault()
  event.dataTransfer.dropEffect = 'move'
}

const handleDrop = (targetIndex, event) => {
  event.preventDefault()
  
  if (draggedColumnIndex.value === null || draggedColumnIndex.value === targetIndex) {
    return
  }

  const columns = [...allColumns.value]
  const draggedColumn = columns[draggedColumnIndex.value]
  
  columns.splice(draggedColumnIndex.value, 1)
  columns.splice(targetIndex, 0, draggedColumn)
  
  allColumns.value = columns
  
  // Update preferences order
  if (!columnPreferences.value) {
    columnPreferences.value = { order: [], hidden: [] }
  }
  columnPreferences.value.order = columns.map(col => col.key)
}

const handleDragEnd = () => {
  isDraggingColumn.value = false
  draggedColumnIndex.value = null
}

const resolveCustomFilterComponent = (component) => {
  if (!component) {
    return 'div'
  }

  const candidates = []
  const pushCandidate = (value) => {
    if (!value) {
      return
    }
    if (!candidates.includes(value)) {
      candidates.push(value)
    }
  }

  const ensureVueExtension = (value) => (value.endsWith('.vue') ? value : `${value}.vue`)

  pushCandidate(component)
  pushCandidate(ensureVueExtension(component))

  if (!component.startsWith('./')) {
    pushCandidate(`./${component}`)
    pushCandidate(ensureVueExtension(`./${component}`))
  }

  if (!component.startsWith('./') && !component.startsWith('Filters/')) {
    pushCandidate(`./Filters/${component}`)
    pushCandidate(ensureVueExtension(`./Filters/${component}`))
  }

  if (component.startsWith('Filters/')) {
    pushCandidate(`./${component}`)
    pushCandidate(ensureVueExtension(`./${component}`))
  }

  for (const candidate of candidates) {
    if (customFilterComponentModules[candidate]) {
      return customFilterComponentModules[candidate].default
    }
  }

  if (component === 'Filters/SubscribableTypeFilter') {
    return SubscribableTypeFilter
  }

  return 'div'
}

const getFilterComponent = (type, filter) => {
  const components = {
    select: SelectFilter,
    boolean: BooleanFilter,
    dependent_select: DependentSelectFilter,
    date: DateFilter,
    custom: resolveCustomFilterComponent(filter?.component)
  }
  return components[type] || 'div'
}

// Initialize all columns when component mounts or columns/allColumns change
watch([() => props.columns, () => props.allColumns], () => {
  initializeAllColumns()
}, { immediate: true })

// Load preferences when resourceSlug is available
watch(() => props.resourceSlug, (newSlug) => {
  if (newSlug) {
    loadColumnPreferences().then(() => {
      initializeAllColumns()
    })
  }
}, { immediate: true })

// Watch for preference changes and update columns
watch(() => columnPreferences.value, () => {
  initializeAllColumns()
}, { deep: true })

// Watch for data changes to reset selection
watch(() => props.data, () => {
  selectedRows.value = []
  internalLoading.value = false
  openMenuRowKey.value = null
  menuPosition.value = 'bottom' // Reset to default
})

watch(
  () => [props.activePresets, props.activePreset],
  ([presets, preset]) => {
    if (presets && presets.length > 0) {
      selectedPresetKeys.value = [...presets]
    } else if (preset) {
      selectedPresetKeys.value = [preset]
    } else {
      selectedPresetKeys.value = []
    }
  }
)

syncSearchFromUrl(page.url)

watch(
  () => page.url,
  (url) => {
    syncSearchFromUrl(url)
  }
)

// Watch search to reset page
watch(searchQuery, (value) => {
  if (suppressSearchEmit.value) {
    return
  }

  currentPage.value = 1
  emit('search', value)
  triggerFilterEvent(1)
})
</script>

