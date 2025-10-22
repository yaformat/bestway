// src/stores/workshopStore.js
import { createStore } from './createStore'
import Workshop from '@/models/Workshop'

const customActions = {

};

export const useWorkshopStore = createStore('WorkshopStore', 'workshop', Workshop, customActions);
