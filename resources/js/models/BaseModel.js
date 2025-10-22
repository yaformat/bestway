// src/models/BaseModel.js
class BaseModel {
  constructor(data = {}) {
    Object.assign(this, data);
  }

  static fromJson(json) {
    return new this(json);
  }

  toJson() {
    return { ...this };
  }
}

export default BaseModel;
