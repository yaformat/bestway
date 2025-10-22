<template>
  <div class="quantity-input-wrapper">
    <!-- Лейбл (опционально) -->
    <label v-if="label" class="quantity-label" :for="inputId">
      {{ label }}
    </label>
    
    <!-- Группа инпута с кнопками -->
    <div class="quantity-input-group" :class="{ 'has-error': hasError }">
      <!-- Кнопка уменьшения -->
      <VBtn
        :id="`${inputId}-decrease`"
        class="quantity-btn quantity-btn-left"
        :class="{ 'quantity-btn-disabled': isDecreaseDisabled }"
        :disabled="isDecreaseDisabled || disabled"
        :size="size"
        variant="outlined"
        :color="color"
        @click="decrease"
        @mousedown.prevent
      >
        <VIcon icon="mdi-minus" :size="iconSize" />
      </VBtn>
      
      <!-- Поле ввода -->
      <VTextField
        :id="inputId"
        :model-value="displayValue"
        type="number"
        inputmode="numeric"
        class="quantity-input"
        :class="inputClass"
        variant="outlined"
        :density="density"
        :disabled="disabled"
        :readonly="readonly"
        :min="min"
        :max="max"
        :step="step"
        hide-details
        @update:model-value="handleInput"
        @blur="handleBlur"
        @keydown="handleKeydown"
      />
      
      <!-- Кнопка увеличения -->
      <VBtn
        :id="`${inputId}-increase`"
        class="quantity-btn quantity-btn-right"
        :class="{ 'quantity-btn-disabled': isIncreaseDisabled }"
        :disabled="isIncreaseDisabled || disabled"
        :size="size"
        variant="outlined"
        :color="color"
        @click="increase"
        @mousedown.prevent
      >
        <VIcon icon="mdi-plus" :size="iconSize" />
      </VBtn>
    </div>
    
    <!-- Сообщение об ошибке -->
    <div v-if="errorMessage" class="quantity-error">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: 0
  },
  label: {
    type: String,
    default: ''
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: Infinity
  },
  step: {
    type: Number,
    default: 1
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['x-small', 'small', 'default', 'large', 'x-large'].includes(value)
  },
  density: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'comfortable', 'compact'].includes(value)
  },
  color: {
    type: String,
    default: 'primary'
  },
  debounce: {
    type: Number,
    default: 0
  },
  validateOnInput: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus'])

// Генерируем уникальный ID для инпута
const inputId = ref(`quantity-input-${Math.random().toString(36).substr(2, 9)}`)

// Внутреннее значение для обработки ввода
const internalValue = ref(props.modelValue)
const hasError = ref(false)
const errorMessage = ref('')

// Дебаунс таймер
let debounceTimer = null

// Вычисляемые свойства
const displayValue = computed(() => {
  const value = Number(internalValue.value)
  return isNaN(value) ? 0 : value
})

const isDecreaseDisabled = computed(() => {
  return displayValue.value <= props.min
})

const isIncreaseDisabled = computed(() => {
  return displayValue.value >= props.max
})

const iconSize = computed(() => {
  switch (props.size) {
    case 'x-small': return 'x-small'
    case 'small': return 'small'
    case 'large': return 'default'
    case 'x-large': return 'large'
    default: return 'small'
  }
})

const inputClass = computed(() => {
  return {
    'quantity-input-xs': props.size === 'x-small',
    'quantity-input-sm': props.size === 'small',
    'quantity-input-lg': props.size === 'large',
    'quantity-input-xl': props.size === 'x-large'
  }
})

// Методы
const validateValue = (value) => {
  const numValue = Number(value)
  
  if (isNaN(numValue)) {
    return { isValid: false, message: 'Введите корректное число' }
  }
  
  if (numValue < props.min) {
    return { isValid: false, message: `Минимальное значение: ${props.min}` }
  }
  
  if (numValue > props.max) {
    return { isValid: false, message: `Максимальное значение: ${props.max}` }
  }
  
  return { isValid: true, message: '' }
}

const updateValue = (newValue, emitChange = true) => {
  const numValue = Math.max(props.min, Math.min(props.max, Number(newValue) || 0))
  
  internalValue.value = numValue
  hasError.value = false
  errorMessage.value = ''
  
  if (props.debounce > 0 && emitChange) {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
      emit('update:modelValue', numValue)
      emit('change', numValue)
    }, props.debounce)
  } else if (emitChange) {
    emit('update:modelValue', numValue)
    emit('change', numValue)
  }
}

