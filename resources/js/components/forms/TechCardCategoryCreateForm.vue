<!-- components/forms/WorkshopCreateForm.vue -->
<template>
  <VRow>
    <VCol cols="12" md="6">
      <ImageUploader v-model:photoId="model.photo_id" v-model:imageUrl="model.imagePreview" />
    </VCol>
    <VCol cols="12" md="6">
      <VCol cols="12">
        <VTextField
          v-model="model.name"
          label="Название"
          :rules="[v => !!v || 'Название обязательно']"
          required
        />
      </VCol>
      <VDivider class="my-3"></VDivider>
      <VCol cols="12" md="8">
        <VTextField
          v-model="model.sort_order"
          placeholder="Порядок"
          label="Порядок"
          type="number"
          inputmode="numeric"
          min="0"
        />
      </VCol>
    </VCol>
  </VRow>
</template>

<script setup>
const props = defineProps({
  model: Object
})
</script>
