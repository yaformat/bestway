// src/stores/resourceStore.js
import { createStore } from './createStore';
import Resource from '@/models/Resource';

const customActions = {
  async fetchCategoriesByType(type) {
    return this.fetchAll({ type });
  },
  async copy(data) {
    console.log(data);

    return {id:100, type: data.type, name: 'test copy resource'};
  },

  async fetchAll(params = {}) {
    console.log('fetch all resource... test');

    // Изменяем URL для включения типа ресурса, если он предоставлен
    const type = params?.type;
    const url = type ? `/resource/${type}` : `/resource/all`; // Используем /resource/<type> если тип есть, иначе /resource
    const resourceName = type ? `resource/${type}` : `resource/all`; 

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

  async fetchStockHistory(params) {
    console.log('fetch resource stocks...', params);
    
    // Формируем параметры для запроса
    const requestParams = {
      stock_id: params.stock_id,
      page: params.page || 1,
      limit: params.limit || 20
    };

    // Добавляем параметры для поиска конкретного действия, если они переданы
    if (params.action_type && params.action_id) {
      requestParams.action_type = params.action_type;
      requestParams.action_id = params.action_id;
    }

    return this.$apiService.post(`/resource/${params.resource_id}/stocks`, requestParams);
  },
  
  async fetchPriceHistory({ resource_id, stock_id, period = 'year', start_date = null, end_date = null, limit = 500, smart_limit = false }) {
    try {
      const requestParams = {
        stock_id,
        period,
        limit
      };

      if (start_date) requestParams.start_date = start_date;
      if (end_date) requestParams.end_date = end_date;
      if (smart_limit) requestParams.smart_limit = smart_limit;

      const response = await this.$apiService.post(`/resource/${resource_id}/price-history`, requestParams);
      return response;
    } catch (error) {
      console.error('Ошибка при загрузке истории цен:', error);
      throw error;
    }
  },

  
  async fetchAllResourceStocks(params) {
    return this.$apiService.post(`/resource/stocks`, params);
  }
};

export const useResourceStore = createStore('ResourceStore', 'resource', Resource, customActions);
