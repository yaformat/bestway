// src/stores/kitchenStore.js
import { createStore } from './createStore'
import Kitchen from '@/models/Kitchen'

const customActions = {

};

export const useKitchenStore = createStore('KitchenStore', 'kitchen', Kitchen, customActions);
