<template>
  <div class="production-list">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center">
        <span>Производство</span>
        <VBtn
          color="primary"
          prepend-icon="mdi-plus"
          :to="{ name: 'production-create' }"
        >
          Создать производство
        </VBtn>
      </VCardTitle>

      <VCardText>
        <!-- Фильтры -->
        <VRow class="mb-4">
          <VCol cols="12" md="3">
            <VTextField
              v-model="filters.search"
              label="Поиск"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              clearable
              @input="debouncedSearch"
            />
          </VCol>
          <VCol cols="12" md="3">
            <VTextField
              v-model="filters.date"
              type="date"
              label="Дата"
              variant="outlined"
              density="compact"
              clearable
              @update:model-value="loadProductions"
            />
          </VCol>
          <VCol cols="12" md="3">
            <VSelect
              v-model="filters.status"
              :items="statusOptions"
              label="Статус"
              variant="outlined"
              density="compact"
              clearable
              @update:model-value="loadProductions"
            />
          </VCol>
          <VCol cols="12" md="3">
            <VBtn
              variant="outlined"
              @click="resetFilters"
            >
              Сбросить фильтры
            </VBtn>
          </VCol>
        </VRow>

        <!-- Список производств -->
        <div v-if="loading" class="text-center py-8">
          <VProgressCircular indeterminate />
          <div class="mt-2">Загрузка производств...</div>
        </div>

        <div v-else-if="productions.length === 0" class="text-center text-grey py-8">
          <VIcon size="64" class="mb-4">mdi-calendar-blank</VIcon>
          <div class="text-h6">Производства не найдены</div>
          <div class="text-body-2">
            {{ hasFilters ? 'Попробуйте изменить параметры поиска' : 'Создайте первое производство для начала работы' }}
          </div>
        </div>

        <VRow v-else>
          <VCol
            v-for="production in productions"
            :key="production.id"
            cols="12"
            md="6"
            lg="4"
          >
            <VCard
              variant="outlined"
              class="production-card"
              hover
            >
              <VCardTitle class="d-flex justify-space-between align-center">
                <span>{{ formatDate(production.date) }}</span>
                <VChip
                  :color="getStatusColor(production.status)"
                  size="small"
                >
                  {{ production.status_label }}
                </VChip>
              </VCardTitle>

              <VCardText>
                <div class="production-stats mb-3">
                  <div class="d-flex justify-space-between mb-2">
                    <span class="text-body-2">Периодов:</span>
                    <span class="font-weight-medium">{{ production.periods_count }}</span>
                  </div>
                  <div class="d-flex justify-space-between mb-2">
                    <span class="text-body-2">Всего блюд:</span>
                    <span class="font-weight-medium">{{ production.total_dishes }}</span>
                  </div>
                  <div class="d-flex justify-space-between mb-2">
                    <span class="text-body-2">Выполнено:</span>
                    <span class="font-weight-medium">{{ production.completed_dishes }}</span>
                  </div>
                </div>

                <!-- Прогресс бар -->
                <div class="mb-3">
                  <div class="d-flex justify-space-between align-center mb-1">
                    <span class="text-body-2">Прогресс</span>
                    <span class="text-body-2">{{ production.progress_percentage }}%</span>
                  </div>
                  <VProgressLinear
                    :model-value="production.progress_percentage"
                    :color="getProgressColor(production.progress_percentage)"
                    height="6"
                    rounded
                  />
                </div>

                <!-- Периоды -->
                <div class="periods-preview">
                  <div class="text-body-2 mb-2">Периоды:</div>
                  <div class="d-flex flex-wrap gap-1">
                    <VChip
                      v-for="period in production.periods.slice(0, 3)"
                      :key="period.id"
                      size="x-small"
                      variant="outlined"
                    >
                      {{ period.time }} {{ period.name ? `: ${period.name}` : '' }}
                    </VChip>
                    <VChip
                      v-if="production.periods.length > 3"
                      size="x-small"
                      variant="outlined"
                      color="grey"
                    >
                      +{{ production.periods.length - 3 }}
                    </VChip>
                  </div>
                </div>
              </VCardText>

              <VCardActions>
                <VBtn
                  :to="{ name: 'production-view', params: { id: production.id } }"
                  color="primary"
                  size="small"
                >
                  Просмотр
                </VBtn>
                <VBtn
                  :to="{ name: 'production-edit', params: { id: production.id } }"
                  variant="outlined"
                  size="small"
                  :disabled="production.status === 'completed'"
                >
                  Редактировать
                </VBtn>
                <VSpacer />
                <VMenu>
                  <template #activator="{ props }">
                    <VBtn
                      icon="mdi-dots-vertical"
                      size="small"
                      variant="text"
                      v-bind="props"
                    />
                  </template>
                  <VList>
                    <VListItem @click="duplicateProduction(production)">
                      <VListItemTitle>Дублировать</VListItemTitle>
                    </VListItem>
                    <VListItem
                      @click="deleteProduction(production)"
                      :disabled="production.status === 'in_progress'"
                    >
                      <VListItemTitle>Удалить</VListItemTitle>
                    </VListItem>
                  </VList>
                </VMenu>
              </VCardActions>
            </VCard>
          </VCol>
        </VRow>

        <!-- Пагинация -->
        <div v-if="pagination.total > pagination.limit" class="mt-6">
          <VPagination
            v-model="pagination.page"
            :length="Math.ceil(pagination.total / pagination.limit)"
            @update:model-value="loadProductions"
          />
        </div>
      </VCardText>
    </VCard>

    <!-- Диалог подтверждения удаления -->
    <VDialog v-model="deleteDialog.show" max-width="400">
      <VCard>
        <VCardTitle>Подтверждение удаления</VCardTitle>
        <VCardText>
          Вы уверены, что хотите удалить производство от {{ formatDate(deleteDialog.production?.date) }}?
          <br><br>
          <span class="text-warning">Это действие нельзя отменить.</span>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="outlined" @click="deleteDialog.show = false">
            Отмена
          </VBtn>
          <VBtn color="error" @click="confirmDelete" :loading="deleteDialog.loading">
            Удалить
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProductionStore } from '@/stores/productionStore'
import { debounce } from 'lodash-es'

