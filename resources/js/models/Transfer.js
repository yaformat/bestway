// src/models/Transfer.js
import BaseModel from './BaseModel';

class Transfer extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Transfer
  }
}

export default Transfer;
