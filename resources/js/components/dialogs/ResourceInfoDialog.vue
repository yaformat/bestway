<template>
  <span v-if="showTrigger" class="resource-name-trigger" @click="openDialog">{{ name }}</span>
  
  <VDialog 
    v-model="showModal" 
    persistent
    :scrim="true"
    scroll-strategy="block"
    :max-width="activeTab === 'price-chart' ? (isMobile ? '100%' : '1200px') : (isMobile ? '100%' : '900px')"
    :fullscreen="isMobile"
  >
    <VCard class="dialog-card">
      <VToolbar color="primary" class="dialog-toolbar">
        <VToolbarTitle>
          {{ activeTab === 'info' ? 'Информация о ресурсе' : 
             activeTab === 'history' ? 'История движения' : 
             'График цены' }}
        </VToolbarTitle>
        <VToolbarItems>
          <VBtn icon variant="plain" @click="closeDialog">
            <VIcon color="white" icon="mdi-close" />
          </VBtn>
        </VToolbarItems>
      </VToolbar>

      <StickyTabs 
        v-model="activeTab" 
        :tabs="tabsConfig"
        class="tabs-fixed"
      />

      <div v-if="loading" class="loader-container">
        <VProgressCircular indeterminate color="primary" />
      </div>

      <div v-else class="content-wrapper">
        <!-- Вкладка: Информация -->
        <div v-show="activeTab === 'info'" class="scroll-content">
          <div :class="['info-content', { 'mobile-layout': isMobile }]">
            <div class="dialog-img-container">
              <img v-if="photoUrl" v-lazy="photoUrl" class="dialog-img" alt="Фото ресурса" />
              <div v-else class="dialog-img-placeholder">
                <VIcon size="64" color="grey lighten-1">mdi-image-outline</VIcon>
              </div>
            </div>
            
            <div class="details">
              <h3 class="resource-title">{{ details.name ?? '' }}</h3>
              
              <VDivider class="my-3" />
              
              <div class="info-grid">
                <div class="info-item">
                  <span class="info-label">Категория:</span>
                  <span class="info-value">{{ details.category?.name }}</span>
                </div>
                
                <div v-if="details.category?.parent" class="info-item">
                  <span class="info-label">Подкатегория:</span>
                  <span class="info-value">{{ details.category?.parent?.name }}</span>
                </div>
                
                <div class="info-item">
                  <span class="info-label">Единица измерения:</span>
                  <span class="info-value">{{ details.unit ?? '' }}</span>
                </div>
                
              </div>

              <!-- Финансовая информация в 2 колонки -->
              <div v-if="details.avg_price || details.last_price || details.stocks_total_price || details.stocks_total_value" class="section">
                <div class="financial-grid">
                  <div v-if="details.stocks_total_value" class="financial-item">
                    <span class="financial-label">Общее количество:</span>
                    <span class="financial-value">{{ details.stocks_total_value.display }}</span>
                  </div>
                  
                  <div v-if="details.stocks_total_price" class="financial-item">
                    <span class="financial-label">Общая стоимость:</span>
                    <span class="financial-value">{{ details.stocks_total_price.display }}</span>
                  </div>
                  
                  <div v-if="details.avg_price" class="financial-item">
                    <span class="financial-label">Средняя цена:</span>
                    <span class="financial-value">{{ details.avg_price.display }}</span>
                  </div>
                  
                  <div v-if="details.last_price" class="financial-item">
                    <span class="financial-label">Последняя цена:</span>
                    <span class="financial-value">{{ details.last_price.display }}</span>
                  </div>
                </div>
              </div>

              <!-- Дополнительная информация -->
              <div v-if="details.min_value || details.deficit_value" class="section">
                <div class="info-grid">
                  <div v-if="details.min_value" class="info-item">
                    <span class="info-label">Минимальное количество:</span>
                    <span class="info-value">{{ details.min_value.display }}</span>
                  </div>
                  
                  <div v-if="details.deficit_value" class="info-item">
                    <span class="info-label">Дефицит мин. количества:</span>
                    <span class="info-value deficit">{{ details.deficit_value.display }}</span>
                  </div>
                </div>
              </div>

              <!-- Потери (только для ingredient и semi_finished) -->
              <div v-if="shouldShowLosses && details.losses?.length" class="section">
                <h4 class="section-title">Потери</h4>
                <div class="chips-container">
                  <VChip
                    v-for="loss in details.losses"
                    :key="loss.id"
                    color="warning"
                    variant="tonal"
                    size="small"
                  >
                    {{ loss.name }}: {{ loss.value }}%
                  </VChip>
                </div>
              </div>

              <!-- Запасы -->
              <div v-if="details.stocks?.length" class="section">
                <VDivider class="my-3" />
                <h4 class="section-title">Запасы</h4>
                <VList class="stocks-list">
                  <VListItem
                    v-for="stock in details.stocks"
                    :key="stock.id"
                    class="stock-item"
                  >
                    <VListItemTitle>{{ stock.name }}</VListItemTitle>
                    <VListItemSubtitle>
                      Количество: {{ stock.value.display }} | Цена: {{ stock.price.display }}
                    </VListItemSubtitle>
                  </VListItem>
                </VList>
              </div>
            </div>
          </div>
        </div>

        <!-- Вкладка: История -->
        <div v-show="activeTab === 'history'" class="history-wrapper">
          <div class="stock-buttons-wrapper">
            <div class="stock-buttons">
              <VBtn
                v-for="stock in details.stocks"
                :key="stock.id"
                @click="selectStock(stock.id)"
                :variant="selectedStockId === stock.id ? 'flat' : 'outlined'"
                :color="selectedStockId === stock.id ? 'primary' : 'default'"
                size="small"
                class="stock-btn"
              >
              {{ stock.name }}
              <VBadge
                class="custom-badge"
                :content="stock.value?.display"
                inline
              ></VBadge>
              </VBtn>
            </div>
          </div>

          <!-- Фиксированный заголовок -->
          <div class="history-header">
            <div class="history-col history-col-1">Время / Тип</div>
            <div class="history-col history-col-2">Количество</div>
            <div class="history-col history-col-3">Остаток</div>
          </div>

          <!-- Скроллируемая область -->
          <div ref="historyScrollArea" class="history-scroll-area">
            <div v-if="groupedHistory.length" class="history-groups">
              <div
                v-for="group in groupedHistory"
                :key="group.date"
                class="history-group"
              >
                <h4 class="group-date">{{ group.date }}</h4>
                
                <div class="history-list">
                  <div
                    v-for="item in group.items"
                    :key="item.id"
                    :ref="el => { if (isHighlightedItem(item)) highlightedItemRef = el }"
                    :class="['history-item', { 'highlighted-item': isHighlightedItem(item) }]"
                  >
                    <div class="history-row">
                      <div class="history-col history-col-1">
                        <div class="history-time">{{ formatTime(item.created_at) }}</div>
                        <div class="history-type">{{ item.action_label }}</div>
                        <div v-if="item.remains" class="history-remains">
                          Остаток: {{ item.remains.value.display }} {{ item.remains.days }} дней
                        </div>
                      </div>
                      <div class="history-col history-col-2">
                        <span :class="['history-value', item.value.raw > 0 ? 'value-positive' : item.value.raw < 0 ? 'value-negative' : '']">
                          {{ item.value?.raw > 0 ? '+' : '' }}{{ item.value.display }}
                        </span>
                      </div>
                      <div class="history-col history-col-3">
                        <span class="history-actual">{{ item.actual_value?.display || '—' }}</span>
                      </div>
                    </div>
                    
                    <!-- Комментарий (если есть) -->
                    <div v-if="item.notes" class="history-notes">
                      <VIcon size="14" class="notes-icon">mdi-comment-text-outline</VIcon>
                      {{ item.notes }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="!loadingHistory" class="empty-state">
              <VIcon size="64" color="grey">mdi-history</VIcon>
              <p>История движения отсутствует</p>
            </div>

            <div v-if="loadingHistory" class="loader-container">
              <VProgressCircular indeterminate color="primary" />
            </div>

            <div v-if="hasMoreHistory && groupedHistory.length > 0" class="load-more-container">
              <VBtn
                :loading="loadingHistory"
                @click="loadMoreHistory"
                variant="outlined"
                block
              >
                Показать еще
              </VBtn>
            </div>
          </div>
        </div>

        <!-- Вкладка: График цены -->
        <div v-if="activeTab === 'price-chart'" class="price-chart-tab">
          <div class="stock-buttons-wrapper">
            <div class="stock-buttons">
              <VBtn
                v-for="stock in details.stocks"
                :key="stock.id"
                @click="selectStockForChart(stock.id)"
                :variant="selectedStockIdForChart === stock.id ? 'flat' : 'outlined'"
                :color="selectedStockIdForChart === stock.id ? 'primary' : 'default'"
                size="small"
                class="stock-btn"
              >
                {{ stock.name }}
                <VBadge
                  class="custom-badge"
                  :content="stock.value?.display"
                  inline
                ></VBadge>
              </VBtn>
            </div>
          </div>
          <div class="price-chart-scroll">
            <PriceChart
              v-if="selectedStockIdForChart"
              :resource-id="id"
              :stock-id="selectedStockIdForChart"
              :key="`chart-${selectedStockIdForChart}`"
              @loaded="handleChartLoaded"
            />
          </div>
        </div>

      </div>
    </VCard>
  </VDialog>
</template>
<script setup>
import { ref, computed, nextTick, watch } from 'vue';
import { useResourceStore } from '@/stores/resourceStore';
import { useDisplay } from 'vuetify';
import StickyTabs from '@/components/StickyTabs.vue';

const props = defineProps({
  id: {
    type: Number,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  allowOpen: {
    type: Boolean,
    default: true,
  },
  actionType: {
    type: String,
    default: null // 'minus', 'supply', 'inventory', 'production'
  },
  actionId: {
    type: Number,
    default: null // ID операции (minus_id, supply_id и т.д.)
  },
  stockId: {
    type: Number,
    default: null
  },
  showTrigger: {
    type: Boolean,
    default: true
  }
});

const { mobile } = useDisplay();
const isMobile = computed(() => mobile.value);

const showModal = ref(false);
const activeTab = ref('info');
const details = ref(null);
const loading = ref(true);
const store = useResourceStore();

// Конфигурация вкладок
const tabsConfig = [
  {
    key: 'info',
    title: 'Информация',
    icon: 'mdi-information-outline'
  },
  {
    key: 'history',
    title: 'История',
    icon: 'mdi-history'
  },
  {
    key: 'price-chart',
    title: 'График цены',
    icon: 'mdi-chart-line'
  }
];

// История движения
const selectedStockId = ref(null);
const historyItems = ref([]);
const loadingHistory = ref(false);
const historyPage = ref(1);
const hasMoreHistory = ref(true);
const highlightActionType = ref(null);
const highlightActionId = ref(null);
const highlightedItemRef = ref(null);
const historyScrollArea = ref(null);
const initialLoadComplete = ref(false); // Флаг для отслеживания первой загрузки
const scrollPerformed = ref(false); // Флаг для отслеживания выполненной прокрутки

const selectedStockIdForChart = ref(null);

const selectStockForChart = (stockId) => {
  selectedStockIdForChart.value = stockId;
};

const handleChartLoaded = (data) => {
  console.log('График загружен:', data);
};

const openDialog = async () => {
  if (!props.allowOpen) return false;

  showModal.value = true;
  loading.value = true;
  initialLoadComplete.value = false;
  scrollPerformed.value = false; // Сбрасываем флаг прокрутки
  
  // Сохраняем данные для поиска записи в истории
  if (props.actionType && props.actionId) {
    highlightActionType.value = props.actionType;
    highlightActionId.value = props.actionId;
    activeTab.value = 'history';
  } else {
    highlightActionType.value = null;
    highlightActionId.value = null;
    activeTab.value = 'info';
  }
  
  // Загружаем детали ресурса
  details.value = await store.fetchSingle(props.id);
  loading.value = false;

  // Автоматически выбираем склад и загружаем историю
  if (details.value?.stocks?.length > 0) {
    const targetStockId = props.stockId || details.value.stocks[0].id;
    selectedStockId.value = targetStockId;   
    selectedStockIdForChart.value = targetStockId; // Для графика

    // Загружаем историю с параметрами для поиска конкретного действия
    await fetchHistory(
      targetStockId, 
      1, 
      props.actionType, 
      props.actionId
    );
    
    // Если нужно подсветить элемент, прокручиваем к нему
    if (props.actionType && props.actionId) {
      await scrollToHighlightedItem();
    }
  }
};

const closeDialog = () => {
  showModal.value = false;
  // Сбрасываем все состояния при закрытии
  highlightActionType.value = null;
  highlightActionId.value = null;
  highlightedItemRef.value = null;
  activeTab.value = 'info';
  historyItems.value = [];
  selectedStockId.value = null;
  details.value = null;
  loading.value = true;
  initialLoadComplete.value = false;
  historyPage.value = 1;
  scrollPerformed.value = false; // Сбрасываем флаг прокрутки
};

const photoUrl = computed(() => {
  return details.value?.photo?.url || '';
});

// Проверка типа ресурса для отображения потерь
const shouldShowLosses = computed(() => {
  const type = details.value?.type;
  return type === 'ingredient' || type === 'semi_finished';
});

// Функция для проверки, является ли элемент истории искомым
const isHighlightedItem = (item) => {
  if (!highlightActionType.value || !highlightActionId.value) return false;
  
  const actionIdField = `${highlightActionType.value}_id`;
  return item[actionIdField] === highlightActionId.value;
};

// Группировка истории по датам
const groupedHistory = computed(() => {
  const groups = {};
  
  historyItems.value.forEach(item => {
    const date = formatDate(item.created_at);
    if (!groups[date]) {
      groups[date] = [];
    }
    groups[date].push(item);
  });

  return Object.entries(groups).map(([date, items]) => ({
    date,
    items
  }));
});

const selectStock = async (stockId) => {
  selectedStockId.value = stockId;
  historyItems.value = [];
  historyPage.value = 1;
  hasMoreHistory.value = true;
  initialLoadComplete.value = false;
  scrollPerformed.value = false; // Сбрасываем флаг прокрутки
  
  // Сбрасываем подсветку при смене склада
  highlightActionType.value = null;
  highlightActionId.value = null;
  highlightedItemRef.value = null;
  
  await fetchHistory(stockId, 1);
};

const fetchHistory = async (stockId, page, actionType = null, actionId = null) => {
  loadingHistory.value = true;
  try {
    const params = {
      resource_id: props.id,
      stock_id: stockId,
      page: page,
      limit: 20
    };

    // Передаем action_type и action_id только для первой загрузки
    if (page === 1 && actionType && actionId) {
      params.action_type = actionType;
      params.action_id = actionId;
    }

    const response = await store.fetchStockHistory(params);
    
    if (page === 1) {
      historyItems.value = response.items;
      initialLoadComplete.value = true;
    } else {
      historyItems.value = [...historyItems.value, ...response.items];
    }
    
    hasMoreHistory.value = historyItems.value.length < response.total_count;
    
    console.log('История загружена:', historyItems.value.length, 'элементов');
    console.log('Total count:', response.total_count);
    console.log('Has more:', hasMoreHistory.value);
    
  } catch (error) {
    console.error('Ошибка загрузки истории:', error);
  } finally {
    loadingHistory.value = false;
  }
};

const loadMoreHistory = () => {
  if (!initialLoadComplete.value) return;
  
  // Вычисляем следующую страницу на основе количества загруженных элементов
  const itemsPerPage = 20;
  const nextPage = Math.floor(historyItems.value.length / itemsPerPage) + 1;
  
  console.log('Загрузка следующей страницы:', nextPage);
  console.log('Текущее количество элементов:', historyItems.value.length);
  
  fetchHistory(selectedStockId.value, nextPage);
};

const scrollToHighlightedItem = async () => {
  console.log('Начинаем прокрутку к элементу с actionType:', highlightActionType.value, 'actionId:', highlightActionId.value);
  
  // Ждем несколько тиков для полного рендеринга
  await nextTick();
  await nextTick();
  await new Promise(resolve => setTimeout(resolve, 300));
  
  if (highlightedItemRef.value && historyScrollArea.value) {
    const element = highlightedItemRef.value;
    const container = historyScrollArea.value;
    
    console.log('Элемент найден, выполняем прокрутку');
    
    // Получаем позиции элемента и контейнера
    const elementRect = element.getBoundingClientRect();
    const containerRect = container.getBoundingClientRect();
    
    // Проверяем, виден ли элемент полностью
    const isVisible = (
      elementRect.top >= containerRect.top &&
      elementRect.bottom <= containerRect.bottom
    );
    
    if (!isVisible) {
      // Вычисляем позицию для прокрутки
      const elementTop = element.offsetTop;
      const containerScrollTop = container.scrollTop;
      const containerHeight = container.clientHeight;
      const elementHeight = element.offsetHeight;
      
      let scrollPosition;
      
      // Если элемент выше видимой области
      if (elementRect.top < containerRect.top) {
        // Прокручиваем так, чтобы элемент был вверху с небольшим отступом
        scrollPosition = elementTop - 20;
      } 
      // Если элемент ниже видимой области
      else {
        // Прокручиваем так, чтобы элемент был внизу с небольшим отступом
        scrollPosition = elementTop - containerHeight + elementHeight + 20;
      }
      
      console.log('Позиция прокрутки:', scrollPosition);
      
      // Плавная прокрутка
      container.scrollTo({
        top: Math.max(0, scrollPosition),
        behavior: 'smooth'
      });

      // Отмечаем, что прокрутка выполнена
      scrollPerformed.value = true;

    } else {
      console.log('Элемент уже видим, прокрутка не требуется');
      // Отмечаем, что прокрутка выполнена
      scrollPerformed.value = true;
    }
  } else {
    console.log('Элемент или контейнер не найден');
    console.log('highlightedItemRef:', highlightedItemRef.value);
    console.log('historyScrollArea:', historyScrollArea.value);
  }
};



// Следим за изменением historyItems для прокрутки
// Следим за изменением historyItems для прокрутки (только для первой загрузки)
watch(historyItems, async (newItems) => {
  // Прокручиваем только если еще не прокручивали и есть что подсвечивать
  if (newItems.length > 0 && highlightActionType.value && highlightActionId.value && !scrollPerformed.value) {
    console.log('История обновлена, проверяем наличие элемента');
    const found = newItems.find(item => isHighlightedItem(item));
    if (found) {
      console.log('Элемент найден в истории, выполняем прокрутку');
      await scrollToHighlightedItem();
    } else {
      console.log('Элемент не найден в текущей истории');
    }
  }
}, { deep: true });

// Следим за изменением activeTab
watch(activeTab, async (newTab) => {
  console.log('Переключение на вкладку:', newTab);
  // Прокручиваем только если еще не прокручивали
  if (newTab === 'history' && highlightActionType.value && highlightActionId.value && historyItems.value.length > 0 && !scrollPerformed.value) {
    await scrollToHighlightedItem();
  }
});



const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Экспортируем метод для программного открытия
defineExpose({
  openDialog
});
</script>



<style scoped>
.dialog-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  max-height: 90vh;
  overflow: hidden;
}

.dialog-toolbar {
  flex-shrink: 0;
}

.tabs-fixed {
  flex-shrink: 0;
}

.content-wrapper {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

/* Информация о ресурсе */
.scroll-content {
  height: 100%;
  overflow-y: auto;
  overflow-x: hidden;
}

.scroll-content::-webkit-scrollbar {
  width: 8px;
}

.scroll-content::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
}

.scroll-content::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

.scroll-content::-webkit-scrollbar-thumb:hover {
  background-color: rgba(0, 0, 0, 0.3);
}

.info-content {
  display: flex;
  gap: 24px;
  padding: 24px;
}

.info-content.mobile-layout {
  flex-direction: column;
}

.dialog-img-container {
  flex: 0 0 300px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.mobile-layout .dialog-img-container {
  flex: 1;
  max-width: 100%;
}

.dialog-img {
  width: 100%;
  max-width: 300px;
  height: auto;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dialog-img-placeholder {
  width: 100%;
  max-width: 300px;
  height: 300px;
  border-radius: 12px;
  background-color: #f5f5f5;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.details {
  flex: 1;
  min-width: 0;
}

.resource-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 16px 0;
  color: rgb(var(--v-theme-primary));
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 16px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-label {
  font-size: 0.875rem;
  color: rgba(0, 0, 0, 0.6);
  font-weight: 500;
}

.info-value {
  font-size: 1rem;
  color: rgba(0, 0, 0, 0.87);
  font-weight: 400;
}

.info-value.deficit {
  color: rgb(var(--v-theme-error));
  font-weight: 600;
}

.section {
  margin-top: 24px;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 12px;
  color: rgba(0, 0, 0, 0.87);
}

/* Финансовая информация в 2 колонки */
.financial-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  background: rgba(0, 0, 0, 0.02);
  padding: 16px;
  border-radius: 8px;
}

.financial-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.financial-label {
  font-size: 0.875rem;
  color: rgba(0, 0, 0, 0.6);
  font-weight: 500;
}

.financial-value {
  font-size: 1.125rem;
  color: rgb(var(--v-theme-primary));
  font-weight: 600;
}

.chips-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.stocks-list {
  background: transparent;
  padding: 0;
}

.stock-item {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
  margin-bottom: 8px;
  padding: 12px;
}

/* История движения */
.history-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.stock-buttons-wrapper {
  padding: 16px 24px 12px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  flex-shrink: 0;
}

.stock-buttons {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.stock-buttons::-webkit-scrollbar {
  height: 6px;
}

.stock-buttons::-webkit-scrollbar-track {
  background: transparent;
}

.stock-buttons::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.stock-btn {
  flex-shrink: 0;
  white-space: nowrap;
}

/* Фиксированный заголовок таблицы */
.history-header {
  display: flex;
  background: rgba(0, 0, 0, 0.05);
  padding: 12px 24px;
  font-weight: 600;
  font-size: 0.875rem;
  color: rgba(0, 0, 0, 0.87);
  flex-shrink: 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

.history-scroll-area {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  scroll-behavior: smooth;
}

.history-scroll-area::-webkit-scrollbar {
  width: 8px;
}

.history-scroll-area::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
}

.history-scroll-area::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

.history-scroll-area::-webkit-scrollbar-thumb:hover {
  background-color: rgba(0, 0, 0, 0.3);
}

.history-groups {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 12px 24px;
}

.history-group {
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
  padding: 12px;
}

.group-date {
  font-size: 0.9375rem;
  font-weight: 600;
  margin-bottom: 12px;
  color: rgb(var(--v-theme-primary));
}

.history-list {
  display:flex;
  flex-direction: column;
  gap: 8px;
}

.history-item {
  background: white;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.history-item.highlighted-item {
  background: rgba(var(--v-theme-primary), 0.12);
  box-shadow: 0 0 0 3px rgba(var(--v-theme-primary), 0.3);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 3px rgba(var(--v-theme-primary), 0.3);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(var(--v-theme-primary), 0.2);
  }
}

.history-row {
  display: flex;
  padding: 12px;
  align-items: center;
}

.history-col {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
}

.history-col-1 {
  flex: 0 0 50%;
  align-items: flex-start;
  gap: 4px;
  flex-direction: column;
}

.history-col-2 {
  flex: 0 0 25%;
  justify-content: center;
}

.history-col-3 {
  flex: 0 0 25%;
  justify-content: flex-end;
}

.history-time {
  color: rgba(0, 0, 0, 0.6);
  font-weight: 500;
}

.history-type {
  font-size: 0.875rem;
  color: rgba(0, 0, 0, 0.87);
  font-weight: 600;
}

.history-remains {
  color: rgba(0, 0, 0, 0.77);
  font-style: italic;
  font-size: 0.8125rem;
}

.history-value {
  font-weight: 600;
  font-size: 0.9375rem;
}

.value-positive {
  color: rgb(var(--v-theme-success));
}

.value-negative {
  color: rgb(var(--v-theme-error));
}

.history-actual {
  font-weight: 500;
  color: rgba(0, 0, 0, 0.87);
  font-size: 0.9375rem;
}

.history-notes {
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.03);
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  font-size: 0.8125rem;
  color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  gap: 6px;
  font-style: italic;
}

.notes-icon {
  flex-shrink: 0;
  opacity: 0.6;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
  text-align: center;
  color: rgba(0, 0, 0, 0.6);
}

.empty-state p {
  margin-top: 16px;
  font-size: 1rem;
}

.load-more-container {
  margin-top: 8px;
  padding: 0 24px 24px;
}

.resource-name-trigger {
  cursor: pointer;
  border-bottom: 1px solid transparent;
  transition: all 0.3s ease;
}

.resource-name-trigger:hover {
  border-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-primary));
}

