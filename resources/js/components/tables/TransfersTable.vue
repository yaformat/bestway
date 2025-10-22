<!-- resources/js/components/tables/TransfersTable.vue -->
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
            Создать трансфер
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
          <VAvatar size="32" class="me-3">
            <VIcon icon="mdi-car" />
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
      
      <!-- Route -->
      <template #item.route="{ item }">
        <div class="text-sm">
          <div>{{ item.from_location }}</div>
          <div class="text-medium-emphasis">→ {{ item.to_location }}</div>
        </div>
      </template>
      
      <!-- Vehicle -->
      <template #item.vehicle_type="{ item }">
        <VChip size="small" :color="getVehicleColor(item.vehicle_type)">
          <VIcon start :icon="getVehicleIcon(item.vehicle_type)" />
          {{ getVehicleLabel(item.vehicle_type) }}
        </VChip>
      </template>
      
      <!-- Price -->
      <template #item.price_from="{ item }">
        <div class="text-high-emphasis font-weight-medium">
          {{ formatCurrency(item.price_from) }}
        </div>
      </template>
      
      <!-- Duration -->
      <template #item.duration_minutes="{ item }">
        <VChip size="small" color="primary" variant="outlined">
          {{ formatDuration(item.duration_minutes) }}
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
    
    <!-- Модальные окна -->
    <EntityDialog
      :isVisible="isCreateDialogVisible"
      title="Создать трансфер"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      @onSubmit="createEntity"
    >
      <TransferCreateForm :model="newEntity" />
    </EntityDialog>
    
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать трансфер"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      @onSubmit="updateEntity"
    >
      <TransferEditForm :model="editEntity" />
    </EntityDialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useTransferStore } from '@/stores/transferStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useTransferStore()

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
    title: 'Маршрут',
    key: 'route',
    sortable: false,
  },
  {
    title: 'Транспорт',
    key: 'vehicle_type',
    sortable: true,
  },
  {
    title: 'Цена от',
    key: 'price_from',
    sortable: true,
  },
  {
    title: 'Длительность',
    key: 'duration_minutes',
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
      with: ['direction']
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
} = useEntityActions(itemsStore, 'трансфер', getItems)

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
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatDuration = (minutes) => {
  if (minutes < 60) {
    return `${minutes} мин.`
  }
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return mins > 0 ? `${hours} ч ${mins} мин.` : `${hours} ч.`
}

const getVehicleColor = (type) => {
  const colors = {
    'sedan': 'primary',
    'suv': 'success',
    'minivan': 'warning',
    'bus': 'error',
    'van': 'info',
  }
  return colors[type] || 'grey'
}

const getVehicleIcon = (type) => {
  const icons = {
    'sedan': 'mdi-car',
    'suv': 'mdi-car-sports',
    'minivan': 'mdi-car-estate',
    'bus': 'mdi-bus',
    'van': 'mdi-van-utility',
  }
  return icons[type] || 'mdi-car'
}

const getVehicleLabel = (type) => {
  const labels = {
    'sedan': 'Седан',
    'suv': 'Внедорожник',
    'minivan': 'Минивэн',
    'bus': 'Автобус',
    'van': 'Фургон',
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