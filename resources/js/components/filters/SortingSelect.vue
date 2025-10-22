<template>
  <VMenu offset-y>
    <template v-slot:activator="{ props: menuProps }">
      <VBtn
        v-bind="menuProps"
        variant="outlined"
        color="secondary"
        class="sorting-btn"
      >
        <VIcon start>mdi-sort</VIcon>
        {{ currentSortLabel }}
        <VIcon end>mdi-chevron-down</VIcon>
      </VBtn>
    </template>

    <VList class="sorting-list">
      <VListItem
        v-for="option in sortingOptions"
        :key="option.value"
        :active="modelValue === option.value"
        @click="selectSort(option.value)"
      >
        <template v-slot:prepend>
          <VIcon :color="modelValue === option.value ? 'primary' : 'default'">
            {{ option.icon || 'mdi-sort-alphabetical-ascending' }}
          </VIcon>
        </template>
        <VListItemTitle>{{ option.label }}</VListItemTitle>
      </VListItem>
    </VList>
  </VMenu>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  sortingOptions: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const currentSortLabel = computed(() => {
  const defaultLabel = 'Сортировка'
  const current = props.sortingOptions.find(option => option.value === props.modelValue)
  if (!current || current?.value ==='default') return defaultLabel
  return current?.label || 'По умолчанию'
})

const selectSort = (value) => {
  emit('update:modelValue', value)
}
</script>

<style scoped>
.sorting-select {
  width: 240px;
}

.sorting-select :deep(.v-field) {
  height: 36px !important;
  border-radius: 8px !important;
}

@media (max-width: 959px) {
  .sorting-select {
    width: 100%;
  }
}
</style>
