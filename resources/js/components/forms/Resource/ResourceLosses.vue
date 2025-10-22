<template>
  <div class="resource-losses">
    <VRow>
      <VCol
        v-for="loss in losses"
        :key="loss.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <div class="d-flex align-center">
          <VTooltip location="top">
            <template v-slot:activator="{ props }">
              <VAvatar
                v-bind="props"
                color="primary"
                variant="tonal"
                size="36"
                class="mr-3"
              >
                <VIcon :icon="lossIcons[loss.id] || 'mdi-percent'" />
              </VAvatar>
            </template>
            <span>{{ getLossDescription(loss.id) }}</span>
          </VTooltip>
          <VTextField
            :model-value="loss.value"
            @update:model-value="updateLoss(loss.id, $event)"
            :label="loss.name"
            type="number"
            min="0"
            max="100"
            density="compact"
            hide-details
            suffix="%"
            class="flex-grow-1"
          />
        </div>
      </VCol>
    </VRow>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const losses = computed(() => props.modelValue || [])

// Иконки для типов потерь
const lossIcons = {
  peeling: 'mdi-knife',
  boiling: 'mdi-pot-steam',
  frying: 'mdi-pan',
  stewing: 'mdi-pot-mix',
  baking: 'mdi-toaster-oven'
}

// Описания для типов потерь
const lossDescriptions = {
  peeling: 'Потери при очистке продукта (удаление кожуры, косточек и т.д.)',
  boiling: 'Потери при варке продукта',
  frying: 'Потери при жарке продукта',
  stewing: 'Потери при тушении продукта',
  baking: 'Потери при запекании продукта'
}

const getLossDescription = (lossId) => {
  return lossDescriptions[lossId] || 'Потери при обработке'
}

const updateLoss = (lossId, value) => {
  const newLosses = [...losses.value]
  const lossIndex = newLosses.findIndex(loss => loss.id === lossId)
  if (lossIndex !== -1) {
    newLosses[lossIndex] = {
      ...newLosses[lossIndex],
      value: Number(value)
    }
    emit('update:modelValue', newLosses)
  }
}
</script>
