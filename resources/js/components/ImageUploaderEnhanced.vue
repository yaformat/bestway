<template>
  <div 
    class="image-uploader-enhanced" 
    :class="{ 'with-img': imageUrl && !photoMarkedForDeletion, 'dark-theme': isDarkTheme }"
    @dragover.prevent="onDragOver"
    @dragleave.prevent="onDragLeave"
    @drop.prevent="onDrop"
  >
    <div 
      class="img-wrapper" 
      :class="{ 
        'with-img': imageUrl && !photoMarkedForDeletion, 
        'drag-over': isDragging,
        'has-error': validationError,
        'cropping-mode': isCropping
      }" 
      :style="{ paddingTop: isCropping ? '0' : aspectRatioPadding }"
    >
      <transition name="fade">
        <img 
          v-if="imageUrl && !photoMarkedForDeletion && !isCropping" 
          :src="imageUrl" 
          alt="Загруженное изображение"
          @load="onImageLoad"
          ref="imageRef"
          class="preview-image"
        />
      </transition>
      
      <!-- Компонент для кадрирования -->
      <div v-if="isCropping" class="cropper-container">
        <Cropper
          ref="cropperRef"
          class="cropper"
          :src="cropperImage"
          :stencil-props="{
            aspectRatio: computedCropperAspectRatio
          }"
          :default-size="defaultCropperSize"
          image-restriction="stencil"
          :min-width="minWidth"
          :min-height="minHeight"
        />
        
        <div class="cropper-controls">
          <VBtn color="error" @click="cancelCropping" class="mr-2">
            Отмена
          </VBtn>
          <VBtn color="primary" @click="applyCrop">
            Применить
          </VBtn>
        </div>
      </div>
      
      <div class="overlay" v-if="imageUrl && !photoMarkedForDeletion && !uploading && !isCropping">
        <div class="image-info" v-if="imageInfo.width">
          {{ imageInfo.width }}x{{ imageInfo.height }} px
        </div>
      </div>

      <form class="form d-flex flex-column justify-center gap-4" v-if="!isCropping">
        <transition name="fade">
          <div v-if="validationError" class="validation-error">
            {{ validationError }}
          </div>
        </transition>
        
        <div v-if="!uploading" class="d-flex flex-wrap justify-center gap-4">
          <VBtn 
            v-if="!photoId || photoMarkedForDeletion" 
            color="primary" 
            @click="triggerFileInput" 
            class="upload-btn"
            :disabled="uploading"
            aria-label="Загрузить изображение"
          >
            <VIcon icon="mdi-cloud-upload-outline" class="d-sm-none" aria-hidden="true" />
            <span class="d-none d-sm-block">Загрузить изображение</span>
            <span class="d-sm-none ms-2">Загрузить</span>
          </VBtn>
          
          <VBtn 
            v-if="photoId && !photoMarkedForDeletion" 
            color="primary" 
            @click="triggerFileInput" 
            class="upload-replace-btn"
            :disabled="uploading"
            aria-label="Заменить изображение"
          >
            <VIcon icon="mdi-cloud-upload-outline" class="d-sm-none" aria-hidden="true" />
            <span class="d-none d-sm-block">Заменить изображение</span>
            <span class="d-sm-none ms-2">Заменить</span>
          </VBtn>
          
          <div v-if="photoId" class="action-buttons">
            <VBtn 
              v-if="!photoMarkedForDeletion" 
              type="reset" 
              color="error" 
              @click="markForDeletion"
              aria-label="Удалить изображение"
            >
              <VIcon icon="mdi-delete" aria-hidden="true" />
            </VBtn>
            
            <VBtn 
              v-if="photoMarkedForDeletion" 
              type="reset" 
              color="primary" 
              @click="undoDeletion"
              aria-label="Отменить удаление"
            >
              <VIcon icon="mdi-undo" aria-hidden="true" />
            </VBtn>
            
            <VBtn 
              v-if="imageUrl && !photoMarkedForDeletion && props.enableCropping" 
              color="secondary" 
              @click="startCropping"
              aria-label="Кадрировать изображение"
              class="mr-2"
            >
              <VIcon icon="mdi-crop" aria-hidden="true" />
            </VBtn>
            
            <VBtn 
              v-if="imageUrl && !photoMarkedForDeletion" 
              color="secondary" 
              @click="rotateImage"
              aria-label="Повернуть изображение"
            >
              <VIcon icon="mdi-rotate-right" aria-hidden="true" />
            </VBtn>
          </div>
        </div>
        
        <input 
          ref="fileInput" 
          type="file" 
          :accept="acceptedFormats" 
          hidden 
          @change="handleFileChange" 
          aria-label="Выбрать файл изображения"
        />
        
        <div class="drop-zone-message" v-if="!imageUrl || photoMarkedForDeletion">
          <VIcon icon="mdi-cloud-upload-outline" size="48" color="grey-lighten-1" aria-hidden="true" />
          <div>Перетащите изображение сюда или нажмите для выбора</div>
        </div>
        
        <VTooltip location="top">
          <template v-slot:activator="{ props }">
            <VIcon v-bind="props" icon="mdi-information-outline" class="info-icon" aria-label="Информация о требованиях" />
          </template>
          <div class="requirements">
            <div>Допустимые форматы: {{ acceptedFormatsDisplay }}</div>
            <div>Максимальный размер файла: {{ formatFileSize(maxFileSizeKB * 1024) }}</div>
            <div v-if="maxWidth && maxHeight">Максимальные размеры изображения: {{ maxWidth }}x{{ maxHeight }} пикселей</div>
            <div>Минимальные размеры изображения: {{ minWidth }}x{{ minHeight }} пикселей</div>
          </div>
        </VTooltip>
      </form>
      
      <transition name="fade">
        <div v-if="uploading" class="progress-container">
          <VProgressCircular 
            :model-value="uploadProgress" 
            color="primary" 
            size="100"
            aria-label="Прогресс загрузки"
          >
            {{ uploadProgress }}%
          </VProgressCircular>
          <div class="upload-status">{{ uploadStatusText }}</div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineEmits, computed, onMounted, nextTick } from 'vue';