const router = useRouter()
const productionStore = useProductionStore()

// Реактивные данные
const loading = ref(false)
const productions = ref([])

const filters = reactive({
  search: '',
  date: '',
  status: null
})

const pagination = reactive({
  page: 1,
  limit: 12,
  total: 0
})

const deleteDialog = reactive({
  show: false,
  production: null,
  loading: false
})

// Опции статусов
const statusOptions = [
  { title: 'Запланировано', value: 'planned' },
  { title: 'В процессе', value: 'in_progress' },
  { title: 'Завершено', value: 'completed' },
  { title: 'Отменено', value: 'cancelled' }
]

// Вычисляемые свойства
const hasFilters = computed(() => {
  return filters.search || filters.date || filters.status
})

// Методы
const loadProductions = async () => {
  try {
    loading.value = true
    
    const params = {
      page: pagination.page,
      limit: pagination.limit,
      search: filters.search || undefined,
      filters: {
        date: filters.date || undefined,
        status: filters.status || undefined
      }
    }

    const response = await productionStore.fetchAll(params)
    
    productions.value = response.items || []
    pagination.total = response.total || 0
    
  } catch (error) {
    console.error('Ошибка загрузки производств:', error)
  } finally {
    loading.value = false
  }
}

const debouncedSearch = debounce(() => {
  pagination.page = 1
  loadProductions()
}, 500)

const resetFilters = () => {
  filters.search = ''
  filters.date = ''
  filters.status = null
  pagination.page = 1
  loadProductions()
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    weekday: 'long'
  })
}

const getStatusColor = (status) => {
  const colors = {
    'planned': 'orange',
    'in_progress': 'blue',
    'completed': 'green',
    'cancelled': 'red'
  }
  return colors[status] || 'grey'
}

const getProgressColor = (percentage) => {
  if (percentage === 100) return 'success'
  if (percentage >= 50) return 'primary'
  if (percentage >= 25) return 'warning'
  return 'error'
}

const duplicateProduction = async (production) => {
  try {
    // Создаем копию производства на завтра
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    
    const duplicateData = {
      date: tomorrow.toISOString().split('T')[0],
      periods: production.periods.map(period => ({
        name: period.name,
        time: period.time,
        dishes: period.dishes.map(dish => ({
          tech_card_id: dish.tech_card_id,
          quantity: dish.quantity
        }))
      }))
    }
    
    await productionStore.create(duplicateData)
    loadProductions()
    
  } catch (error) {
    console.error('Ошибка дублирования производства:', error)
  }
}

const deleteProduction = (production) => {
  deleteDialog.production = production
  deleteDialog.show = true
}

const confirmDelete = async () => {
  try {
    deleteDialog.loading = true
    await productionStore.delete(deleteDialog.production.id)
    deleteDialog.show = false
    loadProductions()
  } catch (error) {
    console.error('Ошибка удаления производства:', error)
  } finally {
    deleteDialog.loading = false
  }
}

onMounted(() => {
  loadProductions()
})
</script>

<style scoped>
.production-card {
  height: 100%;
  transition: all 0.3s ease;
}

.production-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.production-stats {
  border-left: 3px solid rgb(var(--v-theme-primary));
  padding-left: 12px;
}

.periods-preview {
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding-top: 12px;
}
</style>
