// src/models/Kitchen.js
import BaseModel from './BaseModel';

class Kitchen extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Kitchen
  }
}

export default Kitchen;
