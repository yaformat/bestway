<template>
  <div :class="['chips-filter', `chips-filter--${variant}`]">
    <VChip
      v-for="option in filterData.options"
      :key="option.value"
      :color="isOptionSelected(option.value) ? 'primary' : 'surface-variant'"
      :variant="isOptionSelected(option.value) ? 'flat' : 'outlined'"
      :size="chipSize"
      class="chip-option"
      @click="toggleOption(option.value)"
    >
      {{ option.name }}
    </VChip>
  </div>
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

const chipSize = computed(() => {
  switch (props.variant) {
    case 'quick':
      return 'default'
    case 'dropdown':
      return 'small'
    default:
      return 'small'
  }
})

const isOptionSelected = (optionValue) => {
  return props.modelValue.some(selectedValue => selectedValue == optionValue)
}

const toggleOption = (value) => {
  const current = [...props.modelValue]
  const index = current.findIndex(item => item == value)
  
  if (props.filterData?.multiple === false) {
    const newValue = current.some(item => item == value) ? [] : [value]
    emit('update:modelValue', newValue)
  } else {
    if (index > -1) {
      current.splice(index, 1)
    } else {
      current.push(value)
    }
    emit('update:modelValue', current)
  }
}
</script>

<style scoped>
.chips-filter {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin: 1rem 0;
}

.chips-filter--dropdown {
  margin: 0;
  gap: 0.4rem;
  max-height: 250px;
  overflow-y: auto;
}

.chips-filter--quick {
  gap: 0.75rem;
  margin: 0.5rem 0;
}

.chip-option {
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.chip-option:hover {
  transform: translateY(-1px);
}

.chips-filter--quick .chip-option {
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
}

.chips-filter--dropdown .chip-option {
  font-size: 0.8125rem;
}
</style>
