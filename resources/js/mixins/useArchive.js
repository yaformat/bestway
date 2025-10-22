// useArchive.js
import { ref } from 'vue'

export default function useArchive() {
  const isTrashedView = ref(false)
  const successDialogVisible = ref(false)
  const restoreSuccessDialogVisible = ref(false)
  const errorDialogVisible = ref(false)
  const itemIdToArchive = ref(null)
  const itemIdToRestore = ref(null)
  
  // Ref для компонента с диалогами
  const dialogsRef = ref(null)

  const toggleTrashedView = (getItems) => {
    isTrashedView.value = !isTrashedView.value
    getItems(isTrashedView.value)
  }

  const confirmArchive = id => {
    itemIdToArchive.value = id
    dialogsRef.value?.showArchiveDialog()
  }

  const confirmRestore = id => {
    itemIdToRestore.value = id
    dialogsRef.value?.showRestoreDialog()
  }

  const archiveItem = async (itemsStore, getItems) => {
    try {
      await itemsStore.delete(itemIdToArchive.value, false)
      getItems(isTrashedView.value)
      //successDialogVisible.value = true
    } catch (error) {
      errorDialogVisible.value = true
      console.error('Failed to archive item:', error)
    }
  }

  const restoreItem = async (itemsStore, getItems) => {
    try {
      await itemsStore.restore(itemIdToRestore.value, false)
      getItems(isTrashedView.value)
      //restoreSuccessDialogVisible.value = true
    } catch (error) {
      errorDialogVisible.value = true
      console.error('Failed to restore item:', error)
    }
  }

  return {
    isTrashedView,
    successDialogVisible,
    restoreSuccessDialogVisible,
    errorDialogVisible,
    dialogsRef,
    toggleTrashedView,
    confirmArchive,
    confirmRestore,
    archiveItem,
    restoreItem
  }
}
