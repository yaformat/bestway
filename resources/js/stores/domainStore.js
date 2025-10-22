// src/stores/domainStore.js
import { createStore } from './createStore'
import Domain from '@/models/Domain'

const customActions = {
  async updateSettings(domainId, settings) {
    const apiService = await this.$apiService
    const response = await apiService.put(`${this.resource}/${domainId}/settings`, { settings })
    return response
  },
  
  async clearCache(domainId) {
    const apiService = await this.$apiService
    const response = await apiService.post(`${this.resource}/${domainId}/clear-cache`)
    return response
  },
  
  async toggleMaintenanceMode(domainId, status, message = null) {
    const apiService = await this.$apiService
    const response = await apiService.put(`${this.resource}/${domainId}/maintenance`, {
      status,
      message
    })
    return response
  },
  
  async uploadLogo(domainId, formData) {
    const apiService = await this.$apiService
    const response = await apiService.post(`${this.resource}/${domainId}/logo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response
  },
  
  async deleteLogo(domainId) {
    const apiService = await this.$apiService
    const response = await apiService.delete(`${this.resource}/${domainId}/logo`)
    return response
  }
}

export const useDomainStore = createStore('DomainStore', 'domains', Domain, customActions)