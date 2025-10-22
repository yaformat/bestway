<template>
  <div :class="['tree-filter', `tree-filter--${variant}`]">
    <TreeNode
      v-for="node in filterData.options"
      :key="node.value"
      :node="node"
      :selected-values="modelValue"
      :multiple="filterData.multiple !== false"
      :has-expandable-nodes-on-level="filterData.options.some(n => n.children && n.children.length > 0)"
      @toggle="toggleTreeOption"
    />
  </div>
</template>

<script setup>

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  filterData: {
    type: Object,
    required: true
  },
  variant: {
    type: String,
    default: 'default' // 'default', 'dropdown', 'quick'
  }
})

const emit = defineEmits(['update:modelValue'])

const toggleTreeOption = (value) => {
  const current = [...props.modelValue]
  
  if (typeof value === 'object' && value.type) {
    // Обработка выбора/снятия выбора дочерних элементов
    if (value.type === 'select-children') {
      // Добавляем всех детей
      value.childValues.forEach(childValue => {
        if (!current.some(item => item == childValue)) {
          current.push(childValue)
        }
      })
    } else if (value.type === 'deselect-children') {
      // Убираем всех детей
      value.childValues.forEach(childValue => {
        const index = current.findIndex(item => item == childValue)
        if (index > -1) {
          current.splice(index, 1)
        }
      })
    }
  } else {
    // Обычное переключение для листовых узлов
    const index = current.findIndex(item => item == value)
    
    if (props.filterData?.multiple === false) {
      const newValue = current.some(item => item == value) ? [] : [value]
      emit('update:modelValue', newValue)
      return
    } else {
      if (index > -1) {
        current.splice(index, 1)
      } else {
        current.push(value)
      }
    }
  }
  
  emit('update:modelValue', current)
}

</script>

<style scoped>
.tree-filter {
  border: 1px solid rgba(var(--v-border-color), 0.12);
  border-radius: 12px;
  padding: 0.5rem;
  margin: 1rem 0;
}

.tree-filter--dropdown {
  max-height: 300px;
  margin: 0;
  padding: .75rem;
  max-width: 400px;
  min-width: 300px;
  overflow-y: auto;
}

.tree-filter--quick {
  max-height: 400px;
  padding: 1rem;
  margin: 0.5rem 0;
  overflow-y: auto;
}

.tree-filter::-webkit-scrollbar {
  width: 6px;
}

.tree-filter::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-radius: 3px;
}

.tree-filter::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.3);
  border-radius: 3px;
}

.tree-filter::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-primary), 0.5);
}
</style>
