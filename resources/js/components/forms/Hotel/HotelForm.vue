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

  <!-- Вкладки -->
  <StickyTabs v-model="activeTab" :tabs="tabs" />

  <!-- Содержимое вкладок -->
  <VWindow v-model="activeTab" class="mt-4">
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
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.direction_id"
                  :items="directions"
                  item-title="name"
                  item-value="id"
                  label="Направление*"
                  :rules="[rules.required]"
                  placeholder="Выберите направление"
                  prepend-inner-icon="mdi-map-marker"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.resort_id"
                  :items="filteredResorts"
                  item-title="name"
                  item-value="id"
                  label="Курорт"
                  placeholder="Выберите курорт (необязательно)"
                  prepend-inner-icon="mdi-beach"
                  clearable
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.stars"
                  :items="starOptions"
                  label="Звездность"
                  placeholder="Выберите звездность"
                  prepend-inner-icon="mdi-star"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="formData.description"
                  label="Описание"
                  placeholder="Введите описание отеля"
                  rows="4"
                  prepend-inner-icon="mdi-text"
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
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model.number="formData.sort_order"
                  label="Порядок сортировки"
                  type="number"
                  placeholder="0"
                  prepend-inner-icon="mdi-sort"
                />
              </VCol>
              <VCol cols="12">
                <VSelect
                  v-model="formData.rest_type_ids"
                  :items="restTypes"
                  item-title="name"
                  item-value="id"
                  label="Виды отдыха"
                  placeholder="Выберите виды отдыха"
                  prepend-inner-icon="mdi-heart"
                  multiple
                  chips
                />
              </VCol>
              <VCol cols="12">
                <VSwitch
                  v-model="formData.is_active"
                  label="Отель активен"
                  color="primary"
                  inset
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Периоды бронирования -->
    <VWindowItem value="periods">
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <span>Периоды бронирования</span>
          <VBtn color="primary" @click="addBookingPeriod">
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
          
          <VRow v-for="(period, index) in formData.booking_periods" :key="index" class="mb-4">
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
                      />
                    </VCol>
                    <VCol cols="12" md="4">
                      <VTextField
                        v-model="period.start_date"
                        label="Дата начала*"
                        type="date"
                        prepend-inner-icon="mdi-calendar-start"
                      />
                    </VCol>
                    <VCol cols="12" md="4">
                      <VTextField
                        v-model="period.end_date"
                        label="Дата окончания*"
                        type="date"
                        prepend-inner-icon="mdi-calendar-end"
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model.number="period.sort_order"
                        label="Порядок сортировки"
                        type="number"
                        placeholder="0"
                        prepend-inner-icon="mdi-sort"
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VSwitch
                        v-model="period.is_active"
                        label="Период активен"
                        color="primary"
                        inset
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

    <!-- Номера -->
    <VWindowItem value="rooms">
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <span>Номера</span>
          <VBtn color="primary" @click="addRoom">
            <VIcon start icon="mdi-plus" />
            Добавить номер
          </VBtn>
        </VCardTitle>
        <VCardText>
          <VRow v-if="formData.rooms.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-bed" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Нет добавленных номеров</div>
            </VCol>
          </VRow>
          
          <VRow v-for="(room, index) in formData.rooms" :key="index" class="mb-4">
            <VCol cols="12">
              <VCard variant="outlined">
                <VCardText>
                  <div class="d-flex justify-space-between align-center mb-4">
                    <h3>Номер {{ index + 1 }}</h3>
                    <VBtn
                      color="error"
                      variant="text"
                      size="small"
                      @click="removeRoom(index)"
                    >
                      <VIcon icon="mdi-delete" />
                    </VBtn>
                  </div>
                  
                  <VRow>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="room.name"
                        label="Название номера*"
                        placeholder="Standard Double"
                        prepend-inner-icon="mdi-label"
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="room.description"
                        label="Описание"
                        placeholder="Комфортный номер с двуспальной кроватью"
                        prepend-inner-icon="mdi-text"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model.number="room.capacity"
                        label="Вместимость*"
                        type="number"
                        min="1"
                        placeholder="2"
                        prepend-inner-icon="mdi-account-group"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model.number="room.beds_count"
                        label="Количество кроватей*"
                        type="number"
                        min="1"
                        placeholder="1"
                        prepend-inner-icon="mdi-bed"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model.number="room.sort_order"
                        label="Порядок сортировки"
                        type="number"
                        placeholder="0"
                        prepend-inner-icon="mdi-sort"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VSwitch
                        v-model="room.is_active"
                        label="Номер активен"
                        color="primary"
                        inset
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
            Укажите цены для каждого номера в разных периодах бронирования
          </VAlert>
          
          <VRow v-if="formData.rooms.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-currency-usd" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Сначала добавьте номера во вкладке "Номера"</div>
              <VBtn class="mt-4" @click="activeTab = 'rooms'">
                Перейти к номерам
              </VBtn>
            </VCol>
          </VRow>
          
          <VRow v-else-if="formData.booking_periods.length === 0">
            <VCol cols="12" class="text-center py-8">
              <VIcon icon="mdi-calendar-blank" size="64" color="grey-lighten-1" />
              <div class="mt-2 text-grey">Сначала добавьте периоды бронирования во вкладке "Периоды бронирования"</div>
              <VBtn class="mt-4" @click="activeTab = 'periods'">
                Перейти к периодам
              </VBtn>
            </VCol>
          </VRow>
          
          <div v-else>
            <VCard
              v-for="room in formData.rooms"
              :key="room.tempId || room.id"
              class="mb-4"
              variant="outlined"
            >
              <VCardTitle class="d-flex align-center">
                <VIcon icon="mdi-bed" class="mr-2" />
                {{ room.name }}
                <VChip size="small" class="ml-2">
                  Вместимость: {{ room.capacity }}
                </VChip>
              </VCardTitle>
              
              <VCardText>
                <VTable>
                  <thead>
                    <tr>
                      <th>Период</th>
                      <th>Цена</th>
                      <th>Валюта</th>
                      <th>Мин. ночей</th>
                      <th>Макс. ночей</th>
                      <th>Действия</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(period, periodIndex) in formData.booking_periods"
                      :key="period.tempId || period.id"
                    >
                      <td>{{ period.name }}</td>
                      <td>
                        <VTextField
                          v-model.number="room.prices[period.tempId || period.id].price"
                          label="Цена"
                          type="number"
                          min="0"
                          step="0.01"
                          variant="outlined"
                          density="compact"
                          hide-details
                        />
                      </td>
                      <td>
                        <VSelect
                          v-model="room.prices[period.tempId || period.id].currency"
                          :items="currencyOptions"
                          variant="outlined"
                          density="compact"
                          hide-details
                        />
                      </td>
                      <td>
                        <VTextField
                          v-model.number="room.prices[period.tempId || period.id].min_nights"
                          label="Мин."
                          type="number"
                          min="1"
                          variant="outlined"
                          density="compact"
                          hide-details
                        />
                      </td>
                      <td>
                        <VTextField
                          v-model.number="room.prices[period.tempId || period.id].max_nights"
                          label="Макс."
                          type="number"
                          min="1"
                          variant="outlined"
                          density="compact"
                          hide-details
                        />
                      </td>
                      <td>
                        <VBtn
                          variant="text"
                          color="primary"
                          size="small"
                          @click="copyPriceToAllPeriods(room, period.tempId || period.id)"
                        >
                          <VIcon icon="mdi-content-copy" />
                          <VTooltip activator="parent">
                            Копировать цены этого периода на все остальные
                          </VTooltip>
                        </VBtn>
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </VCardText>
            </VCard>
          </div>
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
            />
          </div>
        </VCardText>
      </VCard>
    </VWindowItem>

    <!-- Дополнительные настройки -->
    <VWindowItem value="settings">
      <VCard>
        <VCardTitle>Дополнительные настройки</VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <h3 class="text-h6 mb-3">SEO настройки</h3>
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="formData.seo_title"
                label="SEO заголовок"
                placeholder="Отель Название - Лучший отель в городе"
                prepend-inner-icon="mdi-web"
                counter="255"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="formData.seo_description"
                label="SEO описание"
                placeholder="Описание отеля для поисковых систем"
                prepend-inner-icon="mdi-text"
                counter="255"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="formData.seo_keywords"
                label="SEO ключевые слова"
                placeholder="отель, бронирование, отдых"
                prepend-inner-icon="mdi-tag"
                counter="255"
              />
            </VCol>
            
            <VCol cols="12" class="mt-6">
              <h3 class="text-h6 mb-3">Контактная информация</h3>
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="formData.phone"
                label="Телефон"
                placeholder="+996 (312) 123-456"
                prepend-inner-icon="mdi-phone"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="formData.email"
                label="Email"
                placeholder="info@hotel.com"
                prepend-inner-icon="mdi-email"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="formData.website"
                label="Веб-сайт"
                placeholder="https://hotel.com"
                prepend-inner-icon="mdi-web"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="formData.address"
                label="Адрес"
                placeholder="Улица, дом, город"
                prepend-inner-icon="mdi-map-marker"
              />
            </VCol>
            
            <VCol cols="12" class="mt-6">
              <h3 class="text-h6 mb-3">Удобства</h3>
            </VCol>
            <VCol cols="12">
              <VSelect
                v-model="formData.facilities"
                :items="facilityOptions"
                label="Удобства отеля"
                placeholder="Выберите удобства"
                prepend-inner-icon="mdi-star"
                multiple
                chips
              />
            </VCol>
            
            <VCol cols="12" class="mt-6">
              <h3 class="text-h6 mb-3">Правила бронирования</h3>
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model.number="formData.check_in_time"
                label="Время заезда"
                type="time"
                prepend-inner-icon="mdi-clock-in"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model.number="formData.check_out_time"
                label="Время выезда"
                type="time"
                prepend-inner-icon="mdi-clock-out"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model.number="formData.cancellation_hours"
                label="Отмена бронирования (часы)"
                type="number"
                min="0"
                placeholder="24"
                prepend-inner-icon="mdi-clock"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VSwitch
                v-model="formData.free_cancellation"
                label="Бесплатная отмена"
                color="primary"
                inset
              />
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VWindowItem>
  </VWindow>

  <!-- Кнопки действий -->
  <VCard class="mt-6">
    <VCardActions>
      <VSpacer />
      <VBtn color="secondary" variant="outlined" @click="cancel">
        Отмена
      </VBtn>
      <VBtn color="primary" @click="save" :loading="saving" :disabled="!isFormValid">
        {{ isEditing ? 'Сохранить' : 'Создать' }}
      </VBtn>
    </VCardActions>
  </VCard>
