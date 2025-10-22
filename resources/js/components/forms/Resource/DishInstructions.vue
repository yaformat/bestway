<template>
  <div class="dish-instructions">
    <div class="d-flex justify-space-between align-center mb-4">
      <h3>Инструкции приготовления</h3>
    </div>

    <div v-if="steps.length === 0" class="text-center pa-8">
      <VIcon size="64" color="grey-lighten-1">mdi-pot-mix-outline</VIcon>
      <p class="text-h6 mt-4 text-grey-lighten-1">Нет инструкций</p>
      <p class="text-body-2 text-grey-lighten-1">Добавьте первый шаг приготовления</p>
    </div>

    <!-- Draggable список шагов -->
    <draggable
      v-model="steps"
      tag="div"
      class="draggable-list"
      item-key="id"
      handle=".step-handle"
      :animation="200"
      ghost-class="ghost"
      chosen-class="chosen"
      drag-class="drag"
      @end="updateStepsOrder"
    >
      <template #item="{ element, index }">
        <div class="draggable-item">
            <div :class="['step-card', { 'with-notice': !!element.description_notice }]">
            <div class="step-content">
                <!-- Фото шага -->
                <div class="step-photo">
                <div class="step-image">
                    <div class="step-image-fit">
                    <img v-if="element.photo" :src="element.photo.url" alt="Фото шага" />
                    </div>
                </div>
                </div>

                <!-- Номер шага -->
                <div class="step-title">Шаг {{ index + 1 }}</div>

                <!-- Описание -->
                <div class="step-description">
                <p>{{ element.description }}</p>
                </div>

                <!-- Таймеры -->
                <div class="step-timer">
                <div v-if="isReferencedByOtherStep(element.id)" class="timer-reference">
                    <VIcon icon="mdi-timer-outline" size="16" class="me-1" />
                    <VIcon icon="mdi-arrow-left-thin" size="16" class="me-1" />
                    <span>{{ getReferencingStepName(element.id) }}</span>
                </div>
                <div v-if="hasTimer(element)" class="timer-target">
                    <VIcon icon="mdi-timer-outline" size="16" class="me-1" />
                    <VIcon icon="mdi-arrow-right-thin" size="16" class="me-1" />
                    <span>{{ getTimerTargetStepName(element) }}</span>
                    <VIcon 
                    v-if="isTimerBroken(element)"
                    icon="mdi-alert" 
                    size="16" 
                    color="warning" 
                    class="ms-1"
                    />
                </div>
                </div>

                <!-- Кнопки действий -->
                <div class="step-actions">
                <VBtn
                    variant="tonal"
                    color="warning"
                    size="small"
                    icon
                    class="me-2"
                    @click="confirmRemoveStep(element.id)"
                    title="Удалить шаг"
                >
                    <VIcon size="16" icon="mdi-trashcan-outline" />
                </VBtn>

                <VBtn
                    variant="tonal"
                    color="primary"
                    size="small"
                    icon
                    class="me-2"
                    @click="openEditModal(element, index)"
                    title="Редактировать шаг"
                >
                    <VIcon size="16" icon="mdi-pencil" />
                </VBtn>

                <VBtn
                    v-show="steps.length > 1"
                    variant="tonal"
                    color="secondary"
                    size="small"
                    icon
                    class="step-handle"
                    title="Поменять порядок"
                >
                    <VIcon size="16" icon="mdi-sort" />
                </VBtn>
                </div>
            </div>
            </div>
        </div>
      </template>
    </draggable>

    <!-- Кнопка добавления шага -->
    <div class="text-center my-4">
      <VBtn
        color="primary"
        variant="tonal"
        prepend-icon="mdi-plus"
        @click="addStep"
        class="add-step-btn"
      >
        Добавить шаг
      </VBtn>
    </div>

    <!-- Модальное окно редактирования шага -->
    <VDialog 
      v-model="showEditModal"
      scroll-strategy="none"
      max-width="900px"
    >
      <VCard class="modal-card">
        <VToolbar color="primary">
          <VToolbarTitle>
            {{ isNewStep ? 'Новый шаг' : `Редактирование шага ${editingStepIndex + 1}` }}
          </VToolbarTitle>
          <VSpacer />
          <VBtn icon @click="cancelEdit">
            <VIcon>mdi-close</VIcon>
          </VBtn>
        </VToolbar>
        
        <VCardText class="pt-4 modal-content">
          <VRow>
            <!-- Колонка для фото -->
            <VCol cols="12" md="5">
              <div class="photo-column">
                <ImageUploaderEnhanced 
                  v-model:photoId="editForm.photoId" 
                  v-model:imageUrl="editForm.imagePreview" 
                  :photo="editingStep?.photo" 
                  v-model:photoMarkedForDeletion="editForm.photoMarkedForDeletion"
                />
              </div>
            </VCol>
            
            <!-- Колонка с описанием и таймером -->
            <VCol cols="12" md="7">
              <div class="content-column">
                <VTextarea
                  v-model="editForm.description"
                  label="Описание шага"
                  rows="3"
                  auto-grow
                  variant="outlined"
                  class="mb-4"
                />
                
                <VTextarea
                  v-model="editForm.descriptionNotice"
                  label="Важное примечание к шагу"
                  rows="2"
                  auto-grow
                  variant="outlined"
                  class="mb-4"
                />
                
                <!-- Управление таймером -->
                <div class="timer-controls mb-4">
                  <VCheckbox
                    v-model="editForm.hasTimerEnabled"
                    label="Добавить таймер"
                    class="mb-3"
                  />
                  
                  <!-- Поля таймера -->
                  <div v-if="editForm.hasTimerEnabled" class="timer-fields">
                    <VTextField
                      v-model="editForm.timerName"
                      label="Название таймера"
                      variant="outlined"
                      class="mb-3"
                    />
                    
                    <VRow class="mb-3">
                      <VCol cols="6">
                        <VTextField
                          v-model.number="editForm.timerHours"
                          label="Часы"
                          type="number"
                          min="0"
                          variant="outlined"
                        />
                      </VCol>
                      
                      <VCol cols="6">
                        <VTextField
                          v-model.number="editForm.timerMinutes"
                          label="Минуты"
                          type="number"
                          min="0"
                          max="59"
                          variant="outlined"
                        />
                      </VCol>
                    </VRow>
                    
                    <VSelect
                      v-model="editForm.timerNextStepId"
                      :items="availableSteps"
                      item-title="title"
                      item-value="value"
                      label="Следующий шаг (опционально)"
                      variant="outlined"
                      clearable
                    />
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
        
        <!-- Зафиксированные кнопки внизу -->
        <VCardActions class="fixed-actions">
          <VBtn 
            variant="outlined" 
            color="secondary"
            @click="cancelEdit"
          >
            Отмена
          </VBtn>
          <VSpacer />
          <VBtn 
            variant="flat" 
            color="primary" 
            @click="saveChanges"
          >
            Сохранить
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Диалог подтверждения удаления шага -->
    <ConfirmDialog 
      ref="confirmDeleteStep" 
      title="Удаление шага" 
      message="Вы действительно хотите удалить этот шаг приготовления?" 
      confirm-color="warning" 
      @confirm="handleStepDeleteConfirmed" 
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

