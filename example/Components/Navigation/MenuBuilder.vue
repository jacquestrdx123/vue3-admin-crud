<template>
  <Modal :show="show" title="Menu Builder" @close="$emit('close')" max-width="6xl">
    <div class="space-y-4">
      <!-- Instructions -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <p class="text-sm text-blue-800 dark:text-blue-200">
          <strong>Drag and drop</strong> menu groups and items to reorder them. Changes are saved automatically.
        </p>
      </div>

      <!-- Menu Groups -->
      <div v-if="menuGroupsWithItems.length > 0" class="space-y-3">
        <div
          v-for="(group, groupIndex) in menuGroupsWithItems"
          :key="group.id"
          class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800"
          :class="{
            'ring-2 ring-indigo-500': draggedGroupIndex === groupIndex,
            'opacity-50': isDragging && draggedGroupIndex !== null && draggedGroupIndex !== groupIndex
          }"
        >
          <!-- Group Header -->
          <div
            :draggable="true"
            @dragstart="handleGroupDragStart(groupIndex, $event)"
            @dragover.prevent="handleGroupDragOver(groupIndex, $event)"
            @drop="handleGroupDrop(groupIndex, $event)"
            @dragend="handleGroupDragEnd"
            class="flex items-center justify-between p-4 cursor-move hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            :class="{
              'bg-indigo-50 dark:bg-indigo-900/20': draggedGroupIndex === groupIndex
            }"
          >
            <div class="flex items-center gap-3 flex-1">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
              </svg>
              <div>
                <div class="font-medium text-gray-900 dark:text-white">{{ group.label }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ group.key }}</div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <Badge :color="group.is_active ? 'green' : 'gray'">
                {{ group.is_active ? 'Active' : 'Inactive' }}
              </Badge>
              <span class="text-sm text-gray-500 dark:text-gray-400">Order: {{ group.sort_order }}</span>
            </div>
          </div>

          <!-- Group Items -->
          <div v-if="group.items && group.items.length > 0" class="border-t border-gray-200 dark:border-gray-700">
            <div
              v-for="(item, itemIndex) in group.items"
              :key="item.id"
              :draggable="true"
              @dragstart="handleItemDragStart(groupIndex, itemIndex, $event)"
              @dragover.prevent="handleItemDragOver(groupIndex, itemIndex, $event)"
              @drop="handleItemDrop(groupIndex, itemIndex, $event)"
              @dragend="handleItemDragEnd"
              class="flex items-center justify-between p-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0 cursor-move hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              :class="{
                'bg-indigo-50 dark:bg-indigo-900/20': draggedItemGroupIndex === groupIndex && draggedItemIndex === itemIndex,
                'opacity-50': isDraggingItem && draggedItemGroupIndex === groupIndex && draggedItemIndex !== null && draggedItemIndex !== itemIndex
              }"
            >
              <div class="flex items-center gap-3 flex-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                </svg>
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.label }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ item.route || item.url || 'No URL' }}
                    <span v-if="item.permission_name" class="ml-2 text-purple-600 dark:text-purple-400">
                      • {{ item.permission_name }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <Badge :color="item.is_active ? 'green' : 'gray'" size="sm">
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </Badge>
                <span class="text-xs text-gray-500 dark:text-gray-400">Order: {{ item.sort_order }}</span>
              </div>
            </div>
          </div>
          <div v-else class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center border-t border-gray-200 dark:border-gray-700">
            No items in this group
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
        <p>No menu groups found. Create menu groups first.</p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
        >
          Close
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Modal from '@/Components/UI/Modal.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  menuGroups: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close'])

// Create a computed property that ensures items array exists
const menuGroupsWithItems = computed(() => {
  return props.menuGroups.map(group => ({
    ...group,
    items: group.items || group.menuItems || []
  }))
})

// Group drag state
const isDragging = ref(false)
const draggedGroupIndex = ref(null)
const dragOverGroupIndex = ref(null)

// Item drag state
const isDraggingItem = ref(false)
const draggedItemGroupIndex = ref(null)
const draggedItemIndex = ref(null)
const dragOverItemGroupIndex = ref(null)
const dragOverItemIndex = ref(null)

// Group Drag Handlers
const handleGroupDragStart = (index, event) => {
  isDragging.value = true
  draggedGroupIndex.value = index
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/html', index.toString())
}

const handleGroupDragOver = (index, event) => {
  event.preventDefault()
  event.dataTransfer.dropEffect = 'move'
  dragOverGroupIndex.value = index
}

