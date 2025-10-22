<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="4">
        </VCol>
        <VCol cols="12" sm="8" class="text-end text-right">
          <VBtn variant="outlined" class="ml-2">Создать Склад</VBtn>
          <VBtn class="ml-2">Создать Цех</VBtn>
        </VCol>
      </VRow>
    </VCardText>
    <div v-if="isLoading">
      <TableSkeletonLoader :rows="5" :headers="headers" :hasNameWithImage="false" :actionButtons="0" />
    </div>
    <VTable 
      v-else
      class="app-data-table" 
      density="comfortable"
    >
        <thead>
          <tr>
            <th v-for="header in headers" :key="header.key" :style="{ width: header.width, textAlign: header.align || 'left' }">
              {{ header.title }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <!-- ID -->
            <!-- <td style="width: 80px;">
              <span class="text-sm">{{ item.id }}</span>
            </td> -->

            <!-- Name -->
            <td>
              <span class="text-sm font-weight-medium">{{ item.name }}</span>
            </td>

            <!-- Workshop -->
            <td>
              <div v-if="item.workshop" class="mt-2">
                <VChip size="small" color="primary" variant="tonal">
                  {{ item.workshop.name }}
                </VChip>
              </div>
            </td>

            <!-- Primary Stock -->
            <td class="text-center">
              <VBtn
                :color="item.is_primary ? 'success' : 'grey-lighten-1'"
                :variant="item.is_primary ? 'flat' : 'outlined'"
                size="small"
                icon
                :loading="loadingStates.primary === item.id"
                :disabled="item.is_primary"
                @click="confirmTogglePrimary(item)"
              >
                <VIcon>mdi-check</VIcon>
              </VBtn>
            </td>

            <!-- Production Stock -->
            <td class="text-center">
              <VBtn
                :color="item.is_production ? 'success' : 'grey-lighten-1'"
                :variant="item.is_production ? 'flat' : 'outlined'"
                size="small"
                icon
                :loading="loadingStates.production === item.id"
                :disabled="item.is_production"
                @click="confirmToggleProduction(item)"
              >
                <VIcon>mdi-check</VIcon>
              </VBtn>
            </td>

            <!-- Semi Finished -->
            <td class="text-center">
              <VBtn
                :color="item.is_semi_finished ? 'success' : 'grey-lighten-1'"
                :variant="item.is_semi_finished ? 'flat' : 'outlined'"
                size="small"
                icon
                :loading="loadingStates.semi_finished === item.id"
                :disabled="item.is_semi_finished"
                @click="confirmToggleSemiFinished(item)"
              >
                <VIcon>mdi-check</VIcon>
              </VBtn>
            </td>

            <!-- Actions -->
            <td class="text-center" style="width: 100px;">
              <VBtn
                v-if="item.is_deletable"
                color="error"
                variant="text"
                size="small"
                icon
                :loading="loadingStates.delete === item.id"
                @click="confirmDeleteStock(item)"
              >
                <VIcon>mdi-delete</VIcon>
              </VBtn>
            </td>
          </tr>
        </tbody>
      </VTable>

    <!-- Confirm Dialogs -->
    <ConfirmDialog
      ref="primaryConfirmDialog"
      :message="confirmMessages.primary"
      confirm-text="Подтвердить"
      cancel-text="Отмена"
      confirm-color="primary"
      icon="!"
      icon-color="warning"
      @confirm="handleTogglePrimary"
      @cancel="cancelAction"
    />

    <ConfirmDialog
      ref="productionConfirmDialog"
      :message="confirmMessages.production"
      confirm-text="Подтвердить"
      cancel-text="Отмена"
      confirm-color="primary"
      icon="!"
      icon-color="warning"
      @confirm="handleToggleProduction"
      @cancel="cancelAction"
    />

    <ConfirmDialog
      ref="semiFinishedConfirmDialog"
      :message="confirmMessages.semiFinished"
      confirm-text="Подтвердить"
      cancel-text="Отмена"
      confirm-color="primary"
      icon="!"
      icon-color="warning"
      @confirm="handleToggleSemiFinished"
      @cancel="cancelAction"
    />

    <ConfirmDialog
      ref="deleteConfirmDialog"
      :message="confirmMessages.delete"
      confirm-text="Удалить"
      cancel-text="Отмена"
      confirm-color="error"
      icon="🗑️"
      icon-color="error"
      @confirm="handleDeleteStock"
      @cancel="cancelAction"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useStockStore } from '@/stores/stockStore'

const itemsStore = useStockStore()

const isLoading = ref(false)
const searchQuery = ref('')
const items = ref([])
const itemsTotal = ref(0)
const trashedCount = ref(0)
const loadingStates = ref({
  primary: null,
  production: null,
  semi_finished: null,
  delete: null
})

