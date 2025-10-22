<script setup>
import { ref, computed, watch } from 'vue';

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
    default: () => ({
      description: '',
      description_notice: '',
      photo: null,
    }),
  },
  allowSorting: {
    type: Boolean,
    default: false
  },
  allSteps: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits([
  'removeStep',
  'updateStep'
]);

const removeStep = () => {
  emit('removeStep', props.id);
};

// Состояние модального окна
const showEditModal = ref(false);

// Данные для редактирования
const description = ref(props.data.description || '');
const descriptionNotice = ref(props.data.description_notice || '');
const photoId = ref(props.data.photo?.id || null);
const imagePreview = ref(props.data.photo?.url || null);
const photoMarkedForDeletion = ref(false);

// Данные для таймера
const hasTimerEnabled = ref(!!props.data.timer);
const timerName = ref(props.data.timer?.name || '');
const timerHours = ref(props.data.timer?.time?.hours || 0);
const timerMinutes = ref(props.data.timer?.time?.minutes || 0);
const timerNextStepId = ref(props.data.timer?.next_step_id || null);

// Вычисляем доступные шаги для выбора
const availableSteps = computed(() => {
  const existingSteps = props.allSteps
    .map((step, originalIndex) => ({
      title: `Шаг ${originalIndex + 1}`,
      value: step.id,
      originalIndex
    }))
    .filter(step => step.value !== props.id);

  return existingSteps;
});

// Проверяем валидность выбранного шага
const isSelectedStepValid = computed(() => {
  if (!timerNextStepId.value) return true;
  return availableSteps.value.some(step => step.value === timerNextStepId.value);
});

// Проверяем, существует ли шаг, на который ссылается таймер
const isTimerBroken = computed(() => {
  if (!props.data.timer?.next_step_id) return false;
  return !props.allSteps.some(step => step.id === props.data.timer.next_step_id);
});

// Получаем название шага, на который ведет таймер
const timerTargetStepName = computed(() => {
  if (!props.data.timer?.next_step_id) return null;
  const targetStepIndex = props.allSteps.findIndex(step => step.id === props.data.timer.next_step_id);
  return targetStepIndex !== -1 ? `Шаг ${targetStepIndex + 1}` : 'Удаленный шаг';
});

// Проверяем, ссылается ли на этот шаг другой шаг
const isReferencedByOtherStep = computed(() => {
  return props.allSteps.some(step => 
    step.timer?.next_step_id === props.id && step.id !== props.id
  );
});

// Получаем название шага, который ссылается на текущий
const referencingStepName = computed(() => {
  const referencingStepIndex = props.allSteps.findIndex(step => 
    step.timer?.next_step_id === props.id && step.id !== props.id
  );
  return referencingStepIndex !== -1 ? `Шаг ${referencingStepIndex + 1}` : null;
});

// Открытие модального окна редактирования
const openEditModal = () => {
  description.value = props.data.description || '';
  descriptionNotice.value = props.data.description_notice || '';
  hasTimerEnabled.value = !!props.data.timer;
  timerName.value = props.data.timer?.name || '';
  timerHours.value = props.data.timer?.time?.hours || 0;
  timerMinutes.value = props.data.timer?.time?.minutes || 0;
  
  // Проверяем и очищаем нарушенные ссылки
  const targetStepExists = props.data.timer?.next_step_id && 
    props.allSteps.some(step => step.id === props.data.timer.next_step_id);
  
  timerNextStepId.value = targetStepExists ? props.data.timer.next_step_id : null;
  
  // Сбрасываем состояние фото
  photoId.value = props.data.photo?.id || null;
  imagePreview.value = props.data.photo?.url || null;
  photoMarkedForDeletion.value = false;
  
  showEditModal.value = true;
};

// Удаление таймера - теперь обновляет сразу
const removeTimer = () => {
  hasTimerEnabled.value = false;
  timerName.value = '';
  timerHours.value = 0;
  timerMinutes.value = 0;
  timerNextStepId.value = null;
  
  // Обновляем данные сразу
  emit('updateStep', props.id, 'all', {
    timer: null
  });
};

// Оптимизированное сохранение изменений - один вызов updateStep
const saveChanges = () => {
  // Собираем все изменения в один объект
  const updatedData = {
    description: description.value,
    description_notice: descriptionNotice.value,
    timer: hasTimerEnabled.value ? {
      name: timerName.value,
      time: {
        hours: parseInt(timerHours.value) || 0,
        minutes: parseInt(timerMinutes.value) || 0
      },
      next_step_id: timerNextStepId.value
    } : null
  };

  // Обрабатываем фото
  if (photoMarkedForDeletion.value) {
    updatedData.photo = null;
    updatedData.photoMarkedForDeletion = true;
  } else if (photoId.value && photoId.value !== props.data.photo?.id) {
    updatedData.photo = {
      id: photoId.value,
      url: imagePreview.value
    };
    updatedData.photo_id = photoId.value;
  }

  // Вызываем updateStep один раз со всеми изменениями
  emit('updateStep', props.id, 'all', updatedData);
  
  showEditModal.value = false;
};

// Следим за изменением timerNextStepId и очищаем невалидные значения
watch(timerNextStepId, (newValue) => {
  if (newValue && !isSelectedStepValid.value) {
    timerNextStepId.value = null;
  }
});

// Вычисляем классы для карточки шага
const stepCardClasses = computed(() => ({
  'step-card': true,
  'with-notice': !!props.data.description_notice
}));

// Проверяем, есть ли у шага таймер
const hasTimer = computed(() => !!props.data.timer);

