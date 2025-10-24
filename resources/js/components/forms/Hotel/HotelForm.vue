<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useHotelStore } from '@/stores/hotelStore';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// Роутер и store
const router = useRouter();
const route = useRoute();
const hotelStore = useHotelStore();

// Рефы
const basicFormRef = ref(null);
const saving = ref(false);
const activeTab = ref('basic');
const referenceData = ref(null);
const referenceDataLoading = ref(false);

// Вычисляемые свойства
const isEditing = computed(() => !!route.params.id);
const isFormValid = computed(() => {
  return formData.value.name && formData.value.direction_id;
});

// Справочники - исправляем обработку данных
const directions = computed(() => referenceData.value?.directions || []);
const resorts = computed(() => referenceData.value?.resorts || []);
const restTypes = computed(() => {
  const types = referenceData.value?.rest_types || {};
  return Object.entries(types).map(([value, name]) => ({ value, name }));
});
const hotelTypes = computed(() => {
  const types = referenceData.value?.hotel_types || {};
  return Object.entries(types).map(([value, name]) => ({ value, name }));
});
const currencyOptions = computed(() => {
  const currencies = referenceData.value?.currencies || {};
  return Object.entries(currencies).map(([value, name]) => ({ value, name }));
});
const infoBlocksConfig = computed(() => {
  const blocks = referenceData.value?.info_blocks || {};
  return Object.entries(blocks).map(([key, block]) => ({ key, ...block }));
});

// Вкладки
const tabs = [
  { key: 'basic', title: 'Основное', icon: 'mdi-domain' },
  { key: 'info-blocks', title: 'Доп.инфо', icon: 'mdi-information' },
  { key: 'rooms', title: 'Номера', icon: 'mdi-bed' },
  { key: 'services', title: 'Услуги', icon: 'mdi-room-service' },
  { key: 'periods', title: 'Периоды брони', icon: 'mdi-calendar' },
  { key: 'prices', title: 'Цены', icon: 'mdi-currency-usd' },
  { key: 'photos', title: 'Фото', icon: 'mdi-image' },
];

// Данные формы
const formData = ref({
  name: '',
  description: '',
  direction_id: null,
  resort_id: null,
  currency: 'USD',
  hotel_type: 'hotel',
  rest_types: [],
  latitude: null,
  longitude: null,
  rating: null,
  sort_order: 0,
  is_active: true,
  // Информационные блоки
  info_blocks: [],
  // Строения
  buildings: [],
  // Номера
  rooms: [],
  // Услуги
  services: [],
  // Периоды бронирования
  booking_periods: [],
  // Фото
  main_photo: null,
  gallery_photos: [],
});

// Правила валидации
const rules = {
  required: (value) => !!value || 'Это поле обязательно',
  email: (value) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(value) || 'Введите корректный email';
  },
};

// Методы
const initializeFormData = () => {
  if (isEditing.value) {
    loadHotel();
  } else {
    addDefaultPeriod();
    initializeInfoBlocks();
  }
};

const loadHotel = async () => {
  try {
    const hotel = await hotelStore.getHotelWithDetails(route.params.id);
    Object.assign(formData.value, hotel);
    
    // Инициализация цен для номеров
    formData.value.rooms.forEach(room => {
      room.prices = {};
      formData.value.booking_periods.forEach(period => {
        const existingPrice = room.prices?.[period.id];
        room.prices[period.id] = existingPrice || { price: 0 };
      });
    });
    
    // Инициализация цен для услуг
    formData.value.services.forEach(service => {
      service.prices = {};
      formData.value.booking_periods.forEach(period => {
        const existingPrice = service.prices?.[period.id];
        service.prices[period.id] = existingPrice || { price: 0 };
      });
    });
  } catch (error) {
    console.error('Ошибка загрузки отеля:', error);
    showNotification('Не удалось загрузить данные отеля', 'error');
  }
};

const loadReferenceData = async () => {
  referenceDataLoading.value = true;
  try {
    const data = await hotelStore.fetchReferenceData(isEditing.value ? route.params.id : null);
    referenceData.value = data;
  } catch (error) {
    console.error('Ошибка загрузки справочников:', error);
    showNotification('Не удалось загрузить справочники', 'error');
  } finally {
    referenceDataLoading.value = false;
  }
};

// Инициализация информационных блоков
const initializeInfoBlocks = () => {
  formData.value.info_blocks = infoBlocksConfig.value.map(block => ({
    key: block.key,
    title: block.title,
    content: '',
    is_active: true,
    sort_order: block.sort_order || 0
  }));
};

// Методы для работы с периодами
const addBookingPeriod = () => {
  const newPeriod = {
    tempId: Date.now() + Math.random(),
    name: '',
    start_date: '',
    end_date: '',
    sort_order: formData.value.booking_periods.length,
    is_active: true,
  };
  formData.value.booking_periods.push(newPeriod);
  
  // Добавляем цены для этого периода во все номера
  formData.value.rooms.forEach(room => {
    room.prices[newPeriod.tempId] = { price: 0 };
  });
  
  // Добавляем цены для этого периода во все услуги
  formData.value.services.forEach(service => {
    service.prices[newPeriod.tempId] = { price: 0 };
  });
};

const removeBookingPeriod = (index) => {
  const period = formData.value.booking_periods[index];
  formData.value.booking_periods.splice(index, 1);
  
  // Удаляем цены для этого периода из всех номеров
  formData.value.rooms.forEach(room => {
    delete room.prices[period.tempId || period.id];
  });
  
  // Удаляем цены для этого периода из всех услуг
  formData.value.services.forEach(service => {
    delete service.prices[period.tempId || period.id];
  });
};

const addDefaultPeriod = () => {
  const currentYear = new Date().getFullYear();
  formData.value.booking_periods = [
    {
      tempId: 'default',
      name: 'Стандартный период',
      start_date: `${currentYear}-01-01`,
      end_date: `${currentYear}-12-31`,
      sort_order: 0,
      is_active: true,
    },
  ];
};

