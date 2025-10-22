<template>
  <div class="filters-modal" :class="[`filters-modal--${variant}`]">
    <!-- Заголовок -->
    <div class="filters-modal-header">
      <div class="d-flex align-center justify-space-between w-100">
        <span class="text-h6 font-weight-bold">Фильтры</span>
        <VBtn
          icon="mdi-close"
          variant="text"
          size="small"
          class="close-btn"
          @click="$emit('close')"
        />
      </div>
    </div>

    <VDivider />

    <!-- Выбранные фильтры (чипсы) -->
    <div v-if="hasSelections" class="selected-filters-wrapper">
      <div class="selected-filters-section">
        <div class="selected-chips-container">
          <template v-for="(values, filterKey) in localFilters" :key="filterKey">
            <template v-if="hasFilterSelections(filterKey, values)">
              <div
                v-for="chip in getFilterChips(filterKey, values)"
                :key="`${filterKey}-${chip.key}`"
                class="custom-chip active"
                @click="scrollToFilter(filterKey)"
              >
                <span class="label">{{ chip.label }}</span>
                <span
                  class="close"
                  @click.stop="removeChipValue(filterKey, chip)"
                >&times;</span>
              </div>
            </template>
          </template>
        </div>
      </div>
    </div>


    <VDivider v-if="hasSelections" />

    <!-- Содержимое фильтров -->
    <div class="filters-content">
      <template v-for="(filter, key) in filters" :key="key">
        <div 
          class="filter-item" 
          :data-filter-key="key"
          :id="`filter-${key}`"
        >
          <!-- Заголовок фильтра -->
          <div class="filter-header">
            <div class="d-flex align-center justify-space-between">
              <span class="filter-title font-weight-medium">{{ filter.label || key }}</span>
              
              <!-- Toggle фильтр - только переключатель -->
              <template v-if="filter.type === 'toggle'">
                <ToggleFilter
                  v-model="localFilters[key]"
                  :filter-data="filter"
                  :variant="variant"
                  :inline="true"
                  @update:model-value="updateFilter(key, $event)"
                />
              </template>
              
              <!-- Остальные фильтры - счетчик и кнопка очистки -->
              <template v-else>
                <div class="d-flex align-center">
                  <VChip 
                    v-if="getSelectionCount(key) > 0" 
                    size="small" 
                    color="primary"
                    variant="flat"
                    class="selection-badge"
                  >
                    {{ getSelectionCount(key) }}
                  </VChip>
                  <VBtn
                    v-if="getSelectionCount(key) > 0"
                    size="small"
                    variant="text"
                    color="error"
                    class="clear-filter-btn"
                    @click="clearFilter(key)"
                  >
                    Очистить
                  </VBtn>
                </div>
              </template>
            </div>
          </div>

          <!-- Контент фильтра (только если не toggle) -->
          <div v-if="filter.type !== 'toggle'" class="filter-content">
            <!-- Range фильтр -->
            <RangeFilter
              v-if="filter.type === 'range'"
              v-model="localFilters[key]"
              :filter-data="filter"
              :variant="variant"
              @update:model-value="updateFilter(key, $event)"
            />       
            <!-- Date Range фильтр -->
            <DateRangeFilter
              v-if="filter.type === 'date_range'"
              v-model="localFilters[key]"
              :filter-data="filter"
              :variant="variant"
              @update:model-value="updateFilter(key, $event)"
            />  

            <!-- Tree фильтр -->
            <TreeFilter
              v-else-if="filter.type === 'tree'"
              v-model="localFilters[key]"
              :filter-data="filter"
              :variant="variant"
              @update:model-value="updateFilter(key, $event)"
            />

            <!-- Checkboxes -->
            <CheckboxesFilter
              v-else-if="filter.type === 'checkboxes'"
              v-model="localFilters[key]"
              :filter-data="filter"
              :variant="variant"
              @update:model-value="updateFilter(key, $event)"
            />

            <!-- Radio buttons -->
            <RadioFilter
              v-else-if="filter.type === 'radio'"
              v-model="localFilters[key]"
              :filter-data="filter"
              :variant="variant"
              @update:model-value="updateFilter(key, $event)"
            />

            <!-- Buttons/Chips (default) -->
            <ChipsFilter
              v-else
              v-model="localFilters[key]"
              :filter-data="filter"
              :variant="variant"
              @update:model-value="updateFilter(key, $event)"
            />
          </div>
        </div>

        <!-- Разделитель между фильтрами -->
        <VDivider 
          v-if="key !== Object.keys(filters)[Object.keys(filters).length - 1]" 
        />
      </template>
    </div>

    <!-- Нижние кнопки (закреплены) -->
    <div class="filters-actions-sticky">
      <VDivider />
      <VCardActions class="filters-actions">
        <VBtn
          color="surface-variant"
          variant="outlined"
          size="large"
          class="reset-btn"
          @click="resetAllFilters"
        >
          Сброс
        </VBtn>
        <VBtn
          color="primary"
          variant="flat"
          size="large"
          class="apply-btn"
          @click="applyFilters"
        >
          Применить
          <VChip v-if="totalSelections > 0" size="small" color="white" class="ml-2">
            {{ totalSelections }}
          </VChip>
        </VBtn>
      </VCardActions>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  filters: {
    type: Object,
    required: true
  },
  selectedFilters: {
    type: Object,
    default: () => ({})
  },
  isMobile: {
    type: Boolean,
    default: false
  }
})

