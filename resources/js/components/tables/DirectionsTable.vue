DirectionsTable<!-- resources/js/components/tables/DirectionsTable.vue -->
<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">
          <VTextField 
            v-model="searchQuery" 
            density="compact" 
            placeholder="Поиск направления" 
          />
        </VCol>
        <VCol cols="12" sm="8" class="text-end text-right">
          <ArchiveToggleButton
            :isTrashedView="isTrashedView"
            :trashedCount="trashedCount"
            :toggleTrashedView="() => toggleTrashedView(getItems)"
            @update:isTrashedView="isTrashedView = $event"
          />
          <VBtn @click="isCreateDialogVisible = true" class="ml-2">
            <VIcon start icon="mdi-plus" />
            Создать направление
          </VBtn>
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
        <div class="d-flex align-center">
          <VAvatar
            :image="item.photo?.url"
            size="32"
            class="me-3"
          >
            <VIcon icon="mdi-map" />
          </VAvatar>
          <div>
            <div class="text-high-emphasis font-weight-medium">
              {{ item.name }}
            </div>
            <div class="text-sm text-medium-emphasis">
              {{ item.slug }}
            </div>
          </div>
        </div>
      </template>
      
      <!-- Country -->
      <template #item.country="{ item }">
        <VChip size="small" :color="getCountryColor(item.country)">
          {{ item.country }}
        </VChip>
      </template>
      
      <!-- Excursions count -->
      <template #item.excursions_count="{ item }">
        <VChip size="small" color="primary" variant="outlined">
          {{ item.excursions_count || 0 }}
        </VChip>
      </template>
      
      <!-- Status -->
      <template #item.is_active="{ item }">
        <VSwitch
          :model-value="item.is_active"
          @update:model-value="toggleActive(item.id, $event)"
          density="compact"
          inset
          hide-details
        />
      </template>
      
      <!-- Actions -->
      <template #item.actions="{ item }">
        <div class="text-no-wrap">
          <div v-if="isTrashedView">
            <VBtn 
              @click="confirmRestore(item.id)" 
              size="small" 
              color="secondary" 
              variant="outlined"
            >
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
      title="Создать направление"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      @onSubmit="createEntity"
    >
      <DirectionCreateForm :model="newEntity" />
    </EntityDialog>
    
    <!-- Модальное окно для редактирования -->
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать направление"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      @onSubmit="updateEntity"
    >
      <DirectionEditForm :model="editEntity" />
    </EntityDialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useDirectionStore } from '@/stores/directionStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useDirectionStore()

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
    sortable: true,
  },
  {
    title: 'Страна',
    key: 'country',
    sortable: true,
  },
  {
    title: 'Экскурсий',
    key: 'excursions_count',
    sortable: true,
  },
  {
    title: 'Статус',
    key: 'is_active',
    sortable: true,
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
      with: ['photo']
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
} = useEntityActions(itemsStore, 'направление', getItems)

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

// Методы
const getCountryColor = (country) => {
  const colors = {
    'KG': 'green',
    'KZ': 'blue',
    'UZ': 'purple',
  }
  return colors[country] || 'grey'
}

const toggleActive = async (id, status) => {
  try {
    await itemsStore.toggleActive(id, status)
    await getItems()
  } catch (error) {
    console.error('Ошибка смены статуса:', error)
  }
}

// Watchers
watch(() => props.isActive, (newVal) => {
  isTrashedView.value = false
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