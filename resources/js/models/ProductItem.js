// src/models/Kitchen.js
import BaseModel from './BaseModel';

class ProductItem extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для ProductItem
  }
}

export default ProductItem;
