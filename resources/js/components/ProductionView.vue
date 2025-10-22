<template>
  <div class="production-view">
    <div v-if="loading" class="text-center py-8">
      <VProgressCircular indeterminate size="64" />
      <div class="mt-4">Загрузка производства...</div>
    </div>

    <div v-else-if="production">
      <!-- Заголовок -->
      <VCard class="mb-6">
        <VCardTitle class="d-flex justify-space-between align-center">
          <div>
            <h2 class="text-h4">Производство от {{ formatDate(production.date) }}</h2>
            <div class="text-body-1 text-medium-emphasis mt-1">
              {{ formatDateWithWeekday(production.date) }}
            </div>
          </div>
          <div class="d-flex align-center gap-3">
            <VChip
              :color="getStatusColor(production.status)"
              size="large"
            >
              {{ production.status_label }}
            </VChip>
            <VBtn
              :to="{ name: 'production-edit', params: { id: production.id } }"
              color="primary"
              prepend-icon="mdi-pencil"
              :disabled="production.status === 'completed'"
            >
              Редактировать
            </VBtn>
          </div>
        </VCardTitle>

        <VCardText>
          <!-- Общая статистика -->
          <VRow>
            <VCol cols="12" md="3">
              <VCard variant="tonal" color="primary">
                <VCardText class="text-center">
                  <div class="text-h4 font-weight-bold">{{ production.periods_count }}</div>
                  <div class="text-body-2">Периодов</div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol cols="12" md="3">
              <VCard variant="tonal" color="info">
                <VCardText class="text-center">
                  <div class="text-h4 font-weight-bold">{{ production.total_dishes }}</div>
                  <div class="text-body-2">Всего блюд</div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol cols="12" md="3">
              <VCard variant="tonal" color="success">
                <VCardText class="text-center">
                  <div class="text-h4 font-weight-bold">{{ production.completed_dishes }}</div>
                  <div class="text-body-2">Выполнено</div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol cols="12" md="3">
              <VCard variant="tonal" color="warning">
                <VCardText class="text-center">
                  <div class="text-h4 font-weight-bold">{{ production.progress_percentage }}%</div>
                  <div class="text-body-2">Прогресс</div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>

          <!-- Общий прогресс -->
          <div class="mt-4">
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-h6">Общий прогресс</span>
              <span class="text-h6">{{ production.progress_percentage }}%</span>
            </div>
            <VProgressLinear
              :model-value="production.progress_percentage"
              :color="getProgressColor(production.progress_percentage)"
              height="12"
              rounded
            />
          </div>
        </VCardText>
      </VCard>

      <!-- Периоды -->
      <div class="periods-section">
        <h3 class="text-h5 mb-4">Периоды приема пищи</h3>
        
        <div
          v-for="(period, periodIndex) in sortedPeriods"
          :key="period.id"
          class="mb-6"
        >
          <VCard variant="outlined">
            <VCardTitle class="d-flex justify-space-between align-center">
              <div class="d-flex align-center gap-3">
                <VIcon
                  :icon="getPeriodIcon(period.time)"
                  size="24"
                  :color="getPeriodColor(period.time)"
                />
                <div>
                  <div class="text-h6">
                    {{ period.time }}
                    <template v-if="period.name">: {{ period.name }}</template>
                  </div>
                  <div class="text-body-2 text-medium-emphasis">
                    {{ period.dishes_count }} блюд, {{ period.total_quantity }} порций
                  </div>
                </div>
              </div>
              
              <div class="d-flex align-center gap-2">
                <VChip
                  :color="period.completed_quantity === period.total_quantity ? 'success' : 'warning'"
                  size="small"
                >
                  {{ period.completed_quantity }}/{{ period.total_quantity }}
                </VChip>
                <VBtn
                  icon="mdi-plus"
                  size="small"
                  color="primary"
                  variant="outlined"
                  :disabled="production.status === 'completed'"
                />
              </div>
            </VCardTitle>

            <VCardText>
              <div v-if="period.tech_card_resources.length === 0" class="text-center text-grey py-4">
                Блюда не добавлены
              </div>
              
              <VRow v-else>
                <VCol
                  v-for="dish in period.tech_card_resources"
                  :key="tech_card_resource.id"
                  cols="12"
                  md="6"
                  lg="4"
                >
                  <VCard
                    variant="outlined"
                    class="dish-card"
                    :class="{ 'dish-completed': tech_card_resource.status === 'completed' }"
                  >
                    <VCardText>
                      <div class="d-flex align-center mb-2">
                        <VAvatar
                          size="40"
                          :color="tech_card_resource.tech_card?.photo ? 'transparent' : 'primary'"
                          class="mr-3"
                        >
                          <VImg
                            v-if="tech_card_resource.tech_card?.photo"
                            :src="tech_card_resource.tech_card_resource.photo.url"
                            :alt="tech_card_resource.tech_card_resource.name"
                            cover
                          />
                          <VIcon
                            v-else
                            icon="mdi-silverware-fork-knife"
                            size="20"
                          />
                        </VAvatar>
                        <div class="flex-grow-1">
                          <div class="font-weight-medium">{{ tech_card_resource.tech_card?.name }}</div>
                          <div class="text-body-2 text-medium-emphasis">
                            {{ tech_card_resource.quantity }} порций
                          </div>
                        </div>
                      </div>

                      <div class="dish-details mb-3">
                        <div class="d-flex justify-space-between text-body-2">
                          <span>Общий вес:</span>
                          <span>{{ tech_card_resource.total_weight }}г</span>
                        </div>
                        <div v-if="tech_card_resource.total_cost" class="d-flex justify-space-between text-body-2">
                          <span>Стоимость:</span>
                          <span>{{ formatCost(tech_card_resource.total_cost) }}</span>
                        </div>
                      </div>

                      <div class="d-flex justify-space-between align-center">
                        <VChip
                          :color="getStatusColor(tech_card_resource.status)"
                          size="small"
                        >
                          {{ tech_card_resource.status_label }}
                        </VChip>
                        
                        <div class="d-flex gap-1">
                          <VBtn
                            v-if="tech_card_resource.status === 'pending'"
                            icon="mdi-play"
                            size="small"
                            color="primary"
                            variant="text"
                            @click="updateDishStatus(dish, 'in_progress')"
                            :disabled="production.status === 'completed'"
                          />
                          <VBtn
                            v-if="tech_card_resource.status === 'in_progress'"
                            icon="mdi-check"
                            size="small"
                            color="success"
                            variant="text"
                            @click="updateDishStatus(dish, 'completed')"
                            :disabled="production.status === 'completed'"
                          />
                          <VBtn
                            v-if="tech_card_resource.status === 'completed'"
                            icon="mdi-restart"
                            size="small"
                            color="warning"
                            variant="text"
                            @click="updateDishStatus(dish, 'pending')"
                            :disabled="production.status === 'completed'"
                          />
                        </div>
                      </div>
                    </VCardText>
                  </VCard>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </div>
      </div>
    </div>

    <!-- Модальное окно добавления блюда -->

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProductionStore } from '@/stores/productionStore'