</div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
//import { useToast } from 'vuetify/lib/composables.mjs';

// Роутер и уведомления
const router = useRouter();
const route = useRoute();
//const toast = useToast();

// Рефы
const basicFormRef = ref(null);
const saving = ref(false);
const activeTab = ref('basic');

// Вычисляемые свойства
const isEditing = computed(() => !!route.params.id);
const isFormValid = computed(() => {
  return formData.value.name && formData.value.direction_id;
});

// Опции для селектов
const starOptions = [
  { title: '1 звезда', value: 1 },
  { title: '2 звезды', value: 2 },
  { title: '3 звезды', value: 3 },
  { title: '4 звезды', value: 4 },
  { title: '5 звезд', value: 5 },
];

const currencyOptions = [
  { title: 'USD ($)', value: 'USD' },
  { title: 'EUR (€)', value: 'EUR' },
  { title: 'RUB (₽)', value: 'RUB' },
  { title: 'KGS (с)', value: 'KGS' },
];

const facilityOptions = [
  { title: 'Wi-Fi', value: 'wifi' },
  { title: 'Парковка', value: 'parking' },
  { title: 'Бассейн', value: 'pool' },
  { title: 'Спа', value: 'spa' },
  { title: 'Ресторан', value: 'restaurant' },
  { title: 'Бар', value: 'bar' },
  { title: 'Фитнес-центр', value: 'fitness' },
  { title: 'Конференц-зал', value: 'conference' },
  { title: 'Лифт', value: 'elevator' },
  { title: 'Кондиционер', value: 'ac' },
  { title: 'Минибар', value: 'minibar' },
  { title: 'Сейф', value: 'safe' },
];

