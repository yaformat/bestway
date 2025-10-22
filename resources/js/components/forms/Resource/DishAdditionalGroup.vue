<template>
  <div class="additional-group mb-6">
    <div 
      class="additional-group-header d-flex align-center justify-space-between pa-4"
      :class="{ 'rounded-b-0': isOpen }"
      @click="toggleGroup"
    >
      <div class="d-flex align-center">
        <VIcon icon="mdi-plus-circle" class="me-3" color="secondary" size="24" />
        <div class="text-h6">
          {{ props.data.name || 'Дополнительные ресурсы' }}
        </div>
      </div>
      
      <div class="d-flex">
        <VBtn
          variant="tonal"
          color="secondary"
          size="small"
          icon
        >
          <VIcon 
            size="20" 
            :icon="isOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'" 
          />
        </VBtn>
      </div>
    </div>
    
    <div v-if="isOpen" class="additional-group-content pa-4">
      <!-- Заголовки для десктопа -->
      <div class="resource-headers mb-3">
        <div class="header-cell header-resource">Ресурс</div>
        <div class="header-cell header-quantity">Количество</div>
        <div class="header-cell header-cost">Себестоимость</div>
        <div class="header-cell header-actions">Действия</div>
      </div>

      <!-- Список дополнительных ресурсов -->
      <div class="resources-list">

        <draggable
          :model-value="props.data.resources"
          @update:model-value="updateResources"
          tag="div"
          class="draggable-list"
          item-key="id"
          handle=".resource-handle"
          :animation="200"
          ghost-class="ghost"
          chosen-class="chosen"
          drag-class="drag"
        >
          <template #item="{ element, index }">
            <div class="draggable-item">
              <DishAdditionalResource 
                :id="index" 
                :data="element"
                :allow-sorting="props.data.resources.length > 1"
                @remove-resource="removeResource"
                @update-resource-quantity="updateResourceQuantity"
              />
            </div>
          </template>
        </draggable>

        <!-- Строка с итогами -->
        <div v-if="props.data.resources && props.data.resources.length > 0" class="totals-item">
          <!-- Мобильная версия итогов -->
          <div class="totals-mobile">
            <div class="totals-label mb-2">
              <strong>Итого:</strong>
            </div>
            <div class="totals-values">
              <div class="total-item">
                <span class="total-label">Себестоимость:</span>
                <strong>{{ props.totals.cost.toFixed(2) }}</strong>
              </div>
            </div>
          </div>
          
          <!-- Десктопная версия итогов -->
          <div class="totals-desktop">
            <div class="total-cell total-resource">
              <strong>Итого:</strong>
            </div>
            <div class="total-cell total-quantity"></div>
            <div class="total-cell total-cost">
              <strong>{{ props.totals.cost.toFixed(2) }}</strong>
            </div>
            <div class="total-cell total-actions"></div>
          </div>
        </div>
      </div>
      
      <VRow class="justify-end mt-4">
        <VCol cols="12" md="4" class="text-center">
          <VBtn
            @click="$emit('open-additional-modal', props.id)"
            variant="outlined"
            color="secondary"
            prepend-icon="mdi-plus"
            size="small"
          >
            Добавить ресурсы
          </VBtn>
        </VCol>
      </VRow>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
  data: {
    type: Object,
    required: true,
  },
  totals: {
    type: Object,
    default: () => ({
      cost: 0
    })
  }
})

const emit = defineEmits([
  'remove-additional-group', 
  'open-additional-modal',
  'update-resource-quantity',
  'remove-resource',
  'update:opened'
])

const isOpen = ref(props.data.opened === true || (props.data.resources && props.data.resources.length > 0))

const toggleGroup = () => {
  isOpen.value = !isOpen.value
  emit('update:opened', isOpen.value)
}

const removeAdditionalGroup = () => {
  emit('remove-additional-group', props.id)
}

const removeResource = (resourceIndex) => {
  emit('remove-resource', props.id, resourceIndex)
}

const updateResourceQuantity = (resourceIndex, value) => {
  emit('update-resource-quantity', props.id, resourceIndex, value)
}

watch(() => props.data.opened, (newValue) => {
  if (newValue !== isOpen.value) {
    isOpen.value = newValue
  }
})
</script>

<style scoped>
.additional-group {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border-left: 4px solid rgb(var(--v-theme-secondary));
}

.additional-group:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.additional-group-header {
  cursor: pointer;
  background-color: rgba(var(--v-theme-secondary), 0.05);
  border-radius: 8px;
  border-bottom: 1px solid rgba(var(--v-theme-secondary), 0.2);
}

.additional-group-content {
  background-color: white;
  border-radius: 0 0 8px 8px;
}

/* Заголовки для десктопа */
.resource-headers {
  display: grid;
  grid-template-columns: 2fr 150px 140px 120px;
  gap: 16px;
  padding: 12px 16px;
  background-color: rgba(var(--v-theme-secondary), 0.05);
  border-radius: 8px;
  align-items: center;
}

.header-cell {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--v-theme-on-surface);
  text-align: center;
}

.header-resource {
  text-align: left;
}

/* Список ресурсов */
.resources-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.resource-item {
  padding: 16px;
  background-color: white;
  border: 1px solid rgba(var(--v-theme-secondary), 0.2);
  border-radius: 8px;
}

.resource-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-secondary), 0.4);
}

/* Итоги */
.totals-item {
  background-color: rgba(var(--v-theme-secondary), 0.05);
  border: 1px solid rgba(var(--v-theme-secondary), 0.2);
  border-radius: 8px;
  padding: 16px;
  margin-top: 8px;
}

/* Мобильные итоги - скрываем по умолчанию */
.totals-mobile {
  display: none;
  flex-direction: column;
  gap: 8px;
}

.totals-label {
  font-size: 1rem;
  color: var(--v-theme-secondary);
}

.totals-values {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.total-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
}

.total-label {
  font-weight: 500;
  color: var(--v-theme-on-surface);
}

/* Десктопные итоги */
.totals-desktop {
  display: grid;
  grid-template-columns: 2fr 150px 140px 120px;
  gap: 16px;
  align-items: center;
}

.total-cell {
  text-align: center;
  color: var(--v-theme-secondary);
}

.total-resource {
  text-align: left;
}

/* Адаптивность */
@media (max-width: 1200px) {
  .resource-headers {
    display: none;
  }
  
  .totals-desktop {
    display: none;
  }
  
  .totals-mobile {
    display: flex;
  }
}

.draggable-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.ghost {
  opacity: 0.5;
}

.chosen {
  opacity: 0.8;
}
</style>
