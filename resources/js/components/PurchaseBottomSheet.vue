<template>
  <IOSBottomSheet
    v-model="internalModelValue"
    :max-height="'90vh'"
    :show-close-button="false"
    @close="handleClose"
  >
    <template #header>
      <div class="purchase-sheet-header">
        <div class="resource-info">
          <div class="resource-name">{{ resource?.name }}</div>
          <div v-if="resource" class="resource-details">
            <span class="detail-item">
              Требуется: {{ resource.value_on_action.display }}
            </span>
            <span class="detail-item">
              Куплено: {{ resource.value_purchased.display }}
            </span>
            <span class="detail-item primary--text">
              Осталось: {{ remainingQuantity }} {{ resource.unit_label || 'шт.' }}
            </span>
          </div>
        </div>
      </div>
    </template>

    <div class="purchase-sheet-content">
      <!-- История покупок -->
      <div v-if="resource?.purchases?.length > 0" class="purchases-history">
        <div class="section-title">История покупок</div>
        <div class="purchases-list">
          <div
            v-for="purchase in resource.purchases"
            :key="purchase.id"
            class="purchase-item"
          >
            <div class="purchase-main">
              <div class="purchase-quantity">{{ purchase.value.display }}</div>
              <div class="purchase-price">{{ purchase.price.display }}</div>
            </div>
            <div class="purchase-supplier">{{ purchase.supplier.name }}</div>
            <VBtn
              icon
              size="small"
              variant="text"
              color="error"
              @click="deletePurchase(purchase.id)"
            >
              <VIcon>mdi-delete</VIcon>
            </VBtn>
          </div>
        </div>
        <VDivider class="my-4" />
      </div>

      <!-- Форма покупки -->
      <div class="purchase-form">
        <div class="section-title">Новая покупка</div>

        <!-- Количество -->
        <div class="form-section">
          <div class="form-label">
            Количество
            <span class="primary--text">(осталось: {{ remainingQuantity }})</span>
          </div>
          <VTextField
            v-model.number="purchaseData.quantity"
            type="number"
            :suffix="resource?.unit_label || 'шт.'"
            variant="outlined"
            density="comfortable"
            hide-details
            class="quantity-field"
            :class="{ 'alert-field': isQuantityAlert }"
          >
            <template #append-inner>
              <VBtn
                v-if="remainingQuantity > 0"
                size="small"
                variant="text"
                color="primary"
                @click="fillRemaining"
                class="fill-btn"
              >
                Заполнить остаток
              </VBtn>
            </template>
          </VTextField>
          
          <!-- Быстрые кнопки количества -->
          <div v-if="quickQuantityOptions.length > 0" class="quick-quantity-buttons">
            <VBtn
              v-for="qty in quickQuantityOptions"
              :key="qty"
              size="small"
              variant="outlined"
              class="quick-quantity-btn"
              @click="setQuantity(qty)"
            >
              {{ qty }}
            </VBtn>
          </div>
          
          <div v-if="quantityHint" class="hint-text" :class="quantityHintClass">
            <VIcon size="small" class="mr-1">mdi-information</VIcon>
            {{ quantityHint }}
          </div>
        </div>

        <!-- Цена с двунаправленной связью -->
        <div class="form-section">
          <div class="form-label">Стоимость</div>
          <div class="price-inputs">
            <VTextField
              v-model.number="displayTotalPrice"
              type="number"
              label="Общая стоимость"
              suffix="сом"
              variant="outlined"
              density="comfortable"
              hide-details
              class="price-field"
              @update:model-value="handleTotalPriceChange"
            />
            <VTextField
              v-model.number="displayUnitPrice"
              type="number"
              label="Цена за единицу"
              suffix="сом"
              variant="outlined"
              density="comfortable"
              hide-details
              class="price-field"
              @update:model-value="handleUnitPriceChange"
            />
          </div>
          <div v-if="priceHint" class="hint-text">
            {{ priceHint }}
          </div>
        </div>

        <!-- Поставщик -->
        <div class="form-section">
          <div class="form-label">Поставщик (необязательно)</div>
          <div v-if="selectedSupplier" class="selected-supplier">
            <div class="supplier-info">
              <div class="supplier-name">{{ selectedSupplier.name }}</div>
              <div v-if="selectedSupplier.address" class="supplier-address">
                {{ selectedSupplier.address }}
              </div>
            </div>
            <VBtn
              icon
              size="small"
              variant="text"
              @click="clearSupplier"
            >
              <VIcon>mdi-close</VIcon>
            </VBtn>
          </div>
          <VBtn
            v-else
            variant="outlined"
            block
            prepend-icon="mdi-storefront"
            @click="showSupplierDialog = true"
          >
            Выбрать поставщика
          </VBtn>
        </div>

        <!-- Дата производства -->
        <div v-if="resource?.requires_expiry_date" class="form-section">
          <div class="form-label">Дата производства *</div>
          <VTextField
            v-model="purchaseData.production_date"
            type="date"
            variant="outlined"
            density="comfortable"
            hide-details
          />
        </div>
      </div>
    </div>

    <!-- Нижние кнопки -->
    <template #actions>
      <div class="purchase-actions">
        <VBtn
          variant="outlined"
          block
          @click="handleClose"
          :disabled="isSubmitting"
        >
          Отмена
        </VBtn>
        <VBtn
          color="primary"
          block
          @click="submitPurchase"
          :loading="isSubmitting"
          :disabled="!canSubmit"
        >
          Купить
        </VBtn>
      </div>
    </template>

    <!-- Оверлей при отправке -->
    <div v-if="isSubmitting" class="submitting-overlay">
      <VProgressCircular indeterminate size="32" />
      <div class="mt-2">Покупка...</div>
    </div>
  </IOSBottomSheet>

  <!-- Диалог выбора поставщика (вместо вложенного bottom sheet) -->
  <VDialog v-model="showSupplierDialog" max-width="600px" scrollable>
    <VCard>
      <VCardTitle class="pa-4 pb-2">
        <div class="d-flex align-center">
          <div class="text-h6">Выбор поставщика</div>
          <VSpacer />
          <VBtn icon variant="text" @click="showSupplierDialog = false">
            <VIcon>mdi-close</VIcon>
          </VBtn>
        </div>
      </VCardTitle>
      
      <VDivider />
      
      <VCardText class="pa-4" style="max-height: 400px;">
        <!-- Поиск -->
        <VTextField
          v-model="supplierSearchQuery"
          label="Поиск поставщика"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="comfortable"
          hide-details
          class="mb-4"
          clearable
          autofocus
        />
        
        <!-- Кнопка создания нового поставщика -->
        <VBtn
          variant="outlined"
          block
          prepend-icon="mdi-plus"
          class="mb-4"
          @click="showCreateSupplierForm = true"
        >
          Создать нового поставщика
        </VBtn>
        
        <!-- Список поставщиков -->
        <div class="suppliers-list-dialog">
          <div v-if="filteredSuppliers.length === 0" class="no-results text-center py-8">
            <VIcon size="48" color="grey-lighten-2" class="mb-2">mdi-store-search</VIcon>
            <div class="text-h6 text-grey-darken-1 mb-1">Поставщики не найдены</div>
            <div class="text-body-2 text-grey-darken-1">
              Попробуйте изменить поисковый запрос или создайте нового поставщика
            </div>
          </div>
          
          <div
            v-for="supplier in filteredSuppliers"
            :key="supplier.id"
            class="supplier-item-dialog pa-3 mb-2 rounded-lg cursor-pointer"
            :class="{ 'selected-supplier': isSelected(supplier) }"
            @click="selectSupplier(supplier)"
          >
            <div class="d-flex align-start">
              <VAvatar
                :color="getAvatarColor(supplier.name)"
                class="mr-3 flex-shrink-0"
                size="40"
              >
                <span class="text-white font-weight-bold">
                  {{ getInitials(supplier.name) }}
                </span>
              </VAvatar>
              
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
                
                <div v-if="supplier.address" class="text-caption text-medium-emphasis mb-1">
                  <VIcon size="14" class="mr-1">mdi-map-marker</VIcon>
                  {{ supplier.address }}
                </div>
                
                <div v-if="supplier.phone" class="text-caption text-medium-emphasis mb-1">
                  <VIcon size="14" class="mr-1">mdi-phone</VIcon>
                  {{ supplier.phone }}
                </div>
                
                <div v-if="supplier.contact_person" class="text-caption text-medium-emphasis">
                  <VIcon size="14" class="mr-1">mdi-account</VIcon>
                  {{ supplier.contact_person }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </VCardText>
      
      <VDivider />
      
      <VCardActions class="pa-4">
        <VBtn
          variant="outlined"
          block
          @click="showSupplierDialog = false"
        >
          Отмена
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Диалог создания нового поставщика -->
  <VDialog v-model="showCreateSupplierForm" max-width="600px">
    <VCard>
      <VCardTitle class="pa-4 pb-2">
        <div class="text-h6">Новый поставщик</div>
      </VCardTitle>
      <VDivider />
      <VCardText class="pa-4">
        <VForm @submit.prevent="createSupplier">
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="newSupplier.name"
                label="Название компании*"
                :rules="[requiredValidator]"
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                prepend-inner-icon="mdi-store"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="newSupplier.address"
                label="Адрес"
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                prepend-inner-icon="mdi-map-marker"
              />
            </VCol>
            <VCol cols="12" sm="6">
              <VTextField
                v-model="newSupplier.phone"
                label="Телефон"
                type="tel"
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                prepend-inner-icon="mdi-phone"
              />
            </VCol>
            <VCol cols="12" sm="6">
              <VTextField
                v-model="newSupplier.contact_person"
                label="Контактное лицо"
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                prepend-inner-icon="mdi-account"
              />
            </VCol>
          </VRow>
          <div class="d-flex justify-end mt-4 gap-2">
            <VBtn
              variant="outlined"
              @click="showCreateSupplierForm = false"
              :disabled="isCreatingSupplier"
            >
              Отмена
            </VBtn>
            <VBtn
              color="primary"
              type="submit"
              :loading="isCreatingSupplier"
            >
              Создать
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import IOSBottomSheet from '@/components/IOSBottomSheet.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  resource: { type: Object, default: null },
  suppliers: { type: Array, default: () => [] },
  lastUsedSupplier: { type: Object, default: null }
})

