<template>
  <div :style="{ width: width + 'px', minWidth: width + 'px', height: width + 'px' }" class="user-avatar">
    <!-- Аватар вне v-badge -->
    <VAvatar
      color="primary"
      variant="tonal"
      class="user-avatar-img"
    >
      <VImg class="user-avatar-img-src" v-if="photo && photo.url" :src="photo.url" />
    </VAvatar>
    
    <!-- Отдельный индикатор статуса -->
    <div 
      v-if="showStatus" 
      class="status-indicator"
      :class="{ 'online': isOnline, 'offline': !isOnline }"
      :style="{
        [statusLocation.split(' ')[0]]: statusOffsetY + 'px',
        [statusLocation.split(' ')[1]]: statusOffsetX + 'px'
      }"
    ></div>
  </div>
</template>

<script setup>
import { VAvatar, VImg } from 'vuetify/components';
import { computed } from 'vue';

const props = defineProps({
  photo: {
    type: Object,
    default: () => null,
  },
  width: {
    type: Number,
    default: 42,
  },
  activityAt: {
    type: [String, Date, null],
    default: null,
  },
  showStatus: {
    type: Boolean,
    default: false,
  },
  onlineThreshold: {
    type: Number,
    default: 5, // Минут
  },
  statusLocation: {
    type: String,
    default: 'bottom right',
  },
  statusOffsetX: {
    type: [Number, String],
    default: 3,
  },
  statusOffsetY: {
    type: [Number, String],
    default: 3,
  },
  // Можно также добавить прямое указание статуса
  forceOnline: {
    type: Boolean,
    default: null, // null означает, что используется activityAt
  }
});

// Определяем, онлайн ли пользователь
const isOnline = computed(() => {
  // Если статус принудительно задан, используем его
  if (props.forceOnline !== null) {
    return props.forceOnline;
  }
  
  // Если нет данных об активности, считаем пользователя оффлайн
  if (!props.activityAt) {
    return false;
  }
  
  // Преобразуем activityAt в объект Date
  const activityDate = new Date(props.activityAt);
  
  // Если дата некорректная, считаем пользователя оффлайн
  if (isNaN(activityDate.getTime())) {
    return false;
  }
  
  // Вычисляем разницу между текущим временем и временем последней активности в минутах
  const now = new Date();
  const diffInMinutes = (now - activityDate) / (1000 * 60);
  
  // Если разница меньше порогового значения, считаем пользователя онлайн
  return diffInMinutes <= props.onlineThreshold;
});
</script>

<style scoped>
.user-avatar {
  position: relative;
  overflow: visible;
  display: inline-block;
}

.user-avatar-img {
  width: 100%;
  height: 100%;
  background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZyBmaWxsPSIjY2NjY2NjIj4KICAgIDxwYXRoIGQ9Ik0gMTAgMTEgQyA0LjA4IDExIDIgMTQgMiAxNiBMIDIgMTkgTCAxOCAxOSBMIDE4IDE2IEMgMTggMTQgMTUuOTIgMTEgMTAgMTEgWiIvPgogICAgPGNpcmNsZSBjeD0iMTAiIGN5PSI1LjUiIHI9IjQuNSIvPgogIDwvZz4KPC9zdmc+);
  background-position: 50% 120%;
  background-repeat: no-repeat;
  background-size: 80%;
  border: 1px solid transparent;
  border-color: rgba(var(--v-border-color), var(--v-border-opacity));
}

/* Кастомный индикатор статуса */
.status-indicator {
  position: absolute;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid white;
  z-index: 1;
}

.status-indicator.online {
  background-color: #4CAF50; /* Зеленый для онлайн */
  background-color:rgb(var(--v-theme-success)) !important;
}

.status-indicator.offline {
  background-color: #9E9E9E; /* Серый для оффлайн */
}
</style>
