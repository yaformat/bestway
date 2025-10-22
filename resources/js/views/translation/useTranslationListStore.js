import { defineStore } from 'pinia'
import axiosIns from '@/plugins/axios'

export const useTranslationListStore = defineStore('TranslationListStore',{
    actions: {
        // 👉 Fetch translation
        fetchDirectories(params) {
            return axiosIns.get('/translation', {params})
        },

        // 👉 fetch single translation
        fetchSingleTranslation(id) {
            return new Promise((resolve, reject) => {
                axiosIns.get(`/translation/${id}/edit`).then(response => resolve(response)).catch(error => reject(error))
            })
        },

        // 👉 Add translation
        addTranslation(translationData) {
            return new Promise((resolve, reject) => {
                axiosIns.post('/translation/create', translationData, {})
                .then(response => resolve(response))
                .catch(error => reject(error))
            })
        },

        // 👉 Edit translation
        editTranslation(translationData) {
            return new Promise((resolve, reject) => {
                axiosIns.post('/translation/update', translationData, {})
                .then(response => resolve(response))
                .catch(error => reject(error))
            })
        },

        // 👉 Delete translation
        deleteTranslation(id) {
            return new Promise((resolve, reject) => {
                axiosIns.delete(`/translation/${id}/delete`).then(response => resolve(response)).catch(error => reject(error))
            })
        },
    },
})