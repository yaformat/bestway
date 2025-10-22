<?php

namespace App\Repositories;

use App\Models\Tour;

class TourRepository extends BaseRepository
{
    public function __construct(Tour $model)
    {
        $this->model = $model;
    }

    /**
     * Получает туры с программой
     */
    public function getToursWithProgram($parameters)
    {
        $query = $this->model->with(['direction', 'program', 'photos']);
        $this->applyQueryFilters($query, $parameters);
        $this->applyQuerySorting($query, $parameters->sortBy);
        
        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }

    /**
     * Получает тур с полной программой
     */
    public function getTourWithProgram($id)
    {
        return $this->model->with(['direction', 'program' => function($query) {
            $query->orderBy('day')->orderBy('sort_order');
        }, 'photos'])->findOrFail($id);
    }

    /**
     * Поиск туров по направлению
     */
    public function searchByDirection($directionId, $parameters = null)
    {
        $query = $this->model->where('direction_id', $directionId)
            ->active()
            ->with(['direction', 'program', 'photos']);

        if ($parameters) {
            $this->applyQueryFilters($query, $parameters);
        }

        return $query->paginate($parameters->limit ?? 20);
    }

    /**
     * Копирование связанных данных для тура
     */
    protected function copyRelatedData(\Illuminate\Database\Eloquent\Model $original, \Illuminate\Database\Eloquent\Model $copy): void
    {
        // Копируем программу тура
        foreach ($original->program as $programItem) {
            $newProgram = $programItem->replicate();
            $newProgram->tour_id = $copy->id;
            $newProgram->save();
        }
    }

    /**
     * Применяет фильтры для туров
     */
    public function applyQueryFilters(&$query, \App\Utils\ItemsListSearchParameters $parameters)
    {
        parent::applyQueryFilters($query, $parameters);

        // Фильтр по направлению
        if (!empty($parameters->filters['direction_id'])) {
            $query->where('direction_id', $parameters->filters['direction_id']);
        }

        // Фильтр по длительности
        if (!empty($parameters->filters['duration'])) {
            $duration = $parameters->filters['duration'];
            if (is_array($duration)) {
                $query->whereBetween('duration_days', $duration);
            } else {
                $query->where('duration_days', $duration);
            }
        }

        // Фильтр по уровню сложности
        if (!empty($parameters->filters['difficulty_level'])) {
            $query->where('difficulty_level', $parameters->filters['difficulty_level']);
        }

        // Фильтр по цене
        if (!empty($parameters->filters['price_range'])) {
            $minPrice = $parameters->filters['price_range']['from'] ?? 0;
            $maxPrice = $parameters->filters['price_range']['to'] ?? 999999;
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        // Фильтр по размеру группы
        if (!empty($parameters->filters['group_size'])) {
            $query->where('group_size_max', '>=', $parameters->filters['group_size'][0])
                  ->where('group_size_min', '<=', $parameters->filters['group_size'][1]);
        }
    }
}