// src/stores/hotelStore.js
import { createStore } from './createStore'
import Hotel from '@/models/Hotel'

const customActions = {

};

export const useHotelStore = createStore('HotelStore', 'hotels', Hotel, customActions);
