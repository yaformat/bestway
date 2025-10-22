<script setup>
import { computed, ref, watch } from 'vue'
import { useGlobalDataStore } from '@/stores/globalDataStore'
import router from '@/router'
import draggable from "vuedraggable";

const requiredRule = value => !!value || 'Поле обязательно для заполнения'
const minValueRule = min => value => value >= min || `Значение должно быть не менее ${min}`

const isSubmitting = ref(false)
const isFormValid = ref(false)
const refForm = ref(null)

const groupsend = ref();
const stepsend = ref();

// Вспомогательные функции
const generateId = () => Math.random().toString(36).substr(2, 9)

// Определяем интерфейс для пропсов на основе ответа API
const props = defineProps({
  data: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      is_active: true,
      name: '',
      description: '',
      output_weight: 0,
      cost_price: {
        raw: 0,
        currency: 'с',
        display: '0 с'
      },
      cooking_time: {
        hours: 0,
        minutes: 0
      },
      ready_time: {
        hours: 0,
        minutes: 0
      },
      categories: [],
      kitchens: [],
      workshop: null,
      photo: null,
      steps: [],
      resource_groups: [],
      equipment: []
    })
  }
})

// Основные сторы
const globalDataStore = useGlobalDataStore()
const techCardStore = useTechCardStore()

// Функции для генерации пустых структур
const generateEmptyGroup = (order = 1, isAdditional = false) => ({
  id: generateId(),
  name: isAdditional ? 'Дополнительные ресурсы' : '',
  sort_order: order,
  is_additional: isAdditional ? 1 : 0,
  opened: isAdditional ? false : true,
  resources: []
})

const generateEmptyStep = (index) => ({
  id: generateId(),
  name: `Шаг ${index + 1}`,
  description: '',
  description_notice: null,
  pre_action_days: 0,
  sort_order: index + 1,
  timer: null,
  photo: null,
  video: null,
  resources: []
})

const generateEmptyFormData = () => {
  return {
    name: '',
    description: '',
    output_weight: 0,
    cooking_time: { hours: 0, minutes: 0 },
    ready_time: { hours: 0, minutes: 0 },
    category_ids: [],
    kitchen_ids: [],
    workshop_id: null,
    photo_id: null,
    resource_groups: [
      generateEmptyGroup(1), // Первая обычная группа
      generateEmptyGroup(2, true) // Дополнительная группа
    ],
    steps: Array.from({ length: 3 }, (_, index) => generateEmptyStep(index)),
    equipment: [],
    // Новый блок для выпуска продукции
    product_output: {
      name: '', // Будет автоматически заполняться из названия техкарты
      resource_type_id: null,
      resource_category_id: null,
      shelf_life_days: null,
      storage_conditions: '',
      photo_id: null // Будет дублироваться из основного фото
    }
  }
}

// Инициализация formData пустым объектом
const formData = ref(generateEmptyFormData())
const initialData = ref(null)

// Функция инициализации начальных данных
const initialSet = () => {
  if (!props.data?.id) {
    formData.value = generateEmptyFormData()
  } else {
    // Сохраняем исходные данные
    initialData.value = JSON.parse(JSON.stringify(props.data))
    
    // Копируем данные из props в formData с правильной структурой
    formData.value = {
      name: props.data?.name || '',
      description: props.data?.description || '',
      output_weight: props.data?.output_weight || 0,
      cooking_time: { ...props.data?.cooking_time } || { hours: 0, minutes: 0 },
      ready_time: { ...props.data?.ready_time } || { hours: 0, minutes: 0 },
      
      // Правильно обрабатываем массивы ID
      category_ids: props.data?.categories?.map(cat => cat.id) || [],
      kitchen_ids: props.data?.kitchens?.map(kitchen => kitchen.id) || [],
      
      // Обрабатываем workshop
      workshop_id: props.data?.workshop?.id || null,
      
      // Обрабатываем фото
      photo_id: props.data?.photo?.id || null,
      
      // Глубокое копирование сложных структур
      resource_groups: props.data?.resource_groups?.map(group => ({
        id: group.id,
        name: group.name,
        sort_order: group.sort_order,
        is_additional: group.is_additional,
        resources: group.resources?.map(resource => ({
          resource_id: resource.resource_id,
          weight_brutto: resource.weight_brutto,
          weight_netto: resource.weight_netto,
          weight_output: resource.weight_output,
          losses: [...(resource.losses || [])],
          resource: { ...resource.resource }
        })) || []
      })) || [],
      
      // Копируем шаги
      steps: props.data?.steps?.map(step => ({
        id: step.id,
        name: step.name,
        description: step.description,
        description_notice: step.description_notice,
        pre_action_days: step.pre_action_days,
        sort_order: step.sort_order,
        timer: step.timer,
        photo: step.photo
      })) || [],
      
      // Копируем оборудование
      equipment: props.data?.equipment?.map(eq => ({
        id: eq.id,
        name: eq.name,
        photo: eq.photo,
        category: eq.category
      })) || [],

      // Новый блок для выпуска продукции
      product_output: {
        name: props.data?.name || '',
        resource_type_id: props.data?.product_output?.resource_type_id || null,
        resource_category_id: props.data?.product_output?.resource_category_id || null,
        shelf_life_days: props.data?.product_output?.shelf_life_days || null,
        storage_conditions: props.data?.product_output?.storage_conditions || '',
        photo_id: props.data?.photo?.id || null
      }

    }
  }
}

