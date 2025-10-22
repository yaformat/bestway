<template>
  <div class="draggable-item">
    <VRow class="align-center mb-2 dish-row">
      <!-- Кнопка выполнения -->
      <VCol cols="1">
        <VBtn
          variant="tonal"
          color="success"
          size="small"
          icon
          @click="$emit('complete', dish)"
          title="Отметить как выполненное"
        >
          <VIcon size="16" icon="mdi-check" />
        </VBtn>
      </VCol>
      
      <!-- Информация о блюде -->
      <VCol cols="4">
        <div class="d-flex align-center">
          <VAvatar
            size="40"
            :color="dish.tech_card.photo ? 'transparent' : 'primary'"
            class="mr-3"
          >
            <VImg
              v-if="dish.tech_card.photo"
              :src="dish.tech_card.photo.url"
              :alt="dish.tech_card.name"
              cover
            />
            <VIcon
              v-else
              icon="mdi-silverware-fork-knife"
              size="20"
            />
          </VAvatar>
          <div>
            <div class="font-weight-medium">
              {{ dish.tech_card.name }}
            </div>
            <div class="text-body-2 text-grey">
              ID: {{ dish.tech_card.id }}
            </div>
          </div>
        </div>
      </VCol>
      
      <!-- Управление количеством -->
      <VCol cols="3">
        <div class="quantity-control">
          <div class="quantity-group">
            <VBtn
              variant="outlined"
              size="small"
              class="quantity-btn quantity-btn-left"
              :disabled="dish.quantity <= 1"
              @click="$emit('decrease-quantity', dish)"
            >
              <VIcon size="18">mdi-minus</VIcon>
            </VBtn>
            
            <VTextField
              :model-value="dish.quantity"
              type="number"
              variant="outlined"
              density="compact"
              min="1"
              class="quantity-input"
              hide-details
              @update:model-value="updateQuantity"
              @blur="$emit('validate-quantity', dish)"
            />
            
            <VBtn
              variant="outlined"
              size="small"
              class="quantity-btn quantity-btn-right"
              @click="$emit('increase-quantity', dish)"
            >
              <VIcon size="18">mdi-plus</VIcon>
            </VBtn>
          </div>
          
          <div class="text-center text-body-2 text-grey mt-1">
            Порций
          </div>
        </div>
      </VCol>
      
      <!-- Вес и стоимость -->
      <VCol cols="2">
        <div class="text-body-2 text-grey">
          {{ calculateTotalWeight(dish) }} г
        </div>
        <div v-if="dish.tech_card.cost_price" class="text-body-2 text-grey">
          {{ calculateTotalCost(dish) }}
        </div>
      </VCol>
      
      <!-- Действия -->
      <VCol cols="2">
        <div class="d-flex gap-1">
          <VBtn
            variant="tonal"
            color="warning"
            size="small"
            icon
            @click="$emit('remove', dish)"
          >
            <VIcon size="16" icon="mdi-trashcan-outline" />
          </VBtn>
          
          <VBtn
            v-show="showDragHandle"
            variant="tonal"
            color="secondary"
            size="small"
            icon
            class="dish-handle"
            title="Поменять порядок"
          >
            <VIcon size="16" icon="mdi-sort" />
          </VBtn>
        </div>
      </VCol>
    </VRow>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  dish: {
    type: Object,
    required: true
  },
  showDragHandle: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'complete',
  'increase-quantity',
  'decrease-quantity',
  'validate-quantity',
  'remove'
])

const updateQuantity = (value) => {
  props.dish.quantity = Number(value)
}

const calculateTotalWeight = (dish) => {
  const baseWeight = dish.tech_card.output_weight || 100
  return Math.round(baseWeight * dish.quantity)
}

const calculateTotalCost = (dish) => {
  if (!dish.tech_card.cost_price) return ''
  const totalCost = dish.tech_card.cost_price.raw * dish.quantity
  return `${totalCost.toFixed(2)} ${dish.tech_card.cost_price.currency}`
}
</script>

<style scoped>
.quantity-control {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.quantity-group {
  display: flex;
  width: 100%;
  max-width: 140px;
}

.quantity-btn {
  flex: 0 0 auto;
  min-width: 36px !important;
  border-radius: 4px;
  height: auto !important;
}

.quantity-btn-left {
  border-top-right-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
  border-right: none !important;
}

.quantity-btn-right {
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  border-left: none !important;
}

.quantity-input {
  flex: 1 1 auto;
  min-width: 60px;
}

.quantity-input :deep(.v-field) {
  border-radius: 0 !important;
}

.quantity-input :deep(.v-field__input) {
  text-align: center;
  padding: 0 8px;
}

.quantity-input :deep(.v-field__outline__start) {
  border-radius: 0 !important;
}

.quantity-input :deep(.v-field__outline__end) {
  border-radius: 0 !important;
}

.draggable-item {
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.dish-row {
  background: rgba(var(--v-theme-surface), 1);
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  padding: 12px;
  transition: all 0.2s ease;
}

.dish-row:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dish-handle {
  cursor: grab;
}

.dish-handle:active {
  cursor: grabbing;
}

.chosen.ghost .dish-row {
  visibility: hidden;
}
</style>
