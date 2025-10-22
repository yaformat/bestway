// src/stores/resourceCategoryStore.js
import { createStore } from './createStore'
import ResourceCategory from '@/models/ResourceCategory'

const customActions = {
  fetchCategoriesByType(type) {
    return this.fetchAll({ type });
  },

  async updateTree(data) {
    console.log(data);
  },

  async fetchAll(params = {}) {
    console.log('fetch all resource... test');

    // Изменяем URL для включения типа ресурса, если он предоставлен
    const type = params?.type;
    const url = type ? `/resource_category/${type}` : `/resource_category`; // Используем /resource_category/<type> если тип есть, иначе /resource_category
    const resourceName = type ? `resource_category/${type}` : `resource_category`; 

    // Generate cache key
    const cacheKey = this.generateCacheKey(resourceName, params);
    
    // Check cache
    const cachedData = this.getCachedData(resourceName, cacheKey);
    if (cachedData) {
      return cachedData;
    }

    try {
      const response = await this.$apiService.post(resourceName, params);
      // Save to cache
      this.setCachedData(resourceName, cacheKey, response);
      return response;
    } catch (error) {
      throw error;
    }
  },
};

export const useResourceCategoryStore = createStore('ResourceCategoryStore', 'resource_category', ResourceCategory, customActions);
