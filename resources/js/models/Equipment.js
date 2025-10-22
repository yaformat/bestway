// src/models/Equipment.js
import BaseModel from './BaseModel';

class Equipment extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Equipment
  }
}

export default Equipment;
