// src/stores/userStore.js
import { createStore } from './createStore'
import User from '@/models/User'

const customActions = {

};

export const useUserStore = createStore('UserStore', 'user', User, customActions);
