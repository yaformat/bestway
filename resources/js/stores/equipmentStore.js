// src/stores/equipmentStore.js
import { createStore } from './createStore'
import Equipment from '@/models/Equipment'

const customActions = {

};

export const useEquipmentStore = createStore('EquipmentStore', 'equipment', Equipment, customActions);
