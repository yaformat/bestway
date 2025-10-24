<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;
use App\Models\Hotel;
use App\Repositories\HotelRepository;
use App\Helpers\FilterHelper;
use App\Helpers\HotelHelper;

class HotelController extends BaseController
{
    private $hotelRepository;

    public function __construct(HotelRepository $hotelRepository)
    {
        $this->hotelRepository = $hotelRepository;
    }

    /**
     * Получает список отелей
     */
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->hotelRepository->getHotelsWithRelations($parameters);
        
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\HotelElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Hotel::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        // Получение количества удаленных элементов
        $trashed_count = $this->hotelRepository->trashedCount();
        $response->trashed_count = $trashed_count;

        // Устанавливаем фильтры
        $response->setFilters([
            'direction_id' => FilterHelper::createFilter('Направление', 'tree')
                ->setOptions(FilterHelper::getDirectionOptions())
                ->setMultiple(true)
                ->setQuickFilter(true, true)
                ->toArray(),
            'resort_id' => FilterHelper::createFilter('Курорт', 'checkboxes')
                ->setOptions(FilterHelper::getResortOptions())
                ->setMultiple(true)
                ->setQuickFilter(true)
                ->toArray(),
            'hotel_type' => FilterHelper::createFilter('Тип отеля', 'checkboxes')
                ->setOptions(HotelHelper::getHotelTypes()->toArray())
                ->setMultiple(true)
                ->setQuickFilter(true)
                ->toArray(),
            'rest_types' => FilterHelper::createFilter('Виды отдыха', 'checkboxes')
                ->setOptions(HotelHelper::getRestTypes()->toArray())
                ->setMultiple(true)
                ->setQuickFilter(true)
                ->toArray(),
            'rating' => FilterHelper::createFilter('Рейтинг', 'range')
                ->setRange(0, 5, 0.1)
                ->setQuickFilter(true)
                ->toArray(),
            'currency' => FilterHelper::createFilter('Валюта', 'checkboxes')
                ->setOptions(HotelHelper::getCurrencies())
                ->setMultiple(true)
                ->setQuickFilter(true)
                ->toArray(),
            'price_range' => FilterHelper::createFilter('Цена', 'range')
                ->setRange(0, 50000, 100)
                ->setQuickFilter(true)
                ->toArray(),
            'has_buildings' => FilterHelper::createFilter('Со строениями', 'toggle')
                ->setQuickFilter(true)
                ->toArray(),
            'has_services' => FilterHelper::createFilter('С услугами', 'toggle')
                ->setQuickFilter(true)
                ->toArray(),
            'is_active' => FilterHelper::createFilter('Активные', 'toggle')
                ->setQuickFilter(true, true)
                ->toArray(),
            'only_trashed' => FilterHelper::createFilter("В архиве ({$trashed_count})", 'toggle')
                ->setQuickFilter(true)
                ->toArray(),
            'has_photos' => FilterHelper::createFilter('С фото', 'toggle')
                ->setQuickFilter(true)
                ->toArray(),
        ]);

        // Устанавливаем сортировки
        $response->setSortings([
            ['value' => 'default', 'label' => 'По-умолчанию'],
            ['value' => 'name_asc', 'label' => 'По названию (А-Я)'],
            ['value' => 'name_desc', 'label' => 'По названию (Я-А)'],
            ['value' => 'rating_desc', 'label' => 'По рейтингу (убыв.)'],
            ['value' => 'rating_asc', 'label' => 'По рейтингу (возр.)'],
            ['value' => 'price_asc', 'label' => 'По цене (возр.)'],
            ['value' => 'price_desc', 'label' => 'По цене (убыв.)'],
            ['value' => 'created_at_desc', 'label' => 'Сначала новые'],
            ['value' => 'created_at_asc', 'label' => 'Сначала старые'],
            ['value' => 'sort_order_asc', 'label' => 'По порядку сортировки'],
        ]);

