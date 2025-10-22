<template>
  <span 
    :class="['clickable-change-value', valueClass]"
    @click="handleClick"
  >
    {{ displayValue }}
  </span>
  
  <!-- Диалог монтируется только если useInlineDialog = true -->
  <ResourceInfoDialog
    v-if="useInlineDialog && dialogMounted"
    ref="resourceDialog"
    :id="item.id"
    :name="item.name"
    :allow-open="true"
    :action-type="actionType"
    :action-id="actionId"
    :stock-id="stockId"
    :show-trigger="false"
  />
</template>

<script setup>
import { computed, ref, nextTick } from 'vue';
import ResourceInfoDialog from '@/components/dialogs/ResourceInfoDialog.vue';

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  actionType: {
    type: String,
    required: false
  },
  actionId: {
    type: Number,
    required: false
  },
  stockId: {
    type: Number,
    required: false
  },
  useInlineDialog: {
    type: Boolean,
    default: false // По умолчанию используем внешний диалог
  }
});

const emit = defineEmits(['click']);

const resourceDialog = ref(null);
const dialogMounted = ref(false);

// Вычисляем отображаемое значение
const displayValue = computed(() => {
  if (!props.item.actual_change) return '0';
  
  const value = props.item.actual_change.raw;
  const display = props.item.actual_change.display;
  
  return value > 0 ? `+${display}` : display;
});

// Определяем класс для цвета
const valueClass = computed(() => {
  if (!props.item.actual_change) return '';
  
  const value = props.item.actual_change.raw;
  
  if (value > 0) return 'value-positive';
  if (value < 0) return 'value-negative';
  return '';
});

// Обработчик клика
const handleClick = () => {
  if (props.useInlineDialog) {
    // Используем встроенный диалог (ленивая загрузка)
    if (!dialogMounted.value) {
      dialogMounted.value = true;
    }
    
    nextTick(() => {
      if (resourceDialog.value) {
        resourceDialog.value.openDialog();
      }
    });
  } else {
    // Эмитим событие для внешнего диалога
    emit('click', props.item);
  }
};
</script>

<style scoped>
.clickable-change-value {
  font-weight: 600;
  font-size: 0.9375rem;
  cursor: pointer;
  padding: 2px 6px;
  border-radius: 4px;
  transition: all 0.2s ease;
  position: relative;
}

.clickable-change-value:hover {
  background: rgba(var(--v-theme-primary), 0.1);
}

.value-positive {
  color: rgb(var(--v-theme-success));
}

.value-negative {
  color: rgb(var(--v-theme-error));
}
</style>
