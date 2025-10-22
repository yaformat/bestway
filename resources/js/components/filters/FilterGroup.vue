<template>
<!-- Toggle фильтр - отдельная кнопка с переключателем -->
<div
v-if="filterData.type === 'toggle'"
:class="['filter-group', 'filter-group--toggle', `filter-key--${filterKey || 'default'}`]"
>
<VBtn
:class="['filter-toggle filter-toggle-btn', { 'has-selections': hasSelections }]"
variant="outlined"
size="small"
@click="handleToggleSwitch"
>
<span class="filter-label">{{ filterData.label || filterKey }}</span>
<VSwitch
:model-value="hasSelections"
color="primary"
density="compact"
hide-details
class="toggle-switch-inline ml-2"
/>
</VBtn>
</div>
<!-- Остальные фильтры с dropdown -->
<div
v-else
:class="['filter-group', `filter-group--${filterData.type || 'default'}`, `filter-key--${filterKey || 'default'}`]"
@mouseenter="showDropdown" 
@mouseleave="hideDropdown"
ref="filterGroup"
>
<VBtn
:class="['filter-toggle', { 'has-selections': hasSelections, 'dropdown-open': dropdownVisible }]"
variant="outlined"
size="small"
@click="toggleDropdown"
ref="filterButton"
>
<span class="filter-label">{{ filterData.label || filterKey }}</span>
<!-- Счетчик - абсолютно позиционированный -->
<VChip
v-if="hasSelections && isMultiple"
size="x-small"
class="selection-count-badge"
color="primary"
variant="flat"
>
{{ selectedCount }}
</VChip>
<!-- Крестик очистки - абсолютно позиционированный -->
<VIcon
v-if="hasSelections"
size="small"
class="clear-icon-absolute"
@click.stop="clearFilter"
>
mdi-close
</VIcon>
<!-- Стрелка dropdown -->
<VIcon :class="['dropdown-icon', { 'rotate-180': dropdownVisible }]" size="small">
mdi-chevron-down
</VIcon>
</VBtn>
<VCard
v-show="dropdownVisible"
:class="['filter-dropdown', `filter-dropdown--${filterData.type || 'default'}`, `filter-key--${filterKey || 'default'}`, dropdownPosition]"
elevation="12"
:style="dropdownStyle"
>
<VCardText class="pa-4">
<!-- Range фильтр -->
<RangeFilter
v-if="filterData.type === 'range'"
:model-value="selectedValues"
:filter-data="filterData"
variant="dropdown"
@update:model-value="updateFilter"
/>
<!-- Date Range фильтр -->
<DateRangeFilter
v-if="filterData.type === 'date_range'"
:model-value="selectedValues"
:filter-data="filterData"
variant="dropdown"
@update:model-value="updateFilter"
/>
<!-- Single checkbox -->
<div v-else-if="filterData.type === 'single_checkbox'" class="single-checkbox-wrapper">
<VCheckbox
:model-value="Array.isArray(selectedValues) && selectedValues.length > 0"
:label="filterData.label"
density="compact"
hide-details
color="primary"
class="single-checkbox"
@update:model-value="toggleSingleCheckbox"
/>
</div>
<!-- Tree фильтр -->
<TreeFilter
v-else-if="filterData.type === 'tree'"
:model-value="selectedValues"
:filter-data="filterData"
variant="dropdown"
@update:model-value="updateFilter"
/>
<!-- Checkboxes -->
<CheckboxesFilter
v-else-if="filterData.type === 'checkboxes'"
:model-value="selectedValues"
:filter-data="filterData"
variant="dropdown"
@update:model-value="updateFilter"
/>
<!-- Radio buttons -->
<RadioFilter
v-else-if="filterData.type === 'radio'"
:model-value="selectedValues"
:filter-data="filterData"
variant="dropdown"
@update:model-value="updateFilter"
/>
<!-- Buttons/Chips (default) -->
<ChipsFilter
v-else
:model-value="selectedValues"
:filter-data="filterData"
variant="dropdown"
@update:model-value="updateFilter"
/>
</VCardText>
</VCard>
</div>
</template>
<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'

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
  }
})

const emit = defineEmits(['toggle-option', 'clear-filter', 'update-range', 'update-tree'])

const dropdownVisible = ref(false)
const dropdownPosition = ref('left') // 'left' или 'right'
const dropdownStyle = ref({})
const filterGroup = ref(null)
const filterButton = ref(null)

const hasSelections = computed(() => {
  if (props.filterData.type?.includes('range')) {
    return Object.keys(props.selectedValues || {}).length > 0
  }
  return Array.isArray(props.selectedValues) && props.selectedValues.length > 0
})

const selectedCount = computed(() => {
  if (props.filterData.type?.includes('range')) {
    return Object.keys(props.selectedValues || {}).length
  }
  return Array.isArray(props.selectedValues) ? props.selectedValues.length : 0
})

const isMultiple = computed(() => props.filterData.multiple !== false)