const emit = defineEmits([
  'update:modelValue',
  'purchase',
  'delete-purchase',
  'supplier-selected',
  'supplier-created'
])

const internalModelValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Состояние
const isSubmitting = ref(false)
const isCreatingSupplier = ref(false)
const showSupplierDialog = ref(false)
const showCreateSupplierForm = ref(false)
const supplierSearchQuery = ref('')

// Данные формы
const purchaseData = ref({
  quantity: 0,
  totalPrice: 0,
  unitPrice: 0,
  supplier_id: null,
  production_date: null
})

const selectedSupplier = ref(null)
const newSupplier = ref({
  name: '',
  address: '',
  phone: '',
  contact_person: ''
})

// Отображаемые значения (для двунаправленной связи)
const displayTotalPrice = ref(0)
const displayUnitPrice = ref(0)

// Валидаторы
const requiredValidator = v => !!v || 'Поле обязательно для заполнения'

// Вычисляемые свойства
const remainingQuantity = computed(() => {
  if (!props.resource) return 0
  const required = props.resource.value_on_action.raw
  const purchased = props.resource.value_purchased.raw || 0
  return Math.max(0, required - purchased)
})

const isQuantityAlert = computed(() => {
  return purchaseData.value.quantity > remainingQuantity.value && remainingQuantity.value > 0
})

