// src/stores/hotelStore.js
import { createStore } from './createStore'
import Hotel from '@/models/Hotel'

const customActions = {

  /**
   * Загружает справочники для формы отеля
   */
  async fetchReferenceData(hotelId = null) {

    const apiService = await this.$apiService
    
    try {
      const params = hotelId ? { hotel_id: hotelId } : {};
      const response = await apiService.get('/hotels/reference-data', { params });
      return response;
    } catch (err) {

      throw err;
    }

  },

  /**
   * Получает детальную информацию об отеле
   */
  async getHotelWithDetails(id) {
    const apiService = await this.$apiService
    try {
      const response = await apiService.get(`/hotels/${id}`);
      return response;
    } catch (err) {
      throw err;
    }
  },

};

export const useHotelStore = createStore('HotelStore', 'hotels', Hotel, customActions);