// Методы для работы со строениями
const addBuilding = () => {
  const newBuilding = {
    tempId: Date.now() + Math.random(),
    name: '',
    description: '',
    is_active: true,
    sort_order: formData.value.buildings.length,
    rooms: [],
  };
  formData.value.buildings.push(newBuilding);
};

const removeBuilding = (index) => {
  const building = formData.value.buildings[index];
  formData.value.buildings.splice(index, 1);
  
  // Удаляем номера этого строения
  formData.value.rooms = formData.value.rooms.filter(room => 
    room.hotel_building_id !== (building.tempId || building.id)
  );
};

// Методы для работы с номерами
const addRoom = (buildingId = null) => {
  const newRoom = {
    tempId: Date.now() + Math.random(),
    name: '',
    description: '',
    capacity: 2,
    beds_count: 1,
    hotel_building_id: buildingId,
    is_active: true,
    sort_order: formData.value.rooms.length,
    prices: {},
    photos: [],
    showPhotos: false,
  };
  
  // Инициализация цен для всех периодов
  formData.value.booking_periods.forEach(period => {
    newRoom.prices[period.tempId || period.id] = { price: 0 };
  });
  
  formData.value.rooms.push(newRoom);
};

const removeRoom = (index) => {
  formData.value.rooms.splice(index, 1);
};

// Группировка номеров по строениям
const roomsByBuilding = computed(() => {
  const grouped = {};
  
  // Сначала номера без строения
  grouped.no_building = {
    building: null,
    rooms: formData.value.rooms.filter(room => !room.hotel_building_id)
  };
  
  // Затем номера по строениям
  formData.value.buildings.forEach(building => {
    const buildingId = building.tempId || building.id;
    grouped[buildingId] = {
      building,
      rooms: formData.value.rooms.filter(room => room.hotel_building_id === buildingId)
    };
  });
  
  return grouped;
});

// Методы для работы с услугами
const addService = () => {
  const newService = {
    tempId: Date.now() + Math.random(),
    name: '',
    is_active: true,
    sort_order: formData.value.services.length,
    prices: {},
  };
  
  // Инициализация цен для всех периодов
  formData.value.booking_periods.forEach(period => {
    newService.prices[period.tempId || period.id] = { price: 0 };
  });
  
  formData.value.services.push(newService);
};

const removeService = (index) => {
  formData.value.services.splice(index, 1);
};

// Методы для работы с фото
const updateMainPhotoId = (id) => {
  if (formData.value.main_photo) {
    formData.value.main_photo.id = id;
  } else {
    formData.value.main_photo = { id };
  }
};

const updateMainPhotoUrl = (url) => {
  if (formData.value.main_photo) {
    formData.value.main_photo.url = url;
  } else {
    formData.value.main_photo = { url };
  }
};

const updateGalleryPhotos = (photos) => {
  formData.value.gallery_photos = photos;
};

const updateRoomPhotos = (roomIndex, photos) => {
  formData.value.rooms[roomIndex].photos = photos;
};

// Методы сохранения
const prepareDataForSave = () => {
  const data = { ...formData.value };
  
  // Удаляем временные ID
  data.booking_periods = data.booking_periods.map(period => {
    const { tempId, ...periodData } = period;
    return periodData;
  });
  
  data.rooms = data.rooms.map(room => {
    const { tempId, showPhotos, ...roomData } = room;
    return roomData;
  });
  
  data.services = data.services.map(service => {
    const { tempId, ...serviceData } = service;
    return serviceData;
  });
  
  data.buildings = data.buildings.map(building => {
    const { tempId, ...buildingData } = building;
    return buildingData;
  });
  
  // Подготовка цен для сохранения
  const roomPrices = [];
  data.rooms.forEach(room => {
    Object.entries(room.prices).forEach(([periodId, priceData]) => {
      roomPrices.push({
        hotel_room_id: room.id,
        booking_period_id: periodId,
        price: priceData.price,
        currency: data.currency,
      });
    });
  });
  
  const servicePrices = [];
  data.services.forEach(service => {
    Object.entries(service.prices).forEach(([periodId, priceData]) => {
      servicePrices.push({
        hotel_service_id: service.id,
        booking_period_id: periodId,
        price: priceData.price,
        currency: data.currency,
      });
    });
  });
  
  data.room_prices = roomPrices;
  data.service_prices = servicePrices;
  
  // Фото
  data.main_photo_id = data.main_photo?.id;
  data.gallery_photo_ids = data.gallery_photos
    .filter(p => !p.deleted)
    .map(p => p.id);
  
  return data;
};

const save = async () => {
  if (!isFormValid.value) {
    showNotification('Заполните обязательные поля', 'error');
    activeTab.value = 'basic';
    return;
  }
  
  saving.value = true;
  try {
    const data = prepareDataForSave();
    if (isEditing.value) {
      await hotelStore.update(route.params.id, data);
      showNotification('Отель сохранен', 'success');
    } else {
      await hotelStore.create(data);
      showNotification('Отель создан', 'success');
    }
    router.push('/hotel');
  } catch (error) {
    console.error('Ошибка сохранения:', error);
    showNotification(error.message || 'Ошибка сохранения отеля', 'error');
  } finally {
    saving.value = false;
  }
};

const cancel = () => {
  router.push('/hotel');
};

// Уведомления
const showNotification = (message, type = 'success') => {
  if (window.showNotification) {
    window.showNotification(message, type);
  } else {
    console.log(`${type}: ${message}`);
  }
};

// Наблюдатели
watch(() => formData.value.direction_id, async (newDirectionId) => {
  if (newDirectionId) {
    try {
      const resortData = await hotelStore.fetchResortsByDirection(newDirectionId);
      if (referenceData.value) {
        referenceData.value.resorts = resortData;
      }
    } catch (error) {
      console.error('Ошибка загрузки курортов:', error);
    }
  }
  formData.value.resort_id = null;
});

// Жизненный цикл
onMounted(async () => {
  await loadReferenceData();
  initializeFormData();
});
</script>

