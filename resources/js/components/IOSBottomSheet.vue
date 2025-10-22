<template>
  <VBottomSheet 
    v-model="internalModelValue"
    class="ios-bottom-sheet"
    :scrim="true"
    :persistent="false"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div 
      class="ios-sheet-wrapper"
      ref="sheetWrapperRef"
      @touchstart="handleTouchStart"
      @touchmove="handleTouchMove"
      @touchend="handleTouchEnd"
    >
      <!-- iOS Handle (полоска для drag) -->
      <div class="ios-sheet-handle" ref="handleRef">
        <div class="ios-handle-bar" ref="handleBarRef"></div>
      </div>
      
      <!-- Заголовок (опционально) -->
      <div v-if="title || $slots.header" class="ios-sheet-header">
        <slot name="header">
          <div class="ios-sheet-title">
            <span class="ios-title-text">{{ title }}</span>
            <VBtn 
              v-if="showCloseButton"
              color="surface-variant"
              variant="text"
              :class="closeButtonClass"
              @click="handleCloseButtonClick"
            >
              <VIcon start size="18">{{ closeButtonIcon }}</VIcon>
              {{ closeButtonText }}
            </VBtn>
          </div>
        </slot>
      </div>
      
      <!-- Разделитель -->
      <VDivider v-if="title || $slots.header" class="ios-divider" />
      
      <!-- Контент -->
      <div class="ios-sheet-content" :style="{ maxHeight: maxHeight }">
        <slot />
      </div>
      
      <!-- Нижние действия (опционально) -->
      <template v-if="$slots.actions">
        <VDivider class="ios-divider" />
        <div class="ios-sheet-actions">
          <slot name="actions" />
        </div>
      </template>
    </div>
  </VBottomSheet>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  closeButtonMode: {
    type: String,
    default: 'close', // 'close', 'reset'
    validator: (value) => ['close', 'reset'].includes(value)
  },
  maxHeight: {
    type: String,
    default: '80vh'
  },
  dragToClose: {
    type: Boolean,
    default: true
  },
  dragThreshold: {
    type: Number,
    default: 80
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'reset'])

const internalModelValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Refs для элементов
const sheetWrapperRef = ref(null)
const handleRef = ref(null)
const handleBarRef = ref(null)

// Drag to close functionality
const isDragging = ref(false)
const startY = ref(0)
const currentY = ref(0)
const initialTransform = ref(0)
const canDrag = ref(false)

const handleTouchStart = (event) => {
  if (!props.dragToClose) return
  
  // Проверяем, что касание началось ТОЛЬКО на handle
  const handle = event.target.closest('.ios-sheet-handle')
  
  if (!handle) {
    canDrag.value = false
    return
  }
  
  // Проверяем, что не кликнули по кнопке закрытия
  if (event.target.closest('.ios-close-btn')) {
    canDrag.value = false
    return
  }
  
  canDrag.value = true
  isDragging.value = false // Пока не начали движение
  startY.value = event.touches[0].clientY
  currentY.value = startY.value
  
  // Получаем текущий transform
  const computedStyle = window.getComputedStyle(sheetWrapperRef.value)
  const matrix = computedStyle.transform
  if (matrix && matrix !== 'none') {
    const values = matrix.split('(')[1].split(')')[0].split(',')
    initialTransform.value = parseFloat(values[5]) || 0
  } else {
    initialTransform.value = 0
  }
}

const handleTouchMove = (event) => {
  if (!canDrag.value || !sheetWrapperRef.value) return
  
  currentY.value = event.touches[0].clientY
  const deltaY = currentY.value - startY.value
  
  // Начинаем drag только если движение достаточно значительное и вниз
  if (!isDragging.value) {
    if (Math.abs(deltaY) > 10 && deltaY > 0) {
      isDragging.value = true
      // Убираем transition для плавного drag
      sheetWrapperRef.value.style.transition = 'none'
      event.preventDefault()
    }
    return
  }
  
  // Разрешаем только движение вниз
  if (deltaY > 0) {
    const maxDrag = props.dragThreshold * 3
    const clampedDelta = Math.min(deltaY, maxDrag)
    
    // Применяем resistance эффект - чем дальше тянем, тем сложнее
    const resistance = Math.pow(clampedDelta / maxDrag, 0.7)
    const actualDelta = maxDrag * resistance
    
    // Обновляем позицию
    sheetWrapperRef.value.style.transform = `translateY(${initialTransform.value + actualDelta}px)`
    
    // Обновляем opacity
    const opacityFactor = Math.max(1 - (actualDelta / (props.dragThreshold * 2)), 0.3)
    sheetWrapperRef.value.style.opacity = opacityFactor
    
    // Эффект растяжения handle
    if (handleBarRef.value) {
      const stretchFactor = Math.min(actualDelta / props.dragThreshold, 1)
      const scaleX = 1 + stretchFactor * 0.3
      const scaleY = Math.max(1 - stretchFactor * 0.2, 0.8)
      handleBarRef.value.style.transform = `scaleX(${scaleX}) scaleY(${scaleY})`
      handleBarRef.value.style.opacity = Math.max(0.3, 1 - stretchFactor * 0.5)
    }
    
    event.preventDefault()
  }
}

