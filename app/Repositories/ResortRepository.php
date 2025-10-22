<?php

namespace App\Repositories;

use App\Models\Resort;
use Illuminate\Database\Eloquent\Builder;

class ResortRepository extends BaseRepository
{
    public function __construct(Resort $model)
    {
        $this->model = $model;
    }

    /**
     * Получает курорты с отелями
     */
    public function getResortsWithHotels($parameters)
    {
        $query = $this->model->with(['direction', 'hotels', 'photos']);
        $this->applyQueryFilters($query, $parameters);
        $this->applyQuerySorting($query, $parameters->sortBy);
        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }

    /**
     * Получает курорт с отелями
     */
    public function getResortWithHotels($id)
    {
        return $this->model->with(['direction', 'hotels' => function($query) {
            $query->active()->orderBy('sort_order')->orderBy('name');
        }, 'photos' => function($query) {
            $query->orderBy('is_main', 'desc')->orderBy('sort_order');
        }])->findOrFail($id);
    }

    /**
     * Поиск курортов по направлению
     */
    public function searchByDirection($directionId, $parameters = null)
    {
        $query = $this->model->where('direction_id', $directionId)
            ->active()
            ->with(['direction', 'hotels', 'photos']);
            
        if ($parameters) {
            $this->applyQueryFilters($query, $parameters);
        }
        return $query->paginate($parameters->limit ?? 20);
    }

    /**
     * Получает отели курорта
     */
    public function getResortHotels($resortId)
    {
        $resort = $this->model->findOrFail($resortId);
        return $resort->hotels()->active()->with(['direction', 'photos'])->get();
    }

    /**
     * Копирование связанных данных для курорта
     */
    protected function copyRelatedData(\Illuminate\Database\Eloquent\Model $original, \Illuminate\Database\Eloquent\Model $copy): void
    {
        // Копируем фото курорта
        foreach ($original->photos as $photo) {
            $newPhoto = $photo->replicate();
            $newPhoto->resort_id = $copy->id;
            $newPhoto->save();
        }
    }

    /**
     * Применяет фильтры для курортов
     */
    public function applyQueryFilters(&$query, \App\Utils\ItemsListSearchParameters $parameters)
    {
        parent::applyQueryFilters($query, $parameters);

        // Фильтр по направлению
        if (!empty($parameters->filters['direction_id'])) {
            $query->where('direction_id', $parameters->filters['direction_id']);
        }

        // Фильтр по высоте
        if (!empty($parameters->filters['altitude'])) {
            $altitude = $parameters->filters['altitude'];
            if (is_array($altitude)) {
                $query->whereBetween('altitude', $altitude);
            } else {
                $query->where('altitude', $altitude);
            }
        }

        // Фильтр по диапазону высот
        if (!empty($parameters->filters['altitude_range'])) {
            $minAltitude = $parameters->filters['altitude_range']['from'] ?? 0;
            $maxAltitude = $parameters->filters['altitude_range']['to'] ?? 9999;
            $query->whereBetween('altitude', [$minAltitude, $maxAltitude]);
        }

        // Фильтр по наличию отелей
        if (!empty($parameters->filters['has_hotels'])) {
            $query->has('hotels');
        }

        // Фильтр по минимальному количеству отелей
        if (!empty($parameters->filters['min_hotels'])) {
            $query->withCount('hotels')->having('hotels_count', '>=', $parameters->filters['min_hotels']);
        }

        // Фильтр по координатам (в радиусе)
        if (!empty($parameters->filters['latitude']) && !empty($parameters->filters['longitude'])) {
            $lat = $parameters->filters['latitude'];
            $lng = $parameters->filters['longitude'];
            $radius = $parameters->filters['radius'] ?? 50; // км по умолчанию
            
            // Формула для расчета расстояния (упрощенная)
            $query->selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lng, $lat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        }

        // Фильтр по активности
        if (isset($parameters->filters['is_active'])) {
            $query->where('is_active', $parameters->filters['is_active']);
        }
    }

    /**
     * Применяет сортировку для курортов
     */
    public function applyQuerySorting(Builder &$query, ?string $sortBy): void
    {
        $allowedSorts = [
            'name',
            'direction_id',
            'altitude',
            'sort_order',
            'is_active',
            'created_at',
            'hotels_count'
        ];

        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'hotels_count') {
                $query->withCount('hotels')->orderBy('hotels_count', request('sort_direction', 'asc'));
            } else {
                $query->orderBy($sortBy, request('sort_direction', 'asc'));
            }
        } else {
            // По умолчанию сортируем по sort_order, затем по name
            $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
        }
    }
}