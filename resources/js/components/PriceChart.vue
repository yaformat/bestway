<template>
  <div class="price-chart-wrapper">
    <!-- Компактная статистика в одну строку -->
    <div v-if="stats" class="price-stats-compact">
      <div class="stat-item">
        <span class="stat-label">Текущая:</span>
        <span class="stat-value primary">{{ formatPrice(stats.current_price) }}</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-label">Средняя:</span>
        <span class="stat-value">{{ formatPrice(stats.avg_price) }}</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-label">Мин:</span>
        <span class="stat-value">{{ formatPrice(stats.min_price) }}</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-label">Макс:</span>
        <span class="stat-value">{{ formatPrice(stats.max_price) }}</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-label">Изменение:</span>
        <span :class="['stat-value', stats.price_change >= 0 ? 'positive' : 'negative']">
          {{ stats.price_change >= 0 ? '+' : '' }}{{ formatPrice(stats.price_change) }}
          <span class="percent">({{ stats.price_change_percent >= 0 ? '+' : '' }}{{ stats.price_change_percent }}%)</span>
        </span>
      </div>
    </div>

    <!-- Фильтры периода -->
    <div class="period-filters">
      <VBtnToggle
        v-model="selectedPeriod"
        mandatory
        color="primary"
        variant="outlined"
        divided
        density="compact"
      >
        <VBtn value="month" size="small">Месяц</VBtn>
        <VBtn value="quarter" size="small">Квартал</VBtn>
        <VBtn value="year" size="small">Год</VBtn>
        <VBtn value="all" size="small">Все время</VBtn>
      </VBtnToggle>
    </div>

    <!-- Настройки отображения -->
    <div class="chart-settings" v-if="!loading && chartData && chartData.length > 0">
      <VCheckbox 
        v-model="limitData"
        label="Ограничить количество записей"
        density="compact"
        hide-details
        color="primary"
      >
        <!-- <template v-slot:append>
          <VChip 
            size="x-small" 
            variant="tonal" 
            color="primary"
          >
            {{ getLimitForPeriod(selectedPeriod) }}
          </VChip>
        </template> -->
      </VCheckbox>
    </div>

    <!-- Контейнер графика - всегда в DOM, скрывается через класс когда нет данных -->
    <div 
      class="chart-container"
      :class="{ 'chart-container--hidden': loading || !chartData || chartData.length === 0 }"
    >
      <canvas ref="chartCanvas"></canvas>
    </div>

  <!-- Информация о количестве записей -->
  <div v-if="!loading && stats && stats.records_count" class="records-info">
    <VChip size="small" variant="text" color="grey">
      <VIcon start size="16">mdi-information-outline</VIcon>
      Показано записей: {{ stats.records_count }}
      &nbsp;
      <!-- <span v-if="limitData && selectedPeriod === 'all' && stats.records_all > stats.records_count"> -->
      <span v-if="limitData && stats.records_all > stats.records_count">
        из {{ stats.records_all }} (ограничено)
      </span>
      <!-- <span v-else-if="!limitData && selectedPeriod === 'all' && stats.records_all > 500"> -->
      <span v-else-if="!limitData && stats.records_all > 500">
        из {{ stats.records_all }}
      </span>
      <span v-else-if="stats.records_count !== stats.records_total">
        из {{ stats.records_total }}
      </span>
    </VChip>
  </div>

    <!-- Загрузка -->
    <div v-if="loading" class="chart-loader">
      <VProgressCircular indeterminate color="primary" />
    </div>

    <!-- Пустое состояние -->
    <div v-if="!loading && (!chartData || chartData.length === 0)" class="empty-chart">
      <VIcon size="64" color="grey">mdi-chart-line</VIcon>
      <p>Нет данных для отображения графика</p>
      <p class="text-caption">Отображаются только положительные поступления (поставки, инвентаризация, производство)</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick, computed } from 'vue';
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { useResourceStore } from '@/stores/resourceStore';

// Регистрируем компоненты
Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Title,
  Tooltip,
  Legend,
  Filler
);

