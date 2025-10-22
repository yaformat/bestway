<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  techCard: {
    type: Object,
    required: true,
    default: () => ({}) // Добавляем default значение
  }
});

// Активная вкладка
const activeTab = ref('description');

// Количество порций
const servingsCount = ref(1);

// Форматирование времени
const formatTime = (timeObj) => {
  if (!timeObj) return '0 мин';
  
  const hours = timeObj?.hours || 0;
  const minutes = timeObj?.minutes || 0;
  
  if (hours > 0 && minutes > 0) {
    return `${hours} ч ${minutes} мин`;
  } else if (hours > 0) {
    return `${hours} ч`;
  } else {
    return `${minutes} мин`;
  }
};

// Форматирование времени для таймера шага
const formatStepTime = (timeObj) => {
  if (!timeObj) return '';
  
  const hours = timeObj.hours || 0;
  const minutes = timeObj.minutes || 0;
  
  if (hours === 0 && minutes === 0) return '';
  
  const parts = [];
  if (hours) parts.push(`${hours} ч`);
  if (minutes) parts.push(`${minutes} мин`);
  
  return parts.join(' ');
};

// Функция для получения номера следующего шага
const getNextStepNumber = (stepId) => {
  if (!props.techCard?.steps || !stepId) return stepId;
  
  const nextStep = props.techCard.steps.find(s => s.id === stepId);
  return nextStep?.sort_order || stepId;
};

// Безопасные computed свойства
const cookingTime = computed(() => {
  return formatTime(props.techCard?.cooking_time);
});

const readyTime = computed(() => {
  return formatTime(props.techCard?.ready_time);
});

// Группировка ресурсов
const regularGroups = computed(() => {
  return props.techCard?.resource_groups?.filter(group => !group.additional) || [];
});

const additionalGroup = computed(() => {
  return props.techCard?.resource_groups?.find(group => group.additional === 1);
});

// Используем шаги как есть, без сортировки
const steps = computed(() => props.techCard?.steps || []);

// Функция для расчета количества ингредиента с учетом порций
const calculateAmount = (amount) => {
  const baseAmount = parseFloat(amount) || 0;
  return (baseAmount * servingsCount.value).toFixed(1).replace(/\.0$/, '');
};

// Функция для форматирования веса
const formatWeight = (weight, unit) => {
  const calculatedWeight = calculateAmount(weight);
  
  // Преобразование граммов в килограммы, если вес больше 1000г
  if (unit === 'г' && calculatedWeight >= 1000) {
    return `${(calculatedWeight / 1000).toFixed(1).replace(/\.0$/, '')} кг`;
  }
  
  return `${calculatedWeight} ${unit}`;
};
</script>

