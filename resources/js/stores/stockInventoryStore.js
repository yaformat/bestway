// src/stores/stockInventoryStore.js
import { createStore } from './createStore'
import StockInventory from '@/models/StockInventory'

const customActions = {

  async updateInventory(id, data) {
    console.log('update inventory...');

    const url = `/stock/inventories/${id}`;
    const url2 = `/stock/inventories/${id}`;

    try {
      const response = await this.$apiService.put(url, data);

      this.clearCache(url2);

      return response;
    } catch (error) {
      throw error;
    }
    
  },

  async completeInventory(id, data) {
    console.log('update minus...');

    const url = `/stock/inventories/${id}/complete`;
    const url2 = `/stock/inventories/${id}`;

    try {
      const response = await this.$apiService.put(url, data);

      this.clearCache(url2);

      return response;
    } catch (error) {
      throw error;
    }
    
  },

  async fetchResources(id, params = {}) {
    console.log('fetch inventory resource...');

    const url = `/stock/inventories/${id}/resource`;
    const resourceName = `inventories/${id}/resource`; 

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

export const useStockInventoryStore = createStore('StockInventoryStore', 'stock/inventories', StockInventory, customActions);
