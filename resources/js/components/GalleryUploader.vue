<template>
<div class="gallery-uploader">
  <!-- Список загруженных фото -->
  <div v-if="gallery.length > 0" class="gallery-grid">
    <div
      v-for="(photo, index) in gallery"
      :key="photo.id || photo.tempId"
      class="gallery-item"
      :class="{ 'marked-for-deletion': photo.markedForDeletion }"
      draggable="true"
      @dragstart="onDragStart(index, $event)"
      @dragover.prevent="onDragOver($event)"
      @dragleave="onDragLeave"
      @drop.prevent="onDrop(index, $event)"
    >
      <div class="image-container">
        <img
          v-lazy="photo.url"
          :alt="`Фото ${index + 1}`"
          @load="onImageLoad"
        />
        
        <!-- Оверлей с действиями -->
        <div class="image-overlay">
          <VBtn
            v-if="!photo.markedForDeletion"
            color="error"
            size="small"
            icon="mdi-delete"
            variant="elevated"
            @click="markForDeletion(index)"
            class="action-btn delete-btn"
            aria-label="Удалить фото"
          />
          
          <VBtn
            v-if="photo.markedForDeletion"
            color="success"
            size="small"
            icon="mdi-undo"
            variant="elevated"
            @click="undoDeletion(index)"
            class="action-btn undo-btn"
            aria-label="Отменить удаление"
          />
          
          <!-- Кнопка установки как главное фото -->
          <VBtn
            v-if="index !== 0 && !photo.markedForDeletion"
            color="primary"
            size="small"
            icon="mdi-star"
            variant="elevated"
            @click="setAsMainPhoto(index)"
            class="action-btn main-photo-btn"
            aria-label="Сделать главным фото"
          />
          
          <!-- Индикатор главного фото -->
          <div v-if="index === 0 && !photo.markedForDeletion" class="main-photo-indicator">
            <VIcon icon="mdi-star" color="yellow" />
            <span>Главное</span>
          </div>
        </div>
        
        <!-- Прогресс загрузки -->
        <div v-if="photo.uploading" class="upload-progress">
          <VProgressCircular
            :model-value="photo.progress"
            color="primary"
            size="40"
            width="3"
          >
            {{ photo.progress }}%
          </VProgressCircular>
        </div>
        
        <!-- Ошибка загрузки -->
        <div v-if="photo.error" class="upload-error">
          <VIcon icon="mdi-alert-circle" color="error" />
        </div>
      </div>
      
      <!-- Информация о фото -->
      <div class="photo-info">
        <div class="photo-name">{{ getPhotoName(photo) }}</div>
        <div v-if="photo.dimensions" class="photo-dimensions">
          {{ photo.dimensions.width }}x{{ photo.dimensions.height }} px
        </div>
      </div>
    </div>
    
    <!-- Кнопка добавления нового фото -->
    <div
      class="add-photo-btn"
      :class="{ 'drag-over': isDragging }"
      @dragover.prevent="onDragOver"
      @dragleave="onDragLeave"
      @drop.prevent="onDrop"
      @click="triggerFileInput"
    >
      <VIcon icon="mdi-plus" size="32" color="grey-lighten-1" />
      <div class="add-text">Добавить фото</div>
      <div v-if="gallery.length >= maxPhotos" class="max-photos-warning">
        Максимум: {{ maxPhotos }} фото
      </div>
    </div>
  </div>
  
  <!-- Если нет фото, показываем кнопку загрузки -->
  <div
    v-else
    class="empty-gallery"
    :class="{ 'drag-over': isDragging }"
    @dragover.prevent="onDragOver"
    @dragleave="onDragLeave"
    @drop.prevent="onDrop"
  >
    <div class="empty-content">
      <VIcon icon="mdi-image-multiple" size="64" color="grey-lighten-1" />
      <div class="empty-title">Нет загруженных фото</div>
      <div class="empty-subtitle">
        Нажмите для выбора или перетащите файлы сюда
      </div>
      <VBtn color="primary" class="mt-4" @click="triggerFileInput">
        <VIcon icon="mdi-cloud-upload-outline" start />
        Загрузить фото
      </VBtn>
    </div>
  </div>
  
  <!-- Скрытый input для выбора файлов -->
  <input
    ref="fileInput"
    type="file"
    :accept="acceptedFormats"
    multiple
    hidden
    @change="handleFileChange"
  />
  
  <!-- Информация о требованиях -->
  <VTooltip location="top">
    <template v-slot:activator="{ props }">
      <VIcon
        v-bind="props"
        icon="mdi-information-outline"
        class="requirements-info"
        aria-label="Информация о требованиях"
      />
    </template>
    <div class="requirements">
      <div>Допустимые форматы: {{ acceptedFormatsDisplay }}</div>
      <div>Максимальный размер файла: {{ formatFileSize(maxFileSizeKB * 1024) }}</div>
      <div v-if="maxWidth && maxHeight">
        Максимальные размеры: {{ maxWidth }}x{{ maxHeight }} px
      </div>
      <div>Минимальные размеры: {{ minWidth }}x{{ minHeight }} px</div>
      <div>Максимум фото: {{ maxPhotos }}</div>
    </div>
  </VTooltip>
  
  <!-- Счетчик фото -->
  <div v-if="gallery.length > 0" class="photo-counter">
    {{ gallery.filter(p => !p.markedForDeletion).length }}/{{ maxPhotos }} фото
  </div>
