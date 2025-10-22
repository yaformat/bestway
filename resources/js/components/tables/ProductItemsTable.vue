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
        <TableNameWithImage :item="item" />
      </template>
      <template #item.sku="{ item }">
        <div><span class="text-sm">{{ item.sku }}</span></div>
      </template>
      <template #item.resource="{ item }">
        <ResourceNameWithImage v-if="item.resource" :item="item.resource" />
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
            <VBtn @click="openEditDialog(item)" size="small" variant="outlined">
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

    <!-- Модальное окно для создания -->
    <EntityDialog
      :isVisible="isCreateDialogVisible"
      title="Создать товар"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      :onSubmit="createEntity"
    >
      <ProductItemCreateForm :model="newEntity" />
    </EntityDialog>

    <!-- Модальное окно для редактирования -->
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать товар"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      :onSubmit="updateEntity"
    >
      <ProductItemEditForm :model="editEntity" />
    </EntityDialog>

  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useProductItemStore } from '@/stores/productItemStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useProductItemStore()

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


const props = defineProps({
  isActive: Boolean
})

const headers = [
  {
    title: 'Название',
    key: 'name',
    sortable: false,
  },
  {
    title: 'SKU',
    key: 'sku',
    sortable: false,
  },
  {
    title: 'Связь с ресурсом',
    key: 'resource',
    sortable: false,
  },
  {
    title: 'Действия',
    key: 'actions',
    sortable: false,
  },
]


const getItems = async () => {
  isLoading.value = true

  try {
    const params = getApiParams({
    })

    const response = await itemsStore.fetchAll(params)
    updateFromApiResponse(response)
  } catch (error) {
    console.error('Ошибка загрузки:', error)
  } finally {
    isLoading.value = false
  }

}

const {
  isCreateDialogVisible,
  isEditDialogVisible,
  newEntity,
  editEntity,
  createEntity,
  updateEntity,
  openEditDialog
} = useEntityActions(itemsStore, 'товар', getItems)

watch(() => props.isActive, (newVal) => {
  if (newVal) {
    getItems()
  }
})

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
</script>