<template>
<div class="hotel-form">
  <!-- Заголовок формы -->
  <VCard class="mb-4">
    <VCardTitle class="d-flex align-center justify-space-between">
      <span>{{ isEditing ? 'Редактирование отеля' : 'Создание отеля' }}</span>
      <VChip :color="formData.is_active ? 'success' : 'grey'" size="small">
        {{ formData.is_active ? 'Активен' : 'Неактивен' }}
      </VChip>
    </VCardTitle>
  </VCard>

  <!-- Прелоадер для справочников -->
  <VCard v-if="referenceDataLoading" class="mb-4">
    <VCardText class="text-center py-8">
      <VProgressCircular indeterminate color="primary" size="48" />
      <div class="mt-2">Загрузка справочников...</div>
    </VCardText>
  </VCard>

  <!-- Вкладки -->
  <StickyTabs v-model="activeTab" :tabs="tabs" v-if="!referenceDataLoading" />

  <!-- Содержимое вкладок -->
  <VWindow v-model="activeTab" class="mt-4" v-if="!referenceDataLoading">
    <!-- Основная информация -->
    <VWindowItem value="basic">
      <VCard>
        <VCardTitle>Основная информация</VCardTitle>
        <VCardText>
          <VForm ref="basicFormRef" @submit.prevent>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="formData.name"
                  label="Название отеля*"
                  :rules="[rules.required]"
                  placeholder="Введите название отеля"
                  prepend-inner-icon="mdi-domain"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.direction_id"
                  :items="directions"
                  item-title="name"
                  item-value="value"
                  label="Направление*"
                  :rules="[rules.required]"
                  placeholder="Выберите направление"
                  prepend-inner-icon="mdi-map-marker"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.resort_id"
                  :items="resorts"
                  item-title="name"
                  item-value="value"
                  label="Курорт"
                  placeholder="Выберите курорт (необязательно)"
                  prepend-inner-icon="mdi-beach"
                  clearable
                  :disabled="saving || !formData.direction_id"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.hotel_type"
                  :items="hotelTypes"
                  item-title="name"
                  item-value="value"
                  label="Тип отеля*"
                  :rules="[rules.required]"
                  placeholder="Выберите тип отеля"
                  prepend-inner-icon="mdi-domain"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12">
                <div class="mb-2">
                  <h4>Описание отеля</h4>
                </div>
                <QuillEditor
                  v-model:content="formData.description"
                  contentType="html"
                  theme="snow"
                  :options="{
                    placeholder: 'Введите описание отеля...',
                    modules: {
                      toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'header': [1, 2, 3, false] }],
                        ['link', 'image'],
                        ['clean']
                      ]
                    }
                  }"
                  :disabled="saving"
                  style="height: 300px;"
                  class="mb-4"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.currency"
                  :items="currencyOptions"
                  item-title="name"
                  item-value="value"
                  label="Валюта*"
                  :rules="[rules.required]"
                  placeholder="Выберите валюту"
                  prepend-inner-icon="mdi-currency-usd"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.rest_types"
                  :items="restTypes"
                  item-title="name"
                  item-value="value"
                  label="Виды отдыха"
                  placeholder="Выберите виды отдыха"
                  prepend-inner-icon="mdi-heart"
                  multiple
                  chips
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model.number="formData.latitude"
                  label="Широта"
                  type="number"
                  step="any"
                  placeholder="42.8746"
                  prepend-inner-icon="mdi-latitude"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model.number="formData.longitude"
                  label="Долгота"
                  type="number"
                  step="any"
                  placeholder="74.5698"
                  prepend-inner-icon="mdi-longitude"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model.number="formData.rating"
                  label="Рейтинг"
                  type="number"
                  step="0.1"
                  min="0"
                  max="5"
                  placeholder="4.5"
                  prepend-inner-icon="mdi-star-outline"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model.number="formData.sort_order"
                  label="Порядок сортировки"
                  type="number"
                  placeholder="0"
                  prepend-inner-icon="mdi-sort"
                  :disabled="saving"
                />
              </VCol>
              <VCol cols="12">
                <VSwitch
                  v-model="formData.is_active"
                  label="Отель активен"
                  color="primary"
                  inset
                  :disabled="saving"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Информационные блоки -->
    <VWindowItem value="info-blocks">
      <VCard>
        <VCardTitle>Информационные блоки</VCardTitle>
        <VCardText>
          <VRow v-for="(block, index) in formData.info_blocks" :key="index">
            <VCol cols="12">
              <VCard variant="outlined">
                <VCardText>
                  <div class="d-flex align-center mb-3">
                    <VIcon :icon="infoBlocksConfig.find(b => b.key === block.key)?.icon || 'mdi-information'" class="mr-2" />
                    <h3>{{ block.title }}</h3>
                    <VSpacer />
                    <VSwitch
                      v-model="block.is_active"
                      label="Активен"
                      color="primary"
                      inset
                      :disabled="saving"
                    />
                  </div>
                  <QuillEditor
                    v-model:content="block.content"
                    contentType="html"
                    theme="snow"
                    :options="{
                      placeholder: block.placeholder || 'Введите текст...',
                      modules: {
                        toolbar: [
                          ['bold', 'italic', 'underline'],
                          ['list', 'ordered', 'bullet'],
                          ['link'],
                          ['clean']
                        ]
                      }
                    }"
                    :disabled="saving"
                    style="height: 200px;"
                  />
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Номера -->
    <VWindowItem value="rooms">
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between flex-wrap">
          <span>Номера</span>
          <div class="d-flex gap-2 mt-2 mt-sm-0">
            <VBtn color="secondary" @click="addBuilding" :disabled="saving" variant="outlined">
              <VIcon start icon="mdi-plus" />
              Добавить строение
            </VBtn>
            <VBtn color="primary" @click="addRoom()" :disabled="saving">
              <VIcon start icon="mdi-plus" />
              Добавить номер
            </VBtn>
          </div>
        </VCardTitle>
        <VCardText>
          <!-- Сначала строения без номеров -->
          <VRow v-if="formData.buildings.length === 0 && formData.rooms.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-building" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Нет добавленных строений и номеров</div>
              <VBtn class="mt-4" @click="addBuilding" :disabled="saving">
                Добавить первое строение
              </VBtn>
            </VCol>
          </VRow>

          <!-- Строения с номерами -->
          <template v-for="(group, buildingId) in roomsByBuilding" :key="buildingId">
            <!-- Заголовок строения -->
            <VRow v-if="buildingId !== 'no_building' && group.building" class="mb-4">
              <VCol cols="12">
                <VCard variant="outlined" class="building-header">
                  <VCardText class="pa-3">
                    <div class="d-flex align-center justify-space-between">
                      <div class="d-flex align-center">
                        <VIcon icon="mdi-building" class="mr-2" size="24" />
                        <h3>{{ group.building.name || `Строение ${group.building.tempId}` }}</h3>
                        <VChip size="small" class="ml-2" variant="outlined">
                          {{ group.rooms.length }} номеров
                        </VChip>
                      </div>
                      <div class="d-flex gap-2">
                        <VBtn
                          size="small"
                          color="primary"
                          variant="outlined"
                          @click="addRoom(buildingId)"
                          :disabled="saving"
                        >
                          <VIcon start icon="mdi-plus" />
                          Добавить номер
                        </VBtn>
                        <VBtn
                          size="small"
                          color="error"
                          variant="text"
                          @click="removeBuilding(formData.value.buildings.findIndex(b => (b.tempId || b.id) === buildingId))"
                          :disabled="saving"
                        >
                          <VIcon icon="mdi-delete" />
                        </VBtn>
                      </div>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>

            <!-- Номера без строения -->
            <VRow v-if="buildingId === 'no_building' && group.rooms.length > 0">
              <VCol cols="12">
                <h3 class="mb-3">Номера без строения</h3>
              </VCol>
            </VRow>

            <!-- Номера строения -->
            <VRow v-for="(room, roomIndex) in group.rooms" :key="room.tempId || room.id" class="mb-4">
              <VCol cols="12">
                <VCard variant="outlined" class="room-card">
                  <VCardText>
                    <div class="d-flex justify-space-between align-center mb-4">
                      <h4>{{ room.name || `Номер ${roomIndex + 1}` }}</h4>
                      <VBtn
                        color="error"
                        variant="text"
                        size="small"
                        @click="removeRoom(formData.value.rooms.indexOf(room))"
                        :disabled="saving"
                      >
                        <VIcon icon="mdi-delete" />
                      </VBtn>
                    </div>
                    <VRow>
                      <VCol cols="12" md="4">
                        <VTextField
                          v-model="room.name"
                          label="Название номера*"
                          placeholder="Standard Double"
                          prepend-inner-icon="mdi-label"
                          :disabled="saving"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          v-model.number="room.capacity"
                          label="Вместимость*"
                          type="number"
                          min="1"
                          placeholder="2"
                          prepend-inner-icon="mdi-account-group"
                          :disabled="saving"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          v-model.number="room.beds_count"
                          label="Кровати*"
                          type="number"
                          min="1"
                          placeholder="1"
                          prepend-inner-icon="mdi-bed"
                          :disabled="saving"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          v-model.number="room.sort_order"
                          label="Сортировка"
                          type="number"
                          placeholder="0"
                          prepend-inner-icon="mdi-sort"
                          :disabled="saving"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VSwitch
                          v-model="room.is_active"
                          label="Активен"
                          color="primary"
                          inset
                          :disabled="saving"
                        />
                      </VCol>
                      <VCol cols="12">
                        <div class="d-flex align-center mb-2">
                          <h5>Описание номера</h5>
                          <VSpacer />
                          <VBtn
                            variant="text"
                            size="small"
                            @click="room.showPhotos = !room.showPhotos"
                            :disabled="saving"
                          >
                            <VIcon :icon="room.showPhotos ? 'mdi-eye-off' : 'mdi-eye'" class="mr-1" />
                            {{ room.showPhotos ? 'Скрыть' : 'Показать' }} фото
                          </VBtn>
                        </div>
                        <QuillEditor
                          v-model:content="room.description"
                          contentType="html"
                          theme="snow"
                          :options="{
                            placeholder: 'Введите описание номера...',
                            modules: {
                              toolbar: [
                                ['bold', 'italic', 'underline'],
                                ['list', 'ordered', 'bullet'],
                                ['link'],
                                ['clean']
                              ]
                            }
                          }"
                          :disabled="saving"
                          style="height: 150px;"
                        />
                      </VCol>
                      <!-- Фотографии номера -->
                      <VCol v-if="room.showPhotos" cols="12">
                        <VCard variant="outlined" class="mt-2">
                          <VCardTitle class="text-h6">Фотографии номера</VCardTitle>
                          <VCardText>
                            <GalleryUploader
                              :photos="room.photos"
                              @update:photos="updateRoomPhotos(formData.value.rooms.indexOf(room), $event)"
                              :maxPhotos="10"
                              :maxFileSizeKB="5120"
                              :maxWidth="1920"
                              :maxHeight="1080"
                              :compressImages="true"
                              :compressQuality="0.85"
                              :disabled="saving"
                            />
                          </VCardText>
                        </VCard>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
          </template>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Услуги -->
    <VWindowItem value="services">
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <span>Услуги</span>
          <VBtn color="primary" @click="addService" :disabled="saving">
            <VIcon start icon="mdi-plus" />
            Добавить услугу
          </VBtn>
        </VCardTitle>
        <VCardText>
          <VRow v-if="formData.services.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-room-service" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Нет добавленных услуг</div>
            </VCol>
          </VRow>
          <VRow v-for="(service, index) in formData.services" :key="service.tempId || service.id" class="mb-4">
            <VCol cols="12">
              <VCard variant="outlined">
                <VCardText>
                  <div class="d-flex justify-space-between align-center mb-4">
                    <h4>Услуга {{ index + 1 }}</h4>
                    <VBtn
                      color="error"
                      variant="text"
                      size="small"
                      @click="removeService(index)"
                      :disabled="saving"
                    >
                      <VIcon icon="mdi-delete" />
                    </VBtn>
                  </div>
                  <VRow>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="service.name"
                        label="Название услуги*"
                        placeholder="Завтрак"
                        prepend-inner-icon="mdi-label"
                        :disabled="saving"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model.number="service.sort_order"
                        label="Порядок сортировки"
                        type="number"
                        placeholder="0"
                        prepend-inner-icon="mdi-sort"
                        :disabled="saving"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VSwitch
                        v-model="service.is_active"
                        label="Услуга активна"
                        color="primary"
                        inset
                        :disabled="saving"
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Периоды бронирования -->
    <VWindowItem value="periods">
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <span>Периоды бронирования</span>
          <VBtn color="primary" @click="addBookingPeriod" :disabled="saving">
            <VIcon start icon="mdi-plus" />
            Добавить период
          </VBtn>
        </VCardTitle>
        <VCardText>
          <VRow v-if="formData.booking_periods.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-calendar-blank" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Нет добавленных периодов</div>
            </VCol>
          </VRow>
          <VRow v-for="(period, index) in formData.booking_periods" :key="period.tempId || period.id" class="mb-4">
            <VCol cols="12">
              <VCard variant="outlined">
                <VCardText>
                  <div class="d-flex justify-space-between align-center mb-4">
                    <h3>Период {{ index + 1 }}</h3>
                    <VBtn
                      color="error"
                      variant="text"
                      size="small"
                      @click="removeBookingPeriod(index)"
                      :disabled="saving"
                    >
                      <VIcon icon="mdi-delete" />
                    </VBtn>
                  </div>
                  <VRow>
                    <VCol cols="12" md="4">
                      <VTextField
                        v-model="period.name"
                        label="Название периода*"
                        placeholder="Летний сезон"
                        prepend-inner-icon="mdi-label"
                        :disabled="saving"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model="period.start_date"
                        label="Дата начала*"
                        type="date"
                        prepend-inner-icon="mdi-calendar-start"
                        :disabled="saving"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model="period.end_date"
                        label="Дата окончания*"
                        type="date"
                        prepend-inner-icon="mdi-calendar-end"
                        :disabled="saving"
                      />
                    </VCol>
                    <VCol cols="12" md="2">
                      <VSwitch
                        v-model="period.is_active"
                        label="Активен"
                        color="primary"
                        inset
                        :disabled="saving"
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Цены -->
    <VWindowItem value="prices">
      <VCard>
        <VCardTitle>Цены</VCardTitle>
        <VCardText>
          <VAlert type="info" class="mb-4">
            Укажите цены для каждого номера и услуги в разных периодах бронирования
          </VAlert>
          
          <!-- Таблица цен для номеров -->
          <div v-if="formData.rooms.length > 0" class="mb-6">
            <h3 class="text-h6 mb-3">Цены номеров</h3>
            <VCard variant="outlined">
              <VCardText class="pa-0">
                <div class="price-table-container">
                  <table class="price-table">
                    <thead>
                      <tr>
                        <th class="name-column">Номер</th>
                        <th v-for="period in formData.booking_periods" :key="period.tempId || period.id" class="price-column period-header">
                          <div class="period-name">{{ period.name }}</div>
                          <div class="period-dates" v-if="period.start_date && period.end_date">
                            {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                          </div>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="room in formData.rooms" :key="room.tempId || room.id" class="price-row">
                        <td class="name-column">
                          <div class="room-info">
                            <div class="room-name">{{ room.name || 'Без названия' }}</div>
                            <div class="room-details">
                              <VChip size="x-small" variant="outlined" class="mr-1">
                                {{ room.capacity }} чел.
                              </VChip>
                              <VChip size="x-small" variant="outlined">
                                {{ room.beds_count }} кров.
                              </VChip>
                            </div>
                          </div>
                        </td>
                        <td v-for="period in formData.booking_periods" :key="period.tempId || period.id" class="price-column">
                          <VTextField
                            v-model.number="room.prices[period.tempId || period.id].price"
                            type="number"
                            min="0"
                            step="0.01"
                            variant="outlined"
                            density="compact"
                            hide-details
                            :suffix="formData.currency"
                            :disabled="saving"
                            placeholder="0"
                          />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </VCardText>
            </VCard>
          </div>

          <!-- Таблица цен для услуг -->
          <div v-if="formData.services.length > 0">
            <h3 class="text-h6 mb-3">Цены услуг</h3>
            <VCard variant="outlined">
              <VCardText class="pa-0">
                <div class="price-table-container">
                  <table class="price-table">
                    <thead>
                      <tr>
                        <th class="name-column">Услуга</th>
                        <th v-for="period in formData.booking_periods" :key="period.tempId || period.id" class="price-column period-header">
                          <div class="period-name">{{ period.name }}</div>
                          <div class="period-dates" v-if="period.start_date && period.end_date">
                            {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                          </div>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="service in formData.services" :key="service.tempId || service.id" class="price-row">
                        <td class="name-column">
                          <div class="service-name">{{ service.name || 'Без названия' }}</div>
                        </td>
                        <td v-for="period in formData.booking_periods" :key="period.tempId || period.id" class="price-column">
                          <VTextField
                            v-model.number="service.prices[period.tempId || period.id].price"
                            type="number"
                            min="0"
                            step="0.01"
                            variant="outlined"
                            density="compact"
                            hide-details
                            :suffix="formData.currency"
                            :disabled="saving"
                            placeholder="0"
                          />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </VCardText>
            </VCard>
          </div>

          <!-- Сообщения если нет данных -->
          <VRow v-if="formData.rooms.length === 0 && formData.services.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-currency-usd" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Сначала добавьте номера или услуги</div>
              <div class="mt-4">
                <VBtn class="mr-2" @click="activeTab = 'rooms'" :disabled="saving">
                  Перейти к номерам
                </VBtn>
                <VBtn @click="activeTab = 'services'" :disabled="saving">
                  Перейти к услугам
                </VBtn>
              </div>
            </VCol>
          </VRow>

          <VRow v-else-if="formData.booking_periods.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-calendar-blank" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Сначала добавьте периоды бронирования</div>
              <VBtn class="mt-4" @click="activeTab = 'periods'" :disabled="saving">
                Перейти к периодам
              </VBtn>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Фотографии -->
    <VWindowItem value="photos">
      <VCard>
        <VCardTitle>Фотографии отеля</VCardTitle>
        <VCardText>
          <div class="mb-6">
            <h3 class="text-h6 mb-3">Главное фото</h3>
            <ImageUploaderEnhanced
              :photo="formData.main_photo"
              @update:photoId="updateMainPhotoId"
              @update:imageUrl="updateMainPhotoUrl"
              :enableCropping="true"
              :aspectRatio="'16:9'"
              :maxWidth="1920"
              :maxHeight="1080"
              :disabled="saving"
            />
          </div>
          <div>
            <h3 class="text-h6 mb-3">Галерея фото</h3>
            <GalleryUploader
              :photos="formData.gallery_photos"
              @update:photos="updateGalleryPhotos"
              :maxPhotos="20"
              :maxFileSizeKB="5120"
              :maxWidth="1920"
              :maxHeight="1080"
              :compressImages="true"
              :compressQuality="0.85"
              :disabled="saving"
            />
          </div>
        </VCardText>
      </VCard>
    </VWindowItem>
  </VWindow>

  <!-- Кнопки действий -->
  <VCard class="mt-6">
    <VCardActions>
      <VSpacer />
      <VBtn 
        color="secondary" 
        variant="outlined" 
        @click="cancel"
        :disabled="saving"
      >
        Отмена
      </VBtn>
      <VBtn 
        color="primary" 
        @click="save" 
        :loading="saving" 
        :disabled="!isFormValid || saving"
      >
        {{ isEditing ? 'Сохранить' : 'Создать' }}
      </VBtn>
    </VCardActions>
  </VCard>
</div>
</template>

<style scoped>
.hotel-form {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.v-window-item {
  min-height: 400px;
}

/* Стили для таблицы цен */
.price-table-container {
  overflow-x: auto;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.price-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
  background: white;
}

.price-table th,
.price-table td {
  border: 1px solid rgba(var(--v-border-color), 0.12);
  padding: 12px 8px;
  vertical-align: middle;
}

/* Заголовки периодов - серый фон */
.price-table .period-header {
  background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
  font-weight: 600;
  text-align: center;
  position: sticky;
  top: 0;
  z-index: 2;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.period-name {
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  margin-bottom: 4px;
}

.period-dates {
  font-size: 0.75rem;
  color: rgb(var(--v-theme-on-surface-variant));
  font-weight: 500;
}

/* Подсветка строк при наведении */
.price-table .price-row:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
  transition: background-color 0.2s ease;
}

.price-table .price-row:hover td {
  border-color: rgba(var(--v-theme-primary), 0.2);
}

/* Колонка с названиями */
.price-table .name-column {
  width: 250px;
  min-width: 200px;
  background-color: rgb(var(--v-theme-surface));
  position: sticky;
  left: 0;
  z-index: 1;
  font-weight: 500;
  border-right: 2px solid rgba(var(--v-border-color), 0.24);
}

/* Колонки с ценами */
.price-table .price-column {
  width: 120px;
  min-width: 100px;
  text-align: center;
}

.room-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 4px 0;
}

.room-name {
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.95rem;
}

.room-details {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.service-name {
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.95rem;
}

/* Стили для строений */
.building-header {
  background: linear-gradient(135deg, rgb(var(--v-theme-primary), 0.05) 0%, rgb(var(--v-theme-primary), 0.1) 100%);
  border-left: 4px solid rgb(var(--v-theme-primary));
}

.room-card {
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.room-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-left-color: rgb(var(--v-theme-primary));
}

/* Адаптивность для таблицы */
@media (max-width: 960px) {
  .price-table .name-column {
    width: 200px;
    min-width: 180px;
  }
  .price-table .price-column {
    width: 100px;
    min-width: 90px;
  }
}

@media (max-width: 600px) {
  .hotel-form {
    padding: 10px;
  }
  
  .v-card-title {
    font-size: 1.1rem !important;
  }
  
  .price-table {
    font-size: 0.875rem;
  }
  
  .price-table th,
  .price-table td {
    padding: 8px 4px;
  }
  
  .price-table .name-column {
    width: 150px;
    min-width: 130px;
  }
  
  .price-table .price-column {
    width: 80px;
    min-width: 70px;
  }
  
  .period-dates {
    font-size: 0.65rem;
  }
}

/* Стили для Quill редактора */
.ql-editor {
  min-height: 200px;
  font-size: 14px;
  line-height: 1.6;
  font-family: inherit;
}

.ql-toolbar {
  border-radius: 8px 8px 0 0;
  background-color: rgb(var(--v-theme-surface-variant));
  border: 1px solid rgba(var(--v-border-color), 0.12);
}

.ql-container {
  border-radius: 0 0 8px 8px;
  font-family: inherit;
  border: 1px solid rgba(var(--v-border-color), 0.12);
  border-top: none;
}

.ql-container.ql-snow:focus,
.ql-toolbar.ql-snow:focus {
  outline: none;
  border-color: rgb(var(--v-theme-primary));
  box-shadow: 0 0 0 2px rgba(var(--v-theme-primary), 0.2);
}

/* Улучшенная видимость кнопок тулбара */
.ql-toolbar .ql-formats {
  margin-right: 12px;
}

.ql-toolbar button {
  border-radius: 4px;
  margin: 1px;
  transition: all 0.2s ease;
}

.ql-toolbar button:hover {
  background-color: rgba(var(--v-theme-primary), 0.1);
}

.ql-toolbar button.ql-active {
  background-color: rgba(var(--v-theme-primary), 0.2);
  color: rgb(var(--v-theme-primary));
}

/* Адаптивность для редактора */
@media (max-width: 600px) {
  .ql-toolbar .ql-formats {
    margin-right: 8px;
  }
  
  .ql-editor {
    padding: 12px;
    font-size: 13px;
  }
}

/* Анимации */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Стили для карточек */
.v-card--outlined {
  transition: all 0.3s ease;
}

.v-card--outlined:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

/* Стили для кнопок удаления */
.v-btn--variant-text.v-btn--density-default {
  min-width: auto;
  padding: 0 8px;
  transition: all 0.2s ease;
}

.v-btn--variant-text.v-btn--density-default:hover {
  background-color: rgba(var(--v-theme-error), 0.1);
  color: rgb(var(--v-theme-error));
}

/* Стили для чипов */
.v-chip--size-x-small {
  font-size: 0.7rem;
  height: 20px;
  padding: 0 6px;
}

.v-chip--size-small {
  font-size: 0.75rem;
  height: 24px;
  padding: 0 8px;
}

/* Стили для переключателей */
.v-switch--inset .v-switch__track {
  opacity: 0.7;
}

.v-switch--inset .v-switch__thumb {
  transition: all 0.2s ease;
}

/* Стили для полей ввода в таблице */
.price-table .v-field {
  border-radius: 6px;
  background: rgba(var(--v-theme-surface), 0.8);
  transition: all 0.2s ease;
}

.price-table .v-field:hover {
  background: rgba(var(--v-theme-surface), 1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.price-table .v-field--focused {
  background: white;
  box-shadow: 0 0 0 2px rgba(var(--v-theme-primary), 0.3);
}

.price-table .v-field__input {
  padding: 6px 8px;
  min-height: 32px;
  text-align: right;
  font-weight: 500;
  font-size: 0.9rem;
}

.price-table .v-field__suffix {
  padding-right: 8px;
  opacity: 0.8;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface-variant));
}

/* Стили для заголовков */
h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  margin-bottom: 0.5rem;
}

h4 {
  font-size: 1.1rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
  margin-bottom: 0.5rem;
}

h5 {
  font-size: 1rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
  margin-bottom: 0.5rem;
}

/* Стили для алертов */
.v-alert {
  border-radius: 8px;
  border-left: 4px solid currentColor;
}

.v-alert--type-info {
  background: linear-gradient(135deg, rgba(var(--v-theme-info), 0.1) 0%, rgba(var(--v-theme-info), 0.05) 100%);
}

/* Стили для индикатора загрузки */
.v-progress-circular {
  margin: 0 auto;
}

/* Стили для пустых состояний */
.text-center.py-8 {
  padding: 32px 0;
}

.text-grey {
  color: rgb(var(--v-theme-on-surface-variant));
  font-size: 0.95rem;
}

/* Стили для кнопок действий */
.v-card-actions {
  padding: 16px 24px;
  background: linear-gradient(135deg, rgb(var(--v-theme-surface), 0.8) 0%, rgb(var(--v-theme-surface), 0.95) 100%);
  border-top: 1px solid rgba(var(--v-border-color), 0.12);
}

/* Стили для sticky вкладок */
.sticky-tabs {
  position: sticky;
  top: 64px;
  z-index: 100;
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}

/* Стили для фотографий номеров */
.room-photos-card {
  background-color: rgb(var(--v-theme-surface-variant));
  border-radius: 8px;
}

/* Стили для таблицы на мобильных устройствах */
@media (max-width: 600px) {
  .price-table-container {
    margin: 0 -12px;
    border-radius: 0;
  }
  
  .price-table {
    margin: 0 12px;
    border-radius: 8px;
  }
}

/* Стили для плавной прокрутки */
html {
  scroll-behavior: smooth;
}

/* Стили для подсветки активных полей */
.v-field--focused {
  box-shadow: 0 0 0 2px rgba(var(--v-theme-primary), 0.2);
}

.v-field--focused .v-field__outline {
  border-color: rgb(var(--v-theme-primary));
  border-width: 2px;
}

/* Стили для валидации */
.v-field--error {
  --v-field-border-color: rgb(var(--v-theme-error));
}

.v-field--error .v-field__outline {
  border-color: rgb(var(--v-theme-error));
  border-width: 2px;
}

.v-messages__message {
  font-size: 0.75rem;
  margin-top: 4px;
}

/* Стили для состояний отключения */
.v-field--disabled {
  opacity: 0.6;
  pointer-events: none;
}

.v-field--disabled .v-field__input {
  color: rgb(var(--v-theme-on-surface-variant));
}

/* Стили для индикатора сортировки */
.sort-indicator {
  opacity: 0.5;
  font-size: 0.875rem;
}

/* Стили для кнопок с иконками */
.v-btn--icon.v-btn--density-default {
  width: 36px;
  height: 36px;
}

.v-btn--icon:hover {
  background-color: rgba(var(--v-theme-on-surface), 0.08);
  transform: scale(1.1);
}

/* Стили для выпадающих списков */
.v-select .v-field__input {
  padding: 8px 12px;
}

.v-select .v-field__outline {
  border-radius: 8px;
}

/* Стили для текстовых областей */
.v-textarea .v-field__input {
  padding: 12px;
  line-height: 1.6;
  resize: vertical;
  min-height: 80px;
}

/* Стили для числовых полей */
.v-field--type-number .v-field__input {
  text-align: left;
}

.price-table .v-field--type-number .v-field__input {
  text-align: right;
}

/* Стили для чекбоксов в инпутах */
.v-checkbox .v-selection-control {
  min-height: 40px;
}

.v-checkbox .v-label {
  font-size: 0.9rem;
}

/* Стили для тултипов */
.v-tooltip > .v-overlay__content {
  background: rgb(var(--v-theme-surface-variant));
  color: rgb(var(--v-theme-on-surface-variant));
  border-radius: 6px;
  font-size: 0.875rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 8px 12px;
}

/* Стили для кнопок копирования цен */
.copy-price-btn {
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.copy-price-btn:hover {
  opacity: 1;
  transform: scale(1.05);
}

/* Стили для адаптивной сетки */
@media (max-width: 960px) {
  .v-col-md-6 {
    flex: 0 0 100%;
    max-width: 100%;
  }
}

@media (max-width: 600px) {
  .v-col-sm-12 {
    flex: 0 0 100%;
    max-width: 100%;
  }
  
  .d-flex.flex-wrap {
    flex-direction: column;
  }
  
  .gap-2 {
    gap: 8px !important;
  }
}

/* Стили для прогресса сохранения */
.saving-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(2px);
}

.saving-content {
  background: white;
  padding: 32px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  min-width: 200px;
}

/* Стили для анимации появления элементов */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(20px);
  opacity: 0;
}

/* Стили для кастомных чипов */
.custom-chip {
  margin: 2px;
  transition: all 0.2s ease;
}

.custom-chip:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Стили для иконок состояний */
.status-icon {
  margin-right: 4px;
  font-size: 1.1rem;
}

/* Стили для заголовков карточек */
.v-card-title {
  padding: 20px 24px;
  border-bottom: 1px solid rgba(var(--v-border-color), 0.12);
  background: linear-gradient(135deg, rgb(var(--v-theme-surface), 0.8) 0%, rgb(var(--v-theme-surface), 0.95) 100%);
  font-weight: 600;
}

.v-card-text {
  padding: 24px;
}

/* Стили для футера карточек */
.v-card-actions {
  padding: 16px 24px;
  border-top: 1px solid rgba(var(--v-border-color), 0.12);
}

/* Дополнительные стили для улучшения UX */
.hover-lift {
  transition: all 0.2s ease;
  cursor: pointer;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Стили для индикаторов прогресса */
.progress-linear {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
}

/* Стили для мобильной навигации */
@media (max-width: 600px) {
  .v-tabs {
    overflow-x: auto;
  }
  
  .v-tab {
    min-width: 120px;
    font-size: 0.875rem;
  }
  
  .v-tab__slider {
    height: 3px;
  }
}

/* Стили для дата пикеров */
.v-date-picker {
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.v-date-picker-header {
  background: linear-gradient(135deg, rgb(var(--v-theme-primary), 0.1) 0%, rgb(var(--v-theme-primary), 0.05) 100%);
}

/* Стили для кнопок с градиентами */
.v-btn--gradient {
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-primary-darken-1)) 100%);
  color: white;
  transition: all 0.3s ease;
}