const variant = computed(() => {
  return props.isMobile ? 'modal-mobile' : 'modal'
})

const emit = defineEmits(['apply', 'reset', 'close'])

const localFilters = ref({ ...props.selectedFilters })

watch(() => props.selectedFilters, (newFilters) => {
  localFilters.value = { ...newFilters }
}, { deep: true })

const updateFilter = (filterKey, value) => {
  if (!value || (Array.isArray(value) && value.length === 0) || (typeof value === 'object' && Object.keys(value).length === 0)) {
    delete localFilters.value[filterKey]
  } else {
    localFilters.value[filterKey] = value
  }
}

const hasSelections = computed(() => 
  Object.entries(localFilters.value).some(([key, values]) => 
    hasFilterSelections(key, values)
  )
)

const totalSelections = computed(() => {
  let total = 0
  Object.entries(localFilters.value).forEach(([key, values]) => {
    const filterType = props.filters[key]?.type || 'buttons'
    if (filterType === 'range') {
      total += Object.keys(values || {}).length
    } else {
      total += Array.isArray(values) ? values.length : 0
    }
  })
  return total
})

const hasFilterSelections = (filterKey, values) => {
  const filter = props.filters[filterKey]
  if (filter?.type?.includes('range')) {
    return Object.keys(values || {}).length > 0
  }
  return Array.isArray(values) && values.length > 0
}

const getFilterChips = (filterKey, values) => {
  const filter = props.filters[filterKey]
  const chips = []
  
  if (filter?.type?.includes('range')) {
    if (values.from || values.to) {
      const label = `${filter.label || filterKey}: ${values.from || '∞'} - ${values.to || '∞'}`
      chips.push({ key: filter?.type ?? 'range', label, type: filter?.type ?? 'range' })
    }
  } else if (filter?.type === 'tree') {
    if (values && values.length > 0) {
      if (values.length === 1) {
        const option = findTreeOption(filter.options, values[0])
        const optionName = option?.name || values[0]
        chips.push({ 
          key: values[0], 
          label: `${filter.label || filterKey}: ${optionName}`, 
          type: 'single',
          value: values[0]
        })
      } else {
        chips.push({ 
          key: 'multiple', 
          label: `${filter.label || filterKey} (${values.length})`, 
          type: 'multiple'
        })
      }
    }
  } else if (filter?.type === 'toggle') {
    if (values && values.length > 0) {
      chips.push({ 
        key: 'toggle', 
        label: filter.label || filterKey, 
        type: 'toggle'
      })
    }
  } else if (Array.isArray(values) && values.length > 0) {
    if (values.length === 1) {
      const option = filter.options?.find(opt => opt.value == values[0])
      const optionName = option?.name || values[0]
      chips.push({ 
        key: values[0], 
        label: `${filter.label || filterKey}: ${optionName}`, 
        type: 'single',
        value: values[0]
      })
    } else {
      // Для множественных значений создаем один чипс с типом 'multiple'
      chips.push({ 
        key: 'multiple', 
        label: `${filter.label || filterKey} (${values.length})`, 
        type: 'multiple'
      })
    }
  }
  
  return chips
}


const findTreeOption = (options, value) => {
  for (const option of options) {
    if (option.value == value) return option // нестрогое сравнение
    if (option.children) {
      const found = findTreeOption(option.children, value)
      if (found) return found
    }
  }
  return null
}

const getSelectionCount = (filterKey) => {
  const values = localFilters.value[filterKey]
  const filterType = props.filters[filterKey]?.type || 'buttons'
  
  if (filterType === 'range') {
    return Object.keys(values || {}).length
  }
  return Array.isArray(values) ? values.length : 0
}

