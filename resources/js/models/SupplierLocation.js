// src/models/SupplierLocation.js
import BaseModel from './BaseModel';

class SupplierLocation extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для SupplierLocation
  }
}

export default SupplierLocation;
