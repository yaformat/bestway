<template>
  <VDialog
      v-model="dialog"
      fullscreen
      persistent
      :scrim="true"
      transition="dialog-bottom-transition"
      max-width="1920" 
      class="add-resources-modal"
    >
    <VCard>
        <div class="dialog-toolbar">
            <VToolbar color="primary">
              <VToolbarTitle>
                <template v-if="title">
                  {{ title }}
                </template>
                <template v-else>
                  Выбор ресурсов
                </template>
              </VToolbarTitle>
              <VToolbarItems>
                  <VBtn
                      icon
                      variant="plain"
                      @click="dialog = false"
                  >
                      <VIcon
                      color="white"
                      icon="mdi-close"
                      />
                  </VBtn>
              </VToolbarItems>
            </VToolbar>
        </div>

      <VCardText class="dialog-content">
        <div class="content-body" ref="contentBody">
            <!-- Колонка 1: Типы и категории -->
            <div class="panel left-panel" ref="leftPanel" v-show="!isMobile || !showSelectedResources">
                <div class="panel-header">Типы и категории</div>
                <div class="panel-body">
                    <div v-for="type in resourceTypes" :key="type.id" class="type-list mb-4">
                    <div class="type-header">{{ type.name }}</div>
                    <VList dense>
                        <VListItem
                            @click="selectCategory(type.id, null)"
                            class="list-item-wrap"
                            :class="{ 'v-list-item--active': isCategorySelected(type.id, null) }"
                        >
                            <VListItemTitle><strong>Все категории</strong></VListItemTitle>
                        </VListItem>
                        <VListItem
                            v-for="cat in getCategoriesByType(type.id)"
                            :key="cat.id"
                            @click="selectCategory(type.id, cat.id)"
                            class="list-item-wrap"
                            :class="{ 'v-list-item--active': isCategorySelected(type.id, cat.id) }"
                        >
                            <VListItemTitle>{{ cat.name }}</VListItemTitle>
                        </VListItem>
                    </VList>
                    </div>
                </div>
            </div>

            <!-- Колонка 2: Ресурсы с поиском -->
            <div class="panel middle-panel" ref="middlePanel" v-show="!isMobile || !showSelectedResources">
                <div class="panel-header">
                    <VTextField
                    v-model="searchQuery"
                    label="Поиск ресурсов"
                    density="compact"
                    clearable
                    class="search-input"
                    />
                    <div class="search-status" v-if="isSearching">
                      <VProgressCircular indeterminate size="24" />
                    </div>
                </div>
                <div class="panel-body">
                    <div v-if="resources.length === 0" class="skeleton-container">
                      <div v-for="i in 10" :key="i" class="skeleton-list-item">
                        <div class="skeleton-list-item-content">
                          <!-- Имитация структуры VListItem -->
                          <div class="resource-row">
                            <!-- Чекбокс -->
                            <div class="skeleton-checkbox"></div>
                            
                            <!-- Аватар с бордером 6px -->
                            <div class="skeleton-avatar-container">
                              <div class="skeleton-avatar"></div>
                            </div>
                            
                            <!-- Текстовый контент -->
                            <div class="skeleton-content">
                              <div class="skeleton-title"></div>
                              <div class="skeleton-subtitle"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else-if="visibleResources.length === 0" class="empty-selection">
                      <div class="text-center pa-4">
                        <VIcon 
                          :icon="searchQuery ? 'mdi-magnify-close' : 'mdi-folder-open-outline'" 
                          size="48" 
                          color="grey-lighten-1" 
                        />
                        <div class="mt-2">Ничего не найдено</div>
                        <div class="text-medium-emphasis">
                          <template v-if="searchQuery">
                            Попробуйте изменить поисковый запрос
                          </template>
                          <template v-else-if="selectedType && selectedCategoryId">
                            В выбранной категории нет ресурсов
                          </template>
                          <template v-else-if="selectedType">
                            В выбранном типе нет ресурсов
                          </template>
                          <template v-else>
                            Попробуйте выбрать другие параметры
                          </template>
                        </div>
                      </div>
                    </div>
                    <VVirtualScroll
                      v-else
                      ref="virtualScrollRef"
                      :items="visibleResources"
                      :item-height="66"
                      height="100%"
                    >
                      <template v-slot:default="{ item }">
                        <VListItem
                          @click="toggleResource(item)"
                          class="list-item-wrap"
                        >
                          <div class="resource-row">
                            <VCheckbox
                              :model-value="isSelected(item)"
                              @click.stop="toggleResource(item)"
                              class="mr-2"
                            />
                            <TableNameWithImage :item="item" />
                          </div>
                        </VListItem>
                      </template>
                    </VVirtualScroll>
                </div>
            </div>

            <!-- Колонка 3: Выбранные ресурсы -->
            <div class="panel right-panel" ref="rightPanel" v-show="!isMobile || showSelectedResources">
                <div class="panel-header">
                  <div>Выбранные ресурсы</div>
                  <div class="close-resources" v-if="isMobile">
                    <VBtn
                      icon
                      variant="text"
                      @click="showSelectedResources = false"
                    >
                      <VIcon icon="mdi-arrow-left" />
                    </VBtn>
                  </div>
                  <div class="select-resources">
                      <VBtn
                        color="primary"
                        @click="handleConfirm()"
                        >
                        выбрать 
                        
                        <VBadge
                          class="custom-badge"
                          :content="selectedResources.length"
                          inline
                        ></VBadge>
                      </VBtn>
                  </div>
                </div>
                <div class="panel-body">
                    <div v-if="!selectedResources.length" class="empty-selection">
                      <div class="text-center pa-4">
                        <VIcon icon="mdi-information-outline" size="48" color="grey-lighten-1" />
                        <div class="mt-2">Ресурсы не выбраны</div>
                      </div>
                    </div>
                    <div v-else v-for="(resources, category) in groupedSelectedResources" :key="category">
                      <div class="font-weight-bold px-4 py-2">{{ category }}</div>
                      <VList dense>
                          <VListItem
                          v-for="res in resources"
                          :key="res.id"
                          class="list-item-wrap"
                          >
                          <div class="resource-row">
                              <VBtn icon variant="text" size="small" color="medium-emphasis" @click="removeResource(res)" class="me-2">
                              <VIcon size="24" icon="mdi-close" />
                              </VBtn>
                              <TableNameWithImage :item="res" />
                          </div>
                          </VListItem>
                      </VList>
                    </div>
                </div>
            </div>
        </div>
      </VCardText>
    </VCard>
    <!-- Расширенный мобильный футер -->
    <div v-if="isMobile" class="mobile-footer-extended">
      <!-- Кнопки действий -->
      <VToolbar color="surface" elevation="8" class="mobile-toolbar">
        <VBtn
          variant="outlined"
          @click="toggleSelectedResources"
          :disabled="selectedResources.length === 0"
          class="flex-grow-1 mr-2"
        >
          Просмотр ({{ selectedResources.length }})
        </VBtn>
        
        <VBtn
          color="primary"
          @click="handleConfirm()"
          :disabled="selectedResources.length === 0"
          class="flex-grow-1"
        >
          <VIcon icon="mdi-check" class="mr-2" />
          Выбрать
        </VBtn>
      </VToolbar>
    </div>
  </VDialog>
