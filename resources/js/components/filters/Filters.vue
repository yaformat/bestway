<template>
  <div class="filters-wrapper">
    <!-- Десктопная версия -->
    <div class="d-none d-md-block">
      <!-- Быстрые фильтры -->
      <div v-if="hasQuickFiltersDesktop" class="desktop-row-1 d-flex gap-3 mb-3">
        <div class="quick-filters-container">
          <template v-for="(filter, key) in quickFiltersDesktop" :key="key">
            <FilterGroup
              :filter-key="key"
              :filter-data="filter"
              :selected-values="filtersState.filters[key] || getDefaultValue(filter)"
              @toggle-option="toggleFilterOption"
              @clear-filter="clearFilter"
              @update-range="updateRangeFilter"
              @update-tree="updateTreeFilter"
            />
          </template>
        </div>
      </div>

      <!-- Второй ряд с контролами -->
      <div v-if="hasSorting || hasSearch || hasHiddenFilters" class="desktop-row-2 d-flex align-center gap-3">
        <!-- Сортировка -->
        <SortingSelect
          v-if="hasSorting"
          v-model="filtersState.sortBy"
          :sorting-options="sortingOptions"
          class="sorting-select"
          @update:model-value="handleSortChange"
        />

        <!-- Поиск -->
        <VTextField
          v-if="hasSearch"
          v-model="filtersState.searchQuery"
          placeholder="Поиск (мин. 3 символа)..."
          variant="outlined"
          density="compact"
          hide-details
          clearable
          class="search-field"
          @update:model-value="handleSearchInput"
        >
          <template v-slot:prepend-inner>
            <VIcon color="primary" size="20">mdi-magnify</VIcon>
          </template>
        </VTextField>

        <VSpacer />

        <!-- Кнопка всех фильтров -->
        <VBtn 
          v-if="hasHiddenFilters"
          color="secondary"
          variant="outlined"
          class="all-filters-btn"
          @click="desktopDialog = true"
        >
          <VIcon start size="18">mdi-filter-variant</VIcon>
          Фильтры
          <VChip 
            v-if="totalSelected > 0" 
            class="ml-2" 
            size="x-small"
            color="primary"
            variant="flat"
          >
            {{ totalSelected }}
          </VChip>
        </VBtn>

        <!-- Кнопка сброса -->
        <VBtn 
          variant="outlined" 
          color="error"
          :disabled="!hasAnySelections"
          class="reset-btn"
          @click="resetFilters"
        >
          <VIcon start size="18">mdi-refresh</VIcon>
          Сброс
        </VBtn>
      </div>
    </div>

    <!-- Мобильная версия -->
    <div class="d-block d-md-none mobile-filters">
      <!-- Поиск -->
      <VTextField
        v-if="hasSearch"
        v-model="filtersState.searchQuery"
        placeholder="Поиск (мин. 3 символа)..."
        variant="outlined"
        density="comfortable"
        hide-details
        clearable
        class="mobile-search mb-3"
        @update:model-value="handleSearchInput"
      >
        <template v-slot:prepend-inner>
          <VIcon color="primary" size="20">mdi-magnify</VIcon>
        </template>
      </VTextField>

      <!-- Чипсы фильтров -->
      <div v-if="showChipsContainer" class="chips-container">
        <div class="chips-scroll-area">
          <!-- Быстрые фильтры -->
          <template v-for="(filter, key) in quickFiltersMobile" :key="key">
            <div
              class="custom-chip"
              :class="{ active: isFilterActive(key) }"
              @click="filter.type === 'toggle' ? toggleMobileSwitch(key) : openQuickFilter(key)"
            >
              <span class="label">
                <template v-if="filter.type === 'toggle'">
                  {{ filter.label || key }}
                </template>
                <template v-else>
                  {{ getMobileChipLabel(key, filter) }}
                </template>
              </span>

              <span
                v-if="isFilterActive(key)"
                class="close"
                @click.stop="clearFilter(key)"
              >×</span>
            </div>
          </template>

          <!-- Остальные выбранные фильтры -->
          <template v-for="(values, filterKey) in filtersState.filters" :key="filterKey">
            <template v-if="hasFilterSelections(filterKey, values) && !isQuickFilterMobile(filterKey)">
              <div
                v-for="chip in getFilterChips(filterKey, values)"
                :key="`${filterKey}-${chip.key}`"
                class="custom-chip active"
                @click="openQuickFilter(filterKey, chip)"
              >
                <span class="label">{{ chip.label }}</span>
                <span
                  class="close"
                  @click.stop="removeChipValue(filterKey, chip)"
                >×</span>
              </div>
            </template>
          </template>
        </div>
      </div>

      <!-- Строка с сортировкой, сбросом и фильтрами -->
      <div v-if="hasSorting || hasHiddenFilters" class="mobile-controls">
        <!-- Сортировка -->
        <VBtn
          v-if="hasSorting"
          variant="outlined"
          color="secondary"
          class="mobile-sort-btn"
          size="small"
          @click="openSortingSheet"
        >
          <VIcon start size="18">mdi-sort</VIcon>
          {{ getCurrentSortingLabel() }}
        </VBtn>

        <!-- Кнопка сброса -->
        <VBtn
          v-if="hasAnySelections"
          variant="text"
          color="error"
          class="mobile-reset-btn"
          size="small"
          @click="resetFilters"
        >
          <VIcon start size="18">mdi-refresh</VIcon>
          Сброс
        </VBtn>

        <!-- Фильтры -->
        <VBtn 
          variant="outlined"
          color="secondary"
          class="mobile-filter-btn"
          size="small"
          @click="mobileDialog = true"
        >
          <VIcon size="18">mdi-filter-variant</VIcon>
          <span class="ml-1" v-if="totalSelected === 0" >Фильтры</span>
          <VChip 
            v-else
            class="ml-1" 
            size="x-small"
            color="primary"
            variant="flat"
          >
            {{ totalSelected }}
          </VChip>
        </VBtn>
      </div>
    </div>

    <!-- Модальные окна -->
    <template v-if="isMobile">
      <!-- Мобильное модальное окно -->
      <VDialog v-model="mobileDialog" fullscreen transition="dialog-bottom-transition">
        <FiltersModal
          :filters="filters"
          :selected-filters="filtersState.filters"
          :is-mobile="true"
          @apply="applyModalFilters"
          @reset="resetModalFilters"
          @close="mobileDialog = false"
        />
      </VDialog>  

      <!-- Мобильная сортировка -->
      <IOSBottomSheet
        v-model="sortingSheet"
        title="Сортировка"
        :show-close-button="false"
        max-height="60vh"
        @close="sortingSheet = false"
      >
        <div class="ios-sorting-content">
          <VRadioGroup v-model="filtersState.sortBy" @update:model-value="applySorting">
            <VRadio
              v-for="option in sortingOptions"
              :key="option.value"
              :value="option.value"
              :label="option.label"
              color="primary"
            />
          </VRadioGroup>
        </div>
      </IOSBottomSheet>
      
      <!-- Быстрый фильтр -->
      <IOSBottomSheet
        v-model="quickFilterSheet"
        :title="quickFilterData?.filterKey ? (filters[quickFilterData.filterKey]?.label || quickFilterData.filterKey) : ''"
        :show-close-button="true"
        close-button-mode="reset"
        max-height="80vh"
        @close="quickFilterSheet = false"
        @reset="handleQuickFilterClear"
      >
        <QuickFilterContent
          v-if="quickFilterData"
          ref="quickFilterContentRef"
          :filter-key="quickFilterData.filterKey"
          :filter-data="filters[quickFilterData.filterKey]"
          :selected-values="filtersState.filters[quickFilterData.filterKey] || getDefaultValue(filters[quickFilterData.filterKey])"
          :focused-value="quickFilterData.focusedValue"
          @update="handleQuickFilterUpdate"
        />
        
        <template #actions>
          <VBtn 
            color="primary" 
            variant="flat"
            class="ios-apply-btn"
            @click="applyQuickFilter"
          >
            Применить
          </VBtn>
        </template>
      </IOSBottomSheet>
    </template>
    
    <template v-else>
      <!-- Десктопное модальное окно -->
      <VDialog
        v-model="desktopDialog"
        fullscreen
        transition="slide-x-reverse-transition"
        class="filters-dialog"
        :scrim="true"
      >
        <VCard class="filters-card">
          <div class="filters-content">
            <FiltersModal
              :filters="filters"
              :selected-filters="filtersState.filters"
              :is-mobile="false"
              @apply="applyModalFilters"
              @reset="resetModalFilters"
              @close="desktopDialog = false"
            />
          </div>
        </VCard>
      </VDialog>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'
