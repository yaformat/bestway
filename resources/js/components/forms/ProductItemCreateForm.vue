<!-- components/forms/EquipmentCreateForm.vue -->
<template>
  <VRow>
    <VCol cols="12" md="6">
      <ImageUploader v-model:photoId="model.photo_id" v-model:imageUrl="model.imagePreview" />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="model.name"
        label="Название"
        :rules="[v => !!v || 'Название обязательно']"
        required
      />
      <VDivider class="my-5" />
      <VTextField
        v-model="model.sku"
        label="SKU"
      />
      <!-- Добавьте другие поля формы здесь -->
    </VCol>
  </VRow>
</template>

<script setup>
const props = defineProps({
  model: Object
})
</script>