const props = defineProps({
  resourceId: {
    type: Number,
    required: true
  },
  stockId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['loaded']);
const store = useResourceStore();
const chartCanvas = ref(null);
const chartInstance = ref(null);
const chartData = ref([]);
const stats = ref(null);
const loading = ref(false);
const selectedPeriod = ref('all');
const isMounted = ref(false);
const isRendering = ref(false);
const isMobile = ref(false);
const limitData = ref(true);

// Определяем, мобильное ли устройство
const checkIsMobile = () => {
  isMobile.value = window.innerWidth <= 768;
};

// Вычисляем лимит записей в зависимости от устройства и периода
const getLimitForPeriod = (period) => {
  if (!limitData.value) return 500; // Без лимита
  
  if (period === 'all') {
    return isMobile.value ? 30 : 75;
  }
  // Для других периодов используем стандартные лимиты
  return 500;
};

// Получаем параметры для запроса
const getRequestParams = () => {
  const params = {
    resource_id: props.resourceId,
    stock_id: props.stockId,
    period: selectedPeriod.value,
  };
  
  // Если лимит включен и это период "все время"
  //if (limitData.value && selectedPeriod.value === 'all') {
  if (limitData.value) {
    //params.limit = getLimitForPeriod(selectedPeriod.value);
    params.limit = getLimitForPeriod('all');
    params.smart_limit = true; // Флаг для умного лимитирования на бэкенде
  } else if (!limitData.value) {
    params.limit = 500;
  } else {
    params.limit = getLimitForPeriod(selectedPeriod.value);
  }
  
  return params;
};

// Вычисляем оптимальное количество тиков для оси X
const getMaxTicksLimit = (period) => {
  if (period === 'all') {
    return isMobile.value ? 8 : 12;
  } else if (period === 'year') {
    return isMobile.value ? 10 : 12;
  } else if (period === 'quarter') {
    return isMobile.value ? 8 : 10;
  } else {
    return isMobile.value ? 6 : 8;
  }
};

// Получаем уникальные значения цен из данных для оси Y
const getUniquePrices = (prices) => {
  const uniquePrices = [...new Set(prices)];
  uniquePrices.sort((a, b) => a - b);
  
  // Ограничиваем количество цен для удобства отображения
  const maxPrices = isMobile.value ? 6 : 8;
  if (uniquePrices.length > maxPrices) {
    const step = Math.ceil(uniquePrices.length / maxPrices);
    return uniquePrices.filter((_, index) => index % step === 0);
  }
  
  return uniquePrices;
};

// Группируем данные для графика с адаптивным порогом
const groupedChartData = computed(() => {
  if (!chartData.value || chartData.value.length <= 2) {
    return chartData.value;
  }
  
  // Фильтруем нулевые цены
  const filteredData = chartData.value.filter(item => item.unit_price > 0);
  
  if (filteredData.length === 0) {
    return [];
  }
  
  const grouped = [];
  let currentGroup = [];
  let currentPrice = null;
  // Адаптивный порог группировки - для мобильных группируем агрессивнее
  const priceThreshold = isMobile.value ? 0.02 : 0.01;
  
  filteredData.forEach((item) => {
    const price = item.unit_price;
    
    if (currentPrice === null) {
      currentPrice = price;
      currentGroup.push(item);
      return;
    }
    
    if (Math.abs(price - currentPrice) > priceThreshold) {
      if (currentGroup.length > 1) {
        grouped.push(currentGroup[currentGroup.length - 1]);
      } else if (currentGroup.length === 1) {
        grouped.push(currentGroup[0]);
      }
      currentGroup = [item];
      currentPrice = price;
    } else {
      currentGroup.push(item);
    }
  });
  
  if (currentGroup.length > 0) {
    grouped.push(currentGroup[currentGroup.length - 1]);
  }
  
  if (filteredData.length > 0 && 
      (!grouped[0] || grouped[0].timestamp !== filteredData[0].timestamp)) {
    grouped.unshift(filteredData[0]);
  }
  
  return grouped;
});

const formatPrice = (price) => {
  if (!price && price !== 0) return '—';
  const unitLabel = stats.value?.unit_label || '';
  return `${price.toFixed(2)} с${unitLabel ? '/' + unitLabel : ''}`;
};

const formatDate = (dateString, period) => {
  const date = new Date(dateString);
  if (period === 'month') {
    return date.toLocaleDateString('ru-RU', {
      day: 'numeric',
      month: 'short'
    });
  } else if (period === 'quarter') {
    return date.toLocaleDateString('ru-RU', {
      day: 'numeric',
      month: 'short'
    });
  } else if (period === 'year') {
    return date.toLocaleDateString('ru-RU', {
      month: 'short',
      year: 'numeric'
    });
  } else {
    return date.toLocaleDateString('ru-RU', {
      month: 'short',
      year: 'numeric'
    });
  }
};

const loadChartData = async () => {
  if (!isMounted.value || loading.value) return;
  
  loading.value = true;
  console.log('Начинаем загрузку данных графика...');
  
  try {
    const params = getRequestParams();
    const response = await store.fetchPriceHistory(params);
    chartData.value = response.data || [];
    stats.value = response.stats || null;
    
    console.log('Данные графика загружены:', response);
    
    await nextTick();
    
    // Рендерим график только если есть данные
    if (chartCanvas.value && chartData.value && chartData.value.length > 0) {
      renderChart();
    }
    
    emit('loaded', response);
  } catch (error) {
    console.error('Ошибка загрузки данных графика:', error);
    destroyChart();
  } finally {
    loading.value = false;
  }
};

const destroyChart = () => {
  if (chartInstance.value) {
    try {
      chartInstance.value.destroy();
      console.log('График уничтожен');
    } catch (e) {
      console.warn('Ошибка при уничтожении графика:', e);
    }
    chartInstance.value = null;
  }
  isRendering.value = false;
};

const renderChart = () => {
  if (isRendering.value) {
    console.log('Рендеринг уже выполняется, пропускаем');
    return;
  }
  
  if (!chartCanvas.value) {
    console.warn('Canvas элемент не найден при рендеринге');
    return;
  }
  
  if (!groupedChartData.value || groupedChartData.value.length === 0) {
    console.warn('Нет данных для отображения');
    destroyChart();
    return;
  }
  
  isRendering.value = true;
  
  // Уничтожаем предыдущий график
  destroyChart();
  
  try {
    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) {
      console.error('Не удалось получить контекст canvas');
      isRendering.value = false;
      return;
    }
    
    const labels = groupedChartData.value.map(item => formatDate(item.date, selectedPeriod.value));
    // Дополнительная фильтрация на случай, если нулевые цены прошли
    const validData = groupedChartData.value.filter(item => item.unit_price > 0);
    
    if (validData.length === 0) {
      console.warn('Нет валидных данных (цены > 0) для отображения');
      destroyChart();
      isRendering.value = false;
      return;
    }
    
    const prices = validData.map(item => item.unit_price);
    const pointColors = validData.map((item, index) => {
      // Подсвечиваем ключевые точки: первая, последняя, мин, макс
      if (index === 0) {
        return 'rgba(156, 39, 176, 1)'; // Фиолетовый - начало
      } else if (index === validData.length - 1) {
        return 'rgba(233, 30, 99, 1)'; // Розовый - конец
      } else if (item.unit_price === stats.value?.min_price) {
        return 'rgba(76, 175, 80, 1)'; // Зеленый - минимум
      } else if (item.unit_price === stats.value?.max_price) {
        return 'rgba(255, 152, 0, 1)'; // Оранжевый - максимум
      }
      
      // Обычные точки по типу действия
      switch(item.action) {
        case 'supply': return 'rgba(33, 150, 243, 1)';
        case 'inventory': return 'rgba(255, 152, 0, 1)';
        case 'production': return 'rgba(33, 150, 243, 1)';
        default: return 'rgba(158, 158, 158, 1)';
      }
    });
    
    // Получаем уникальные цены для оси Y (только положительные)
    const uniquePrices = getUniquePrices(prices);
    
    // Вычисляем минимальную и максимальную цену с запасом
    const minPrice = Math.min(...prices);
    const maxPrice = Math.max(...prices);
    const priceRange = maxPrice - minPrice;
    
    // Устанавливаем минимальный отступ (5% от минимальной цены или 0.1, что больше)
    const minPadding = Math.max(minPrice * 0.05, 0.1);
    // Устанавливаем отступ 10% от диапазона, но не менее минимального
    const padding = Math.max(priceRange * 0.1, minPadding);
    
    // Вычисляем границы с запасом (гарантируем положительные значения)
    const yMin = Math.max(0.01, minPrice - padding); // Минимум 0.01
    const yMax = maxPrice + padding;
    
    chartInstance.value = new Chart(ctx, {
      type: 'line',
      data: {
        labels: validData.map(item => formatDate(item.date, selectedPeriod.value)),
        datasets: [{
          label: 'Цена за единицу',
          data: prices,
          borderColor: 'rgba(33, 150, 243, 1)',
          backgroundColor: 'rgba(33, 150, 243, 0.1)',
          pointBackgroundColor: pointColors,
          pointBorderColor: pointColors,
          pointRadius: isMobile.value ? 4 : 5,
          pointHoverRadius: isMobile.value ? 6 : 7,
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 0
        },
        interaction: {
          mode: 'nearest',
          axis: 'x',
          intersect: false
        },
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: isMobile.value ? 8 : 12,
            titleFont: {
              size: isMobile.value ? 12 : 14,
              weight: 'bold'
            },
            bodyFont: {
              size: isMobile.value ? 11 : 13
            },
            callbacks: {
              title: (context) => {
                const index = context[0].dataIndex;
                const item = validData[index];
                const date = new Date(item.date);
                return date.toLocaleDateString('ru-RU', {
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                });
              },
              label: (context) => {
                const index = context.dataIndex;
                const item = validData[index];
                const labels = [
                  `Цена за ед.: ${item.unit_price_display}`,
                  `Действие: ${item.action_label}`,
                ];
                
                // Добавляем пометки для ключевых точек
                if (index === 0) {
                  labels.push('📍 Начало периода');
                } else if (index === validData.length - 1) {
                  labels.push('📍 Конец периода');
                } else if (item.unit_price === stats.value?.min_price) {
                  labels.push('📉 Минимальная цена');
                } else if (item.unit_price === stats.value?.max_price) {
                  labels.push('📈 Максимальная цена');
                }
                
                if (item.value_display) {
                  labels.push(`Количество: ${item.value_display}`);
                }
                if (item.total_price_display) {
                  labels.push(`Общая цена: ${item.total_price_display}`);
                }
                return labels;
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              maxRotation: 45,
              minRotation: isMobile.value ? 35 : 45,
              autoSkip: true,
              maxTicksLimit: getMaxTicksLimit(selectedPeriod.value),
              font: {
                size: isMobile.value ? 10 : 11
              }
            }
          },
          y: {
            // Устанавливаем границы с запасом
            min: yMin,
            max: yMax,
            beginAtZero: false,
            ticks: {
              // Используем только реальные значения цен, но в рамках границ
              callback: (value) => {
                if (value <= 0) return ''; // Не показываем нулевые и отрицательные значения
                const unitLabel = stats.value?.unit_label || '';
                return `${value.toFixed(2)} с${unitLabel ? '/' + unitLabel : ''}`;
              },
              font: {
                size: isMobile.value ? 10 : 11
              },
              maxTicksLimit: isMobile.value ? 5 : 7,
              // Автоматический расчет шага
              autoSkip: true
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          }
        }
      }
    });
    console.log('График успешно создан');
    isRendering.value = false;
  } catch (error) {
    console.error('Ошибка при создании графика:', error);
    destroyChart();
    isRendering.value = false;
  }
};

// Следим за изменением периода
watch(selectedPeriod, async () => {
  if (isMounted.value && !loading.value) {
    console.log('Изменился период, перезагружаем данные');
    destroyChart();
    chartData.value = [];
    stats.value = null;
    await loadChartData();
  }
});

// Следим за изменением опции лимитирования
watch(limitData, async () => {
  if (isMounted.value && !loading.value) {
    console.log('Изменилась опция лимитирования, перезагружаем данные');
    destroyChart();
    chartData.value = [];
    stats.value = null;
    await loadChartData();
  }
});

// Следим за изменением склада
watch(() => props.stockId, async (newStockId, oldStockId) => {
  if (oldStockId === undefined) return;
  
  console.log('Изменился stockId:', props.stockId);
  destroyChart();
  chartData.value = [];
  stats.value = null;
  
  await nextTick();
  
  if (isMounted.value) {
    await loadChartData();
  }
});

// Следим за изменением размера окна
const handleResize = () => {
  const wasMobile = isMobile.value;
  checkIsMobile();
  
  // Если изменился тип устройства, перерисовываем график
  if (wasMobile !== isMobile.value && chartInstance.value) {
    renderChart();
  }
};

onMounted(async () => {
  isMounted.value = true;
  console.log('Компонент смонтирован');
  
  // Определяем тип устройства
  checkIsMobile();
  
  // Добавляем слушатель изменения размера окна
  window.addEventListener('resize', handleResize);
  
  await nextTick();
  
  // Canvas всегда должен быть в DOM
  if (chartCanvas.value) {
    console.log('Canvas элемент найден, начинаем загрузку данных');
    await loadChartData();
  } else {
    console.error('Canvas элемент не найден в DOM');
  }
});

onBeforeUnmount(() => {
  console.log('Компонент размонтируется');
  
  // Удаляем слушатель изменения размера окна
  window.removeEventListener('resize', handleResize);
  
  destroyChart();
  isMounted.value = false;
});
</script>

<style scoped>
.price-chart-wrapper {
  position: relative;
  padding: 20px;
  min-height: 600px;
}

/* Компактная статистика в одну строку */
.price-stats-compact {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 8px 16px;
  padding: 16px;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
  margin-bottom: 20px;
}

.stat-item {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.stat-label {
  font-size: 0.8125rem;
  color: rgba(0, 0, 0, 0.6);
  font-weight: 500;
}

.stat-value {
  font-size: 1rem;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.87);
}

.stat-value.primary {
  color: rgb(var(--v-theme-primary));
  font-size: 1.125rem;
}

.stat-value.positive {
  color: rgb(var(--v-theme-success));
}

.stat-value.negative {
  color: rgb(var(--v-theme-error));
}

.stat-value .percent {
  font-size: 0.875rem;
  margin-left: 2px;
}

.stat-divider {
  width: 1px;
  height: 20px;
  background: rgba(0, 0, 0, 0.12);
}

.period-filters {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

/* Настройки отображения */
.chart-settings {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 6px;
}

.chart-container {
  position: relative;
  height: 450px;
  width: 100%;
  margin-bottom: 12px;
  transition: all 0.3s ease;
}

/* Класс для скрытия контейнера */
.chart-container--hidden {
  height: 0!important;
  margin: 0!important;
  overflow: hidden!important;
  opacity: 0!important;
}

.records-info {
  display: flex;
  justify-content: center;
  padding-top: 8px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.chart-loader {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
  height: 450px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-chart {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 24px;
  text-align: center;
  color: rgba(0, 0, 0, 0.6);
  min-height: 400px;
}

.empty-chart p {
  margin-top: 16px;
  font-size: 1rem;
}

.empty-chart .text-caption {
  margin-top: 8px;
  font-size: 0.875rem;
  color: rgba(0, 0, 0, 0.5);
}

/* Адаптация для планшетов */
@media (max-width: 960px) {
  .price-chart-wrapper {
    padding: 16px;
    min-height: 500px;
  }
  
  .price-stats-compact {
    gap: 6px 12px;
    padding: 12px;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
  
  .stat-value {
    font-size: 0.9375rem;
  }
  
  .stat-value.primary {
    font-size: 1rem;
  }
  
  .chart-container {
    height: 380px;
  }
  
  .chart-loader {
    height: 380px;
  }
}

/* Мобильная адаптация */
@media (max-width: 600px) {
  .price-chart-wrapper {
    padding: 12px;
    min-height: auto;
  }
  
  .price-stats-compact {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
    padding: 12px;
  }
  
  .stat-item {
    justify-content: space-between;
    padding: 6px 0;
  }
  
  .stat-divider {
    display: none;
  }
  
  .stat-label {
    font-size: 0.8125rem;
  }
  
  .stat-value {
    font-size: 1rem;
  }
  
  .stat-value.primary {
    font-size: 1.125rem;
  }
  
  .stat-value .percent {
    font-size: 0.8125rem;
  }
  
  .period-filters {
    margin-bottom: 12px;
  }
  
  .period-filters .v-btn-toggle {
    width: 100%;
  }
  
  .period-filters .v-btn {
    flex: 1;
    font-size: 0.75rem;
    padding: 0 8px;
  }
  
  .chart-settings {
    padding: 6px 12px;
    margin-bottom: 12px;
  }
  
  .chart-settings .v-checkbox {
    font-size: 0.8125rem;
  }
  
  .chart-container {
    height: 300px;
  }
  
  .chart-loader {
    height: 300px;
  }
  
  .empty-chart {
    padding: 40px 16px;
    min-height: 300px;
  }
  
  .empty-chart .v-icon {
    font-size: 48px;
  }
  
  .empty-chart p {
    font-size: 0.9375rem;
    margin-top: 12px;
  }
  
  .empty-chart .text-caption {
    font-size: 0.75rem;
    margin-top: 6px;
  }
}

/* Дополнительные улучшения для маленьких экранов */
@media (max-width: 480px) {
  .price-chart-wrapper {
    padding: 8px;
  }
  
  .price-stats-compact {
    padding: 8px;
    gap: 6px;
  }
  
  .stat-item {
    padding: 4px 0;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
  
  .stat-value {
    font-size: 0.9375rem;
  }
  
  .stat-value.primary {
    font-size: 1rem;
  }
  
  .period-filters {
    margin-bottom: 10px;
  }
  
  .period-filters .v-btn {
    font-size: 0.6875rem;
    padding: 0 4px;
  }
  
  .chart-settings {
    padding: 4px 8px;
    margin-bottom: 10px;
  }
  
  .chart-container {
    height: 250px;
  }
  
  .chart-loader {
    height: 250px;
  }
  
  .empty-chart {
    padding: 32px 12px;
    min-height: 250px;
  }
  
  .empty-chart .v-icon {
    font-size: 40px;
  }
  
  .empty-chart p {
    font-size: 0.875rem;
  }
  
  .empty-chart .text-caption {
    font-size: 0.7rem;
  }
}

/* Анимация появления графика */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.chart-container:not(.chart-container--hidden) {
  animation: fadeIn 0.3s ease-out;
}

/* Улучшение для темной темы */
@media (prefers-color-scheme: dark) {
  .price-stats-compact,
  .chart-settings {
    background: rgba(255, 255, 255, 0.05);
  }
  
  .stat-label {
    color: rgba(255, 255, 255, 0.7);
  }
  
  .stat-value {
    color: rgba(255, 255, 255, 0.9);
  }
  
  .records-info {
    border-top-color: rgba(255, 255, 255, 0.1);
  }
  
  .empty-chart {
    color: rgba(255, 255, 255, 0.7);
  }
  
  .empty-chart .text-caption {
    color: rgba(255, 255, 255, 0.5);
  }
}

/* Фокус состояния для доступности */
.period-filters .v-btn:focus-visible,
.chart-settings .v-checkbox:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

/* Улучшение для high-dpi экранов */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .chart-container canvas {
    image-rendering: crisp-edges;
    image-rendering: -webkit-optimize-contrast;
  }
}

/* Предотвращение горизонтальной прокрутки на мобильных */
.price-chart-wrapper {
  overflow-x: hidden;
  width: 100%;
  box-sizing: border-box;
}

/* Улучшение для печатной версии */
@media print {
  .price-chart-wrapper {
    padding: 10px;
    min-height: auto;
  }
  
  .period-filters,
  .chart-settings,
  .chart-loader {
    display: none;
  }
  
  .chart-container {
    height: 300px;
    page-break-inside: avoid;
  }
  
  .empty-chart {
    display: none;
  }
  
  .price-stats-compact {
    background: none;
    border: 1px solid #ccc;
    margin-bottom: 10px;
  }
}
</style>