const resetForm = () => {
  if (initialData.value) {
    initialSet()
  } else {
    formData.value = generateEmptyFormData()
  }
}

// Следим за изменениями props.data
watch(() => props.data, (newData) => {
  if (newData) {
    // Only initialize the form if formData is empty or this is the first load
    if (!formData.value.name || Object.keys(formData.value).length === 0) {
      initialSet()
      console.log('Initial formData setup:', formData.value)
    }
  }
}, { immediate: true })

// Следим за изменением названия техкарты для автозаполнения
watch(() => formData.value.name, (newName) => {
  formData.value.product_output.name = newName
})

// Следим за изменением фото техкарты для дублирования
watch(() => formData.value.photo_id, (newPhotoId) => {
  formData.value.product_output.photo_id = newPhotoId
})

// Функции для работы с группами ресурсов
// Функция добавления новой группы
const addResourceGroup = (scroll) => {
  const maxSortOrder = Math.max(
    ...regularGroups.value.map(g => g.sort_order),
    -1
  )
  
  const newGroup = generateEmptyGroup(maxSortOrder + 1)
  
  // Вставляем новую группу перед дополнительной
  const additionalIndex = formData.value.resource_groups.findIndex(g => g.is_additional === 1)
  if (additionalIndex !== -1) {
    formData.value.resource_groups.splice(additionalIndex, 0, newGroup)
  } else {
    formData.value.resource_groups.push(newGroup)
  }

  if (scroll) {
    nextTick(() => {
      groupsend.value?.scrollIntoView({ behavior: "smooth" })
    })
  }
}

// Функции для работы с шагами
const addStep = (scroll) => {
  const newStepIndex = formData.value.steps.length
  formData.value.steps.push(generateEmptyStep(newStepIndex))

  if (scroll) {
    nextTick(() => {
      stepsend.value?.scrollIntoView({ behavior: "smooth" })
    })
  }
}

const handleUpdateStep = (stepId, field, value) => {
  console.log('Updating step:', { stepId, field, value });
  
  // Находим индекс шага в массиве
  const stepIndex = formData.value.steps.findIndex(step => step.id === stepId);
  if (stepIndex === -1) {
    console.error('Step not found:', stepId);
    return;
  }
  
if (field === 'all') {
    // Обновляем все поля сразу
    formData.value.steps[stepIndex] = {
      ...formData.value.steps[stepIndex],
      ...value
    };
  } else {
    // Для обратной совместимости оставляем возможность обновлять отдельные поля
    formData.value.steps[stepIndex][field] = value;
  }
  
  console.log('Updated step:', formData.value.steps[stepIndex]);
};

// Данные из глобального стора
const techCardCats = computed(() => globalDataStore.tech_card_categories)
const techCardKitchens = computed(() => globalDataStore.kitchens)
const techCardWorkshops = computed(() => globalDataStore.workshops)

const resourceTypes = computed(() => globalDataStore.resource_types || [])
const resourceCategories = computed(() => globalDataStore.resource_categories || [])

// Фильтрованные категории ресурсов по выбранному типу
const filteredResourceCategories = computed(() => {
  if (!formData.value.product_output.resource_type_id) {
    return []
  }
  
  return resourceCategories.value.filter(category => 
    category.type === formData.value.product_output.resource_type_id
  )
})

// Обработчик изменения типа ресурса
const onResourceTypeChange = () => {
  // Сбрасываем выбранную категорию при смене типа
  formData.value.product_output.resource_category_id = null
}

// Разделяем группы на обычные и дополнительную
const regularGroups = computed(() => {
  return formData.value.resource_groups
    .filter(group => !group.is_additional)
    .sort((a, b) => a.sort_order - b.sort_order)
})

const additionalGroup = computed(() => {
  return formData.value.resource_groups.find(group => group.is_additional === 1)
})

// Функции для перемещения групп
const moveGroupUp = (index) => {
  if (index === 0) return

  console.log('move group up');

  // Получаем отсортированные обычные группы
  const sortedGroups = regularGroups.value
  const currentGroup = sortedGroups[index]
  const prevGroup = sortedGroups[index - 1]
  
  if (currentGroup && prevGroup) {
    // Меняем местами sort_order
    const tempOrder = currentGroup.sort_order
    currentGroup.sort_order = prevGroup.sort_order
    prevGroup.sort_order = tempOrder
  }
}

const moveGroupDown = (index) => {
  if (index >= regularGroups.value.length - 1) return

  console.log('move group down');

  // Получаем отсортированные обычные группы
  const sortedGroups = regularGroups.value
  const currentGroup = sortedGroups[index]
  const nextGroup = sortedGroups[index + 1]
  
  if (currentGroup && nextGroup) {
    // Меняем местами sort_order
    const tempOrder = currentGroup.sort_order
    currentGroup.sort_order = nextGroup.sort_order
    nextGroup.sort_order = tempOrder
  }
}

