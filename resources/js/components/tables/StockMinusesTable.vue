<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">
        </VCol>
        <VCol cols="12" sm="8" class="text-end text-right">
          <VBtn @click="isCreateDialogVisible = true" class="ml-2">Создать Списание</VBtn>
        </VCol>
      </VRow>
    </VCardText>
    <Filters
      v-model="selectedFilters"
      :filters="dynamicFilters"
      :has-search="false"
      :has-sorting="true"
      :search-query="searchQuery"
      :sort-by="sortBy"
      :sorting-options="dynamicSortingOptions"
      @filtersChange="(changes) => handleFiltersChange(changes, getItems)"
    />
    <VDataTableServer
      :loading="isLoading"
      :items="items"
      :headers="headers"
      class="rounded-0"
    >
      <!-- Date -->
      <template #item.date="{ item }">
        <FormattedDate :date="item.created_at" />
      </template>
      <!-- Stock -->
      <template #item.stock="{ item }">
        <div>
          <div v-if="item.stock"><span class="text-sm">{{ item.stock.name }}</span></div>
        </div>
      </template>
      <!-- User -->
      <template #item.user="{ item }">
        <div>
          <div v-if="item.user">
            <UserNameWithImage :user="item.user" nameKey="full_name" />
          </div>
        </div>
      </template>
      <!-- total_price -->
      <template #item.total_price="{ item }">
          <span class="text-sm" v-if="item.total_price">{{ item.total_price.display }}</span>
      </template>
      <!-- status -->
      <template #item.status="{ item }">
        <StatusBadge :status="item.status" />
      </template>
      <!-- Actions -->
      <template #item.actions="{ item }">
        <div class="text-no-wrap">
          <VBtn :to="{ name: 'stock-minus-id', params: { id: item.id } }" size="small" variant="outlined">
            <VIcon icon="mdi-unfold-more-vertical" />
          </VBtn>
        </div>
      </template>
      <template #bottom>
        <!--<TableFooter :options="options" :total="itemsTotal" />-->
      </template>
    </VDataTableServer>

    <div class="text-center my-4">
      <VBtn @click="loadMore" :disabled="isLoading || items.length >= itemsTotal">
        Загрузить еще
      </VBtn>
    </div>

    <!-- Модальное окно для создания -->
    <EntityDialog
      :isVisible="isCreateDialogVisible"
      title="Создать списание"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      :use-bottom-sheet="true"

      :onSubmit="createEntity"
    >
      <StockMinusCreateForm :model="newEntity" />
    </EntityDialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useStockMinusStore } from '@/stores/stockMinusStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useStockMinusStore()

// Используем mixin для фильтров и таблицы
const {
  selectedFilters,
  searchQuery,
  sortBy,
  dynamicFilters,
  dynamicSortingOptions,
  isLoading,
  items,
  itemsTotal,
  options,
  handleFiltersChange,
  getApiParams,
  updateFromApiResponse,
  resetFilters,
  setupWatchers
} = useTableFilters({
  defaultItemsPerPage: 20,
  autoLoad: false,
  debounceDelay: 300
})

const headers = [
  {
    title: 'Дата',
    key: 'date',
  },
  {
    title: 'Склад',
    key: 'stock',
  },
  {
    title: 'Пользователь',
    key: 'user',
  },
  {
    title: 'Сумма',
    key: 'total_price',
  },
  {
    title: 'Статус',
    key: 'status',
  },
  {
    title: 'Действия',
    key: 'actions',
    sortable: false,
  },
]

const props = defineProps({
  isActive: Boolean
})

// Основная функция загрузки данных
const getItems = async () => {
  isLoading.value = true

  try {
    const params = getApiParams()
    const response = await itemsStore.fetchAll(params)
    
    // Для "Загрузить еще" - добавляем к существующим элементам
    if (options.value.page === 1) {
      items.value = response.items || []
    } else {
      items.value = [...items.value, ...(response.items || [])]
    }
    
    // Обновляем остальные данные
    itemsTotal.value = response.total_count || 0
    updateFromApiResponse(response)
    
  } catch (error) {
    console.error('Ошибка загрузки инвентаризаций:', error)
  } finally {
    isLoading.value = false
  }
}

// Функция "Загрузить еще"
const loadMore = () => {
  options.value.page += 1
  getItems()
}


const {
  isCreateDialogVisible,
  isEditDialogVisible,
  newEntity,
  editEntity,
  createEntity,
  updateEntity,
  openEditDialog
} = useEntityActions(itemsStore, 'списание', getItems)

// Настройка watchers (но переопределяем логику для "Загрузить еще")
const setupCustomWatchers = () => {
  watch(() => options.value.itemsPerPage, (newVal, oldVal) => {
    if (newVal !== oldVal) {
      options.value.page = 1
      getItems()
    }
  })

  // Для этого компонента не нужен watcher на page, 
  // так как мы используем "Загрузить еще" вместо пагинации
}

// Watchers для props
watch(() => props.isActive, (newVal) => {
  if (newVal) {
    // Сбрасываем на первую страницу при активации
    options.value.page = 1
    getItems()
  }
})

// Watcher для сброса страницы при изменении фильтров
watch([selectedFilters, searchQuery, sortBy], () => {
  options.value.page = 1
}, { deep: true })

onMounted(() => {
  setupCustomWatchers()
  if (props.isActive) {
    getItems()
  }
})


watch(() => props.isActive, (newVal) => {
  if (newVal) {
    getItems()
  }
})

watch(() => options.value.itemsPerPage, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    options.value.page = 1
    getItems()
  }
})

watch(() => options.value.page, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    getItems()
  }
})

onMounted(() => {
  if (props.isActive) {
    getItems()
  }
})
</script>