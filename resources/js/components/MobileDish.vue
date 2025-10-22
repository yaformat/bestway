<template>
  <div class="mobile-dish" :class="{ 'completed': isCompleted, 'selected': isSelected }">
    <div class="dish-content" @click="handleDishClick">
      <!-- Чекбокс для выбора (в режимах редактирования/выполнения) -->
      <VCheckbox
        v-if="showCheckbox"
        :model-value="isSelected"
        :color="checkboxColor"
        density="compact"
        hide-details
        class="dish-checkbox"
        @click.stop
        @update:model-value="toggleSelection"
      />

      <!-- Иконка перетаскивания (в режиме сортировки) -->
      <VIcon
        v-if="showDragHandle"
        icon="mdi-drag-horizontal-variant"
        class="drag-handle mr-3"
        color="grey-lighten-1"
      />

      <!-- Информация о блюде -->
      <div class="dish-info">
        <div class="dish-name">{{ tech_card_resource.tech_card_resource.name }}</div>
        
        <!-- Inline редактирование количества в режиме EDIT_DAY -->
        <div v-if="mobileState.currentMode === MOBILE_MODES.EDIT_DAY && !isCompleted" class="quantity-inline-edit mt-2">
          <div class="quantity-edit-group">
            <QuantityInput
              :model-value="tech_card_resource.quantity_guest || 0"
              label="Гости"
              :min="0"
              :max="999"
              size="x-small"
              density="compact"
              color="primary"
              @update:model-value="updateGuestQuantity"
            />
            
            <QuantityInput
              :model-value="tech_card_resource.quantity_staff || 0"
              label="Персонал"
              :min="0"
              :max="999"
              size="x-small"
              density="compact"
              color="secondary"
              @update:model-value="updateStaffQuantity"
            />
          </div>
        </div>
        
        <div v-if="isCompleted" class="completed-info">
          Выполнено: {{ formatDateTime(tech_card_resource.completed_at) }}
        </div>
      </div>

      <!-- Счетчик количества (только не в режиме EDIT_DAY) -->
      <div v-if="mobileState.currentMode !== MOBILE_MODES.EDIT_DAY" class="dish-quantity">
        <VBtn
          v-if="!isCompleted && mobileState.currentMode !== MOBILE_MODES.SORT && mobileState.currentMode !== MOBILE_MODES.DELETE && mobileState.currentMode !== MOBILE_MODES.COMPLETE"
          variant="outlined"
          size="small"
          class="quantity-btn"
          @click.stop="openQuantityModal"
        >
          {{ tech_card_resource.quantity_guest || 0 }} / {{ tech_card_resource.quantity_staff || 0 }}
        </VBtn>
        <div v-else class="quantity-display">
          {{ tech_card_resource.quantity_guest || 0 }} / {{ tech_card_resource.quantity_staff || 0 }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useMobileMode, MOBILE_MODES } from '@/composables/useMobileMode'

const props = defineProps({
  tech_card_resource: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'update-quantity',
  'open-quantity-modal'
])

const { mobileState } = useMobileMode()

const isCompleted = computed(() => {
  return props.tech_card_resource.status === 'completed' || props.tech_card_resource.completed_at !== null
})

const isSelected = computed(() => {
  return mobileState.selectedDishes.has(props.tech_card_resource.id)
})

const showCheckbox = computed(() => {
  return (mobileState.currentMode === MOBILE_MODES.DELETE || 
          mobileState.currentMode === MOBILE_MODES.COMPLETE) && 
         !isCompleted.value
})

const showDragHandle = computed(() => {
  return mobileState.currentMode === MOBILE_MODES.SORT && !isCompleted.value
})

const checkboxColor = computed(() => {
  if (mobileState.currentMode === MOBILE_MODES.DELETE) {
    return 'error'
  } else if (mobileState.currentMode === MOBILE_MODES.COMPLETE) {
    return 'success'
  }
  return 'primary'
})

