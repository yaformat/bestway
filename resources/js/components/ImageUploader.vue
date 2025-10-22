<template>
  <div class="image-uploader">
    <div class="img-wrapper" :class="{ 'with-img': imageUrl && !photoMarkedForDeletion }" :style="{ paddingTop: aspectRatioPadding }">
      <img v-lazy="imageUrl" v-if="imageUrl && !photoMarkedForDeletion" />
      <form class="form d-flex flex-column justify-center gap-4">
        <!-- <div class="msg v-card-title">Загрузить фото</div> -->
        <div v-if="!uploading" class="d-flex flex-wrap justify-center gap-4">
          <VBtn v-if="!photoId || photoMarkedForDeletion" color="primary" @click="triggerFileInput" class="upload-btn">
            <VIcon icon="mdi-cloud-upload-outline" class="d-sm-none" />
            <span class="d-none d-sm-block">Выбрать изображение</span>
            <span class="d-sm-none ms-2">Выбрать</span>
          </VBtn>
          <VBtn v-if="photoId && !photoMarkedForDeletion" color="primary" @click="triggerFileInput" class="upload-replace-btn">
            <VIcon icon="mdi-cloud-upload-outline" class="d-sm-none" />
            <span class="d-none d-sm-block">Заменить изображение</span>
            <span class="d-sm-none ms-2">Заменить</span>
          </VBtn>
          <div v-if="photoId" class="action-buttons">
            <VBtn v-if="!photoMarkedForDeletion" type="reset" color="error" @click="markForDeletion">
              <VIcon icon="mdi-delete" />
            </VBtn>
            <VBtn v-if="photoMarkedForDeletion" type="reset" color="primary" @click="undoDeletion">
              <VIcon icon="mdi-undo" />
            </VBtn>
          </div>
        </div>
        <input ref="fileInput" type="file" :accept="acceptedFormats" hidden @change="handleFileChange" />
        <VTooltip>
          <template v-slot:activator="{ props }">
            <VIcon v-bind="props" icon="mdi-information-outline" class="info-icon" />
          </template>
          <div class="requirements">
            <div>Допустимые форматы: {{ acceptedFormats.replace(/image\//g, '') }}</div>
            <div>Максимальный размер файла: {{ maxFileSizeKB }} KB</div>
            <div v-if="maxWidth && maxHeight">Максимальные размеры изображения: {{ maxWidth }}x{{ maxHeight }} пикселей</div>
            <div>Минимальные размеры изображения: {{ minWidth }}x{{ minHeight }} пикселей</div>
          </div>
        </VTooltip>
      </form>
      <div v-if="uploadProgress > 0 && uploadProgress < 100" class="progress-container">
        <VProgressCircular :model-value="uploadProgress" color="primary" size="100" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineEmits, computed } from 'vue';
import { VBtn, VIcon, VProgressCircular, VTooltip } from 'vuetify/components';
import { usePhotoStore } from '@/stores/photoStore';

const props = defineProps({
  photo: {
    type: Object,
    default: null,
  },
  acceptedFormats: {
    type: String,
    default: 'image/jpeg,image/png,image/gif',
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
});

const emit = defineEmits(['update:photoId', 'update:imageUrl', 'update:photoMarkedForDeletion']);

const imageUrl = ref(props.photo ? props.photo.url : null);
const photoId = ref(props.photo ? props.photo.id : null);
const fileInput = ref(null);
const photoStore = usePhotoStore();

const uploading = ref(false);
const uploadProgress = ref(0);
const photoMarkedForDeletion = ref(false);

watch(() => photoStore.uploadProgress, (newVal) => {
  uploadProgress.value = newVal;
});

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileChange = async (event) => {
  const file = event.target.files[0];
  if (file) {
    const isValid = await validateFile(file);
    if (!isValid) {
      alert('Файл не соответствует требованиям.');
      return;
    }
    if (photoId.value) {
      // Если есть предыдущее фото, помеченное для удаления, сбросим это состояние
      photoMarkedForDeletion.value = false;
      emit('update:photoMarkedForDeletion', false);
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      imageUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
    uploadImage(file);
  }
};

const validateFile = (file) => {
  const isValidFormat = props.acceptedFormats.split(',').includes(file.type);
  const isValidSize = file.size / 1024 <= props.maxFileSizeKB;

  if (!isValidFormat || !isValidSize) {
    return false;
  }

  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      const isValidMaxDimensions = (!props.maxWidth || img.width <= props.maxWidth) && (!props.maxHeight || img.height <= props.maxHeight);
      const isValidMinDimensions = img.width >= props.minWidth && img.height >= props.minHeight;
      resolve(isValidMaxDimensions && isValidMinDimensions);
    };
    img.src = URL.createObjectURL(file);
  });
};

const uploadImage = async (file) => {
  try {
    uploading.value = true;
    const photo = await photoStore.upload(file);
    imageUrl.value = photo.url;
    photoId.value = photo.id;
    uploading.value = false;
    emit('update:photoId', photo.id);
    emit('update:imageUrl', photo.url);
  } catch (error) {
    uploading.value = false;
    console.error('Error uploading image:', error);
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

watch(() => props.photo, (newVal) => {
  imageUrl.value = newVal ? newVal.url : null;
  photoId.value = newVal ? newVal.id : null;
});

const aspectRatioPadding = computed(() => {
  const [width, height] = props.aspectRatio.split(':').map(Number);
  return `${(height / width) * 100}%`;
});
</script>

<style scoped>
.img-wrapper {
  width: 100%;
  position: relative;
  /* padding-top: 75%; */
  padding-top: var(--aspect-ratio-padding);
  border: 1px solid;
  background-color: rgb(var(--v-theme-surface));
  border-color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  background-image: url(data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxOTkxLjI3IDE5MjEuMzMiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO2ZpbGwtb3BhY2l0eTowfS5jbHMtMntmaWxsOnNpbHZlcn08L3N0eWxlPjwvZGVmcz48dGl0bGU+cGxhY2Vob2xkZXIgaWNvbjwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMCAwaDE5OTEuMjd2MTkyMS4zM0gweiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTEyODguNTIgNzg3LjUxbC0zNC4yLTE5OS45LTcwMC4xNSAxMjIuOTMgMTAyLjYxIDU4NC43MSA2OS40OS0xMS43NnY1MC4yNGg3MTAuODNWNzg3LjUxem0tNTYyLjI1IDQzNy4xOGwtMjIuNDUgNC4zMS04My4zOC00NzEuNCA1ODYuODQtMTAzLjY5IDIzLjUyIDEzMy42Mkg3MjYuMjd6bTY1My4xMSA1MS4zMUg3ODRWODQ1LjIyaDU5NS40eiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTgyMi40NiAxMjMwLjA4aDUxOC40NHYtMTcwLjIybC0xMDktMTIzLjZhMjggMjggMCAwIDAtNDQuNzQgMy41OGwtOTMuOSAxNDguODJhMTguMjcgMTguMjcgMCAwIDEtMjcuNzMgMy43OGwtNjEuMDItNTUuNDRhMjUuMjggMjUuMjggMCAwIDAtMzcuMDcgMy4zN3oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtMiIgY3g9Ijg5Mi4xNiIgY3k9Ijk0OC4yIiByPSI0MC44MSIvPjwvc3ZnPg==);
  background-position: center center;
  background-repeat: no-repeat;
  border-radius: 10px;
  overflow: hidden;
}
.img-wrapper .form {
  position: absolute;
  z-index: 10;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  text-align: center;
}
.img-wrapper .msg {
  padding: 10px;
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
.img-wrapper.with-img img[lazy=loading] {
  opacity:0;
}
.img-wrapper.with-img img[lazy=error] {
  background: #fff4f4;
}
.img-wrapper.with-img img[lazy=loaded] {
  opacity:1;
  transition:all 0.2s;
}
.img-wrapper.with-img .msg {
  opacity: 0;
}
.progress-container {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.upload-btn {
  position: absolute;
  bottom: 10%;
  left: 50%;
  transform: translateX(-50%);
}
.upload-replace-btn {
  position: absolute;
  bottom: 10%;
  left: 50%;
  transform: translateX(-50%);
}
.action-buttons {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  gap: 10px;
}
.info-icon {
  position: absolute;
  top: 10px;
  left: 10px;
  cursor: pointer;
}
.requirements {
  font-size: 0.9em;
  padding:10px;
}
</style>
