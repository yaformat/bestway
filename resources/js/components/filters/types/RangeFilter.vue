<template>
  <div :class="['range-filter', `range-filter--${variant}`]">
    <VTextField
      :model-value="modelValue.from"
      :placeholder="`От ${filterData.min || 0}`"
      type="number"
      inputmode="numeric"
      min="0"
      :density="inputDensity"
      variant="outlined"
      hide-details
      class="range-input"
      @update:model-value="updateRange('from', $event)"
    />
    <span class="range-separator">—</span>
    <VTextField
      :model-value="modelValue.to"
      :placeholder="`До ${filterData.max || 100000}`"
      type="number"
      inputmode="numeric"
      min="0"
      :density="inputDensity"
      variant="outlined"
      hide-details
      class="range-input"
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

const updateRange = (type, value) => {
  const newValue = { ...props.modelValue }
  
  if (value && value !== '') {
    newValue[type] = parseInt(value)
  } else {
    delete newValue[type]
  }
  
  emit('update:modelValue', newValue)
}
</script>

<style scoped>
.range-filter {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1rem 0;
}

.range-filter--dropdown {
  margin: 0;
  gap: 0.75rem;
}

.range-filter--quick {
  gap: 1.25rem;
  margin: 0.5rem 0;
}

.range-input {
  flex: 1;
}

.range-separator {
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 1rem;
}

.range-filter--quick .range-separator {
  font-size: 1.125rem;
}
</style>