const canSubmit = computed(() => {
  return purchaseData.value.quantity > 0 &&
         purchaseData.value.totalPrice > 0 &&
         !isSubmitting.value &&
         (!props.resource?.requires_expiry_date || purchaseData.value.production_date)
})

const quantityHint = computed(() => {
  if (!props.resource) return ''
  const remaining = remainingQuantity.value
  const unit = props.resource.unit_label || 'шт.'
  
  if (remaining === 0) {
    return 'Ресурс полностью закуплен'
  } else if (purchaseData.value.quantity > remaining) {
    return `Превышает на ${purchaseData.value.quantity - remaining} ${unit}`
  } else {
    return `Останется ${remaining - purchaseData.value.quantity} ${unit}`
  }
})

const quantityHintClass = computed(() => {
  if (remainingQuantity.value === 0) return 'success-hint'
  if (isQuantityAlert.value) return 'warning-hint'
  return ''
})

const priceHint = computed(() => {
  if (!purchaseData.value.quantity || !purchaseData.value.unitPrice) {
    return ''
  }
  return `Цена за единицу: ${formatNumber(purchaseData.value.unitPrice)} сом`
})

const quickQuantityOptions = computed(() => {
  const remaining = remainingQuantity.value
  if (remaining <= 0) return []
  
  const options = []
  const step = Math.max(1, Math.floor(remaining / 10))
  
  for (let i = step; i <= remaining; i += step) {
    options.push(i)
  }
  
  // Добавляем точное количество
  if (options[options.length - 1] !== remaining) {
    options.push(remaining)
  }
  
  return options.slice(0, 5) // Максимум 5 вариантов
})

