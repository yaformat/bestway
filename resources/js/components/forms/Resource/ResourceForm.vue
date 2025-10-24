<template>
  <section>
    <VForm 
      ref="refForm"
      v-model="isFormValid"
      @submit.prevent="onSubmit"
      class="pb-5"
    >
      <VCard :title="title">
        
        <!-- Показываем табы только если их больше одного -->
        <div v-if="tabs.length > 1" class="px-4 pt-2">
          <VTabs v-model="tab" color="primary">
            <VTab 
              v-for="(tabItem, index) in tabs" 
              :key="index"
              :prepend-icon="tabItem.icon"
            >
              {{ tabItem.title }}
              <VChip 
                v-if="tabItem.count > 0"
                size="small"
                class="ml-2"
                color="primary"
              >
                {{ tabItem.count }}
              </VChip>
            </VTab>
          </VTabs>
        </div>

        <VCardText>
          <VWindow v-model="tab" class="disable-tab-transition" :touch="false">
            
            <!-- Вкладка: Описание ресурса -->
            <VWindowItem>

              <div class="photo-management">
  <!-- Заголовок -->
  <VCardTitle class="mb-4">
    Управление фотографиями
  </VCardTitle>
  
  <!-- Enhanced ImageUploader для главного фото -->
  <VCard class="mb-6">
    <VCardTitle>Главное фото</VCardTitle>
    <VCardText>
      <ImageUploaderEnhanced
        :photo="mainPhoto"
        @update:photoId="updateMainPhotoId"
        @update:imageUrl="updateMainPhotoUrl"
        :enableCropping="true"
        :aspectRatio="'16:9'"
        :maxWidth="1920"
        :maxHeight="1080"
      />
    </VCardText>
  </VCard>
  
  <!-- GalleryUploader для галереи -->
  <VCard>
    <VCardTitle>Галерея фото</VCardTitle>
    <VCardText>
      <GalleryUploader
        :photos="galleryPhotos"
        @update:photos="updateGalleryPhotos"
        :maxPhotos="20"
        :maxFileSizeKB="5120"
        :maxWidth="1920"
        :maxHeight="1080"
        :compressImages="true"
        :compressQuality="0.85"
      />
    </VCardText>
  </VCard>
  
  <!-- Кнопки действий -->
  <VCardActions class="mt-4">
    <VSpacer />
    <VBtn color="primary" @click="savePhotos" :loading="saving">
      Сохранить фото
    </VBtn>
    <VBtn color="secondary" variant="outlined" @click="cancel">
      Отмена
    </VBtn>
  </VCardActions>
