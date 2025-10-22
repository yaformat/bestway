<?php

namespace App\Repositories;

use App\Models\Direction;

class DirectionRepository extends BaseRepository
{
    public function __construct(Direction $model)
    {
        $this->model = $model;
    }

    /**
     * Получает корневые направления (без родителя)
     */
    public function getRootDirections()
    {
        return $this->model->root()->active()->with(['children' => function($query) {
            $query->active()->orderBy('sort_order');
        }])->orderBy('sort_order')->get();
    }

    /**
     * Получает дочерние направления
     */
    public function getChildDirections($parentId)
    {
        return $this->model->where('parent_id', $parentId)
            ->active()
            ->with(['children'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Получает полное дерево направлений
     */
    public function getDirectionTree()
    {
        return $this->model->root()
            ->active()
            ->with(['children' => function($query) {
                $query->active()->with(['children' => function($subQuery) {
                    $subQuery->active();
                }])->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Применяет фильтры для направлений
     */
    public function applyQueryFilters(&$query, \App\Utils\ItemsListSearchParameters $parameters)
    {
        parent::applyQueryFilters($query, $parameters);

        // Фильтр по родительскому направлению
        if (!empty($parameters->filters['parent_id'])) {
            $parentId = $parameters->filters['parent_id'][0] ?? null;
            if ($parentId === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        // Фильтр по активности
        if (!empty($parameters->filters['is_active'])) {
            $query->where('is_active', $parameters->filters['is_active'][0]);
        }
    }
}