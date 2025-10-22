<!-- components/forms/SupplierEditForm.vue -->
<template>
  <VTextField
    v-model="model.name"
    label="Название"
    :rules="[v => !!v || 'Название обязательно']"
    required
  />
  <VDivider class="my-3" />
  <VSelect
    search-input
    v-model="model.location_id"
    label="Локация"
    placeholder="Выбрать локацию"
    :items="supplier_locations"
    item-title="name" item-value="id"
    :menu-props="{ maxHeight: 200 }"
  />
  <!-- Добавьте другие поля формы здесь -->
</template>

<script setup>
import { useGlobalDataStore } from '@/stores/globalDataStore';

const globalDataStore = useGlobalDataStore()
const supplier_locations = ref(globalDataStore.supplier_locations)

const props = defineProps({
  model: Object
})

</script>
