<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;
use App\Models\Resort;
use App\Repositories\ResortRepository;

class ResortController extends BaseController
{
    private $resortRepository;

    public function __construct(ResortRepository $resortRepository)
    {
        $this->resortRepository = $resortRepository;
    }

    /**
     * Получает список курортов
     */
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->resortRepository->getResortsWithHotels($parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\ResortElement($item))->toArray();
        });
        $response = new ItemsListBaseResponse(
            Resort::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );
        $response->trashed_count = $this->resortRepository->trashedCount();
        return ApiResponse::success($response);
    }

    /**
     * Получает детальную информацию о курорте
     */
    public function show($id, Request $request)
    {
        $result = $this->resortRepository->getResortWithHotels($id);
        $response = new \App\Http\Responses\ResortElement($result);
        return ApiResponse::success($response);
    }

    /**
     * Поиск курортов по направлению
     */
    public function searchByDirection($directionId, ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->resortRepository->searchByDirection($directionId, $parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\ResortElement($item))->toArray();
        });
        $response = new ItemsListBaseResponse(
            Resort::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );
        return ApiResponse::success($response);
    }

    /**
     * Создает новый курорт
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'altitude' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $resort = $this->resortRepository->create($data);
        return ApiResponse::success($resort, 'Курорт создан', 201);
    }

    /**
     * Обновляет курорт
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'altitude' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $resort = $this->resortRepository->update($id, $data);
        return ApiResponse::success($resort, 'Курорт обновлен');
    }

    /**
     * Удаляет курорт
     */
    public function destroy($id)
    {
        $result = $this->resortRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно удалено');
        }
        return ApiResponse::error('Невозможно удалить курорт', 409);
    }

    /**
     * Восстанавливает курорт
     */
    public function restore($id)
    {
        $result = $this->resortRepository->restore($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно восстановлено');
        }
        return ApiResponse::error('Невозможно восстановить курорт', 409);
    }

    /**
     * Копирует курорт
     */
    public function copy($id, Request $request)
    {
        $newData = ['name' => $request->name ?? 'Новый курорт'];
        $copy = $this->resortRepository->copy($id, $newData);
        return ApiResponse::success($copy, 'Курорт скопирован', 201);
    }

    /**
     * Переключает активность курорта
     */
    public function toggleActive($id, Request $request)
    {
        $result = $this->resortRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }
        return ApiResponse::error('Action forbidden', 409);
    }

    /**
     * Получает отели курорта
     */
    public function hotels($id, Request $request)
    {
        $hotels = $this->resortRepository->getResortHotels($id);
        $items = $hotels->map(function ($item) {
            return (new \App\Http\Responses\HotelElement($item))->toArray();
        });
        return ApiResponse::success($items);
    }
}