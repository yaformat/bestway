<!-- components/forms/SupplierLocationEditForm.vue -->
<template>
  <VTextField
    v-model="model.name"
    label="Название"
    :rules="[v => !!v || 'Название обязательно']"
    required
  />
  <!-- Добавьте другие поля формы здесь -->
</template>

<script setup>
const props = defineProps({
  model: Object
})

</script>