// Локальное состояние
const steps = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value)
})

// Состояние модального окна
const showEditModal = ref(false)
const editingStep = ref(null)
const editingStepIndex = ref(-1)
const isNewStep = ref(false)

// Диалог подтверждения удаления
const confirmDeleteStep = ref(null)
const pendingStepDelete = ref(null)

// Форма редактирования
const editForm = ref({
  description: '',
  descriptionNotice: '',
  photoId: null,
  imagePreview: null,
  photoMarkedForDeletion: false,
  hasTimerEnabled: false,
  timerName: '',
  timerHours: 0,
  timerMinutes: 0,
  timerNextStepId: null
})

// Вычисляемые свойства
const availableSteps = computed(() => {
  return steps.value
    .map((step, index) => ({
      title: `Шаг ${index + 1}`,
      value: step.id,
      originalIndex: index
    }))
    .filter(step => step.value !== editingStep.value?.id)
})

// Методы
const generateId = () => Math.random().toString(36).substr(2, 9)

const addStep = () => {
  const newStep = {
    id: generateId(),
    description: '',
    description_notice: '',
    photo: null,
    timer: null,
    order: steps.value.length
  }
  
  // Добавляем шаг в массив
  steps.value = [...steps.value, newStep]
  
  // Открываем модальное окно для редактирования нового шага
  openEditModal(newStep, steps.value.length - 1, true)
}

