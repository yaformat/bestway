// src/stores/productionStore.js
import { createStore } from './createStore'
import Production from '@/models/Production'

const customActions = {
    async fetchSingle(productionId) {
        try {
            const response = await this.$apiService.get(`production/${productionId}`);
            return response;
        } catch (error) {
            console.error('Error fetching photo:', error);
            throw error;
        }
    },

    // получение производства на дату
    async fetchByDate(date) {
        try {
            const response = await this.$apiService.get(`/production/date/${date}`)
            return response
        } catch (error) {
            console.error('Ошибка получения производства на дату:', error)
            throw error
        }
    },
    
    async fetchByDateRange(startDate, endDate) {
        try {
            const response = await this.$apiService.get(`/production/range/${startDate}/${endDate}`)
            return response
        } catch (error) {
            console.error('Ошибка получения производства на диапазон дат:', error)
            throw error
        }
    },

    // Обновление заметок производства
    async updateNotes(productionId, notes) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/notes`, {
                notes
            })
            return response
        } catch (error) {
            console.error('Ошибка обновления заметок:', error)
            throw error
        }
    },

    // Обновление статуса блюда
    async updateDishStatus(productionId, dishId, status) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/tech_card_resources/${dishId}/status`, {
                status
            })
            return response
        } catch (error) {
            console.error('Ошибка обновления статуса блюда:', error)
            throw error
        }
    },

    // Обновление количества блюда (новый метод с поддержкой quantity_guest и quantity_staff)
    async updateDishQuantity(date, dishId, quantityData) {
        try {
            const response = await this.$apiService.post(`/production/date/${date}/tech_card_resources/${dishId}/quantity`, {
                quantity_guest: quantityData.quantity_guest || 0,
                quantity_staff: quantityData.quantity_staff || 0
            })
            return response
        } catch (error) {
            console.error('Ошибка обновления количества блюда:', error)
            throw error
        }
    },

    // Добавление блюд в период (обновленный метод)
    async addDishesToPeriod(productionId, periodId, tech_card_resources) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/periods/${periodId}/tech_card_resources`, {
                tech_card_resources: tech_card_resources.map(tech_card_resource => ({
                    tech_card_resource_id: tech_card_resource.id || tech_card_resource.tech_card_resource_id,
                    quantity_guest: tech_card_resource.quantity_guest || tech_card_resource.quantity || 0,
                    quantity_staff: tech_card_resource.quantity_staff || 0
                }))
            })
            return response
        } catch (error) {
            console.error('Ошибка добавления блюд в период:', error)
            throw error
        }
    },

    async createForDate(date, data) {
        console.log('createForDate', data);
        try {
            const response = await this.$apiService.post(`/production/date/${date}/create`, data)
            return response
        } catch (error) {
            console.error('Ошибка добавления блюд в период:', error)
            throw error
        }
    },
    async updateForDate(date, data) {
        console.log('updateForDate', data);
        try {
            const response = await this.$apiService.post(`/production/date/${date}/update`, data)
            return response
        } catch (error) {
            console.error('Ошибка добавления блюд в период:', error)
            throw error
        }
    },

    // Удаление блюда из периода
    async removeDish(date, dishId) {
        try {
            const response = await this.$apiService.delete(`date/${date}/tech_card_resources/${dishId}`)
            return response
        } catch (error) {
            console.error('Ошибка удаления блюда:', error)
            throw error
        }
    },

    // Добавление блюд в период (старый метод для обратной совместимости)
    async addDishesToPeriodLegacy(productionId, periodId, tech_card_resources) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/periods/${periodId}/tech_card_resources`, {
                tech_card_resources: tech_card_resources.map(tech_card_resource => ({
                    tech_card_resource_id: tech_card_resource.id,
                    quantity_guest: tech_card_resource.quantity || 1,
                    quantity_staff: 0
                }))
            })
            return response
        } catch (error) {
            console.error('Ошибка добавления блюд в период:', error)
            throw error
        }
    },

    // Обновление периода
    async updatePeriod(productionId, periodId, periodData) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/periods/${periodId}`, periodData)
            return response
        } catch (error) {
            console.error('Ошибка обновления периода:', error)
            throw error
        }
    },

    // Добавление нового периода
    async addPeriod(productionId, periodData) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/periods`, periodData)
            return response
        } catch (error) {
            console.error('Ошибка добавления периода:', error)
            throw error
        }
    },

    // Удаление периода
    async removePeriod(productionId, periodId) {
        try {
            const response = await this.$apiService.delete(`${productionId}/periods/${periodId}`)
            return response
        } catch (error) {
            console.error('Ошибка удаления периода:', error)
            throw error
        }
    },

    // Обновление порядка блюд в периоде
    async updateDishesOrder(productionId, periodId, dishesOrder) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/periods/${periodId}/tech_card_resources-order`, {
                tech_card_resources: dishesOrder.map((tech_card_resource, index) => ({
                    id: tech_card_resource.id,
                    sort_order: index
                }))
            })
            return response
        } catch (error) {
            console.error('Ошибка обновления порядка блюд:', error)
            throw error
        }
    },

    // Создание производства (обновленный метод)
    async create(productionData) {
        try {
            // Преобразуем данные для поддержки новых полей
            const transformedData = {
                ...productionData,
                periods: productionData.periods?.map(period => ({
                    ...period,
                    tech_card_resources: period.tech_card_resources?.map(resource => ({
                        tech_card_resource_id: resource.tech_card_resource_id || resource.id,
                        quantity_guest: resource.quantity_guest || resource.quantity || 0,
                        quantity_staff: resource.quantity_staff || 0
                    }))
                }))
            }

            const response = await this.$apiService.put('/production', transformedData)
            return response
        } catch (error) {
            console.error('Ошибка создания производства:', error)
            throw error
        }
    },

    // Обновление производства (обновленный метод)
    async update(productionId, productionData) {
        try {
            // Преобразуем данные для поддержки новых полей
            const transformedData = {
                ...productionData,
                periods: productionData.periods?.map(period => ({
                    ...period,
                    tech_card_resources: period.tech_card_resources?.map(resource => ({
                        tech_card_resource_id: resource.tech_card_resource_id || resource.id,
                        quantity_guest: resource.quantity_guest || resource.quantity || 0,
                        quantity_staff: resource.quantity_staff || 0,
                        status: resource.status
                    }))
                }))
            }

            const response = await this.$apiService.put(`/production/${productionId}`, transformedData)
            return response
        } catch (error) {
            console.error('Ошибка обновления производства:', error)
            throw error
        }
    },

    // Получение статистики производства
    async getProductionStats(productionId) {
        try {
            const response = await this.$apiService.get(`/production/${productionId}/stats`)
            return response
        } catch (error) {
            console.error('Ошибка получения статистики производства:', error)
            throw error
        }
    },

    // Массовое обновление статуса блюд
    async bulkUpdateDishStatus(productionId, dishIds, status) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/tech_card_resources/bulk-status`, {
                dish_ids: dishIds,
                status
            })
            return response
        } catch (error) {
            console.error('Ошибка массового обновления статуса блюд:', error)
            throw error
        }
    },

    // Массовое удаление блюд
    async bulkRemoveDishes(productionId, dishIds) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/tech_card_resources/bulk-delete`, {
                dish_ids: dishIds
            })
            return response
        } catch (error) {
            console.error('Ошибка массового удаления блюд:', error)
            throw error
        }
    },

    // Проверка доступности ингредиентов для блюда
    async checkDishAvailability(productionId, dishId) {
        try {
            const response = await this.$apiService.get(`/production/${productionId}/tech_card_resources/${dishId}/availability`)
            return response
        } catch (error) {
            console.error('Ошибка проверки доступности ингредиентов:', error)
            throw error
        }
    },

    // Получение детальной информации о блюде в производстве
    async getDishDetails(productionId, dishId) {
        try {
            const response = await this.$apiService.get(`/production/${productionId}/tech_card_resources/${dishId}`)
            return response
        } catch (error) {
            console.error('Ошибка получения деталей блюда:', error)
            throw error
        }
    },

    async transferIngredientsToWorkshop(productionId, transferData) {
        try {
            const response = await this.$apiService.post(`/production/${productionId}/transfer-ingredients-to-workshop`, transferData)
            console.log('Response from transferIngredientsToWorkshop:', response)
            return response
        } catch (error) {
            console.error('Ошибка перемещения ингредиентов:', error)
            throw error
        }
    }

};

export const useProductionStore = createStore('ProductionStore', 'production', Production, customActions);
