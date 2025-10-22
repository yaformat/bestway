<!-- src/resources/js/components/tables/ResourceCategoriesTree.vue -->
<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">
          <VTextField v-model="searchQuery" density="compact" placeholder="Поиск Категории" />
        </VCol>
        <VCol cols="12" sm="8" class="text-end text-right">
          <ArchiveToggleButton
            :isTrashedView="isTrashedView"
            :trashedCount="trashedCount"
            :toggleTrashedView="() => toggleTrashedView(getItems)"
            @update:isTrashedView="isTrashedView = $event"
          />
          <VBtn @click="isCreateDialogVisible = true" class="ml-2">Создать Категорию {{ resourceType }}</VBtn>
        </VCol>
      </VRow>
    </VCardText>
    <div v-if="isLoading">
      <TableSkeletonLoader :rows="5" :headers="headers" />
    </div>
    <div v-else>
      <div class="table">
        <div class="table-header">
          <div v-for="header in headers" :key="header.key" class="table-cell"><div>{{ header.title }}</div></div>
        </div>
          <ResourceCategoryItem
            v-for="category in treeData" :key="category.id"
            :node="category"
            :isTrashedView="isTrashedView"
            @edit="openEditDialog"
            @archive="confirmArchive"
            @restore="confirmRestore"
          />
      </div>
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

    <!-- Модальное окно для создания -->
    <EntityDialog
      :isVisible="isCreateDialogVisible"
      title="Создать категорию"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      :onSubmit="createEntity"
    >
      <ResourceCategoryCreateForm :model="newEntity" :categories="treeData" />
    </EntityDialog>

    <!-- Модальное окно для редактирования -->
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать категорию"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      :onSubmit="updateEntity"
    >
      <ResourceCategoryEditForm :model="editEntity" :categories="treeData" />
    </EntityDialog>

  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useResourceCategoryStore } from '@/stores/resourceCategoryStore'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'
import { buildCategoryTree } from '@/utils/treeBuilder'
import ResourceCategoryItem from '../ResourceCategoryItem.vue'

const itemsStore = useResourceCategoryStore()

const isLoading = ref(false)
const searchQuery = ref('')
const treeData = ref([])
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
    title: 'Кол-во РЕСУРСОВ',
    key: 'resources_count',
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
    //limit: options.value.itemsPerPage,
    limit: -1,
    q: searchQuery.value,
    options: options.value,
    type: props.resourceType,
  }

  if (onlyTrashed) {
    params.only_trashed = true
  }

  itemsStore.fetchAll(params).then(response => {
    treeData.value = buildCategoryTree(response.items)
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
} = useEntityActions(itemsStore, 'категория', getItems)

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
  isActive: Boolean,
  resourceType: { 
    type: String,
    default: 'misc'
  },
})


newEntity.value.type = props.resourceType

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

watch(() => props.resourceType, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    isTrashedView.value = false;
    getItems(isTrashedView.value)
  }

  newEntity.value.type = newVal
})

onMounted(() => {
  if (props.isActive) {
    getItems()
  }
})
</script>

<style>
.table {
  display: flex;
  flex-direction: column;
}

.table-header, .table-row {
  display: flex;
  width:100%;
}

.table-cell {
  flex: 1;
  padding: 10px;
  border-bottom: 1px solid #e0e0e0;
}

.table-header .table-cell {
    display: flex;
    flex: 1;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 0.75rem;
    font-weight: 500 !important;
    padding: 0 16px;
    letter-spacing: 0.17px !important;
    text-transform: uppercase !important;
    height: 54px;
    background: rgb(var(--v-table-header-background)) !important;
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity)) !important;
}

.actions {
  display: flex;
  gap: 10px;
}

.tree-item-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.tree-item-row {
  position: relative;
  padding-left: calc(var(--level) * 20px + 16px); /* отступ + место для линии */
  display: flex;
  align-items: center;
  min-height: 32px;
  margin-left: 16px;
}

/* Контейнер для линий */
.tree-lines {
  position: absolute;
  top: 0;
  left: calc((var(--level) - 1) * 20px);
  width: 20px;
  height: 100%;
}

/* Вертикальная линия */
.tree-lines::before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  left: 50%;
  width: 1px;
  background-color: #ccc;
}

/* Горизонтальная линия */
.tree-lines::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 1px;
  background-color: #ccc;
}

/* Убрать вертикальную линию у последнего потомка */
.tree-item-row.is-last .tree-lines::before {
  height: 50%;
}


</style>
