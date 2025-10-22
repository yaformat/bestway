<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;
use App\Models\Hotel;
use App\Repositories\HotelRepository;

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

        $response->trashed_count = $this->hotelRepository->trashedCount();
        return ApiResponse::success($response);
    }

    /**
     * Получает детальную информацию об отеле
     */
    public function show($id, Request $request)
    {
        $result = $this->hotelRepository->getHotelWithDetails($id);
        $response = new \App\Http\Responses\HotelElement($result);
        return ApiResponse::success($response);
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rating' => 'nullable|numeric|between:0,5',
            'stars' => 'nullable|integer|between:0,5',
            'is_active' => 'boolean',
            'rest_types' => 'nullable|array',
            'rest_types.*' => 'exists:rest_types,id',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $hotel = $this->hotelRepository->create($data);

        // Привязываем виды отдыха
        if (!empty($data['rest_types'])) {
            $hotel->restTypes()->attach($data['rest_types']);
        }

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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rating' => 'nullable|numeric|between:0,5',
            'stars' => 'nullable|integer|between:0,5',
            'is_active' => 'boolean',
            'rest_types' => 'nullable|array',
            'rest_types.*' => 'exists:rest_types,id',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $hotel = $this->hotelRepository->update($id, $data);

        // Обновляем виды отдыха
        if (isset($data['rest_types'])) {
            $hotel->restTypes()->sync($data['rest_types']);
        }

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

    public function toggleActive($id, Request $request)
    {
        $result = $this->hotelRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }

        return ApiResponse::error('Action forbidden', 409);
    }

}