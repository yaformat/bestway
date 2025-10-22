// src/stores/stockTransferStore.js
import { createStore } from './createStore'
import StockTransfer from '@/models/StockTransfer'

const customActions = {

  async updateTransfer(id, data) {
    console.log('update transfer...');

    const url = `/stock/transfers/${id}`;
    const url2 = `/stock/transfers/${id}`;
    this.clearCache(url2);
  },

  async fetchResources(id, params = {}) {
    console.log('fetch transfer resource...');

    const url = `/stock/transfers/${id}/resource`;
    const resourceName = `transfers/${id}/resource`; 

    // Generate cache key
    const cacheKey = this.generateCacheKey(resourceName, params);
    
    // Check cache
    const cachedData = this.getCachedData(resourceName, cacheKey);
    if (cachedData) {
      return cachedData;
    }

    try {
      const response = await this.$apiService.post(url, params);
      // Save to cache
      this.setCachedData(resourceName, cacheKey, response);
      return response;
    } catch (error) {
      throw error;
    }
  },

};

export const useStockTransferStore = createStore('StockTransferStore', 'stock/transfers', StockTransfer, customActions);