const route = useRoute()
const productionStore = useProductionStore()

// Реактивные данные
const loading = ref(false)
const production = ref(null)

// Вычисляемые свойства
const sortedPeriods = computed(() => {
  if (!production.value?.periods) return []
  return [...production.value.periods].sort((a, b) => a.time.localeCompare(b.time))
})

// Методы
const loadProduction = async () => {
  try {
    loading.value = true
    const id = route.params.id
    production.value = await productionStore.fetchSingle(id)
  } catch (error) {
    console.error('Ошибка загрузки производства:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateWithWeekday = (dateString) => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getStatusColor = (status) => {
  const colors = {
    'planned': 'orange',
    'pending': 'orange',
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

const getPeriodIcon = (time) => {
  const hour = parseInt(time.split(':')[0])
  if (hour < 11) return 'mdi-weather-sunny'
  if (hour < 16) return 'mdi-weather-partly-cloudy'
  return 'mdi-weather-night'
}

const getPeriodColor = (time) => {
  const hour = parseInt(time.split(':')[0])
  if (hour < 11) return 'orange'
  if (hour < 16) return 'blue'
  return 'purple'
}

const formatCost = (cost) => {
  return `${cost.toFixed(2)} ₽`
}

const updateDishStatus = async (dish, newStatus) => {
  try {
    await productionStore.updateDishStatus(tech_card_resource.id, { status: newStatus })
    // Обновляем локальные данные
    tech_card_resource.status = newStatus
    tech_card_resource.status_label = getStatusLabel(newStatus)
    if (newStatus === 'completed') {
      tech_card_resource.completed_at = new Date().toISOString()
    }
    
    // Пересчитываем прогресс
    await loadProduction()
    
  } catch (error) {
    console.error('Ошибка обновления статуса блюда:', error)
  }
}

const getStatusLabel = (status) => {
  const labels = {
    'pending': 'Ожидает выпуска',
    'in_progress': 'В процессе',
    'completed': 'Выпущено',
    'cancelled': 'Отменено'
  }
  return labels[status] || status
}

const handleDishAdded = () => {
  loadProduction()
}

onMounted(() => {
  loadProduction()
})
</script>

<style scoped>
.dish-card {
  transition: all 0.3s ease;
  height: 100%;
}

.dish-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.dish-completed {
  background-color: rgba(var(--v-theme-success), 0.05);
  border-color: rgba(var(--v-theme-success), 0.3);
}

.dish-details {
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding: 8px 0;
}

.periods-section {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
