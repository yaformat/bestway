<!-- resources/js/components/tables/ResortsTable.vue -->
<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">

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
            Создать курорт
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
            :image="item.main_photo?.url"
            size="32"
            class="me-3"
          >
            <VIcon icon="mdi-map-marker" />
          </VAvatar>
          <div>
            <div class="text-high-emphasis font-weight-medium">
              {{ item.name }}
            </div>
            <div class="text-sm text-medium-emphasis">
              {{ item.direction?.name }}
            </div>
          </div>
        </div>
      </template>
      
      <!-- Type -->
      <template #item.type="{ item }">
        <VChip size="small" :color="getTypeColor(item.type)">
          <VIcon start :icon="getTypeIcon(item.type)" />
          {{ getTypeLabel(item.type) }}
        </VChip>
      </template>
      
      <!-- Altitude -->
      <template #item.altitude="{ item }">
        <div class="text-sm">
          {{ item.altitude }} м
        </div>
      </template>
      
      <!-- Hotels count -->
      <template #item.hotels_count="{ item }">
        <VChip size="small" color="primary" variant="outlined">
          {{ item.hotels_count || 0 }} отелей
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
                  <VListItem @click="openHotelsDialog(item)">
                    <template #prepend>
                      <VIcon icon="mdi-bed" />
                    </template>
                    <VListItemTitle>Отели</VListItemTitle>
                  </VListItem>
                  <VListItem @click="copyResort(item)" :disabled="isCopying && copyingResortId === item.id">
                    <template #prepend>
                      <VProgressCircular
                        v-if="isCopying && copyingResortId === item.id"
                        indeterminate
                        size="20"
                        width="2"
                        class="mr-2"
                      />
                      <VIcon 
                        v-else
                        icon="mdi-content-copy" 
                      />
                    </template>
                    <VListItemTitle>
                      {{ isCopying && copyingResortId === item.id ? 'Копирование...' : 'Копировать' }}
                    </VListItemTitle>
                  </VListItem>
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
    
    <!-- Модальные окна -->
    <EntityDialog
      :isVisible="isCreateDialogVisible"
      title="Создать курорт"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      @onSubmit="createEntity"
    >
      <ResortCreateForm :model="newEntity" />
    </EntityDialog>
    
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать курорт"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      @onSubmit="updateEntity"
    >
      <ResortEditForm :model="editEntity" />
    </EntityDialog>
    
    <EntityDialog
      :isVisible="isHotelsDialogVisible"
      title="Отели курорта"
      submitButtonText="Закрыть"
      @update:isVisible="isHotelsDialogVisible = $event"
      size="large"
    >
      <ResortHotelsForm :model="selectedResort" />
    </EntityDialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useResortStore } from '@/stores/resortStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useResortStore()

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
    title: 'Направление',
    key: 'direction.name',
    sortable: true,
  },
  {
    title: 'Тип',
    key: 'type',
    sortable: true,
  },
  {
    title: 'Высота',
    key: 'altitude',
    sortable: true,
  },
  {
    title: 'Отели',
    key: 'hotels_count',
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
      with: ['direction', 'main_photo']
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
} = useEntityActions(itemsStore, 'курорт', getItems)

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

// Дополнительные диалоги
const isHotelsDialogVisible = ref(false)
const selectedResort = ref(null)
const isCopying = ref(false)
const copyingResortId = ref(null)

const props = defineProps({
  isActive: Boolean
})

// Методы
const getTypeColor = (type) => {
  const colors = {
    'ski': 'primary',
    'beach': 'success',
    'spa': 'warning',
    'eco': 'info',
    'cultural': 'error',
    'adventure': 'purple',
  }
  return colors[type] || 'grey'
}

const getTypeIcon = (type) => {
  const icons = {
    'ski': 'mdi-ski',
    'beach': 'mdi-beach',
    'spa': 'mdi-spa',
    'eco': 'mdi-leaf',
    'cultural': 'mdi-bank',
    'adventure': 'mdi-hiking',
  }
  return icons[type] || 'mdi-map-marker'
}

const getTypeLabel = (type) => {
  const labels = {
    'ski': 'Горнолыжный',
    'beach': 'Пляжный',
    'spa': 'SPA',
    'eco': 'Эко',
    'cultural': 'Культурный',
    'adventure': 'Приключенческий',
  }
  return labels[type] || type
}

const toggleActive = async (id, status) => {
  try {
    await itemsStore.toggleActive(id, status)
    await getItems()
  } catch (error) {
    console.error('Ошибка смены статуса:', error)
  }
}

const openHotelsDialog = (resort) => {
  selectedResort.value = resort
  isHotelsDialogVisible.value = true
}

const copyResort = async (resort) => {
  if (!confirm(`Скопировать курорт "${resort.name}"?`)) {
    return
  }
  
  isCopying.value = true
  copyingResortId.value = resort.id
  
  try {
    await itemsStore.copy(resort.id)
    
    if (window.showNotification) {
      window.showNotification(`Курорт "${resort.name}" успешно скопирован`, 'success')
    } else {
      console.log(`Курорт "${resort.name}" успешно скопирован`)
    }
    
    await getItems()
  } catch (error) {
    console.error('Ошибка копирования курорта:', error)
    
    if (window.showNotification) {
      window.showNotification('Ошибка при копировании курорта', 'error')
    } else {
      console.error('Ошибка при копировании курорта')
    }
  } finally {
    isCopying.value = false
    copyingResortId.value = null
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