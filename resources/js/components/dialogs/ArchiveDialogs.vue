<!-- ArchiveDialogs.vue -->
<template>
  <div>
    <!-- Диалоги подтверждения -->
    <ConfirmDialog
      ref="confirmArchiveDialog"
      message="Вы уверены, что хотите архивировать этот элемент?"
      confirm-text="Архивировать"
      confirm-color="warning"
      @confirm="handleArchiveConfirm"
    />

    <ConfirmDialog
      ref="confirmRestoreDialog"
      message="Вы уверены, что хотите восстановить этот элемент?"
      confirm-text="Восстановить"
      confirm-color="success"
      @confirm="handleRestoreConfirm"
    />

    <!-- Информационные диалоги -->
    <MessageDialog
      :isDialogVisible="successDialogVisible"
      dialogTitle="Элемент архивирован"
      dialogMessage="Элемент был успешно архивирован."
      dialogType="success"
      @update:isDialogVisible="updateSuccessDialogVisible"
    />

    <MessageDialog
      :isDialogVisible="restoreSuccessDialogVisible"
      dialogTitle="Элемент восстановлен"
      dialogMessage="Элемент был успешно восстановлен."
      dialogType="success"
      @update:isDialogVisible="updateRestoreSuccessDialogVisible"
    />

    <MessageDialog
      :isDialogVisible="errorDialogVisible"
      dialogTitle="Ошибка"
      dialogMessage="Произошла ошибка при выполнении действия."
      dialogType="error"
      @update:isDialogVisible="updateErrorDialogVisible"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';

const confirmArchiveDialog = ref(null);
const confirmRestoreDialog = ref(null);

const props = defineProps({
  successDialogVisible: Boolean,
  restoreSuccessDialogVisible: Boolean,
  errorDialogVisible: Boolean,
  onArchive: Function,
  onRestore: Function,
  updateSuccessDialogVisible: Function,
  updateRestoreSuccessDialogVisible: Function,
  updateErrorDialogVisible: Function
});

const handleArchiveConfirm = () => {
  props.onArchive?.();
};

const handleRestoreConfirm = () => {
  props.onRestore?.();
};

// Экспортируем методы для использования в родительском компоненте
defineExpose({
  showArchiveDialog: () => confirmArchiveDialog.value?.open(),
  showRestoreDialog: () => confirmRestoreDialog.value?.open()
});
</script>
