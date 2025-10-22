// src/models/RestType.js
import BaseModel from './BaseModel';

class RestType extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для RestType
  }
}

export default RestType;
