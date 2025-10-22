// src/models/Organization.js
import BaseModel from './BaseModel';

class Organization extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Organization
  }
}

export default Organization;
