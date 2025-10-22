<!-- components/forms/KitchenEditForm.vue -->
<template>
  <ImageUploader 
    v-model:photoId="model.photo_id" 
    v-model:imageUrl="model.imagePreview" 
    v-model:photoMarkedForDeletion="model.photoMarkedForDeletion" 
    :photo="props.model.photo" 
  />
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