</div>
</template>

<script setup>
import { ref, watch, defineEmits, computed, nextTick } from 'vue';
import { VBtn, VIcon, VProgressCircular, VTooltip } from 'vuetify/components';
import { usePhotoStore } from '@/stores/photoStore';

const props = defineProps({
  photos: {
    type: Array,
    default: () => [],
  },
  acceptedFormats: {
    type: String,
    default: 'image/jpeg,image/png,image/gif,image/webp',
  },
  maxFileSizeKB: {
    type: Number,
    default: 2048,
  },
  maxWidth: {
    type: Number,
    default: null,
  },
  maxHeight: {
    type: Number,
    default: null,
  },
  minWidth: {
    type: Number,
    default: 100,
  },
  minHeight: {
    type: Number,
    default: 100,
  },
  maxPhotos: {
    type: Number,
    default: 10,
  },
  compressImages: {
    type: Boolean,
    default: true,
  },
  compressQuality: {
    type: Number,
    default: 0.8,
  },
});

const emit = defineEmits(['update:photos', 'error']);

// Реактивные данные
const gallery = ref([]);
const fileInput = ref(null);
const photoStore = usePhotoStore();
const isDragging = ref(false);
const draggedIndex = ref(null);

// Вычисляемые свойства
const acceptedFormatsDisplay = computed(() => {
  return props.acceptedFormats
    .replace(/image\//g, '')
    .split(',')
    .map(format => format.toUpperCase())
    .join(', ');
});

// Инициализация
watch(() => props.photos, (newPhotos) => {
  gallery.value = newPhotos.map(photo => ({
    ...photo,
    markedForDeletion: false,
    uploading: false,
    progress: 0,
    error: null,
    dimensions: null,
  }));
}, { immediate: true });

// Методы
const triggerFileInput = () => {
  if (gallery.value.length >= props.maxPhotos) {
    emit('error', `Максимум ${props.maxPhotos} фото`);
    return;
  }
  fileInput.value.click();
};

const handleFileChange = async (event) => {
  const files = Array.from(event.target.files);
  await processFiles(files);
  event.target.value = ''; // Сбрасываем input для возможности выбора тех же файлов
};

const processFiles = async (files) => {
  const remainingSlots = props.maxPhotos - gallery.value.length;
  const filesToProcess = files.slice(0, remainingSlots);
  
  if (files.length > remainingSlots) {
    emit('error', `Можно загрузить только ${remainingSlots} фото (лимит: ${props.maxPhotos})`);
  }
  
  for (const file of filesToProcess) {
    await addPhotoToGallery(file);
  }
};

const addPhotoToGallery = async (file) => {
  try {
    // Валидация файла
    const isValid = await validateFile(file);
    if (!isValid) {
      return;
    }
    
    // Создаем временный объект фото
    const tempId = Date.now() + Math.random();
    const tempPhoto = {
      tempId,
      url: URL.createObjectURL(file),
      fileName: file.name,
      uploading: true,
      progress: 0,
      error: null,
      markedForDeletion: false,
      dimensions: null,
    };
    
    // Добавляем в галерею
    gallery.value.push(tempPhoto);
    updatePhotos();
    
    // Загружаем на сервер
    await uploadPhoto(file, tempId);
    
  } catch (error) {
    console.error('Ошибка обработки файла:', error);
    emit('error', `Ошибка обработки файла ${file.name}: ${error.message}`);
  }
};

const validateFile = async (file) => {
  // Проверка формата
  const isValidFormat = props.acceptedFormats.split(',').includes(file.type);
  if (!isValidFormat) {
    emit('error', `Недопустимый формат файла: ${file.type}`);
    return false;
  }
  
  // Проверка размера
  const isValidSize = file.size / 1024 <= props.maxFileSizeKB;
  if (!isValidSize) {
    emit('error', `Файл ${file.name} слишком большой (${formatFileSize(file.size)})`);
    return false;
  }
  
  // Проверка размеров изображения
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      const isValidMaxDimensions = (!props.maxWidth || img.width <= props.maxWidth) && 
                                   (!props.maxHeight || img.height <= props.maxHeight);
      const isValidMinDimensions = img.width >= props.minWidth && img.height >= props.minHeight;
      
      if (!isValidMaxDimensions) {
        emit('error', `Фото ${file.name} слишком большое. Максимум: ${props.maxWidth}x${props.maxHeight}`);
        resolve(false);
      } else if (!isValidMinDimensions) {
        emit('error', `Фото ${file.name} слишком маленькое. Минимум: ${props.minWidth}x${props.minHeight}`);
        resolve(false);
      } else {
        resolve(true);
      }
    };
    
    img.onerror = () => {
      emit('error', `Не удалось загрузить изображение: ${file.name}`);
      resolve(false);
    };
    
    img.src = URL.createObjectURL(file);
  });
};

