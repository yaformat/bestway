<template>
  <div>
    <div class="transfer-visualization mb-4">
      <div class="stock-from">
        <VLabel class="font-weight-bold mb-2">Склад ОТКУДА</VLabel>
        <div class="selected-stock">
          {{ selectedFromStockName || 'Не выбран' }}
        </div>
      </div>
      <div class="transfer-arrow">
        <VIcon size="large" color="info">mdi-arrow-right-bold</VIcon>
      </div>
      <div class="stock-to">
        <VLabel class="font-weight-bold mb-2">Склад КУДА</VLabel>
        <div class="selected-stock">
          {{ selectedToStockName || 'Не выбран' }}
        </div>
      </div>
    </div>

    <VDivider class="my-4" />

    <div class="stock-selection-container">
      <div class="stock-column">
        <VLabel class="font-weight-bold mb-2">Склад ОТКУДА</VLabel>
        <VRadioGroup
          v-model="model.stock_id"
          :rules="[stockRequiredValidator]"
        >
          <VRadio
            v-for="stock in stocks"
            :key="stock.id"
            :label="stock.name"
            :value="stock.id"
            :disabled="model.stock_to_id === stock.id"
            color="primary"
          />
        </VRadioGroup>
      </div>

      <VDivider vertical class="mx-4 d-none d-md-block" />
      <VDivider class="my-4 d-md-none" />

      <div class="stock-column">
        <VLabel class="font-weight-bold mb-2">Склад КУДА</VLabel>
        <VRadioGroup
          v-model="model.stock_to_id"
          :rules="[stockToRequiredValidator]"
        >
          <VRadio
            v-for="stock in stocks"
            :key="stock.id"
            :label="stock.name"
            :value="stock.id"
            :disabled="model.stock_id === stock.id"
            color="secondary"
          />
        </VRadioGroup>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useGlobalDataStore } from '@/stores/globalDataStore'

const props = defineProps({
  model: {
    type: Object,
    required: true
  }
})

const model = props.model
const globalDataStore = useGlobalDataStore()
const stocks = ref(globalDataStore.stocks)

const selectedFromStockName = computed(() => {
  const stock = stocks.value.find(s => s.id === model.stock_id)
  return stock ? stock.name : null
})

const selectedToStockName = computed(() => {
  const stock = stocks.value.find(s => s.id === model.stock_to_id)
  return stock ? stock.name : null
})

const stockRequiredValidator = (value) => {
  if (!value) return 'Пожалуйста, выберите склад ОТКУДА'
  return true
}

const stockToRequiredValidator = (value) => {
  if (!value) return 'Пожалуйста, выберите склад КУДА'
  return true
}

// если выбрали один и тот же склад — автоматически сбрасываем второй
watch(
  () => model.stock_id,
  (newVal) => {
    if (newVal && model.stock_to_id === newVal) {
      model.stock_to_id = null
    }
  }
)

watch(
  () => model.stock_to_id,
  (newVal) => {
    if (newVal && model.stock_id === newVal) {
      model.stock_id = null
    }
  }
)
</script>

<style scoped>
.transfer-visualization {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 8px;
}

.stock-from, .stock-to {
  flex: 1;
  text-align: center;
}

.selected-stock {
  font-size: 1.2rem;
  padding: 8px;
  border-radius: 4px;
  background-color: white;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stock-from .selected-stock {
  border-left: 4px solid var(--v-primary-base);
}

.stock-to .selected-stock {
  border-left: 4px solid var(--v-secondary-base);
}

.transfer-arrow {
  padding: 0 16px;
}

/* Стили для двухколоночного отображения */
.stock-selection-container {
  display: flex;
  flex-direction: column;
}

@media (min-width: 768px) {
  .stock-selection-container {
    flex-direction: row;
  }
  
  .stock-column {
    flex: 1;
  }
}
</style>
