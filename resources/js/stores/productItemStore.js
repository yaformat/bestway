// src/stores/productItemStore.js
import { createStore } from './createStore'
import ProductItem from '@/models/ProductItem'

const customActions = {

};

export const useProductItemStore = createStore('ProductItemStore', 'product_item', ProductItem, customActions);
