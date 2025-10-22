<template>
  <div class="dish-ingredients">

    <!-- Основные группы -->
    <draggable
      v-model="regularGroups"
      tag="div"
      class="groups-list"
      item-key="id"
      handle=".group-handle"
      :animation="200"
      ghost-class="ghost"
      chosen-class="chosen"
      drag-class="drag"
      :disabled="!canSort"
      @end="updateGroupsOrder"
    >
      <template #item="{ element, index }">
        <DishIngredientGroup
          :id="element.id"
          :index="index"
          :data="element"
          :totals="getGroupTotals(element)"
          :allow-sorting="canSort"
          :can-delete="index > 0"
          @remove-resource-group="confirmRemoveResourceGroup"
          @open-ingredient-modal="openIngredientModal"
          @update-resource-weight="handleUpdateResourceWeight"
          @update-resource-losses="handleUpdateResourceLosses"
          @remove-resource="confirmRemoveResource"
          @update:opened="updateGroupOpened"
        />
      </template>
    </draggable>

    <!-- Кнопка добавления основной группы -->
    <div class="text-center my-4">
      <VBtn
        color="primary"
        variant="tonal"
        prepend-icon="mdi-plus"
        @click="addResourceGroup"
        class="add-group-btn"
      >
        Добавить группу ингредиентов
      </VBtn>
    </div>
    
    <!-- Дополнительная группа -->
    <DishAdditionalGroup
      v-if="additionalGroup"
      :id="additionalGroup.id"
      :data="additionalGroup"
      :totals="getGroupTotals(additionalGroup)"
      @open-additional-modal="openAdditionalModal"
      @update-resource-quantity="handleUpdateResourceQuantity"
      @remove-resource="confirmRemoveAdditionalResource"
      @update:opened="updateAdditionalGroupOpened"
    />

    <!-- Общие итоги -->
    <VCard class="mt-6" color="primary" variant="tonal">
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <div class="d-flex justify-space-between align-center">
              <div class="text-h6">Общий выход блюда:</div>
              <div class="text-h6">{{ totalOutput.toFixed(2) }} г</div>
            </div>
          </VCol>
          <VCol cols="12" md="6">
            <div class="d-flex justify-space-between align-center">
              <div class="text-h6">Общая себестоимость:</div>
              <div class="text-h6">{{ totalCost.toFixed(2) }} с</div>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Модальные окна -->
    <AddResourcesModal
      v-model="showResourceModal"
      :allowed-types="resourceModalConfig.allowedTypes"
      :initial-selected="resourceModalConfig.initialSelected"
      @resources-selected="resourceModalConfig.onSelect"
    />

    <!-- Диалоги подтверждения -->
    <ConfirmDialog 
      ref="confirmDeleteGroup" 
      title="Удаление группы" 
      message="Вы действительно хотите удалить эту группу и все её ингредиенты?" 
      confirm-color="warning" 
      @confirm="handleGroupDeleteConfirmed" 
    />
    
    <ConfirmDialog 
      ref="confirmDeleteResource" 
      title="Удаление ингредиента" 
      message="Вы действительно хотите удалить этот ингредиент?" 
      confirm-color="warning" 
      @confirm="handleResourceDeleteConfirmed" 
    />

  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useGlobalDataStore } from '@/stores/globalDataStore'
import draggable from 'vuedraggable'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const globalDataStore = useGlobalDataStore()

// Refs
const showResourceModal = ref(false)
const resourceModalConfig = ref({})
const confirmDeleteGroup = ref(null)
const confirmDeleteResource = ref(null)

// Переменные для хранения данных удаления
const pendingGroupDelete = ref(null)
const pendingResourceDelete = ref(null)

const resourceGroups = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value)
})


// Состояние групп
const regularGroups = computed({
  get: () => props.modelValue.filter(g => !g.is_additional) || [],
  set: (value) => {
    const newGroups = [...props.modelValue]
    const additionalIndex = newGroups.findIndex(g => g.is_additional)
    if (additionalIndex !== -1) {
      newGroups.splice(0, additionalIndex, ...value)
    } else {
      newGroups.splice(0, newGroups.length, ...value)
    }
    emit('update:modelValue', newGroups)
  }
})

