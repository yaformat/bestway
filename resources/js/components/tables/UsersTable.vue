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
      @filtersChange="handleFiltersChange"
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
      <template #item.full_name="{ item }">
        <UserNameWithImage :user="item" nameKey="full_name" />
      </template>
      <!-- email -->
      <template #item.email="{ item }">
        <span class="text-sm">{{ item.email }}</span>
      </template>
      <!-- activity_at -->
      <template #item.activity_at="{ item }">
        <FormattedDate :date="item.activity_at" />
      </template>
      <!-- Actions -->
      <template #item.active="{ item }">
        <VSwitch v-model="item.is_active" @change="toggleActive(item.id, item.is_active)" />
      </template>
      <template #item.actions="{ item }">
        <div class="text-no-wrap">
          <VBtn :to="{ name: 'users', params: { id: item.id } }" size="small" variant="outlined">
            <VIcon icon="mdi-pencil-outline" />
          </VBtn>
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
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { VDataTableServer } from 'vuetify/components'
import { useUserStore } from '@/stores/userStore'
import useArchive from '@/mixins/useArchive'

const itemsStore = useUserStore()

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
    title: 'ФИО',
    key: 'full_name',
  },
  {
    title: 'Email',
    key: 'email',
  },
  {
    title: 'Дата активности',
    key: 'activity_at',
    sortable: false,
  },
  {
    title: 'Активен',
    key: 'active',
    sortable: false,
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

const toggleActive = async (id, status) => {
  try {
    await itemsStore.toggleActive(id, status)
    //getItems()
  } catch (error) {
    console.error('Failed to update is_active:', error)
  }
}

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

//Грузим список без условий
getItems()

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