</template>

<script setup>
import Fuse from 'fuse.js'
import { ref, watch, computed, nextTick, shallowRef, onMounted, onBeforeUnmount } from 'vue'
import { useGlobalDataStore } from '@/stores/globalDataStore'
import { useResourceStore } from '@/stores/resourceStore'

const virtualScrollRef = ref(null)

const globalStore = useGlobalDataStore()
const resourceStore = useResourceStore()

const resourceTypes = computed(() => {
  if (!props.allowedTypes.length) {
    return globalStore.resource_types || []
  }
  return (globalStore.resource_types || []).filter(type =>
    props.allowedTypes.includes(type.id)
  )
})

const categories = computed(() => globalStore.resource_categories || [])

// Используем shallowRef для больших списков данных
const resources = shallowRef([])

const fuse = ref(null)
const fuseOptions = {
  keys: [
    { name: 'name', weight: 0.9 },
    { name: 'description', weight: 0.1 },
    { name: 'category.name', weight: 0.1 },
  ],
  includeScore: true,     
  threshold: 0.4, 
  ignoreLocation: true,
  minMatchCharLength: 2,
  tokenize: true,         
  matchAllTokens: false,  
  findAllMatches: true,   
  shouldSort: true,       
  isCaseSensitive: false, 
  useExtendedSearch: true 
}