const uploadPhoto = async (file, tempId) => {
  try {
    // Находим индекс временного фото
    const photoIndex = gallery.value.findIndex(p => p.tempId === tempId);
    if (photoIndex === -1) return;
    
    // Сжимаем изображение если нужно
    let fileToUpload = file;
    if (props.compressImages && file.type !== 'image/gif') {
      try {
        fileToUpload = await compressImage(file);
      } catch (error) {
        console.warn('Ошибка сжатия изображения:', error);
      }
    }
    
    // Загружаем на сервер
    const photo = await photoStore.upload(fileToUpload, (progress) => {
      gallery.value[photoIndex].progress = progress;
    });
    
    // Обновляем данные фото
    gallery.value[photoIndex] = {
      ...gallery.value[photoIndex],
      id: photo.id,
      url: photo.url,
      uploading: false,
      progress: 100,
    };
    
    // Освобождаем временный URL
    URL.revokeObjectURL(gallery.value[photoIndex].url);
    
    updatePhotos();
    
  } catch (error) {
    console.error('Ошибка загрузки фото:', error);
    
    const photoIndex = gallery.value.findIndex(p => p.tempId === tempId);
    if (photoIndex !== -1) {
      gallery.value[photoIndex].uploading = false;
      gallery.value[photoIndex].error = true;
      emit('error', `Ошибка загрузки фото ${file.name}`);
    }
  }
};

const compressImage = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (event) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        let width = img.width;
        let height = img.height;
        
        // Уменьшаем размер если нужно
        if (props.maxWidth && width > props.maxWidth) {
          height = (props.maxWidth / width) * height;
          width = props.maxWidth;
        }
        
        if (props.maxHeight && height > props.maxHeight) {
          width = (props.maxHeight / height) * width;
          height = props.maxHeight;
        }
        
        canvas.width = width;
        canvas.height = height;
        ctx.drawImage(img, 0, 0, width, height);
        
        canvas.toBlob(
          (blob) => {
            if (blob) {
              const compressedFile = new File([blob], file.name, {
                type: file.type,
                lastModified: Date.now(),
              });
              resolve(compressedFile);
            } else {
              reject(new Error('Не удалось сжать изображение'));
            }
          },
          file.type,
          props.compressQuality
        );
      };
      
      img.onerror = () => reject(new Error('Не удалось загрузить изображение'));
      img.src = event.target.result;
    };
    
    reader.onerror = () => reject(new Error('Не удалось прочитать файл'));
    reader.readAsDataURL(file);
  });
};

// Drag and Drop для изменения порядка
const onDragStart = (index, event) => {
  draggedIndex.value = index;
  event.dataTransfer.effectAllowed = 'move';
  event.target.classList.add('dragging');
};

