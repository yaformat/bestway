<?php

namespace App\Repositories;

use App\Models\Hotel;

class HotelRepository extends BaseRepository
{
    public function __construct(Hotel $model)
    {
        $this->model = $model;
    }

    /**
     * Получает отели с связанными данными
     */
    public function getHotelsWithRelations($parameters)
    {
        $query = $this->model->with(['direction', 'resort', 'rooms', 'restTypes', 'photos']);
        $this->applyQueryFilters($query, $parameters);
        $this->applyQuerySorting($query, $parameters->sortBy);
        
        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }

    /**
     * Получает отель со всеми связанными данными
     */
    public function getHotelWithDetails($id)
    {
        return $this->model->with([
            'direction',
            'resort',
            'rooms.prices.bookingPeriod',
            'bookingPeriods',
            'restTypes',
            'photos'
        ])->findOrFail($id);
    }

    /**
     * Поиск отелей по направлению
     */
    public function searchByDirection($directionId, $parameters = null)
    {
        $query = $this->model->where('direction_id', $directionId)
            ->active()
            ->with(['direction', 'resort', 'rooms', 'photos']);

        if ($parameters) {
            $this->applyQueryFilters($query, $parameters);
            $this->applyQuerySorting($query, $parameters->sortBy);
        }

        return $query->paginate($parameters->limit ?? 20);
    }

    /**
     * Поиск отелей по курорту
     */
    public function searchByResort($resortId, $parameters = null)
    {
        $query = $this->model->where('resort_id', $resortId)
            ->active()
            ->with(['direction', 'resort', 'rooms', 'photos']);

        if ($parameters) {
            $this->applyQueryFilters($query, $parameters);
            $this->applyQuerySorting($query, $parameters->sortBy);
        }

        return $query->paginate($parameters->limit ?? 20);
    }

    /**
     * Копирование связанных данных для отеля
     */
    protected function copyRelatedData(\Illuminate\Database\Eloquent\Model $original, \Illuminate\Database\Eloquent\Model $copy): void
    {
        // Копируем виды отдыха
        $copy->restTypes()->attach($original->restTypes->pluck('id'));
        
        // Копируем номера отеля
        foreach ($original->rooms as $originalRoom) {
            $newRoom = $originalRoom->replicate();
            $newRoom->hotel_id = $copy->id;
            $newRoom->save();
            
            // Копируем цены для номеров
            foreach ($originalRoom->prices as $price) {
                $newPrice = $price->replicate();
                $newPrice->hotel_room_id = $newRoom->id;
                $newPrice->save();
            }
        }
        
        // Копируем периоды бронирования
        foreach ($original->bookingPeriods as $period) {
            $newPeriod = $period->replicate();
            $newPeriod->hotel_id = $copy->id;
            $newPeriod->save();
        }
    }

    /**
     * Применяет фильтры для отелей
     */
    public function applyQueryFilters(&$query, \App\Utils\ItemsListSearchParameters $parameters)
    {
        parent::applyQueryFilters($query, $parameters);

        // Фильтр по направлению
        if (!empty($parameters->filters['direction_id'])) {
            $query->where('direction_id', $parameters->filters['direction_id']);
        }

        // Фильтр по курорту
        if (!empty($parameters->filters['resort_id'])) {
            $query->where('resort_id', $parameters->filters['resort_id']);
        }

        // Фильтр по звездности
        if (!empty($parameters->filters['stars'])) {
            $query->whereIn('stars', $parameters->filters['stars']);
        }

        // Фильтр по рейтингу
        if (!empty($parameters->filters['rating'])) {
            $query->where('rating', '>=', $parameters->filters['rating'][0]);
        }

        // Фильтр по видам отдыха
        if (!empty($parameters->filters['rest_types'])) {
            $query->whereHas('restTypes', function($q) use ($parameters) {
                $q->whereIn('rest_types.id', $parameters->filters['rest_types']);
            });
        }

        // Фильтр по цене
        if (!empty($parameters->filters['price_range'])) {
            $minPrice = $parameters->filters['price_range']['from'] ?? 0;
            $maxPrice = $parameters->filters['price_range']['to'] ?? 999999;
            
            $query->whereHas('rooms.prices', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }
    }
}