</div>
              <VContainer fluid class="pa-0">
                <VRow>
                  <VCol cols="12" md="6">
                    <ImageUploaderEnhanced
                      v-model:photoId="formData.photo_id"
                      v-model:imageUrl="formData.imagePreview"
                      v-model:photoMarkedForDeletion="formData.photoMarkedForDeletion"
                      :photo="formData.photo"
                    />
                  </VCol>
                  
                  <VCol cols="12" md="6">
                    <VRow>
                      <!-- Name -->
                      <VCol cols="12">
                        <VTextField
                          v-model="formData.name"
                          placeholder="Название"
                          label="Название"
                          :rules="[requiredRule]"
                        />
                      </VCol>

                      <!-- Notes -->
                      <VCol cols="12">
                        <VTextarea
                          v-model="formData.notes"
                          rows="2"
                          label="Описание"
                          placeholder="Краткое описание"
                        />
                      </VCol>

                        <!-- Units -->
                        <VCol cols="4" md="4">
                          <VSelect
                            v-model="formData.unit"
                            label="Покупаем в"
                            :rules="[requiredRule]"
                            placeholder="Выбрать единицу"
                            :items="units"
                            item-title="name" 
                            item-value="id"
                            :menu-props="{ maxHeight: 200 }"
                          />
                        </VCol>
                        <VCol cols="4" md="4">
                          <VSelect
                            v-model="formData.unit_alt"
                            label="Расходуем в"
                            :rules="[requiredRule]"
                            placeholder="Выбрать единицу"
                            :items="units"
                            item-title="name" 
                            item-value="id"
                            :menu-props="{ maxHeight: 200 }"
                          />
                        </VCol>
                        <VCol cols="4" md="4">
                          <VTextField
                            v-model.number="formData.unit_factor"
                            placeholder="Переводной коэффициент"
                            label="Переводной коэффициент"
                            type="number"
                            min="0"
                          />
                        </VCol>

                      <!-- Category -->
                      <VCol cols="12">
                        <VSelect
                          v-model="formData.category.id"
                          label="Категория"
                          :rules="[requiredRule]"
                          placeholder="Выбрать категорию"
                          :items="filteredCategories"
                          item-title="name" 
                          item-value="id"
                          :menu-props="{ maxHeight: 200 }"
                        />
                      </VCol>


                      <template v-if="formData.type === 'dish'">
                      <VCol cols="12">
                        <VSelect class="mb-5"
                            v-model="formData.kitchen_ids"
                            label="Вид кухни"
                            :rules="[]"
                            placeholder="Выбрать вид кухни"
                            multiple="true"
                            clearable="true"
                            :items="kitchens"
                            item-title="name" item-value="id"
                            :menu-props="{ maxHeight: 200 }"
                        />
                      </VCol>
                      </template>
                      <template v-if="formData.type === 'dish' || formData.type === 'semi_finished'">
                      <VCol cols="12">
                        <VSelect class="mb-5"
                            v-model="formData.workshop.id"
                            label="Цех"
                            :rules="[]"
                            placeholder="Выбрать цех"
                            clearable="true"
                            :items="workshops"
                            item-title="name" item-value="id"
                            :menu-props="{ maxHeight: 200 }"
                        />
                      </VCol>
                      </template>

                      <!-- Shelf Life -->
                      <template v-if="formData.type !== 'dish' && formData.type !== 'equipment'">
                        <VCol cols="6">
                          <VTextField
                            v-model.number="formData.shelf_life_days"
                            placeholder="Срок годности"
                            label="Срок годности"
                            type="number"
                            min="0"
                          />
                        </VCol>
                        <VCol cols="6">
                          <VCheckbox
                            v-model="formData.shelf_life_days_required"
                            label="Обязательное внесение при закупке"
                          />
                        </VCol>
                      </template>

                      <!-- Поля специфичные для блюд -->
                      <template v-if="formData.type === 'dish' || formData.type === 'semi_finished'">
                        <VCol cols="12">

                          <h4 class="my-2">
                            Общее время приготовления:
                          </h4>
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
                        
                        <VCol cols="12">

                          <h4 class="my-2">
                            Активное время приготовления
                          </h4>
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
                      </template>
                    </VRow>
                  </VCol>
                </VRow>
              </VContainer>
            </VWindowItem>

            <!-- Вкладка: Потери при обработке -->
            <VWindowItem v-if="formData.type === 'ingredient' || formData.type === 'semi_finished'">
              <ResourceLosses v-model="formData.losses" />
            </VWindowItem>

            <!-- Вкладка: Товарный справочник -->
            <VWindowItem v-if="formData.type !== 'dish' && formData.type !== 'semi_finished'">
              <ProductCatalog
                v-model="selectedProductItems"
                :available-items="productItems"
                :resource-id="formData.id"
                :default-shelf-life="formData.shelf_life_days"
              />
            </VWindowItem>

            <!-- Дополнительные вкладки для блюд -->
            <VWindowItem v-if="formData.type === 'dish' || formData.type === 'semi_finished'">
              <DishIngredients v-model="formData.ingredient_groups" />
            </VWindowItem>

            <VWindowItem v-if="formData.type === 'dish' || formData.type === 'semi_finished'">
              <DishInstructions v-model="formData.instructions" />
            </VWindowItem>

            <VWindowItem v-if="formData.type === 'dish' || formData.type === 'semi_finished'">
                <DishEquipment v-model="formData.equipment" />
            </VWindowItem>

          </VWindow>
        </VCardText>
      </VCard>

      <!-- Form Actions -->
      <VCard class="mt-4">
        <div class="w-100 d-flex justify-space-between align-center px-5 py-5">
          <VBtn
            color="secondary"
            variant="outlined"
            @click="cancelForm"
          >
            Отмена
          </VBtn>
          <VBtn
            @click="submitForm"
            :loading="isSubmitting"
            :disabled="!isFormValid"
            color="primary"
            size="large"
          >
            {{ btnLabel }}
          </VBtn>
        </div>
      </VCard>
    </VForm>
  </section>
