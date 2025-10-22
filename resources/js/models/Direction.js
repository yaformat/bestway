// src/models/Direction.js
import BaseModel from './BaseModel';

class Direction extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для Direction
  }
}

export default Direction;
