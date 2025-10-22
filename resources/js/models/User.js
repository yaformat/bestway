// src/models/User.js
import BaseModel from './BaseModel';

class User extends BaseModel {
  constructor(data = {}) {
    super(data);
    this.name = data.name || '';
    // другие поля, специфичные для User
  }
}

export default User;
