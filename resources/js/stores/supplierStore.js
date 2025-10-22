// src/stores/supplierStore.js
import { createStore } from './createStore'
import Supplier from '@/models/Supplier'

const customActions = {

};

export const useSupplierStore = createStore('SupplierStore', 'supplier', Supplier, customActions);
