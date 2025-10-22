<!-- components/forms/StockMinusCreateForm.vue -->
<template>
  <VLabel class="font-weight-bold mb-2">Склад</VLabel>
  <VRadioGroup
    v-model="model.stock_id"
    :rules="[stockRequiredValidator]"
  >
    <template v-for="stock in stocks" :key="stock.id">
      <VRadio
        :label="stock.name"
        :value="stock.id"
        color="primary"
      />
    </template>
  </VRadioGroup>
  
  <!-- Добавьте другие поля формы здесь -->
</template>

<script setup>
import { useGlobalDataStore } from '@/stores/globalDataStore';

const globalDataStore = useGlobalDataStore()
const stocks = ref(globalDataStore.stocks)

const props = defineProps({
  model: Object
})

const stockRequiredValidator = (value) => {
  if (value === null || value === undefined || value === '') {
    return 'Пожалуйста, выберите склад'
  }
  return true
}


</script>
