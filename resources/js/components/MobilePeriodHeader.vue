<template>
  <div class="mobile-period-header">
    <!-- Обычный режим -->
    <template v-if="mobileState.currentMode !== MOBILE_MODES.EDIT_DAY">
      <!-- Разделитель периода -->
      <div class="period-divider">
        <VDivider />
        <div class="period-header-content">
          <!-- Чекбокс для удаления периода (в режиме DELETE) -->
          <div v-if="mobileState.currentMode === MOBILE_MODES.DELETE" class="period-checkbox-container">
            <VCheckbox
              :model-value="isSelected"
              color="error"
              density="compact"
              hide-details
              class="period-checkbox"
              :disabled="!canDelete"
              @update:model-value="togglePeriodSelection"
            />
          </div>
          
          <div class="period-title">
            {{ formatTime(period.time) }}
            <template v-if="period.name">
              : {{ period.name }}
            </template>
          </div>
          
          <!-- Действия периода -->
          <div class="period-actions">
            <!-- Иконка редактирования (всегда видна, кроме режима удаления) -->
            <VBtn
              v-if="mobileState.currentMode !== MOBILE_MODES.DELETE"
              icon="mdi-pencil"
              size="small"
              color="primary"
              variant="text"
              @click="$emit('edit-period', period)"
            />
            
            <!-- Иконка удаления (только в обычном режиме просмотра и если можно удалить) -->
            <!-- <VBtn
              v-if="mobileState.currentMode === MOBILE_MODES.VIEW && canDelete"
              icon="mdi-trashcan-outline"
              size="small"
              color="error"
              variant="text"
              @click="$emit('delete-period', period.id)"
            /> -->
          </div>
        </div>
        <VDivider />
      </div>

      <!-- Комментарий периода -->
      <div v-if="period.comment" class="production-notes">
        <span class="text-body-2 text-medium-emphasis">{{ period.comment }}</span>
      </div>

      <!-- Информация о гостях и персонале -->
      <div class="period-info">
        <VChip size="small" color="primary" variant="outlined" class="mr-2">
          Гости: {{ period.guests_count || 0 }}
        </VChip>
        <VChip size="small" color="secondary" variant="outlined">
          Персонал: {{ period.staff_count || 0 }}
        </VChip>
      </div>
    </template>

    <!-- Режим редактирования дня -->
    <template v-else>
      <div class="period-edit-form">
        <VCard variant="outlined" class="mb-3">
          <VCardText class="pb-2">
            <!-- Время и название -->
            <VRow dense>
              <VCol cols="6">
                <VTextField
                  :model-value="period.time"
                  type="time"
                  label="Время"
                  variant="outlined"
                  density="compact"
                  hide-details
                  @update:model-value="updatePeriodField('time', $event)"
                />
              </VCol>
              <VCol cols="6">
                <VTextField
                  :model-value="period.name || ''"
                  label="Название"
                  variant="outlined"
                  density="compact"
                  hide-details
                  @update:model-value="updatePeriodField('name', $event)"
                />
              </VCol>
            </VRow>

            <!-- Количество людей -->
            <VRow dense class="mt-2">
              <VCol cols="6">
                <QuantityInput
                  :model-value="period.guests_count || 0"
                  label="Гости"
                  :min="0"
                  :max="9999"
                  size="small"
                  density="compact"
                  color="primary"
                  @update:model-value="updatePeriodField('guests_count', $event)"
                />
              </VCol>
              <VCol cols="6">
                <QuantityInput
                  :model-value="period.staff_count || 0"
                  label="Персонал"
                  :min="0"
                  :max="9999"
                  size="small"
                  density="compact"
                  color="secondary"
                  @update:model-value="updatePeriodField('staff_count', $event)"
                />
              </VCol>
            </VRow>

            <!-- Комментарий -->
            <VTextField
              :model-value="period.comment || ''"
              label="Комментарий"
              variant="outlined"
              density="compact"
              hide-details
              class="mt-2"
              @update:model-value="updatePeriodField('comment', $event)"
            />
          </VCardText>
        </VCard>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useMobileMode, MOBILE_MODES } from '@/composables/useMobileMode'

