<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheInvalidation
{
    public static function bootCacheInvalidation()
    {
        static::created(function ($model) {
            $model->invalidateCache();
        });

        static::updated(function ($model) {
            $model->invalidateCache();
        });

        static::deleted(function ($model) {
            $model->invalidateCache();
        });

        static::restored(function ($model) {
            $model->invalidateCache();
        });
    }

    protected function invalidateCache()
    {
        Cache::forget(self::getCacheKey());
    }

    public static function getCacheKey()
    {        
        return strtolower((new static)->getTable()) . '_cache';
    }
}
