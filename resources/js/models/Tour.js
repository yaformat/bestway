// src/models/Tour.js
import BaseModel from './BaseModel';

class Tour extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Tour
  }
}

export default Tour;
