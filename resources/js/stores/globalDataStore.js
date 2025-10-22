// src/stores/globalDataStore.js
import { defineStore } from 'pinia';
import ApiService from '@/services/apiService';
import { log } from '@/utils/logger';

export const useGlobalDataStore = defineStore('globalData', {
  state: () => ({
    users: [],
    stocks: [],
    units: [],
    suppliers: [],
    supplier_locations: [],
    resource_types: [],
    resource_losses: [],
    resource_categories: [],
    kitchens: [],
    workshops: [],
  }),
  actions: {
    async fetchData() {
      try {

        const apiService = new ApiService('global-data', null);
        
        log('fetching global data');
        
        const response = await apiService.get('/global-data')
        
        log('global data fetched');

        this.users = response.users || []
        this.stocks = response.stocks || []
        this.units = response.units || []
        this.suppliers = response.suppliers || []
        this.supplier_locations = response.supplier_locations || []
        this.resource_types = response.resource_types || []
        this.resource_losses = response.resource_losses || []
        this.resource_categories = response.resource_categories || []
        this.kitchens = response.kitchens || []
        this.workshops = response.workshops || []
        
        log('global data loaded')

      } catch (error) {
        log('Failed to fetch global data:');
      }
    },
  },
})