const handleTouchEnd = (event) => {
  if (!canDrag.value) return
  
  // Если не было реального drag'а, просто сбрасываем флаги
  if (!isDragging.value) {
    canDrag.value = false
    return
  }
  
  if (!sheetWrapperRef.value) return
  
  const deltaY = currentY.value - startY.value
  
  // Восстанавливаем transition
  sheetWrapperRef.value.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.3s ease'
  
  if (handleBarRef.value) {
    handleBarRef.value.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.2s ease'
  }
  
  if (deltaY > props.dragThreshold) {
    // Закрываем sheet
    closeWithAnimation()
  } else {
    // Возвращаем на место
    resetToOriginalPosition()
  }
  
  isDragging.value = false
  canDrag.value = false
}

const closeWithAnimation = () => {
  if (!sheetWrapperRef.value) return
  
  // Анимация закрытия
  sheetWrapperRef.value.style.transform = `translateY(100%)`
  sheetWrapperRef.value.style.opacity = '0'
  
  // Сбрасываем handle
  if (handleBarRef.value) {
    handleBarRef.value.style.transform = 'scaleX(1) scaleY(1)'
    handleBarRef.value.style.opacity = '1'
  }
  
  // Закрываем через небольшую задержку
  setTimeout(() => {
    close()
  }, 250)
}

const resetToOriginalPosition = () => {
  if (!sheetWrapperRef.value) return
  
  // Возвращаем в исходное положение
  sheetWrapperRef.value.style.transform = `translateY(${initialTransform.value}px)`
  sheetWrapperRef.value.style.opacity = '1'
  
  // Сбрасываем handle
  if (handleBarRef.value) {
    handleBarRef.value.style.transform = 'scaleX(1) scaleY(1)'
    handleBarRef.value.style.opacity = '1'
  }
  
  // Убираем inline стили через некоторое время
  setTimeout(() => {
    if (sheetWrapperRef.value) {
      sheetWrapperRef.value.style.transition = ''
      sheetWrapperRef.value.style.transform = ''
      sheetWrapperRef.value.style.opacity = ''
    }
    if (handleBarRef.value) {
      handleBarRef.value.style.transition = ''
      handleBarRef.value.style.transform = ''
      handleBarRef.value.style.opacity = ''
    }
  }, 400)
}

// Вычисляемые свойства для кнопки закрытия
const closeButtonIcon = computed(() => {
  return props.closeButtonMode === 'reset' ? 'mdi-refresh' : 'mdi-close'
})

const closeButtonText = computed(() => {
  return props.closeButtonMode === 'reset' ? 'сброс' : 'закрыть'
})

const closeButtonClass = computed(() => {
  return props.closeButtonMode === 'reset' ? 'ios-reset-btn' : 'ios-close-btn'
})

const handleCloseButtonClick = () => {
  if (props.closeButtonMode === 'reset') {
    emit('reset')
  } else {
    close()
  }
}

const close = () => {
  emit('close')
  emit('update:modelValue', false)
}

// Сброс стилей при закрытии/открытии
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    // При открытии сбрасываем все inline стили и флаги
    nextTick(() => {
      isDragging.value = false
      canDrag.value = false
      
      if (sheetWrapperRef.value) {
        sheetWrapperRef.value.style.transition = ''
        sheetWrapperRef.value.style.transform = ''
        sheetWrapperRef.value.style.opacity = ''
      }
      if (handleBarRef.value) {
        handleBarRef.value.style.transition = ''
        handleBarRef.value.style.transform = ''
        handleBarRef.value.style.opacity = ''
      }
    })
  }
})
</script>


<style>
/* iOS Bottom Sheet стили */
.ios-bottom-sheet :deep(.v-bottom-sheet__content) {
  border-radius: 20px 20px 0 0 !important;
  overflow: hidden;
  box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.15) !important;
  background: transparent !important;
}

.ios-sheet-wrapper {
  background: rgba(var(--v-theme-surface), 1);
  border-radius: 20px 20px 0 0;
  overflow: hidden;
  position: relative;
  width: 100%;
  max-height: 90vh;
  min-height: 50vh;
  display: flex;
  flex-direction: column;
  will-change: transform, opacity;
}