// Находим контейнер с фильтрами
const findFiltersContainer = () => {
  if (!filterGroup.value || !filterGroup.value.$el) {
    return null
  }
  
  let element = filterGroup.value.$el.parentElement
  
  // Ищем родительский элемент с классом, содержащим 'filter'
  while (element && element !== document.body) {
    if (element.classList.contains('filters-container') || 
        element.classList.contains('filters') ||
        element.classList.contains('filter-section') ||
        (element.classList.contains('v-col') && element.querySelector('.filter-group'))) {
      return element
    }
    element = element.parentElement
  }
  
  // Если не нашли специфический контейнер, используем родительский .v-col
  const vColElement = filterGroup.value.$el.closest('.v-col')
  return vColElement || filterGroup.value.$el.parentElement
}

// Определяем позицию dropdown с учетом контейнера
const calculateDropdownPosition = async () => {
  if (!filterButton.value || !filterButton.value.$el || !dropdownVisible.value) {
    return
  }
  
  await nextTick()
  
  const buttonElement = filterButton.value.$el
  const buttonRect = buttonElement.getBoundingClientRect()
  const container = findFiltersContainer()
  
  if (!container) {
    // Если не нашли контейнер, используем старую логику
    calculateViewportPosition()
    return
  }
  
  const containerRect = container.getBoundingClientRect()
  const dropdownWidth = 320
  
  // Доступное пространство справа внутри контейнера
  const spaceOnRight = containerRect.right - buttonRect.right
  const spaceOnLeft = buttonRect.left - containerRect.left
  
  // Определяем позицию
  if (spaceOnRight >= dropdownWidth) {
    // Места справа достаточно
    dropdownPosition.value = 'left'
    dropdownStyle.value = {
      left: '0',
      right: 'auto'
    }
  } else if (spaceOnLeft >= dropdownWidth) {
    // Места слева достаточно
    dropdownPosition.value = 'right'
    dropdownStyle.value = {
      left: 'auto',
      right: '0'
    }
  } else {
    // Места с обеих сторон недостаточно, выбираем большую сторону
    if (spaceOnRight > spaceOnLeft) {
      dropdownPosition.value = 'left'
      dropdownStyle.value = {
        left: '0',
        right: 'auto',
        maxWidth: `${Math.max(spaceOnRight, 200)}px` // Минимум 200px
      }
    } else {
      dropdownPosition.value = 'right'
      dropdownStyle.value = {
        left: 'auto',
        right: '0',
        maxWidth: `${Math.max(spaceOnLeft, 200)}px` // Минимум 200px
      }
    }
  }
}

// Резервная функция для расчета позиции относительно viewport
const calculateViewportPosition = () => {
  if (!filterButton.value || !filterButton.value.$el) {
    return
  }
  
  const buttonRect = filterButton.value.$el.getBoundingClientRect()
  const viewportWidth = window.innerWidth
  const dropdownWidth = 320
  
  const spaceOnRight = viewportWidth - buttonRect.right
  
  if (spaceOnRight >= dropdownWidth) {
    dropdownPosition.value = 'left'
    dropdownStyle.value = {
      left: '0',
      right: 'auto'
    }
  } else {
    dropdownPosition.value = 'right'
    dropdownStyle.value = {
      left: 'auto',
      right: '0'
    }
  }
}

const clearFilter = () => {
  emit('clear-filter', props.filterKey)
  dropdownVisible.value = false
}

const showDropdown = async () => {
  dropdownVisible.value = true
  await calculateDropdownPosition()
}

const hideDropdown = () => {
  setTimeout(() => {
    dropdownVisible.value = false
  }, 150)
}

const toggleDropdown = async () => {
  dropdownVisible.value = !dropdownVisible.value
  if (dropdownVisible.value) {
    await calculateDropdownPosition()
  }
}

const handleToggleSwitch = (checked) => {
  emit('toggle-option', props.filterKey, '1')
}

const updateFilter = (newValue) => {
  if (props.filterData.type?.includes('range')) {
    emit('update-range', props.filterKey, 'from', newValue.from)
    emit('update-range', props.filterKey, 'to', newValue.to)
  } else if (props.filterData.type === 'tree') {
    emit('update-tree', props.filterKey, newValue)
  } else if (props.filterData.type === 'toggle') {
    emit('toggle-option', props.filterKey, '1')
  } else if (props.filterData.type === 'radio') {
    const currentValues = Array.isArray(props.selectedValues) ? props.selectedValues : []
    const newValues = Array.isArray(newValue) ? newValue : []
    
    if (newValues.length === 0) {
      currentValues.forEach(value => emit('toggle-option', props.filterKey, value))
    } else if (currentValues.length === 0) {
      emit('toggle-option', props.filterKey, newValues[0])
    } else if (currentValues[0] === newValues[0]) {
      emit('toggle-option', props.filterKey, currentValues[0])
    } else {
      emit('toggle-option', props.filterKey, currentValues[0])
      emit('toggle-option', props.filterKey, newValues[0])
    }
  } else {
    const currentValues = Array.isArray(props.selectedValues) ? props.selectedValues : []
    const toAdd = newValue.filter(val => !currentValues.some(curr => curr == val))
    const toRemove = currentValues.filter(val => !newValue.some(newVal => newVal == val))
    
    toAdd.forEach(value => emit('toggle-option', props.filterKey, value))
    toRemove.forEach(value => emit('toggle-option', props.filterKey, value))
  }
}