import { VBtn, VIcon, VProgressCircular, VTooltip } from 'vuetify/components';
import { usePhotoStore } from '@/stores/photoStore';
import { useTheme } from 'vuetify';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

const props = defineProps({
  photo: {
    type: Object,
    default: null,
  },
  acceptedFormats: {
    type: String,
    default: 'image/jpeg,image/png,image/gif,image/webp',
  },
  maxFileSizeKB: {
    type: Number,
    default: 2048, // 2 MB
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
  aspectRatio: {
    type: String,
    default: '4:3', // Default aspect ratio
  },
  compressImages: {
    type: Boolean,
    default: true,
  },
  compressQuality: {
    type: Number,
    default: 0.8, // 80% качество
  },
  enableCropping: {
    type: Boolean,
    default: true,
  },
  cropperAspectRatio: {
    type: Number,
    default: null, // Если null, будет использоваться аспект из props.aspectRatio
  },
});

const emit = defineEmits(['update:photoId', 'update:imageUrl', 'update:photoMarkedForDeletion', 'error']);

const theme = useTheme();
const isDarkTheme = computed(() => theme.global.current.value.dark);

const imageUrl = ref(props.photo ? props.photo.url : null);
const photoId = ref(props.photo ? props.photo.id : null);
const fileInput = ref(null);
const imageRef = ref(null);
const cropperRef = ref(null);
const photoStore = usePhotoStore();

const uploading = ref(false);
const uploadProgress = ref(0);
const uploadStatusText = ref('');
const photoMarkedForDeletion = ref(false);
const isDragging = ref(false);
const validationError = ref(null);
const imageInfo = ref({ width: 0, height: 0 });
const imageRotation = ref(0);
const isCropping = ref(false);
const cropperImage = ref(null);
const originalImage = ref(null);

// Вычисляемые свойства
const aspectRatioPadding = computed(() => {
  const [width, height] = props.aspectRatio.split(':').map(Number);
  return `${(height / width) * 100}%`;
});

const acceptedFormatsDisplay = computed(() => {
  return props.acceptedFormats
    .replace(/image\//g, '')
    .split(',')
    .map(format => format.toUpperCase())
    .join(', ');
});

const defaultCropperSize = computed(() => {
  return {
    width: 80,
    height: 80,
  };
});

const computedCropperAspectRatio = computed(() => {
  if (props.cropperAspectRatio) return props.cropperAspectRatio;
  
  const [width, height] = props.aspectRatio.split(':').map(Number);
  return width / height;
});

// Методы
const triggerFileInput = () => {
  validationError.value = null;
  fileInput.value.click();
};

const onDragOver = (event) => {
  isDragging.value = true;
  event.dataTransfer.dropEffect = 'copy';
};

const onDragLeave = () => {
  isDragging.value = false;
};

const onDrop = (event) => {
  isDragging.value = false;
  const files = event.dataTransfer.files;
  if (files.length > 0) {
    handleFile(files[0]);
  }
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    handleFile(file);
  }
};

const handleFile = async (file) => {
  validationError.value = null;
  
  try {
    const isValid = await validateFile(file);
    if (!isValid) {
      return;
    }
    
    if (photoId.value) {
      // Если есть предыдущее фото, помеченное для удаления, сбросим это состояние
      photoMarkedForDeletion.value = false;
      emit('update:photoMarkedForDeletion', false);
    }
    
    // Сохраняем оригинальное изображение для кадрирования
    originalImage.value = file;
    
    // Предпросмотр изображения
    const reader = new FileReader();
    reader.onload = (e) => {
      imageUrl.value = e.target.result;
      
      // Если включено автоматическое кадрирование, показываем кроппер
      if (props.enableCropping && props.cropperAspectRatio) {
        startCropping();
      } else {
        processAndUploadImage(file);
      }
    };
    reader.readAsDataURL(file);
  } catch (error) {
    console.error('Ошибка обработки файла:', error);
    validationError.value = 'Произошла ошибка при обработке файла';
    emit('error', error);
  }
};

const processAndUploadImage = async (file) => {
  // Если нужно сжать изображение
  if (props.compressImages && file.type !== 'image/gif') {
    try {
      const compressedFile = await compressImage(file);
      uploadImage(compressedFile);
    } catch (error) {
      console.warn('Ошибка сжатия изображения, загружаем оригинал:', error);
      uploadImage(file);
    }
  } else {
    uploadImage(file);
  }
};

const validateFile = async (file) => {
  // Проверка формата
  const isValidFormat = props.acceptedFormats.split(',').includes(file.type);
  if (!isValidFormat) {
    validationError.value = `Недопустимый формат файла. Разрешены только: ${acceptedFormatsDisplay.value}`;
    return false;
  }
  
  // Проверка размера
  const isValidSize = file.size / 1024 <= props.maxFileSizeKB;
  if (!isValidSize) {
    validationError.value = `Размер файла превышает максимально допустимый (${formatFileSize(props.maxFileSizeKB * 1024)})`;
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
        validationError.value = `Изображение слишком большое. Максимальный размер: ${props.maxWidth}x${props.maxHeight} пикселей`;
        resolve(false);
      } else if (!isValidMinDimensions) {
        validationError.value = `Изображение слишком маленькое. Минимальный размер: ${props.minWidth}x${props.minHeight} пикселей`;
        resolve(false);
      } else {
        resolve(true);
      }
    };
    img.onerror = () => {
      validationError.value = 'Не удалось загрузить изображение. Проверьте, что файл является изображением.';
      resolve(false);
    };
    img.src = URL.createObjectURL(file);
  });
};

