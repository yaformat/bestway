<!-- PhotoDisplay.vue -->
<template>
  <div 
    class="photo-display" 
    :style="{ width: computedWidth, height: computedHeight }"
  >
    <div class="photo-container" :style="containerStyle">
      <!-- Заглушка -->
      <div 
        v-if="!imageLoaded && !imageError" 
        class="photo-placeholder"
      >
        <VIcon 
          :icon="placeholderIcon" 
          :size="iconSize" 
          color="grey-lighten-1"
        />
      </div>

      <!-- Изображение с v-lazy -->
      <img
        v-if="src"
        v-lazy="src"
        :alt="alt"
        class="photo-image"
        @load="handleLoad"
        @error="handleError"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  src: {
    type: String,
    default: ''
  },
  alt: {
    type: String,
    default: 'Изображение'
  },
  width: {
    type: [String, Number],
    default: '100%'
  },
  height: {
    type: [String, Number],
    default: 'auto'
  },
  aspectRatio: {
    type: String,
    default: null // например: '16/9', '1/1', '4/3'
  },
  placeholderIcon: {
    type: String,
    default: 'mdi-image-outline'
  },
  rounded: {
    type: [Boolean, String],
    default: false
  },
  objectFit: {
    type: String,
    default: 'cover',
    validator: (value) => ['cover', 'contain', 'fill', 'none', 'scale-down'].includes(value)
  }
});

const imageLoaded = ref(false);
const imageError = ref(false);

// Вычисляемые свойства
const computedWidth = computed(() => {
  return typeof props.width === 'number' ? `${props.width}px` : props.width;
});

const computedHeight = computed(() => {
  if (props.aspectRatio) {
    return 'auto';
  }
  return typeof props.height === 'number' ? `${props.height}px` : props.height;
});

const containerStyle = computed(() => {
  const style = {};
  
  if (props.aspectRatio) {
    style.aspectRatio = props.aspectRatio;
    style.paddingTop = '0';
  } else if (props.height === 'auto' && !props.aspectRatio) {
    // Если высота auto и нет aspect-ratio, устанавливаем минимальную высоту
    style.minHeight = '100px';
  }
  
  return style;
});

const iconSize = computed(() => {
  const width = typeof props.width === 'number' ? props.width : 100;
  return Math.min(width / 3, 48);
});

// Обработчики событий
const handleLoad = () => {
  imageLoaded.value = true;
  imageError.value = false;
};

const handleError = () => {
  imageError.value = true;
  imageLoaded.value = false;
};

// Сброс состояния при изменении src
watch(() => props.src, () => {
  if (props.src) {
    imageLoaded.value = false;
    imageError.value = false;
  }
}, { immediate: true });
</script>

<style scoped>
.photo-display {
  position: relative;
  overflow: hidden;
}

.photo-container {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f5f5;
  border-radius: v-bind('props.rounded === true ? "8px" : props.rounded || "0"');
  background-image: url(data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxOTkxLjI3IDE5MjEuMzMiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO2ZpbGwtb3BhY2l0eTowfS5jbHMtMntmaWxsOnNpbHZlcn08L3N0eWxlPjwvZGVmcz48dGl0bGU+cGxhY2Vob2xkZXIgaWNvbjwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMCAwaDE5OTEuMjd2MTkyMS4zM0gweiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTEyODguNTIgNzg3LjUxbC0zNC4yLTE5OS45LTcwMC4xNSAxMjIuOTMgMTAyLjYxIDU4NC43MSA2OS40OS0xMS43NnY1MC4yNGg3MTAuODNWNzg3LjUxem0tNTYyLjI1IDQzNy4xOGwtMjIuNDUgNC4zMS04My4zOC00NzEuNCA1ODYuODQtMTAzLjY5IDIzLjUyIDEzMy42Mkg3MjYuMjd6bTY1My4xMSA1MS4zMUg3ODRWODQ1LjIyaDU5NS40eiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTgyMi40NiAxMjMwLjA4aDUxOC40NHYtMTcwLjIybC0xMDktMTIzLjZhMjggMjggMCAwIDAtNDQuNzQgMy41OGwtOTMuOSAxNDguODJhMTguMjcgMTguMjcgMCAwIDEtMjcuNzMgMy43OGwtNjEuMDItNTUuNDRhMjUuMjggMjUuMjggMCAwIDAtMzcuMDcgMy4zN3oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtMiIgY3g9Ijg5Mi4xNiIgY3k9Ijk0OC4yIiByPSI0MC44MSIvPjwvc3ZnPg==);
  background-position: center center;
  background-repeat: no-repeat;
  background-size: 60px 60px;
}

.photo-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
}

.photo-image {
  width: 100%;
  height: 100%;
  object-fit: v-bind('props.objectFit');
  position: absolute;
  top: 0;
  left: 0;
  background: #fff;
}

/* Стили для v-lazy директивы */
.photo-image[lazy=error] {
  background: #fff4f4;
  opacity: 0;
}

.photo-image[lazy=loading] {
  opacity: 0.3;
  animation: pulse 1.5s infinite;
}

.photo-image[lazy=loaded] {
  opacity: 1;
  transition: opacity 0.3s ease;
}

@keyframes pulse {
  0% {
    background-color: #f0f0f0;
  }
  50% {
    background-color: #e0e0e0;
  }
  100% {
    background-color: #f0f0f0;
  }
}
</style>
