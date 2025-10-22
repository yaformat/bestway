<!-- resources/js/components/tables/DomainsTable.vue -->
<template>
  <div>
    <VCardText class="d-flex justify-space-between flex-wrap gap-4">
      <VRow>
        <VCol cols="12" sm="6">
          <h3 class="text-h5 mb-0">Управление доменами</h3>
          <p class="text-medium-emphasis">Настройка всех доменов и их параметров</p>
        </VCol>
        <VCol cols="12" sm="6" class="text-end">
          <VBtn @click="addNewDomain" color="primary">
            <VIcon start icon="mdi-plus" />
            Добавить домен
          </VBtn>
          <VBtn @click="loadDomains" variant="outlined" class="ml-2">
            <VIcon start icon="mdi-refresh" />
            Обновить
          </VBtn>
        </VCol>
      </VRow>
    </VCardText>
    
    <VCardText v-if="isLoading">
      <div class="text-center py-8">
        <VProgressCircular indeterminate size="48" />
        <p class="mt-4">Загрузка доменов...</p>
      </div>
    </VCardText>
    
    <VCardText v-else-if="domains.length === 0">
      <div class="text-center py-8">
        <VIcon size="64" icon="mdi-web" color="grey-lighten-2" />
        <p class="mt-4 text-medium-emphasis">Домены не найдены</p>
        <VBtn @click="addNewDomain" color="primary" class="mt-4">
          Создать первый домен
        </VBtn>
      </div>
    </VCardText>
    
    <div v-else>
      <!-- Табы для доменов -->
      <VTabs v-model="activeTab" class="mb-4">
        <VTab
          v-for="domain in domains"
          :key="domain.id"
          :value="domain.id"
        >
          <VIcon start icon="mdi-web" />
          {{ domain.domain }}
          <VChip
            v-if="!domain.is_active"
            size="x-small"
            color="error"
            class="ml-2"
          >
            Неактивен
          </VChip>
        </VTab>
        <VTab @click="addNewDomain">
          <VIcon start icon="mdi-plus" />
          Добавить
        </VTab>
      </VTabs>
      
      <VWindow v-model="activeTab">
        <VWindowItem
          v-for="domain in domains"
          :key="domain.id"
          :value="domain.id"
        >
          <DomainForm
            :model="domain"
            @save="saveDomain"
            @delete="deleteDomain"
            @toggle-active="toggleDomainActive"
          />
        </VWindowItem>
      </VWindow>
    </div>
    
    <!-- Модальное окно для создания нового домена -->
    <VDialog v-model="isCreateDialogVisible" max-width="800">
      <VCard>
        <VCardTitle>
          <VIcon start icon="mdi-plus" />
          Создать новый домен
        </VCardTitle>
        
        <VCardText>
          <VForm ref="createForm" v-model="newDomainValid">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newDomain.name"
                  label="Название домена"
                  placeholder="Например: Bestway Кыргызстан"
                  :rules="[required]"
                  required
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newDomain.domain"
                  label="Домен"
                  placeholder="Например: bestway.site"
                  :rules="[required, validDomain]"
                  required
                />
              </VCol>
              
              <VCol cols="12">
                <VTextarea
                  v-model="newDomain.description"
                  label="Описание"
                  placeholder="Описание домена"
                  rows="3"
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VSwitch
                  v-model="newDomain.is_default"
                  label="Домен по умолчанию"
                  hint="Будет использоваться если домен не определен"
                  persistent-hint
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VSwitch
                  v-model="newDomain.is_active"
                  label="Активен"
                  hint="Домен доступен для использования"
                  persistent-hint
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VTextField
                  v-model.number="newDomain.sort_order"
                  label="Порядок сортировки"
                  type="number"
                  hint="Чем меньше число, тем выше в списке"
                  persistent-hint
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
        
        <VCardActions>
          <VSpacer />
          <VBtn @click="isCreateDialogVisible = false" variant="text">
            Отмена
          </VBtn>
          <VBtn 
            @click="createDomain" 
            color="primary"
            :disabled="!newDomainValid"
            :loading="isCreating"
          >
            Создать
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
    
    <!-- Уведомления -->
    <VSnackbar v-model="showNotificationSnackbar" :color="notificationColor">
      {{ notificationText }}
    </VSnackbar>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useDomainStore } from '@/stores/domainStore'
import DomainForm from '@/components/forms/DomainForm.vue'

const domainStore = useDomainStore()

const domains = ref([])
const activeTab = ref(null)
const isLoading = ref(false)
const isCreateDialogVisible = ref(false)
const isCreating = ref(false)
const newDomainValid = ref(false)

// Новый домен
const newDomain = ref({
  name: '',
  domain: '',
  description: '',
  is_default: false,
  is_active: true,
  sort_order: 0
})

// Уведомления
const showNotificationSnackbar = ref(false)
const notificationText = ref('')
const notificationColor = ref('success')

const props = defineProps({
  isActive: Boolean
})

// Методы
const loadDomains = async () => {
  isLoading.value = true
  try {
    const response = await domainStore.fetchAll({ with: ['settings'] })
    domains.value = response.data || response
    if (domains.value.length > 0 && !activeTab.value) {
      activeTab.value = domains.value[0].id
    }
  } catch (error) {
    console.error('Ошибка загрузки доменов:', error)
    showNotification('Ошибка загрузки доменов', 'error')
  } finally {
    isLoading.value = false
  }
}

const saveDomain = async (domainData) => {
  try {
    await domainStore.update(domainData.id, domainData)
    showNotification('Домен успешно сохранен', 'success')
    await loadDomains()
  } catch (error) {
    console.error('Ошибка сохранения домена:', error)
    showNotification('Ошибка сохранения домена', 'error')
  }
}

const deleteDomain = async (domainId) => {
  if (!confirm('Вы уверены, что хотите удалить этот домен?')) {
    return
  }
  
  try {
    await domainStore.delete(domainId)
    showNotification('Домен успешно удален', 'success')
    await loadDomains()
  } catch (error) {
    console.error('Ошибка удаления домена:', error)
    showNotification('Ошибка удаления домена', 'error')
  }
}

const toggleDomainActive = async (domainId, status) => {
  try {
    await domainStore.toggleActive(domainId, status)
    showNotification(`Домен ${status ? 'активирован' : 'деактивирован'}`, 'success')
    await loadDomains()
  } catch (error) {
    console.error('Ошибка смены статуса:', error)
    showNotification('Ошибка смены статуса', 'error')
  }
}

const addNewDomain = () => {
  newDomain.value = {
    name: '',
    domain: '',
    description: '',
    is_default: false,
    is_active: true,
    sort_order: domains.value.length
  }
  isCreateDialogVisible.value = true
}

const createDomain = async () => {
  isCreating.value = true
  try {
    await domainStore.create(newDomain.value)
    showNotification('Домен успешно создан', 'success')
    isCreateDialogVisible.value = false
    await loadDomains()
  } catch (error) {
    console.error('Ошибка создания домена:', error)
    showNotification('Ошибка создания домена', 'error')
  } finally {
    isCreating.value = false
  }
}

const showNotification = (text, color = 'success') => {
  notificationText.value = text
  notificationColor.value = color
  showNotificationSnackbar.value = true
}

// Валидаторы
const required = (value) => !!value || 'Это поле обязательно'
const validDomain = (value) => {
  const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9](?:\.[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9])*$/;
  return domainRegex.test(value) || 'Введите корректный домен'
}

// Watchers
watch(() => props.isActive, (newVal) => {
  if (newVal) {
    loadDomains()
  }
})

onMounted(() => {
  if (props.isActive) {
    loadDomains()
  }
})
</script>