const compressImage = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (event) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Определяем размеры для сжатия
        let width = img.width;
        let height = img.height;
        
        // Если изображение больше максимальных размеров, уменьшаем его
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
        
        // Применяем поворот, если есть
        if (imageRotation.value !== 0) {
          ctx.save();
          ctx.translate(canvas.width / 2, canvas.height / 2);
          ctx.rotate((imageRotation.value * Math.PI) / 180);
          ctx.drawImage(img, -width / 2, -height / 2, width, height);
          ctx.restore();
        } else {
          ctx.drawImage(img, 0, 0, width, height);
        }
        
        // Конвертируем в blob
        canvas.toBlob(
          (blob) => {
            if (blob) {
              // Создаем новый файл с тем же именем и типом
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
      img.onerror = () => reject(new Error('Не удалось загрузить изображение для сжатия'));
      img.src = event.target.result;
    };
    reader.onerror = () => reject(new Error('Не удалось прочитать файл'));
    reader.readAsDataURL(file);
  });
};

const uploadImage = async (file) => {
  try {
    uploading.value = true;
    uploadProgress.value = 0;
    uploadStatusText.value = 'Подготовка к загрузке...';
    
    // Имитация задержки для отображения начального статуса
    await new Promise(resolve => setTimeout(resolve, 300));
    
    uploadStatusText.value = 'Загрузка...';
    const photo = await photoStore.upload(file);
    
    uploadProgress.value = 100;
    uploadStatusText.value = 'Завершено!';
    
    // Небольшая задержка перед скрытием прогресса
    await new Promise(resolve => setTimeout(resolve, 500));
    
    imageUrl.value = photo.url;
    photoId.value = photo.id;
    uploading.value = false;
    
    emit('update:photoId', photo.id);
    emit('update:imageUrl', photo.url);
  } catch (error) {
    uploading.value = false;
    uploadStatusText.value = 'Ошибка загрузки';
    validationError.value = 'Не удалось загрузить изображение. Пожалуйста, попробуйте еще раз.';
    console.error('Error uploading image:', error);
    emit('error', error);
  }
};

