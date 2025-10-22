<template>
  <IOSBottomSheet
    v-model="isOpen"
    :title="resource?.name || 'Списание позиции'"
    :show-close-button="true"
    close-button-mode="close"
    max-height="85vh"
    @close="handleClose"
  >
    <div class="write-off-editor">
      <!-- Информация о ресурсе -->
      <div class="resource-info">
        <div class="info-row">
          <span class="info-label">Текущий остаток:</span>
          <span class="info-value">{{ resource?.value_actual?.display || '0' }}</span>
        </div>
        <div v-if="currentWriteOffValue > 0" class="info-row">
          <span class="info-label">Остаток после списания:</span>
          <span 
            class="info-value" 
            :class="getRemainingClass()"
          >
            {{ calculateRemaining() }}
          </span>
        </div>
      </div>

      <!-- Поле ввода количества -->
      <div class="quantity-section">
        <VTextField
          ref="quantityInputRef"
          v-model="localQuantity"
          type="number"
          label="Количество для списания"
          variant="outlined"
          density="comfortable"
          :placeholder="resource?.unit_label || ''"
          :suffix="resource?.unit_label || ''"
          :rules="quantityRules"
          :error="hasQuantityError"
          :error-messages="quantityErrorMessage"
          autofocus
          class="quantity-input"
          @update:model-value="validateQuantity"
        />
      </div>

      <!-- Причины списания (опционально) -->
      <div v-if="showReasonSection" class="reasons-section">
        <div class="section-title">Причина списания (необязательно)</div>
        
        <VRadioGroup
          v-model="localReason"
        >
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

    <!-- Кнопки действий -->
    <template #actions>
      <div class="actions-wrapper">
        <VBtn
          v-if="hasExistingValue"
          color="error"
          variant="outlined"
          class="action-btn clear-btn"
          @click="handleClear"
        >
          <VIcon start>mdi-delete-outline</VIcon>
          Очистить
        </VBtn>
        <VBtn
          color="primary"
          class="action-btn apply-btn ios-apply-btn"
          :disabled="!isValid"
          @click="handleApply"
        >
          Применить
        </VBtn>
      </div>
    </template>
  </IOSBottomSheet>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import IOSBottomSheet from '@/components/IOSBottomSheet.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  resource: {
    type: Object,
    default: null
  },
  initialQuantity: {
    type: [Number, String],
    default: null
  },
  initialReason: {
    type: String,
    default: null
  },
  initialComment: {
    type: String,
    default: ''
  },
  showReasonSection: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'apply', 'clear'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Refs
const quantityInputRef = ref(null)

// Локальные данные
const localQuantity = ref(null)
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
const currentWriteOffValue = computed(() => {
  return parseFloat(localQuantity.value) || 0
})

const hasExistingValue = computed(() => {
  return props.initialQuantity && parseFloat(props.initialQuantity) > 0
})

const hasQuantityError = computed(() => {
  if (!localQuantity.value) return false
  const value = parseFloat(localQuantity.value)
  const actualValue = parseFloat(props.resource?.value_actual?.raw) || 0
  return value > actualValue
})

const quantityErrorMessage = computed(() => {
  if (hasQuantityError.value) {
    return 'Количество превышает текущий остаток'
  }
  return ''
})

const quantityRules = computed(() => [
  v => !!v || 'Укажите количество',
  v => parseFloat(v) > 0 || 'Количество должно быть больше 0',
  v => parseFloat(v) <= parseFloat(props.resource?.value_actual?.raw || 0) || 'Количество превышает текущий остаток'
])

const isValid = computed(() => {
  if (!localQuantity.value || parseFloat(localQuantity.value) <= 0) return false
  if (hasQuantityError.value) return false
  // Причина теперь необязательна
  return true
})

// Методы
const validateQuantity = () => {
  // Дополнительная валидация при необходимости
}

const calculateRemaining = () => {
  if (!props.resource?.value_actual) return null
  
  const actualValue = parseFloat(props.resource.value_actual.raw) || 0
  const writeOffValue = currentWriteOffValue.value
  
  if (writeOffValue === 0) return null
  
  const remaining = actualValue - writeOffValue
  return `${remaining.toFixed(2)} ${props.resource.unit_label}`
}

const getRemainingClass = () => {
  if (!props.resource?.value_actual) return ''
  
  const actualValue = parseFloat(props.resource.value_actual.raw) || 0
  const writeOffValue = currentWriteOffValue.value
  const remaining = actualValue - writeOffValue
  
  if (remaining < 0) return 'negative-remaining'
  if (remaining === 0) return 'zero-remaining'
  return 'positive-remaining'
}

const handleApply = () => {
  if (!isValid.value) return
  
  emit('apply', {
    quantity: parseFloat(localQuantity.value),
    reason: localReason.value || null,
    comment: localReason.value === 'other' ? localComment.value : ''
  })
  
  handleClose()
}

const handleClear = () => {
  emit('clear')
  handleClose()
}

const handleClose = () => {
  isOpen.value = false
}

const resetForm = () => {
  localQuantity.value = props.initialQuantity || null
  localReason.value = props.initialReason || null
  localComment.value = props.initialComment || ''
}

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    resetForm()
    // Устанавливаем фокус на поле ввода после открытия
    nextTick(() => {
      if (quantityInputRef.value) {
        quantityInputRef.value.focus()
      }
    })
  }
})

watch(() => localReason.value, (newValue) => {
  if (newValue !== 'other') {
    localComment.value = ''
  }
})
</script>

<style scoped>
.write-off-editor {
  padding: 20px;
}

.resource-info {
  border: 1px solid rgba(var(--v-theme-surface-variant), 0.5);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 24px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.info-row:last-child {
  margin-bottom: 0;
}

.info-label {

}

.info-value {
  font-size: 1rem;
  font-weight: 600;
}

.positive-remaining {
  color: rgb(var(--v-theme-success));
}

.zero-remaining {
  color: rgb(var(--v-theme-warning));
}

.negative-remaining {
  color: rgb(var(--v-theme-error));
}

.quantity-section {
  margin-bottom: 24px;
}

.quantity-input {
  font-size: 1.1rem;
}

.quantity-input :deep(input) {
  font-weight: 600;
}

.quantity-input :deep(.v-text-field__suffix) {
  opacity: 1 !important;
  visibility: visible !important;
  transition: none !important;
}

.reasons-section {
  margin-bottom: 16px;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  margin-bottom: 12px;
}

.reason-radio {
  margin-bottom: 4px;
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

.actions-wrapper {
  display: flex;
  gap: 12px;
  width: 100%;
}

.action-btn {
  height: 48px !important;
  border-radius: 12px !important;
  font-weight: 600;
  text-transform: none !important;
  letter-spacing: 0 !important;
}

.clear-btn {
  flex: 0 0 auto;
  min-width: 120px;
}

.apply-btn {
  flex: 1;
}

/* Темная тема */
.v-theme--dark .resource-info {
  background: rgba(var(--v-theme-surface-variant), 0.3);
}
</style>
