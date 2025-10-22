// src/stores/userProfileStore.js
import { defineStore } from 'pinia';

export const useUserProfileStore = defineStore('user', {
    state: () => ({
        user: {
          first_name: '',
          last_name: '',
          middle_name: '',
          email: '',
          locale: '',
          photo: null,
        },
        userDataLoaded: false,
    }),

    actions: {
        _apiService: null,
        
        async getApiService() {
            if (!this._apiService) {
                // Используем динамический импорт вместо require
                const ApiServiceModule = await import('@/services/apiService');
                const ApiService = ApiServiceModule.default;
                this._apiService = new ApiService('user', null);
            }
            return this._apiService;
        },

        async fetchUserProfile() {
            this.userDataLoaded = false;
            try {
                const apiService = await this.getApiService();
                const response = await apiService.get('/user/profile');
                this.user = response;
                console.log(`User Profile Fetch: ${this.user.first_name}`);
            } catch (error) {
                let msg = error.response ? error.response.data.message : error.message;
                console.log(`User Profile Fetch Error: ${msg}`);
            } finally {
                this.userDataLoaded = true;
            }
            return this.user;
        },

        async updateUserProfile(updatedData) {
            this.userDataLoaded = false;
            try {
                const apiService = await this.getApiService();
                const response = await apiService.put('/user/profile', updatedData);
                this.user = response;
                console.log(`User Profile Update: ${this.user.first_name}`);
            } catch (error) {
                let msg = error.response ? error.response.data.message : error.message;
                console.log(`User Profile Update Error: ${msg}`);
            } finally {
                this.userDataLoaded = true;
            }
            return this.user;
        },
    },
});