const handleInput = (value) => {
  if (props.validateOnInput) {
    const validation = validateValue(value)
    hasError.value = !validation.isValid
    errorMessage.value = validation.message
  }
  
  internalValue.value = value
  
  if (!hasError.value) {
    updateValue(value)
  }
}

const handleBlur = (event) => {
  const value = event.target.value
  const validation = validateValue(value)
  
  if (!validation.isValid) {
    // Возвращаем к последнему валидному значению
    nextTick(() => {
      updateValue(props.modelValue, false)
    })
  } else {
    updateValue(value)
  }
  
  hasError.value = false
  errorMessage.value = ''
  emit('blur', event)
}

const handleKeydown = (event) => {
  // Разрешаем только цифры, стрелки, backspace, delete, tab
  const allowedKeys = [
    'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight',
    'Backspace', 'Delete', 'Tab', 'Enter', 'Escape'
  ]
  
  if (event.key === 'ArrowUp') {
    event.preventDefault()
    increase()
  } else if (event.key === 'ArrowDown') {
    event.preventDefault()
    decrease()
  } else if (event.key === 'Enter') {
    event.target.blur()
  } else if (!allowedKeys.includes(event.key) && !/^\d$/.test(event.key)) {
    event.preventDefault()
  }
}

const increase = () => {
  if (!isIncreaseDisabled.value && !props.disabled) {
    updateValue(displayValue.value + props.step)
  }
}

const decrease = () => {
  if (!isDecreaseDisabled.value && !props.disabled) {
    updateValue(displayValue.value - props.step)
  }
}

// Следим за изменениями modelValue извне
import { watch } from 'vue'

watch(() => props.modelValue, (newValue) => {
  if (newValue !== internalValue.value) {
    internalValue.value = newValue
  }
}, { immediate: true })
</script>

<style scoped>
.quantity-input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.quantity-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
  margin-bottom: 4px;
}

.quantity-input-group {
  display: flex;
  align-items: stretch;
  position: relative;
  width: fit-content;
  height: 40px;
}

.quantity-input-group.has-error {
  --v-border-color: rgb(var(--v-theme-error));
}

.quantity-btn {
  flex-shrink: 0;
  min-width: 40px !important;
  height: auto !important;
  border-radius: 4px !important;
  z-index: 1;
}

.quantity-btn-left {
  border-top-right-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
  border-right: none !important;
}

.quantity-btn-right {
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  border-left: none !important;
}

.quantity-btn-disabled {
  opacity: 0.6;
}

.quantity-input {
  flex: 1 1 auto;
  min-width: 50px;
  max-width: 120px;
}

.quantity-input :deep(.v-field) {
  border-radius: 0 !important;
}

.quantity-input :deep(.v-field__input) {
  text-align: center;
  padding: 0 8px;
  min-height: 100%;
  height: 100%;
}

.quantity-input :deep(.v-field__outline__start) {
  border-radius: 0 !important;
}

.quantity-input :deep(.v-field__outline__end) {
  border-radius: 0 !important;
}

/* Размеры для разных size */
.quantity-input-xs {
  min-width: 40px;
  max-width: 80px;
}

.quantity-input-xs :deep(.v-field__input) {
  padding: 0 4px;
  font-size: 0.75rem;
}

.quantity-input-sm {
  min-width: 45px;
  max-width: 90px;
}

.quantity-input-sm :deep(.v-field__input) {
  padding: 0 6px;
  font-size: 0.875rem;
}

.quantity-input-lg {
  min-width: 60px;
  max-width: 140px;
}

.quantity-input-lg :deep(.v-field__input) {
  padding: 0 12px;
  font-size: 1.125rem;
}

.quantity-input-xl {
  min-width: 70px;
  max-width: 160px;
}

.quantity-input-xl :deep(.v-field__input) {
  padding: 0 16px;
  font-size: 1.25rem;
}

.quantity-error {
  font-size: 0.75rem;
  color: rgb(var(--v-theme-error));
  margin-top: 2px;
}

/* Убираем стрелки у number input в Chrome/Safari */
.quantity-input :deep(input[type="number"]::-webkit-outer-spin-button),
.quantity-input :deep(input[type="number"]::-webkit-inner-spin-button) {
  -webkit-appearance: none;
  margin: 0;
}

/* Убираем стрелки у number input в Firefox */
.quantity-input :deep(input[type="number"]) {
  -moz-appearance: textfield;
}

/* Адаптация для мобильных устройств */
@media (max-width: 600px) {
  .quantity-btn {
    min-width: 36px !important;
  }
  
  .quantity-input {
    min-width: 45px;
  }
  
  .quantity-input-xs {
    min-width: 35px;
  }
  
  .quantity-input-sm {
    min-width: 40px;
  }
}
</style>