const props = defineProps({
  modelValue: Boolean,
  allowedTypes: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: '',
  },
  initialSelected: {
    type: Array,
    default: () => [],
  }
})

const emit = defineEmits(['update:modelValue', 'resourcesSelected'])

const dialog = ref(props.modelValue)
const selectedType = ref(null)
const selectedCategoryId = ref(null)
const selectedResources = ref([])

// Добавьте функцию для инициализации выбранных ресурсов
const initializeSelectedResources = async () => {
  if (props.initialSelected && props.initialSelected.length > 0) {
    // Если ресурсы еще не загружены, загрузите их
    if (resources.value.length === 0) {
      await fetchResources()
    }
    
    // Найдите ресурсы по их ID и добавьте в selectedResources
    selectedResources.value = resources.value.filter(resource => 
      props.initialSelected.includes(resource.id)
    )
    
    console.log('Initialized selected resources:', selectedResources.value)
  } else {
    // Если initialSelected пуст, очистите выбранные ресурсы
    selectedResources.value = []
  }
}

const searchQuery = ref('')
const debouncedSearch = ref('')
const isSearching = ref(false)
const showSelectedResources = ref(false)
const isMobile = ref(false)
let searchTimeout = null

const contentBody = ref(null)
const leftPanel = ref(null)
const middlePanel = ref(null)
const rightPanel = ref(null)

const processSearchQuery = (query) => {
  if (!query) return '';
  
  // Нормализуем запрос
  const normalizedQuery = query.toLowerCase().trim();
  const words = normalizedQuery.split(/\s+/);
  
  // Для нескольких слов создаем расширенный запрос
  if (words.length > 1) {
    return {
      $and: words.map(word => ({
        $or: fuseOptions.keys.map(key => ({
          [key.name]: word
        }))
      }))
    };
  }
  
  return normalizedQuery;
};

// Вызовите функцию при монтировании и при изменении пропа initialSelected
watch(() => props.initialSelected, initializeSelectedResources, { immediate: true })

// Также вызовите функцию после загрузки ресурсов, если initialSelected не пуст
watch(() => resources.value.length, () => {
  if (resources.value.length > 0 && props.initialSelected && props.initialSelected.length > 0) {
    initializeSelectedResources()
  }
})

// Дебаунсинг поиска
watch(searchQuery, (newValue) => {
  isSearching.value = true
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    debouncedSearch.value = newValue
    isSearching.value = false
  }, 300)
})

// Создаем экземпляр Fuse только при изменении списка ресурсов
// watch(() => resources.value.length, () => {
//   if (resources.value.length) {
//     fuse.value = new Fuse(resources.value, fuseOptions)
//   }
// }, { immediate: true })
watch(() => resources.value.length, () => {
  if (resources.value.length) {
    // Добавляем предварительную обработку данных
    const preparedResources = resources.value.map(resource => ({
      ...resource,
      searchField: `${resource.name} ${resource.description || ''} ${resource.category?.name || ''}`
    }));
    
    fuse.value = new Fuse(preparedResources, {
      ...fuseOptions,
      keys: [
        ...fuseOptions.keys,
        { name: 'searchField', weight: 1.0 }
      ]
    });
  }
}, { immediate: true });


// Загружаем ресурсы только при открытии модального окна
watch(dialog, async (isOpen) => {
  if (isOpen && resources.value.length === 0) {
    await fetchResources()
  }
  if (!isOpen) {
    // Очистка памяти при закрытии модального окна
    if (resources.value.length > 1000) {
      resources.value = []
      fuse.value = null
    }
  }
})

watch(() => props.modelValue, val => (dialog.value = val))
watch(dialog, val => emit('update:modelValue', val))

// Функция мемоизации для вычисляемых свойств
function useMemoize(fn, deps) {
  const cache = ref(null)
  const lastDeps = ref([])
  
  const depsChanged = () => {
    if (lastDeps.value.length !== deps.length) return true
    return deps.some((dep, i) => dep !== lastDeps.value[i])
  }
  
  if (cache.value === null || depsChanged()) {
    cache.value = fn()
    lastDeps.value = [...deps]
  }
  
  return cache.value
}

