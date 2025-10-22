<template>
  <div class="skeleton-loader">
    <div class="skeleton-header">
      <div v-for="header in headers" :key="header.key" class="skeleton-header-cell">
        <div>{{ header.title }}</div>
      </div>
    </div>
    <div 
      v-for="n in rows" 
      :key="n" 
      class="skeleton-row"
      :style="{ height: getRandomRowHeight() }"
    >
      <!-- Первая ячейка с изображением и именем, если нужно -->
      <div v-if="hasNameWithImage" class="skeleton-cell name-with-image">
        <div class="image-placeholder"></div>
        <div class="name-placeholder"></div>
      </div>
      
      <!-- Динамические ячейки с разной шириной -->
      <div 
        v-for="(header, index) in headers.slice(hasNameWithImage ? 1 : 0, -1)" 
        :key="header.key" 
        class="skeleton-cell"
      >
        <div 
          class="dynamic-placeholder" 
          :style="{ 
            width: getRandomWidth(), 
            height: header.height || '20px',
            opacity: 0.7 + (index % 3) * 0.1
          }"
        ></div>
      </div>
      
      <!-- Последняя ячейка с кнопками действий -->
      <div class="skeleton-cell actions-placeholder">
        <div v-for="i in actionButtons" :key="i" class="button-placeholder"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TableSkeletonLoader',
  props: {
    rows: {
      type: Number,
      default: 5
    },
    headers: {
      type: Array,
      default: () => []
    },
    hasNameWithImage: {
      type: Boolean,
      default: false
    },
    actionButtons: {
      type: Number,
      default: 2
    },
    minWidth: {
      type: Number,
      default: 40
    },
    maxWidth: {
      type: Number,
      default: 80
    },
    minRowHeight: {
      type: Number,
      default: 48
    },
    maxRowHeight: {
      type: Number,
      default: 56
    }
  },
  methods: {
    getRandomWidth() {
      const randomWidth = Math.floor(Math.random() * (this.maxWidth - this.minWidth + 1)) + this.minWidth;
      return `${randomWidth}%`;
    },
    getRandomRowHeight() {
      const randomHeight = Math.floor(Math.random() * (this.maxRowHeight - this.minRowHeight + 1)) + this.minRowHeight;
      return `${randomHeight}px`;
    }
  }
};
</script>

<style scoped>
.skeleton-loader {
  display: flex;
  flex-direction: column;
  width: 100%;
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 4px;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
}

.skeleton-header {
  width: 100%;
  display: flex;
  background: rgb(var(--v-table-header-background));
}

.skeleton-header-cell {
  display: flex;
  flex: 1;
  align-items: center;
  padding: 0 16px;
  border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  font-size: 0.75rem;
  font-weight: 500 !important;
  letter-spacing: 0.17px !important;
  text-transform: uppercase !important;
  height: 54px;
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity)) !important;
}

.skeleton-row {
  display: flex;
  width: 100%;
  min-height: 52px;
  align-items: center;
  border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  transition: background-color 0.2s;
}

.skeleton-row:last-child {
  border-bottom: 0 none;
}

.skeleton-row:hover {
  background-color: rgba(var(--v-border-color), 0.08);
}

.skeleton-cell {
  flex: 1;
  padding: 0 16px;
  height: 100%;
  min-width:80px;
  display: flex;
  align-items: center;
}

.name-with-image {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 180px;
}

.image-placeholder {
  width: 40px;
  height: 40px;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 4px;
  animation: pulse 1.5s infinite;
  flex-shrink: 0;
}

.name-placeholder {
  width: 120px;
  height: 20px;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 4px;
  animation: pulse 1.5s infinite;
}

.dynamic-placeholder {
  height: 20px;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 4px;
  animation: pulse 1.5s infinite;
}

.actions-placeholder {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  min-width: 120px;
}

.button-placeholder {
  width: 36px;
  height: 36px;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 4px;
  animation: pulse 1.5s infinite;
}  

@keyframes pulse {
  0% {
    opacity: 0.35;
  }
  50% {
    opacity: 0.9;
  }
  100% {
    opacity: 0.35;
  }
}

/* Добавляем поддержку темной темы */
:deep(.v-theme--dark) {
  .image-placeholder,
  .name-placeholder,
  .dynamic-placeholder,
  .button-placeholder {
    background-color: rgba(var(--v-theme-on-surface), 0.16);
  }
}
</style>
