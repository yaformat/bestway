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
      <TableSkeletonLoader :rows="5" :headers="headers" :hasNameWithImage="true" />
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
        <ResourceNameWithImage :item="item" />
      </template>
      <template #item.category="{ item }">
        <div>
          <div v-if="item.category"><span class="text-sm">{{ item.category.name }}</span></div>
        </div>
      </template>
      <template #item.product_items_count="{ item }">
        <span class="text-sm">{{ item.product_items_count }}</span>
      </template>
      <template #item.shelf_life_days="{ item }">
        <ShelfLifeDaysBadge :days="item.shelf_life_days" :required="item.shelf_life_days_required" />
      </template>
      <template #item.losses="{ item }">
        <FormattedLosses :losses="item.losses" />
      </template>
      <template #item.tech_cards_count="{ item }">
        <span class="text-sm">{{ item.tech_cards_count }}</span>
      </template>
      <!-- Actions -->
      <template #item.actions="{ item }">
        <div class="text-no-wrap">
          <div v-if="item.is_trashed">
            <VBtn @click="confirmRestore(item.id)" size="small" color="secondary" variant="outlined">
              <VIcon icon="mdi-restore" />
              Восстановить
            </VBtn>
          </div>
          <div v-else>
            <VBtn :to="{ name: 'resource-resourceType-edit-id', params: { resourceType: item.type, id: item.id } }" size="small" variant="outlined">
              <VIcon icon="mdi-pencil-outline" />
            </VBtn>
            <VBtn icon variant="text" size="small" color="medium-emphasis">
              <VIcon size="24" icon="mdi-dots-vertical" />
              <VMenu activator="parent">
                <VList>
                  <VListItem @click="confirmArchive(item.id)">
                    <template #prepend>
                      <VIcon icon="mdi-archive-outline" />
                    </template>
                    <VListItemTitle>Архивировать</VListItemTitle>
                  </VListItem>
                </VList>
              </VMenu>
            </VBtn>
          </div>
        </div>
      </template>
      <template #bottom>
        <TableFooter :options="options" :total="itemsTotal" />
      </template>
    </VDataTableServer>

    <!-- Диалоги архивирования -->
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

    <!-- Диалоги копирования -->
    <CopyResourceDialogs
      :show-copy-dialog="showCopyDialog"
      :show-copy-success-dialog="showCopySuccessDialog"
      :copy-loading="copyLoading"
      :copy-form-ref="copyFormRef"
      :copy-form-data="copyFormData"
      :resource-types="resourceTypes"
      :copy-form-rules="copyFormRules"
      @confirm-copy="() => confirmCopy(itemsStore, getItems)"
      @go-to-resource="goToNewResource"
      @close-copy-dialog="closeCopyDialog"
      @close-copy-success-dialog="closeCopySuccessDialog"
      @update:show-copy-dialog="val => showCopyDialog = val"
      @update:show-copy-success-dialog="val => showCopySuccessDialog = val"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useResourceStore } from '@/stores/resourceStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import { useCopyResource } from '@/mixins/useCopyResource'
import useArchive from '@/mixins/useArchive'

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

// Используем mixin для копирования
const {
  showCopyDialog,
  showCopySuccessDialog,
  copyLoading,
  copyFormRef,
  copyFormData,
  resourceTypes,
  copyFormRules,
  openCopyDialog,
  closeCopyDialog,
  confirmCopy,
  goToNewResource,
  closeCopySuccessDialog
} = useCopyResource({
  routeName: 'resource-resourceType-edit-id'
})

// Архивирование
const {
  successDialogVisible,
  restoreSuccessDialogVisible,
  errorDialogVisible,
  dialogsRef,
  confirmArchive,
  confirmRestore,
  archiveItem,
  restoreItem
} = useArchive()

const headers = [
{
    title: 'Название',
    key: 'name', sortable: false,
  },
  {
    title: 'Категория',
    key: 'category', sortable: false,
  },
  {
    title: 'Товары',
    key: 'product_items_count', sortable: false,
  },
  {
    title: 'Срок хранения',
    key: 'shelf_life_days', sortable: false,
  },
  {
    title: 'Потери',
    key: 'losses',
    sortable: false,
  },
  {
    title: 'Тех. карты',
    key: 'tech_cards_count',
  },
  {
    title: 'Действия',
    key: 'actions',
    sortable: false,
  },
]

const props = defineProps({
  isActive: Boolean,
  resourceType: { 
    type: String,
    default: 'misc'
  },
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

watch(() => props.resourceType, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    resetFilters()
    getItems()
  }
})

onMounted(() => {
  if (props.isActive) {
    getItems()
  }
})
</script>
