<template>
  <div class="product-catalog">
    <div class="mb-4">
      <h3>Товарный справочник</h3>
      <p class="text-body-2 text-medium-emphasis">
      </p>
    </div>

    <!-- Привязанные товары -->
    <div v-if="selectedItems.length > 0" class="mb-6">
      <h4 class="mb-3">Привязанные товары ({{ selectedItems.length }})</h4>
      <draggable v-model="selectedItems" item-key="id" class="d-flex flex-wrap">
        <template #item="{ element }">
          <div class="product-item">
            <VCard class="mb-4" outlined>
              <VCardText>
                <ProductItemNameWithImage :item="element" />
                <VTextField
                  class="mt-3"
                  :model-value="element.shelf_life_days"
                  @update:model-value="updateItemShelfLife(element.id, $event)"
                  placeholder="Срок годности"
                  label="Срок годности"
                  type="number"
                  min="0"
                />
              </VCardText>
              <VCardActions>
                <VBtn @click="detachItem(element)" color="error" size="small">
                  Отвязать
                </VBtn>
              </VCardActions>
            </VCard>
          </div>
        </template>
      </draggable>
    </div>

    <!-- Кнопка показать/скрыть каталог -->
    <div class="mb-4">
      <VBtn 
        @click="toggleCatalog"
        :prepend-icon="showCatalog ? 'mdi-eye-off' : 'mdi-plus'"
        :color="showCatalog ? 'secondary' : 'primary'"
      >
        {{ showCatalog ? 'Скрыть каталог' : 'Привязать товары' }}
      </VBtn>
    </div>

    <!-- Каталог товаров -->
    <VCard v-if="showCatalog">
      <VCardTitle class="d-flex justify-space-between align-center">
        <span>Выбор товаров</span>
        <VBtn icon @click="toggleCatalog">
          <VIcon>mdi-close</VIcon>
        </VBtn>
      </VCardTitle>
      <VCardText>
        <VTextField
          v-model="searchQuery"
          placeholder="Поиск товаров"
          label="Поиск товаров"
          prepend-inner-icon="mdi-magnify"
          class="mb-4"
          clearable
        />
        
        <VTable>
          <thead>
            <tr>
              <th>Название</th>
              <th>Привязан к ресурсу</th>
              <th>Действие</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filteredItems" :key="item.id">
              <td>
                <ProductItemNameWithImage :item="item" />
              </td>
              <td>
                <VChip 
                  v-if="item.resource" 
                  size="small" 
                  color="info"
                >
                  {{ item.resource.name }}
                </VChip>
                <span v-else class="text-medium-emphasis">Не привязан</span>
              </td>
              <td>
                <VBtn 
                  @click="isItemAttached(item) ? detachItem(item) : attachItem(item)" 
                  :disabled="item.resource && item.resource.id != resourceId"
                  :color="isItemAttached(item) ? 'error' : 'primary'"
                  size="small"
                >
                  {{ isItemAttached(item) ? 'Отвязать' : 'Привязать' }}
                </VBtn>
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  availableItems: {
    type: Array,
    default: () => []
  },
  resourceId: {
    type: Number,
    default: null
  },
  defaultShelfLife: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const showCatalog = ref(false)
const searchQuery = ref('')

const selectedItems = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value)
})

const filteredItems = computed(() => {
  return props.availableItems.filter(item => 
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const toggleCatalog = () => {
  showCatalog.value = !showCatalog.value
}

const attachItem = (item) => {
  if (!isItemAttached(item)) {
    const newItems = [...selectedItems.value, { 
      ...item, 
      shelf_life_days: item.shelf_life_days ?? props.defaultShelfLife 
    }]
    emit('update:modelValue', newItems)
  }
}

const detachItem = (item) => {
  const newItems = selectedItems.value.filter(selectedItem => selectedItem.id !== item.id)
  emit('update:modelValue', newItems)
}

const isItemAttached = (item) => {
  return selectedItems.value.some(selectedItem => selectedItem.id === item.id)
}

const updateItemShelfLife = (itemId, value) => {
  const newItems = selectedItems.value.map(item => 
    item.id === itemId 
      ? { ...item, shelf_life_days: Number(value) }
      : item
  )
  emit('update:modelValue', newItems)
}
</script>

<style scoped>
.product-item {
  flex: 1 1 calc(20% - 16px);
  margin: 8px;
  max-width: calc(20% - 16px);
}
</style>