// Модифицируем обработчики сортировки
const moveEnd = (evt) => {
  // После окончания перетаскивания обновляем порядок только обычных групп
  regularGroups.value.forEach((group, index) => {
    group.sort_order = index
  })
}

// Проверка возможности перемещения
const checkMove = (evt) => {
  // Запрещаем перемещение, если это дополнительная группа
  const draggedGroup = formData.value.resource_groups[evt.draggedContext.index]
  return !draggedGroup.is_additional
}

// Функции для работы с модальными окнами
// Модальное окно ресурсов
const showResourceModal = ref(false)
const resourceModalConfig = ref({
  allowedTypes: [],
  initialSelected: [],
  onSelect: null
})

// Функция для открытия модального окна с разными конфигурациями
const openResourceModal = (config) => {
  resourceModalConfig.value = config
  showResourceModal.value = true
}

// Функция для открытия модального окна ингредиентов (обычная группа)
const openIngredientModal = (groupId) => {
  const group = formData.value.resource_groups.find(g => g.id === groupId)
  openResourceModal({
    allowedTypes: ['ingredient', 'semi_finished'], 
    initialSelected: group ? group.resources.map(r => r.resource_id) : [],
    onSelect: (selectedItems) => handleIngredientsSelected(groupId, selectedItems)
  })
}

// Функция для открытия модального окна доп. ингредиентов
const openAdditionalIngredientModal = (groupId) => {
  const group = formData.value.resource_groups.find(g => g.id === groupId)
  openResourceModal({
    allowedTypes: ['household'], // например
    initialSelected: group ? group.resources.map(r => r.resource_id) : [],
    onSelect: (selectedItems) => handleIngredientsSelected(groupId, selectedItems)
  })
}

// Функция для открытия модального окна оборудования
const openEquipmentModal = () => {
  openResourceModal({
    allowedTypes: ['equipment'],
    initialSelected: formData.value.equipment.map(e => e.id),
    onSelect: handleEquipmentSelected
  })
}


const handleEquipmentSelected = (selectedItems) => {
  // Получаем текущее оборудование
  const currentEquipment = formData.value.equipment || []

  // Создаем новый массив оборудования, сохраняя существующие значения
  formData.value.equipment = selectedItems.map(item => {
    // Ищем оборудование среди существующих
    const existingEquipment = currentEquipment.find(e => e.id === item.id)
    
    if (existingEquipment) {
      // Если оборудование уже существует, сохраняем его значения
      return {
        ...existingEquipment,
        // Обновляем только базовые данные оборудования
        name: item.name,
        category: item.category,
        photo: item.photo
      }
    } else {
      // Если это новое оборудование, создаем новую запись
      return {
        id: item.id,
        name: item.name,
        category: item.category,
        photo: item.photo
      }
    }
  })
}

const handleAdditionalIngredientsSelected = (groupId, selectedItems) => {
  // Логика для дополнительных ингредиентов
}

// Обработчики выбора ресурсов
const handleIngredientsSelected = (groupId, selectedItems) => {
  const groupIndex = formData.value.resource_groups.findIndex(g => g.id === groupId)
  if (groupIndex === -1) return

  // Получаем текущие ресурсы группы
  const currentResources = formData.value.resource_groups[groupIndex].resources || []

  // Создаем новый массив ресурсов, сохраняя существующие значения
  formData.value.resource_groups[groupIndex].resources = selectedItems.map(item => {
    // Ищем ресурс среди существующих
    const existingResource = currentResources.find(r => r.resource_id === item.id)
    
    if (existingResource) {
      // Если ресурс уже существует, сохраняем его значения
      return {
        ...existingResource,
        resource: item // Обновляем только данные самого ресурса
      }
    } else {
      // Если это новый ресурс, создаем его с начальными значениями
      return {
        resource_id: item.id,
        weight_brutto: 0,
        weight_netto: 0,
        weight_output: 0,
        losses: [],
        resource: item
      }
    }
  })
}

