<template>
  <IOSBottomSheet
    v-model="internalModelValue"
    title="Выбор поставщика"
    :max-height="'85vh'"
    @close="handleClose"
  >
    <div class="pa-4">
      <!-- Поиск -->
      <VTextField
        v-model="searchQuery"
        label="Поиск поставщика"
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        density="comfortable"
        hide-details
        autofocus
        class="mb-4 search-field"
        clearable
      />
      
      <!-- Кнопка создания нового поставщика -->
      <VBtn
        variant="outlined"
        block
        prepend-icon="mdi-plus"
        class="mb-4 create-supplier-btn"
        @click="createNewSupplier"
      >
        Создать нового поставщика
      </VBtn>
      
      <!-- Список поставщиков -->
      <div class="suppliers-list">
        <div v-if="filteredSuppliers.length === 0" class="no-results text-center py-8">
          <VIcon size="48" color="grey-lighten-2" class="mb-2">mdi-store-search</VIcon>
          <div class="text-h6 text-grey-darken-1 mb-1">Поставщики не найдены</div>
          <div class="text-body-2 text-grey-darken-1">
            Попробуйте изменить поисковый запрос или создайте нового поставщика
          </div>
        </div>
        
        <div v-for="group in groupedSuppliers" :key="group.letter" class="supplier-group">
          <div class="group-header">{{ group.letter }}</div>
          <div
            v-for="supplier in group.suppliers"
            :key="supplier.id"
            class="supplier-item pa-3 mb-2 rounded-lg cursor-pointer"
            :class="{ 'selected-supplier': isSelected(supplier) }"
            @click="selectSupplier(supplier)"
          >
            <div class="d-flex align-start">
              <!-- Аватар поставщика -->
              <VAvatar
                :color="getAvatarColor(supplier.name)"
                class="mr-3 flex-shrink-0"
                size="40"
              >
                <span class="text-white font-weight-bold">
                  {{ getInitials(supplier.name) }}
                </span>
              </VAvatar>
              
              <!-- Информация о поставщике -->
              <div class="flex-grow-1">
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="font-weight-medium text-body-1">
                    {{ supplier.name }}
                  </div>
                  <VIcon 
                    v-if="isSelected(supplier)"
                    color="primary"
                    size="20"
                  >
                    mdi-check-circle
                  </VIcon>
                </div>
                
                <!-- Адрес -->
                <div v-if="supplier.address" class="text-caption text-medium-emphasis mb-1">
                  <VIcon size="14" class="mr-1">mdi-map-marker</VIcon>
                  {{ supplier.address }}
                </div>
                
                <!-- Телефон -->
                <div v-if="supplier.phone" class="text-caption text-medium-emphasis mb-1">
                  <VIcon size="14" class="mr-1">mdi-phone</VIcon>
                  {{ supplier.phone }}
                </div>
                
                <!-- Контактное лицо -->
                <div v-if="supplier.contact_person" class="text-caption text-medium-emphasis">
                  <VIcon size="14" class="mr-1">mdi-account</VIcon>
                  {{ supplier.contact_person }}
                </div>
                
                <!-- Примечания -->
                <div v-if="supplier.notes" class="text-caption text-medium-emphasis mt-2">
                  <VIcon size="14" class="mr-1">mdi-text</VIcon>
                  {{ supplier.notes }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Кнопки действий -->
    <template #actions>
      <div class="pa-4">
        <VBtn
          variant="outlined"
          block
          @click="handleClose"
        >
          Отмена
        </VBtn>
      </div>
    </template>
  </IOSBottomSheet>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import IOSBottomSheet from '@/components/IOSBottomSheet.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  suppliers: {
    type: Array,
    default: () => []
  },
  selectedSupplier: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'select', 'create-new'])

const internalModelValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const searchQuery = ref('')
const debouncedSearch = ref('')
const searchTimeout = ref(null)

// Поиск с задержкой (debounce)
watch(searchQuery, (newValue) => {
  clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    debouncedSearch.value = newValue.toLowerCase()
  }, 300)
})

// Фильтрация поставщиков
const filteredSuppliers = computed(() => {
  if (!debouncedSearch.value) {
    return props.suppliers
  }
  
  return props.suppliers.filter(supplier => {
    return supplier.name.toLowerCase().includes(debouncedSearch.value) ||
           (supplier.address && supplier.address.toLowerCase().includes(debouncedSearch.value)) ||
           (supplier.phone && supplier.phone.includes(debouncedSearch.value)) ||
           (supplier.contact_person && supplier.contact_person.toLowerCase().includes(debouncedSearch.value))
  })
})

// Группировка по первой букве
const groupedSuppliers = computed(() => {
  const groups = {}
  
  filteredSuppliers.value.forEach(supplier => {
    const firstLetter = supplier.name.charAt(0).toUpperCase()
    if (!groups[firstLetter]) {
      groups[firstLetter] = []
    }
    groups[firstLetter].push(supplier)
  })
  
  return Object.keys(groups).sort().map(letter => ({
    letter,
    suppliers: groups[letter]
  }))
})

// Проверка, выбран ли поставщик
const isSelected = (supplier) => {
  return props.selectedSupplier && props.selectedSupplier.id === supplier.id
}