/* iOS Handle (полоска сверху) */
.ios-sheet-handle {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 16px 0 12px 0; /* Увеличиваем область для касания */
  background: rgba(var(--v-theme-surface), 1);
  cursor: grab;
  user-select: none;
  flex-shrink: 0;
  touch-action: none;
  -webkit-tap-highlight-color: transparent;
  position: relative;
}

.ios-sheet-handle::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: transparent;
  /* Увеличиваем область касания */
}

.ios-sheet-handle:active {
  cursor: grabbing;
}

.ios-handle-bar {
  width: 36px;
  height: 5px;
  background: rgba(var(--v-theme-on-surface), 0.4); /* Делаем более заметным */
  border-radius: 3px;
  transition: all 0.2s ease;
  transform-origin: center;
  will-change: transform, opacity;
  position: relative;
  z-index: 1;
}

.ios-sheet-handle:hover .ios-handle-bar {
  background: rgba(var(--v-theme-on-surface), 0.5);
  transform: scaleY(1.2);
}

/* Заголовок */
.ios-sheet-header {
  background: rgba(var(--v-theme-surface), 1);
  flex-shrink: 0;
}

.ios-sheet-title {
  padding: 8px 20px 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ios-title-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: rgba(var(--v-theme-on-surface), 0.87);
}

/* Стили для обычной кнопки закрытия */
.ios-close-btn {
  /*
  background: rgba(var(--v-theme-on-surface), 0.08) !important;
  border-radius: 50% !important;
  */
  color: rgba(var(--v-theme-on-surface), 0.6) !important;
  height: 32px !important;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.ios-close-btn:hover {
  /*
  background: rgba(var(--v-theme-on-surface), 0.12) !important;
  */
  color: rgba(var(--v-theme-on-surface), 0.8) !important;
  transform: scale(1.05);
}

/* Стили для кнопки сброса */
.ios-reset-btn {
  /*
  background: rgba(var(--v-theme-error), 0.1) !important;
  border-radius: 50% !important;
  */
  color: rgb(var(--v-theme-error)) !important;
  height: 32px !important;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.ios-reset-btn:hover {
  /*
  background: rgba(var(--v-theme-error), 0.15) !important;
  */
  color: rgb(var(--v-theme-error)) !important;
  transform: scale(1.05);
}

.ios-close-btn :deep(.v-btn__content),
.ios-reset-btn :deep(.v-btn__content) {
  color: inherit !important;
}

.ios-close-btn :deep(.v-icon),
.ios-reset-btn :deep(.v-icon) {
  color: inherit !important;
}

/* Контент */
.ios-sheet-content {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 0;
  min-height: 0;
}

/* Действия */
.ios-sheet-actions {
  padding: 16px 20px 20px 20px;
  background: rgba(var(--v-theme-surface), 0.95);
  backdrop-filter: blur(20px);
  flex-shrink: 0;
}

/* Разделители */
.ios-divider {
  border-color: rgba(var(--v-border-color), 0.08) !important;
  margin: 0 !important;
  flex-shrink: 0;
}

/* Скроллбар в iOS стиле */
.ios-sheet-content::-webkit-scrollbar {
  width: 4px;
}

.ios-sheet-content::-webkit-scrollbar-track {
  background: transparent;
}

.ios-sheet-content::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 2px;
}

.ios-sheet-content::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-on-surface), 0.3);
}

.ios-apply-btn {
  height: 48px !important;
  border-radius: 12px !important;
  font-weight: 600;
  text-transform: none !important;
  letter-spacing: 0 !important;
  flex: 1;
  width:100%;
}

/* Темная тема */
.v-theme--dark .ios-handle-bar {
  background: rgba(255, 255, 255, 0.4); /* Делаем более заметным */
}

.v-theme--dark .ios-sheet-handle:hover .ios-handle-bar {
  background: rgba(255, 255, 255, 0.5);
}

.v-theme--dark .ios-close-btn {
  background: rgba(255, 255, 255, 0.1) !important;
  color: rgba(255, 255, 255, 0.7) !important;
}

.v-theme--dark .ios-close-btn:hover {
  background: rgba(255, 255, 255, 0.15) !important;
  color: rgba(255, 255, 255, 0.9) !important;
}

.v-theme--dark .ios-reset-btn {
  background: rgba(244, 67, 54, 0.15) !important;
  color: rgb(244, 67, 54) !important;
}

.v-theme--dark .ios-reset-btn:hover {
  background: rgba(244, 67, 54, 0.2) !important;
}

/* Анимации */
.ios-bottom-sheet :deep(.v-overlay__content) {
  align-items: flex-end !important;
}

/* Убираем выделение на мобильных */
* {
  -webkit-tap-highlight-color: transparent;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.ios-sheet-content * {
  -webkit-user-select: auto;
  -khtml-user-select: auto;
  -moz-user-select: auto;
  -ms-user-select: auto;
  user-select: auto;
}
</style>
