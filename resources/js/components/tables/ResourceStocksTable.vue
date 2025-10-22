<template>
  <div>
    <Filters
      v-model="selectedFilters"
      :filters="dynamicFilters"
      :has-search="true"
      :has-sorting="true"
      :search-query="searchQuery"
      :sort-by="sortBy"
      :sorting-options="dynamicSortingOptions"
      @filtersChange="(changes) => handleFiltersChange(changes, getItems)"
    />
    <div v-if="isLoading">
      <TableSkeletonLoader :rows="5" :headers="headers" />
    </div>
    <VDataTableServer
      v-else
      v-model:items-per-page="options.itemsPerPage"
      v-model:page="options.page"
      :loading="isLoading"
      :items="items"
      :items-length="itemsTotal"
      :headers="headers"
      class="rounded-0"
      @update:options="options = $event"
    >
        <!-- Name -->
        <template #item.name="{ item }">
          <span class="text-sm">
            <ResourceInfoDialog :id="item.id" :name="item.name" />
          </span>
        </template>

        <!-- Category -->
        <template #item.category="{ item }">
          <span class="text-sm" v-if="item.category">{{ item.category.name }}</span>
        </template>
        <template #item.stock="{ item }">
          <span class="text-sm" v-if="item.stocks[0]">{{ item.stocks[0].name }}</span>
        </template>
        <template #item.avg_price="{ item }">
          <span class="text-sm" v-if="item.avg_price">{{ item.avg_price.display }}</span>
        </template>
        <template #item.last_price="{ item }">
          <span class="text-sm" v-if="item.last_price">{{ item.last_price.display }}</span>
        </template>

        <!-- Total Value -->
        <template #item.stocks_total_value="{ item }">
          <span class="text-sm">{{ item.stocks_total_value.display }}</span>
        </template>

        <!-- Total Price -->
        <template #item.stocks_total_price="{ item }">
          <span class="text-sm">{{ item.stocks_total_price.display }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="text-no-wrap">
            <VBtn @click="openStockHistoryDialog(item.id, item.stocks)" size="small" variant="outlined">
              <VIcon icon="mdi-history" />
            </VBtn>
          </div>
        </template>
        
      <template #bottom>
        <TableFooter :options="options" :total="itemsTotal" />
      </template>
    </VDataTableServer>

    <ResourceStockHistoryDialog :resourceId="selectedResourceId" :stocks="selectedStocks" v-model:showModal="showStockHistoryDialog" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useResourceStore } from '@/stores/resourceStore'
import { useTableFilters } from '@/mixins/useTableFilters'

const itemsStore = useResourceStore()


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
    title: 'Название',
    key: 'name',
    sortable: false,
  },
  {
    title: 'Категория',
    key: 'category',
    sortable: false,
  },
  {
    title: 'Склад',
    key: 'stock',
    sortable: false,
  },
  {
    title: 'Средняя цена',
    key: 'avg_price',
    sortable: false,
  },
  {
    title: 'Цена последней закупки',
    key: 'last_price',
    sortable: false,
  },
  {
    title: 'Остаток',
    key: 'stocks_total_value',
    sortable: false,
  },
  {
    title: 'Сумма',
    key: 'stocks_total_price',
    sortable: false,
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
    const params = getApiParams({
      type: props.resourceType
    })

    const response = await itemsStore.fetchAll(params)
    updateFromApiResponse(response)
  } catch (error) {
    console.error('Ошибка загрузки ресурсов:', error)
  } finally {
    isLoading.value = false
  }
}

// Настройка watchers
setupWatchers(getItems)

// Watchers для props
watch(() => props.isActive, (newVal) => {
  if (newVal) {
    getItems()
  }
})

onMounted(() => {
  if (props.isActive) {
    getItems()
  }
})



const showStockHistoryDialog = ref(false);
const selectedResourceId = ref(null);
const selectedStocks = ref([]);

const openStockHistoryDialog = (resourceId, stocks) => {
  selectedResourceId.value = resourceId;
  selectedStocks.value = stocks;
  showStockHistoryDialog.value = true;
  console.log('openStockHistoryDialog');
  console.log(selectedResourceId.value+': '+showStockHistoryDialog.value);
};
</script>