const calculateResourceWeights = (resource, changedField) => {
  // Разделяем потери на очистку и остальные
  let peelingLoss = 0;
  let otherLosses = 0;
  
  if (resource.losses) {
    resource.losses.forEach(loss => {
      const lossValue = parseFloat(loss.value) || 0;
      if (loss.id === 'peeling') {
        peelingLoss = lossValue;
      } else {
        otherLosses += lossValue;
      }
    });
  }
  
  // Рассчитываем множители потерь
  const peelingMultiplier = (100 - peelingLoss) / 100;
  const otherLossesMultiplier = (100 - otherLosses) / 100;
  
  // Если изменился вес брутто
  if (changedField === 'weight_brutto') {
    const weight_brutto = parseFloat(resource.weight_brutto) || 0;
    
    // Рассчитываем вес нетто с учетом только потери "очистка"
    const weight_netto = parseFloat((weight_brutto * peelingMultiplier).toFixed(2));
    
    // Рассчитываем выход с учетом остальных потерь
    const weight_output = parseFloat((weight_netto * otherLossesMultiplier).toFixed(2));
    
    return {
      weight_brutto,
      weight_netto,
      weight_output
    };
  }
  
  // Если изменился вес нетто
  if (changedField === 'weight_netto') {
    const weight_netto = parseFloat(resource.weight_netto) || 0;
    
    // Рассчитываем вес брутто с учетом только потери "очистка"
    const weight_brutto = parseFloat((weight_netto / peelingMultiplier).toFixed(2));
    
    // Рассчитываем выход с учетом остальных потерь
    const weight_output = parseFloat((weight_netto * otherLossesMultiplier).toFixed(2));
    
    return {
      weight_brutto,
      weight_netto,
      weight_output
    };
  }
  
  // Если изменился вес выхода (закомментировано на будущее)
  if (changedField === 'weight_output') {
    /* 
    const weight_output = parseFloat(resource.weight_output) || 0;
    
    // Рассчитываем вес нетто с учетом остальных потерь
    const weight_netto = parseFloat((weight_output / otherLossesMultiplier).toFixed(2));
    
    // Рассчитываем вес брутто с учетом потери "очистка"
    const weight_brutto = parseFloat((weight_netto / peelingMultiplier).toFixed(2));
    
    return {
      weight_brutto,
      weight_netto,
      weight_output
    };
    */
    return null;
  }
  
  return null;
};

const handleUpdateResourceWeight = (groupId, resourceIndex, field, value) => {
  const groupIndex = formData.value.resource_groups.findIndex(g => g.id === groupId);
  if (groupIndex === -1) return;
  
  const resource = formData.value.resource_groups[groupIndex].resources[resourceIndex];
  if (!resource) return;
  
  // Обновляем значение
  resource[field] = parseFloat(value) || 0;
  
  // Пересчитываем веса только при изменении брутто или нетто
  if (field === 'weight_brutto' || field === 'weight_netto') {
    const weights = calculateResourceWeights(resource, field);
    if (weights) {
      resource.weight_brutto = weights.weight_brutto;
      resource.weight_netto = weights.weight_netto;
      resource.weight_output = weights.weight_output;
    }
  }
  // Опционально для будущего: пересчет при изменении выхода
  /* else if (field === 'weight_output') {
    const weights = calculateResourceWeights(resource, field);
    if (weights) {
      resource.weight_brutto = weights.weight_brutto;
      resource.weight_netto = weights.weight_netto;
    }
  } */
};


const handleUpdateResourceLosses = (groupId, resourceIndex, losses) => {
  const groupIndex = formData.value.resource_groups.findIndex(g => g.id === groupId);
  if (groupIndex === -1) return;
  
  const resource = formData.value.resource_groups[groupIndex].resources[resourceIndex];
  if (!resource) return;
  
  // Сохраняем текущее значение брутто
  const currentBrutto = parseFloat(resource.weight_brutto) || 0;
  
  // Обновляем потери для ресурса
  resource.losses = losses;
  
  // Пересчитываем веса на основе текущего брутто
  const weights = calculateResourceWeights(resource, 'weight_brutto');
  if (weights) {
    // Обновляем только нетто и выход, сохраняя брутто
    resource.weight_netto = weights.weight_netto;
    resource.weight_output = weights.weight_output;
  }
};


// Навигация по вкладкам
const tab = ref(0)
const tabs = [
  { icon: 'mdi-silverware-fork-knife', title: 'Описание блюда' },
  { icon: 'mdi-food-apple', title: 'Состав' },
  { icon: 'mdi-pot-mix', title: 'Приготовление' },
  { icon: 'mdi-tools', title: 'Оборудование' },
  { icon: 'mdi-package-variant', title: 'Выпуск продукции' },
]

// Заголовок формы
const title = computed(() => props.data.id ? 'Редактирование тех. карты' : 'Создание тех. карты')
const btnLabel = computed(() => props.data.id ? 'Обновить тех. карту' : 'Создать тех. карту')

// Функция для подготовки данных к отправке
const prepareDataForSubmit = () => {
  // Создаем копию данных формы
  const dataToSend = { ...formData.value }
  
  // Если это обновление, добавляем ID
  if (props.data.id) {
    dataToSend.id = props.data.id
  }
  
  return dataToSend
}

const submitForm = () => {
    onSubmit();
}
const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      isSubmitting.value = true;

      const dataToSend = { ...formData.value };
      let id = null;
      // Если это обновление, добавляем ID
      if (props.data.id) {
        dataToSend.id = id = props.data.id
      }
      //delete dataToSend.category;

      console.log('Отправляемые данные:', dataToSend)

      const action = id ? techCardStore.update(id, dataToSend) : techCardStore.create(dataToSend)

      action
        .then(response => {
          console.log('Успешно сохранено:', response)
          // Если это создание, можно перенаправить на страницу редактирования
          if (!id && response.id) {
            // Здесь можно добавить перенаправление на страницу редактирования
            router.push({ name: 'techcard-edit', params: { id: response.id } })
          } else {
            router.replace('/techcard/');
          }
        })
        .catch(error => {
          console.error('Ошибка при сохранении:', error)
        })
        .finally(() => {
          isSubmitting.value = false
        })
      
    }
  })
}