const markForDeletion = () => {
  photoMarkedForDeletion.value = true;
  emit('update:photoMarkedForDeletion', true);
};

const undoDeletion = () => {
  photoMarkedForDeletion.value = false;
  emit('update:photoMarkedForDeletion', false);
};

const rotateImage = async () => {
  if (!imageRef.value) return;
  
  imageRotation.value = (imageRotation.value + 90) % 360;
  
  // Применяем поворот к изображению через CSS
  //imageRef.value.style.transform = `rotate(${imageRotation.value}deg)`;
  
  // Если изображение уже загружено, нужно повернуть его на сервере
  if (photoId.value) {
    try {
      //await photoStore.rotateImage(photoId.value, imageRotation.value);
      await photoStore.rotateImage(photoId.value, 90);
      // Обновляем URL изображения, чтобы получить повернутую версию
      const photo = await photoStore.fetch(photoId.value);
      //imageUrl.value = photo.url;
      imageRef.value.style.transform = `rotate(${imageRotation.value}deg)`;
      //imageUrl.value = photo.url + '?v=' + Date.now(); // Добавляем параметр для обхода кеша
    } catch (error) {
      console.error('Error rotating image:', error);
      validationError.value = 'Не удалось повернуть изображение';
      emit('error', error);
    }
  }
};

const startCropping = () => {
  if (!imageUrl.value) return;
  
  cropperImage.value = imageUrl.value;
  isCropping.value = true;
};

const cancelCropping = () => {
  isCropping.value = false;
};

const applyCrop = async () => {
  if (!cropperRef.value) return;
  
  try {
    const { canvas } = cropperRef.value.getResult();
    
    // Конвертируем canvas в blob
    const blob = await new Promise(resolve => {
      canvas.toBlob(resolve, 'image/jpeg', props.compressQuality);
    });
    
    // Создаем новый файл
    const croppedFile = new File([blob], 'cropped-image.jpg', {
      type: 'image/jpeg',
      lastModified: Date.now(),
    });
    
    // Обновляем предпросмотр
    imageUrl.value = URL.createObjectURL(croppedFile);
    
    // Выходим из режима кадрирования
    isCropping.value = false;
    
    // Загружаем обрезанное изображение
    uploadImage(croppedFile);
  } catch (error) {
    console.error('Error cropping image:', error);
    validationError.value = 'Не удалось обрезать изображение';
    emit('error', error);
    isCropping.value = false;
  }
};

const onImageLoad = () => {
  if (imageRef.value) {
    imageInfo.value = {
      width: imageRef.value.naturalWidth,
      height: imageRef.value.naturalHeight
    };
  }
};

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B';
  else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  else return (bytes / 1048576).toFixed(1) + ' MB';
};

// Наблюдатели
watch(() => props.photo, (newVal) => {
  imageUrl.value = newVal ? newVal.url : null;
  photoId.value = newVal ? newVal.id : null;
  imageRotation.value = 0;
  validationError.value = null;
});

watch(() => photoStore.uploadProgress, (newVal) => {
  uploadProgress.value = newVal;
});

// Жизненный цикл
onMounted(() => {
  // Инициализация компонента
  if (imageUrl.value) {
    nextTick(() => {
      onImageLoad();
    });
  }
});
</script>

<style scoped>
.image-uploader-enhanced {
  width: 100%;
}

