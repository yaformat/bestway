<!-- components/forms/WorkshopCreateForm.vue -->
<template>
  <ImageUploader v-model:photoId="model.photo_id" v-model:imageUrl="model.imagePreview" />
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


console.log(props.model);
</script>
