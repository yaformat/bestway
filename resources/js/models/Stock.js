// src/models/Stock.js
import BaseModel from './BaseModel';

class Stock extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Stock
  }
}

export default Stock;
