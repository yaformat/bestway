<template>
  <VDialog v-model="internalShowModal" max-width="900px" class="resource-dialog">
    <VCard>
      <VToolbar color="primary" class="dialog-toolbar">
        <VToolbarTitle>История движения РЕСУРСА</VToolbarTitle>
        <VSpacer />
        <VToolbarItems>
            <VBtn icon variant="plain" @click="closeDialog">
                <VIcon color="white" icon="mdi-close" />
            </VBtn>
          <!-- <VBtn variant="text" @click="closeDialog">OK</VBtn> -->
        </VToolbarItems>
      </VToolbar>
      <VCardText class="dialog-content">
        <div class="stock-buttons">
            <VBtn
                v-for="stock in stocks"
                :key="stock.id"
                @click="selectStock(stock.id)"
                :class="{ 'selected-stock': selectedStockId === stock.id }"
            >
                {{ stock.name }}
            </VBtn>
        </div>
        <VTable class="table">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Тип</th>
                <th>Количество</th>
                <th>Цена</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in historyItems" :key="item.id" class="mb-4">
                <td><FormattedDate :date="item.created_at" /></td>
                <td>{{ item.action_label }}</td>
                <td> {{ item.value.display }}</td>
                <td> {{ item.price.display }}</td>
            </tr>
            </tbody>
        </VTable>
        <div v-if="loading" class="loader-container">
          <VProgressCircular indeterminate color="primary" />
        </div>
        <VBtn :disabled="loading || !hasMore" @click="loadMore">Показать еще</VBtn>
      </VCardText>
      <VCardActions>
        <!-- <VBtn color="primary" @click="closeDialog">Закрыть</VBtn> -->
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useResourceStore } from '@/stores/resourceStore';
import { VProgressCircular } from 'vuetify/components';

const props = defineProps({
  resourceId: {
    type: Number,
    required: true,
  },
  stocks: {
    type: Array,
    required: true,
  },
  showModal: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['update:showModal']);

const internalShowModal = ref(props.showModal);
const selectedStockId = ref(null);
const historyItems = ref([]);
const loading = ref(false);
const page = ref(1);
const hasMore = ref(true);
const store = useResourceStore();

const fetchHistory = async (stockId, page) => {
  loading.value = true;
  const response = await store.fetchStockHistory({ resource_id: props.resourceId, stock_id: stockId, page });
  historyItems.value = [...historyItems.value, ...response.items];
  if (historyItems.value.length >= response.total_count) {
    hasMore.value = false;
  }
  loading.value = false;
};

const selectStock = (stockId) => {
  selectedStockId.value = stockId;
  historyItems.value = [];
  page.value = 1;
  hasMore.value = true;
  fetchHistory(stockId, page.value);
};

const loadMore = () => {
  page.value += 1;
  fetchHistory(selectedStockId.value, page.value);
};

const closeDialog = () => {
  internalShowModal.value = false;
  emit('update:showModal', false);
};

watch(() => props.showModal, (newVal) => {
  internalShowModal.value = newVal;
  if (newVal && props.stocks.length > 0) {
    selectStock(props.stocks[0].id);
  }
});

watch(internalShowModal, (newVal) => {
  if (!newVal) {
    emit('update:showModal', false);
  }
});
</script>

<style scoped>
.dialog-toolbar {
  position: sticky;
  top: 0;
  z-index: 1;
}

.resource-dialog .dialog-content {
  max-height: 60vh;
  overflow-y: auto;
}

.stock-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
}

.selected-stock {
  background-color: #1976d2;
  color: white;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.history-item {
  padding: 8px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
}
</style>
