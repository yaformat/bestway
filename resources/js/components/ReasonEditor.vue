<template>
  <VDialog
    v-model="isOpen"
    max-width="500px"
    persistent
  >
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center">
        <span>Причина списания</span>
        <VBtn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="handleClose"
        />
      </VCardTitle>

      <VDivider />

      <VCardText class="pt-4">
        <div class="reason-editor">
          <!-- Причины списания -->
          <div class="reasons-section">
            <VRadioGroup v-model="localReason">
              <VRadio
                v-for="reason in writeOffReasons"
                :key="reason.id"
                :value="reason.id"
                :label="reason.name"
                color="primary"
                class="reason-radio"
              />
            </VRadioGroup>

            <!-- Поле для комментария -->
            <VExpandTransition>
              <div v-if="localReason === 'other'" class="comment-section">
                <VTextarea
                  v-model="localComment"
                  label="Укажите причину списания"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  placeholder="Опишите причину списания..."
                  class="comment-textarea"
                />
              </div>
            </VExpandTransition>
          </div>
        </div>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-4">
        <VBtn
          color="error"
          variant="text"
          @click="handleReset"
        >
          Сбросить
        </VBtn>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="text"
          @click="handleClose"
        >
          Отмена
        </VBtn>
        <VBtn
          color="primary"
          variant="elevated"
          :disabled="!isValid"
          @click="handleApply"
        >
          Применить
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  initialReason: {
    type: String,
    default: null
  },
  initialComment: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'apply', 'reset'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Локальные данные
const localReason = ref(null)
const localComment = ref('')

// Причины списания
const writeOffReasons = [
  { id: 'expired', name: 'Истек срок годности' },
  { id: 'damaged', name: 'Повреждено' },
  { id: 'defective', name: 'Брак' },
  { id: 'lost', name: 'Утеря' },
  { id: 'theft', name: 'Хищение' },
  { id: 'usage', name: 'Использовано в производстве' },
  { id: 'other', name: 'Другая причина' }
]

// Вычисляемые свойства
const isValid = computed(() => {
  if (!localReason.value) return false
  if (localReason.value === 'other' && !localComment.value.trim()) return false
  return true
})

// Методы
const handleApply = () => {
  if (!isValid.value) return
  
  emit('apply', {
    reason: localReason.value,
    comment: localReason.value === 'other' ? localComment.value : ''
  })
  
  handleClose()
}

const handleReset = () => {
  localReason.value = null
  localComment.value = ''
  
  emit('reset')
  handleClose()
}

const handleClose = () => {
  isOpen.value = false
}

const resetForm = () => {
  localReason.value = props.initialReason || null
  localComment.value = props.initialComment || ''
}

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    resetForm()
  }
})

watch(() => localReason.value, (newValue) => {
  if (newValue !== 'other') {
    localComment.value = ''
  }
})
</script>

<style scoped>
.reason-editor {
  min-height: 200px;
}

.reasons-section {
  margin-bottom: 16px;
}

.reason-radio {
  margin-bottom: 8px;
}

.reason-radio :deep(.v-label) {
  font-size: 0.9375rem;
}

.comment-section {
  margin-top: 16px;
}

.comment-textarea {
  margin-top: 8px;
}
</style>