</template>

<script setup>
import { computed, ref, reactive, onMounted, watch } from 'vue'
import { useResourceStore } from '@/stores/resourceStore'
import { useGlobalDataStore } from '@/stores/globalDataStore'
import { useProductItemStore } from '@/stores/productItemStore'
import { buildCategoryTree, flattenTreeWithIndent } from '@/utils/treeBuilder'
import { requiredValidator } from '@/@core/utils/validators'
import router from '@/router'


const props = defineProps({
  resourceData: {
    type: Object,
    required: true
  }
})

// Stores
const resourceStore = useResourceStore()
const globalDataStore = useGlobalDataStore()
const productItemStore = useProductItemStore()

// Refs
const refForm = ref(null)
const isFormValid = ref(false)
const isSubmitting = ref(false)
const tab = ref(0)
const categories = ref([])
const kitchens = computed(() => globalDataStore.kitchens || [])
const workshops = computed(() => globalDataStore.workshops || [])
const losses = computed(
  () => globalDataStore.resource_losses?.map(loss => ({
    id: loss.id,
    name: loss.name,
    value: '' // Initialize with default value
  })) || []
)

const selectedProductItems = ref([])
const productItems = ref([])


// Правила валидации
const requiredRule = value => !!value || 'Поле обязательно для заполнения'

// Данные формы
const formData = reactive({
  ...props.resourceData,
  losses: props.resourceData.losses || losses.value,
  cooking_time: props.resourceData.cooking_time || { hours: 0, minutes: 0 },
  ready_time: props.resourceData.ready_time || { hours: 0, minutes: 0 },
  ingredient_groups: props.resourceData.ingredient_groups || [],
  instructions: props.resourceData.instructions || [],
  equipment: props.resourceData.equipment || [],
  category: props.resourceData.category || { id: 0 },
  unit_factor: props.resourceData.unit_factor || 1
})

// Computed properties
const allowedTypesForLosses = ['ingredient', 'semi_finished']
const isAllowedLosses = computed(() => {
  return allowedTypesForLosses.includes(formData.type)
})

const title = computed(() => {
  const resourceTypeName = getResourceTypeName(formData.type)
  return formData.id 
    ? `Редактирование ${resourceTypeName.toLowerCase()}`
    : `Создание ${resourceTypeName.toLowerCase()}`
})

const btnLabel = computed(() => {
  const resourceTypeName = getResourceTypeName(formData.type)
  return formData.id 
    ? `Обновить ${resourceTypeName.toLowerCase()}`
    : `Создать ${resourceTypeName.toLowerCase()}`
})

const tabs = computed(() => {
    const baseTabs = [
        { icon: 'mdi-information', title: 'Описание ресурса', count: 0 }
    ]

    if (formData.type === 'ingredient' || formData.type === 'semi_finished') {
        baseTabs.push({
            icon: 'mdi-percent',
            title: 'Потери',
            count: formData.losses?.filter(loss => loss.value > 0).length || 0
        })
    }

    if (formData.type === 'ingredient') {
        baseTabs.push({
            icon: 'mdi-package-variant',
            title: 'Товары',
            count: selectedProductItems.value?.length || 0
        })
    }

    if (formData.type === 'dish' || formData.type === 'semi_finished') {
        baseTabs.push(
            { 
                icon: 'mdi-food-apple', 
                title: 'Состав', 
                count: formData.ingredient_groups?.reduce((sum, group) => sum + (group.resources?.length || 0), 0) || 0
            },
            { 
                icon: 'mdi-pot-mix', 
                title: 'Приготовление', 
                count: formData.instructions?.length || 0
            },
            {
                icon: 'mdi-tools',
                title: 'Оборудование',
                count: formData.equipment?.length || 0
            }
        )
    }

    return baseTabs
})

