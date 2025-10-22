// mixins/useTableFilters.js
import { ref, reactive, computed, watch } from 'vue'

export function useTableFilters(config = {}) {
  const {
    defaultItemsPerPage = 20,
    autoLoad = true,
    debounceDelay = 300
  } = config

  // Состояние фильтров
  const selectedFilters = ref({})
  const searchQuery = ref('')
  const sortBy = ref('default')

  // Динамические данные с бэкенда
  const backendFilters = ref({})
  const backendSortings = ref([])

  // Состояние таблицы
  const isLoading = ref(false)
  const items = ref([])
  const itemsTotal = ref(0)
  
  const options = ref({
    page: 1,
    itemsPerPage: defaultItemsPerPage,
    sortBy: [],
    groupBy: [],
    search: '',
    total: 0,
  })

  // Computed свойства для использования данных с бэкенда
  const dynamicFilters = computed(() => {
    return Object.keys(backendFilters.value).length > 0 ? backendFilters.value : {}
  })

  const dynamicSortingOptions = computed(() => {
    return backendSortings.value.length > 0 ? backendSortings.value : [
      { value: 'default', label: 'По умолчанию' }
    ]
  })

  // Debounced функция для загрузки
  let loadTimeout = null
  const debouncedLoad = (loadFunction) => {
    clearTimeout(loadTimeout)
    loadTimeout = setTimeout(() => {
      loadFunction()
    }, debounceDelay)
  }

  // Обработчик изменения фильтров
  const handleFiltersChange = (changes, loadFunction) => {
    console.log('Все изменения фильтров:', changes)
    
    // Обновляем локальные переменные
    searchQuery.value = changes.searchQuery
    sortBy.value = changes.sortBy
    selectedFilters.value = changes.filters
    
    // Сбрасываем страницу при изменении фильтров
    options.value.page = 1
    
    // Применяем фильтры с debounce
    if (loadFunction) {
      debouncedLoad(loadFunction)
    }
  }

  // Получение параметров для API
  const getApiParams = (additionalParams = {}) => {
    return {
      page: options.value.page,
      limit: options.value.itemsPerPage,
      search: searchQuery.value,
      options: options.value,
      filters: selectedFilters.value,
      sort_by: sortBy.value,
      ...additionalParams
    }
  }

  // Обновление данных из ответа API
  const updateFromApiResponse = (response) => {
    items.value = response.items || []
    itemsTotal.value = response.total_count || 0
    
    // Сохраняем фильтры и сортировки с бэкенда
    if (response.filters) {
      backendFilters.value = response.filters
    }
    if (response.sortings) {
      backendSortings.value = response.sortings
    }
    
    // Обновляем опции таблицы
    options.value.page = response.page || options.value.page
    options.value.itemsPerPage = response.limit || options.value.itemsPerPage
  }

  // Сброс фильтров
  const resetFilters = () => {
    selectedFilters.value = {}
    searchQuery.value = ''
    sortBy.value = 'default'
    options.value.page = 1
  }

  // Проверка наличия активных фильтров
  const hasActiveFilters = computed(() => {
    return searchQuery.value !== '' || 
           sortBy.value !== 'default' || 
           Object.keys(selectedFilters.value).length > 0
  })

  // Универсальная функция загрузки
  const loadItems = async (store, additionalParams = {}) => {
    isLoading.value = true
    
    try {
      const params = getApiParams(additionalParams)
      const response = await store.fetchAll(params)
      updateFromApiResponse(response)
    } catch (error) {
      console.error('Ошибка загрузки данных:', error)
    } finally {
      isLoading.value = false
    }
  }

  // Watchers для автоматической загрузки при изменении пагинации
  const setupWatchers = (loadFunction) => {
    watch(() => options.value.itemsPerPage, (newVal, oldVal) => {
      if (newVal !== oldVal) {
        options.value.page = 1
        loadFunction()
      }
    })

    watch(() => options.value.page, (newVal, oldVal) => {
      if (newVal !== oldVal) {
        loadFunction()
      }
    })
  }

  return {
    // Состояние фильтров
    selectedFilters,
    searchQuery,
    sortBy,
    backendFilters,
    backendSortings,

    // Состояние таблицы
    isLoading,
    items,
    itemsTotal,
    options,

    // Computed
    dynamicFilters,
    dynamicSortingOptions,

    // Методы
    handleFiltersChange,
    getApiParams,
    updateFromApiResponse,
    resetFilters,
    loadItems,
    setupWatchers,
    debouncedLoad
  }
}