import { debounce } from 'lodash-es'

const { mobile } = useDisplay()
const isMobile = computed(() => mobile.value)

const props = defineProps({
  filters: { type: Object, required: true },
  modelValue: { type: Object, default: () => ({}) },
  hasSearch: { type: Boolean, default: false },
  searchQuery: { type: String, default: '' },
  hasSorting: { type: Boolean, default: false },
  sortBy: { type: String, default: '' },
  sortingOptions: { type: Array, default: () => [] }
})

const emit = defineEmits(['update:modelValue', 'update:searchQuery', 'update:sortBy', 'filtersChange'])

const route = useRoute()
const router = useRouter()

// Локальная переменная для поиска
const searchInput = ref(props.searchQuery)

// Единое реактивное состояние
const filtersState = reactive({
  searchQuery: props.searchQuery,
  sortBy: props.sortBy ?? 'default',
  filters: { ...props.modelValue }
})

// Отслеживание предыдущего состояния для предотвращения дублирующих эмитов
const previousState = ref({
  searchQuery: '',
  sortBy: 'default',
  filters: {}
})

// Функция для сравнения состояний
const isStateChanged = () => {
  const prev = previousState.value
  const current = {
    searchQuery: filtersState.searchQuery,
    sortBy: filtersState.sortBy,
    filters: filtersState.filters
  }
  
  return (
    prev.searchQuery !== current.searchQuery ||
    prev.sortBy !== current.sortBy ||
    JSON.stringify(prev.filters) !== JSON.stringify(current.filters)
  )
}