const handleDishClick = () => {
  if (showCheckbox.value) {
    toggleSelection()
  }
}

// Открытие модального окна количества через эмит
const openQuantityModal = () => {
  console.log('Open quantity modal for dish:', props.tech_card_resource.id)
  console.log('Event:', event)
  emit('open-quantity-modal', props.tech_card_resource)
}

const toggleSelection = () => {
  if (mobileState.currentMode === MOBILE_MODES.DELETE || 
      mobileState.currentMode === MOBILE_MODES.COMPLETE) {
    if (isSelected.value) {
      mobileState.selectedDishes.delete(props.tech_card_resource.id)
    } else {
      mobileState.selectedDishes.add(props.tech_card_resource.id)
    }
  }
}

// Методы для инлайн-редактирования количества
const updateGuestQuantity = (value) => {
  emit('update-quantity', {
    dishId: props.tech_card_resource.id,
    quantity_guest: value,
    quantity_staff: props.tech_card_resource.quantity_staff || 0
  })
}

const updateStaffQuantity = (value) => {
  emit('update-quantity', {
    dishId: props.tech_card_resource.id,
    quantity_guest: props.tech_card_resource.quantity_guest || 0,
    quantity_staff: value
  })
}

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return ''
  
  const date = new Date(dateTimeString)
  return date.toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.mobile-dish {
  margin-bottom: 8px;
  transition: all 0.2s ease;
}

.mobile-dish.selected {
  transform: scale(0.98);
}

.dish-content {
  display: flex;
  align-items: flex-start;
  padding: 12px;
  background: rgba(var(--v-theme-surface), 1);
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  transition: all 0.2s ease;
}

.mobile-dish.selected .dish-content {
  background: rgba(var(--v-theme-primary), 0.08);
  border-color: rgb(var(--v-theme-primary));
}

.mobile-dish.completed .dish-content {
  opacity: 0.7;
  background: rgba(var(--v-theme-success), 0.05);
}

.dish-checkbox {
  flex-shrink: 0;
  margin-right: 8px;
  margin-top: 2px;
}

.drag-handle {
  cursor: grab;
  flex-shrink: 0;
  margin-top: 2px;
}

.drag-handle:active {
  cursor: grabbing;
}

.dish-info {
  flex: 1;
  min-width: 0;
  margin-right: 12px;
}

.dish-name {
  font-weight: 500;
  font-size: 0.95rem;
  line-height: 1.2;
}

.dish-description {
  font-size: 0.8rem;
  color: rgba(var(--v-theme-on-surface), 0.7);
  margin-top: 2px;
}

.completed-info {
  font-size: 0.75rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
  margin-top: 2px;
}

.dish-quantity {
  flex-shrink: 0;
  margin-top: 2px;
}

.quantity-btn {
  min-width: 50px;
  font-size: 0.8rem;
  font-weight: 500;
}

.quantity-display {
  font-size: 0.8rem;
  color: rgba(var(--v-theme-on-surface), 0.7);
  padding: 4px 8px;
  background: rgba(var(--v-theme-success), 0.5);
  border-radius: 4px;
  text-align: center;
  min-width: 50px;
  opacity: 0.5;
}

.quantity-inline-edit {
  background: rgba(var(--v-theme-primary), 0.03);
  border-radius: 6px;
  padding: 8px;
}

.quantity-edit-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.quantity-edit-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.quantity-label {
  font-size: 0.8rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.8);
  min-width: 60px;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.quantity-value {
  font-size: 0.9rem;
  font-weight: 500;
  min-width: 20px;
  text-align: center;
}

.quantity-controls-modal {
  padding: 8px 0;
}

.quantity-group {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  padding: 16px;
}

.quantity-label {
  display: flex;
  align-items: center;
  font-weight: 500;
  font-size: 0.875rem;
}

.quantity-control {
  display: flex;
  align-items: center;
  justify-content: center;
}

.quantity-input {
  max-width: 80px;
}
</style>
