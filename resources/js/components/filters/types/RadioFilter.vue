<template>
  <VRadioGroup
    :model-value="getRadioValue()"
    color="primary"
    :class="['radio-filter', `radio-filter--${variant}`]"
    @update:model-value="setRadioValue"
  >
    <VRadio
      v-for="option in filterData.options"
      :key="option.value"
      :value="option.value"
      :label="option.name"
      :density="radioDensity"
      class="radio-option"
    />
  </VRadioGroup>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
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

const radioDensity = computed(() => {
  return props.variant === 'quick' ? 'comfortable' : 'compact'
})

const getRadioValue = () => {
  if (!Array.isArray(props.modelValue) || props.modelValue.length === 0) {
    return null
  }
  
  const selectedValue = props.modelValue[0]
  const matchingOption = props.filterData.options?.find(option => option.value == selectedValue)
  return matchingOption ? matchingOption.value : selectedValue
}

const setRadioValue = (value) => {
  if (value === null) {
    // Снимаем выбор
    emit('update:modelValue', [])
  } else {
    const currentValue = getRadioValue()
    if (currentValue === value) {
      // Если кликнули на уже выбранное значение, снимаем выбор
      emit('update:modelValue', [])
    } else {
      // Выбираем новое значение
      emit('update:modelValue', [value])
    }
  }
}
</script>

<style scoped>
.radio-filter {
  margin: 1rem 0;
}

.radio-filter--dropdown {
  max-height: 250px;
  margin: 0;
  overflow-y: auto;
}

.radio-filter--quick {
  max-height: 300px;
  margin: 0.5rem 0;
}

.radio-option {
  margin-bottom: 0.5rem;
}

.radio-filter--quick .radio-option {
  margin-bottom: 0.75rem;
}

.radio-filter::-webkit-scrollbar {
  width: 6px;
}

.radio-filter::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-radius: 3px;
}

.radio-filter::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.3);
  border-radius: 3px;
}

.radio-filter::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-primary), 0.5);
}
</style>
