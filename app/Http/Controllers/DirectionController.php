<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;
use App\Models\Direction;
use App\Repositories\DirectionRepository;

class DirectionController extends BaseController
{
    private $directionRepository;

    public function __construct(DirectionRepository $directionRepository)
    {
        $this->directionRepository = $directionRepository;
    }

    /**
     * Получает список направлений
     */
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        $result = $this->directionRepository->getItems($parameters);
        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\DirectionElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            Direction::class,
            \App\Presets\Sortings\DefaultSortings::class,
            $items,
            $parameters,
            $result
        );

        $response->trashed_count = $this->directionRepository->trashedCount();
        return ApiResponse::success($response);
    }

    /**
     * Получает детальную информацию о направлении
     */
    public function show($id, Request $request)
    {
        $result = $this->directionRepository->getItem($id);
        $response = new \App\Http\Responses\DirectionElement($result);
        return ApiResponse::success($response);
    }

    /**
     * Получает корневые направления
     */
    public function getRootDirections()
    {
        $directions = $this->directionRepository->getRootDirections();
        $items = $directions->map(function ($item) {
            return (new \App\Http\Responses\DirectionElement($item))->toArray();
        });
        return ApiResponse::success($items);
    }

    /**
     * Получает дочерние направления
     */
    public function getChildDirections($parentId)
    {
        $directions = $this->directionRepository->getChildDirections($parentId);
        $items = $directions->map(function ($item) {
            return (new \App\Http\Responses\DirectionElement($item))->toArray();
        });
        return ApiResponse::success($items);
    }

    /**
     * Получает полное дерево направлений
     */
    public function getDirectionTree()
    {
        $tree = $this->directionRepository->getDirectionTree();
        $items = $tree->map(function ($item) {
            return (new \App\Http\Responses\DirectionElement($item))->toArray();
        });
        return ApiResponse::success($items);
    }

    /**
     * Создает новое направление
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:directions,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $direction = $this->directionRepository->create($data);
        return ApiResponse::success($direction, 'Направление создано', 201);
    }

    /**
     * Обновляет направление
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:directions,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $direction = $this->directionRepository->update($id, $data);
        return ApiResponse::success($direction, 'Направление обновлено');
    }

    /**
     * Удаляет направление
     */
    public function destroy($id)
    {
        $result = $this->directionRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно удалено');
        }
        return ApiResponse::error('Невозможно удалить направление', 409);
    }

    /**
     * Восстанавливает направление
     */
    public function restore($id)
    {
        $result = $this->directionRepository->restore($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно восстановлено');
        }
        return ApiResponse::error('Невозможно восстановить направление', 409);
    }

    public function toggleActive($id, Request $request)
    {
        $result = $this->directionRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }

        return ApiResponse::error('Action forbidden', 409);
    }
}