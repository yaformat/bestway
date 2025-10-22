<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;
use App\Models\Tour;
use App\Repositories\TourRepository;

class TourController extends BaseController
{
    private $tourRepository;

    public function __construct(TourRepository $tourRepository)
    {
        $this->tourRepository = $tourRepository;
    }

    /**
     * Получает список туров
     */
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->tourRepository->getToursWithProgram($parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\TourElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Tour::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        $response->trashed_count = $this->tourRepository->trashedCount();
        return ApiResponse::success($response);
    }

    /**
     * Получает детальную информацию о туре
     */
    public function show($id, Request $request)
    {
        $result = $this->tourRepository->getTourWithProgram($id);
        $response = new \App\Http\Responses\TourElement($result);
        return ApiResponse::success($response);
    }

    /**
     * Поиск туров по направлению
     */
    public function searchByDirection($directionId, ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->tourRepository->searchByDirection($directionId, $parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\TourElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Tour::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        return ApiResponse::success($response);
    }

    /**
     * Создает новый тур
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'group_size_min' => 'required|integer|min:1',
            'group_size_max' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $tour = $this->tourRepository->create($data);
        return ApiResponse::success($tour, 'Тур создан', 201);
    }

    /**
     * Обновляет тур
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'group_size_min' => 'required|integer|min:1',
            'group_size_max' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'photo_ids' => 'nullable|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $data = $request->all();
        $tour = $this->tourRepository->update($id, $data);
        return ApiResponse::success($tour, 'Тур обновлен');
    }

    /**
     * Удаляет тур
     */
    public function destroy($id)
    {
        $result = $this->tourRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно удалено');
        }
        return ApiResponse::error('Невозможно удалить тур', 409);
    }

    /**
     * Восстанавливает тур
     */
    public function restore($id)
    {
        $result = $this->tourRepository->restore($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно восстановлено');
        }
        return ApiResponse::error('Невозможно восстановить тур', 409);
    }

    /**
     * Копирует тур
     */
    public function copy($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $newData = ['name' => $request->name];
        $copy = $this->tourRepository->copy($id, $newData);
        return ApiResponse::success($copy, 'Тур скопирован', 201);
    }
}