// Функция для обновления предыдущего состояния
const updatePreviousState = () => {
  previousState.value = {
    searchQuery: filtersState.searchQuery,
    sortBy: filtersState.sortBy,
    filters: JSON.parse(JSON.stringify(filtersState.filters))
  }
}

// Дебаунсинг для отправки изменений с проверкой состояния
const debouncedEmitChanges = debounce(() => {
  if (isStateChanged()) {
    emit('filtersChange', {
      searchQuery: filtersState.searchQuery,
      sortBy: filtersState.sortBy,
      filters: filtersState.filters
    })
    updatePreviousState()
  }
}, 750)

// Единый метод обновления состояния
const updateFiltersState = (key, value) => {
  filtersState[key] = value
  updateURL()
  debouncedEmitChanges()
}

// Синхронизация с props
watch(() => props.searchQuery, (newValue) => { 
  if (filtersState.searchQuery !== newValue) {
    filtersState.searchQuery = newValue
  }
})
watch(() => props.sortBy, (newValue) => { 
  if (filtersState.sortBy !== newValue) {
    filtersState.sortBy = newValue
  }
})
watch(() => props.modelValue, (newValue) => { 
  if (JSON.stringify(filtersState.filters) !== JSON.stringify(newValue)) {
    filtersState.filters = { ...newValue }
  }
}, { deep: true })

// Обработчик ввода поиска с проверкой минимальной длины
const handleSearchInput = (value) => {
  searchInput.value = value
  
  // Применяем поиск только если длина >= 3 символов или поле пустое
  if (!value || value.length >= 3) {
    handleSearchChange(value)
  }
}

// Обработчики изменений
const handleSearchChange = (value) => updateFiltersState('searchQuery', value)
const handleSortChange = (value) => updateFiltersState('sortBy', value)

// Computed для проверки наличия быстрых фильтров для десктопа
const hasQuickFiltersDesktop = computed(() => {
  return Object.keys(quickFiltersDesktop.value).length > 0
})

// Computed свойства остаются без изменений
const quickFiltersDesktop = computed(() => 
  Object.fromEntries(Object.entries(props.filters).filter(([, filter]) => filter.quickFilterDesktop))
)

const quickFiltersMobile = computed(() => 
  Object.fromEntries(Object.entries(props.filters).filter(([, filter]) => filter.quickFilterMobile))
)

const hasHiddenFilters = computed(() => 
  Object.entries(props.filters).some(([, filter]) => !filter.quickFilterDesktop)
)

const hasSelections = computed(() => 
  Object.entries(filtersState.filters).some(([key, values]) => hasFilterSelections(key, values))
)

