<template>
  <div class="draggable-item completed-dish">
    <VRow class="align-center mb-2 dish-row">
      <!-- Иконка выполнения -->
      <VCol cols="1">
        <VIcon color="success" size="16" icon="mdi-check-circle" />
      </VCol>
      
      <!-- Информация о блюде -->
      <VCol cols="4">
        <div class="d-flex align-center">
          <VAvatar
            size="40"
            color="success"
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
            <div class="font-weight-medium text-decoration-line-through text-success">
              {{ dish.tech_card.name }}
            </div>
            <div class="text-body-2 text-grey">
              ID: {{ dish.tech_card.id }}
            </div>
          </div>
        </div>
      </VCol>
      
      <!-- Количество (только отображение) -->
      <VCol cols="3">
        <div class="text-center">
          <div class="text-body-2 text-success font-weight-medium">
            {{ dish.quantity }} порций
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
      
      <!-- Пустая колонка для действий -->
      <VCol cols="2"></VCol>
    </VRow>
  </div>
</template>

<script setup>
const props = defineProps({
  dish: {
    type: Object,
    required: true
  }
})

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
.draggable-item {
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.completed-dish {
  opacity: 0.6;
  background: rgba(var(--v-theme-success), 0.05);
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
</style>
