// src/stores/videoStore.js
import { defineStore } from 'pinia';
import ApiService from '@/services/apiService';

const apiService = new ApiService('video', null);

export const useVideoStore = defineStore('video', {
    state: () => ({
        uploadProgress: 0,
    }),
    actions: {
        async upload(file) {
            this.uploadProgress = 0;
            try {
                const response = await apiService.uploadVideo(file, (progressEvent) => {
                    this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                });
                return response;
            } catch (error) {
                console.error('Error uploading video:', error);
                throw error;
            } finally {
                this.uploadProgress = 0; // Reset progress after upload
            }
        },
        async delete(videoId) {
            try {
                await apiService.delete(videoId);
            } catch (error) {
                console.error('Error deleting video:', error);
                throw error;
            }
        },
    },
});
