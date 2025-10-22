// src/stores/photoStore.js
import { defineStore } from 'pinia';

export const usePhotoStore = defineStore('photo', {
    state: () => ({
        uploadProgress: 0,
    }),
    
    actions: {
        _apiService: null,
        
        async getApiService() {
            if (!this._apiService) {
                const ApiServiceModule = await import('@/services/apiService');
                const ApiService = ApiServiceModule.default;
                this._apiService = new ApiService('photo', null);
            }
            return this._apiService;
        },
        
        async upload(file) {
            this.uploadProgress = 0;
            try {
                const apiService = await this.getApiService();
                const response = await apiService.uploadPhoto(file, (progressEvent) => {
                    this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                });
                return response;
            } catch (error) {
                console.error('Error uploading photo:', error);
                throw error;
            } finally {
                this.uploadProgress = 0;
            }
        },
        
        async fetch(photoId) {
            try {
                const apiService = await this.getApiService();
                const response = await apiService.get(`photo/${photoId}`);
                return response;
            } catch (error) {
                console.error('Error fetching photo:', error);
                throw error;
            }
        },
        
        async delete(photoId) {
            try {
                const apiService = await this.getApiService();
                await apiService.delete(photoId);
            } catch (error) {
                console.error('Error deleting photo:', error);
                throw error;
            }
        },
        
        async rotateImage(photoId, angle) {
            try {
                const apiService = await this.getApiService();
                await apiService.post(`photo/${photoId}/rotate`, { angle });
                return true;
            } catch (error) {
                console.error('Error rotating image:', error);
                throw error;
            }
        },
    },
});
