<?php

namespace App\Repositories;

use App\Models\Hotel;
use App\Models\HotelBuilding;
use App\Models\HotelService;
use App\Models\HotelInfoBlock;

class HotelRepository extends BaseRepository
{
    public function __construct(Hotel $model)
    {
        $this->model = $model;
    }

    /**
     * Получает отели с связанными данными
     * Для списка отелей загружаем минимум связей для производительности
     */
    public function getHotelsWithRelations($parameters)
    {
        $query = $this->model->with([
            'direction', 
            'resort', 
            'rooms.prices', // Загружаем цены номеров для расчета минимальной цены
            'services.prices', // Загружаем цены услуг для расчета минимальной цены
            'photos' // Основное фото
        ]);

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
            'rooms.prices.bookingPeriod', // Номера с их ценами
            'buildings.rooms',
            'infoBlocks',
            'services.prices.bookingPeriod', // Услуги с ценами
            'bookingPeriods' => function($query) {
                $query->ordered();
            },
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
            ->with(['direction', 'resort', 'rooms.building', 'buildings', 'photos']);

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
            ->with(['direction', 'resort', 'rooms.building', 'buildings', 'photos']);

        if ($parameters) {
            $this->applyQueryFilters($query, $parameters);
            $this->applyQuerySorting($query, $parameters->sortBy);
        }

        return $query->paginate($parameters->limit ?? 20);
    }

    /**
     * Создание или обновление строения отеля
     */
    public function createOrUpdateBuilding($hotelId, $data, $buildingId = null)
    {
        if ($buildingId) {
            $building = HotelBuilding::where('hotel_id', $hotelId)
                ->findOrFail($buildingId);
            $building->update($data);
        } else {
            $data['hotel_id'] = $hotelId;
            $data['sort_order'] = HotelBuilding::where('hotel_id', $hotelId)->max('sort_order') + 1;
            $building = HotelBuilding::create($data);
        }
        
        return $building;
    }

    /**
     * Создание или обновление дополнительной услуги
     */
    public function createOrUpdateService($hotelId, $data, $serviceId = null)
    {
        if ($serviceId) {
            $service = HotelService::where('hotel_id', $hotelId)
                ->findOrFail($serviceId);
            $service->update($data);
        } else {
            $data['hotel_id'] = $hotelId;
            $data['sort_order'] = HotelService::where('hotel_id', $hotelId)->max('sort_order') + 1;
            $service = HotelService::create($data);
        }
        
        return $service;
    }

    /**
     * Создание или обновление информационного блока
     */
    public function createOrUpdateInfoBlock($hotelId, $data, $blockId = null)
    {
        if ($blockId) {
            $block = HotelInfoBlock::where('hotel_id', $hotelId)
                ->findOrFail($blockId);
            $block->update($data);
        } else {
            $data['hotel_id'] = $hotelId;
            $data['sort_order'] = HotelInfoBlock::where('hotel_id', $hotelId)->max('sort_order') + 1;
            $block = HotelInfoBlock::create($data);
        }
        
        return $block;
    }

    /**
     * Удаление строения
     */
    public function deleteBuilding($hotelId, $buildingId)
    {
        $building = HotelBuilding::where('hotel_id', $hotelId)
            ->findOrFail($buildingId);
        
        // Перемещаем номера в корень отеля
        $building->rooms()->update(['hotel_building_id' => null]);
        
        return $building->delete();
    }

    /**
     * Удаление услуги
     */
    public function deleteService($hotelId, $serviceId)
    {
        $service = HotelService::where('hotel_id', $hotelId)
            ->findOrFail($serviceId);
        
        // Удаляем цены услуги
        $service->prices()->delete();
        
        return $service->delete();
    }

    /**
     * Удаление информационного блока
     */
    public function deleteInfoBlock($hotelId, $blockId)
    {
        $block = HotelInfoBlock::where('hotel_id', $hotelId)
            ->findOrFail($blockId);
        
        return $block->delete();
    }

    /**
     * Обновление порядка строений
     */
    public function updateBuildingsOrder($hotelId, $buildingIds)
    {
        foreach ($buildingIds as $index => $buildingId) {
            HotelBuilding::where('hotel_id', $hotelId)
                ->where('id', $buildingId)
                ->update(['sort_order' => $index]);
        }
    }

    /**
     * Обновление порядка услуг
     */
    public function updateServicesOrder($hotelId, $serviceIds)
    {
        foreach ($serviceIds as $index => $serviceId) {
            HotelService::where('hotel_id', $hotelId)
                ->where('id', $serviceId)
                ->update(['sort_order' => $index]);
        }
    }

