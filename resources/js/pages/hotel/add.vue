<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Определяем режим редактирования
const isEditing = ref(!!route.params.id)
const hotelId = ref(route.params.id || null)

// Инициализируем данные отеля с правильной структурой
const hotelData = ref({
  id: null,
  name: '',
  description: '',
  direction_id: null,
  direction: null,
  resort_id: null,
  resort: null,
  latitude: null,
  longitude: null,
  rating: null,
  stars: null,
  sort_order: 0,
  is_active: true,
  rest_type_ids: [],
  rest_types: [],
  
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
  
  // Дополнительные поля
  created_at: null,
  updated_at: null,
})

// Загрузка данных при редактировании
const loadHotelData = async () => {
  if (!isEditing.value) return
  
  // try {
  //   const response = await fetch(`/api/hotels/${hotelId.value}`)
  //   if (!response.ok) {
  //     throw new Error('Не удалось загрузить данные отеля')
  //   }
    
  //   const data = await response.json()
    
  //   // Обновляем данные отеля
  //   hotelData.value = {
  //     ...hotelData.value,
  //     ...data,
      
  //     // Инициализируем цены для номеров
  //     rooms: data.rooms?.map(room => ({
  //       ...room,
  //       prices: room.prices || {},
  //     })) || [],
      
  //     // Инициализируем периоды
  //     booking_periods: data.booking_periods || [],
      
  //     // Фотографии
  //     main_photo: data.main_photo || null,
  //     gallery_photos: data.gallery_photos || [],
  //   }
    
  // } catch (error) {
  //   console.error('Ошибка загрузки отеля:', error)
  //   // Можно добавить уведомление об ошибке
  //   router.push('/hotels')
  // }
}

// Инициализация данных для нового отеля
const initializeNewHotel = () => {
  const currentYear = new Date().getFullYear()
  
  hotelData.value.booking_periods = [
    {
      tempId: 'default',
      name: 'Стандартный период',
      start_date: `${currentYear}-01-01`,
      end_date: `${currentYear}-12-31`,
      sort_order: 0,
      is_active: true,
    },
  ]
  
  hotelData.value.rooms = [
    {
      tempId: 'default',
      name: 'Standard Room',
      description: 'Стандартный номер',
      capacity: 2,
      beds_count: 1,
      sort_order: 0,
      is_active: true,
      prices: {
        default: {
          price: 100,
          currency: 'USD',
          min_nights: 1,
          max_nights: 30,
        },
      },
    },
  ]
}

// Загрузка данных при монтировании компонента
onMounted(async () => {
  if (isEditing.value) {
    await loadHotelData()
  } else {
    initializeNewHotel()
  }
})
</script>

<template>
<div>
  <HotelForm
    :hotelData="hotelData"
    :isEditing="isEditing"
    @saved="handleSaved"
    @cancelled="handleCancelled"
  />
</div>
</template>