// src/stores/TourStore.js
import { createStore } from './createStore'
import Tour from '@/models/Tour'

const customActions = {

};

export const useTourStore = createStore('TourStore', 'tours', Tour, customActions);