// Вкладки
const tabs = [
  { key: 'basic', title: 'Основная информация', icon: 'mdi-domain' },
  { key: 'periods', title: 'Периоды бронирования', icon: 'mdi-calendar' },
  { key: 'rooms', title: 'Номера', icon: 'mdi-bed' },
  { key: 'prices', title: 'Цены', icon: 'mdi-currency-usd' },
  { key: 'photos', title: 'Фотографии', icon: 'mdi-image' },
  { key: 'settings', title: 'Настройки', icon: 'mdi-cog' },
];

// Данные формы
const formData = ref({
  name: '',
  description: '',
  direction_id: null,
  resort_id: null,
  latitude: null,
  longitude: null,
  rating: null,
  stars: null,
  sort_order: 0,
  is_active: true,
  rest_type_ids: [],
  
  // SEO
  seo_title: '',
  seo_description: '',
  seo_keywords: '',
  
  // Контакты
  phone: '',
  email: '',
  website: '',
  address: '',
  
  // Правила
  check_in_time: '14:00',
  check_out_time: '12:00',
  cancellation_hours: 24,
  free_cancellation: false,
  
  // Удобства
  facilities: [],
  
  // Связанные данные
  booking_periods: [],
  rooms: [],
  main_photo: null,
  gallery_photos: [],
});

