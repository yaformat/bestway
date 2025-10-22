<script setup>
import { useResourceStore } from '@/stores/resourceStore'
import { ref } from 'vue'
import router from '@/router'

const route = useRoute()
const resourceStore = useResourceStore()

// Инициализируем с правильной структурой
const resourceData = ref({
  id: null,
  name: '',
  notes: '',
  type: 'ingredient',
  category_id: null,
  unit: 'gram',
  shelf_life_days: null,
  has_expiry: false,
  photo_id: null,
  photo: null,
  losses: [],
  cooking_time: { hours: 0, minutes: 0 },
  ready_time: { hours: 0, minutes: 0 }
})

const resourceId = Number(route.params.id)
const isLoading = ref(true)

const loadResource = async (id) => {
  try {
    isLoading.value = true
    const response = await resourceStore.fetchSingle(id)
    
    // Объединяем загруженные данные с базовой структурой
    resourceData.value = {
      ...resourceData.value,
      ...response,
      losses: response.losses || [],
      cooking_time: response.cooking_time || { hours: 0, minutes: 0 },
      ready_time: response.ready_time || { hours: 0, minutes: 0 }
    }
    
    console.log(resourceData.value)
  } catch (error) {
    console.log(error)
  } finally {
    isLoading.value = false
  }
}

loadResource(resourceId)
</script>

<template>
  <div>
    <div v-if="isLoading" class="text-center pa-4">
      <VProgressCircular indeterminate />
      <div class="mt-2">Загрузка...</div>
    </div>
    
    <ResourceForm 
      v-else
      :resourceData="resourceData" 
    />
  </div>
</template>
