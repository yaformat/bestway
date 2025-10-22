// src/stores/stockSupplyStore.js
import { createStore } from './createStore'
import StockSupply from '@/models/StockSupply'
import { useCache } from '@/mixins/useCache';

const customActions = {

  async processing(id) {
    console.log('processing supply');
    const params = {};
    const response = this.$apiService.post(`/stock/supplies/${id}/process`, params);
    useCache.clearCache(`stock/supplies`);
    return response;
  },

  async purchase(id, data) {
    console.log('purchase for supply');
    const response = this.$apiService.post(`/stock/supplies/${id}/purchases`, data);
    useCache.clearCache(`stock/supplies`);
    return response;
  },

  async complete(id) {
    console.log('completing supply');
    const params = {};
    return this.$apiService.post(`/stock/supplies/${id}/complete`, params);
  },

};

export const useStockSupplyStore = createStore('StockSupplyStore', 'stock/supplies', StockSupply, customActions);