        return ApiResponse::success($response);
    }

    /**
     * Получает детальную информацию об отеле
     */
    public function show($id, Request $request)
    {
        $result = $this->hotelRepository->getHotelWithDetails($id);
        $response = new \App\Http\Responses\HotelElementDetails($result);
        return ApiResponse::success($response);
    }

    /**
     * Получает справочники для формы отеля
     */
    public function formReferenceData(Request $request)
    {
        $hotelId = $request->input('hotel_id');
        
        // Получаем базовые справочники
        $referenceData = [
            'directions' => FilterHelper::getDirectionOptionsFlat(),
            'resorts' => FilterHelper::getResortOptions(),
            'hotel_types' => HotelHelper::getHotelTypesForSelect(),
            'rest_types' => HotelHelper::getRestTypesForSelect(),
            'currencies' => HotelHelper::getCurrencies(),
            'info_blocks' => HotelHelper::getInfoBlocksForForm(),
        ];

        // Если редактируем отель, добавляем связанные данные
        if ($hotelId) {
            try {
                $hotel = $this->hotelRepository->getHotelWithDetails($hotelId);
                
                // Получаем курорты для выбранного направления
                if ($hotel->direction_id) {
                    $referenceData['resorts'] = FilterHelper::getResortOptions($hotel->direction_id);
                }
                
                // Добавляем связанные данные отеля
                $referenceData['hotel_rest_types'] = $hotel->rest_types ?? [];
                $referenceData['hotel_info_blocks'] = $hotel->infoBlocks->map(function ($block) {
                    return [
                        'id' => $block->id,
                        'key' => $block->block_key,
                        'content' => $block->content,
                        'is_active' => $block->is_active,
                        'sort_order' => $block->sort_order
                    ];
                })->toArray();
                $referenceData['hotel_buildings'] = $hotel->buildings->map(function ($building) {
                    return [
                        'id' => $building->id,
                        'name' => $building->name,
                        'description' => $building->description,
                        'is_active' => $building->is_active,
                        'sort_order' => $building->sort_order
                    ];
                })->toArray();
                $referenceData['hotel_services'] = $hotel->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'description' => $service->description,
                        'is_active' => $service->is_active,
                        'sort_order' => $service->sort_order
                    ];
                })->toArray();
                
            } catch (\Exception $e) {
                // Если отель не найден, возвращаем ошибку
                return ApiResponse::error('Отель не найден', 404);
            }
        }
        
        return ApiResponse::success($referenceData);
    }

    /**
     * Поиск отелей по направлению
     */
    public function searchByDirection($directionId, ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->hotelRepository->searchByDirection($directionId, $parameters);
        
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\HotelElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Hotel::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        return ApiResponse::success($response);
    }

    /**
     * Поиск отелей по курорту
     */
    public function searchByResort($resortId, ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->hotelRepository->searchByResort($resortId, $parameters);
        
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\HotelElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Hotel::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        return ApiResponse::success($response);
    }

    /**
     * Создает новый отель
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'resort_id' => 'nullable|exists:resorts,id',
            'currency' => 'required|string|size:3',
            'hotel_type' => 'required|string|in:hotel,pension,sanatorium,guest_house,children_camp',
            'rest_types' => 'nullable|array',
            'rest_types.*' => 'string|in:family,medical,recreational,sport,excursion,business',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rating' => 'nullable|numeric|between:0,5',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $hotel = $this->hotelRepository->create($data);
        
        return ApiResponse::success($hotel, 'Отель создан', 201);
    }

    /**
     * Обновляет отель
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'resort_id' => 'nullable|exists:resorts,id',
            'currency' => 'required|string|size:3',
            'hotel_type' => 'required|string|in:hotel,pension,sanatorium,guest_house,children_camp',
            'rest_types' => 'nullable|array',
            'rest_types.*' => 'string|in:family,medical,recreational,sport,excursion,business',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rating' => 'nullable|numeric|between:0,5',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $hotel = $this->hotelRepository->update($id, $data);
        
        return ApiResponse::success($hotel, 'Отель обновлен');
    }

    /**
     * Удаляет отель
     */
    public function destroy($id)
    {
        $result = $this->hotelRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно удалено');
        }
        return ApiResponse::error('Невозможно удалить отель', 409);
    }

    /**
     * Восстанавливает отель
     */
    public function restore($id)
    {
        $result = $this->hotelRepository->restore($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно восстановлено');
        }
        return ApiResponse::error('Невозможно восстановить отель', 409);
    }

    /**
     * Копирует отель
     */
    public function copy($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $newData = ['name' => $request->name];
        $copy = $this->hotelRepository->copy($id, $newData);
        
        return ApiResponse::success($copy, 'Отель скопирован', 201);
    }

    /**
     * Изменяет активность отеля
     */
    public function toggleActive($id, Request $request)
    {
        $result = $this->hotelRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }
        return ApiResponse::error('Action forbidden', 409);
    }

    /**
     * Создание или обновление строения
     */
    public function saveBuilding(Request $request, $hotelId)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:hotel_buildings,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['name', 'description', 'is_active']);
        $building = $this->hotelRepository->createOrUpdateBuilding($hotelId, $data, $request->input('id'));
        
        return ApiResponse::success($building, 'Строение сохранено');
    }

    /**
     * Удаление строения
     */
    public function deleteBuilding(Request $request, $hotelId, $buildingId)
    {
        $result = $this->hotelRepository->deleteBuilding($hotelId, $buildingId);
        return ApiResponse::success($result, 'Строение удалено');
    }

    /**
     * Обновление порядка строений
     */
    public function updateBuildingsOrder(Request $request, $hotelId)
    {
        $request->validate([
            'building_ids' => 'required|array',
            'building_ids.*' => 'integer|exists:hotel_buildings,id'
        ]);

        $this->hotelRepository->updateBuildingsOrder($hotelId, $request->input('building_ids'));
        return ApiResponse::success(null, 'Порядок обновлен');
    }

    /**
     * Создание или обновление услуги
     */
    public function saveService(Request $request, $hotelId)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:hotel_services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['name', 'description', 'is_active']);
        $service = $this->hotelRepository->createOrUpdateService($hotelId, $data, $request->input('id'));
        
        return ApiResponse::success($service, 'Услуга сохранена');
    }

    /**
     * Удаление услуги
     */
    public function deleteService(Request $request, $hotelId, $serviceId)
    {
        $result = $this->hotelRepository->deleteService($hotelId, $serviceId);
        return ApiResponse::success($result, 'Услуга удалена');
    }

    /**
     * Обновление порядка услуг
     */
    public function updateServicesOrder(Request $request, $hotelId)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'integer|exists:hotel_services,id'
        ]);

        $this->hotelRepository->updateServicesOrder($hotelId, $request->input('service_ids'));
        return ApiResponse::success(null, 'Порядок обновлен');
    }

    /**
     * Создание или обновление информационного блока
     */
    public function saveInfoBlock(Request $request, $hotelId)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:hotel_info_blocks,id',
            'block_key' => 'required|string|exists:hotels.info_blocks',
            'content' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['block_key', 'content', 'is_active']);
        $block = $this->hotelRepository->createOrUpdateInfoBlock($hotelId, $data, $request->input('id'));
        
        return ApiResponse::success($block, 'Информационный блок сохранен');
    }

    /**
     * Удаление информационного блока
     */
    public function deleteInfoBlock(Request $request, $hotelId, $blockId)
    {
        $result = $this->hotelRepository->deleteInfoBlock($hotelId, $blockId);
        return ApiResponse::success($result, 'Информационный блок удален');
    }

    /**
     * Обновление порядка информационных блоков
     */
    public function updateInfoBlocksOrder(Request $request, $hotelId)
    {
        $request->validate([
            'block_ids' => 'required|array',
            'block_ids.*' => 'integer|exists:hotel_info_blocks,id'
        ]);

        $this->hotelRepository->updateInfoBlocksOrder($hotelId, $request->input('block_ids'));
        return ApiResponse::success(null, 'Порядок обновлен');
    }
}