const onDragOver = (event) => {
  event.dataTransfer.dropEffect = 'move';
  if (!event.currentTarget.classList.contains('gallery-item')) {
    isDragging.value = true;
  }
};

const onDragLeave = (event) => {
  if (event.target === event.currentTarget) {
    isDragging.value = false;
  }
};

const onDrop = (targetIndex, event) => {
  event.preventDefault();
  isDragging.value = false;
  
  // Убираем класс у перетаскиваемого элемента
  const draggingElement = document.querySelector('.dragging');
  if (draggingElement) {
    draggingElement.classList.remove('dragging');
  }
  
  if (draggedIndex.value !== null && draggedIndex.value !== targetIndex) {
    const draggedPhoto = gallery.value[draggedIndex.value];
    gallery.value.splice(draggedIndex.value, 1);
    gallery.value.splice(targetIndex, 0, draggedPhoto);
    updatePhotos();
  }
  
  draggedIndex.value = null;
  
  // Если это drop на кнопку добавления, обрабатываем файлы
  if (event.currentTarget.classList.contains('add-photo-btn') || 
      event.currentTarget.classList.contains('empty-gallery')) {
    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
  }
};

// Действия с фото
const markForDeletion = (index) => {
  gallery.value[index].markedForDeletion = true;
  updatePhotos();
};

const undoDeletion = (index) => {
  gallery.value[index].markedForDeletion = false;
  updatePhotos();
};

const setAsMainPhoto = (index) => {
  const photo = gallery.value.splice(index, 1)[0];
  gallery.value.unshift(photo);
  updatePhotos();
};

// Вспомогательные методы
const onImageLoad = (event) => {
  const img = event.target;
  const photoElement = img.closest('.gallery-item');
  const index = Array.from(photoElement.parentNode.children).indexOf(photoElement);
  
  if (gallery.value[index]) {
    gallery.value[index].dimensions = {
      width: img.naturalWidth,
      height: img.naturalHeight,
    };
  }
};

const getPhotoName = (photo) => {
  return photo.fileName || `Фото ${gallery.value.indexOf(photo) + 1}`;
};

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B';
  else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  else return (bytes / 1048576).toFixed(1) + ' MB';
};

const updatePhotos = () => {
  // Отправляем только загруженные фото без временных
  const uploadedPhotos = gallery.value
    .filter(p => p.id && !p.markedForDeletion)
    .map(({ id, url, sort_order }) => ({ id, url, sort_order }));
    
  // Добавляем фото, помеченные на удаление
  const deletedPhotos = gallery.value
    .filter(p => p.id && p.markedForDeletion)
    .map(p => ({ ...p, deleted: true }));
    
  emit('update:photos', [...uploadedPhotos, ...deletedPhotos]);
};
</script>

<style scoped>
.gallery-uploader {
  width: 100%;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.gallery-item {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  background-color: rgb(var(--v-theme-surface));
  border: 2px solid rgba(var(--v-theme-on-surface), 0.12);
  transition: all 0.3s ease;
  cursor: move;
}

.gallery-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  border-color: rgb(var(--v-theme-primary));
}

.gallery-item.dragging {
  opacity: 0.5;
  transform: rotate(5deg);
}

.gallery-item.marked-for-deletion {
  opacity: 0.5;
  border-color: rgb(var(--v-theme-error));
}

.gallery-item.marked-for-deletion .image-container img {
  filter: grayscale(100%);
}

.image-container {
  position: relative;
  width: 100%;
  padding-top: 75%; /* 4:3 соотношение */
  overflow: hidden;
}

.image-container img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s ease;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.3) 0%,
    transparent 30%,
    transparent 70%,
    rgba(0, 0, 0, 0.5) 100%
  );
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  padding: 12px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.gallery-item:hover .image-overlay {
  opacity: 1;
}

.action-btn {
  width: 36px !important;
  height: 36px !important;
  border-radius: 50% !important;
}

.delete-btn {
  background-color: rgb(var(--v-theme-error)) !important;
}

.undo-btn {
  background-color: rgb(var(--v-theme-success)) !important;
}

.main-photo-btn {
  background-color: rgb(var(--v-theme-warning)) !important;
}

