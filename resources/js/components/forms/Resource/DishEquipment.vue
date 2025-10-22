<template>
  <div class="dish-equipment">
    <div class="d-flex justify-space-between align-center mb-4">
      <h3>Оборудование</h3>
    </div>

    <div v-if="equipment.length === 0" class="text-center pa-8">
      <VIcon size="64" color="grey-lighten-1">mdi-tools</VIcon>
      <p class="text-h6 mt-4 text-grey-lighten-1">Оборудование не выбрано</p>
      <p class="text-body-2 text-grey-lighten-1">Добавьте необходимое оборудование для приготовления</p>
    </div>

    <!-- Список оборудования -->
    <VRow v-else>
      <VCol
        v-for="(item, index) in equipment"
        :key="item.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <VCard class="equipment-card" variant="outlined">
          <div class="equipment-image">
            <VImg
              v-if="item.photo"
              :src="item.photo.url"
              :alt="item.name"
              height="120"
              cover
            />
            <div v-else class="no-image d-flex align-center justify-center">
              <VIcon size="48" color="grey-lighten-2">mdi-tools</VIcon>
            </div>
          </div>
          
          <VCardTitle class="text-body-1">
            {{ item.name }}
          </VCardTitle>
          
          <VCardSubtitle v-if="item.category">
            {{ item.category.name }}
          </VCardSubtitle>
          
          <VCardText v-if="item.description" class="text-body-2">
            {{ item.description }}
          </VCardText>
          
          <VCardActions>
            <VSpacer />
            <VBtn
              icon="mdi-delete"
              variant="text"
              color="error"
              size="small"
              @click="removeEquipment(index)"
            />
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
    <!-- Кнопка добавления оборудования -->
    <div class="text-center my-4">
      <VBtn
        color="primary"
        variant="tonal"
        prepend-icon="mdi-plus"
        @click="openEquipmentModal"
        class="add-equipment-btn"
      >
        Добавить оборудования
      </VBtn>
    </div>

    <!-- Модальное окно выбора оборудования -->
    <AddResourcesModal
      v-model="showEquipmentModal"
      :allowed-types="['equipment']"
      :initial-selected="selectedEquipmentIds"
      @resources-selected="handleEquipmentSelected"
      title="Выбор оборудования"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const showEquipmentModal = ref(false)

const equipment = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value)
})

const selectedEquipmentIds = computed(() => {
  return equipment.value.map(item => item.id)
})

const openEquipmentModal = () => {
  showEquipmentModal.value = true
}

const handleEquipmentSelected = (selectedItems) => {
  // Получаем текущее оборудование
  const currentEquipment = equipment.value || []

  // Создаем новый массив оборудования, сохраняя существующие значения
  const newEquipment = selectedItems.map(item => {
    // Ищем оборудование среди существующих
    const existingEquipment = currentEquipment.find(e => e.id === item.id)
    
    if (existingEquipment) {
      // Если оборудование уже существует, сохраняем его значения
      return {
        ...existingEquipment,
        // Обновляем только базовые данные оборудования
        name: item.name,
        category: item.category,
        photo: item.photo,
        description: item.description
      }
    } else {
      // Если это новое оборудование, создаем новую запись
      return {
        id: item.id,
        name: item.name,
        category: item.category,
        photo: item.photo,
        description: item.description
      }
    }
  })
  
  emit('update:modelValue', newEquipment)
}

const removeEquipment = (index) => {
  const newEquipment = equipment.value.filter((_, i) => i !== index)
  emit('update:modelValue', newEquipment)
}
</script>

<style scoped>
.add-equipment-btn {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
}

.equipment-card {
  height: 100%;
  transition: transform 0.2s ease-in-out;
}

.equipment-card:hover {
  transform: translateY(-2px);
}

.equipment-image {
  position: relative;
  height: 120px;
  overflow: hidden;
}

.no-image {
  height: 100%;
  background-color: rgba(var(--v-theme-surface-variant), 0.5);
}

.equipment-image .v-img {
  border-radius: 4px 4px 0 0;
}
</style>
