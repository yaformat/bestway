// mixins/useCopyResource.js
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

export function useCopyResource(options = {}) {
  const {
    resourceTypes = [
      { label: 'Ингредиент', value: 'ingredient' },
      { label: 'Полуфабрикат', value: 'semi_finished' },
      { label: 'Блюдо', value: 'dish' },
      { label: 'Хозяйственный', value: 'household' },
      { label: 'Разное', value: 'misc' }
    ],
    routeName = 'resource-resourceType-edit-id'
  } = options

  const router = useRouter()

  // Состояние диалогов
  const showCopyDialog = ref(false)
  const showCopySuccessDialog = ref(false)
  const copyLoading = ref(false)
  const copyFormRef = ref(null)

  // Данные формы копирования
  const copyFormData = reactive({
    name: '',
    type: ''
  })

  // Копируемый ресурс и новый ID
  const copyingResource = ref(null)
  const newResourceId = ref(null)

  // Правила валидации
  const copyFormRules = {
    name: [v => !!v || 'Название обязательно'],
    type: [v => !!v || 'Тип обязателен']
  }

  // Открытие диалога копирования
  const openCopyDialog = (resource) => {
    console.log('Открытие диалога копирования для ресурса:', resource)
    
    copyingResource.value = resource
    copyFormData.name = `${resource.name} (копия)`
    copyFormData.type = resource.type
    
    console.log('Данные формы после заполнения:', copyFormData)
    
    showCopyDialog.value = true
  }

  // Закрытие диалога копирования
  const closeCopyDialog = () => {
    showCopyDialog.value = false
    copyingResource.value = null
    
    copyFormData.name = ''
    copyFormData.type = ''
    
    if (copyFormRef.value) {
      copyFormRef.value.reset()
    }
  }

  // Подтверждение копирования
  const confirmCopy = async (store, onSuccess = null) => {
    if (!copyingResource.value) return
    
    copyLoading.value = true
    
    try {
      const copyData = {
        original_id: copyingResource.value.id,
        name: copyFormData.name,
        type: copyFormData.type
      }
      
      console.log('Отправка данных для копирования:', copyData)
      
      const response = await store.copy(copyData)
      newResourceId.value = response.id
      
      showCopyDialog.value = false
      showCopySuccessDialog.value = true
      
      // Вызываем callback для обновления списка
      if (onSuccess) {
        onSuccess()
      }
      
    } catch (error) {
      console.error('Ошибка при копировании ресурса:', error)
      // Здесь можно добавить показ toast с ошибкой
    } finally {
      copyLoading.value = false
    }
  }

  // Переход к новому ресурсу
  const goToNewResource = () => {
    showCopySuccessDialog.value = false
    if (newResourceId.value) {
      router.push({
        name: routeName,
        params: {
          resourceType: copyFormData.type,
          id: newResourceId.value
        }
      })
    }
  }

  // Закрытие диалога успеха без перехода
  const closeCopySuccessDialog = () => {
    showCopySuccessDialog.value = false
    newResourceId.value = null
  }

  return {
    // Состояние
    showCopyDialog,
    showCopySuccessDialog,
    copyLoading,
    copyFormRef,
    copyFormData,
    copyingResource,
    newResourceId,

    // Данные
    resourceTypes,
    copyFormRules,

    // Методы
    openCopyDialog,
    closeCopyDialog,
    confirmCopy,
    goToNewResource,
    closeCopySuccessDialog
  }
}
