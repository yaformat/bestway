// src/models/Domain.js
import BaseModel from './BaseModel';

class Domain extends BaseModel {
  constructor(data = {}) {
    this.id = data.id || null
    this.name = data.name || ''
    this.domain = data.domain || ''
    this.description = data.description || ''
    this.is_default = data.is_default ?? false
    this.is_active = data.is_active ?? true
    this.sort_order = data.sort_order || 0
    this.settings = data.settings || {}
    this.created_at = data.created_at || null
    this.updated_at = data.updated_at || null
  }

  /**
   * Получает настройку по ключу
   */
  getSetting(key, defaultValue = null) {
    return this.settings?.[key] ?? defaultValue
  }
  
  /**
   * Устанавливает настройку
   */
  setSetting(key, value) {
    if (!this.settings) {
      this.settings = {}
    }
    this.settings[key] = value
  }
  
  /**
   * Получает все настройки
   */
  getAllSettings() {
    return this.settings || {}
  }
  
  /**
   * Проверяет, является ли домен текущим
   */
  isCurrent() {
    return window.location.host === this.domain
  }
  
  /**
   * Получает базовый URL домена
   */
  getBaseUrl() {
    const protocol = window.location.protocol
    return `${protocol}//${this.domain}`
  }

}

export default Domain;
