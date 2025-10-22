<!-- components/forms/ResourceCategoryCreateForm.vue -->
<template>
  <VTextField
    v-model="model.name"
    label="Название"
    :rules="[v => !!v || 'Название обязательно']"
    required
  />
  <VDivider class="my-5" />
  <VRow>
    <VCol cols="12" md="8">
      <VSelect
        search-input
        v-model="model.parent_id"
        label="Родительская категория"
        placeholder="Выбрать категорию"
        :items="categories"
        item-title="name" item-value="id"
        :menu-props="{ maxHeight: 200 }"
      />
    </VCol>
    <VCol cols="12" md="4">
      <VTextField
        v-model="model.sort_order"
        placeholder="Порядок"
        label="Порядок"
        type="number"
        inputmode="numeric"
        min="0"
      />
    </VCol>
  </VRow>
  <!-- Добавьте другие поля формы здесь -->
</template>

<script setup>
import { watch } from 'vue';
import { flattenTreeWithIndent } from '@/utils/treeBuilder'

const props = defineProps({
  model: Object,
  categories: Array
});


const categories = flattenTreeWithIndent(props.categories, {
  root: '[Корневой уровень]',
  maxLevel: 0,
  excludeId: null,
})

</script>
