// src/stores/RestTypeStore.js
import { createStore } from './createStore'
import RestType from '@/models/RestType'

const customActions = {

};

export const useRestTypeStore = createStore('RestTypeStore', 'rest_types', RestType, customActions);
