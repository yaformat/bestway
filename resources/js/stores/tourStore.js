// src/stores/tourStore.js
import { createStore } from './createStore'
import Tour from '@/models/Tour'

const customActions = {
  async saveProgram(tourId, program) {
    const apiService = await this.$apiService
    const response = await apiService.post(`${this.resource}/${tourId}/program`, { program })
    return response
  },
  
  async uploadPhoto(tourId, formData) {
    const apiService = await this.$apiService
    const response = await apiService.post(`${this.resource}/${tourId}/photo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response
  },
  
  async deletePhoto(tourId, photoId) {
    const apiService = await this.$apiService
    const response = await apiService.delete(`${this.resource}/${tourId}/photo/${photoId}`)
    return response
  },
  
  async copy(tourId) {
    const apiService = await this.$apiService
    const response = await apiService.post(`${this.resource}/${tourId}/copy`)
    return response
  }
}

export const useTourStore = createStore('TourStore', 'tours', Tour, customActions)