// Фильтрация поставщиков для диалога
const filteredSuppliers = computed(() => {
  if (!supplierSearchQuery.value) {
    return props.suppliers
  }
  
  const query = supplierSearchQuery.value.toLowerCase()
  return props.suppliers.filter(supplier => {
    return supplier.name.toLowerCase().includes(query) ||
           (supplier.address && supplier.address.toLowerCase().includes(query)) ||
           (supplier.phone && supplier.phone.includes(query)) ||
           (supplier.contact_person && supplier.contact_person.toLowerCase().includes(query))
  })
})

// Методы
const formatNumber = (num) => {
  return new Intl.NumberFormat('ru-RU', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

const fillRemaining = () => {
  purchaseData.value.quantity = remainingQuantity.value
  updateDisplayPrices()
}

const setQuantity = (quantity) => {
  purchaseData.value.quantity = quantity
  updateDisplayPrices()
}

const handleTotalPriceChange = (value) => {
  if (value !== null && value !== undefined && purchaseData.value.quantity > 0) {
    purchaseData.value.totalPrice = value
    purchaseData.value.unitPrice = value / purchaseData.value.quantity
    displayUnitPrice.value = formatNumber(purchaseData.value.unitPrice)
  }
}

const handleUnitPriceChange = (value) => {
  if (value !== null && value !== undefined && purchaseData.value.quantity > 0) {
    purchaseData.value.unitPrice = value
    purchaseData.value.totalPrice = value * purchaseData.value.quantity
    displayTotalPrice.value = formatNumber(purchaseData.value.totalPrice)
  }
}

const updateDisplayPrices = () => {
  if (purchaseData.value.quantity > 0 && purchaseData.value.unitPrice > 0) {
    purchaseData.value.totalPrice = purchaseData.value.quantity * purchaseData.value.unitPrice
    displayTotalPrice.value = formatNumber(purchaseData.value.totalPrice)
    displayUnitPrice.value = formatNumber(purchaseData.value.unitPrice)
  }
}

const selectSupplier = (supplier) => {
  selectedSupplier.value = supplier
  purchaseData.value.supplier_id = supplier.id
  emit('supplier-selected', supplier)
  showSupplierDialog.value = false
}

const clearSupplier = () => {
  selectedSupplier.value = null
  purchaseData.value.supplier_id = null
}

const createSupplier = async () => {
  isCreatingSupplier.value = true
  try {
    // Здесь должен быть вызов API для создания поставщика
    const newSupplierData = {
      id: Date.now(),
      ...newSupplier.value
    }
    
    // Автоматически выбираем нового поставщика
    selectSupplier(newSupplierData)
    
    // Очищаем форму
    newSupplier.value = {
      name: '',
      address: '',
      phone: '',
      contact_person: ''
    }
    
    showCreateSupplierForm.value = false
  } catch (error) {
    console.error('Ошибка при создании поставщика:', error)
  } finally {
    isCreatingSupplier.value = false
  }
}

const submitPurchase = () => {
  if (!canSubmit.value) return
  
  const data = {
    supplier_id: purchaseData.value.supplier_id,
    value: purchaseData.value.quantity,
    price: purchaseData.value.totalPrice,
    production_date: purchaseData.value.production_date
  }
  
  emit('purchase', data)
}

const deletePurchase = (purchaseId) => {
  emit('delete-purchase', purchaseId)
}

const handleClose = () => {
  // Сбрасываем форму
  purchaseData.value = {
    quantity: 0,
    totalPrice: 0,
    unitPrice: 0,
    supplier_id: null,
    production_date: null
  }
  displayTotalPrice.value = 0
  displayUnitPrice.value = 0
  selectedSupplier.value = null
  
  internalModelValue.value = false
}

// Вспомогательные методы для диалога поставщиков
const isSelected = (supplier) => {
  return selectedSupplier.value && selectedSupplier.value.id === supplier.id
}

const getInitials = (name) => {
  if (!name) return '?'
  const words = name.trim().split(/\s+/)
  if (words.length >= 2) {
    return (words[0][0] + words[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

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

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    // Инициализация при открытии
    if (props.lastUsedSupplier) {
      selectedSupplier.value = props.lastUsedSupplier
      purchaseData.value.supplier_id = props.lastUsedSupplier.id
    }
    
    // Начальная цена за единицу, если есть
    if (props.resource?.price?.value) {
      purchaseData.value.unitPrice = props.resource.price.value
      displayUnitPrice.value = formatNumber(props.resource.price.value)
    }
  }
})

watch(() => purchaseData.value.quantity, () => {
  updateDisplayPrices()
})
</script>

<style scoped>
.purchase-sheet-header {
  padding: 1.5rem 1rem 1rem;
  background: rgba(var(--v-theme-surface), 1);
}

.resource-info {
  text-align: center;
}

.resource-name {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.resource-details {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.detail-item {
  font-size: 0.875rem;
  opacity: 0.8;
}

.purchase-sheet-content {
  padding: 0 1rem;
  max-height: 60vh;
  overflow-y: auto;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid rgba(var(--v-theme-primary), 0.2);
}

.purchases-history {
  margin-bottom: 1.5rem;
}

.purchases-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.purchase-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: rgba(var(--v-theme-surface), 0.5);
  border-radius: 8px;
  transition: all 0.2s ease;
  /* Убрана анимация */
}

.purchase-item:hover {
  background: rgba(var(--v-theme-surface), 0.8);
}

.purchase-main {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.purchase-quantity {
  font-weight: 500;
}

.purchase-price {
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.purchase-supplier {
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
  min-width: 120px;
}

.purchase-form {
  padding-bottom: 1rem;
}

.form-section {
  margin-bottom: 1.5rem;
  animation: slideUp 0.3s ease forwards;
  opacity: 0;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quantity-field {
  transition: all 0.3s ease;
}

.alert-field {
  border-color: rgb(var(--v-theme-warning)) !important;
  background: rgba(var(--v-theme-warning), 0.05);
}

.hint-text {
  font-size: 0.75rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
}

.success-hint {
  color: rgb(var(--v-theme-success));
}

.warning-hint {
  color: rgb(var(--v-theme-warning));
}

.fill-btn {
  font-size: 0.75rem;
  text-transform: none;
  height: 24px;
}

.quick-quantity-buttons {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
  flex-wrap: wrap;
}

.quick-quantity-btn {
  min-width: auto;
  padding: 0 12px;
  height: 28px;
  font-size: 0.75rem;
}

.price-inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.price-field {
  transition: all 0.3s ease;
}

.selected-supplier {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  background: rgba(var(--v-theme-primary), 0.05);
  border: 2px solid rgba(var(--v-theme-primary), 0.2);
  border-radius: 8px;
}

.supplier-info {
  flex: 1;
}

.supplier-name {
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.supplier-address {
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.purchase-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  padding: 1rem;
}

.submitting-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 10;
  border-radius: 12px;
}

/* Стили для диалога поставщиков */
.suppliers-list-dialog {
  max-height: 300px;
  overflow-y: auto;
}

.suppliers-list-dialog::-webkit-scrollbar {
  width: 4px;
}

.suppliers-list-dialog::-webkit-scrollbar-track {
  background: transparent;
}

.suppliers-list-dialog::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 2px;
}

.supplier-item-dialog {
  background: rgba(var(--v-theme-surface), 0.8);
  border: 2px solid transparent;
  transition: all 0.2s ease;
  cursor: pointer;
  user-select: none;
}

.supplier-item-dialog:hover {
  background: rgba(var(--v-theme-surface), 1);
  border-color: rgba(var(--v-theme-primary), 0.2);
  transform: translateX(4px);
}

.supplier-item-dialog.selected-supplier {
  background: rgba(var(--v-theme-primary), 0.08);
  border-color: rgb(var(--v-theme-primary));
}

.no-results {
  padding: 3rem 1rem;
  text-align: center;
}

/* Анимации */
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Применяем задержку для каждого поля */
.form-section:nth-child(1) { animation-delay: 0.1s; }
.form-section:nth-child(2) { animation-delay: 0.2s; }
.form-section:nth-child(3) { animation-delay: 0.3s; }
.form-section:nth-child(4) { animation-delay: 0.4s; }

/* Скроллбар */
.purchase-sheet-content::-webkit-scrollbar {
  width: 4px;
}

.purchase-sheet-content::-webkit-scrollbar-track {
  background: transparent;
}

.purchase-sheet-content::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 2px;
}

/* Адаптивность */
@media (max-width: 600px) {
  .price-inputs {
    grid-template-columns: 1fr;
  }
  
  .purchase-actions {
    grid-template-columns: 1fr;
  }
  
  .resource-details {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .purchase-item {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .purchase-main {
    width: 100%;
  }
  
  .purchase-supplier {
    width: 100%;
    min-width: auto;
  }
}

/* Эффект ripple для кнопок */
.quick-quantity-btn {
  position: relative;
  overflow: hidden;
}

.quick-quantity-btn::before {
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

.quick-quantity-btn:active::before {
  width: 200%;
  height: 200%;
}

/* Hover эффекты */
.selected-supplier:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.price-field:hover .v-field__outline {
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.quantity-field:hover .v-field__outline {
  border-color: rgba(var(--v-theme-primary), 0.3);
}

/* Фокус состояния */
.price-field.v-field--focused .v-field__outline {
  border-color: rgb(var(--v-theme-primary));
}

.quantity-field.v-field--focused .v-field__outline {
  border-color: rgb(var(--v-theme-primary));
}

/* Темная тема */
.v-theme--dark .submitting-overlay {
  background: rgba(0, 0, 0, 0.9);
}

.v-theme--dark .purchase-item {
  background: rgba(var(--v-theme-surface), 0.3);
}

.v-theme--dark .purchase-item:hover {
  background: rgba(var(--v-theme-surface), 0.5);
}

.v-theme--dark .selected-supplier {
  background: rgba(var(--v-theme-primary), 0.1);
}

.v-theme--dark .supplier-item-dialog {
  background: rgba(var(--v-theme-surface), 0.6);
}

.v-theme--dark .supplier-item-dialog:hover {
  background: rgba(var(--v-theme-surface), 0.8);
}

.v-theme--dark .supplier-item-dialog.selected-supplier {
  background: rgba(var(--v-theme-primary), 0.15);
}

/* Улучшенная доступность */
.form-section:focus-within {
  transform: scale(1.01);
}

.quick-quantity-btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}
</style>