const hasAnySelections = computed(() => 
  hasSelections.value || filtersState.searchQuery || (filtersState.sortBy && filtersState.sortBy !== 'default')
)

const showChipsContainer = computed(() => 
  Object.keys(quickFiltersMobile.value).length > 0 || 
  Object.entries(filtersState.filters).some(([key, values]) => 
    hasFilterSelections(key, values) && !isQuickFilterMobile(key)
  )
)

const totalSelected = computed(() => {
  let total = 0
  Object.entries(filtersState.filters).forEach(([key, values]) => {
    if (hasFilterSelections(key, values)) {
      const filter = props.filters[key]
      if (filter?.type?.includes('range')) {
        total += Object.keys(values || {}).length
      } else {
        total += Array.isArray(values) ? values.length : 0
      }
    }
  })
  return total
})

// Модальные окна
const mobileDialog = ref(false)
const desktopDialog = ref(false)
const quickFilterSheet = ref(false)
const quickFilterData = ref(null)
const sortingSheet = ref(false)
const quickFilterContentRef = ref(null)

// Утилиты остаются без изменений
const getDefaultValue = (filter) => filter?.type?.includes('range') ? {} : []

const hasFilterSelections = (filterKey, values) => {
  const filter = props.filters[filterKey]
  if (filter?.type?.includes('range')) {
    return Object.keys(values || {}).length > 0
  }
  return Array.isArray(values) && values.length > 0
}

const getMobileChipLabel = (filterKey, filter) => {
  const values = filtersState.filters[filterKey]
  
  if (isQuickFilterMobile(filterKey)) {
    if (!values || !Array.isArray(values) || values.length === 0) {
      return filter.label || filterKey
    }
  }
  
  if (filter.type === 'toggle') {
    return filter.label || filterKey
  }
  
  if (values && values.length === 1) {
    let optionName = values[0]
    
    if (filter.type === 'tree') {
      const option = findTreeOption(filter.options, values[0])
      if (option) {
        optionName = option.name
      }
    } else if (filter.options) {
      const option = filter.options.find(opt => opt.value == values[0])
      if (option) {
        optionName = option.name
      }
    }
    
    return `${filter.label || filterKey}: ${optionName}`
  }
  
  if (values && values.length > 0) {
    return `${filter.label || filterKey} (${values.length})`
  }
  
  return filter.label || filterKey
}

const findTreeOption = (options, value) => {
  if (!options) return null
  
  for (const option of options) {
    if (option.value == value) return option
    if (option.children) {
      const found = findTreeOption(option.children, value)
      if (found) return found
    }
  }
  return null
}

