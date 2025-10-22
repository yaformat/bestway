<template>
  <VAppBar 
    v-if="isMobile" 
    :color="currentToolbarColor"
    :height="toolbarHeight"
    class="mobile-toolbar"
    elevation="2"
  >
    <div class="toolbar-content">
      <!-- Левая кнопка -->
      <VBtn
        :icon="leftIcon"
        variant="text"
        color="white"
        size="default"
        class="toolbar-btn"
        @click="handleLeftClick"
      />

      <!-- Центральная часть с заголовком -->
      <div class="toolbar-title-section">
        <div class="toolbar-title">{{ currentTitle }}</div>
        <div v-if="currentSubtitle" class="toolbar-subtitle">{{ currentSubtitle }}</div>
      </div>

      <!-- Правая кнопка -->
      <VBtn
        v-if="rightButtonConfig.show"
        :icon="rightButtonConfig.icon"
        :color="rightButtonConfig.color"
        variant="text"
        size="default"
        class="toolbar-btn"
        :class="{ 'toolbar-btn-with-text': rightButtonConfig.text }"
        :disabled="rightButtonDisabled"
        @click="handleRightClick"
      >
        <VIcon :icon="rightButtonConfig.icon" />
        <span v-if="rightButtonConfig.text" class="btn-text">{{ rightButtonConfig.text }}</span>
      </VBtn>
    </div>

    <!-- Прогресс бар для загрузки -->
    <VProgressLinear
      v-if="loading"
      indeterminate
      color="white"
      height="2"
      class="toolbar-progress"
    />
  </VAppBar>

  <!-- Диалог подтверждения -->
  <ConfirmDialog
    ref="confirmDialog"
    :title="confirmConfig.title"
    :message="confirmConfig.message"
    :confirm-color="confirmConfig.color"
    @confirm="handleConfirm"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { useDisplay } from 'vuetify'
import { useMobileMode } from '@/composables/useMobileMode'

const props = defineProps({
  title: {
    type: String,
    default: 'Производство'
  },
  subtitle: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  },
  hasSelections: {
    type: Boolean,
    default: false
  },
  canDeleteSelections: {
    type: Boolean,
    default: true
  },
  // Новые пропсы для кастомизации цветов
  modeColors: {
    type: Object,
    default: () => ({
      view: 'primary',
      sort: 'primary',
      edit: 'error',
      complete: 'success'
    })
  }
})

const emit = defineEmits([
  'back', 
  'cancel-mode', 
  'show-mode-selector', 
  'apply-mode',
  'delete-selected', 
  'complete-selected'
])

const { mobile } = useDisplay()
const { mobileState, MOBILE_MODES, toggleModeSelector, exitMode } = useMobileMode()

const confirmDialog = ref(null)
const confirmConfig = ref({
  title: '',
  message: '',
  color: 'primary',
  action: null
})

const isMobile = computed(() => mobile.value)
const toolbarHeight = computed(() => 72)

// Вычисляемое свойство для цвета тулбара
const currentToolbarColor = computed(() => {
  switch (mobileState.currentMode) {
    case MOBILE_MODES.SORT:
      return props.modeColors.sort
    case MOBILE_MODES.DELETE:
      return props.modeColors.edit
    case MOBILE_MODES.COMPLETE:
      return props.modeColors.complete
    case MOBILE_MODES.EDIT_DAY:
      return 'info'
    case MOBILE_MODES.VIEW:
    default:
      return props.modeColors.view
  }
})

// Вычисляемые свойства для заголовков
const currentTitle = computed(() => {
  switch (mobileState.currentMode) {
    case MOBILE_MODES.SORT:
      return 'Сортировка'
    case MOBILE_MODES.DELETE:
      return 'Удаление'
    case MOBILE_MODES.COMPLETE:
      return 'Выполнение'
    case MOBILE_MODES.EDIT_DAY:
      return 'Редактирование дня'
    default:
      return props.title
  }
})

const currentSubtitle = computed(() => {
  if (mobileState.currentMode === MOBILE_MODES.VIEW) {
    return props.subtitle
  }
  
  if (mobileState.currentMode === MOBILE_MODES.EDIT_DAY) {
    return 'Измените параметры периодов и блюд'
  }
  
  // Показываем количество выбранных блюд и периодов
  const selectedDishes = mobileState.selectedDishes.size
  const selectedPeriods = mobileState.selectedPeriods.size
  
  if (selectedDishes > 0 || selectedPeriods > 0) {
    const parts = []
    if (selectedDishes > 0) parts.push(`блюд: ${selectedDishes}`)
    if (selectedPeriods > 0) parts.push(`периодов: ${selectedPeriods}`)
    return `Выбрано ${parts.join(', ')}`
  }
  
  return getModeDescription()
})

// Левая кнопка
const leftIcon = computed(() => {
  return mobileState.currentMode === MOBILE_MODES.VIEW ? 'mdi-arrow-left' : 'mdi-close'
})