// Справочники
const directions = ref([]);
const resorts = ref([]);
const restTypes = ref([]);

// Правила валидации
const rules = {
  required: (value) => !!value || 'Это поле обязательно',
  email: (value) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(value) || 'Введите корректный email';
  },
};

// Вычисляемые свойства
const filteredResorts = computed(() => {
  if (!formData.value.direction_id) return [];
  return resorts.value.filter(r => r.direction_id === formData.value.direction_id);
});

// Методы
const initializeFormData = () => {
  if (isEditing.value) {
    // Загрузка данных отеля для редактирования
    loadHotel();
  } else {
    // Инициализация для создания
    addDefaultPeriod();
    addDefaultRoom();
  }
};

const loadHotel = async () => {
  try {
    const response = await fetch(`/api/hotels/${route.params.id}`);
    const hotel = await response.json();
    
    // Заполнение формы данными
    Object.assign(formData.value, hotel);
    
    // Инициализация цен для номеров
    formData.value.rooms.forEach(room => {
      room.prices = {};
      formData.value.booking_periods.forEach(period => {
        const existingPrice = room.prices?.[period.id];
        room.prices[period.id] = existingPrice || {
          price: 0,
          currency: 'USD',
          min_nights: 1,
          max_nights: 30,
        };
      });
    });
    
  } catch (error) {
    console.error('Ошибка загрузки отеля:', error);
    //toast.error('Не удалось загрузить данные отеля');
  }
};

const loadReferenceData = async () => {
  try {
    // Загрузка направлений
    const directionsResponse = await fetch('/api/directions');
    directions.value = await directionsResponse.json();
    
    // Загрузка курортов
    const resortsResponse = await fetch('/api/resorts');
    resorts.value = await resortsResponse.json();
    
    // Загрузка видов отдыха
    const restTypesResponse = await fetch('/api/rest-types');
    restTypes.value = await restTypesResponse.json();
    
  } catch (error) {
    console.error('Ошибка загрузки справочников:', error);
  }
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
    room.prices[newPeriod.tempId] = {
      price: 0,
      currency: 'USD',
      min_nights: 1,
      max_nights: 30,
    };
  });
};