.main-photo-indicator {
  display: flex;
  align-items: center;
  gap: 4px;
  background-color: rgba(255, 193, 7, 0.9);
  color: black;
  padding: 4px 8px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
}

.upload-progress {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: rgba(var(--v-theme-surface), 0.9);
  border-radius: 50%;
  padding: 8px;
}

.upload-error {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: rgba(var(--v-theme-error), 0.9);
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.photo-info {
  padding: 8px 12px;
  background-color: rgb(var(--v-theme-surface-variant));
}

.photo-name {
  font-size: 14px;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.photo-dimensions {
  font-size: 12px;
  color: rgba(var(--v-theme-on-surface), 0.7);
  margin-top: 2px;
}

.add-photo-btn {
  position: relative;
  border-radius: 12px;
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  background-color: rgb(var(--v-theme-surface));
  min-height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 20px;
}

.add-photo-btn:hover {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
  transform: translateY(-2px);
}

.add-photo-btn.drag-over {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.1);
  transform: scale(1.02);
}

.add-text {
  margin-top: 8px;
  font-size: 16px;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.max-photos-warning {
  margin-top: 8px;
  font-size: 12px;
  color: rgb(var(--v-theme-error));
  text-align: center;
}

.empty-gallery {
  border-radius: 12px;
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  background-color: rgb(var(--v-theme-surface));
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.empty-gallery:hover {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.empty-gallery.drag-over {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.1);
  transform: scale(1.01);
}

.empty-content {
  text-align: center;
  padding: 40px;
}

.empty-title {
  font-size: 20px;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.9);
  margin-top: 16px;
}

.empty-subtitle {
  font-size: 14px;
  color: rgba(var(--v-theme-on-surface), 0.6);
  margin-top: 8px;
  margin-bottom: 24px;
}

.requirements-info {
  position: absolute;
  top: 16px;
  right: 16px;
  cursor: pointer;
  color: rgba(var(--v-theme-on-surface), 0.5);
}

.requirements {
  font-size: 0.9em;
  padding: 12px;
  line-height: 1.5;
  max-width: 300px;
}

.photo-counter {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 16px;
  background-color: rgb(var(--v-theme-surface-variant));
  border-radius: 8px;
  font-size: 14px;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

/* Адаптивность */
@media (max-width: 960px) {
  .gallery-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
  }
}

@media (max-width: 600px) {
  .gallery-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 8px;
  }
  
  .gallery-item {
    border-radius: 8px;
  }
  
  .add-photo-btn {
    min-height: 150px;
    border-radius: 8px;
    padding: 16px;
  }
  
  .empty-gallery {
    min-height: 250px;
    border-radius: 8px;
  }
  
  .empty-content {
    padding: 24px;
  }
  
  .empty-title {
    font-size: 18px;
  }
  
  .action-btn {
    width: 32px !important;
    height: 32px !important;
  }
  
  .image-overlay {
    padding: 8px;
  }
  
  .photo-info {
    padding: 6px 8px;
  }
  
  .photo-name {
    font-size: 12px;
  }
  
  .photo-dimensions {
    font-size: 10px;
  }
}

/* Анимации */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.gallery-item {
  animation: fadeIn 0.3s ease;
}

/* Темная тема */
.gallery-uploader .gallery-item {
  background-color: rgb(var(--v-theme-surface-variant));
  border-color: rgba(var(--v-theme-on-surface-variant), 0.12);
}

.gallery-uploader .add-photo-btn,
.gallery-uploader .empty-gallery {
  background-color: rgb(var(--v-theme-surface-variant));
  border-color: rgba(var(--v-theme-on-surface-variant), 0.3);
}

.gallery-uploader .photo-info {
  background-color: rgb(var(--v-theme-surface));
}

/* Эффект загрузки */
.gallery-item.uploading {
  pointer-events: none;
}

.gallery-item.uploading .image-container img {
  opacity: 0.5;
}

/* Индикатор перетаскивания */
.gallery-item.drag-over {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.1);
}

/* Стили для ленивой загрузки */
.image-container img[lazy="loading"] {
  opacity: 0;
  filter: blur(5px);
}

.image-container img[lazy="loaded"] {
  opacity: 1;
  filter: blur(0);
  transition: all 0.3s ease;
}

.image-container img[lazy="error"] {
  opacity: 0.3;
  background-color: rgba(var(--v-theme-error), 0.1);
}
</style>