// В родительском компоненте, где отображаются все группы

// Вычисляем тоталы для всех групп
const totalsByGroup = computed(() => {
  return formData.value.resource_groups.map(group => {
    if (!group.resources || group.resources.length === 0) {
      return { id: group.id, brutto: 0, netto: 0, output: 0, cost: 0 };
    }
    
    return {
      id: group.id,
      brutto: group.resources.reduce((sum, resource) => sum + (parseFloat(resource.weight_brutto) || 0), 0),
      netto: group.resources.reduce((sum, resource) => sum + (parseFloat(resource.weight_netto) || 0), 0),
      output: group.resources.reduce((sum, resource) => sum + (parseFloat(resource.weight_output) || 0), 0),
      cost: group.resources.reduce((sum, resource) => {
        let cost = 0;
        if (resource.resource && resource.resource.use_price) {
          const price = resource.resource.use_price.raw || 0;
          const weight = parseFloat(resource.weight_brutto) || 0;
          
          cost = resource.resource.unit === 'kilogram' 
            ? price * weight / 1000 
            : price * weight;
        }
        return sum + cost;
      }, 0)
    };
  });
});

// Вычисляем общие тоталы по всей техкарте
const techCardTotals = computed(() => {
  return totalsByGroup.value.reduce((totals, groupTotal) => {
    return {
      brutto: totals.brutto + groupTotal.brutto,
      netto: totals.netto + groupTotal.netto,
      output: totals.output + groupTotal.output,
      cost: totals.cost + groupTotal.cost
    };
  }, { brutto: 0, netto: 0, output: 0, cost: 0 });
});

// Отдельно вычисляем тоталы для обычных групп и дополнительной группы
const regularGroupsTotals = computed(() => {
  const regularGroups = formData.value.resource_groups.filter(g => !g.additional);
  
  return regularGroups.reduce((totals, group) => {
    const groupTotal = totalsByGroup.value.find(t => t.id === group.id) || 
                      { brutto: 0, netto: 0, output: 0, cost: 0 };
    
    return {
      brutto: totals.brutto + groupTotal.brutto,
      netto: totals.netto + groupTotal.netto,
      output: totals.output + groupTotal.output,
      cost: totals.cost + groupTotal.cost
    };
  }, { brutto: 0, netto: 0, output: 0, cost: 0 });
});

const additionalGroupTotals = computed(() => {
  const additionalGroup = formData.value.resource_groups.find(g => g.additional === 1);
  if (!additionalGroup) return { brutto: 0, netto: 0, output: 0, cost: 0 };
  
  const groupTotal = totalsByGroup.value.find(t => t.id === additionalGroup.id) || 
                    { brutto: 0, netto: 0, output: 0, cost: 0 };
  
  return groupTotal;
});



// Refs для диалогов
const confirmDeleteGroup = ref(null);
const confirmDeleteResource = ref(null);
const confirmDeleteStep = ref(null);

// Временные переменные для хранения элементов на удаление
let groupToDelete = null;
let resourceToDelete = null;
let stepToDelete = null;

// Модифицированные методы удаления с подтверждением
const removeResourceGroup = (groupId) => {
  groupToDelete = groupId;
  confirmDeleteGroup.value.open();
};

const removeResource = (groupId, resourceIndex) => {
  resourceToDelete = { groupId, resourceIndex };
  confirmDeleteResource.value.open();
};

const removeStep = (stepId) => {
  stepToDelete = stepId;
  confirmDeleteStep.value.open();
};

// Обработчики подтверждения удаления
const handleGroupDeleteConfirmed = () => {
  if (!groupToDelete) return;

  const groupIndex = formData.value.resource_groups.findIndex(g => g.id === groupToDelete);
  if (groupIndex !== -1) {
    formData.value.resource_groups.splice(groupIndex, 1);
  }

  groupToDelete = null;
};

const handleResourceDeleteConfirmed = () => {
  if (!resourceToDelete) return;

  const { groupId, resourceIndex } = resourceToDelete;
  const groupIndex = formData.value.resource_groups.findIndex(g => g.id === groupId);
  if (groupIndex !== -1) {
    formData.value.resource_groups[groupIndex].resources.splice(resourceIndex, 1);
  }

  resourceToDelete = null;
};

const handleStepDeleteConfirmed = () => {
  if (!stepToDelete) return;

  const stepIndex = formData.value.steps.findIndex(step => step.id === stepToDelete);
  if (stepIndex !== -1) {
    formData.value.steps.splice(stepIndex, 1);
    
    // Обновляем порядковые номера оставшихся шагов
    formData.value.steps.forEach((step, idx) => {
      step.sort_order = idx + 1;
      step.name = `Шаг ${idx + 1}`;
    });
  }

  stepToDelete = null;
};

</script>