.img-wrapper {
  width: 100%;
  position: relative;
  border: 2px dashed rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  background-color: rgb(var(--v-theme-surface));
  background-image: url(data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxOTkxLjI3IDE5MjEuMzMiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO2ZpbGwtb3BhY2l0eTowfS5jbHMtMntmaWxsOnNpbHZlcn08L3N0eWxlPjwvZGVmcz48dGl0bGU+cGxhY2Vob2xkZXIgaWNvbjwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMCAwaDE5OTEuMjd2MTkyMS4zM0gweiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTEyODguNTIgNzg3LjUxbC0zNC4yLTE5OS45LTcwMC4xNSAxMjIuOTMgMTAyLjYxIDU4NC43MSA2OS40OS0xMS43NnY1MC4yNGg3MTAuODNWNzg3LjUxem0tNTYyLjI1IDQzNy4xOGwtMjIuNDUgNC4zMS04My4zOC00NzEuNCA1ODYuODQtMTAzLjY5IDIzLjUyIDEzMy42Mkg3MjYuMjd6bTY1My4xMSA1MS4zMUg3ODRWODQ1LjIyaDU5NS40eiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTgyMi40NiAxMjMwLjA4aDUxOC40NHYtMTcwLjIybC0xMDktMTIzLjZhMjggMjggMCAwIDAtNDQuNzQgMy41OGwtOTMuOSAxNDguODJhMTguMjcgMTguMjcgMCAwIDEtMjcuNzMgMy43OGwtNjEuMDItNTUuNDRhMjUuMjggMjUuMjggMCAwIDAtMzcuMDcgMy4zN3oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtMiIgY3g9Ijg5Mi4xNiIgY3k9Ijk0OC4yIiByPSI0MC44MSIvPjwvc3ZnPg==);
  background-position: center center;
  background-repeat: no-repeat;
  border-radius: 10px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.img-wrapper.drag-over {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
  transform: scale(1.01);
}

.img-wrapper.has-error {
  border-color: rgb(var(--v-theme-error));
}

.img-wrapper.cropping-mode {
  background-image: none;
  height: 400px;
}

.img-wrapper .form {
  position: absolute;
  z-index: 10;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.img-wrapper img {
  z-index: 0;
  opacity: 0;
  transition: 0.3s all;
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  object-fit: cover;
  width: 100%;
  height: 100%;
}

.img-wrapper.with-img img {
  opacity: 1;
  transition: all 0.3s ease;
}

.progress-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: rgba(var(--v-theme-surface), 0.8);
  z-index: 20;
}

.upload-status {
  margin-top: 16px;
  font-size: 16px;
  font-weight: 500;
}

.upload-btn, .upload-replace-btn {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 15;
}

.action-buttons {
  position: absolute;
  top: 16px;
  right: 16px;
  display: flex;
  gap: 8px;
  z-index: 15;
}

.info-icon {
  position: absolute;
  top: 16px;
  left: 16px;
  cursor: pointer;
  z-index: 15;
}

.requirements {
  font-size: 0.9em;
  padding: 12px;
  line-height: 1.5;
}

.validation-error {
  position: absolute;
  top: 16px;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(var(--v-theme-error), 0.9);
  color: white;
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 14px;
  max-width: 80%;
  z-index: 30;
}

.drop-zone-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  padding: 16px;
  text-align: center;
  max-width: 80%;
}

.overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
  padding: 8px 16px;
  color: white;
  font-size: 12px;
  z-index: 5;
}

.image-info {
  text-align: right;
}

/* Анимации */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Темная тема */
.dark-theme .img-wrapper {
  background-color: rgb(var(--v-theme-surface-variant));
  border-color: rgba(var(--v-theme-on-surface-variant), var(--v-medium-emphasis-opacity));
}

.dark-theme .drop-zone-message {
  color: rgba(var(--v-theme-on-surface-variant), var(--v-high-emphasis-opacity));
}



.cropper-container {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
}

.cropper {
  flex: 1;
  width: 100%;
  height: calc(100% - 60px);
}

.cropper-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 12px;
  background-color: rgb(var(--v-theme-surface));
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 20;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 600px) {
  .img-wrapper {
    border-radius: 8px;
  }
  
  .action-buttons {
    top: 8px;
    right: 8px;
    gap: 4px;
  }
  
  .info-icon {
    top: 8px;
    left: 8px;
  }
  
  .validation-error {
    font-size: 12px;
    padding: 6px 12px;
  }
  
  .upload-status {
    font-size: 14px;
  }
}
</style>
