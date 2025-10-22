// src/stores/createStore.js
import { defineStore } from 'pinia';
import { useCache } from '@/mixins/useCache';

export function createStore(storeName, resource, Model, customActions = {}) {
  // Функция для получения экземпляра ApiService при необходимости
  const getApiService = async () => {
    // Используем динамический импорт вместо require
    const ApiServiceModule = await import('@/services/apiService');
    const ApiService = ApiServiceModule.default;
    return new ApiService(resource, Model);
  };

  const defaultActions = {
    async fetchAll(params = {}, showError = true) {
      const cacheKey = useCache.generateCacheKey(resource, params);
      const cachedData = useCache.getCachedData(resource, cacheKey);
      if (cachedData) {
        this.items = cachedData;
        return this.items;
      }

      this.loading = true;
      this.error = null;
      try {
        // Получаем ApiService только при вызове метода
        const apiService = await getApiService();
        const response = await apiService.fetchAll(params, showError);
        useCache.setCachedData(resource, cacheKey, response);
        this.items = response;
        return response;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchSingle(id, showError = true) {
      const cacheKey = useCache.generateCacheKey(resource, { id });
      const cachedData = useCache.getCachedData(resource, cacheKey);
      if (cachedData) {
        this.item = cachedData;
        return this.item;
      }

      this.loading = true;
      this.error = null;
      try {
        const apiService = await getApiService();
        const response = await apiService.fetchSingle(id, showError);
        useCache.setCachedData(resource, cacheKey, response);
        this.item = response;
        return response;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async create(data, showError = true) {
      this.loading = true;
      this.error = null;
      try {
        const apiService = await getApiService();
        const response = await apiService.create(new Model(data), showError);
        useCache.clearCache(resource);
        return response;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async update(id, data, showError = true) {
      this.loading = true;
      this.error = null;
      try {
        const apiService = await getApiService();
        const response = await apiService.update(id, new Model(data), showError);
        useCache.clearCache(resource);
        return response;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async delete(id, showError = true) {
      this.loading = true;
      this.error = null;
      try {
        const apiService = await getApiService();
        const response = await apiService.delete(id, showError);
        useCache.clearCache(resource);
        return response;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async restore(id, showError = true) {
      this.loading = true;
      this.error = null;
      try {
        const apiService = await getApiService();
        const response = await apiService.restore(id, showError);
        useCache.clearCache(resource);
        return response;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async toggleActive(id, status, showError = true) {
      this.loading = true;
      this.error = null;
      try {
        const apiService = await getApiService();
        const response = await apiService.toggleActive(id, status, showError);
        useCache.clearCache(resource);
        return response;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },
  };

  // Модифицируем обработку пользовательских действий
  const processCustomActions = () => {
    const boundCustomActions = {};
    for (const [key, action] of Object.entries(customActions)) {
      boundCustomActions[key] = async function(...args) {
        const apiService = await getApiService();
        return action.apply({
          $apiService: apiService,
          resource,
          ...useCache,
        }, args);
      };
    }
    return boundCustomActions;
  };

  return defineStore(storeName, {
    state: () => ({
      items: [],
      item: null,
      loading: false,
      error: null,
    }),
    actions: {
      ...defaultActions,
      ...processCustomActions(),
    },
  });
}