// Подтверждение удаления шага
const confirmRemoveStep = (stepId) => {
  pendingStepDelete.value = stepId
  confirmDeleteStep.value.open()
}

// Обработчик подтвержденного удаления шага
const handleStepDeleteConfirmed = () => {
  if (pendingStepDelete.value) {
    removeStep(pendingStepDelete.value)
    pendingStepDelete.value = null
  }
}

const removeStep = (stepId) => {
  const newSteps = steps.value.filter(step => step.id !== stepId)
  steps.value = newSteps
  emit('update:modelValue', newSteps)
  updateStepsOrder()
}

const openEditModal = (step, index, isNew = false) => {
  editingStep.value = step
  editingStepIndex.value = index
  isNewStep.value = isNew
  
  editForm.value = {
    description: step.description || '',
    descriptionNotice: step.description_notice || '',
    photoId: step.photo?.id || null,
    imagePreview: step.photo?.url || null,
    photoMarkedForDeletion: false,
    hasTimerEnabled: !!step.timer,
    timerName: step.timer?.name || '',
    timerHours: step.timer?.time?.hours || 0,
    timerMinutes: step.timer?.time?.minutes || 0,
    timerNextStepId: step.timer?.next_step_id || null
  }
  
  showEditModal.value = true
}

const cancelEdit = () => {
  if (isNewStep.value) {
    // Если это новый шаг и пользователь отменил - удаляем его
    const newSteps = steps.value.filter(step => step.id !== editingStep.value.id)
    steps.value = newSteps
  }
  
  showEditModal.value = false
  resetEditState()
}

const resetEditState = () => {
  editingStep.value = null
  editingStepIndex.value = -1
  isNewStep.value = false
}

const saveChanges = () => {
  if (!editingStep.value) return
  
  const updatedStep = {
    ...editingStep.value,
    description: editForm.value.description,
    description_notice: editForm.value.descriptionNotice,
    timer: editForm.value.hasTimerEnabled ? {
      name: editForm.value.timerName,
      time: {
        hours: parseInt(editForm.value.timerHours) || 0,
        minutes: parseInt(editForm.value.timerMinutes) || 0
      },
      next_step_id: editForm.value.timerNextStepId
    } : null
  }

  // Обработка фото
  if (editForm.value.photoMarkedForDeletion) {
    updatedStep.photo = null
  } else if (editForm.value.photoId && editForm.value.photoId !== editingStep.value.photo?.id) {
    updatedStep.photo = {
      id: editForm.value.photoId,
      url: editForm.value.imagePreview
    }
  }

  // Обновляем шаг в массиве
  const newSteps = [...steps.value]
  newSteps[editingStepIndex.value] = updatedStep
  steps.value = newSteps
  
  showEditModal.value = false
  resetEditState()
}

const updateStepsOrder = () => {
  const newSteps = steps.value.map((step, index) => ({
    ...step,
    order: index
  }))
  steps.value = newSteps
}

// Вспомогательные методы
const hasTimer = (step) => !!step.timer

const isTimerBroken = (step) => {
  if (!step.timer?.next_step_id) return false
  return !steps.value.some(s => s.id === step.timer.next_step_id)
}

const getTimerTargetStepName = (step) => {
  if (!step.timer?.next_step_id) return null
  const targetStepIndex = steps.value.findIndex(s => s.id === step.timer.next_step_id)
  return targetStepIndex !== -1 ? `Шаг ${targetStepIndex + 1}` : 'Удаленный шаг'
}

const isReferencedByOtherStep = (stepId) => {
  return steps.value.some(step => 
    step.timer?.next_step_id === stepId && step.id !== stepId
  )
}

const getReferencingStepName = (stepId) => {
  const referencingStepIndex = steps.value.findIndex(step => 
    step.timer?.next_step_id === stepId && step.id !== stepId
  )
  return referencingStepIndex !== -1 ? `Шаг ${referencingStepIndex + 1}` : null
}
</script>

<style scoped>
.add-step-btn {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
}

.step-card {
  margin-bottom: 8px;
  transition: all 0.3s ease;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #fff;
}
.step-card.with-notice {
  border-color: rgb(var(--v-theme-success))!important
}

.step-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.step-content {
  display: flex;
  align-items: center;
  padding: 12px;
  gap: 16px;
}

