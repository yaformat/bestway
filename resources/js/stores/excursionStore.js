// src/stores/ExcursionStore.js
import { createStore } from './createStore'
import Excursion from '@/models/Excursion'

const customActions = {

};

export const useExcursionStore = createStore('ExcursionStore', 'excursions', Excursion, customActions);
