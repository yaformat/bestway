// src/models/Supplier.js
import BaseModel from './BaseModel';

class Supplier extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Supplier
  }
}

export default Supplier;
