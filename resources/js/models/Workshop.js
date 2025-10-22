// src/models/Workshop.js
import BaseModel from './BaseModel';

class Workshop extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Workshop
  }
}

export default Workshop;
