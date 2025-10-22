// src/resources/js/services/apiService.js

import axiosIns from '@/plugins/axios';
import { useSnackbarStore } from '@/stores/snackbarStore';
import { useGlobalDataStore } from '@/stores/globalDataStore';

class ApiService {
    static refetchResourceTypes = [
        'resource_category', 
        'supplier', 
        'supplier_location'
    ];
    static isOnline = navigator.onLine; // Добавляем отслеживание состояния сети

    constructor(resource, model) {
        this.resource = resource;
        this.model = model;
        this.snackbarStore = useSnackbarStore();
        this.globalDataStore = useGlobalDataStore();

        // Инициализируем слушатели состояния сети
        this.setupNetworkListeners();
    }

    // Добавляем метод для настройки слушателей сети
    setupNetworkListeners() {
        window.addEventListener('online', () => {
            ApiService.isOnline = true;
            this.snackbarStore.showSnackbar('Подключение к интернету восстановлено', 'success');
        });

        window.addEventListener('offline', () => {
            ApiService.isOnline = false;
            this.snackbarStore.showSnackbar('Отсутствует подключение к интернету', 'error');
        });
    }

    // Проверка состояния сети перед запросом
    checkConnection() {
        if (!ApiService.isOnline) {
            const error = new Error('Отсутствует подключение к интернету');
            error.isNetworkError = true;
            return Promise.reject(error);
        }
        return Promise.resolve();
    }

    shouldRefetch() {
        return ApiService.refetchResourceTypes.includes(this.resource);
    }

    refetchGlobalData = () => {
        this.globalDataStore.fetchData();
    }

    handleError = (error, showError = true) => {
        if (showError) {
            // Определяем тип ошибки и показываем соответствующее сообщение
            let message;

            if (error.isNetworkError) {
                message = 'Отсутствует подключение к интернету';
            } else if (error.code === 'ECONNABORTED') {
                message = 'Превышено время ожидания запроса';
            } else if (!error.response) {
                message = 'Ошибка сети. Проверьте подключение к интернету';
            } else {
                message = error.response?.data?.message || 'Произошла ошибка';
            }

            console.error('API Error:', error);
            this.snackbarStore.showSnackbar(message, 'error');
        }
        return Promise.reject(error);
    }

    handleSuccess = (response) => {
        if (response.success && response.message) {
            this.snackbarStore.showSnackbar(response.message, 'success');
        }
        return response.data;
    }

    // Модифицируем существующие методы, добавляя проверку соединения

    get(endpoint, params, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.get(endpoint, { 
                params,
                timeout: 15000 // Увеличиваем timeout до 15 секунд
            }))
            .then(response => response.data)
            .catch(error => this.handleError(error, showError));
    }

    post(endpoint, data, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.post(endpoint, data, {
                timeout: 15000
            }))
            .then(response => response.data)
            .catch(error => this.handleError(error, showError));
    }

    put(endpoint, data, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.put(endpoint, data, {
                timeout: 15000
            }))
            .then(response => response.data)
            .catch(error => this.handleError(error, showError));
    }

    fetchAll(params, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.post(`/${this.resource}`, params))
            .then(response => {
                const data = this.handleSuccess(response);
                return {
                    ...data,
                    items: data.items.map(item => this.model.fromJson(item))
                };
            })
            .catch(error => this.handleError(error, showError));
    }


    fetchSingle(id, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.get(`/${this.resource}/${id}`))
            .then(response => {
                const data = this.handleSuccess(response);
                return this.model.fromJson(data);
            })
            .catch(error => this.handleError(error, showError));
    }

    create(data, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.put(`/${this.resource}`, data.toJson()))
            .then(response => {
                const data = this.handleSuccess(response);
                
                if (this.shouldRefetch()) this.refetchGlobalData();

                return this.model.fromJson(data.data);
            })
            .catch(error => this.handleError(error, showError));
    }

    update(id, data, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.put(`/${this.resource}/${id}`, data.toJson()))
            .then(response => {
                const data = this.handleSuccess(response);

                if (this.shouldRefetch()) this.refetchGlobalData();

                return this.model.fromJson(data.data);
            })
            .catch(error => this.handleError(error, showError));
    }

    delete(id, showError = true) {
        return this.checkConnection()
            .then(() => axiosIns.delete(`/${this.resource}/${id}`))
            .then(response => {
                this.handleSuccess(response);
                
                if (this.shouldRefetch()) this.refetchGlobalData();

                return response.data;
            })
            .catch(error => this.handleError(error, showError));
    }

    restore(id, showError = true) {
      return this.checkConnection()
          .then(() => axiosIns.post(`/${this.resource}/${id}/restore`))
          .then(response => {
              const data = this.handleSuccess(response);
                
              if (this.shouldRefetch()) this.refetchGlobalData();

              return this.model.fromJson(data.data);
          })
          .catch(error => this.handleError(error, showError));
    }

    toggleActive(id, status, showError = true) {
      return this.checkConnection()
          .then(() => axiosIns.post(`/${this.resource}/${id}/toggle-active`, { status }))
          .then(response => {
              const data = this.handleSuccess(response);
                
              if (this.shouldRefetch()) this.refetchGlobalData();

              return data;
          })
          .catch(error => this.handleError(error, showError));
    }

    // Upload photo with progress
    uploadPhoto(file, onUploadProgress) {
        return this.checkConnection()
            .then(() => {
                const formData = new FormData();
                formData.append('photo', file);

                return axiosIns.post(`/${this.resource}/upload`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    onUploadProgress,
                    timeout: 30000 // Увеличиваем timeout для загрузки файлов
                });
            })
            .then(response => response.data)
            .catch(error => this.handleError(error));
    }

    // Upload video with progress
    uploadVideo(file, onUploadProgress) {
        return this.checkConnection()
            .then(() => {
                const formData = new FormData();
                formData.append('video', file);

                return axiosIns.post(`/${this.resource}/upload`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    onUploadProgress,
                    timeout: 60000 // Увеличиваем timeout для загрузки видео
                });
            })
            .then(response => response.data)
            .catch(error => this.handleError(error));
    }

    // Добавляем метод для повторного выполнения запроса
    retryRequest(requestPromise, retries = 3, delay = 1000) {
        return new Promise((resolve, reject) => {
            requestPromise()
                .then(resolve)
                .catch((error) => {
                    if (retries === 0) {
                        return reject(error);
                    }

                    if (!ApiService.isOnline) {
                        // Если нет сети, ждем восстановления соединения
                        const onlineHandler = () => {
                            window.removeEventListener('online', onlineHandler);
                            setTimeout(() => {
                                this.retryRequest(requestPromise, retries - 1, delay)
                                    .then(resolve)
                                    .catch(reject);
                            }, delay);
                        };
                        window.addEventListener('online', onlineHandler);
                    } else {
                        // Если есть сеть, просто повторяем через delay
                        setTimeout(() => {
                            this.retryRequest(requestPromise, retries - 1, delay)
                                .then(resolve)
                                .catch(reject);
                        }, delay);
                    }
                });
        });
    }

    // Пример использования retry для важных запросов
    fetchCriticalData(params) {
        return this.retryRequest(
            () => this.fetchAll(params),
            3, // количество попыток
            1000 // задержка между попытками в мс
        );
    }
}

export default ApiService;
