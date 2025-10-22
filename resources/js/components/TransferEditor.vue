<template>
  <IOSBottomSheet
    v-model="isOpen"
    :title="resource?.name || 'Перемещение позиции'"
    :show-close-button="true"
    close-button-mode="close"
    max-height="85vh"
    @close="handleClose"
  >
    <div class="transfer-editor">
      <!-- Информация о ресурсе -->
      <div class="resource-info">
        <div class="info-row">
          <span class="info-label">Остаток на складе {{ stockFrom?.name }}:</span>
          <span class="info-value">{{ resource?.value_actual?.display || '0' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Остаток на складе {{ stockTo?.name }}:</span>
          <span class="info-value">{{ resource?.value_to?.display || '0' }}</span>
        </div>
      </div>

      <!-- Поле ввода количества -->
      <div class="quantity-section">
        <VTextField
          ref="quantityInputRef"
          v-model="localQuantity"
          type="number"
          label="Количество для перемещения"
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

      <!-- Предпросмотр остатков после перемещения -->
      <div v-if="currentTransferValue > 0" class="preview-section">
        <div class="preview-title">После перемещения:</div>
        <div class="preview-balances">
          <div class="preview-row">
            <div class="preview-label">
              <VIcon size="18" color="error">mdi-arrow-up</VIcon>
              {{ stockFrom?.name }}:
            </div>
            <div 
              :class="[
                'preview-value',
                getRemainingFromClass()
              ]"
            >
              {{ calculateRemainingFrom() }}
            </div>
          </div>
          <div class="preview-row">
            <div class="preview-label">
              <VIcon size="18" color="success">mdi-arrow-down</VIcon>
              {{ stockTo?.name }}:
            </div>
            <div class="preview-value positive-balance">
              {{ calculateRemainingTo() }}
            </div>
          </div>
        </div>
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
  stockFrom: {
    type: Object,
    default: null
  },
  stockTo: {
    type: Object,
    default: null
  },
  initialQuantity: {
    type: [Number, String],
    default: null
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

// Вычисляемые свойства
const currentTransferValue = computed(() => {
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
  return true
})

// Методы
const validateQuantity = () => {
  // Дополнительная валидация при необходимости
}

const calculateRemainingFrom = () => {
  if (!props.resource?.value_actual) return null
  
  const actualValue = parseFloat(props.resource.value_actual.raw) || 0
  const transferValue = currentTransferValue.value
  
  if (transferValue === 0) return null
  
  const remaining = actualValue - transferValue
  return `${remaining.toFixed(2)} ${props.resource.unit_label}`
}

const calculateRemainingTo = () => {
  if (!props.resource?.value_to) return null
  
  const toValue = parseFloat(props.resource.value_to.raw) || 0
  const transferValue = currentTransferValue.value
  
  if (transferValue === 0) return null
  
  const remaining = toValue + transferValue
  return `${remaining.toFixed(2)} ${props.resource.unit_label}`
}

const getRemainingFromClass = () => {
  if (!props.resource?.value_actual) return ''
  
  const actualValue = parseFloat(props.resource.value_actual.raw) || 0
  const transferValue = currentTransferValue.value
  const remaining = actualValue - transferValue
  
  if (remaining < 0) return 'negative-balance'
  if (remaining === 0) return 'zero-balance'
  return 'positive-balance'
}

const handleApply = () => {
  if (!isValid.value) return
  
  emit('apply', {
    quantity: parseFloat(localQuantity.value)
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
</script>

<style scoped>
.transfer-editor {
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

.preview-section {
  background: rgba(var(--v-theme-primary), 0.08);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}

.preview-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  margin-bottom: 12px;
}

.preview-balances {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.preview-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.preview-label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.preview-value {
  font-size: 1rem;
  font-weight: 600;
}

.positive-balance {
  color: rgb(var(--v-theme-success));
}

.zero-balance {
  color: rgb(var(--v-theme-warning));
}

.negative-balance {
  color: rgb(var(--v-theme-error));
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

.v-theme--dark .preview-section {
  background: rgba(var(--v-theme-primary), 0.15);
}
</style>
