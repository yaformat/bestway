<script setup>
import {
  requiredValidator,
} from '@validators'
import { useResourceStore } from '@/stores/resourceStore'
import { useGlobalDataStore } from '@/stores/globalDataStore'
import { useProductItemStore } from '@/stores/productItemStore'
import { buildCategoryTree, flattenTreeWithIndent } from '@/utils/treeBuilder'
import router from '@/router'
import draggable from 'vuedraggable'

const route = useRoute()

const globalDataStore = useGlobalDataStore()
const resourceStore = useResourceStore()
const productItemStore = useProductItemStore()
const categories = ref([])


const units = ref(globalDataStore.units)
const losses = ref(globalDataStore.resource_losses)

// Иконки для типов потерь
const lossIcons = {
  peeling: 'mdi-knife',
  boiling: 'mdi-pot-steam',
  frying: 'mdi-pan',
  stewing: 'mdi-pot-mix',
  baking: 'mdi-toaster-oven'
};

// Описания для типов потерь
const lossDescriptions = {
  peeling: 'Потери при очистке продукта (удаление кожуры, косточек и т.д.)',
  boiling: 'Потери при варке продукта',
  frying: 'Потери при жарке продукта',
  stewing: 'Потери при тушении продукта',
  baking: 'Потери при запекании продукта'
};

// Функция для получения описания
const getLossDescription = (lossId) => {
  return lossDescriptions[lossId] || 'Потери при обработке';
};

const productItems = ref([])
const selectedProductItems = ref([])
const showProductItemsTable = ref(false)
const searchQuery = ref('')

const allowedTypesForLosses = ['ingredient', 'semi_finished'];

const isResourceAdded = ref(false)
const isFormValid = ref(false)

const resourceData = {
  name: '',
  unit: '',
  unit_alt: '',
  unit_factor: 1,
  notes: '',
  description: '',
  type: null,
  category_id: null,
  category: {id: 0},
  losses: [],
  shelf_life_days: null,
  shelf_life_days_required: false
}
const resourceId = Number(route.params.id);
const resourceDataLocal = ref(structuredClone(resourceData))

console.log('edit resource id: '+route.params.id)

const submitForm = () => {
    onSubmit();
}
const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const dataToSend = { ...resourceDataLocal.value, category_id: resourceDataLocal.value.category.id, productItems: selectedProductItems.value };
      delete dataToSend.category;
      delete dataToSend.tech_cards;
      delete dataToSend.stocks;

      console.log(dataToSend)

      updateResource(dataToSend)
      
    }
  })
}

resourceStore.fetchSingle(resourceId).then(response => {
    console.log('fetch response');
    console.log(response);

    response.category.id = response.category.id ?? 0;
    
    resourceDataLocal.value = response;
    selectedProductItems.value = response.product_items.map(item => ({ ...item, shelf_life_days: (item.shelf_life_days) ?? resourceDataLocal.value.shelf_life_days }));


    categories.value = getCategories(globalDataStore.resource_categories, resourceDataLocal.value.type)


    console.log(resourceDataLocal.value);
    fetchProductItems();

}).catch(error => {
  console.log(error)
})

const fetchProductItems = () => {
  productItemStore.fetchAll().then(response => {
    productItems.value = response.items;
  }).catch(error => {
    console.error(error)
  })
}

const updateResource = resourceData => {
  resourceStore.update(resourceId, resourceData).then(response => {

    isResourceAdded.value = true

    router.replace('/resource/'+resourceData.type);

  }).catch(error => {
    console.error(error)
  })
}

const refForm = ref()

const isAllowedLosses = computed(() => {
  return allowedTypesForLosses.includes(resourceDataLocal.value.type);
});

// Computed property for shelf_life_days validation rules
const shelfLifeDaysRules = computed(() => {
  return isAllowedLosses.value ? [requiredValidator] : [];
});



watch(
  () => globalDataStore.resource_categories,
  (newVal) => {
    categories.value = getCategories(newVal, resourceDataLocal.value.type)
  }
)

