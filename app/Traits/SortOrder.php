<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait SortOrder
{
    public static function bootSortOrder()
    {
        static::creating(function ($model) {
            if (!Schema::hasColumn($model->getTable(), 'sort_order')) {
                return;
            }

            // Если sort_order уже установлен (например, из реквеста) — оставляем как есть
            if (!is_null($model->sort_order)) {
                return;
            }

            // Строим запрос 
            $query = self::query();

            // Определение parent_id, если есть
            $parentIdColumn = 'parent_id';
            $hasParent = Schema::hasColumn($model->getTable(), $parentIdColumn);
            // на максимум в пределах parent_id
            if ($hasParent && isset($model->$parentIdColumn)) {
                $query->where($parentIdColumn, $model->$parentIdColumn);
            }
            // Определение type, если есть
            $typeColumn = 'type';
            $hasType = Schema::hasColumn($model->getTable(), $typeColumn);
            // на максимум в пределах parent_id
            if ($hasType && isset($model->$typeColumn)) {
                $query->where($typeColumn, $model->$typeColumn);
            }

            $model->sort_order = ($query->max('sort_order') ?? 0) + 1;
        });

        static::addGlobalScope('order', function (Builder $builder) {
            if (Schema::hasColumn((new static)->getTable(), 'sort_order')) {
                $builder->orderBy('sort_order', 'asc');
            }
        });

    }

}
