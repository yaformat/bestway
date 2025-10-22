// src/models/ResourceCategory.js
import BaseModel from './BaseModel';

class ResourceCategory extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для ResourceCategory
  }
}

export default ResourceCategory;