// Получение инициалов для аватара
const getInitials = (name) => {
  if (!name) return '?'
  const words = name.trim().split(/\s+/)
  if (words.length >= 2) {
    return (words[0][0] + words[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

// Получение цвета аватара на основе имени
const getAvatarColor = (name) => {
  if (!name) return 'grey'
  
  const colors = [
    'blue', 'green', 'purple', 'red', 'orange', 
    'teal', 'indigo', 'pink', 'cyan', 'lime'
  ]
  
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  
  const index = Math.abs(hash) % colors.length
  return colors[index]
}

// Выбор поставщика
const selectSupplier = (supplier) => {
  emit('select', supplier)
  handleClose()
}

// Создание нового поставщика
const createNewSupplier = () => {
  emit('create-new')
  handleClose()
}

// Закрытие
const handleClose = () => {
  searchQuery.value = ''
  internalModelValue.value = false
}

// Сброс поиска при открытии
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    searchQuery.value = ''
  }
})
</script>

<style scoped>
.search-field {
  transition: all 0.3s ease;
}

.search-field:focus-within {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.create-supplier-btn {
  border-style: dashed;
  transition: all 0.3s ease;
}

.create-supplier-btn:hover {
  border-style: solid;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.suppliers-list {
  max-height: 60vh;
  overflow-y: auto;
  padding-right: 8px;
}

.suppliers-list::-webkit-scrollbar {
  width: 4px;
}

.suppliers-list::-webkit-scrollbar-track {
  background: transparent;
}

.suppliers-list::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 2px;
}

.supplier-group {
  margin-bottom: 1.5rem;
}

.group-header {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(var(--v-theme-primary), 0.7);
  margin-bottom: 0.5rem;
  padding: 0 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.supplier-item {
  background: rgba(var(--v-theme-surface), 0.8);
  border: 2px solid transparent;
  transition: all 0.2s ease;
  cursor: pointer;
  user-select: none;
  position: relative;
  overflow: hidden;
}

.supplier-item:hover {
  background: rgba(var(--v-theme-surface), 1);
  border-color: rgba(var(--v-theme-primary), 0.2);
  transform: translateX(4px);
}

.supplier-item:active {
  transform: translateX(2px);
}

.supplier-item::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(var(--v-theme-primary), 0.1);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: width 0.3s ease, height 0.3s ease;
}

.supplier-item:active::before {
  width: 200%;
  height: 200%;
}

.selected-supplier {
  background: rgba(var(--v-theme-primary), 0.08);
  border-color: rgb(var(--v-theme-primary));
}

.no-results {
  padding: 3rem 1rem;
}

/* Анимация появления элементов */
.supplier-item {
  animation: slideIn 0.3s ease forwards;
  opacity: 0;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Применяем задержку для каждого элемента */
.supplier-item:nth-child(1) { animation-delay: 0.05s; }
.supplier-item:nth-child(2) { animation-delay: 0.1s; }
.supplier-item:nth-child(3) { animation-delay: 0.15s; }
.supplier-item:nth-child(4) { animation-delay: 0.2s; }
.supplier-item:nth-child(5) { animation-delay: 0.25s; }
.supplier-item:nth-child(n+6) { animation-delay: 0.3s; }

/* Групповая анимация */
.supplier-group {
  animation: fadeIn 0.4s ease forwards;
  opacity: 0;
}

.supplier-group:nth-child(1) { animation-delay: 0.1s; }
.supplier-group:nth-child(2) { animation-delay: 0.2s; }
.supplier-group:nth-child(3) { animation-delay: 0.3s; }
.supplier-group:nth-child(n+4) { animation-delay: 0.4s; }

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hover эффект для аватара */
.supplier-item:hover .v-avatar {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.supplier-item .v-avatar {
  transition: all 0.2s ease;
}

/* Темная тема */
.v-theme--dark .supplier-item {
  background: rgba(var(--v-theme-surface), 0.6);
}

.v-theme--dark .supplier-item:hover {
  background: rgba(var(--v-theme-surface), 0.8);
}

.v-theme--dark .selected-supplier {
  background: rgba(var(--v-theme-primary), 0.15);
}

/* Улучшенная доступность */
.supplier-item:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

/* Оптимизация для мобильных устройств */
@media (max-width: 600px) {
  .supplier-item {
    padding: 0.75rem;
  }
  
  .supplier-item:hover {
    transform: none;
  }
  
  .supplier-item:active {
    transform: scale(0.98);
  }
  
  .group-header {
    padding-left: 0.25rem;
    font-size: 0.8rem;
  }
}

/* Плавное появление/скрытие */
.v-enter-active,
.v-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Улучшенные переходы для полей */
.search-field .v-field__outline {
  transition: all 0.2s ease;
}

/* Стили для пустого состояния */
.no-results .v-icon {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.5;
  }
  50% {
    opacity: 1;
  }
}

/* Эффект загрузки при поиске */
.searching {
  position: relative;
}

.searching::after {
  content: '';
  position: absolute;
  top: 50%;
  right: 12px;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(var(--v-theme-primary), 0.3);
  border-top-color: rgb(var(--v-theme-primary));
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Улучшенные стили для кнопки создания */
.create-supplier-btn .v-btn__content {
  gap: 8px;
}

.create-supplier-btn:hover .v-icon {
  transform: rotate(90deg);
}

.create-supplier-btn .v-icon {
  transition: transform 0.3s ease;
}
</style>