const handleGroupDrop = (targetIndex, event) => {
  event.preventDefault()
  dragOverGroupIndex.value = null

  if (draggedGroupIndex.value === null || draggedGroupIndex.value === targetIndex) {
    return
  }

  const groups = menuGroupsWithItems.value.map(g => ({ ...g, items: [...(g.items || [])] }))
  const draggedGroup = groups[draggedGroupIndex.value]
  groups.splice(draggedGroupIndex.value, 1)
  groups.splice(targetIndex, 0, draggedGroup)

  // Update sort_order based on new position
  const groupIds = groups.map((group, index) => ({
    id: group.id,
    sort_order: index + 1
  }))

  axios.post(route('vue.menu-groups.reorder'), {
    groups: groupIds
  })
    .then(() => {
      router.reload({ only: ['menuGroups'] })
    })
    .catch((error) => {
      console.error('Failed to reorder menu groups:', error)
      alert('Failed to reorder menu groups. Please try again.')
    })
}

const handleGroupDragEnd = () => {
  isDragging.value = false
  draggedGroupIndex.value = null
  dragOverGroupIndex.value = null
}

// Item Drag Handlers
const handleItemDragStart = (groupIndex, itemIndex, event) => {
  isDraggingItem.value = true
  draggedItemGroupIndex.value = groupIndex
  draggedItemIndex.value = itemIndex
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/html', `${groupIndex}-${itemIndex}`)
}

const handleItemDragOver = (groupIndex, itemIndex, event) => {
  event.preventDefault()
  event.dataTransfer.dropEffect = 'move'
  dragOverItemGroupIndex.value = groupIndex
  dragOverItemIndex.value = itemIndex
}

const handleItemDrop = (targetGroupIndex, targetItemIndex, event) => {
  event.preventDefault()
  dragOverItemGroupIndex.value = null
  dragOverItemIndex.value = null

  if (
    draggedItemGroupIndex.value === null ||
    draggedItemIndex.value === null ||
    (draggedItemGroupIndex.value === targetGroupIndex && draggedItemIndex.value === targetItemIndex)
  ) {
    return
  }

  const groups = menuGroupsWithItems.value.map(g => ({ ...g, items: [...(g.items || [])] }))
  const sourceGroup = groups[draggedItemGroupIndex.value]
  const targetGroup = groups[targetGroupIndex]
  const draggedItem = { ...sourceGroup.items[draggedItemIndex.value] }

  // Remove from source
  sourceGroup.items.splice(draggedItemIndex.value, 1)

  // If moving within same group
  if (draggedItemGroupIndex.value === targetGroupIndex) {
    targetGroup.items.splice(targetItemIndex, 0, draggedItem)
  } else {
    // Moving to different group
    targetGroup.items.splice(targetItemIndex, 0, draggedItem)
    draggedItem.menu_group_id = targetGroup.id
  }

  // Update sort_order for source group items
  const sourceItemIds = sourceGroup.items.map((item, index) => item.id)

  // Update sort_order for target group items  
  const targetItemIds = targetGroup.items.map((item, index) => item.id)

  // Prepare promises
  const promises = []

  // Always update source group order
  promises.push(
    axios.post(route('vue.menu-groups.reorder-menu-items', sourceGroup.id), {
      menu_item_ids: sourceItemIds
    })
  )

  // If moving to different group, update target group and move the item
  if (targetGroupIndex !== draggedItemGroupIndex.value) {
    promises.push(
      axios.post(route('vue.menu-groups.reorder-menu-items', targetGroup.id), {
        menu_item_ids: targetItemIds
      })
    )
    // Update menu_group_id for the moved item
    promises.push(
      axios.put(route('vue.menu-items.update', draggedItem.id), {
        menu_group_id: targetGroup.id
      })
    )
  }

  Promise.all(promises)
    .then(() => {
      router.reload({ only: ['menuGroups'] })
    })
    .catch((error) => {
      console.error('Failed to reorder menu items:', error)
      alert('Failed to reorder menu items. Please try again.')
    })
}

const handleItemDragEnd = () => {
  isDraggingItem.value = false
  draggedItemGroupIndex.value = null
  draggedItemIndex.value = null
  dragOverItemGroupIndex.value = null
  dragOverItemIndex.value = null
}
</script>

<style scoped>
/* Drag and drop visual feedback */
[draggable="true"] {
  user-select: none;
}
</style>

