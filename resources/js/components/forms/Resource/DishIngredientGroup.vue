<template>
  <div class="resource-group mb-6">
    <div 
      class="resource-group-header d-flex align-center justify-space-between pa-4"
      :class="{ 'rounded-b-0': isOpen }"
    >
      <div class="d-flex align-center">
        <div class="group-handle me-3">
            <VBtn
              variant="tonal"
              color="secondary"
              size="small"
              icon
              class="resource-handle"
              title="Поменять порядок"
            >
              <VIcon size="16" icon="mdi-sort" />
            </VBtn>
        </div>
            
        <VTextField
          v-if="isOpen"
          :model-value="props.data.name"
          @update:model-value="updateGroupName"
          variant="outlined"
          density="compact"
          placeholder="Название группы ингредиентов..."
          hide-details
          class="group-name-input"
        />
        <div v-else class="text-h6">
          {{ props.data.name || `Группа ${props.index + 1}` }}
        </div>
      </div>
      
      <div class="d-flex">
        <VBtn
          v-if="props.index !== 0"
          variant="tonal"
          color="error"
          size="small"
          icon
          class="me-2"
          @click="removeResourceGroup"
        >
          <VIcon size="20" icon="mdi-trashcan-outline" />
        </VBtn>
        
        <VBtn
          variant="tonal"
          color="primary"
          size="small"
          icon
          @click="toggleGroup"
        >
          <VIcon 
            size="20" 
            :icon="isOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'" 
          />
        </VBtn>
      </div>
    </div>
    
    <div v-if="isOpen" class="resource-group-content pa-4">
      <!-- Заголовки для десктопа -->
      <div class="resource-headers mb-3">
        <div class="header-cell header-ingredient">Ингредиент</div>
        <div class="header-cell header-brutto">Брутто, г</div>
        <div class="header-cell header-netto">Нетто, г</div>
        <div class="header-cell header-output">Выход, г</div>
        <div class="header-cell header-cost">Себестоимость</div>
        <div class="header-cell header-actions">Действия</div>
      </div>

      <div v-if="props.data.resources.length === 0" class="text-center pa-8">
          <VIcon size="64" color="grey-lighten-1">mdi-food-apple-outline</VIcon>
          <p class="text-h6 mt-4 text-grey-lighten-1">Нет ингредиентов</p>
          <p class="text-body-2 text-grey-lighten-1">Добавьте первый ингредиент</p>
      </div>

      <!-- Список ресурсов -->
      <div 
        v-else 
        class="resources-list"
      >
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
              <DishIngredientResource 
                :id="index" 
                :data="element"
                :allow-sorting="props.data.resources.length > 1"
                @remove-resource="removeResource"
                @update-resource-weight="updateResourceWeight"
                @update-resource-losses="updateResourceLosses"
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
                <span class="total-label">Выход:</span>
                <strong>{{ props.totals.output.toFixed(2) }} г</strong>
              </div>
              <div class="total-item">
                <span class="total-label">Себестоимость:</span>
                <strong>{{ props.totals.cost.toFixed(2) }}</strong>
              </div>
            </div>
          </div>
          
          <!-- Десктопная версия итогов -->
          <div class="totals-desktop">
            <div class="total-cell total-ingredient">
              <strong>Итого:</strong>
            </div>
            <div class="total-cell total-brutto"></div>
            <div class="total-cell total-netto"></div>
            <div class="total-cell total-output">
              <strong>{{ props.totals.output.toFixed(2) }}</strong>
            </div>
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
            @click="$emit('open-ingredient-modal', props.id)"
            variant="outlined"
            color="primary"
            prepend-icon="mdi-plus"
            size="small"
          >
            Добавить ингредиенты
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
  index: {
    type: Number,
    required: true,
  },
  data: {
    type: Object,
    required: true,
  },
  totals: {
    type: Object,
    default: () => ({
      brutto: 0,
      netto: 0,
      output: 0,
      cost: 0
    })
  }
})

const emit = defineEmits([
  'remove-resource-group', 
  'open-ingredient-modal',
  'update-resource-weight',
  'update-resource-losses',
  'remove-resource',
  'update:opened'
])

const isOpen = ref(props.index === 0 || props.data.opened === true || (props.data.resources && props.data.resources.length > 0))

const toggleGroup = () => {
  isOpen.value = !isOpen.value
  emit('update:opened', props.id, isOpen.value)
}

const removeResourceGroup = () => {
  if (props.index === 0) return
  emit('remove-resource-group', props.id)
}

const updateGroupName = (value) => {
  // Обновляем название группы через родительский компонент
  const updatedData = { ...props.data, name: value }
  // Эмитим событие для обновления данных группы
  emit('update-group-data', props.id, updatedData)
}

const updateResources = (newResources) => {
  // Обновляем ресурсы в группе
  const updatedData = { ...props.data, resources: newResources }
  emit('update-group-data', props.id, updatedData)
}

const removeResource = (resourceIndex) => {
  emit('remove-resource', props.id, resourceIndex)
}

const updateResourceWeight = (resourceIndex, field, value) => {
  emit('update-resource-weight', props.id, resourceIndex, field, value)
}

const updateResourceLosses = (resourceIndex, losses) => {
  emit('update-resource-losses', props.id, resourceIndex, losses)
}

watch(() => props.data.opened, (newValue) => {
  if (newValue !== isOpen.value) {
    isOpen.value = newValue
  }
})
</script>

<style scoped>
.resource-group {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.resource-group:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.resource-group-header {
  background-color: var(--v-theme-surface);
  border-radius: 8px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.group-name-input {
  width: 30%;
  min-width: 250px;
  max-width: 400px;
}

.resource-group-content {
  background-color: white;
  border-radius: 0 0 8px 8px;
}

/* Заголовки для десктопа */
.resource-headers {
  display: grid;
  grid-template-columns: 2fr 120px 120px 120px 140px 120px;
  gap: 16px;
  padding: 12px 16px;
  background-color: rgba(var(--v-theme-primary), 0.05);
  border-radius: 8px;
  align-items: center;
}

.header-cell {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--v-theme-on-surface);
  text-align: center;
}

.header-ingredient {
  text-align: left;
}

/* Список ресурсов */
.resources-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Итоги */
.totals-item {
  background-color: rgba(var(--v-theme-primary), 0.05);
  border: 1px solid rgba(var(--v-theme-primary), 0.2);
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
  color: var(--v-theme-primary);
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
  grid-template-columns: 2fr 120px 120px 120px 140px 120px;
  gap: 16px;
  align-items: center;
}

.total-cell {
  text-align: center;
  color: var(--v-theme-primary);
}

.total-ingredient {
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
