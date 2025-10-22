<?php

namespace App\Repositories;

use App\Models\Excursion;

class ExcursionRepository extends BaseRepository
{
    public function __construct(Excursion $model)
    {
        $this->model = $model;
    }

    /**
     * Получает экскурсии с программой
     */
    public function getExcursionsWithProgram($parameters)
    {
        $query = $this->model->with(['direction', 'program', 'photos']);
        $this->applyQueryFilters($query, $parameters);
        $this->applyQuerySorting($query, $parameters->sortBy);
        
        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }

    /**
     * Получает экскурсию с полной программой
     */
    public function getExcursionWithProgram($id)
    {
        return $this->model->with(['direction', 'program' => function($query) {
            $query->orderBy('sort_order');
        }, 'photos'])->findOrFail($id);
    }

    /**
     * Поиск экскурсий по направлению
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
     * Копирование связанных данных для экскурсии
     */
    protected function copyRelatedData(\Illuminate\Database\Eloquent\Model $original, \Illuminate\Database\Eloquent\Model $copy): void
    {
        // Копируем программу экскурсии
        foreach ($original->program as $programItem) {
            $newProgram = $programItem->replicate();
            $newProgram->excursion_id = $copy->id;
            $newProgram->save();
        }
    }

    /**
     * Применяет фильтры для экскурсий
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
                $query->whereBetween('duration_hours', $duration);
            } else {
                $query->where('duration_hours', $duration);
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
    }
}