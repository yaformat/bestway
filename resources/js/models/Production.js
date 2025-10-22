// src/models/Production.js
import BaseModel from './BaseModel';

class Production extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.date = data.date || '';
    // другие поля, специфичные для Production
  }
}

export default Production;