const getFilterChips = (filterKey, values) => {
  const filter = props.filters[filterKey]
  const chips = []
  
  if (filter?.type?.includes('range')) {
    if (values.from || values.to) {
      const label = `${filter.label || filterKey}: ${values.from || '∞'} - ${values.to || '∞'}`
      chips.push({ key: 'range', label, type: 'range' })
    }
  } else if (filter?.type === 'tree') {
    if (values && values.length > 0) {
      if (values.length === 1) {
        const option = findTreeOption(filter.options, values[0])
        const optionName = option?.name || values[0]
        chips.push({ 
          key: values[0], 
          label: `${filter.label || filterKey}: ${optionName}`, 
          type: 'tree',
          value: values[0]
        })
      } else {
        chips.push({ 
          key: 'multiple', 
          label: `${filter.label || filterKey} (${values.length})`, 
          type: 'tree'
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
      const option = filter.options?.find(opt => opt.value === values[0])
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
  
  return chips
}

const isQuickFilterMobile = (filterKey) => props.filters[filterKey]?.quickFilterMobile === true

const isFilterActive = (filterKey) => {
  const values = filtersState.filters[filterKey]
  if (!values) return false
  const filterType = props.filters[filterKey]?.type || 'buttons'
  return filterType === 'range' ? Object.keys(values).length > 0 : Array.isArray(values) && values.length > 0
}

const getCurrentSortingLabel = () => {
  const defaultLabel = 'Сортировка'
  const option = props.sortingOptions.find(opt => opt.value === filtersState.sortBy)
  if (!option || option?.value ==='default') return defaultLabel
  return option?.label || 'По умолчанию'
}

// Методы фильтрации (объединены для использования updateFiltersState)
const updateFilterValue = (filterKey, updateFn) => {
  updateFn()
  updateFiltersState('filters', filtersState.filters)
}

const toggleFilterOption = (filterKey, value) => {
  updateFilterValue(filterKey, () => {
    const filter = props.filters[filterKey]
    const filterType = filter?.type || 'buttons'
    
    if (!filtersState.filters[filterKey]) {
      filtersState.filters[filterKey] = getDefaultValue(filter)
    }
    
    if (filterType === 'radio' || filter?.multiple === false) {
      const current = filtersState.filters[filterKey]
      filtersState.filters[filterKey] = current.includes(value) ? [] : [value]
    } else if (filterType === 'toggle') {
      filtersState.filters[filterKey] = filtersState.filters[filterKey].length > 0 ? [] : ['1']
    } else {
      const current = filtersState.filters[filterKey]
      const index = current.indexOf(value)
      if (index > -1) {
        current.splice(index, 1)
      } else {
        current.push(value)
      }
    }
  })
}

const toggleMobileSwitch = (filterKey) => {
  updateFilterValue(filterKey, () => {
    const current = filtersState.filters[filterKey] || []
    filtersState.filters[filterKey] = current.length > 0 ? [] : ['1']
  })
}

const updateRangeFilter = (filterKey, type, value) => {
  updateFilterValue(filterKey, () => {
    if (!filtersState.filters[filterKey]) {
      filtersState.filters[filterKey] = {}
    }
    
    if (value && value !== '') {
      filtersState.filters[filterKey][type] = value
    } else {
      delete filtersState.filters[filterKey][type]
    }
    
    if (Object.keys(filtersState.filters[filterKey]).length === 0) {
      delete filtersState.filters[filterKey]
    }
  })
}

const updateTreeFilter = (filterKey, selectedNodes) => {
  updateFilterValue(filterKey, () => {
    if (selectedNodes.length === 0) {
      delete filtersState.filters[filterKey]
    } else {
      filtersState.filters[filterKey] = selectedNodes
    }
  })
}

const clearFilter = (filterKey) => {
  updateFilterValue(filterKey, () => {
    delete filtersState.filters[filterKey]
  })
}

const resetFilters = () => {
  filtersState.searchQuery = ''
  filtersState.sortBy = 'default'
  filtersState.filters = {}
  
  emit('update:searchQuery', '')
  emit('update:sortBy', 'default')
  emit('update:modelValue', {})
  
  updateURL()
  
  // Принудительно эмитим изменения при сбросе
  emit('filtersChange', {
    searchQuery: '',
    sortBy: 'default',
    filters: {}
  })
  updatePreviousState()
}

const resetModalFilters = () => {
  filtersState.filters = {}
  mobileDialog.value = false
  desktopDialog.value = false
  updateFiltersState('filters', filtersState.filters)
}

// Модальные методы
const applyModalFilters = (filters) => {
  filtersState.filters = { ...filters }
  mobileDialog.value = false
  desktopDialog.value = false
  updateFiltersState('filters', filtersState.filters)
}

const openSortingSheet = () => { sortingSheet.value = true }

const applySorting = (value) => {
  updateFiltersState('sortBy', value)
  sortingSheet.value = false
}

// Quick filter методы
const openQuickFilter = (filterKey, chip) => {
  if (props.filters[filterKey]?.type === 'toggle') return
  
  quickFilterData.value = {
    filterKey,
    focusedValue: chip?.type === 'single' ? chip.value : null
  }
  quickFilterSheet.value = true
}

const applyQuickFilter = () => {
  if (quickFilterContentRef.value) {
    quickFilterContentRef.value.applyChanges()
  }
  quickFilterSheet.value = false
}

const handleQuickFilterUpdate = (filterKey, values) => {
  if (!hasFilterSelections(filterKey, values)) {
    delete filtersState.filters[filterKey]
  } else {
    filtersState.filters[filterKey] = values
  }
  updateFiltersState('filters', filtersState.filters)
}

const handleQuickFilterClear = () => {
  if (quickFilterData.value) {
    delete filtersState.filters[quickFilterData.value.filterKey]
    updateFiltersState('filters', filtersState.filters)
  }
  quickFilterSheet.value = false
}

const removeChipValue = (filterKey, chip) => {
  const filter = props.filters[filterKey]
  
  if (chip.type?.includes('range')) {
    delete filtersState.filters[filterKey]
  } else if (chip.type === 'toggle') {
    delete filtersState.filters[filterKey]
  } else if (chip.type === 'single') {
    const current = filtersState.filters[filterKey] || []
    const index = current.indexOf(chip.value)
    if (index > -1) {
      current.splice(index, 1)
    }
    if (current.length === 0) {
      delete filtersState.filters[filterKey]
    }
  } else {
    openQuickFilter(filterKey, chip)
    return
  }
  
  updateFiltersState('filters', filtersState.filters)
}

const updateURL = () => {
  const newQuery = {}
  
  Object.entries(route.query).forEach(([key, value]) => {
    const isFilterParam = Object.keys(props.filters).some(filterKey => 
      key === filterKey || 
      key.startsWith(`${filterKey}[]`) || 
      key === `${filterKey}_from` || 
      key === `${filterKey}_to`
    )
    
    if (!isFilterParam && key !== 'search' && key !== 'sort') {
      newQuery[key] = value
    }
  })
  
  Object.entries(filtersState.filters).forEach(([key, values]) => {
    const filter = props.filters[key]
    const filterType = filter?.type || 'buttons'
    
    if (filterType === 'range') {
      if (values.from) newQuery[`${key}_from`] = values.from
      if (values.to) newQuery[`${key}_to`] = values.to
    } else if (Array.isArray(values) && values.length > 0) {
      if (filter?.multiple === true) {
        newQuery[`${key}[]`] = values
      } else {
        newQuery[key] = values[0]
      }
    }
  })
  
  if (filtersState.searchQuery) {
    newQuery.search = filtersState.searchQuery
  }
  
  if (filtersState.sortBy && filtersState.sortBy !== 'default') {
    newQuery.sort = filtersState.sortBy
  }
  
  router.replace({ query: newQuery }).catch(() => {})
}

const loadFromURL = () => {
  const urlFilters = {}
  
  Object.entries(props.filters).forEach(([key, filter]) => {
    const filterType = filter?.type || 'buttons'
    
    if (filterType === 'range') {
      const from = route.query[`${key}_from`]
      const to = route.query[`${key}_to`]
      if (from || to) {
        urlFilters[key] = {}
        if (from) urlFilters[key].from = parseInt(from)
        if (to) urlFilters[key].to = parseInt(to)
      }
    } else {
      const arrayValues = route.query[`${key}[]`]
      const singleValue = route.query[key]
      
      let values = null
      if (arrayValues) {
        values = Array.isArray(arrayValues) ? arrayValues : [arrayValues]
      } else if (singleValue) {
        values = Array.isArray(singleValue) ? singleValue : [singleValue]
      }
      
      if (values) {
        urlFilters[key] = values
      }
    }
  })
  
  if (route.query.search) {
    filtersState.searchQuery = route.query.search
  }
  
  if (route.query.sort) {
    filtersState.sortBy = route.query.sort
  }
  
  filtersState.filters = { ...urlFilters }
  
  // Инициализируем предыдущее состояние при загрузке
  updatePreviousState()
  
  // Принудительно эмитим изменения после загрузки из URL
  emit('filtersChange', {
    searchQuery: filtersState.searchQuery,
    sortBy: filtersState.sortBy,
    filters: filtersState.filters
  })
}

// Флаг для отслеживания первой загрузки
const isFirstLoad = ref(true)

// Computed для проверки готовности фильтров
const filtersReady = computed(() => Object.keys(props.filters).length > 0)

// Watcher на готовность фильтров
watch(
  filtersReady,
  (ready) => {
    if (ready && isFirstLoad.value) {
      loadFromURL()
      isFirstLoad.value = false
    }
  },
  { immediate: true }
)

</script>


<style scoped>
/* Стили для диалога с эффектом offcanvas */
.filters-dialog :deep(.v-overlay__content) {
  position: fixed !important;
  right: 0 !important;
  top: 0 !important;
  bottom: 0 !important;
  left: auto !important;
  width: 400px !important;
  max-width: 400px !important;
  height: 100vh !important;
  max-height: 100vh !important;
  margin: 0 !important;
  border-radius: 0 !important;
  transform-origin: right center !important;
}

/* Стили для карточки */
.filters-card {
  height: 100vh !important;
  max-height: 100vh !important;
  border-radius: 0 !important;
  display: flex !important;
  flex-direction: column !important;
  overflow: hidden !important;
}

/* Контейнер с прокруткой */
.filters-content {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
}

/* Стили для скроллбара */
.filters-content::-webkit-scrollbar {
  width: 6px;
}

.filters-content::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
}

.filters-content::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.filters-content::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

/* Адаптивность для мобильных устройств */
@media (max-width: 599px) {
  .filters-dialog :deep(.v-overlay__content) {
    width: 100vw !important;
    max-width: 100vw !important;
    left: 0 !important;
  }
}

/* Альтернативный вариант с кастомной анимацией */
.filters-dialog.v-dialog--active :deep(.v-overlay__content) {
  animation: slideInFromRight 0.3s ease-out;
}

@keyframes slideInFromRight {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

/* Стили для затемнения фона */
.filters-dialog :deep(.v-overlay__scrim) {
  background: rgba(0, 0, 0, 0.5) !important;
}


.filters-wrapper {
  margin-bottom: 0.5rem;
  padding: 0 0.5rem;
}

/* Десктопные стили */
.desktop-row-1,
.desktop-row-2 {
  width: 100%;
}

.quick-filters-container {
  display: flex;
  gap: 8px;
  padding: 2px 0;
}

.quick-filter-btn {
  height: 40px !important;
  border-radius: 8px !important;
  font-weight: 500;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Второй ряд */
.desktop-row-2 {
  min-height: 40px;
}

.desktop-row-2 .v-field,
.desktop-row-2 .v-btn {
  height: 40px !important;
  border-radius: 8px !important;
}

.sorting-select {
  width: 200px;
  flex-shrink: 0;
}

.search-field {
  width: 320px;
  flex-shrink: 0;
}

.search-field :deep(.v-field__input) {
  min-height: 40px;
  padding-top: 0;
  padding-bottom: 0;
}

.sorting-btn,
.all-filters-btn,
.reset-btn {
  height: 40px !important;
  font-weight: 500;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Адаптивность */
@media (max-width: 1200px) {
  .search-field {
    width: 260px;
  }
  
  .sorting-select {
    width: 180px;
  }
}


/* Мобильные стили */
.mobile-filters {
  width: 100%;
  max-width: 100%;
}

.mobile-search {
  width: 100%;
  border-radius: 12px;
}

.mobile-search :deep(.v-field__input) {
  min-height: 48px;
  padding-top: 0;
  padding-bottom: 0;
}

.mobile-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 8px;
}

.mobile-sort-btn,
.mobile-reset-btn,
.mobile-filter-btn {
  min-width: 40px !important;
}

.mobile-filter-btn .v-btn__content {
  white-space: nowrap;
}

.chips-container {
  width: 100%;
  min-width: 0;
  margin-bottom: 0.5rem;
}

.chips-scroll-area {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 2px 0;
  scrollbar-width: none;
  -ms-overflow-style: none;
  -webkit-overflow-scrolling: touch;
  min-width: 0;
}

.chips-scroll-area::-webkit-scrollbar {
  display: none;
}

.filter-chip {
  flex-shrink: 0;
  white-space: nowrap;
}

/* Адаптивность для очень маленьких экранов */
@media (max-width: 360px) {
  .mobile-controls {
    gap: 6px;
  }
  
  .mobile-sort-btn,
  .mobile-reset-btn,
  .mobile-filter-btn {
    min-width: 36px !important;
    font-size: 0.875rem;
  }
  
  .mobile-filter-btn .v-icon {
    font-size: 16px !important;
  }
}

/* iOS специфичные стили */
.ios-sorting-content {
  padding: 16px 16px 48px 16px;
}

/* Адаптивность */
@media (max-width: 599px) {
  .mobile-actions {
    flex-direction: column;
  }
  
  .mobile-action-btn {
    width: 100%;
  }
}

/* Анимации */
.filter-chip {
  transition: transform 0.2s ease;
}

.filter-chip:active {
  transform: scale(0.95);
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
  background-color: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
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
  color: rgb(var(--v-theme-primary));
  background: rgb(var(--v-theme-on-primary));
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