// Конфигурация правой кнопки
const rightButtonConfig = computed(() => {
  if (mobileState.currentMode === MOBILE_MODES.VIEW) {
    return {
      show: true,
      icon: 'mdi-dots-vertical',
      color: 'white',
      text: null
    }
  }
  
  switch (mobileState.currentMode) {
    case MOBILE_MODES.SORT:
      return {
        show: true,
        icon: 'mdi-check',
        color: 'white',
        text: null
      }
      
    case MOBILE_MODES.DELETE:
      return {
        show: true,
        icon: 'mdi-trashcan-outline',
        color: 'white',
        text: null
      }
      
    case MOBILE_MODES.COMPLETE:
      return {
        show: true,
        icon: 'mdi-check-circle',
        color: 'white',
        text: null
      }
      
    case MOBILE_MODES.EDIT_DAY:
      return {
        show: true,
        icon: 'mdi-check',
        color: 'white',
        text: null
      }
      
    default:
      return {
        show: false,
        icon: '',
        color: 'white',
        text: null
      }
  }
})

const rightButtonDisabled = computed(() => {
  if (mobileState.currentMode === MOBILE_MODES.EDIT) {
    return !props.hasSelections || !props.canDeleteSelections
  }
  
  if (mobileState.currentMode === MOBILE_MODES.COMPLETE) {
    return !props.hasSelections
  }
  
  return false
})

// Методы обработки кликов
const handleLeftClick = () => {
  if (mobileState.currentMode === MOBILE_MODES.VIEW) {
    emit('back')
  } else {
    emit('cancel-mode')
    exitMode()
  }
}

const handleRightClick = () => {
  if (mobileState.currentMode === MOBILE_MODES.VIEW) {
    emit('show-mode-selector')
    toggleModeSelector()
  } else {
    handleApplyMode()
  }
}

const handleApplyMode = () => {
  switch (mobileState.currentMode) {
    case MOBILE_MODES.SORT:
      emit('apply-mode', { mode: 'sort' })
      exitMode()
      break
      
    case MOBILE_MODES.DELETE:
      if (props.hasSelections && props.canDeleteSelections) {
        showDeleteConfirmation()
      }
      break
      
    case MOBILE_MODES.COMPLETE:
      if (props.hasSelections) {
        showCompleteConfirmation()
      }
      break
      
    case MOBILE_MODES.EDIT_DAY:
      emit('apply-mode', { mode: 'edit_day' })
      exitMode()
      break
  }
}

const showDeleteConfirmation = () => {
  const selectedCount = mobileState.selectedDishes.size + mobileState.selectedPeriods.size
  
  confirmConfig.value = {
    title: 'Подтверждение удаления',
    message: `Вы уверены, что хотите удалить выбранные элементы (${selectedCount})?`,
    color: 'error',
    action: 'delete'
  }
  
  confirmDialog.value.open()
}

const showCompleteConfirmation = () => {
  const selectedCount = mobileState.selectedDishes.size
  
  confirmConfig.value = {
    title: 'Подтверждение выполнения',
    message: `Отметить выбранные блюда (${selectedCount}) как выполненные?`,
    color: 'success',
    action: 'complete'
  }
  
  confirmDialog.value.open()
}

const handleConfirm = () => {
  switch (confirmConfig.value.action) {
    case 'delete':
      emit('delete-selected', {
        dishes: Array.from(mobileState.selectedDishes),
        periods: Array.from(mobileState.selectedPeriods)
      })
      break
      
    case 'complete':
      emit('complete-selected', Array.from(mobileState.selectedDishes))
      break
  }
}

const getModeDescription = () => {
  switch (mobileState.currentMode) {
    case MOBILE_MODES.SORT:
      return 'Перетащите блюда для изменения порядка'
    case MOBILE_MODES.DELETE:
      return 'Выберите элементы для удаления'
    case MOBILE_MODES.COMPLETE:
      return 'Выберите блюда для выполнения'
    case MOBILE_MODES.EDIT_DAY:
      return 'Редактируйте параметры дня'
    default:
      return ''
  }
}
</script>

<style scoped>
.mobile-toolbar {
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toolbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  height: 100%;
  padding: 0 8px;
}

.toolbar-btn {
  flex-shrink: 0;
  min-width: 48px;
  height: 48px;
  transition: all 0.2s ease;
}

.toolbar-btn-with-text {
  min-width: auto;
  padding: 0 12px;
  gap: 8px;
}

.btn-text {
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
}

.toolbar-title-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  min-width: 0;
  padding: 0 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toolbar-title {
  font-size: 1.125rem;
  font-weight: 500;
  line-height: 1.2;
  color: white;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
}

.toolbar-subtitle {
  font-size: 0.875rem;
  font-weight: 400;
  line-height: 1.2;
  color: rgba(255, 255, 255, 0.8);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
  margin-top: 2px;
}

.toolbar-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}

.toolbar-btn:disabled {
  opacity: 0.5;
}

/* Адаптация для очень маленьких экранов */
@media (max-width: 360px) {
  .toolbar-title {
    font-size: 1rem;
  }
  
  .toolbar-subtitle {
    font-size: 0.8125rem;
  }
  
  .toolbar-title-section {
    padding: 0 8px;
  }
  
  .btn-text {
    font-size: 0.8125rem;
  }
}
</style>
