// src/stores/organizationStore.js
import { createStore } from './createStore'
import Organization from '@/models/Organization'

const customActions = {

};

export const useOrganizationStore = createStore('OrganizationStore', 'organization', Organization, customActions);
