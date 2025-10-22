// src/stores/stockStore.js
import { createStore } from './createStore'
import { useCache } from '@/mixins/useCache';
import Stock from '@/models/Stock'

const customActions = {

    async togglePrimary(id) {
        console.log('processing primary stock');
        const params = {};
        const response = this.$apiService.post(`/stock/${id}/primary`, params);
        useCache.clearCache(`stock`);
        return response;
    },
    async toggleProduction(id) {
        console.log('processing production stock');
        const params = {};
        const response = this.$apiService.post(`/stock/${id}/production`, params);
        useCache.clearCache(`stock`);
        return response;
    },
    async toggleSemiFinished(id) {
        console.log('processing semi-finished stock');
        const params = {};
        const response = this.$apiService.post(`/stock/${id}/semi_finished`, params);
        useCache.clearCache(`stock`);
        return response;
    }

};

export const useStockStore = createStore('StockStore', 'stock', Stock, customActions);
