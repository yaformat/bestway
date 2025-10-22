<template>
  <div :class="['checkboxes-filter', `checkboxes-filter--${variant}`]">
    <VCheckbox
      v-for="option in filterData.options"
      :key="option.value"
      :model-value="isOptionSelected(option.value)"
      :label="option.name"
      color="primary"
      hide-details
      density="compact"
      class="checkbox-option"
      @update:model-value="toggleOption(option.value)"
    />
  </div>
</template>

<script setup>
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

const isOptionSelected = (optionValue) => {
  return props.modelValue.some(selectedValue => selectedValue == optionValue)
}

const toggleOption = (value) => {
  const current = [...props.modelValue]
  const index = current.findIndex(item => item == value)
  
  if (index > -1) {
    current.splice(index, 1)
  } else {
    current.push(value)
  }
  
  emit('update:modelValue', current)
}
</script>

<style scoped>
.checkboxes-filter {
  margin: 1rem 0;
}

.checkboxes-filter--dropdown {
  max-height: 300px;
  margin: 0;
  overflow-y: auto;
}

.checkboxes-filter--quick {
  max-height: 300px;
}

.checkbox-option {
  margin-bottom: 0.5rem;
}

.checkboxes-filter::-webkit-scrollbar {
  width: 6px;
}

.checkboxes-filter::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-radius: 3px;
}

.checkboxes-filter::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.3);
  border-radius: 3px;
}
</style>
