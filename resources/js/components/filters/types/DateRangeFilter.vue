<template>
  <div :class="['date-range-filter', `date-range-filter--${variant}`]">
    <VTextField
      :model-value="modelValue.from"
      :placeholder="fromPlaceholder"
      type="date"
      :density="inputDensity"
      variant="outlined"
      hide-details
      class="date-input"
      @update:model-value="updateRange('from', $event)"
    />
    <span class="date-separator">—</span>
    <VTextField
      :model-value="modelValue.to"
      :placeholder="toPlaceholder"
      type="date"
      :density="inputDensity"
      variant="outlined"
      hide-details
      class="date-input"
      @update:model-value="updateRange('to', $event)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  },
  filterData: {
    type: Object,
    required: true
  },
  variant: {
    type: String,
    default: 'default' // 'default', 'dropdown', 'quick'
  }
})

const emit = defineEmits(['update:modelValue'])

const inputDensity = computed(() => {
  return props.variant === 'quick' ? 'comfortable' : 'compact'
})

const fromPlaceholder = computed(() => {
  if (props.filterData.minDate) {
    return `От ${formatDateForDisplay(props.filterData.minDate)}`
  }
  return 'От'
})

const toPlaceholder = computed(() => {
  if (props.filterData.maxDate) {
    return `До ${formatDateForDisplay(props.filterData.maxDate)}`
  }
  return 'До'
})

const formatDateForDisplay = (dateString) => {
  if (!dateString) return ''
  
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch (error) {
    return dateString
  }
}

const updateRange = (type, value) => {
  const newValue = { ...props.modelValue }
  
  if (value && value !== '') {
    // Валидация даты
    if (isValidDate(value)) {
      newValue[type] = value
    }
  } else {
    delete newValue[type]
  }
  
  // Дополнительная валидация: "от" не должно быть больше "до"
  if (newValue.from && newValue.to && newValue.from > newValue.to) {
    if (type === 'from') {
      newValue.to = newValue.from
    } else {
      newValue.from = newValue.to
    }
  }
  
  emit('update:modelValue', newValue)
}

const isValidDate = (dateString) => {
  const date = new Date(dateString)
  return date instanceof Date && !isNaN(date)
}
</script>

<style scoped>
.date-range-filter {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1rem 0;
}

.date-range-filter--dropdown {
  margin: 0;
  gap: 0.75rem;
  min-width:320px;
}

.date-range-filter--quick {
  gap: 1.25rem;
  margin: 0.5rem 0;
}

.date-input {
  flex: 1;
}

.date-separator {
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 1rem;
}

.date-range-filter--quick .date-separator {
  font-size: 1.125rem;
}

/* Стили для date input */
.date-input :deep(.v-field__input) {
  cursor: pointer;
}

.date-input :deep(input[type="date"]) {
  cursor: pointer;
}

/* Скрываем иконку календаря в WebKit браузерах если нужно */
.date-input :deep(input[type="date"]::-webkit-calendar-picker-indicator) {
  cursor: pointer;
  opacity: 0.6;
}

.date-input :deep(input[type="date"]::-webkit-calendar-picker-indicator:hover) {
  opacity: 1;
}
</style>