watch(
  () => globalDataStore.units,
  (newVal) => {
    units.value = newVal
  }
)

const getCategories = (categories, type = null) => {
  return buildCategoryTree(categories, type)
}

const filteredCategories = computed(() => flattenTreeWithIndent(categories.value))

const attachProductItem = (item) => {
  if (!selectedProductItems.value.find(selectedItem => selectedItem.id === item.id)) {
    selectedProductItems.value.push({ ...item, shelf_life_days: resourceDataLocal.value.shelf_life_days });
  }
};

const detachProductItem = (item) => {
  selectedProductItems.value = selectedProductItems.value.filter(selectedItem => selectedItem.id !== item.id);
};

const toggleProductItemsTable = () => {
  showProductItemsTable.value = !showProductItemsTable.value;
};

const filteredProductItems = computed(() => {
  return productItems.value.filter(item => item.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const isItemAttached = (item) => {
  return selectedProductItems.value.some(selectedItem => selectedItem.id === item.id);
};
</script>

<template>
  <VContainer fluid class="pa-0">
  <VRow>
    <VCol cols="12" md="6">     
      <ImageUploaderEnhanced
        v-model:photoId="resourceDataLocal.photo_id"
        v-model:imageUrl="resourceDataLocal.imagePreview"
        v-model:photoMarkedForDeletion="resourceDataLocal.photoMarkedForDeletion"
        :photo="resourceDataLocal.photo"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VCard title="Обновить ресурс">
        <VCardText>
          <!-- 👉 Form -->
          <VForm 
            class="mt-6"
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >

            <VRow>
              <!-- Name -->
              <VCol
                md="12"
                cols="12"
              >
                <VTextField
                  v-model="resourceDataLocal.name"
                  placeholder="Название"
                  label="Название"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- notes -->
              <VCol
                md="12"
                cols="12"
              >
              <VTextarea
                v-model="resourceDataLocal.notes"
                rows="2"
                label="Описание"
                placeholder="Краткое описание"
              />

              </VCol>

              <!-- 👉 UNIT -->
              <VCol
                md="4"
                cols="6"
              >
                <VSelect
                  v-model="resourceDataLocal.unit"
                  label="Покупаем в "
                  :rules="[requiredValidator]"
                  placeholder="Выбрать единицу"
                  :items="units"
                  item-title="name" item-value="id"
                  :menu-props="{ maxHeight: 200 }"
                />
              </VCol>
              <VCol
                md="4"
                cols="6"
              >
                <VSelect
                  v-model="resourceDataLocal.unit_alt"
                  label="Расходуем в "
                  :rules="[requiredValidator]"
                  placeholder="Выбрать единицу"
                  :items="units"
                  item-title="name" item-value="id"
                  :menu-props="{ maxHeight: 200 }"
                />
              </VCol>
              <VCol
                md="4"
                cols="12"
              >
                <VTextField
                  v-model="resourceDataLocal.unit_factor"
                  placeholder="Переводной коэффициент"
                  label="Переводной коэффициент"
                  type="number"
                  inputmode="numeric"
                  min="0"
                />
              </VCol>

              <!-- 👉 Category -->
              <VCol
                md="12"
                cols="12"
              >
                <VSelect
                  search-input
                  v-model="resourceDataLocal.category.id"
                  label="Категория"
                  :rules="[requiredValidator]"
                  placeholder="Выбрать категорию"
                  :items="filteredCategories"
                  item-title="name" item-value="id"
                  :menu-props="{ maxHeight: 200 }"
                />
              </VCol>

                <VCol
                  md="4"
                  cols="6"
                >
                      <VTextField
                        v-model="resourceDataLocal.shelf_life_days"
                        placeholder="Срок годности"
                        label="Срок годности"
                        type="number"
                        inputmode="numeric"
                        min="0"
                      />
                </VCol>

                <VCol
                  md="6"
                  cols="6"
                >
                    <VCheckbox
                      v-model="resourceDataLocal.shelf_life_days_required" 
                      label="Обязателеное внесение при закупке" 
                    />
                </VCol>


            </VRow>


          </VForm>
        </VCardText>
      </VCard>
    </VCol>
    
    <VCol cols="12" v-if="isAllowedLosses">
      <VCard title="Потери при обработке">
        <VCardText>
          <VRow>
            <VCol
              v-for="loss in resourceDataLocal.losses"
              :key="loss.id"
              cols="12"
              sm="6"
              md="4"
              lg="3"
            >
              <div class="d-flex align-center">
                <VTooltip location="top">
                  <template v-slot:activator="{ props }">
                    <VAvatar
                      v-bind="props"
                      color="primary"
                      variant="tonal"
                      size="36"
                      class="mr-3"
                    >
                      <VIcon :icon="lossIcons[loss.id] || 'mdi-percent'" />
                    </VAvatar>
                  </template>
                  <span>{{ getLossDescription(loss.id) }}</span>
                </VTooltip>
                <VTextField
                  v-model="loss.value"
                  :label="loss.name"
                  type="number"
                  inputmode="numeric"
                  min="0"
                  max="100"
                  density="compact"
                  hide-details
                  suffix="%"
                  class="flex-grow-1"
                />
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard title="Товарный справочник">
        <VCardText>
          <draggable v-model="selectedProductItems" item-key="id" class="d-flex flex-wrap">
            <template #item="{ element }">
              <div class="product-item">
                <VCard class="mb-4" outlined>
                  <VCardText>
                    <ProductItemNameWithImage :item="element" />
                    <VTextField
                      class="mt-5"
                      v-model="element.shelf_life_days"
                      placeholder="Срок годности"
                      label="Срок годности"
                      type="number"
                      inputmode="numeric"
                      min="0"
                    />
                  </VCardText>
                  <VCardActions>
                    <VBtn @click="detachProductItem(element)" color="error">Отвязать</VBtn>
                  </VCardActions>
                </VCard>
              </div>
            </template>
          </draggable>
          <div v-if="!showProductItemsTable">
            <VBtn  @click="toggleProductItemsTable">
              Привязать товары
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VRow v-if="showProductItemsTable">
        <VCol cols="12" class="d-flex justify-end">
          <VBtn icon @click="toggleProductItemsTable">
            <VIcon>mdi-close</VIcon>
          </VBtn>
        </VCol>
        <VCol cols="12" md="12">
          <VTextField
            v-model="searchQuery"
            placeholder="Поиск товаров"
            label="Поиск товаров"
          />
        </VCol>
        <!-- Product Items -->
        <VCol
          cols="12"
          md="12"
        >
          <VTable>
            <thead>
              <tr>
                <th>Название</th>
                <th>Привязан к ресурсу</th>
                <th>Выбрать</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredProductItems" :key="item.id">
                <td>
                  <ProductItemNameWithImage :item="item" />  
                </td>
                <td>
                  <span v-if="item.resource">{{ item.resource.name }}</span>
                  <span v-else>Нет</span>
                </td>
                <td>
                  <VBtn @click="isItemAttached(item) ? detachProductItem(item) : attachProductItem(item)" :disabled="item.resource && item.resource.id != resourceId">
                    {{ isItemAttached(item) ? 'Отвязать' : 'Привязать' }}
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCol>
      </VRow>
    </VCol>

    <VCol cols="12">
      <VRow>
        <!-- 👉 Form Actions -->
        <VCol
          cols="12"
          class="d-flex flex-wrap gap-4 justify-center align-center"
        >
          <VBtn
            @click="submitForm()"
            size="large"
          >
            Сохранить изменения
          </VBtn>

          <VBtn
            color="secondary"
            variant="outlined"
            :to="{ name: 'resource-resourceType', params: { resourceType: resourceDataLocal.type } }"
          >
            Отмена
          </VBtn>
          
        </VCol>
        
      </VRow>
    </VCol>

  </VRow>
  </VContainer>
</template>

<style scoped>
.product-item {
  flex: 1 1 calc(20% - 16px); /* 5 items per row with some gap */
  margin: 8px;
  max-width: calc(20% - 16px);
}
</style>