const additionalGroup = computed(() => {
  return props.modelValue.find(g => g.is_additional) || generateEmptyGroup(999, true)
})

// Проверка возможности сортировки
const canSort = computed(() => {
  return regularGroups.value.every(group => !group.opened)
})

// Вычисляемые итоги
const totalOutput = computed(() => {
  return regularGroups.value.reduce((total, group) => {
    return total + getGroupTotal(group, 'weight_output')
  }, 0)
})

const totalCost = computed(() => {
  const regularCost = regularGroups.value.reduce((total, group) => {
    return total + getGroupCost(group)
  }, 0)
  
  const additionalCost = getGroupCost(additionalGroup.value)
  
  return regularCost + additionalCost
})

const totalBrutto = computed(() => {
  return regularGroups.value.reduce((total, group) => {
    return total + getGroupTotal(group, 'weight_brutto')
  }, 0)
})

const totalNetto = computed(() => {
  return regularGroups.value.reduce((total, group) => {
    return total + getGroupTotal(group, 'weight_netto')
  }, 0)
})

// Методы
const generateId = () => Math.random().toString(36).substr(2, 9)

const generateEmptyGroup = (order = 1, isAdditional = false) => ({
  id: generateId(),
  name: isAdditional ? 'Дополнительные ресурсы' : '',
  sort_order: order,
  is_additional: isAdditional ? 1 : 0,
  opened: true,
  resources: []
})

const addResourceGroup = () => {
  const maxSortOrder = Math.max(
    ...regularGroups.value.map(g => g.sort_order || 0),
    0
  )
  
  const newGroup = generateEmptyGroup(maxSortOrder + 1)
  
  // Вставляем новую группу перед дополнительной
  const additionalIndex = resourceGroups.value.findIndex(g => g.is_additional === 1)
  if (additionalIndex !== -1) {
    const newGroups = [...resourceGroups.value]
    newGroups.splice(additionalIndex, 0, newGroup)
    emit('update:modelValue', newGroups)
  } else {
    emit('update:modelValue', [...resourceGroups.value, newGroup])
  }
}

const addAdditionalGroup = () => {
  const newGroup = generateEmptyGroup(999, true)
  emit('update:modelValue', [...resourceGroups.value, newGroup])
}

const updateGroupsOrder = () => {
  // После drag & drop обновляем порядок
  const newGroups = resourceGroups.value.map((group, index) => {
    if (!group.is_additional) {
      return { ...group, sort_order: index + 1 }
    }
    return group
  })
  
  emit('update:modelValue', newGroups)
}

const updateGroupOpened = (groupId, opened) => {
  const groupIndex = resourceGroups.value.findIndex(g => g.id === groupId)
  if (groupIndex !== -1) {
    const newGroups = [...resourceGroups.value]
    newGroups[groupIndex].opened = opened
    emit('update:modelValue', newGroups)
  }
}

const updateAdditionalGroupOpened = (opened) => {
  if (additionalGroup.value) {
    const groupIndex = resourceGroups.value.findIndex(g => g.id === additionalGroup.value.id)
    if (groupIndex !== -1) {
      const newGroups = [...resourceGroups.value]
      newGroups[groupIndex].opened = opened
      emit('update:modelValue', newGroups)
    }
  }
}

const openIngredientModal = (groupId) => {
  const group = resourceGroups.value.find(g => g.id === groupId)
  resourceModalConfig.value = {
    allowedTypes: ['ingredient', 'semi_finished'],
    initialSelected: group ? group.resources.map(r => r.resource_id) : [],
    onSelect: (selectedItems) => handleIngredientsSelected(groupId, selectedItems)
  }
  showResourceModal.value = true
}

const openAdditionalModal = (groupId) => {
  const group = resourceGroups.value.find(g => g.id === groupId)
  resourceModalConfig.value = {
    allowedTypes: ['household'],
    initialSelected: group ? group.resources.map(r => r.resource_id) : [],
    onSelect: (selectedItems) => handleAdditionalSelected(groupId, selectedItems)
  }
  showResourceModal.value = true
}