<template>
  <div class="tech-card-view">
    <!-- Заголовок и основная информация -->
    <div class="tech-card-header">
      <div class="tech-card-image" v-if="techCard?.photo?.url">
        <PhotoDisplay 
          :src="techCard.photo.url" 
          aspect-ratio="1/1"
          rounded="12px"
          :alt="techCard?.name || 'Техкарта'"
        />
      </div>
      
      <div class="tech-card-info">
        <h1 class="tech-card-title">{{ techCard?.name || 'Без названия' }}</h1>
        
        <div class="tech-card-meta">
          <div class="meta-item" v-if="techCard?.categories?.length">
            <VIcon icon="mdi-tag-multiple" size="small" />
            <span>{{ techCard.categories.map(c => c?.name).filter(Boolean).join(', ') }}</span>
          </div>
          
          <div class="meta-item" v-if="techCard?.kitchens?.length">
            <VIcon icon="mdi-silverware-fork-knife" size="small" />
            <span>{{ techCard.kitchens.map(k => k?.name).filter(Boolean).join(', ') }}</span>
          </div>
          
          <div class="meta-item" v-if="techCard?.workshop?.name">
            <VIcon icon="mdi-domain" size="small" />
            <span>{{ techCard.workshop.name }}</span>
          </div>
        </div>
        
        <div class="tech-card-times">
          <div class="time-item">
            <div class="time-label">Общее время:</div>
            <div class="time-value">{{ cookingTime }}</div>
          </div>
          
          <div class="time-item">
            <div class="time-label">Активное время:</div>
            <div class="time-value">{{ readyTime }}</div>
          </div>
        </div>
        
        <div class="tech-card-output">
          <div class="output-label">Выход:</div>
          <div class="output-value">{{ calculateAmount(techCard?.output_weight || 0) }} г</div>
        </div>
        
        <div class="tech-card-cost">
          <div class="cost-label">Себестоимость:</div>
          <div class="cost-value">{{ techCard?.cost_price?.display || 'Не указана' }}</div>
        </div>
      </div>
    </div>
    
    <!-- Вкладки -->
    <div class="tech-card-tabs">
      <VTabs v-model="activeTab" color="primary" grow>
        <VTab value="description">
          <VIcon icon="mdi-information-outline" class="mr-2" />
          Описание
        </VTab>
        <VTab value="cooking">
          <VIcon icon="mdi-chef-hat" class="mr-2" />
          Приготовление
        </VTab>
      </VTabs>
    </div>
    
    <!-- Содержимое вкладок -->
    <VWindow v-model="activeTab" class="tech-card-content">
      <!-- Вкладка "Описание" -->
      <VWindowItem value="description">
        <!-- Описание -->
        <div class="tech-card-section" v-if="techCard?.description">
          <h2 class="section-title">О блюде</h2>
          <div class="section-content description">
            {{ techCard.description }}
          </div>
        </div>
        
        <!-- Ингредиенты -->
        <div class="tech-card-section">
          <h2 class="section-title">Ингредиенты</h2>
          
          <!-- Переключатель порций -->
          <div class="servings-control">
            <span class="servings-label">Количество порций:</span>
            <div class="servings-buttons">
              <VBtn
                variant="text"
                density="comfortable"
                icon="mdi-minus"
                :disabled="servingsCount <= 1"
                @click="servingsCount--"
              />
              <span class="servings-value">{{ servingsCount }}</span>
              <VBtn
                variant="text"
                density="comfortable"
                icon="mdi-plus"
                @click="servingsCount++"
              />
            </div>
          </div>
          
          <div v-for="group in regularGroups" :key="group?.id" class="ingredient-group">
            <h3 v-if="group?.name" class="group-title">{{ group.name }}</h3>
            
            <table class="ingredients-table">
              <thead>
                <tr>
                  <th>Ингредиент</th>
                  <th>Количество</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="resource in (group?.resources || [])" :key="resource?.id" class="ingredient-row">
                  <td class="ingredient-name">
                    <TableNameWithImage :item="resource?.resource" />
                  </td>
                  <td class="ingredient-amount">
                    {{ formatWeight(resource?.weight_brutto || 0, resource?.resource?.unit_alt_label || 'г') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Дополнительные ингредиенты -->
          <div v-if="additionalGroup?.resources?.length" class="ingredient-group additional">
            <h3 class="group-title">{{ additionalGroup.name || 'Дополнительные ингредиенты' }}</h3>
            
            <table class="ingredients-table">
              <thead>
                <tr>
                  <th>Ингредиент</th>
                  <th>Количество</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="resource in additionalGroup.resources" :key="resource?.id" class="ingredient-row">
                  <td class="ingredient-name">
                    <TableNameWithImage :item="resource?.resource" />
                  </td>
                  <td class="ingredient-amount">
                    {{ formatWeight(resource?.weight_brutto || 0, resource?.resource?.unit_alt_label || 'г') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Оборудование -->
        <div class="tech-card-section" v-if="techCard?.equipment?.length">
          <h2 class="section-title">Оборудование</h2>
          
          <div class="equipment-list">
            <div v-for="item in techCard.equipment" :key="item?.id" class="equipment-item">
              <div v-if="item?.photo?.url" class="equipment-image">
                <img :src="item.photo.url" :alt="item?.name || 'Оборудование'">
              </div>
              <div class="equipment-name">{{ item?.name || 'Без названия' }}</div>
            </div>
          </div>
        </div>
      </VWindowItem>
      
      <!-- Вкладка "Приготовление" -->
      <VWindowItem value="cooking">
        <!-- Приготовление -->
        <div class="tech-card-section">
          <h2 class="section-title">Пошаговое приготовление</h2>
          
          <div class="steps-list">
            <div v-for="step in steps" :key="step?.id" class="step-item">
              <div class="step-heading">
                <h3 class="step-name">{{ step?.name || 'Шаг без названия' }}</h3>
              </div>
              
              <div class="step-content">
                <div v-if="step?.photo?.url" class="step-image">
                  <PhotoDisplay 
                    :src="step.photo.url" 
                    aspect-ratio="1/1"
                    rounded="12px"
                    :alt="step?.name || 'Шаг приготовления'"
                  />
                </div>
                
                <div class="step-descriptions">
                  {{ step?.description || '' }}
                  
                  <div v-if="step?.description_notice" class="step-notice">
                    <VIcon icon="mdi-information" size="small" />
                    {{ step.description_notice }}
                  </div>

                  <!-- Таймер -->
                  <div v-if="step?.timer" class="step-timer">
                    <VIcon icon="mdi-timer" size="20" class="timer-icon" />
                    <div class="timer-content">
                      <div class="timer-name" v-if="step.timer?.name">{{ step.timer.name }}</div>
                      <div class="timer-time">
                        {{ formatStepTime(step.timer?.time) }}
                      </div>
                      <div class="timer-next" v-if="step.timer?.next_step_id">
                        Перейти к шагу {{ getNextStepNumber(step.timer.next_step_id) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </VWindowItem>
    </VWindow>
  </div>
</template>

<style scoped lang="scss">
.tech-card-view {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);

.tech-card-header {
  display: flex;
  gap: 30px;
  margin-bottom: 30px;
}

.tech-card-image {
  width: 350px;
  border-radius: 12px;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tech-card-info {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.tech-card-title {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 16px;
  color: var(--v-theme-primary);
  line-height: 1.2;
}

.tech-card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
  padding: 12px 16px;
  background-color: rgba(var(--v-theme-primary-rgb), 0.05);
  border-radius: 8px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--v-theme-on-surface-variant);
  font-size: 14px;
}

.tech-card-times {
  display: flex;
  gap: 24px;
  margin-bottom: 16px;
}

.time-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.time-label {
  font-weight: 500;
  color: var(--v-theme-on-surface-variant);
}

.time-value {
  font-weight: 600;
}

.tech-card-output, .tech-card-cost {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.output-label, .cost-label {
  font-weight: 500;
  color: var(--v-theme-on-surface-variant);
  min-width: 120px;
}

.output-value, .cost-value {
  font-weight: 600;
  font-size: 18px;
}

.cost-value {
  color: var(--v-theme-primary);
}

.servings-control {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
  padding: 12px 16px;
  background-color: rgba(var(--v-theme-primary-rgb), 0.05);
  border-radius: 8px;
}

.servings-label {
  font-weight: 500;
  color: var(--v-theme-on-surface-variant);
}

.servings-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

.servings-value {
  min-width: 24px;
  text-align: center;
  font-weight: 600;
  font-size: 18px;
  color: var(--v-theme-primary);
}

.tech-card-tabs {
  margin-bottom: 24px;
  border-bottom: 1px solid var(--v-theme-outline-variant);
}

.tech-card-content {
  min-height: 400px;
}

.tech-card-section {
  margin-bottom: 40px;
  padding-top: 20px;
}

.section-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 24px;
  color: var(--v-theme-primary);
  position: relative;
  padding-bottom: 12px;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 60px;
  height: 3px;
  background-color: var(--v-theme-primary);
  border-radius: 2px;
}

.description {
  line-height: 1.8;
  white-space: pre-line;
  font-size: 16px;
  color: var(--v-theme-on-surface);
  padding: 16px;
  background-color: rgba(var(--v-theme-on-surface-rgb), 0.02);
  border-radius: 8px;
}

.ingredient-group {
  margin-bottom: 32px;
}

.group-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 16px;
  color: var(--v-theme-on-surface);
  padding-left: 12px;
  border-left: 4px solid var(--v-theme-secondary);
}

.ingredients-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.ingredients-table thead {
  background-color: rgba(var(--v-theme-primary-rgb), 0.1);
}

.ingredients-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: var(--v-theme-on-surface);
}

.ingredients-table th:last-child {
  text-align: right;
}

.ingredient-row {
  border-bottom: 1px solid rgba(var(--v-theme-outline-variant-rgb), 0.5);
}

.ingredient-row:last-child {
  border-bottom: none;
}

.ingredient-name {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 500;
}

.ingredient-image {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.ingredient-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ingredient-amount {
  padding: 12px 16px;
  text-align: right;
  font-weight: 600;
  color: var(--v-theme-primary);
}

.additional .ingredients-table thead {
  background-color: rgba(var(--v-theme-secondary-rgb), 0.1);
}

.additional .ingredient-amount {
  color: var(--v-theme-secondary);
}

.steps-list {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.step-item {
  padding: 24px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  position: relative;
}

.step-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(to right, var(--v-theme-primary), var(--v-theme-secondary));
  border-radius: 4px 4px 0 0;
}

.step-heading {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 20px;
}

.step-number {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: var(--v-theme-primary);
  color: white;
  font-weight: 600;
  font-size: 18px;
  box-shadow: 0 2px 8px rgba(var(--v-theme-primary-rgb), 0.4);
}

.step-name {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
  color: var(--v-theme-on-surface);
  text-align: left;
}

.step-content {
  display: flex;
  gap: 24px;
}

.step-image {
  width: 240px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.step-descriptions {
  flex-grow: 1;
  line-height: 1.8;
  font-size: 16px;
}

.step-timer {
  margin-top: 16px;
  padding: 12px 16px;
  background-color: rgba(var(--v-theme-primary-rgb), 0.05);
  border-radius: 8px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.timer-icon {
  color: var(--v-theme-primary);
}

.timer-content {
  flex-grow: 1;
}

.timer-name {
  font-weight: 500;
  margin-bottom: 4px;
}

.timer-time {
  font-size: 18px;
  font-weight: 600;
  color: var(--v-theme-primary);
}

.timer-next {
  margin-top: 4px;
  font-size: 14px;
  color: var(--v-theme-on-surface-variant);
}

.step-notice {
  margin-top: 16px;
  padding: 12px 16px;
  background-color: rgba(var(--v-theme-info-rgb), 0.1);
  border-radius: 8px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-style: italic;
  border-left: 3px solid var(--v-theme-info);
}

.equipment-list {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  padding: 16px;
  background-color: rgba(var(--v-theme-on-surface-rgb), 0.02);
  border-radius: 8px;
}

.equipment-item {
  width: 140px;
  text-align: center;
  padding: 16px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease;
}

.equipment-item:hover {
  transform: translateY(-4px);
}

.equipment-image {
  width: 80px;
  height: 80px;
  margin: 0 auto 12px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.equipment-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.equipment-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--v-theme-on-surface);
}

@media (max-width: 960px) {
  .tech-card-header {
    flex-direction: column;
  }
  
  .tech-card-image {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .step-content {
    flex-direction: column;
  }
  
  .step-image {
    width: 100%;
  }
  
  .ingredients-table {
    font-size: 14px;
  }
  
  .ingredient-name {
    padding: 10px 12px;
  }
  
  .ingredient-amount {
    padding: 10px 12px;
  }
}


}

</style>
