<template>
  <div class="video-uploader">
    <div class="video-wrapper" :class="{ 'with-video': videoUrl && !videoMarkedForDeletion }">
      <video v-if="videoUrl && !videoMarkedForDeletion" controls>
        <source :src="videoUrl" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      <form class="form d-flex flex-column justify-center gap-4">
        <div class="msg v-card-title">Загрузить видео</div>
        <div v-if="!uploading" class="d-flex flex-wrap justify-center gap-4">
          <VBtn v-if="!videoId || videoMarkedForDeletion" color="primary" @click="triggerFileInput" class="upload-btn">
            <VIcon icon="mdi-cloud-upload-outline" class="d-sm-none" />
            <span class="d-none d-sm-block">Загрузить видео</span>
          </VBtn>
          <VBtn v-if="videoId && !videoMarkedForDeletion" color="primary" @click="triggerFileInput" class="upload-replace-btn">
            <VIcon icon="mdi-cloud-upload-outline" class="d-sm-none" />
            <span class="d-none d-sm-block">Заменить видео</span>
          </VBtn>
          <div v-if="videoId" class="action-buttons">
            <VBtn v-if="!videoMarkedForDeletion" type="reset" color="error" @click="markForDeletion">
              <VIcon icon="mdi-delete" />
            </VBtn>
            <VBtn v-if="videoMarkedForDeletion" type="reset" color="primary" @click="undoDeletion">
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
            <div>Допустимые форматы: {{ acceptedFormats.replace(/video\//g, '') }}</div>
            <div>Максимальный размер файла: {{ maxFileSizeKB }} KB</div>
            <div v-if="maxWidth && maxHeight">Максимальные размеры видео: {{ maxWidth }}x{{ maxHeight }} пикселей</div>
            <div>Минимальные размеры видео: {{ minWidth }}x{{ minHeight }} пикселей</div>
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
import { ref, watch, defineEmits } from 'vue';
import { VBtn, VIcon, VProgressCircular, VTooltip } from 'vuetify/components';
import { useVideoStore } from '@/stores/videoStore';

const props = defineProps({
  video: {
    type: Object,
    default: null,
  },
  acceptedFormats: {
    type: String,
    default: 'video/mp4',
  },
  maxFileSizeKB: {
    type: Number,
    default: 10240, // 10 MB
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
});

const emit = defineEmits(['update:videoId', 'update:videoUrl', 'update:videoMarkedForDeletion']);

const videoUrl = ref(props.video ? props.video.url : null);
const videoId = ref(props.video ? props.video.id : null);
const fileInput = ref(null);
const videoStore = useVideoStore();

const uploading = ref(false);
const uploadProgress = ref(0);
const videoMarkedForDeletion = ref(false);

watch(() => videoStore.uploadProgress, (newVal) => {
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
    if (videoId.value) {
      // Если есть предыдущее видео, помеченное для удаления, сбросим это состояние
      videoMarkedForDeletion.value = false;
      emit('update:videoMarkedForDeletion', false);
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      videoUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
    uploadVideo(file);
  }
};

const validateFile = (file) => {
  const isValidFormat = props.acceptedFormats.split(',').includes(file.type);
  const isValidSize = file.size / 1024 <= props.maxFileSizeKB;

  if (!isValidFormat || !isValidSize) {
    return false;
  }

  return new Promise((resolve) => {
    const video = document.createElement('video');
    video.onloadedmetadata = () => {
      const isValidMaxDimensions = (!props.maxWidth || video.videoWidth <= props.maxWidth) && (!props.maxHeight || video.videoHeight <= props.maxHeight);
      const isValidMinDimensions = video.videoWidth >= props.minWidth && video.videoHeight >= props.minHeight;
      resolve(isValidMaxDimensions && isValidMinDimensions);
    };
    video.src = URL.createObjectURL(file);
  });
};

const uploadVideo = async (file) => {
  try {
    uploading.value = true;
    const video = await videoStore.upload(file);
    videoUrl.value = video.url;
    videoId.value = video.id;
    uploading.value = false;
    emit('update:videoId', video.id);
    emit('update:videoUrl', video.url);
  } catch (error) {
    uploading.value = false;
    console.error('Error uploading video:', error);
  }
};

const markForDeletion = () => {
  videoMarkedForDeletion.value = true;
  emit('update:videoMarkedForDeletion', true);
};

const undoDeletion = () => {
  videoMarkedForDeletion.value = false;
  emit('update:videoMarkedForDeletion', false);
};

const removeVideo = async () => {
  if (!videoId.value) return;

  try {
    await videoStore.delete(videoId.value);
    videoUrl.value = null;
    videoId.value = null;
    emit('update:videoId', null);
    emit('update:videoUrl', null);
  } catch (error) {
    console.error('Error deleting video:', error);
  }
};

watch(() => props.video, (newVal) => {
  videoUrl.value = newVal ? newVal.url : null;
  videoId.value = newVal ? newVal.id : null;
});
</script>

<style scoped>
.video-wrapper {
  width: 100%;
  position: relative;
  padding-top: 56.25%; /* 16:9 aspect ratio */
  background: #eee;
  border-radius: 10px;
  overflow: hidden;
}
.video-wrapper .form {
  position: absolute;
  z-index: 10;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  text-align: center;
}
.video-wrapper .msg {
  padding: 10px;
}
.video-wrapper video {
  z-index: 0;
  opacity: 0;
  transition: 0.3s all;
  max-width: 100%;
  display: block;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.video-wrapper.with-video video {
  opacity: 1;
}
.video-wrapper.with-video .msg {
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
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
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
