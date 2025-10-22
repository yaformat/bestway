<!-- resources/js/components/tables/HotelsTable.vue -->
<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">
          <VTextField 
            v-model="searchQuery" 
            density="compact" 
            placeholder="Поиск отеля" 
          />
        </VCol>
        <VCol cols="12" sm="8" class="text-end text-right">
          <ArchiveToggleButton
            :isTrashedView="isTrashedView"
            :trashedCount="trashedCount"
            :toggleTrashedView="() => toggleTrashedView(getItems)"
            @update:isTrashedView="isTrashedView = $event"
          />
           <VBtn size="small" class="ml-2" :to="{ name: 'hotel-add' }">
                <VIcon start icon="mdi-plus" />
                Создать отель
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
            <VIcon icon="mdi-domain" />
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
      
      <!-- Stars -->
      <template #item.stars="{ item }">
        <div class="d-flex align-center">
          <VIcon 
            v-for="star in 5" 
            :key="star" 
            :color="star <= item.stars ? 'warning' : 'grey-lighten-2'"
            icon="mdi-star"
            size="16"
          />
        </div>
      </template>
      
      <!-- Rating -->
      <template #item.rating="{ item }">
        <VChip size="small" :color="getRatingColor(item.rating)">
          {{ item.rating }}
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
                  <VListItem @click="openRoomsDialog(item)">
                    <template #prepend>
                      <VIcon icon="mdi-bed" />
                    </template>
                    <VListItemTitle>Номера</VListItemTitle>
                  </VListItem>
                  <VListItem @click="openPricesDialog(item)">
                    <template #prepend>
                      <VIcon icon="mdi-currency-usd" />
                    </template>
                    <VListItemTitle>Цены</VListItemTitle>
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
    
    <!-- Модальное окно для создания -->
    <EntityDialog
      :isVisible="isCreateDialogVisible"
      title="Создать отель"
      submitButtonText="Создать"
      @update:isVisible="isCreateDialogVisible = $event"
      @onSubmit="createEntity"
    >
      <HotelCreateForm :model="newEntity" />
    </EntityDialog>
    
    <!-- Модальное окно для редактирования -->
    <EntityDialog
      :isVisible="isEditDialogVisible"
      title="Редактировать отель"
      submitButtonText="Сохранить"
      @update:isVisible="isEditDialogVisible = $event"
      @onSubmit="updateEntity"
    >
      <HotelEditForm :model="editEntity" />
    </EntityDialog>
    
    <!-- Модальное окно для управления номерами -->
    <EntityDialog
      :isVisible="isRoomsDialogVisible"
      title="Управление номерами"
      submitButtonText="Сохранить"
      @update:isVisible="isRoomsDialogVisible = $event"
      @onSubmit="saveRooms"
      size="large"
    >
      <HotelRoomsForm :model="selectedHotel" />
    </EntityDialog>
    
    <!-- Модальное окно для управления ценами -->
    <EntityDialog
      :isVisible="isPricesDialogVisible"
      title="Управление ценами"
      submitButtonText="Сохранить"
      @update:isVisible="isPricesDialogVisible = $event"
      @onSubmit="savePrices"
      size="large"
    >
      <HotelPricesForm :model="selectedHotel" />
    </EntityDialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useHotelStore } from '@/stores/hotelStore'
import { useTableFilters } from '@/mixins/useTableFilters'
import useArchive from '@/mixins/useArchive'
import useEntityActions from '@/mixins/useEntityActions'

const itemsStore = useHotelStore()

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
    title: 'Звездность',
    key: 'stars',
    sortable: true,
  },
  {
    title: 'Рейтинг',
    key: 'rating',
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
} = useEntityActions(itemsStore, 'отель', getItems)

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
const isRoomsDialogVisible = ref(false)
const isPricesDialogVisible = ref(false)
const selectedHotel = ref(null)

const props = defineProps({
  isActive: Boolean
})

// Методы
const getRatingColor = (rating) => {
  if (rating >= 4.5) return 'success'
  if (rating >= 3.5) return 'warning'
  if (rating >= 2.5) return 'info'
  return 'error'
}

const toggleActive = async (id, status) => {
  try {
    await itemsStore.toggleActive(id, status)
    await getItems()
  } catch (error) {
    console.error('Ошибка смены статуса:', error)
  }
}

const openRoomsDialog = (hotel) => {
  selectedHotel.value = hotel
  isRoomsDialogVisible.value = true
}

const openPricesDialog = (hotel) => {
  selectedHotel.value = hotel
  isPricesDialogVisible.value = true
}

const saveRooms = async () => {
  try {
    await itemsStore.saveRooms(selectedHotel.value.id, selectedHotel.value.rooms)
    isRoomsDialogVisible.value = false
    await getItems()
  } catch (error) {
    console.error('Ошибка сохранения номеров:', error)
  }
}

const savePrices = async () => {
  try {
    await itemsStore.savePrices(selectedHotel.value.id, selectedHotel.value.prices)
    isPricesDialogVisible.value = false
    await getItems()
  } catch (error) {
    console.error('Ошибка сохранения цен:', error)
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