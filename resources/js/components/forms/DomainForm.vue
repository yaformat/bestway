<!-- resources/js/components/forms/DomainForm.vue -->
<template>
  <div>
    <VCard>
      <VCardTitle class="d-flex align-center">
        <VIcon start icon="mdi-web" />
        {{ model.name }}
        <VSpacer />
        <VChip
          :color="model.is_active ? 'success' : 'error'"
          size="small"
        >
          {{ model.is_active ? 'Активен' : 'Неактивен' }}
        </VChip>
        <VChip
          v-if="model.is_default"
          color="warning"
          size="small"
          class="ml-2"
        >
          По умолчанию
        </VChip>
      </VCardTitle>
      
      <VDivider />
      
      <VTabs v-model="activeTab">
        <VTab value="basic">Основное</VTab>
        <VTab value="seo">SEO</VTab>
        <VTab value="contact">Контакты</VTab>
        <VTab value="appearance">Внешний вид</VTab>
        <VTab value="analytics">Аналитика</VTab>
        <VTab value="advanced">Дополнительно</VTab>
      </VTabs>
      
      <VWindow v-model="activeTab">
        <!-- Основное -->
        <VWindowItem value="basic">
          <VCardText>
            <VForm ref="basicForm" v-model="basicValid">
              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="model.name"
                    label="Название домена"
                    :rules="[required]"
                    required
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="model.domain"
                    label="Домен"
                    :rules="[required, validDomain]"
                    required
                    readonly
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextarea
                    v-model="model.description"
                    label="Описание"
                    rows="3"
                  />
                </VCol>
                
                <VCol cols="12" md="4">
                  <VSwitch
                    v-model="model.is_default"
                    label="Домен по умолчанию"
                    :disabled="model.is_default"
                  />
                </VCol>
                
                <VCol cols="12" md="4">
                  <VSwitch
                    v-model="model.is_active"
                    label="Активен"
                  />
                </VCol>
                
                <VCol cols="12" md="4">
                  <VTextField
                    v-model.number="model.sort_order"
                    label="Порядок сортировки"
                    type="number"
                  />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>
        
        <!-- SEO -->
        <VWindowItem value="seo">
          <VCardText>
            <VForm ref="seoForm" v-model="seoValid">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="settings.seo_title"
                    label="SEO Title"
                    placeholder="Заголовок страницы"
                    counter="60"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextarea
                    v-model="settings.seo_description"
                    label="SEO Description"
                    placeholder="Описание для поисковиков"
                    rows="3"
                    counter="160"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextField
                    v-model="settings.seo_keywords"
                    label="SEO Keywords"
                    placeholder="Ключевые слова через запятую"
                  />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>
        
        <!-- Контакты -->
        <VWindowItem value="contact">
          <VCardText>
            <VForm ref="contactForm" v-model="contactValid">
              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="settings.contact_phone"
                    label="Телефон"
                    placeholder="+996 (312) 123-456"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="settings.contact_email"
                    label="Email"
                    type="email"
                    placeholder="info@example.com"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextField
                    v-model="settings.contact_address"
                    label="Адрес"
                    placeholder="Физический адрес"
                  />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>
        
        <!-- Внешний вид -->
        <VWindowItem value="appearance">
          <VCardText>
            <VForm ref="appearanceForm" v-model="appearanceValid">
              <VRow>
                <VCol cols="12" md="6">
                  <VSelect
                    v-model="settings.theme"
                    :items="themeOptions"
                    label="Тема оформления"
                    item-title="label"
                    item-value="value"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSelect
                    v-model="settings.language"
                    :items="languageOptions"
                    label="Язык по умолчанию"
                    item-title="label"
                    item-value="value"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSelect
                    v-model="settings.default_currency"
                    :items="currencyOptions"
                    label="Валюта по умолчанию"
                    item-title="label"
                    item-value="value"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSelect
                    v-model="settings.timezone"
                    :items="timezoneOptions"
                    label="Часовой пояс"
                    item-title="label"
                    item-value="value"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VFileInput
                    v-model="logoFile"
                    label="Логотип"
                    accept="image/*"
                    prepend-icon="mdi-image"
                    @change="handleLogoUpload"
                  />
                  <VImg
                    v-if="settings.logo"
                    :src="settings.logo"
                    max-width="200"
                    class="mt-2"
                  />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>
        
        <!-- Аналитика -->
        <VWindowItem value="analytics">
          <VCardText>
            <VForm ref="analyticsForm" v-model="analyticsValid">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="settings.google_analytics"
                    label="Google Analytics ID"
                    placeholder="GA-XXXXXXXXX"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextField
                    v-model="settings.yandex_metrica"
                    label="Яндекс.Метрика ID"
                    placeholder="XXXXXXXXX"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextarea
                    v-model="settings.head_code"
                    label="Код в <head>"
                    placeholder="Дополнительный код для head"
                    rows="5"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextarea
                    v-model="settings.body_open_code"
                    label="Код после <body>"
                    placeholder="Код после открытия body"
                    rows="5"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VTextarea
                    v-model="settings.body_close_code"
                    label="Код перед </body>"
                    placeholder="Код перед закрытием body"
                    rows="5"
                  />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>
        
        <!-- Дополнительно -->
        <VWindowItem value="advanced">
          <VCardText>
            <VForm ref="advancedForm" v-model="advancedValid">
              <VRow>
                <VCol cols="12">
                  <VSwitch
                    v-model="settings.maintenance_mode"
                    label="Режим обслуживания"
                    hint="Сайт будет недоступен для пользователей"
                    persistent-hint
                  />
                </VCol>
                
                <VCol cols="12" v-if="settings.maintenance_mode">
                  <VTextarea
                    v-model="settings.maintenance_message"
                    label="Сообщение об обслуживании"
                    placeholder="Сообщение для пользователей во время обслуживания"
                    rows="3"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VTextField
                    v-model.number="settings.cache_ttl"
                    label="Время кэша (секунды)"
                    type="number"
                    hint="Как долго хранить настройки в кэше"
                    persistent-hint
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSwitch
                    v-model="settings.debug_mode"
                    label="Режим отладки"
                    hint="Включить отладочную информацию"
                    persistent-hint
                  />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VWindowItem>
      </VWindow>
      
      <VDivider />
      
      <VCardActions>
        <VSpacer />
        <VBtn @click="$emit('toggle-active', model.id, !model.is_active)" variant="text">
          <VIcon start :icon="model.is_active ? 'mdi-pause' : 'mdi-play'" />
          {{ model.is_active ? 'Деактивировать' : 'Активировать' }}
        </VBtn>
        <VBtn @click="confirmDelete" color="error" variant="text">
          <VIcon start icon="mdi-delete" />
          Удалить
        </VBtn>
        <VBtn @click="save" color="primary" :loading="isSaving">
          <VIcon start icon="mdi-content-save" />
          Сохранить
        </VBtn>
      </VCardActions>
    </VCard>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'