// Refs для диалогов подтверждения
const primaryConfirmDialog = ref(null)
const productionConfirmDialog = ref(null)
const semiFinishedConfirmDialog = ref(null)
const deleteConfirmDialog = ref(null)

// Текущий элемент для действия
const currentActionItem = ref(null)

// Сообщения для подтверждения
const confirmMessages = ref({
  primary: '',
  production: '',
  semiFinished: '',
  delete: ''
})

const options = ref({
  page: 1,
  itemsPerPage: 20,
  sortBy: [],
  groupBy: [],
  search: '',
  total: 0,
})

const headers = [
  // {
  //   title: 'ID',
  //   key: 'id',
  //   width: '80px',
  //   sortable: false,
  // },
  {
    title: 'Склад',
    key: 'name',
    sortable: false,
  },
  {
    title: 'Цех производства',
    key: 'workshop',
    sortable: false,
  },
  {
    title: 'Для поставок сырья',
    key: 'is_primary',
    align: 'center',
    sortable: false,
    width: '120px',
  },
  {
    title: 'Для Готовой Продукции',
    key: 'is_production',
    align: 'center',
    sortable: false,
    width: '140px',
  },
  {
    title: 'Для Полуфабрикатов',
    key: 'is_semi_finished',
    align: 'center',
    sortable: false,
    width: '140px',
  },
  {
    title: '',
    key: 'actions',
    align: 'center',
    sortable: false,
    width: '100px',
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
    items.value = response.data || response.items
    itemsTotal.value = response.total_count || response.data?.length || 0
    isLoading.value = false
    
    options.value.page = response.page || 1
    options.value.itemsPerPage = response.limit || 20
    trashedCount.value = response.trashed_count || 0
  }).catch(error => {
    console.error(error)
    isLoading.value = false
  })
}

// Функции подтверждения действий
const confirmTogglePrimary = (item) => {
  // Если свойство уже установлено - ничего не делаем
  if (item.is_primary) {
    return
  }
  
  currentActionItem.value = item
  confirmMessages.value.primary = `Сделать склад "${item.name}" основным складом для поставок сырья?`
  primaryConfirmDialog.value.open()
}

const confirmToggleProduction = (item) => {
  // Если свойство уже установлено - ничего не делаем
  if (item.is_production) {
    return
  }
  
  currentActionItem.value = item
  confirmMessages.value.production = `Сделать склад "${item.name}" складом для готовой продукции?`
  productionConfirmDialog.value.open()
}

const confirmToggleSemiFinished = (item) => {
  // Если свойство уже установлено - ничего не делаем
  if (item.is_semi_finished) {
    return
  }
  
  currentActionItem.value = item
  confirmMessages.value.semiFinished = `Сделать склад "${item.name}" складом для полуфабрикатов?`
  semiFinishedConfirmDialog.value.open()
}

const confirmDeleteStock = (item) => {
  currentActionItem.value = item
  confirmMessages.value.delete = `Вы уверены, что хотите удалить склад "${item.name}"?`
  deleteConfirmDialog.value.open()
}

// Обработчики подтвержденных действий
const handleTogglePrimary = async () => {
  if (!currentActionItem.value) return
  
  loadingStates.value.primary = currentActionItem.value.id
  
  try {
    await itemsStore.togglePrimary(currentActionItem.value.id)
    await getItems()
  } catch (error) {
    console.error('Ошибка при переключении основного склада:', error)
  } finally {
    loadingStates.value.primary = null
    currentActionItem.value = null
  }
}

const handleToggleProduction = async () => {
  if (!currentActionItem.value) return
  
  loadingStates.value.production = currentActionItem.value.id
  
  try {
    await itemsStore.toggleProduction(currentActionItem.value.id)
    await getItems()
  } catch (error) {
    console.error('Ошибка при переключении производственного склада:', error)
  } finally {
    loadingStates.value.production = null
    currentActionItem.value = null
  }
}

const handleToggleSemiFinished = async () => {
  if (!currentActionItem.value) return
  
  loadingStates.value.semi_finished = currentActionItem.value.id
  
  try {
    await itemsStore.toggleSemiFinished(currentActionItem.value.id)
    await getItems()
  } catch (error) {
    console.error('Ошибка при переключении полуфабрикатного склада:', error)
  } finally {
    loadingStates.value.semi_finished = null
    currentActionItem.value = null
  }
}

const handleDeleteStock = async () => {
  if (!currentActionItem.value) return
  
  loadingStates.value.delete = currentActionItem.value.id
  
  try {
    await itemsStore.delete(currentActionItem.value.id)
    await getItems()
  } catch (error) {
    console.error('Ошибка при удалении склада:', error)
  } finally {
    loadingStates.value.delete = null
    currentActionItem.value = null
  }
}

const cancelAction = () => {
  currentActionItem.value = null
}

const props = defineProps({
  isActive: Boolean
})

watch(() => props.isActive, (newVal) => {
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
