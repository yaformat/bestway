<?php

namespace App\Repositories;

use App\Models\Transfer;

class TransferRepository extends BaseRepository
{
    public function __construct(Transfer $model)
    {
        $this->model = $model;
    }

    /**
     * Получает трансферы с фото
     */
    public function getTransfersWithPhotos($parameters)
    {
        $query = $this->model->with(['photos']);
        $this->applyQueryFilters($query, $parameters);
        $this->applyQuerySorting($query, $parameters->sortBy);
        
        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }

    /**
     * Поиск трансферов по типу
     */
    public function searchByType($type, $parameters = null)
    {
        $query = $this->model->where('type', $type)
            ->active()
            ->with(['photos']);

        if ($parameters) {
            $this->applyQueryFilters($query, $parameters);
        }

        return $query->paginate($parameters->limit ?? 20);
    }

    /**
     * Применяет фильтры для трансферов
     */
    public function applyQueryFilters(&$query, \App\Utils\ItemsListSearchParameters $parameters)
    {
        parent::applyQueryFilters($query, $parameters);

        // Фильтр по типу
        if (!empty($parameters->filters['type'])) {
            $query->where('type', $parameters->filters['type']);
        }

        // Фильтр по местоположению
        if (!empty($parameters->filters['from_location'])) {
            $query->where('from_location', 'like', '%' . $parameters->filters['from_location'] . '%');
        }

        if (!empty($parameters->filters['to_location'])) {
            $query->where('to_location', 'like', '%' . $parameters->filters['to_location'] . '%');
        }

        // Фильтр по цене
        if (!empty($parameters->filters['price_range'])) {
            $minPrice = $parameters->filters['price_range']['from'] ?? 0;
            $maxPrice = $parameters->filters['price_range']['to'] ?? 999999;
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        // Фильтр по вместимости
        if (!empty($parameters->filters['capacity'])) {
            $query->where('capacity', '>=', $parameters->filters['capacity']);
        }
    }
}