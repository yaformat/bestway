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
      weight_brutto: '', // будем использовать для количества
    }),
  },
  allowSorting: {
    type: Boolean,
    default: false
  },
})

const emit = defineEmits([
  'removeResource',
  'updateResourceWeight',
])

// Вычисляем себестоимость ресурса
const costPrice = computed(() => {
  if (!props.data.resource || !props.data.weight_brutto) return 0
  
  const price = props.data.resource.use_price?.raw || 0
  const quantity = parseFloat(props.data.weight_brutto) || 0
  
  return (price * quantity).toFixed(2)
})

// Форматируем себестоимость для отображения
const formattedCostPrice = computed(() => {
  const currency = props.data.resource?.use_price?.currency || 'с'
  return `${costPrice.value} ${currency}`
})

const removeResource = () => {
  emit('removeResource', props.id)
}

const updateQuantity = (e) => {
  emit('updateResourceWeight', props.id, 'weight_brutto', e.target.value)
}
</script>

<template>
  <div class="resource-card">
    <!-- Мобильная версия -->
    <div class="resource-mobile">
      <div class="resource-info">
        <div class="resource-name-section">
          <TableNameWithImage :item="props.data.resource" />
        </div>

        <!-- Количество и себестоимость в одну строку -->
        <div class="resource-bottom">
          <div class="quantity-section">
            <label class="field-label">Количество ({{ props.data.resource?.unit_label || 'шт' }})</label>
            <VTextField
              variant="outlined"
              density="compact"
              hide-details
              type="number"
              :model-value="props.data.weight_brutto"
              @input="updateQuantity"
              class="resource-input"
            />
          </div>

          <div class="cost-section">
            <label class="field-label">Себестоимость</label>
            <div class="cost-price">{{ formattedCostPrice }}</div>
          </div>

          <div class="resource-actions">
            <VBtn
              variant="tonal"
              color="warning"
              size="small"
              icon
              class="me-2"
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
              class="resource-handle"
              title="Поменять порядок"
            >
              <VIcon size="16" icon="mdi-sort" />
            </VBtn>
          </div>
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

      <div class="resource-cell resource-quantity-cell">
        <VTextField
          variant="outlined"
          density="compact"
          hide-details
          type="number"
          :model-value="props.data.weight_brutto"
          @input="updateQuantity"
          class="resource-input"
          :label="`Количество (${props.data.resource?.unit_label || 'шт'})`"
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
            class="me-2"
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
            class="resource-handle"
            title="Поменять порядок"
          >
            <VIcon size="16" icon="mdi-sort" />
          </VBtn>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.resource-card {
  padding: 16px;
  background-color: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}
.resource-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

/* Десктопная версия - показываем по умолчанию */
.resource-desktop {
  display: grid;
  grid-template-columns: 2fr 200px 150px 120px;
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

.resource-quantity-cell,
.resource-cost-cell,
.resource-actions-cell {
  justify-content: center;
}

.resource-input {
  width: 100%;
  max-width: 180px;
}

.cost-price {
  font-weight: 500;
  color: var(--v-theme-primary);
  text-align: center;
}

.actions-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}

.resource-handle {
  cursor: move;
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

/* Нижняя строка с количеством, себестоимостью и кнопками */
.resource-bottom {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 12px;
}

.quantity-section,
.cost-section {
  flex: 1;
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
  gap: 8px;
  flex-shrink: 0;
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
  .resource-bottom {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }
  
  .resource-actions {
    justify-content: center;
  }
}
</style>
