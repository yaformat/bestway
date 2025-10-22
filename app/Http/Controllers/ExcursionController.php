<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;
use App\Models\Excursion;
use App\Repositories\ExcursionRepository;

class ExcursionController extends BaseController
{
    private $excursionRepository;

    public function __construct(ExcursionRepository $excursionRepository)
    {
        $this->excursionRepository = $excursionRepository;
    }

    /**
     * Получает список экскурсий
     */
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->excursionRepository->getExcursionsWithProgram($parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\ExcursionElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Excursion::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        $response->trashed_count = $this->excursionRepository->trashedCount();
        return ApiResponse::success($response);
    }

    /**
     * Получает детальную информацию об экскурсии
     */
    public function show($id, Request $request)
    {
        $result = $this->excursionRepository->getExcursionWithProgram($id);
        $response = new \App\Http\Responses\ExcursionElement($result);
        return ApiResponse::success($response);
    }

    /**
     * Поиск экскурсий по направлению
     */
    public function searchByDirection($directionId, ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->excursionRepository->searchByDirection($directionId, $parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\ExcursionElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Excursion::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        return ApiResponse::success($response);
    }
    /**
     * Создает новую экскурсию
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'duration_hours' => 'required|numeric|min:0.5',
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
        $excursion = $this->excursionRepository->create($data);
        return ApiResponse::success($excursion, 'Экскурсия создана', 201);
    }

    /**
     * Обновляет экскурсию
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
            'duration_hours' => 'required|numeric|min:0.5',
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
        $excursion = $this->excursionRepository->update($id, $data);
        return ApiResponse::success($excursion, 'Экскурсия обновлена');
    }

    /**
     * Удаляет экскурсию
     */
    public function destroy($id)
    {
        $result = $this->excursionRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно удалено');
        }
        return ApiResponse::error('Невозможно удалить экскурсию', 409);
    }

    /**
     * Восстанавливает экскурсию
     */
    public function restore($id)
    {
        $result = $this->excursionRepository->restore($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно восстановлено');
        }
        return ApiResponse::error('Невозможно восстановить экскурсию', 409);
    }

    /**
     * Копирует экскурсию
     */
    public function copy($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $newData = ['name' => $request->name];
        $copy = $this->excursionRepository->copy($id, $newData);
        return ApiResponse::success($copy, 'Экскурсия скопирована', 201);
    }

    public function toggleActive($id, Request $request)
    {
        $result = $this->excursionRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }

        return ApiResponse::error('Action forbidden', 409);
    }
}