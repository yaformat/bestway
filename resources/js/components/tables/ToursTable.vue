<!-- resources/js/components/tables/ToursTable.vue -->
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
            Создать тур
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
            <VIcon icon="mdi-tour" />
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
      
      <!-- Duration -->
      <template #item.duration_days="{ item }">
        <VChip size="small" color="primary" variant="outlined">
          {{ item.duration_days }} дн.
        </VChip>
      </template>
      
      <!-- Price -->
      <template #item.price_from="{ item }">
        <div class="text-high-emphasis font-weight-medium">
          {{ formatCurrency(item.price_from) }}
        </div>
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
                  <VListItem @click="openProgramDialog(item)">
                    <template #prepend>
                      <VIcon icon="mdi-map" />
                    </template>
                    <VListItemTitle>Программа</VListItemTitle>
                  </VListItem>
                  <VListItem @click="copyTour(item)">
                    <template #prepend>
                      <VIcon icon="mdi-content-copy" />
                    </template>
                    <VListItemTitle>Копировать тур</VListItemTitle>
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
      title="Создать тур"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      @onSubmit="createEntity"
    >
      <TourCreateForm :model="newEntity" />
    </EntityDialog>
    
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать тур"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      @onSubmit="updateEntity"
    >
      <TourEditForm :model="editEntity" />
    </EntityDialog>
    
    <EntityDialog
      :isVisible="isProgramDialogVisible"
      title="Программа тура"
      submitButtonText="Сохранить"
      @update:isVisible="isProgramDialogVisible = $event"
      @onSubmit="saveProgram"
      size="large"
    >
      <TourProgramForm :model="selectedTour" />
    </EntityDialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useTourStore } from '@/stores/tourStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useTourStore()

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
    title: 'Длительность',
    key: 'duration_days',
    sortable: true,
  },
  {
    title: 'Цена от',
    key: 'price_from',
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
} = useEntityActions(itemsStore, 'тур', getItems)

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
const isProgramDialogVisible = ref(false)
const selectedTour = ref(null)
const isCopying = ref(false)

const props = defineProps({
  isActive: Boolean
})

// Методы
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const toggleActive = async (id, status) => {
  try {
    await itemsStore.toggleActive(id, status)
    await getItems()
  } catch (error) {
    console.error('Ошибка смены статуса:', error)
  }
}

const openProgramDialog = (tour) => {
  selectedTour.value = tour
  isProgramDialogVisible.value = true
}

const saveProgram = async () => {
  try {
    await itemsStore.saveProgram(selectedTour.value.id, selectedTour.value.program)
    isProgramDialogVisible.value = false
    await getItems()
  } catch (error) {
    console.error('Ошибка сохранения программы:', error)
  }
}

// Метод копирования тура
const copyTour = async (tour) => {
  if (!confirm(`Скопировать тур "${tour.name}"?`)) {
    return
  }
  
  isCopying.value = true
  try {
    const response = await itemsStore.copy(tour.id)
    
    // Показываем уведомление об успешном копировании
    if (window.showNotification) {
      window.showNotification(`Тур "${tour.name}" успешно скопирован`, 'success')
    } else {
      // Fallback если нет глобального уведомления
      console.log(`Тур "${tour.name}" успешно скопирован`)
    }
    
    // Обновляем список
    await getItems()
    
    // Опционально: открываем диалог редактирования скопированного тура
    if (response?.data?.id) {
      // Можно сразу открыть скопированный тур для редактирования
      // openEditDialog(response.data)
    }
  } catch (error) {
    console.error('Ошибка копирования тура:', error)
    
    if (window.showNotification) {
      window.showNotification('Ошибка при копировании тура', 'error')
    } else {
      console.error('Ошибка при копировании тура')
    }
  } finally {
    isCopying.value = false
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