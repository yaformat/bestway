<template>
  <VCard class="date-navigation mb-4" variant="outlined">
    <VCardText class="pa-3">
      <VRow align="center" no-gutters>
        <!-- Стрелка назад -->
        <VCol cols="auto">
          <VBtn
            icon="mdi-chevron-left"
            variant="text"
            size="small"
            :loading="navigating"
            @click="navigateToPrevious"
          />
        </VCol>

        <!-- Центральная часть с датой -->
        <VCol class="text-center">
          <div class="date-info">
            <div class="date-main">
              {{ formattedDate }}
            </div>
            
            <div class="date-weekday">
              {{ weekday }}
            </div>
          </div>
        </VCol>

        <!-- Стрелка вперед -->
        <VCol cols="auto">
          <VBtn
            icon="mdi-chevron-right"
            variant="text"
            size="small"
            :loading="navigating"
            @click="navigateToNext"
          />
        </VCol>
      </VRow>

      <!-- Индикатор загрузки -->
      <VProgressLinear
        v-if="navigating"
        indeterminate
        color="primary"
        height="2"
        class="mt-2"
      />
    </VCardText>
  </VCard>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  currentDate: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['date-change'])

const navigating = ref(false)

// Вычисляемые свойства
const formattedDate = computed(() => {
  const date = new Date(props.currentDate)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
})

const weekday = computed(() => {
  const date = new Date(props.currentDate)
  return date.toLocaleDateString('ru-RU', {
    weekday: 'long'
  })
})

// Методы навигации
const navigateToPrevious = async () => {
  if (navigating.value) return
  
  navigating.value = true
  try {
    const currentDate = new Date(props.currentDate)
    const previousDate = new Date(currentDate)
    previousDate.setDate(currentDate.getDate() - 1)
    
    const dateString = previousDate.toISOString().split('T')[0]
    emit('date-change', dateString)
  } finally {
    navigating.value = false
  }
}

const navigateToNext = async () => {
  if (navigating.value) return
  
  navigating.value = true
  try {
    const currentDate = new Date(props.currentDate)
    const nextDate = new Date(currentDate)
    nextDate.setDate(currentDate.getDate() + 1)
    
    const dateString = nextDate.toISOString().split('T')[0]
    emit('date-change', dateString)
  } finally {
    navigating.value = false
  }
}
</script>

<style scoped>
.date-navigation {
  position: sticky;
  position: relative;
  top: 0;
  z-index: 10;
  backdrop-filter: blur(10px);
}

.date-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.date-main {
  font-size: 1.125rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
  display: flex;
  align-items: center;
  justify-content: center;
}

.date-weekday {
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  text-transform: capitalize;
}

/* Мобильные стили */
@media (max-width: 600px) {
  .date-main {
    font-size: 1rem;
  }
  
  .date-weekday {
    font-size: 0.8125rem;
  }
}
</style>
