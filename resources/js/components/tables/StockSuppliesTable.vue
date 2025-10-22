<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">
        </VCol>
        <VCol cols="12" sm="8" class="text-end text-right">
          <VBtn :to="{ name: 'stock-supply-add' }" class="ml-2">Создать Поставку</VBtn>
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
          <VBtn :to="{ name: 'stock-supply-id', params: { id: item.id } }" size="small" variant="outlined">
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

    <ArchiveDialogs
      ref="dialogsRef"
      :success-dialog-visible="successDialogVisible"
      :restore-success-dialog-visible="restoreSuccessDialogVisible"
      :error-dialog-visible="errorDialogVisible"
      :on-archive="() => archiveItem(itemsStore, getItems)"
      :on-restore="() => restoreItem(itemsStore, getItems)"
      @update:success-dialog-visible="val => successDialogVisible = val"
      @update:restore-success-dialog-visible="val => restoreSuccessDialogVisible = val"
      @update:error-dialog-visible="val => errorDialogVisible = val"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useStockSupplyStore } from '@/stores/stockSupplyStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useArchive from '@/mixins/useArchive'

const itemsStore = useStockSupplyStore()


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

const loadMore = () => {
  options.value.page += 1
  getItems()
}

const getItems = (onlyTrashed = false) => {
  isLoading.value = true

  const params = {
    page: options.value.page,
    limit: options.value.itemsPerPage,
    q: searchQuery.value,
    options: options.value,
  }

  if (onlyTrashed) {
    params.only_trashed = true
  }

  itemsStore.fetchAll(params).then(response => {
    if (options.value.page === 1) {
      items.value = response.items
    } else {
      items.value = [...items.value, ...response.items]
    }
    itemsTotal.value = response.total_count
    isLoading.value = false
    
    options.value.page = response.page
    options.value.itemsPerPage = response.limit
    trashedCount.value = response.trashed_count
  }).catch(error => {
    console.error(error)
    isLoading.value = false
  })

}

const {
  isTrashedView,
  confirmDialogVisible,
  confirmRestoreDialogVisible,
  successDialogVisible,
  restoreSuccessDialogVisible,
  errorDialogVisible,
  dialogsRef,
  toggleTrashedView,
  confirmArchive,
  confirmRestore,
  archiveItem,
  restoreItem
} = useArchive()

const props = defineProps({
  isActive: Boolean
})

watch(() => props.isActive, (newVal) => {
  isTrashedView.value = false
  if (newVal) {
    getItems()
  }
})

watch(() => options.value.itemsPerPage, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    options.value.page = 1
    getItems(isTrashedView.value)
  }
})

watch(() => options.value.page, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    getItems(isTrashedView.value)
  }
})

onMounted(() => {
  if (props.isActive) {
    getItems()
  }
})
</script>
