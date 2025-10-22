// src/stores/stockMinusStore.js
import { createStore } from './createStore'
import StockMinus from '@/models/StockMinus'

const customActions = {

  async updateMinus(id, data) {
    console.log('update minus...');

    const url = `/stock/minuses/${id}`;
    const url2 = `/stock/minuses/${id}`;

    try {
      const response = await this.$apiService.put(url, data);

      this.clearCache(url2);

      return response;
    } catch (error) {
      throw error;
    }
    
  },

  async completeMinus(id, data) {
    console.log('update minus...');

    const url = `/stock/minuses/${id}/complete`;
    const url2 = `/stock/minuses/${id}`;

    try {
      const response = await this.$apiService.put(url, data);

      this.clearCache(url2);

      return response;
    } catch (error) {
      throw error;
    }
    
  },

  async fetchResources(id, params = {}) {
    console.log('fetch minus resource...');

    const url = `/stock/minuses/${id}/resource`;
    const resourceName = `minuses/${id}/resource`; 

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

export const useStockMinusStore = createStore('StockMinusStore', 'stock/minuses', StockMinus, customActions);