const scrollToFilter = (filterKey) => {
  const element = document.getElementById(`filter-${filterKey}`)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const clearFilter = (filterKey) => {
  delete localFilters.value[filterKey]
}

const removeChipValue = (filterKey, chip) => {
  if (chip.type.includes('range')) {
    delete localFilters.value[filterKey]
  } else if (chip.type === 'toggle') {
    delete localFilters.value[filterKey]
  } else if (chip.type === 'single') {
    // Для одиночного значения удаляем конкретное значение
    const current = localFilters.value[filterKey] || []
    const index = current.findIndex(item => item == chip.value)
    if (index > -1) {
      current.splice(index, 1)
    }
    if (current.length === 0) {
      delete localFilters.value[filterKey]
    }
  } else if (chip.type === 'multiple' || chip.type === 'tree') {
    // Для множественных значений очищаем весь фильтр
    delete localFilters.value[filterKey]
  }
}


const resetAllFilters = () => {
  localFilters.value = {}
  emit('reset')
}

const applyFilters = () => {
  emit('apply', localFilters.value)
}
</script>

<style scoped>
.filters-modal {
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
  border: 0!important;
  border-radius: 0!important;
  overflow: hidden;
  background: rgba(var(--v-theme-surface), 1);
}

/* Варианты модального окна */
.filters-modal--modal {
  max-width: 400px;
}

.filters-modal--modal-mobile {
  height: 100vh;
  width: 100vw;
}

.filters-modal-header {
  padding: 1rem 1rem;
  background: rgba(var(--v-theme-surface), 0.95);
  backdrop-filter: blur(10px);
  flex-shrink: 0;
}

.close-btn {
  transition: all 0.2s ease;
}

.selected-filters-wrapper {
  padding: 0!important;
  margin: 0!important;
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(var(--v-theme-surface), 1);
  flex-shrink: 0;
}

.selected-filters-section {
  padding: 1rem;
  background: rgba(var(--v-theme-primary), 0.05);
}

.selected-chips-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.filter-chip {
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.filter-chip:hover {
  transform: translateY(-1px);
}

.filters-content {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 0.5rem 0;
}

.filter-item {
  padding: 1.5rem 1rem 0.5rem 1rem;
  scroll-margin-top: 80px;
  scroll-behavior: smooth;
}

.filter-header {
  margin-bottom: 1rem;
}

.filter-title {
  font-size: 1rem;
  color: rgba(var(--v-theme-on-surface), 0.87);
}

.selection-badge {
  font-size: 0.75rem;
  font-weight: 600;
  min-width: 24px;
  height: 24px;
  margin-right: 0.5rem;
}

.clear-filter-btn {
  font-size: 0.75rem;
  min-width: auto;
  padding: 0 8px;
}

/* Закрепленные кнопки */
.filters-actions-sticky {
  position: sticky;
  bottom: 0;
  background: rgba(var(--v-theme-surface), 0.95);
  backdrop-filter: blur(10px);
  z-index: 10;
  flex-shrink: 0;
}

.filters-actions {
  padding: 1rem;
  display: flex;
  gap: 0.75rem;
}

.reset-btn {
  height: 48px !important;
  font-weight: 600;
  flex: 1;
}

.apply-btn {
  height: 48px !important;
  font-weight: 700;
  flex: 2;
}

/* Скроллбар стилизация */
.filters-content::-webkit-scrollbar {
  width: 6px;
}

.filters-content::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-radius: 3px;
}

.filters-content::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.3);
  border-radius: 3px;
}

/* Адаптивные стили для мобильной версии */
.filters-modal--modal-mobile .filters-modal-header {
  padding: 1rem;
}

.filters-modal--modal-mobile .filter-item {
  padding: 1rem 1rem 0.5rem 1rem;
}

.filters-modal--modal-mobile .filters-actions {
  padding: 1rem;
  gap: 0.5rem;
}

.custom-chip {
  display: inline-flex;
  align-items: center;
  height: 32px;
  padding: 0 12px;
  font-size: 0.875rem;
  line-height: 1;
  border-radius: 16px;
  /*
  background-color: rgb(var(--v-theme-surface-variant));
  */
  color: rgb(var(--v-theme-on-surface));
  border: 1px solid rgb(var(--v-border-color));
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.custom-chip:hover {
  background-color: rgba(var(--v-theme-on-surface), 0.06);
}

.custom-chip.active {
    background-color: rgb(var(--v-theme-primary), 0.1);
    border-color: transparent;
    color: rgb(var(--v-theme-primary));
}

.custom-chip .label {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.custom-chip .close {
  margin-left: 8px;
  font-weight: bold;
  font-size: 16px;
  opacity: 0.8;
  cursor: pointer;
  transition: opacity 0.2s ease;
  color: rgb(var(--v-theme-on-primary));
  background: rgb(var(--v-theme-primary));
  width: 16px;
  height: 16px;
  line-height: 16px;
  border-radius: 50%;
  text-align: center;
}

.custom-chip .close:hover {
  opacity: 1;
}

.custom-chip:active {
  transform: scale(0.97);
}
</style>