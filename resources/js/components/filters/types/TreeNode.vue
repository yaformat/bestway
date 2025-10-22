<template>
  <div class="mobile-tree-node">
    <div class="tree-node-header">
      <VBtn
        v-if="node.children && node.children.length > 0"
        :icon="expanded ? 'mdi-chevron-down' : 'mdi-chevron-right'"
        size="small"
        variant="text"
        density="compact"
        class="expand-btn"
        @click="expanded = !expanded"
      />
      <div v-else-if="hasExpandableNodesOnLevel" class="expand-spacer" />
      
      <VCheckbox
        :model-value="isSelected"
        :indeterminate="isIndeterminate"
        :label="node.name"
        color="primary"
        density="compact"
        hide-details
        class="tree-checkbox"
        @update:model-value="handleToggle"
      />
    </div>
    
    <VExpandTransition>
      <div v-if="expanded && node.children" class="tree-children">
        <TreeNode
          v-for="child in node.children"
          :key="child.value"
          :node="child"
          :selected-values="selectedValues"
          :multiple="multiple"
          :has-expandable-nodes-on-level="hasExpandableNodesOnLevel"
          @toggle="$emit('toggle', $event)"
        />
      </div>
    </VExpandTransition>
  </div>
</template>


<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  node: {
    type: Object,
    required: true
  },
  selectedValues: {
    type: Array,
    default: () => []
  },
  multiple: {
    type: Boolean,
    default: true
  },
  hasExpandableNodesOnLevel: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle'])

const expanded = ref(false)

// Проверяем, есть ли среди дочерних узлов те, у которых есть свои дети
const childrenHaveExpandableNodes = computed(() => {
  console.log('Computing childrenHaveExpandableNodes for:', props.node.name)
  console.log('Node children:', props.node.children)
  
  if (!props.node.children || props.node.children.length === 0) {
    console.log('No children, returning false')
    return false
  }
  
  const result = props.node.children.some(child => {
    const hasChildren = child.children && child.children.length > 0
    console.log(`Child ${child.name} has children:`, hasChildren)
    return hasChildren
  })
  
  console.log('Final result:', result)
  return result
})

// Получаем все дочерние значения рекурсивно (только листовые узлы)
const getAllChildValues = (children) => {
  let values = []
  for (const child of children) {
    if (child.children && child.children.length > 0) {
      values = values.concat(getAllChildValues(child.children))
    } else {
      values.push(child.value)
    }
  }
  return values
}

// Проверка выбранности текущего узла
const isSelected = computed(() => {
  if (!props.node.children || props.node.children.length === 0) {
    return props.selectedValues.some(value => value == props.node.value)
  } else {
    const childValues = getAllChildValues(props.node.children)
    return childValues.every(childValue => 
      props.selectedValues.some(selectedValue => selectedValue == childValue)
    )
  }
})

// Логика для indeterminate состояния
const isIndeterminate = computed(() => {
  if (!props.node.children || props.node.children.length === 0 || !props.multiple) {
    return false
  }
  
  const childValues = getAllChildValues(props.node.children)
  const selectedChildrenCount = childValues.filter(childValue => 
    props.selectedValues.some(selectedValue => selectedValue == childValue)
  ).length
  
  return selectedChildrenCount > 0 && selectedChildrenCount < childValues.length
})

const handleToggle = (checked) => {
  if (!props.node.children || props.node.children.length === 0) {
    emit('toggle', props.node.value)
  } else {
    const childValues = getAllChildValues(props.node.children)
    
    if (checked) {
      emit('toggle', { 
        type: 'select-children',
        childValues: childValues
      })
    } else {
      emit('toggle', { 
        type: 'deselect-children',
        childValues: childValues
      })
    }
  }
}

// Автоматически разворачиваем узел, если у него есть выбранные дочерние элементы
const checkShouldExpand = () => {
  if (!props.node.children || props.node.children.length === 0) return
  
  const childValues = getAllChildValues(props.node.children)
  const hasSelectedChildren = childValues.some(childValue => 
    props.selectedValues.some(selectedValue => selectedValue == childValue)
  )
  
  if (hasSelectedChildren) {
    expanded.value = true
  }
}

// Проверяем при инициализации и при изменении выбранных значений
//watch(() => props.selectedValues, checkShouldExpand, { immediate: true })
</script>


<style scoped>
.mobile-tree-node {
  border-left: 2px solid rgba(var(--v-theme-primary), 0.1);
  padding-left: 0.5rem;
  transition: all 0.2s ease;
}

.mobile-tree-node:hover {
  border-left-color: rgba(var(--v-theme-primary), 0.3);
  background: rgba(var(--v-theme-primary), 0.02);
}

.tree-node-header {
  display: flex;
  align-items: center;
  padding: 0.25rem 0;
  gap: 0.5rem;
}

.expand-btn {
  transition: all 0.2s ease;
  border-radius: 50%;
}

.expand-btn:hover {
  background: rgba(var(--v-theme-primary), 0.1);
  transform: scale(1.1);
}

.expand-spacer {
  width: 26px;
  flex-shrink: 0;
}

.tree-checkbox {
  flex-grow: 1;
  margin: 0;
}

.tree-children {
  margin-left: 1rem;
  padding-left: 0.5rem;
  border-left: 1px dashed rgba(var(--v-theme-on-surface), 0.12);
}

.mobile-tree-node:last-child {
  /*border-left: none;*/
}

/* Анимация для выбранных элементов */
.tree-checkbox :deep(.v-selection-control__wrapper) {
  transition: all 0.2s ease;
}

.tree-checkbox :deep(.v-checkbox .v-selection-control__input:hover .v-selection-control__wrapper) {
  transform: scale(1.05);
}

/* Стили для выбранных элементов */
.tree-checkbox :deep(.v-selection-control--dirty .v-selection-control__wrapper) {
  background: rgba(var(--v-theme-primary), 0.08);
  border-radius: 4px;
}
</style>
