<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;
use App\Models\Transfer;
use App\Repositories\TransferRepository;

class TransferController extends BaseController
{
    private $transferRepository;

    public function __construct(TransferRepository $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    /**
     * Получает список трансферов
     */
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->transferRepository->getTransfersWithPhotos($parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\TransferElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Transfer::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        $response->trashed_count = $this->transferRepository->trashedCount();
        return ApiResponse::success($response);
    }

    /**
     * Получает детальную информацию о трансфере
     */
    public function show($id, Request $request)
    {
        $result = $this->transferRepository->getItem($id);
        $response = new \App\Http\Responses\TransferElement($result);
        return ApiResponse::success($response);
    }

    /**
     * Поиск трансферов по типу
     */
    public function searchByType($type, ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->transferRepository->searchByType($type, $parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\TransferElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Transfer::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        return ApiResponse::success($response);
    }

    /**
     * Создает новый трансфер
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:50',
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'capacity' => 'required|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $transfer = $this->transferRepository->create($data);
        return ApiResponse::success($transfer, 'Трансфер создан', 201);
    }

    /**
     * Обновляет трансфер
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:50',
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'capacity' => 'required|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $transfer = $this->transferRepository->update($id, $data);
        return ApiResponse::success($transfer, 'Трансфер обновлен');
    }

    /**
     * Удаляет трансфер
     */
    public function destroy($id)
    {
        $result = $this->transferRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно удалено');
        }
        return ApiResponse::error('Невозможно удалить трансфер', 409);
    }

    /**
     * Восстанавливает трансфер
     */
    public function restore($id)
    {
        $result = $this->transferRepository->restore($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно восстановлено');
        }
        return ApiResponse::error('Невозможно восстановить трансфер', 409);
    }

    /**
     * Копирует трансфер
     */
    public function copy($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $newData = ['name' => $request->name];
        $copy = $this->transferRepository->copy($id, $newData);
        return ApiResponse::success($copy, 'Трансфер скопирован', 201);
    }

    public function toggleActive($id, Request $request)
    {
        $result = $this->transferRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }

        return ApiResponse::error('Action forbidden', 409);
    }
}