const handleIngredientsSelected = (groupId, selectedItems) => {
  const groupIndex = resourceGroups.value.findIndex(g => g.id === groupId)
  if (groupIndex === -1) return

  const currentResources = resourceGroups.value[groupIndex].resources || []
  
  const newGroups = [...resourceGroups.value]
  newGroups[groupIndex].resources = selectedItems.map(item => {
    const existingResource = currentResources.find(r => r.resource_id === item.id)
    
    if (existingResource) {
      return {
        ...existingResource,
        resource: item
      }
    } else {
      return {
        id: generateId(),
        resource_id: item.id,
        weight_brutto: 0,
        weight_netto: 0,
        weight_output: 0,
        losses: [],
        resource: item
      }
    }
  })
  
  emit('update:modelValue', newGroups)
}

const handleAdditionalSelected = (groupId, selectedItems) => {
  const groupIndex = resourceGroups.value.findIndex(g => g.id === groupId)
  if (groupIndex === -1) return

  const currentResources = resourceGroups.value[groupIndex].resources || []
  
  const newGroups = [...resourceGroups.value]
  newGroups[groupIndex].resources = selectedItems.map(item => {
    const existingResource = currentResources.find(r => r.resource_id === item.id)
    
    if (existingResource) {
      return {
        ...existingResource,
        resource: item
      }
    } else {
      return {
        id: generateId(),
        resource_id: item.id,
        quantity: 0,
        resource: item
      }
    }
  })
  
  emit('update:modelValue', newGroups)
}

const handleUpdateResourceWeight = (groupId, resourceIndex, field, value) => {
  const groupIndex = resourceGroups.value.findIndex(g => g.id === groupId)
  if (groupIndex === -1) return

  const resource = resourceGroups.value[groupIndex].resources[resourceIndex]
  if (!resource) return

  // Обновляем значение
  resource[field] = parseFloat(value) || 0

  // Пересчитываем веса
  if (field === 'weight_brutto' || field === 'weight_netto') {
    const weights = calculateResourceWeights(resource, field)
    if (weights) {
      resource.weight_brutto = weights.weight_brutto
      resource.weight_netto = weights.weight_netto
      resource.weight_output = weights.weight_output
    }
  }

  emit('update:modelValue', [...resourceGroups.value])
}

const handleUpdateResourceQuantity = (groupId, resourceIndex, value) => {
  const groupIndex = resourceGroups.value.findIndex(g => g.id === groupId)
  if (groupIndex === -1) return

  const newGroups = [...resourceGroups.value]
  newGroups[groupIndex].resources[resourceIndex].quantity = parseFloat(value) || 0
  
  emit('update:modelValue', newGroups)
}

const handleUpdateResourceLosses = (groupId, resourceIndex, losses) => {
  const groupIndex = resourceGroups.value.findIndex(g => g.id === groupId)
  if (groupIndex === -1) return

  const resource = resourceGroups.value[groupIndex].resources[resourceIndex]
  if (!resource) return

  const newGroups = [...resourceGroups.value]
  newGroups[groupIndex].resources[resourceIndex].losses = losses
  
  // Пересчитываем веса на основе текущего брутто
  const weights = calculateResourceWeights(newGroups[groupIndex].resources[resourceIndex], 'weight_brutto')
  if (weights) {
    newGroups[groupIndex].resources[resourceIndex].weight_netto = weights.weight_netto
    newGroups[groupIndex].resources[resourceIndex].weight_output = weights.weight_output
  }
  
  emit('update:modelValue', newGroups)
}

const calculateResourceWeights = (resource, changedField) => {
  // Логика расчета весов из техкарты
  let peelingLoss = 0
  let otherLosses = 0
  
  if (resource.losses) {
    resource.losses.forEach(loss => {
      const lossValue = parseFloat(loss.value) || 0
      if (loss.id === 'peeling') {
        peelingLoss = lossValue
      } else {
        otherLosses += lossValue
      }
    })
  }
  
  const peelingMultiplier = (100 - peelingLoss) / 100
  const otherLossesMultiplier = (100 - otherLosses) / 100
  
  if (changedField === 'weight_brutto') {
    const weight_brutto = parseFloat(resource.weight_brutto) || 0
    const weight_netto = parseFloat((weight_brutto * peelingMultiplier).toFixed(2))
    const weight_output = parseFloat((weight_netto * otherLossesMultiplier).toFixed(2))
    
    return { weight_brutto, weight_netto, weight_output }
  }
  
  if (changedField === 'weight_netto') {
    const weight_netto = parseFloat(resource.weight_netto) || 0
    const weight_brutto = parseFloat((weight_netto / peelingMultiplier).toFixed(2))
    const weight_output = parseFloat((weight_netto * otherLossesMultiplier).toFixed(2))
    
    return { weight_brutto, weight_netto, weight_output }
  }
  
  return null
}


