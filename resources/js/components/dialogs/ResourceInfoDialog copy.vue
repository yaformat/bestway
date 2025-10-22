<template>
  <span class="resource-name-trigger" @click="openDialog">{{ name }}</span>
  <VDialog 
    v-model="showModal" 
    scroll-strategy="none"
    max-width="800px"
  >
    <VCard>
      <VToolbar color="primary" class="dialog-toolbar">
        <VToolbarTitle>Информация о РЕСУРСА</VToolbarTitle>
        <VSpacer />
        <VToolbarItems>
          <!-- <VBtn variant="text" @click="showModal = false">OK</VBtn> -->
          <VBtn icon variant="plain" @click="showModal = false">
            <VIcon color="white" icon="mdi-close" />
          </VBtn>
        </VToolbarItems>
      </VToolbar>
      <PerfectScrollbar
        :options="{ wheelPropagation: false }"
      >
      <VCardText>
        <div v-if="loading" class="loader-container">
          <VProgressCircular indeterminate color="primary" />
        </div>
        <div v-else class="dialog-content d-flex">
          <div class="dialog-img-container">
            <img v-if="photoUrl" v-lazy="photoUrl" class="dialog-img" />
          </div>
          <div class="details">
            <div v-if="details">
              <h3>{{ details.name }}</h3>
              <p><strong>Категория:</strong> {{ details.category.name }}</p>
              <p v-if="details.category.parent"><strong>Подкатегория:</strong> {{ details.category.parent.name }}</p>
              <p><strong>Единица измерения:</strong> {{ details.unit }}</p>
              <p><strong>Минимальный запас:</strong> {{ details.min_stock }}</p>
              <p v-if="details.avg_price"><strong>Средняя цена:</strong> {{ details.avg_price.display }}</p>
              <p v-if="details.last_price"><strong>Последняя цена:</strong> {{ details.last_price.display }}</p>
              <p v-if="details.stocks_total_price"><strong>Общая стоимость запасов:</strong> {{ details.stocks_total_price.display }}</p>
              <p v-if="details.stocks_total_value"><strong>Общее количество запасов:</strong> {{ details.stocks_total_value.display }}</p>
              <p v-if="details.min_value"><strong>Минимальное количество:</strong> {{ details.min_value.display }}</p>
              <p v-if="details.deficit_value"><strong>Дефицит:</strong> {{ details.deficit_value.display }}</p>
              <p><strong>Потери:</strong></p>
              <ul class="styled-list">
                <li v-for="loss in details.losses" :key="loss.id">{{ loss.name }}: {{ loss.value }}%</li>
              </ul>
              <p><strong>Запасы:</strong></p>
              <ul class="styled-list">
                <li v-for="stock in details.stocks" :key="stock.id">{{ stock.name }}: {{ stock.value.display }} ({{ stock.price.display }})</li>
              </ul>
            </div>
          </div>
        </div>
      </VCardText>
      </PerfectScrollbar>
      <VCardActions>
        <!-- <VBtn color="primary" @click="showModal = false">Закрыть</VBtn> -->
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { ref, computed } from 'vue';
import { useResourceStore } from '@/stores/resourceStore';
import { VProgressCircular } from 'vuetify/components';

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
  }
});

const showModal = ref(false);
const details = ref(null);
const loading = ref(true);
const store = useResourceStore();

const openDialog = async () => {
  if(!props.allowOpen) return false;

  showModal.value = true;
  details.value = await store.fetchSingle(props.id);
  loading.value = false;
};

const photoUrl = computed(() => {
  return details.value ? details.value.photo?.url : '';
});
</script>

<style scoped>
.dialog-toolbar {
  position: sticky;
  top: 0;
  z-index: 1;
}

.dialog-img-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 16px;
}

.dialog-img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
}

.details {
  flex: 2;
  padding: 16px;
}

.details h3 {
  margin-top: 0;
}

.styled-list {
  list-style-type: none;
  padding: 0;
}

.styled-list li {
  padding: 4px 0;
  border-bottom: 1px solid #e0e0e0;
}

.resource-name-trigger {
  cursor: pointer;
  border-bottom: 1px solid transparent;
}

.resource-name-trigger:hover {
  border-color: inherit;
  transition: all 0.3s;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
}
</style>
