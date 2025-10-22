import { ref } from 'vue'

export default function useEntityActions(entityStore, entityName, getItems) {
  const isCreateDialogVisible = ref(false)
  const isEditDialogVisible = ref(false)
  const newEntity = ref({ name: '' })
  const editEntity = ref({ id: null, name: '' })

  const createEntity = () => {
    entityStore.create(newEntity.value).then(() => {
      isCreateDialogVisible.value = false
      getItems()
    }).catch(error => {
      console.error(`Failed to create ${entityName}:`, error)
    })
  }

  const updateEntity = () => {
    entityStore.update(editEntity.value.id, editEntity.value).then(() => {
      isEditDialogVisible.value = false
      getItems()
    }).catch(error => {
      console.error(`Failed to update ${entityName}:`, error)
    })
  }

  const openEditDialog = (item) => {
    console.log('edit dialog opened');
    console.log(item);
    editEntity.value = { ...item.raw ?? item }
    isEditDialogVisible.value = true
  }

  return {
    isCreateDialogVisible,
    isEditDialogVisible,
    newEntity,
    editEntity,
    createEntity,
    updateEntity,
    openEditDialog
  }
}