// Категории
function getCategoriesByType(typeId) {
  return categories.value.filter(cat => cat.type == typeId && !cat.parent)
}

function selectCategory(typeId, categoryId) {
  selectedType.value = typeId
  selectedCategoryId.value = categoryId
  searchQuery.value = null
  nextTick(() => {
    contentBody.value?.scrollTo({
      left: middlePanel.value?.offsetLeft,
      behavior: 'smooth'
    })
  })
}

function isCategorySelected(typeId, categoryId) {
  return selectedType.value === typeId && selectedCategoryId.value === categoryId
}

// Загрузка ресурсов
async function fetchResources() {
  try {
    const response = await resourceStore.fetchAll({
        type: 'all',
        includeTypes: props.allowedTypes,
        limit: -1,
    })
    resources.value = response.items || []
  } catch (e) {
    console.error('Ошибка загрузки ресурсов:', e)
  }
}

const visibleResources = computed(() => {
  return useMemoize(() => {
    let list = resources.value;

    if (props.allowedTypes.length) {
      list = list.filter(res => props.allowedTypes.includes(res.type));
    }

    if (debouncedSearch.value && fuse.value) {
      // Изменяем эту часть
      const processedQuery = processSearchQuery(debouncedSearch.value);
      const searchResults = fuse.value.search(processedQuery);
      
      // Фильтруем результаты с низкой релевантностью
      const filteredResults = searchResults
        .filter(result => result.score < 0.5)
        .map(result => result.item);
      
      return filteredResults;
    }

    if (selectedType.value) {
      list = list.filter(res => res.type === selectedType.value);
    }

    if (selectedCategoryId.value) {
      list = list.filter(res => res.category?.id === selectedCategoryId.value);
    }

    return list;
  }, [debouncedSearch.value, selectedType.value, selectedCategoryId.value, resources.value.length]);
});

const groupedSelectedResources = computed(() => {
  return selectedResources.value.reduce((groups, resource) => {
    const category = resource.category?.name || 'Без категории'
    if (!groups[category]) {
      groups[category] = []
    }
    groups[category].push(resource)
    return groups
  }, {})
})

function isSelected(resource) {
  return selectedResources.value.some(r => r.id === resource.id)
}

function toggleResource(resource) {
  // Изменяем состояние
  if (isSelected(resource)) {
    removeResource(resource)
  } else {
    selectedResources.value.push(resource)
  }
}

function removeResource(resource) {
  selectedResources.value = selectedResources.value.filter(r => r.id !== resource.id)
}

function handleConfirm() {
  emit('resourcesSelected', selectedResources.value)
  dialog.value = false
}

function toggleSelectedResources() {
  showSelectedResources.value = !showSelectedResources.value
}

// Проверка размера экрана
function checkScreenSize() {
  isMobile.value = window.innerWidth < 960
  
  // Если не мобильный, всегда показываем все панели
  if (!isMobile.value) {
    showSelectedResources.value = false
  }
}

// Обработка изменения размера окна с throttling
const throttle = (fn, delay) => {
  let lastCall = 0
  return function(...args) {
    const now = new Date().getTime()
    if (now - lastCall < delay) return
    lastCall = now
    return fn(...args)
  }
}

const handleResize = throttle(() => {
  checkScreenSize()
}, 100)

onMounted(() => {
  checkScreenSize() // Проверяем размер экрана при монтировании
  window.addEventListener('resize', handleResize, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  clearTimeout(searchTimeout)
})
</script>

<style scoped>
.add-resources-modal {
  width: 100%;
  height: 100%;
  margin:0 auto;
}

.add-resources-modal .dialog-content {
  height: 80vh;
  padding-left:0!important;
  padding-right:0!important;
  padding-top: 0!important;
  padding-bottom: 0;
}

@media (max-width: 960px) {
  .add-resources-modal .dialog-content {
    padding-bottom: 80px !important; /* Место для футера */
  }
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
}

.content-body {
  display: flex;
  gap: 0;
  height: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  border: 1px solid transparent;
  border-color: rgba(var(--v-border-color), var(--v-border-opacity));
}

.panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.panel-header {
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  padding: 8px 16px;
  font-weight: 500;
  border-bottom: 1px solid transparent;
  border-color: rgba(var(--v-border-color), var(--v-border-opacity));
  font-size: 18px;
  min-height: 60px;
  align-items: center;
  display: flex;
  justify-content: space-between;
  position: relative;
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity)) !important;
}

