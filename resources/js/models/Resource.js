// src/models/Resource.js
import BaseModel from './BaseModel';

class Resource extends BaseModel {
  constructor(data = {}) {
    super(data);
    // this.notes = data.notes || '';
    // this.unit = data.unit || '';
    // this.unit_alt = data.unit_alt || '';
    // this.unit_factor = data.unit_factor || 1;
    // this.has_stock = data.has_stock || 0;
    // this.tech_cards_count = data.tech_cards_count || 0;
    // this.round = data.round || 0;
    // this.min_stock = data.min_stock || 0;
    // this.photo = data.photo || null;
    // this.losses = data.losses || [];
    // this.category = data.category || null;
    // this.avg_price = data.avg_price || {};
    // this.last_price = data.last_price || {};
    // this.stocks_total_price = data.stocks_total_price || {};
    // this.stocks_total_value = data.stocks_total_value || {};
    // this.min_value = data.min_value || null;
    // this.deficit_value = data.deficit_value || null;
    // this.stocks = data.stocks || [];
    // this.created_at = data.created_at || null;
    // this.updated_at = data.updated_at || null;
  }

  //TODO?
  // static fromJson(json) {
  //   return new Resource({
  //     ...json,
  //     category: json.category ? BaseModel.fromJson(json.category) : null,
  //     losses: json.losses ? json.losses.map(loss => ({ ...loss })) : [],
  //     stocks: json.stocks ? json.stocks.map(stock => ({ ...stock })) : [],
  //   });
  // }
}

export default Resource;