.step-handle-section {
  display: flex;
  align-items: center;
  gap: 8px;
}

.step-title {
  font-weight: 600;
  font-size: 16px;
  min-width: 60px;
}

.step-photo {
  flex-shrink: 0;
  border: 1px solid #eee;
}

.step-image {
  display: block;
  width: 50px;
  min-width: 50px;
  height: 0;
  padding-top: 50px;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.step-image-fit {
  display: block;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  background-image: url(data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxOTkxLjI3IDE5MjEuMzMiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO2ZpbGwtb3BhY2l0eTowfS5jbHMtMntmaWxsOnNpbHZlcn08L3N0eWxlPjwvZGVmcz48dGl0bGU+cGxhY2Vob2xkZXIgaWNvbjwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMCAwaDE5OTEuMjd2MTkyMS4zM0gweiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTEyODguNTIgNzg3LjUxbC0zNC4yLTE5OS45LTcwMC4xNSAxMjIuOTMgMTAyLjYxIDU4NC43MSA2OS40OS0xMS43NnY1MC4yNGg3MTAuODNWNzg3LjUxem0tNTYyLjI1IDQzNy4xOGwtMjIuNDUgNC4zMS04My4zOC00NzEuNCA1ODYuODQtMTAzLjY5IDIzLjUyIDEzMy42Mkg3MjYuMjd6bTY1My4xMSA1MS4zMUg3ODRWODQ1LjIyaDU5NS40eiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTgyMi40NiAxMjMwLjA4aDUxOC40NHYtMTcwLjIybC0xMDktMTIzLjZhMjggMjggMCAwIDAtNDQuNzQgMy41OGwtOTMuOSAxNDguODJhMTguMjcgMTguMjcgMCAwIDEtMjcuNzMgMy43OGwtNjEuMDItNTUuNDRhMjUuMjggMjUuMjggMCAwIDAtMzcuMDcgMy4zN3oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtMiIgY3g9Ijg5Mi4xNiIgY3k9Ijk0OC4yIiByPSI0MC44MSIvPjwvc3ZnPg==);
  background-position: center center;
  background-repeat: no-repeat;
}

.step-image img {
  object-fit: cover;
  position: relative;
  height: 100%;
  width: 100%;
  background: #fff;
}

.step-description {
  flex-grow: 1;
  min-width: 0; /* Важно для text-overflow */
}

.step-description p {
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.step-timer {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
  font-size: 14px;
}

.timer-reference,
.timer-target {
  display: flex;
  align-items: center;
  padding: 4px 8px;
  background-color: rgba(var(--v-theme-info), 0.1);
  border-radius: 4px;
  color: var(--v-theme-info);
}

.step-actions {
  display: flex;
  gap: 8px;
  margin-left: auto;
  flex-shrink: 0;
}

.photo-column {
  height: 100%;
}

.content-column {
  height: 100%;
}

.timer-fields {
  padding: 16px;
  background-color: rgba(var(--v-theme-primary), 0.05);
  border-radius: 8px;
  border: 1px solid rgba(var(--v-theme-primary), 0.2);
}

.draggable-list {
  min-height: 20px;
}

.draggable-item {
  margin-bottom: 8px;
  transition: all 0.3s ease;
}


.step-handle {
  cursor: grab;
}

.step-handle:active {
  cursor: grabbing;
}

.drag-handle-icon {
  opacity: 0.6;
  transition: opacity 0.2s ease;
}

.dish-handle:hover .drag-handle-icon {
  opacity: 1;
}

/* Draggable ghost styles */
.ghost {
  opacity: 0.75;
  background: rgba(var(--v-theme-primary), 0.1);
  border: 2px dashed rgba(var(--v-theme-primary), 0.3);
}

.chosen {
  transform: scale(1.02);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.chosen.ghost .step-card {
    visibility:hidden;
} 

.modal-card {
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.modal-content {
  flex: 1;
  overflow-y: auto;
  padding-bottom: 80px; /* Отступ для фиксированных кнопок */
}

.fixed-actions {
  position: sticky;
  bottom: 0;
  background-color: white;
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  padding: 16px 24px;
  margin-top: auto;
  z-index: 1;
}

/* Для мобильных устройств */
@media (max-width: 600px) {
  .fixed-actions {
    padding: 12px 16px;
  }
  
  .modal-content {
    padding-bottom: 70px;
  }
}
</style>
