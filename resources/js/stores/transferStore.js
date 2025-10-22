// src/stores/transferStore.js
import { createStore } from './createStore'
import Transfer from '@/models/Transfer'

const customActions = {

};

export const useTransferStore = createStore('TransferStore', 'transfers', Transfer, customActions);