// Подтверждение удаления группы
const confirmRemoveResourceGroup = (groupId) => {
  const group = resourceGroups.value.find(g => g.id === groupId)
  if (!group) return
  
  // Проверяем, есть ли ресурсы в группе
  const hasResources = group.resources && group.resources.length > 0
  
  if (hasResources) {
    // Если есть ресурсы - показываем подтверждение
    pendingGroupDelete.value = groupId
    confirmDeleteGroup.value.open()
  } else {
    // Если нет ресурсов - удаляем сразу
    removeResourceGroup(groupId)
  }
}

// Обработчик подтвержденного удаления группы
const handleGroupDeleteConfirmed = () => {
  if (pendingGroupDelete.value) {
    removeResourceGroup(pendingGroupDelete.value)
    pendingGroupDelete.value = null
  }
}

const removeResourceGroup = (groupId) => {
  const newGroups = resourceGroups.value.filter(g => g.id !== groupId)
  emit('update:modelValue', newGroups)
}

// Подтверждение удаления ресурса
const confirmRemoveResource = (groupId, resourceIndex) => {
  pendingResourceDelete.value = { groupId, resourceIndex }
  confirmDeleteResource.value.open()
}

const confirmRemoveAdditionalResource = (groupId, resourceIndex) => {
  pendingResourceDelete.value = { groupId, resourceIndex }
  confirmDeleteResource.value.open()
}

// Обработчик подтвержденного удаления ресурса
const handleResourceDeleteConfirmed = () => {
  if (pendingResourceDelete.value) {
    const { groupId, resourceIndex } = pendingResourceDelete.value
    removeResource(groupId, resourceIndex)
    pendingResourceDelete.value = null
  }
}

const removeResource = (groupId, resourceIndex) => {
  const groupIndex = resourceGroups.value.findIndex(g => g.id === groupId)
  if (groupIndex === -1) return

  const newGroups = [...resourceGroups.value]
  newGroups[groupIndex].resources.splice(resourceIndex, 1)
  emit('update:modelValue', newGroups)
}

const removeAdditionalResource = (groupId, resourceIndex) => {
  removeResource(groupId, resourceIndex)
}

const getGroupTotal = (group, field) => {
  if (!group.resources || group.resources.length === 0) return 0
  return group.resources.reduce((sum, resource) => sum + (parseFloat(resource[field]) || 0), 0)
}

const getGroupCost = (group) => {
  if (!group.resources || group.resources.length === 0) return 0
  
  return group.resources.reduce((sum, resource) => {
    let cost = 0
    if (resource.resource && resource.resource.use_price) {
      const price = resource.resource.use_price.raw || 0
      
      if (group.is_additional) {
        // Для дополнительных ресурсов используем quantity
        const quantity = parseFloat(resource.quantity) || 0
        cost = price * quantity
      } else {
        // Для обычных ресурсов используем weight_brutto
        const weight = parseFloat(resource.weight_brutto) || 0
        cost = resource.resource.unit === 'kilogram' 
          ? price * weight / 1000 
          : price * weight
      }
    }
    return sum + cost
  }, 0)
}

const getGroupTotals = (group) => {
  return {
    brutto: getGroupTotal(group, 'weight_brutto'),
    netto: getGroupTotal(group, 'weight_netto'),
    output: getGroupTotal(group, 'weight_output'),
    cost: getGroupCost(group)
  }
}

onMounted(() => {
  if (regularGroups.value.length === 0) {
    const initialGroup = generateEmptyGroup(1)
    emit('update:modelValue', [initialGroup, additionalGroup.value])
  }
})
</script>

<style scoped>
.groups-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.add-group-btn {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
}

.cursor-move {
  cursor: move;
}

.ghost {
  opacity: 0.5;
  background: rgba(var(--v-theme-primary), 0.1);
}

.chosen {
  opacity: 0.8;
}

.drag {
  transform: rotate(2deg);
}
</style>