const units = computed(() => globalDataStore.units || [])

const getCategories = (categories, type = null) => {
  return buildCategoryTree(categories, type)
}

const filteredCategories = computed(() => flattenTreeWithIndent(categories.value))

// Methods
const getResourceTypeName = (type) => {
  const typeNames = {
    'ingredient': 'Ингредиент',
    'semi_finished': 'Полуфабрикат',
    'dish': 'Блюдо',
    'equipment': 'Оборудование',
    'household': 'Хозяйственный товар'
  }
  return typeNames[type] || 'Ресурс'
}

const fetchProductItems = () => {
  productItemStore.fetchAll().then(response => {
    productItems.value = response.items
  }).catch(error => {
    console.error(error)
  })
}

const submitForm = async () => {
  const { valid } = await refForm.value.validate()
  if (!valid) return

  try {
    isSubmitting.value = true
    
    const dataToSend = { 
      ...formData, 
      category_id: formData.category?.id || formData.category_id,
      workshop_id: formData.workshop?.id || formData.workshop_id,
      productItems: selectedProductItems.value 
    }
    
    // Очищаем ненужные поля
    delete dataToSend.category
    delete dataToSend.tech_cards
    delete dataToSend.stocks

    console.log('Submitting:', dataToSend)

    if (formData.id) {
      await resourceStore.update(formData.id, dataToSend)
    } else {
      await resourceStore.create(dataToSend)
    }

    router.replace(`/resource/${formData.type}`)
    
  } catch (error) {
    console.error('Ошибка сохранения ресурса:', error)
  } finally {
    isSubmitting.value = false
  }
}

const cancelForm = () => {
  router.replace(`/resource/${formData.type}`)
}

const onSubmit = () => {
  submitForm()
}

// Watchers
watch(
  () => globalDataStore.resource_categories,
  (newVal) => {
    categories.value = getCategories(newVal, formData.type)
  }
)

watch(
  () => losses.value,
  (newLosses) => {
    if (!formData.losses || !formData.losses.length) {
      formData.losses = newLosses
    }
  },
  { immediate: true }
)

// Initialization
onMounted(() => {
  categories.value = getCategories(globalDataStore.resource_categories, formData.type)
  
  if (formData.type !== 'dish') {
    fetchProductItems()
    
    if (props.resourceData.product_items) {
      selectedProductItems.value = props.resourceData.product_items.map(item => ({
        ...item,
        shelf_life_days: item.shelf_life_days ?? formData.shelf_life_days
      }))
    }
  }
})


// Данные
const mainPhoto = ref(null);
const galleryPhotos = ref([]);
const saving = ref(false);

// Методы
const updateMainPhotoId = (id) => {
  if (mainPhoto.value) {
    mainPhoto.value.id = id;
  }
};

const updateMainPhotoUrl = (url) => {
  if (mainPhoto.value) {
    mainPhoto.value.url = url;
  } else {
    mainPhoto.value = { id: null, url };
  }
};

const updateGalleryPhotos = (photos) => {
  galleryPhotos.value = photos;
};

const savePhotos = async () => {
  saving.value = true;
  try {
    // Логика сохранения фото
    const allPhotos = [
      mainPhoto.value,
      ...galleryPhotos.value.filter(p => !p.deleted)
    ];
    
    // Отправка на сервер
    console.log('Сохраняем фото:', allPhotos);
    
    // Успешное сохранение
    saving.value = false;
  } catch (error) {
    console.error('Ошибка сохранения:', error);
    saving.value = false;
  }
};

const cancel = () => {
  // Логика отмены
  console.log('Отмена');
};
</script>

<style scoped>
.photo-management {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}
</style>
