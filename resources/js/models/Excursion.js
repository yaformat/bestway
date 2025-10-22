// src/models/Excursion.js
import BaseModel from './BaseModel';

class Excursion extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Excursion
  }
}

export default Excursion;
