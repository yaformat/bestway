<template>
  <div :class="['toggle-filter', `toggle-filter--${variant}`]">
    <VSwitch
      v-if="!inline"
      :model-value="isToggled"
      :label="filterData.label"
      color="primary"
      density="compact"
      hide-details
      class="toggle-switch"
      @update:model-value="handleToggle"
    />
    
    <!-- Inline режим - только переключатель без лейбла -->
    <VSwitch
      v-else
      :model-value="isToggled"
      color="primary"
      density="compact"
      hide-details
      class="toggle-switch toggle-switch--inline"
      @update:model-value="handleToggle"
    />
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
    default: 'default' // 'default', 'dropdown', 'modal', 'modal-mobile'
  },
  inline: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const isToggled = computed(() => {
  return Array.isArray(props.modelValue) && props.modelValue.length > 0
})

const handleToggle = (checked) => {
  const newValue = checked ? ['1'] : []
  emit('update:modelValue', newValue)
}
</script>

<style scoped>
.toggle-filter {
  padding: 0.5rem 0;
}

.toggle-filter--dropdown {
  padding: 0.25rem 0;
}

.toggle-filter--modal,
.toggle-filter--modal-mobile {
  padding: 0;
}

.toggle-switch {
  margin: 0;
}

.toggle-switch--inline {
  min-height: auto;
}

.toggle-switch :deep(.v-selection-control) {
  min-height: auto;
}

.toggle-switch :deep(.v-label) {
  font-weight: 500;
  opacity: 1;
}
</style>