.panel-body {
  padding: 8px;
  overflow-y: auto;
  flex: 1;
}

.type-header {
  padding: 8px 16px;
  font-weight: bold;
  font-size: 1rem;
  background-color: rgba(var(--v-theme-primary), 0.8);
  color: #fff;
  border-radius: 4px;
  margin-bottom: 4px;
  position:sticky;
  top:0;
  z-index:10;
}
.type-list .v-list-item {
  border-color: rgba(var(--v-theme-primary), 0.8);
  border-radius:4px!important;
}

.search-input {
  width: 100%;
  border-radius: 8px;
  position: sticky;
  top: 0;
  z-index: 10;
}

.search-status {
  position: absolute;
  right: 32px;
  top: 50%;
  transform: translateY(-50%);
  z-index:10;
}

.left-panel {
  width: 25%;
  overflow-y: auto;
  border-right: 1px solid transparent;
  border-color: rgba(var(--v-border-color), var(--v-border-opacity));
}

.middle-panel {
  width: 45%;
  overflow-y: auto;
}

.right-panel {
  width: 30%;
  overflow-y: auto;
  border-left: 1px solid transparent;
  border-color: rgba(var(--v-border-color), var(--v-border-opacity));
}

.list-item-wrap {
    padding-inline-start: 8px!important;
    padding-inline-end: 8px!important;
    padding-top: 8px!important;
    padding-bottom: 8px!important;
}

.resource-row {
  display: flex;
  align-items: center;
}

.selected-resources-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

.empty-selection {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity)) !important;
}

.skeleton-container {
  width: 100%;
}

.skeleton-list-item {
  min-height: 48px;
  padding: 4px 8px;
  display: flex;
  align-items: center;
  position: relative;
  border-radius: 4px;
  margin-bottom: 2px;
}

.skeleton-list-item-content {
  flex: 1 1 auto;
  min-width: 0;
  padding: 8px 0;
}

.skeleton-checkbox {
  width: 18px;
  height: 18px;
  border-radius: 2px;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  margin-right: 16px;
  flex-shrink: 0;
}

.skeleton-avatar-container {
  width: 50px;
  height: 50px;
  margin-right: 8px;
  flex-shrink: 0;
  position: relative;
}

.skeleton-avatar {
  width: 100%;
  height: 100%;
  border-radius: 6px;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  box-sizing: border-box;
}

.skeleton-content {
  flex: 1;
  min-width: 0;
}

.skeleton-title {
  height: 16px;
  width: 70%;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 4px;
  margin-bottom: 4px;
}

.skeleton-subtitle {
  height: 14px;
  width: 40%;
  background-color: rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 4px;
}

.skeleton-checkbox,
.skeleton-avatar,
.skeleton-title,
.skeleton-subtitle {
  animation: pulse 1.5s infinite;
}  

@keyframes pulse {
  0% {
    opacity: 0.35;
  }
  50% {
    opacity: 0.9;
  }
  100% {
    opacity: 0.35;
  }
}

@media (max-width: 960px) {
  .add-resources-modal {
    max-width: 100%;
    max-height: 100%;
  }

  .content-body {
    flex-direction: row;
    overflow-x: auto;
  }

  .panel-header {
    font-size: 16px;
  }

  .left-panel, .middle-panel {
    width: 80%;
    min-width: 80%;
  }
  .right-panel {
    width: 100%;
    min-width: 100%;
  }
}

.mobile-footer-extended {
  position: sticky;
  bottom: 0;
  z-index: 10;
  background: rgba(var(--v-theme-surface), 1);
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.mobile-toolbar {
  padding: 8px 16px !important;
  min-height: 64px !important;
}

.mobile-toolbar .v-btn--variant-elevated {
  box-shadow: 0 3px 6px rgba(var(--v-theme-primary), 0.3) !important;
}
</style>