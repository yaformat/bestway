<?php

namespace App\Traits;
use App\Enums\StockActionStatusEnum;
use App\Helpers\Helper;

trait StockActionTrait 
{

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->status) {
                $model->status = StockActionStatusEnum::DRAFT;
            }
            if (!$model->user_id) {
                $model->user_id = \Auth::user()->id;
            }
        });
    }

    public function stock()
    {
        return $this->belongsTo('App\Models\Stock', 'stock_id')
                    ->withTrashed()
                    ->select('id', 'name');
    }

    public function getIsDeletableAttribute() {
        return $this->status === StockActionStatusEnum::DRAFT && !$this->resources()->exists();
    }

    public function getIsStocksHasChangesAttribute()
    {
        if ($this->status !== StockActionStatusEnum::DRAFT) {
            $this->is_stocks_has_changes = false;
        }
        
        return $this->is_stocks_has_changes ?? false;
    }

    public function getTotalPriceAttribute($value)
    {
        return Helper::formatPriceObject($value);
    }

    public function getActionStatusAttribute()
    {
        return Helper::formatStatusObject($this->status);
    }

}