const props = defineProps({
  model: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['save', 'delete', 'toggle-active'])

const activeTab = ref('basic')
const isSaving = ref(false)
const logoFile = ref(null)

// Валидация форм
const basicValid = ref(false)
const seoValid = ref(false)
const contactValid = ref(false)
const appearanceValid = ref(false)
const analyticsValid = ref(false)
const advancedValid = ref(false)

// Настройки домена
const settings = reactive({
  // SEO
  seo_title: '',
  seo_description: '',
  seo_keywords: '',
  
  // Контакты
  contact_phone: '',
  contact_email: '',
  contact_address: '',
  
  // Внешний вид
  theme: 'default',
  language: 'ru',
  default_currency: 'KGS',
  timezone: 'Asia/Bishkek',
  logo: '',
  favicon: '',
  
  // Цвета
  primary_color: '#3B82F6',
  secondary_color: '#10B981',
  accent_color: '#F59E0B',
  
  // Аналитика
  google_analytics: '',
  yandex_metrica: '',
  head_code: '',
  body_open_code: '',
  body_close_code: '',
  
  // Дополнительно
  maintenance_mode: false,
  maintenance_message: 'Сайт на техническом обслуживании',
  cache_ttl: 3600,
  debug_mode: false
})

// Опции для селектов
const themeOptions = [
  { label: 'По умолчанию', value: 'default' },
  { label: 'Темная тема', value: 'dark' },
  { label: 'Светлая тема', value: 'light' },
  { label: 'Авто', value: 'auto' }
]

const languageOptions = [
  { label: 'Русский', value: 'ru' },
  { label: 'English', value: 'en' },
  { label: 'Кыргызча', value: 'ky' },
  { label: 'Қазақша', value: 'kk' },
  { label: 'O\'zbekcha', value: 'uz' }
]

const currencyOptions = [
  { label: 'KGS - Кыргызский сом', value: 'KGS' },
  { label: 'USD - Доллар США', value: 'USD' },
  { label: 'EUR - Евро', value: 'EUR' },
  { label: 'RUB - Российский рубль', value: 'RUB' },
  { label: 'KZT - Казахстанский тенге', value: 'KZT' },
  { label: 'UZS - Узбекский сум', value: 'UZS' }
]

const timezoneOptions = [
  { label: 'Asia/Bishkek', value: 'Asia/Bishkek' },
  { label: 'Asia/Almaty', value: 'Asia/Almaty' },
  { label: 'Asia/Tashkent', value: 'Asia/Tashkent' },
  { label: 'Europe/Moscow', value: 'Europe/Moscow' },
  { label: 'UTC', value: 'UTC' }
]

// Методы
const loadSettings = () => {
  if (props.model.settings) {
    Object.assign(settings, props.model.settings)
  }
}

const save = async () => {
  isSaving.value = true
  try {
    // Объединяем основные данные домена с настройками
    const domainData = {
      ...props.model,
      settings: { ...settings }
    }
    
    emit('save', domainData)
  } finally {
    isSaving.value = false
  }
}

const confirmDelete = () => {
  if (confirm(`Вы уверены, что хотите удалить домен ${props.model.domain}?`)) {
    emit('delete', props.model.id)
  }
}

const handleLogoUpload = async (file) => {
  if (!file) return
  
  // Здесь должна быть логика загрузки файла на сервер
  // Временно просто устанавливаем preview
  const reader = new FileReader()
  reader.onload = (e) => {
    settings.logo = e.target.result
  }
  reader.readAsDataURL(file)
}

// Валидаторы
const required = (value) => !!value || 'Это поле обязательно'
const validDomain = (value) => {
  const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9](?:\.[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9])*$/;
  return domainRegex.test(value) || 'Введите корректный домен'
}

// Watchers
watch(() => props.model, () => {
  loadSettings()
}, { immediate: true, deep: true })

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.v-window-item {
  min-height: 400px;
}
</style>