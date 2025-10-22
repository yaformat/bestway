// src/stores/DirectionStore.js
import { createStore } from './createStore'
import Direction from '@/models/Direction'

const customActions = {

};

export const useDirectionStore = createStore('DirectionStore', 'directions', Direction, customActions);