const removeBookingPeriod = (index) => {
  const period = formData.value.booking_periods[index];
  formData.value.booking_periods.splice(index, 1);
  
  // Удаляем цены для этого периода из всех номеров
  formData.value.rooms.forEach(room => {
    delete room.prices[period.tempId || period.id];
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

// Методы для работы с номерами
const addRoom = () => {
  const newRoom = {
    tempId: Date.now() + Math.random(),
    name: '',
    description: '',
    capacity: 2,
    beds_count: 1,
    sort_order: formData.value.rooms.length,
    is_active: true,
    prices: {},
  };
  
  // Инициализация цен для всех периодов
  formData.value.booking_periods.forEach(period => {
    newRoom.prices[period.tempId || period.id] = {
      price: 0,
      currency: 'USD',
      min_nights: 1,
      max_nights: 30,
    };
  });
  
  formData.value.rooms.push(newRoom);
};

const removeRoom = (index) => {
  formData.value.rooms.splice(index, 1);
};

const addDefaultRoom = () => {
  formData.value.rooms = [
    {
      tempId: 'default',
      name: 'Standard Room',
      description: 'Стандартный номер',
      capacity: 2,
      beds_count: 1,
      sort_order: 0,
      is_active: true,
      prices: {},
    },
  ];
  
  // Инициализация цен
  formData.value.rooms[0].prices = {};
  formData.value.booking_periods.forEach(period => {
    formData.value.rooms[0].prices[period.tempId || period.id] = {
      price: 100,
      currency: 'USD',
      min_nights: 1,
      max_nights: 30,
    };
  });
};

// Методы для работы с ценами
const copyPriceToAllPeriods = (room, sourcePeriodId) => {
  const sourcePrice = room.prices[sourcePeriodId];
  
  formData.value.booking_periods.forEach(period => {
    const periodId = period.tempId || period.id;
    if (periodId !== sourcePeriodId) {
      room.prices[periodId] = { ...sourcePrice };
    }
  });
  
  //toast.success('Цены скопированы на все периоды');
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

// Методы сохранения
const prepareDataForSave = () => {
  const data = { ...formData.value };
  
  // Удаляем временные ID
  data.booking_periods = data.booking_periods.map(period => {
    const { tempId, ...periodData } = period;
    return periodData;
  });
  
  data.rooms = data.rooms.map(room => {
    const { tempId, ...roomData } = room;
    return roomData;
  });
  
  // Подготовка цен для сохранения
  const prices = [];
  data.rooms.forEach(room => {
    Object.entries(room.prices).forEach(([periodId, priceData]) => {
      prices.push({
        hotel_room_id: room.id,
        booking_period_id: periodId,
        ...priceData,
      });
    });
  });
  
  data.prices = prices;
  
  // Фото
  data.main_photo_id = data.main_photo?.id;
  data.gallery_photo_ids = data.gallery_photos
    .filter(p => !p.deleted)
    .map(p => p.id);
  
  return data;
};

const save = async () => {
  if (!isFormValid.value) {
    //toast.error('Заполните обязательные поля');
    activeTab.value = 'basic';
    return;
  }
  
  saving.value = true;
  
  try {
    const data = prepareDataForSave();
    
    const url = isEditing.value 
      ? `/api/hotels/${route.params.id}`
      : '/api/hotels';
    
    const method = isEditing.value ? 'PUT' : 'POST';
    
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });
    
    if (response.ok) {
      const hotel = await response.json();
      //toast.success(isEditing.value ? 'Отель сохранен' : 'Отель создан');
      router.push('/hotels');
    } else {
      const error = await response.json();
      //toast.error(error.message || 'Ошибка сохранения');
    }
    
  } catch (error) {
    console.error('Ошибка сохранения:', error);
    //toast.error('Ошибка сохранения отеля');
  } finally {
    saving.value = false;
  }
};

const cancel = () => {
  router.push('/hotels');
};

// Наблюдатели
watch(() => formData.value.direction_id, (newDirectionId) => {
  if (newDirectionId) {
    // Сбрасываем курорт при смене направления
    formData.value.resort_id = null;
  }
});

// Жизненный цикл
onMounted(() => {
  loadReferenceData();
  initializeFormData();
});
</script>

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
.v-table th {
  font-weight: 600;
  background-color: rgb(var(--v-theme-surface-variant));
}

.v-table td {
  padding: 8px;
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

/* Адаптивность */
@media (max-width: 600px) {
  .hotel-form {
    padding: 10px;
  }
  
  .v-card-title {
    font-size: 1.1rem !important;
  }
  
  .v-table {
    font-size: 0.875rem;
  }
  
  .v-table th,
  .v-table td {
    padding: 4px;
  }
}
</style>