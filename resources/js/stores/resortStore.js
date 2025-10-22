// src/stores/ResortStore.js
import { createStore } from './createStore'
import Resort from '@/models/Resort'

const customActions = {

};

export const useResortStore = createStore('ResortStore', 'resorts', Resort, customActions);
