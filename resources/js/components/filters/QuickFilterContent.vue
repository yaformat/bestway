<template>
  <div class="quick-filter-content">
    <!-- Range фильтр -->
    <RangeFilter
      v-if="filterData.type === 'range'"
      v-model="localValues"
      :filter-data="filterData"
      variant="quick"
      @update:model-value="updateLocalValues"
    />
    <!-- Date Range фильтр -->
    <DateRangeFilter
      v-if="filterData.type === 'date_range'"
      v-model="localValues"
      :filter-data="filterData"
      variant="quick"
      @update:model-value="updateLocalValues"
    />

    <!-- Toggle фильтр -->
    <div v-else-if="filterData.type === 'toggle'" class="toggle-filter-ios">
      <VSwitch
        :model-value="Array.isArray(localValues) ? localValues.length > 0 : false"
        :label="filterData.label"
        color="primary"
        hide-details
        density="comfortable"
        class="ios-toggle-switch"
        @update:model-value="updateToggle"
      />
    </div>

    <!-- Остальные фильтры -->
    <TreeFilter
      v-else-if="filterData.type === 'tree'"
      v-model="localValues"
      :filter-data="filterData"
      variant="quick"
      @update:model-value="updateLocalValues"
    />

    <CheckboxesFilter
      v-else-if="filterData.type === 'checkboxes'"
      v-model="localValues"
      :filter-data="filterData"
      variant="quick"
      @update:model-value="updateLocalValues"
    />

    <RadioFilter
      v-else-if="filterData.type === 'radio'"
      v-model="localValues"
      :filter-data="filterData"
      variant="quick"
      @update:model-value="updateLocalValues"
    />

    <ChipsFilter
      v-else
      v-model="localValues"
      :filter-data="filterData"
      variant="quick"
      @update:model-value="updateLocalValues"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  filterKey: {
    type: String,
    required: true
  },
  filterData: {
    type: Object,
    required: true
  },
  selectedValues: {
    type: [Array, Object],
    default: () => []
  },
  focusedValue: {
    type: [String, Number],
    default: null
  }
})

const emit = defineEmits(['update', 'apply', 'clear'])

const initializeLocalValues = () => {
  if (props.filterData.type?.includes('range')) {
    return { ...props.selectedValues }
  }
  return Array.isArray(props.selectedValues) ? [...props.selectedValues] : []
}

const localValues = ref(initializeLocalValues())

// Следим за изменениями selectedValues только при инициализации
watch(() => props.selectedValues, (newValues) => {
  if (props.filterData.type?.includes('range')) {
    localValues.value = { ...newValues }
  } else {
    localValues.value = Array.isArray(newValues) ? [...newValues] : []
  }
}, { deep: true, immediate: true })

// НЕ эмитим update автоматически - только сохраняем локально
const updateLocalValues = (newValue) => {
  localValues.value = newValue
  // Убираем автоматический emit('update')
}

const updateToggle = (value) => {
  const newValue = value ? ['1'] : []
  localValues.value = newValue
  // Убираем автоматический emit('update')
}

// Методы для кнопок
const applyChanges = () => {
  emit('update', props.filterKey, localValues.value)
  emit('apply')
}

const clearChanges = () => {
  if (props.filterData.type?.includes('range')) {
    localValues.value = {}
  } else {
    localValues.value = []
  }
  emit('clear')
}

// Экспортируем методы для использования в родительском компоненте
defineExpose({
  applyChanges,
  clearChanges,
  localValues
})
</script>

<style scoped>
.quick-filter-content {
  padding: 20px;
  min-height: 200px;
}

.toggle-filter-ios {
  padding: 20px 0;
  display: flex;
  justify-content: center;
}

.ios-toggle-switch {
  margin: 0;
}
</style>
