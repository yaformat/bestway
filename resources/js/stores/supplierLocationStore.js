// src/stores/supplierStore.js
import { createStore } from './createStore'
import SupplierLocation from '@/models/SupplierLocation'

const customActions = {

};

export const useSupplierLocationStore = createStore('SupplierLocationStore', 'supplier_location', SupplierLocation, customActions);