<template>
  <section>
    <VForm 
      ref="refForm"
      v-model="isFormValid"
      @submit.prevent="onSubmit"
      class="pb-5"
    >
    <VCard :title="title">

    <StickyTabs :model-value="tab" @update:model-value="newTab => tab = newTab" :tabs="tabs" />

    <VCardText class="">
      <VWindow v-model="tab" class="disable-tab-transition" :touch="false">
        <VWindowItem>
          <VRow>
              <VCol cols="12" md="6">
                <ImageUploaderEnhanced 
                  v-model:photoId="formData.photo_id" 
                  v-model:imageUrl="props.data.imagePreview" 
                  :photo="props.data.photo" 
                  v-model:photoMarkedForDeletion="formData.photoMarkedForDeletion"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField class="mb-5"
                  v-model="formData.name"  
                  :rules="[requiredRule]"

                  label="Название"
                  placeholder=""
                />

                <VTextarea class="mb-5"
                  v-model="formData.description"
                  label="Описание"
                  placeholder="Описание"
                  :rows="4"
                  auto-grow
                />

                <VSelect class="mb-5"
                    v-model="formData.kitchen_ids"
                    label="Вид кухни"
                    :rules="[]"
                    placeholder="Выбрать вид кухни"
                    multiple="true"
                    clearable="true"
                    :items="techCardKitchens"
                    item-title="name" item-value="id"
                    :menu-props="{ maxHeight: 200 }"
                  />

                <VSelect class="mb-5"
                    v-model="formData.category_ids"
                    label="Категория"
                    :rules="[]"
                    placeholder="Выбрать категорию"
                    multiple="true"
                    clearable="true"
                    :items="techCardCats"
                    item-title="name" item-value="id"
                    :menu-props="{ maxHeight: 200 }"
                  />

                <VSelect class="mb-5"
                    v-model="formData.workshop_id"
                    label="Цех"
                    :rules="[]"
                    placeholder="Выбрать цех"
                    clearable="true"
                    :items="techCardWorkshops"
                    item-title="name" item-value="id"
                    :menu-props="{ maxHeight: 200 }"
                  />


                  <VDivider class="my-3" />

                  <h4 class="mb-2"><strong>Время приготовления</strong></h4>

                  <VRow>
                    <VCol cols="12" md="6">

                      <h6 class="my-2">
                        Общее время приготовления:
                      </h6>
                      <VRow>

                          <VCol cols="6">
                            <VTextField
                              v-model="formData.cooking_time.hours"
                              label="Часы"
                              placeholder="00"
                              type="number"
                              inputmode="numeric"
                              min="0"
                            />
                          </VCol>
                          
                          <VCol cols="6">
                            <VTextField
                              v-model="formData.cooking_time.minutes"
                              label="Минуты"
                              placeholder="00"
                              type="number"
                              inputmode="numeric"
                              min="0"
                            />
                          </VCol>

                      </VRow>
                    </VCol>
                    
                    <VCol cols="12" md="6">

                      <h6 class="my-2">
                        Активное время приготовления
                      </h6>
                      <VRow>

                          <VCol cols="6">
                            <VTextField
                              v-model="formData.ready_time.hours"
                              label="Часы"
                              placeholder="00"
                              type="number"
                              inputmode="numeric"
                              min="0"
                            />
                          </VCol>
                          
                          <VCol cols="6">
                            <VTextField
                              v-model="formData.ready_time.minutes"
                              label="Минуты"
                              placeholder="00"
                              type="number"
                              inputmode="numeric"
                              min="0"
                            />
                          </VCol>

                      </VRow>

                  </VCol>

                  </VRow>

                </VCol>



              </VRow>




        </VWindowItem>

        <VWindowItem>
          <div ref="groups">

            <!-- Обычные группы -->
            <div v-for="(group, index) in regularGroups" :key="group.id" class="group-item">
              <TechCardResourceGroups
                :index="index"
                :id="group.id"
                :data="group"
                :totals="totalsByGroup.find(t => t.id === group.id)"
                :allowSorting="regularGroups.length > 1"
                @remove-resource-group="removeResourceGroup"
                @remove-resource="removeResource"
                @update-resource-weight="handleUpdateResourceWeight"
                @update-resource-losses="handleUpdateResourceLosses"
                @open-ingredient-modal="openIngredientModal"
              >
                <!-- Добавляем слот для кнопок сортировки -->
                <template #sort-buttons>
                  <div class="sort-buttons">
                    <VBtn
                      icon="mdi-arrow-up"
                      size="small"
                      variant="text"
                      :disabled="index === 0"
                      @click="moveGroupUp(index)"
                    />
                    <VBtn
                      icon="mdi-arrow-down"
                      size="small"
                      variant="text"
                      :disabled="index === regularGroups.length - 1"
                      @click="moveGroupDown(index)"
                    />
                  </div>
                </template>
              </TechCardResourceGroups>
            </div>

            <div ref="groupsend"></div>

            <!-- Кнопка добавления группы -->
            <VRow class="mt-5">
              <VCol cols="12" class="text-center">
                <VBtn
                  class="w-100"
                  variant="tonal"
                  prepend-icon="mdi-plus"
                  @click="addResourceGroup"
                >
                  Добавить группу
                </VBtn>
              </VCol>
            </VRow>


            <!-- Дополнительная группа -->
            <div v-if="additionalGroup" class="mt-5">
              <TechCardAdditionalResourceGroup
                :index="-1"
                :id="additionalGroup.id"
                :data="additionalGroup"
                :totals="additionalGroupTotals"
                :allowSorting="additionalGroup.resources.length > 1"
                @remove-resource="removeResource"
                @update-resource-weight="handleUpdateResourceWeight"
                @open-ingredient-modal="openAdditionalIngredientModal"
              />
            </div>


          </div>
        </VWindowItem>

        <VWindowItem>
          <div ref="steps">
                <draggable
                  v-model="formData.steps"
                  tag="div"
                  class="draggable-list"
                  item-key="id"
                  handle=".step-handle"
                  :animation="200"
                  ghost-class="ghost"
                  chosen-class="chosen"
                  drag-class="drag"
                >
              
                <template #item="{ element, index }">
                  <div class="draggable-item">
                    <TechCardSteps
                      :index="index"
                      :id="element.id"
                      :data="element"
                      :allowSorting="formData.steps.length > 1"
                      :allSteps="formData.steps"
                      @remove-step="removeStep"
                      @edit-step="editStep"
                      @updateStep="handleUpdateStep"
                    />
                  </div>
                </template>
              
              
              </draggable>


              <VRow class="mt-5">

                <VCol cols="12" class="text-center">
                  <VBtn
                    variant="tonal"
                    color="primary"
                    class="w-100"
                    @click="addStep"
                  >
                    <VIcon
                        icon="mdi-plus"
                        class="me-2"
                    />
                    Добавить шаг
                  </VBtn>
                </VCol>

              </VRow>
              <div ref="stepsend"></div>
          </div>
        </VWindowItem>

          <VWindowItem>
            <VRow>
              <VCol cols="12">
                <VCard>
                  <VCardTitle>Оборудование</VCardTitle>
                  <VCardText>
                    <div v-if="formData.equipment.length === 0" class="text-center pa-4">
                      <VIcon icon="mdi-tools" size="48" color="grey-lighten-1" />
                      <div class="text-body-1 mt-2">Оборудование не выбрано</div>
                    </div>
                    <VList v-else>
                      <VListItem v-for="equipment in formData.equipment" :key="equipment.id">

                        <TableNameWithImage :item="equipment" />

                        <template v-slot:append>
                          <VBtn
                            variant="tonal"
                            color="warning"
                            size="small"
                            icon
                            class="me-2"
                            @click="formData.equipment = formData.equipment.filter(e => e.id !== equipment.id)"
                          >
                            <VIcon size="16" icon="mdi-trashcan-outline" />
                          </VBtn>
                        </template>
                      </VListItem>
                    </VList>
                  </VCardText>
                  <VCardActions>
                    <VBtn
                      block
                      color="primary"
                      variant="tonal"
                      prepend-icon="mdi-plus"
                      @click="openEquipmentModal"
                    >
                      Добавить оборудование
                    </VBtn>
                  </VCardActions>
                </VCard>
              </VCol>
            </VRow>
          </VWindowItem>




   </VWindow>


    <VDivider class="my-5" />

    <VRow>


      <VCol cols="12" md="6">
        <div class="tech-card-totals">
          <div>ИТОГО ПО ТЕХКАРТЕ:</div>
          <div>Выход: {{ techCardTotals.output.toFixed(2) }} г</div>
          <div>Себестоимость: {{ techCardTotals.cost.toFixed(2) }} с</div>
        </div>
      </VCol>

      <VCol cols="12" md="6" class="text-center">
          <VBtn
            size="large"
            variant="text"
            @click="resetForm"
          >
            Сброс
          </VBtn>
          <VBtn
            size="large"
            :loading="isSubmitting"
            :disabled="!isFormValid"
            @click="submitForm"
          >
            {{ btnLabel }}
          </VBtn>
      </VCol>  
    </VRow>

    </VCardText>
    </VCard>

  </VForm>

  <AddResourcesModal
    v-model="showResourceModal"
    :allowed-types="resourceModalConfig.allowedTypes"
    :initial-selected="resourceModalConfig.initialSelected"
    @resources-selected="resourceModalConfig.onSelect"
  />

    <!-- Диалоги подтверждения -->
    <ConfirmDialog
      ref="confirmDeleteGroup"
      title="Удаление группы"
      message="Вы действительно хотите удалить эту группу и все её ресурсы?"
      confirm-color="warning"
      @confirm="handleGroupDeleteConfirmed"
    />

    <ConfirmDialog
      ref="confirmDeleteResource"
      title="Удаление ресурса"
      message="Вы действительно хотите удалить этот ресурс?"
      confirm-color="warning"
      @confirm="handleResourceDeleteConfirmed"
    />

    <ConfirmDialog
      ref="confirmDeleteStep"
      title="Удаление шага"
      message="Вы действительно хотите удалить этот шаг?"
      confirm-color="warning"
      @confirm="handleStepDeleteConfirmed"
    />

 </section>
  