const props = defineProps({
  period: {
    type: Object,
    required: true
  },
  editable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['edit-period', 'delete-period', 'update-period'])

const { mobileState } = useMobileMode()

const formatTime = (time) => {
  return new Date(`2000-01-01T${time}`).toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const updatePeriodField = (field, value) => {
  emit('update-period', {
    periodId: props.period.id,
    field,
    value
  })
}

// Получаем невыполненные блюда периода
const getUncompletedDishes = () => {
  if (!props.period.dishes) return []
  return props.period.dishes.filter(dish => 
    dish.status !== 'completed' && !dish.completed_at
  )
}

// Получаем выполненные блюда периода
const getCompletedDishes = () => {
  if (!props.period.dishes) return []
  return props.period.dishes.filter(dish => 
    dish.status === 'completed' || dish.completed_at !== null
  )
}

// Проверяем, можно ли удалить период
const canDelete = computed(() => {
  const uncompletedDishes = getUncompletedDishes()
  const completedDishes = getCompletedDishes()
  
  // Кейс 1: Пустой период - можно удалить
  if (uncompletedDishes.length === 0 && completedDishes.length === 0) {
    return true
  }
  
  // Кейс 2: Только невыполненные блюда - можно удалить
  if (uncompletedDishes.length > 0 && completedDishes.length === 0) {
    return true
  }
  
  // Кейс 3: Есть выполненные блюда - можно выбрать только невыполненные
  if (completedDishes.length > 0) {
    return uncompletedDishes.length > 0
  }
  
  return false
})

// Проверяем, выбран ли период для удаления
const isSelected = computed(() => {
  return mobileState.selectedPeriods.has(props.period.id)
})

// Проверяем, должен ли период быть автоматически выбран
// (когда все его невыполненные блюда выбраны вручную)
const shouldAutoSelectPeriod = computed(() => {
  const uncompletedDishes = getUncompletedDishes()
  
  if (uncompletedDishes.length === 0) return false
  
  // Проверяем, все ли невыполненные блюда выбраны
  return uncompletedDishes.every(dish => 
    mobileState.selectedDishes.has(dish.id)
  )
})

// Переключение выбора периода
const togglePeriodSelection = (selected) => {
  if (!canDelete.value) return
  
  const uncompletedDishes = getUncompletedDishes()
  const completedDishes = getCompletedDishes()
  
  if (selected) {
    // Выбираем период
    mobileState.selectedPeriods.add(props.period.id)
    
    // Кейс 1: Пустой период - только отмечаем период для удаления
    if (uncompletedDishes.length === 0 && completedDishes.length === 0) {
      // Ничего не делаем с блюдами, так как их нет
      return
    }
    
    // Кейс 2 и 3: Автоматически выбираем все невыполненные блюда
    uncompletedDishes.forEach(dish => {
      mobileState.selectedDishes.add(dish.id)
    })
  } else {
    // Снимаем выбор с периода
    mobileState.selectedPeriods.delete(props.period.id)
    
    // Снимаем выбор со всех блюд этого периода
    uncompletedDishes.forEach(dish => {
      mobileState.selectedDishes.delete(dish.id)
    })
  }
}

// Следим за изменениями выбранных блюд
watch(() => mobileState.selectedDishes.size, () => {
  const uncompletedDishes = getUncompletedDishes()
  
  // Если период был выбран вручную через галочку
  if (isSelected.value) {
    const allDishesSelected = uncompletedDishes.every(dish => 
      mobileState.selectedDishes.has(dish.id)
    )
    
    // Если не все блюда периода выбраны, снимаем выбор с периода
    if (uncompletedDishes.length > 0 && !allDishesSelected) {
      mobileState.selectedPeriods.delete(props.period.id)
    }
  }
  
  // НЕ автоматически выбираем период, если все блюда выбраны вручную
  // Период выбирается только через свою галочку
}, { deep: true })
</script>

<style scoped>
.mobile-period-header {
  margin-bottom: 16px;
}

.period-divider {
  margin: 16px 0;
}

.period-header-content {
  display: flex;
  align-items: center;
  padding: 12px 0;
  position: relative;
}

.period-checkbox-container {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  display: flex;
  justify-content: center;
}

.period-checkbox {
  flex-shrink: 0;
}

.period-title {
  font-size: 1.125rem;
  font-weight: 500;
  color: rgb(var(--v-theme-primary));
  text-align: center;
  flex: 1;
  padding: 0 50px; /* Отступы для чекбокса и действий */
}

.period-actions {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.period-info {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 12px;
}

.period-edit-form {
  margin-bottom: 12px;
}

.production-notes {
    display: flex;
    align-items: flex-start;
    padding: 10px 12px;
    background: rgba(var(--v-theme-warning), 0.05);
    border-radius: 8px;
    border-left: 4px solid rgb(var(--v-theme-warning));

    margin: 0 -4px 16px -4px;
    font-size: 0.875rem;
    color: rgba(var(--v-theme-on-surface), 0.8);
}
</style>
