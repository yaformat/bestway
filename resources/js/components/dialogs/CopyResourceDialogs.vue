<!-- components/CopyResourceDialogs.vue -->
<template>
  <!-- Диалог копирования -->
  <VDialog 
    :model-value="showCopyDialog" 
    @update:model-value="$emit('update:show-copy-dialog', $event)"
    max-width="500px"
  >
    <VCard>
      <VCardTitle class="text-h5">
        Копирование ресурса
      </VCardTitle>
      
      <VCardText>
        <VForm ref="copyFormRef" v-model="formValid">
          <VTextField
            v-model="copyFormData.name"
            label="Название нового ресурса"
            :rules="copyFormRules.name"
            variant="outlined"
            class="mb-4"
          />
          
          <VSelect
            v-model="copyFormData.type"
            :items="resourceTypes"
            item-title="label"
            item-value="value"
            label="Тип ресурса"
            :rules="copyFormRules.type"
            variant="outlined"
          />
        </VForm>
      </VCardText>
      
      <VCardActions>
        <VSpacer />
        <VBtn
          color="grey-darken-1"
          variant="text"
          @click="$emit('close-copy-dialog')"
        >
          Отмена
        </VBtn>
        <VBtn
          color="primary"
          variant="flat"
          :disabled="!formValid"
          :loading="copyLoading"
          @click="$emit('confirm-copy')"
        >
          Копировать
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Диалог успешного копирования -->
  <VDialog 
    :model-value="showCopySuccessDialog" 
    @update:model-value="$emit('update:show-copy-success-dialog', $event)"
    max-width="400px"
  >
    <VCard>
      <VCardTitle class="text-h5 text-success">
        <VIcon icon="mdi-check-circle" class="me-2" />
        Успешно скопировано
      </VCardTitle>
      
      <VCardText>
        Ресурс успешно скопирован. Хотите перейти к редактированию нового ресурса?
      </VCardText>
      
      <VCardActions>
        <VSpacer />
        <VBtn
          color="grey-darken-1"
          variant="text"
          @click="$emit('close-copy-success-dialog')"
        >
          Остаться здесь
        </VBtn>
        <VBtn
          color="primary"
          variant="flat"
          @click="$emit('go-to-resource')"
        >
          Перейти к ресурсу
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  showCopyDialog: Boolean,
  showCopySuccessDialog: Boolean,
  copyLoading: Boolean,
  copyFormRef: Object,
  copyFormData: Object,
  resourceTypes: Array,
  copyFormRules: Object
})

defineEmits([
  'confirm-copy',
  'go-to-resource',
  'close-copy-dialog',
  'close-copy-success-dialog',
  'update:show-copy-dialog',
  'update:show-copy-success-dialog'
])

// Локальная переменная для валидации формы
const formValid = ref(false)
</script>
