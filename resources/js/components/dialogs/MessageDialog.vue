<template>
  <VDialog
    max-width="500"
    :model-value="isDialogVisible"
    @update:model-value="updateModelValue"
  >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VBtn
          icon
          :variant="dialogType === 'success' ? 'outlined' : 'elevated'"
          :color="dialogType === 'success' ? 'success' : 'error'"
          class="my-4"
          style=" block-size: 88px;inline-size: 88px; pointer-events: none;"
        >
          <span class="text-3xl">
            <VIcon :icon="dialogType === 'success' ? 'mdi-check' : 'mdi-alert-circle'" />
          </span>
        </VBtn>

        <h1 class="text-h4 mb-4">
          {{ dialogTitle }}
        </h1>

        <p>{{ dialogMessage }}</p>

        <VBtn
          :color="dialogType === 'success' ? 'success' : 'error'"
          @click="closeDialog"
        >
          Ok
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  dialogTitle: {
    type: String,
    required: true,
  },
  dialogMessage: {
    type: String,
    required: true,
  },
  dialogType: {
    type: String,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
])

const updateModelValue = val => {
  emit('update:isDialogVisible', val)
}

const closeDialog = () => {
  emit('update:isDialogVisible', false)
}
</script>
