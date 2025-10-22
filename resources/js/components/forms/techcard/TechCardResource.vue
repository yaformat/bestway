<script setup>
import { computed, ref, onMounted, watch } from 'vue'

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
      weight_brutto: '',
      weight_netto: '',
      weight_output: '',
      losses: [],
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
  'updateResourceLosses',
])

const activeLosses = ref([])

const availableLosses = computed(() => {
  return props.data.resource?.losses?.filter(loss => loss.value > 0) || []
})

const initializeLosses = () => {
  console.log(props.data);
  if (!props.data.resource?.losses) return
  
  if (props.data.losses?.length > 0) {
    activeLosses.value = props.data.losses.map(loss => loss.id)
    return
  }
  
  const newActiveLosses = props.data.resource.losses
    .filter(loss => loss.id === 'peeling' && loss.value > 0)
    .map(loss => loss.id)
  
  activeLosses.value = newActiveLosses
  
  if (newActiveLosses.length > 0) {
    const appliedLosses = newActiveLosses.map(id => {
      const resourceLoss = props.data.resource.losses.find(l => l.id === id)
      return {
        id: resourceLoss.id,
        name: resourceLoss.name,
        value: resourceLoss.value
      }
    })
    emit('updateResourceLosses', props.id, appliedLosses)
  }
}

onMounted(initializeLosses)
watch(() => props.data, initializeLosses)

const toggleLoss = (lossId) => {
  const index = activeLosses.value.indexOf(lossId)
  
  if (index === -1) {
    activeLosses.value.push(lossId)
  } else {
    activeLosses.value.splice(index, 1)
  }
  
  const appliedLosses = activeLosses.value.map(id => {
    const resourceLoss = props.data.resource.losses.find(l => l.id === id)
    return {
      id: resourceLoss.id,
      name: resourceLoss.name,
      value: resourceLoss.value
    }
  })
  
  emit('updateResourceLosses', props.id, appliedLosses)
}

const isLossActive = (lossId) => {
  return activeLosses.value.includes(lossId)
}

const costPrice = computed(() => {
  if (!props.data.resource || !props.data.weight_brutto) return 0
  
  const price = props.data.resource.use_price?.raw || 0
  const weight = parseFloat(props.data.weight_brutto) || 0
  
  if (props.data.resource.unit === 'kilogram') {
    return (price * weight / 1000).toFixed(2)
  }
  
  return (price * weight).toFixed(2)
})

const formattedCostPrice = computed(() => {
  const currency = props.data.resource?.use_price?.currency || 'с'
  return `${costPrice.value} ${currency}`
})

const removeResource = () => {
  emit('removeResource', props.id)
}

const updateBrutto = (e) => {
  emit('updateResourceWeight', props.id, 'weight_brutto', e.target.value)
}

const updateNetto = (e) => {
  emit('updateResourceWeight', props.id, 'weight_netto', e.target.value)
}

const updateOutput = (e) => {
  emit('updateResourceWeight', props.id, 'weight_output', e.target.value)
}
</script>

<template>
  <div class="resource-card">
    <!-- Мобильная версия -->
    <div class="resource-mobile">
      <div class="resource-info">
        <div class="resource-name-section">
          <TableNameWithImage :item="props.data.resource" />
          
          <div v-if="availableLosses.length > 0" class="losses-container mt-2">
            <VChip
              v-for="loss in availableLosses"
              :key="loss.id"
              :color="isLossActive(loss.id) ? 'primary' : 'default'"
              :variant="isLossActive(loss.id) ? 'flat' : 'outlined'"
              size="x-small"
              class="loss-chip"
              @click="toggleLoss(loss.id)"
            >
              {{ loss.name }} (<strong>{{ loss.value }}%</strong>)
            </VChip>
          </div>
        </div>

        <!-- Поля веса в одну строку -->
        <div class="resource-weights">
          <div class="field-group">
            <label class="field-label">Брутто, г</label>
            <VTextField
              variant="outlined"
              density="compact"
              hide-details
              type="number"
              :model-value="props.data.weight_brutto"
              @input="updateBrutto"
              class="resource-input"
            />
          </div>

          <div class="field-group">
            <label class="field-label">Нетто, г</label>
            <VTextField
              variant="outlined"
              density="compact"
              hide-details
              type="number"
              :model-value="props.data.weight_netto"
              @input="updateNetto"
              class="resource-input"
            />
          </div>

          <div class="field-group">
            <label class="field-label">Выход, г</label>
            <VTextField
              variant="outlined"
              density="compact"
              hide-details
              type="number"
              :model-value="props.data.weight_output"
              @input="updateOutput"
              class="resource-input"
            />
          </div>
        </div>

        <!-- Себестоимость и действия в одну строку -->
        <div class="resource-bottom">
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
          
          <div v-if="availableLosses.length > 0" class="losses-container mt-1">
            <VChip
              v-for="loss in availableLosses"
              :key="loss.id"
              :color="isLossActive(loss.id) ? 'primary' : 'default'"
              :variant="isLossActive(loss.id) ? 'flat' : 'outlined'"
              size="x-small"
              class="loss-chip"
              @click="toggleLoss(loss.id)"
            >
              {{ loss.name }} (<strong>{{ loss.value }}%</strong>)
            </VChip>
          </div>
        </div>
      </div>

      <div class="resource-cell resource-input-cell">
        <VTextField
          variant="outlined"
          density="compact"
          hide-details
          type="number"
          :model-value="props.data.weight_brutto"
          @input="updateBrutto"
          class="resource-input"
        />
      </div>

      <div class="resource-cell resource-input-cell">
        <VTextField
          variant="outlined"
          density="compact"
          hide-details
          type="number"
          :model-value="props.data.weight_netto"
          @input="updateNetto"
          class="resource-input"
        />
      </div>

      <div class="resource-cell resource-input-cell">
        <VTextField
          variant="outlined"
          density="compact"
          hide-details
          type="number"
          :model-value="props.data.weight_output"
          @input="updateOutput"
          class="resource-input"
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
  grid-template-columns: 2fr 120px 120px 120px 140px 120px;
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
  max-width: 100px;
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

/* Поля веса в одну строку */
.resource-weights {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 8px;
}

/* Нижняя строка с себестоимостью и кнопками */
.resource-bottom {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 12px;
}

.cost-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
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
  gap: 8px;
  flex-shrink: 0;
}

.losses-container {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.loss-chip {
  cursor: pointer;
  font-size: 0.7rem;
  height: 20px !important;
  flex-shrink: 0;
}

.loss-chip.v-chip--variant-outlined {
  border-color: var(--v-theme-primary);
  color: var(--v-theme-primary);
  background-color: transparent !important;
}

.loss-chip.v-chip--variant-flat {
  background-color: var(--v-theme-primary);
  color: white;
}

.loss-chip:hover {
  opacity: 0.9;
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

/* Для очень маленьких экранов делаем поля веса в 2 колонки */
@media (max-width: 480px) {
  .resource-weights {
    grid-template-columns: 1fr 1fr;
    gap: 6px;
  }
  
  .resource-weights .field-group:nth-child(3) {
    grid-column: 1 / -1;
  }
  
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