// Форматируем время таймера для отображения
const formattedTimerTime = computed(() => {
  if (!props.data.timer?.time) return '';
  
  const hours = props.data.timer.time.hours || 0;
  const minutes = props.data.timer.time.minutes || 0;
  
  if (hours > 0) {
    return `${hours} ч ${minutes} мин`;
  } else {
    return `${minutes} мин`;
  }
});
</script>

<template>
  <div :ref="'stepCard' + props.id" :class="stepCardClasses">
    <div class="step-content">
      <!-- Фото шага -->
      <div class="step-photo">
        <div class="step-image">
          <div class="step-image-fit">
            <img v-if="props.data.photo" :src="props.data.photo.url" alt="Фото шага" />
          </div>
        </div>
      </div>
      <div class="step-title">Шаг {{ props.index + 1 }}</div>
      <!-- Описание -->
      <div class="step-description">
        <p>{{ props.data.description }}</p>
      </div>

      <!-- Таймеры -->
      <div class="step-timer">
        <div v-if="isReferencedByOtherStep" class="timer-reference">
          <VIcon icon="mdi-timer-outline" size="16" class="me-1" />
          <VIcon icon="mdi-arrow-left-thin" size="16" class="me-1" />
          <span>{{ referencingStepName }}</span>
        </div>
        <div v-if="hasTimer" class="timer-target">
          <VIcon icon="mdi-timer-outline" size="16" class="me-1" />
          <VIcon icon="mdi-arrow-right-thin" size="16" class="me-1" />
          <span>{{ timerTargetStepName }}</span>
          <VIcon 
            v-if="isTimerBroken"
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
          @click="removeStep"
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
          @click="openEditModal"
          title="Редактировать шаг"
        >
          <VIcon size="16" icon="mdi-pencil" />
        </VBtn>

        <VBtn
          v-show="props.allowSorting"
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

  <!-- Модальное окно редактирования шага -->
  <VDialog 
    v-model="showEditModal"
    scroll-strategy="none"
    max-width="900px"
  >
    <VCard class="modal-card">
      <VToolbar color="primary">
        <VToolbarTitle>Редактирование шага {{ props.index + 1 }}</VToolbarTitle>
        <VSpacer />
        <VBtn icon @click="showEditModal = false">
          <VIcon>mdi-close</VIcon>
        </VBtn>
      </VToolbar>
      
      <VCardText class="pt-4 modal-content">
        <VRow>
          <!-- Колонка для фото -->
          <VCol cols="12" md="5">
            <div class="photo-column">
              <ImageUploaderEnhanced 
                v-model:photoId="photoId" 
                v-model:imageUrl="imagePreview" 
                :photo="props.data.photo" 
                v-model:photoMarkedForDeletion="photoMarkedForDeletion"
              />
            </div>
          </VCol>
          
          <!-- Колонка с описанием и таймером -->
          <VCol cols="12" md="7">
            <div class="content-column">
              <VTextarea
                v-model="description"
                label="Описание шага"
                rows="3"
                auto-grow
                variant="outlined"
                class="mb-4"
              />
              
              <VTextarea
                v-model="descriptionNotice"
                label="Важное примечание к шагу"
                rows="2"
                auto-grow
                variant="outlined"
                class="mb-4"
              />
              
              <!-- Управление таймером -->
              <div class="timer-controls mb-4">
                <!-- Если таймера нет - показываем чекбокс для добавления -->
                <VCheckbox
                  v-if="!hasTimer || !hasTimerEnabled"
                  v-model="hasTimerEnabled"
                  label="Добавить таймер"
                  class="mb-3"
                />
                
                <!-- Если таймер есть - показываем кнопку удаления -->
                <div v-else class="d-flex align-center mb-3">
                  <VIcon icon="mdi-timer-outline" class="me-2" />
                  <span class="text-subtitle-1 me-4">Таймер настроен</span>
                  <VBtn
                    variant="outlined"
                    color="error"
                    size="small"
                    @click="removeTimer"
                  >
                    <VIcon size="16" icon="mdi-delete" class="me-1" />
                    Удалить таймер
                  </VBtn>
                </div>
              </div>
              
              <!-- Поля таймера (показываются только если включен) -->
              <div v-if="hasTimerEnabled" class="timer-fields">
                <VTextField
                  v-model="timerName"
                  label="Название таймера"
                  variant="outlined"
                  class="mb-3"
                />
                
                <VRow class="mb-3">
                  <VCol cols="6">
                    <VTextField
                      v-model="timerHours"
                      label="Часы"
                      type="number"
                      min="0"
                      variant="outlined"
                    />
                  </VCol>
                  
                  <VCol cols="6">
                    <VTextField
                      v-model="timerMinutes"
                      label="Минуты"
                      type="number"
                      min="0"
                      max="59"
                      variant="outlined"
                    />
                  </VCol>
                </VRow>
                
                <!-- Предупреждение о нарушенной ссылке -->
                <VAlert
                  v-if="isTimerBroken && hasTimerEnabled"
                  type="warning"
                  class="mb-4"
                  variant="tonal"
                >
                  <VAlertTitle>Внимание!</VAlertTitle>
                  Таймер ссылался на удаленный шаг. Выберите новый шаг или оставьте поле пустым.
                </VAlert>
                
                <VSelect
                  v-model="timerNextStepId"
                  :items="availableSteps"
                  label="Следующий шаг (опционально)"
                  variant="outlined"
                  clearable
                  :error="!isSelectedStepValid"
                  :error-messages="!isSelectedStepValid ? ['Выбранный шаг не существует'] : []"
                />
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
          @click="showEditModal = false"
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
</template>

<style scoped>
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
