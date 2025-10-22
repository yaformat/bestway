// src/models/Resort.js
import BaseModel from './BaseModel';

class Resort extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Resort
  }
}

export default Resort;