const toggleSingleCheckbox = (checked) => {
  emit('toggle-option', props.filterKey, '1')
}

// Слушатель изменения размера окна
const handleResize = () => {
  if (dropdownVisible.value) {
    calculateDropdownPosition()
  }
}

// Слушатель скролла для пересчета позиции
const handleScroll = () => {
  if (dropdownVisible.value) {
    calculateDropdownPosition()
  }
}

// Безопасный вызов функций
const safeCalculatePosition = () => {
  try {
    calculateDropdownPosition()
  } catch (error) {
    console.warn('Ошибка при расчете позиции dropdown:', error)
    // В случае ошибки используем резервный метод
    calculateViewportPosition()
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  window.addEventListener('scroll', handleScroll, true)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('scroll', handleScroll, true)
})
</script>

<style scoped>
.filter-group {
  position: relative;
  z-index: 1;
}

.filter-toggle {
  transition: all 0.3s ease;
  border-radius: 8px;
  font-weight: 500;
  min-height: 40px;
  position: relative;
  z-index: 2;
  min-width: 60px;
  padding-right: 40px !important;
}

.filter-toggle:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.15);
}

.filter-toggle.has-selections {
  border-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.05);
}

.filter-toggle.dropdown-open {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  z-index: 1001;
}

.filter-label {
  font-weight: 500;
  white-space: nowrap;
}

.selection-count-badge {
  position: absolute !important;
  top: -8px;
  right: 16px;
  font-size: 0.75rem !important;
  font-weight: 600;
  z-index: 3;
  pointer-events: none;
}

.clear-icon-absolute {
  position: absolute !important;
  top: 50%;
  right: 20px;
  transform: translateY(-50%);
  opacity: 0.7;
  transition: opacity 0.2s ease;
  z-index: 3;
  cursor: pointer;
}

.clear-icon-absolute:hover {
  opacity: 1;
  color: rgb(var(--v-theme-error));
}

.dropdown-icon {
  position: absolute !important;
  top: 50%;
  right: 4px;
  transform: translateY(-50%);
  transition: transform 0.3s ease;
  z-index: 3;
}

.dropdown-icon.rotate-180 {
  transform: translateY(-50%) rotate(180deg);
}

.filter-dropdown {
  position: absolute;
  top: 100%;
  z-index: 1000;
  min-width: 280px;
  max-width: 400px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
  border-top: none;
  animation: slideDown 0.3s ease;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12) !important;
}

/* Позиционирование dropdown */
.filter-dropdown--left {
  left: 0;
  right: auto;
}

.filter-dropdown--right {
  left: auto;
  right: 0;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
  .filter-dropdown {
    left: auto !important;
    right: 0 !important;
    min-width: 250px;
    max-width: calc(100vw - 20px);
  }
  
  .filter-dropdown--left,
  .filter-dropdown--right {
    left: auto !important;
    right: 0 !important;
  }
}

/* Для очень узких экранов */
@media (max-width: 480px) {
  .filter-dropdown {
    min-width: 200px;
    max-width: calc(100vw - 10px);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.single-checkbox-wrapper {
  padding: 0.5rem 0;
}

.single-checkbox {
  margin: 0;
}

.filter-group--toggle {
  position: relative;
  z-index: 1;
}

.filter-toggle-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px 0 18px !important;
}

.toggle-switch-inline {
  margin: 0;
  flex-shrink: 0;
}

.toggle-switch-inline :deep(.v-selection-control) {
  min-height: auto;
}

.toggle-switch-inline :deep(.v-input__details) {
  display: none;
}

/* Дополнительные стили для лучшей видимости */
.filter-dropdown--right {
  animation: slideDownRight 0.3s ease;
}

@keyframes slideDownRight {
  from {
    opacity: 0;
    transform: translateY(-10px) translateX(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0) translateX(0);
  }
}

/* Улучшение для темной темы */
@media (prefers-color-scheme: dark) {
  .filter-dropdown {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
  }
}

/* Предотвращение выхода за границы */
.filter-dropdown {
  max-height: 80vh;
  overflow-y: auto;
}

/* Когда dropdown слишком большой по высоте */
.filter-dropdown::-webkit-scrollbar {
  width: 6px;
}

.filter-dropdown::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

.filter-dropdown::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 3px;
}

.filter-dropdown::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.5);
}

/* Для случаев когда контейнер с фильтрами имеет ограниченную ширину */
.filters-container,
.filter-section {
  position: relative;
  overflow: visible;
}
</style>