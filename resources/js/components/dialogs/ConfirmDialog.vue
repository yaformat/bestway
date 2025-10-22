<!-- ConfirmDialog.vue -->
<template>
  <VDialog 
    v-model="dialog"
    scroll-strategy="none"
    max-width="500"
    >
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VBtn
          icon
          variant="outlined"
          :color="iconColor"
          class="my-4"
          style="block-size: 88px; inline-size: 88px; pointer-events: none;"
        >
          <span class="text-5xl">{{ icon }}</span>
        </VBtn>

        <h6 class="text-lg font-weight-medium">
          {{ message }}
        </h6>
      </VCardText>

      <VCardText class="d-flex align-center justify-center gap-2">
        <VBtn
          variant="elevated"
          :color="confirmColor"
          :loading="loading"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </VBtn>

        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleCancel"
        >
          {{ cancelText }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  message: {
    type: String,
    default: 'Вы уверены?'
  },
  confirmText: {
    type: String,
    default: 'Подтвердить'
  },
  cancelText: {
    type: String,
    default: 'Отмена'
  },
  confirmColor: {
    type: String,
    default: 'primary'
  },
  icon: {
    type: String,
    default: '!'
  },
  iconColor: {
    type: String,
    default: 'warning'
  }
});

const emit = defineEmits(['confirm', 'cancel']);

const dialog = ref(false);
const loading = ref(false);

const open = () => {
  dialog.value = true;
};

const close = () => {
  dialog.value = false;
};

const handleConfirm = async () => {
  loading.value = true;
  try {
    await emit('confirm');
  } finally {
    loading.value = false;
    dialog.value = false;
  }
};

const handleCancel = () => {
  emit('cancel');
  dialog.value = false;
};

// Экспортируем методы для использования через ref
defineExpose({
  open,
  close
});
</script>