</template>

<style>
/*
.selected-ingredient {
  background-color: #e0e0e0;
}


  .list-group {
    list-style:none;
  }
  .group-card {
    padding: 10px 15px;
    border-radius: 10px;
    background: #f8f8f8;
    margin-bottom: 15px;
    box-shadow: 0 0 4px -2px rgba(0, 0, 0, 0.75);
  }
  .group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .step-name-wrapper {
    display:flex;align-items:center;
  }
  .step-img {
    display:block;
    width:50px;
    min-width: 50px;
    height:0;
    padding-top:50px;
    position:relative;
    overflow:hidden;
}
.step-img-fit {
    display:block;
    width:100%;
    height:100%;
    position:absolute;
    top:0;
    left:0;

    background-image: url(data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxOTkxLjI3IDE5MjEuMzMiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO2ZpbGwtb3BhY2l0eTowfS5jbHMtMntmaWxsOnNpbHZlcn08L3N0eWxlPjwvZGVmcz48dGl0bGU+cGxhY2Vob2xkZXIgaWNvbjwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMCAwaDE5OTEuMjd2MTkyMS4zM0gweiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTEyODguNTIgNzg3LjUxbC0zNC4yLTE5OS45LTcwMC4xNSAxMjIuOTMgMTAyLjYxIDU4NC43MSA2OS40OS0xMS43NnY1MC4yNGg3MTAuODNWNzg3LjUxem0tNTYyLjI1IDQzNy4xOGwtMjIuNDUgNC4zMS04My4zOC00NzEuNCA1ODYuODQtMTAzLjY5IDIzLjUyIDEzMy42Mkg3MjYuMjd6bTY1My4xMSA1MS4zMUg3ODRWODQ1LjIyaDU5NS40eiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTgyMi40NiAxMjMwLjA4aDUxOC40NHYtMTcwLjIybC0xMDktMTIzLjZhMjggMjggMCAwIDAtNDQuNzQgMy41OGwtOTMuOSAxNDguODJhMTguMjcgMTguMjcgMCAwIDEtMjcuNzMgMy43OGwtNjEuMDItNTUuNDRhMjUuMjggMjUuMjggMCAwIDAtMzcuMDcgMy4zN3oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtMiIgY3g9Ijg5Mi4xNiIgY3k9Ijk0OC4yIiByPSI0MC44MSIvPjwvc3ZnPg==);
    background-position:center center;
    background-repeat:no-repeat;
  }
  .step-img-fit img {
    object-fit: cover;
    position: relative;
    height: 100%;
    width: 100%;
    z-index:1;
  }
  .step-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .step-descr {
    padding-top:10px;
  }
  .step-name {
    display: inline-block;
    padding: 5px 10px;
    color: #000;
    border-radius: 5px;
    font-size: 1.2em;
    font-weight: 600;
    white-space: nowrap;
  }
  .step-description {
    max-height: 70px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .img-preview-wrapper {

    width: 100%;
    position: relative;
    padding-top: 100%;
    background: #eee;
    border-radius: 10px;
    overflow:hidden;

  }
  .img-preview-wrapper-fit {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    z-index:0;
  }

  .img-preview-wrapper img {
    z-index:0;
    opacity:0;
    transition:0.3s all;
    object-fit: cover;
    position: relative;
    height: 100%;
    width: 100%;
  }

  .img-preview-wrapper .form {
    position: absolute;
    z-index: 10;
    top: 50%;
    left: 50%;
    width: 100%;
    text-align: center;
    transform: translate(-50%,-50%);

  }
  .image-upload-progress {
    position:absolute;opacity:0;z-index:-1;width:100%;}
  .img-preview-wrapper .msg {padding: 10px;}
  .img-preview-wrapper.with-img img {opacity:0.5;}
  .img-preview-wrapper.with-img .msg {opacity:0;}
  .img-preview-wrapper.with-img .buttons {opacity:0;}
  .img-preview-wrapper.with-img .image-upload-progress {opacity:1;z-index:99999;}

  html.moving, html.moving body {overflow:hidden!important}


  .form-padding {
    padding-left:15px!important;
    padding-right:15px!important;
    padding-top:15px!important;
    padding-bottom:15px!important;
  }

  .fixed-footer {
    padding-top:10px;
    padding-bottom:80px;
    width:100%;z-index:100;
    position:absolute!important;
    bottom:0;left:0;
  }


  .sortable-ghost {background:#cecece;}
  .sortable-drag {box-shadow:0 0 10px 0px rgb(0 0 0 / 15%)}


  .tech-card-steps {
  padding: 16px;
}

.steps-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.no-steps {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px;
  background-color: rgba(0, 0, 0, 0.03);
  border-radius: 8px;
  text-align: center;
}

.steps-footer {
  margin-top: 16px;
  display: flex;
  justify-content: center;
}

.sort-buttons {
  display: flex;
  gap: 4px;
}

.sort-buttons .v-btn {
  opacity: 0.7;
}

.sort-buttons .v-btn:hover {
  opacity: 1;
}


.draggable-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.ghost {
  opacity: 0.5;
}

.chosen {
  opacity: 0.8;
}


.product-photo-preview .v-img {
  border-radius: 8px;
}

.product-photo-preview .v-card {
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}
*/
</style>