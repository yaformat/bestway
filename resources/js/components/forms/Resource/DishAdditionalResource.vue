<template>
  <div class="additional-resource-card">
    <!-- Мобильная версия -->
    <div class="resource-mobile">
      <div class="resource-info">
        <div class="resource-name-section">
          <TableNameWithImage :item="props.data.resource" />
        </div>

        <!-- Поля количества и стоимости -->
        <div class="resource-fields">
          <div class="field-group">
            <label class="field-label">Количество</label>
            <VTextField
              variant="outlined"
              density="compact"
              hide-details
              type="number"
              :model-value="props.data.quantity"
              @input="updateQuantity"
              class="resource-input"
              :suffix="props.data.resource?.unit_alt_label || 'шт'"
            />
          </div>

          <div class="field-group">
            <label class="field-label">Себестоимость</label>
            <div class="cost-price">{{ formattedCostPrice }}</div>
          </div>
        </div>

        <!-- Действия -->
        <div class="resource-actions">
          <VBtn
            variant="tonal"
            color="warning"
            size="small"
            icon
            @click="removeResource"
          >
            <VIcon size="16" icon="mdi-trashcan-outline" />
          </VBtn>
          <VBtn
            v-show="props.allowSorting"
            variant="tonal"
            color="secondary"
            size="small"
            icon
            class="ms-2 resource-handle"
            title="Поменять порядок"
          >
            <VIcon size="16" icon="mdi-sort" />
          </VBtn>
        </div>
      </div>
    </div>

    <!-- Десктопная версия -->
    <div class="resource-desktop">
      <div class="resource-cell resource-name-cell">
        <div class="resource-name-content">
          <TableNameWithImage :item="props.data.resource" />
        </div>
      </div>

      <div class="resource-cell resource-input-cell">
        <VTextField
          variant="outlined"
          density="compact"
          hide-details
          type="number"
          :model-value="props.data.quantity"
          @input="updateQuantity"
          class="resource-input"
          :suffix="props.data.resource?.unit_alt_label || 'шт'"
        />
      </div>

      <div class="resource-cell resource-cost-cell">
        <div class="cost-price">{{ formattedCostPrice }}</div>
      </div>

      <div class="resource-cell resource-actions-cell">
        <div class="actions-wrapper">
          <VBtn
            variant="tonal"
            color="warning"
            size="small"
            icon
            @click="removeResource"
          >
            <VIcon size="16" icon="mdi-trashcan-outline" />
          </VBtn>
          <VBtn
            v-show="props.allowSorting"
            variant="tonal"
            color="secondary"
            size="small"
            icon
            class="ms-2 resource-handle"
            title="Поменять порядок"
          >
            <VIcon size="16" icon="mdi-sort" />
          </VBtn>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: {
    type: Number,
    required: true,
  },
  data: {
    type: Object,
    required: true,
    default: () => ({
      resource: null,
      quantity: 0,
    }),
  },
  allowSorting: {
    type: Boolean,
    default: false
  },
})

const emit = defineEmits([
  'remove-resource',
  'update-resource-quantity',
])

const costPrice = computed(() => {
  if (!props.data.resource || !props.data.quantity) return 0
  
  const price = props.data.resource.use_price?.raw || 0
  const quantity = parseFloat(props.data.quantity) || 0
  
  return (price * quantity).toFixed(2)
})

const formattedCostPrice = computed(() => {
  const currency = props.data.resource?.use_price?.currency || 'с'
  return `${costPrice.value} ${currency}`
})

const removeResource = () => {
  emit('remove-resource', props.id)
}

const updateQuantity = (e) => {
  emit('update-resource-quantity', props.id, e.target.value)
}
</script>

<style scoped>
.additional-resource-card {
  padding: 16px;
  background-color: white;
  border: 1px solid rgba(var(--v-theme-secondary), 0.2);
  border-radius: 8px;
}

.additional-resource-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-secondary), 0.4);
}

/* Десктопная версия - показываем по умолчанию */
.resource-desktop {
  display: grid;
  grid-template-columns: 2fr 150px 140px 120px;
  gap: 16px;
  align-items: center;
  padding: 12px 16px;
}

.resource-cell {
  display: flex;
  align-items: center;
}

.resource-name-cell {
  min-width: 0;
  justify-content: flex-start;
}

.resource-name-content {
  width: 100%;
}

.resource-input-cell,
.resource-cost-cell,
.resource-actions-cell {
  justify-content: center;
}

.resource-input {
  width: 100%;
  max-width: 120px;
}

.cost-price {
  font-weight: 500;
  color: var(--v-theme-secondary);
  text-align: center;
}

.actions-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Мобильная версия - скрываем по умолчанию */
.resource-mobile {
  display: none;
}

.resource-mobile .resource-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.resource-name-section {
  margin-bottom: 0;
}

/* Поля количества и стоимости */
.resource-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  align-items: end;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.field-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--v-theme-on-surface);
  opacity: 0.8;
}

.resource-actions {
  display: flex;
  justify-content: center;
  gap: 8px;
}

/* Адаптивность - переключаемся на мобильную версию на маленьких экранах */
@media (max-width: 1200px) {
  .resource-desktop {
    display: none;
  }
  
  .resource-mobile {
    display: block;
  }
}

/* Для очень маленьких экранов */
@media (max-width: 480px) {
  .resource-fields {
    grid-template-columns: 1fr;
    gap: 8px;
  }
}
</style>
