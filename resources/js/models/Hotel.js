// src/models/Hotel.js
import BaseModel from './BaseModel';

class Hotel extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Hotel
  }
}

export default Hotel;
