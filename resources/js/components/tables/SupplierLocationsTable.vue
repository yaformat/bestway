<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">
          <VTextField v-model="searchQuery" density="compact" placeholder="Поиск локации" />
        </VCol>
        <VCol cols="12" sm="8" class="text-end text-right">
          <ArchiveToggleButton
            :isTrashedView="isTrashedView"
            :trashedCount="trashedCount"
            :toggleTrashedView="() => toggleTrashedView(getItems)"
            @update:isTrashedView="isTrashedView = $event"
          />
          <VBtn @click="isCreateDialogVisible = true" class="ml-2">Создать Локацию</VBtn>
        </VCol>
      </VRow>
    </VCardText>
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
      <!-- suppliers_count -->
      <template #item.suppliers_count="{ item }">
        <span class="text-sm">{{ item.suppliers_count }}</span>
      </template>
      <!-- Actions -->
      <template #item.actions="{ item }">
        <div class="text-no-wrap">
          <div v-if="isTrashedView">
            <VBtn @click="confirmRestore(item.id)" size="small" variant="outlined">
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
      title="Создать локацию"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      :onSubmit="createEntity"
    >
      <SupplierLocationCreateForm :model="newEntity" />
    </EntityDialog>

    <!-- Модальное окно для редактирования -->
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать локацию"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      :onSubmit="updateEntity"
    >
      <SupplierLocationEditForm :model="editEntity" />
    </EntityDialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useSupplierLocationStore } from '@/stores/supplierLocationStore'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useSupplierLocationStore()

const isLoading = ref(false)
const searchQuery = ref('')
const items = ref([])
const itemsTotal = ref(0)
const trashedCount = ref(0)

const options = ref({
  page: 1,
  itemsPerPage: 20,
  sortBy: [],
  groupBy: [],
  search: '',
  total: 0,
})

const headers = [
  {
    title: 'Название',
    key: 'name',
  },
  {
    title: 'Кол-во поставщиков',
    key: 'suppliers_count',
  },
  {
    title: 'Действия',
    key: 'actions',
    sortable: false,
  },
]

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
    items.value = response.items
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
  isCreateDialogVisible,
  isEditDialogVisible,
  newEntity,
  editEntity,
  createEntity,
  updateEntity,
  openEditDialog
} = useEntityActions(itemsStore, 'локация', getItems)

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