/* Адаптивность */
@media (max-width: 600px) {
  .dialog-card {
    max-height: 100vh;
  }

  .info-content {
    padding: 16px;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .financial-grid {
    gap: 12px;
    padding: 12px;
  }

  .stock-buttons-wrapper {
    padding: 12px 16px 8px;
  }

  .history-header {
    font-size: 0.8125rem;
    padding: 10px 16px;
  }

  .history-groups {
    padding: 8px 8px;
    gap: 8px;
  }

  .history-row {
    padding: 8px;
  }

  .history-col {
    font-size: 0.8125rem;
  }

  .history-col-1 {
    flex: 0 0 40%;
  }

  .history-col-2 {
    flex: 0 0 30%;
  }

  .history-col-3 {
    flex: 0 0 30%;
  }

  .history-time {
    font-size: 0.75rem;
  }

  .history-type {
    font-size: 0.8125rem;
  }

  .history-value,
  .history-actual,
  .history-remains {
    font-size: 0.875rem;
  }

  .history-notes {
    padding: 6px 8px;
    font-size: 0.75rem;
  }

  .history-group {
    padding: 8px;
  }

  .group-date {
    font-size: 0.875rem;
    margin-bottom: 8px;
  }

  .load-more-container {
    padding: 0 16px 16px;
  }
}


.price-chart-tab {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.price-chart-tab .stock-buttons-wrapper {
  padding: 16px 24px 12px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  flex-shrink: 0;
}

.price-chart-scroll {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
}

.price-chart-scroll::-webkit-scrollbar {
  width: 8px;
}

.price-chart-scroll::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
}

.price-chart-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

.price-chart-scroll::-webkit-scrollbar-thumb:hover {
  background-color: rgba(0, 0, 0, 0.3);
}

/* Адаптация для мобильных */
@media (max-width: 600px) {
  .price-chart-tab .stock-buttons-wrapper {
    padding: 12px 16px 8px;
  }
}
</style>