.v-btn--gradient:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.3);
}

/* Стили для разделителей */
.divider {
  height: 1px;
  background: linear-gradient(90deg, transparent 0%, rgba(var(--v-border-color), 0.5) 50%, transparent 100%);
  margin: 16px 0;
}

/* Стили для бейджей */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge--success {
  background-color: rgba(var(--v-theme-success), 0.1);
  color: rgb(var(--v-theme-success));
}

.badge--warning {
  background-color: rgba(var(--v-theme-warning), 0.1);
  color: rgb(var(--v-theme-warning));
}

.badge--error {
  background-color: rgba(var(--v-theme-error), 0.1);
  color: rgb(var(--v-theme-error));
}

/* Стили для аккордеонов */
.v-expansion-panel {
  border-radius: 8px !important;
  margin-bottom: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.v-expansion-panel-title {
  padding: 16px 20px;
  font-weight: 500;
}

.v-expansion-panel-text__wrapper {
  padding: 0 20px 20px 20px;
}

/* Стили для спиннеров */
.v-progress-circular--indeterminate {
  animation: rotate 1.5s linear infinite;
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}

/* Стили для модальных окон */
.v-dialog {
  border-radius: 12px;
  overflow: hidden;
}

.v-dialog > .v-card {
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

/* Стили для снэкбаров */
.v-snackbar {
  border-radius: 8px;
  margin: 16px;
}

.v-snackbar__wrapper {
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

/* Стили для навигационных элементов */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 0;
  font-size: 0.875rem;
  color: rgb(var(--v-theme-on-surface-variant));
}

.breadcrumb-item {
  transition: color 0.2s ease;
}

.breadcrumb-item:hover {
  color: rgb(var(--v-theme-primary));
}

.breadcrumb-separator {
  opacity: 0.5;
}

/* Стили для фильтров */
.filter-chip {
  margin: 4px;
  transition: all 0.2s ease;
}

.filter-chip.v-chip--selected {
  background-color: rgb(var(--v-theme-primary));
  color: white;
}

/* Стили для поиска */
.search-field {
  max-width: 400px;
}

.search-field .v-field__prefix {
  padding-right: 8px;
}

/* Стили для пагинации */
.v-pagination {
  justify-content: center;
  margin-top: 24px;
}

.v-pagination__item {
  margin: 0 4px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.v-pagination__item:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.v-pagination__item--is-active {
  background-color: rgb(var(--v-theme-primary));
  color: white;
}

/* Стили для темной темы */
@media (prefers-color-scheme: dark) {
  .price-table {
    background: rgb(var(--v-theme-surface));
  }
  
  .price-table .period-header {
    background: linear-gradient(135deg, rgba(var(--v-theme-on-surface), 0.1) 0%, rgba(var(--v-theme-on-surface), 0.05) 100%);
  }
  
  .price-table .price-row:hover {
    background-color: rgba(var(--v-theme-primary), 0.15);
  }
  
  .building-header {
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.1) 0%, rgba(var(--v-theme-primary), 0.05) 100%);
  }
  
  .v-card--outlined {
    border-color: rgba(var(--v-border-color), 0.24);
  }
}

/* Стили для печати */
@media print {
  .hotel-form {
    max-width: none;
    padding: 0;
  }
  
  .v-card-actions,
  .v-btn {
    display: none !important;
  }
  
  .price-table {
    font-size: 12px;
  }
  
  .price-table th,
  .price-table td {
    border: 1px solid #000;
    padding: 4px;
  }
}

/* Стили для доступности */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Стили для high contrast mode */
@media (prefers-contrast: high) {
  .price-table th,
  .price-table td {
    border-width: 2px;
  }
  
  .v-field--focused {
    box-shadow: 0 0 0 3px rgba(var(--v-theme-primary), 0.5);
  }
  
  .v-btn:hover {
    border-width: 2px;
  }
}

/* Дополнительные утилитарные классы */
.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.text-break-word {
  word-wrap: break-word;
  word-break: break-word;
}

.shadow-sm {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
}

.shadow-md {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.shadow-lg {
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.rounded-lg {
  border-radius: 12px;
}

.rounded-xl {
  border-radius: 16px;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-primary-darken-1)) 100%);
}

.bg-gradient-surface {
  background: linear-gradient(135deg, rgb(var(--v-theme-surface)) 0%, rgb(var(--v-theme-surface-variant)) 100%);
}

/* Финальные стили для улучшения производительности */
.will-change-transform {
  will-change: transform;
}

.gpu-accelerated {
  transform: translateZ(0);
  backface-visibility: hidden;
  perspective: 1000px;
}
</style>