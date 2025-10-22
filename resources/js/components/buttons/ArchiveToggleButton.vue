<template>
  <VBtn
    @click="toggleTrashedView"
    variant="outlined"
    color="secondary"
    :disabled="trashedCount === 0 && !isTrashedView"
  >
    <VCheckbox
      :model-value="isTrashedView"
      @update:model-value="updateIsTrashedView"
      hide-details
      :disabled="trashedCount === 0"
    />
    <VIcon left>mdi-trash-can-outline</VIcon>
    Архив
    <VBadge :content="trashedCount" color="secondary" class="ml-4">
      <template #badge>
        <span>{{ trashedCount }}</span>
      </template>
    </VBadge>
  </VBtn>
</template>

<script setup>
const props = defineProps({
  isTrashedView: Boolean,
  trashedCount: Number,
  toggleTrashedView: Function
})

const emit = defineEmits(['update:isTrashedView'])

const updateIsTrashedView = (value) => {
  emit('update:isTrashedView', value)
}
</script>