    /**
     * Обновление порядка информационных блоков
     */
    public function updateInfoBlocksOrder($hotelId, $blockIds)
    {
        foreach ($blockIds as $index => $blockId) {
            HotelInfoBlock::where('hotel_id', $hotelId)
                ->where('id', $blockId)
                ->update(['sort_order' => $index]);
        }
    }

    /**
     * Копирование связанных данных для отеля
     */
    protected function copyRelatedData(\Illuminate\Database\Eloquent\Model $original, \Illuminate\Database\Eloquent\Model $copy): void
    {
        // Копируем информационные блоки
        foreach ($original->infoBlocks as $infoBlock) {
            $newInfoBlock = $infoBlock->replicate();
            $newInfoBlock->hotel_id = $copy->id;
            $newInfoBlock->save();
        }

        // Копируем строения
        foreach ($original->buildings as $building) {
            $newBuilding = $building->replicate();
            $newBuilding->hotel_id = $copy->id;
            $newBuilding->save();

            // Копируем номера в строении
            foreach ($building->rooms as $room) {
                $newRoom = $room->replicate();
                $newRoom->hotel_id = $copy->id;
                $newRoom->hotel_building_id = $newBuilding->id;
                $newRoom->save();

                // Копируем цены для номеров
                foreach ($room->prices as $price) {
                    $newPrice = $price->replicate();
                    $newPrice->hotel_room_id = $newRoom->id;
                    $newPrice->save();
                }
            }
        }

        // Копируем номера без строений
        foreach ($original->rooms()->whereNull('hotel_building_id')->get() as $room) {
            $newRoom = $room->replicate();
            $newRoom->hotel_id = $copy->id;
            $newRoom->save();

            // Копируем цены для номеров
            foreach ($room->prices as $price) {
                $newPrice = $price->replicate();
                $newPrice->hotel_room_id = $newRoom->id;
                $newPrice->save();
            }
        }

        // Копируем дополнительные услуги
        foreach ($original->services as $service) {
            $newService = $service->replicate();
            $newService->hotel_id = $copy->id;
            $newService->save();

            // Копируем цены для услуг
            foreach ($service->prices as $price) {
                $newPrice = $price->replicate();
                $newPrice->hotel_service_id = $newService->id;
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

        // Фильтр по типу отеля
        if (!empty($parameters->filters['hotel_type'])) {
            $query->where('hotel_type', $parameters->filters['hotel_type']);
        }

        // Фильтр по типам отдыха (JSON поле)
        if (!empty($parameters->filters['rest_types'])) {
            $query->where(function($q) use ($parameters) {
                foreach ($parameters->filters['rest_types'] as $restType) {
                    $q->orWhereJsonContains('rest_types', $restType);
                }
            });
        }

        // Фильтр по рейтингу
        if (!empty($parameters->filters['rating'])) {
            $query->where('rating', '>=', $parameters->filters['rating'][0]);
        }

        // Фильтр по валюте
        if (!empty($parameters->filters['currency'])) {
            $query->where('currency', $parameters->filters['currency']);
        }

        // Фильтр по цене (учитываем и номера, и услуги)
        if (!empty($parameters->filters['price_range'])) {
            $minPrice = $parameters->filters['price_range']['from'] ?? 0;
            $maxPrice = $parameters->filters['price_range']['to'] ?? 999999;
            
            $query->where(function($q) use ($minPrice, $maxPrice) {
                $q->whereHas('rooms.prices', function($subQ) use ($minPrice, $maxPrice) {
                    $subQ->whereBetween('price', [$minPrice, $maxPrice]);
                })->orWhereHas('services.prices', function($subQ) use ($minPrice, $maxPrice) {
                    $subQ->whereBetween('price', [$minPrice, $maxPrice]);
                });
            });
        }

        // Фильтр по наличию строений
        if (!empty($parameters->filters['has_buildings'])) {
            $query->whereHas('buildings');
        }

        // Фильтр по наличию дополнительных услуг
        if (!empty($parameters->filters['has_services'])) {
            $query->whereHas('services');
        }
    }

    /**
     * Получает цены для отеля по периоду
     */
    public function getHotelPrices($hotelId, $bookingPeriodId = null)
    {
        $query = $this->model->findOrFail($hotelId)->getAllPrices();

        if ($bookingPeriodId) {
            $query = $query->where('booking_period_id', $bookingPeriodId);
        }

        return $query->load(['hotelRoom.building', 'service', 'bookingPeriod']);
    }

    /**
     * Обновляет порядок периодов бронирования
     */
    public function updateBookingPeriodsOrder($hotelId, $periods)
    {
        foreach ($periods as $index => $periodId) {
            $this->model->findOrFail($hotelId)
                ->bookingPeriods()
                ->where('id', $periodId)
                ->update(['sort_order' => $index]);
        }
    }
}