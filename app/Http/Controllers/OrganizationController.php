<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;

use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;

use App\Http\Requests\OrganizationCreateRequest;
use App\Http\Requests\OrganizationUpdateRequest;

use App\Models\Organization;
use App\Repositories\OrganizationRepository;

class OrganizationController extends BaseController
{
    private $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
    * @OA\Post(
    *     path="/api/organization",
    *     tags={"Организации"},
    *     @OA\RequestBody(
    *         description="Данные запроса для списка справочника Оборудование",
    *         required=true,
    *         @OA\JsonContent(
    *           allOf={
    *               @OA\Schema(ref="#/components/schemas/ItemsListBaseRequest")
    *           }
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *           @OA\JsonContent(
    *           allOf={
    *               @OA\Schema(ref="#/components/schemas/SuccessResult"),
    *               @OA\Schema(
    *                   @OA\Property(
    *                      property="data",
    *                      allOf={
    *                          @OA\Schema(
    *                              ref="#/components/schemas/ItemsListBaseResponse"
    *                          ),
    *                          @OA\Schema(
    *                          @OA\Property(
    *                                  property="items",
    *                                  type="array",
    *                                       @OA\Items(ref="#/components/schemas/OrganizationElement")
    *                                   )
    *                               )
    *                           }
    *                   ),
    *             ),
    *           }
    *        )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Неверный запрос.",
    *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Неавторизованный доступ, требуется аутентификация",
    *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
    *     )
    * ),
    * )
    */
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        
        $result = $this->organizationRepository->getItems($parameters);

        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\OrganizationElement($item))->toArray();
        });

        $response = new ItemsListBaseResponse(
            \App\Models\Organization::class, 
            \App\Presets\Sortings\DefaultSortings::class,
            $items, 
            $parameters, 
            $result
        );

        return ApiResponse::success($response);
    }

    /**
     * @OA\Get(
     *     path="/api/organization/{id}",
     *     tags={"Организации"},
     *     summary="Получение данных организации",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID организации",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Неверный запрос",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Неавторизованный доступ, требуется аутентификация",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
     *     )
     * )
     */
    public function show($id, Request $request)
    {
        $result = $this->organizationRepository->getItem($id);

        $response = new \App\Http\Responses\OrganizationElement($result);

        return ApiResponse::success($response);
    }

    /**
     * @OA\Put(
     *     path="/api/organization/create",
     *     tags={"Организации"},
     *     summary="Создание новой организации",
     *     @OA\RequestBody(
     *         request="form",
     *         description="Атрибуты для новой организации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Создание новой организации",
     *         ref="#/components/responses/SuccessResult"
     *     )
     * )
     */
    public function store(OrganizationCreateRequest $request)
    {
        $data = $request->all();

        $organization = Organization::create($data);

        return ApiResponse::success($organization->toArray(), 'Успешно создан', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/organization/{id}/edit",
     *     tags={"Организации"},
     *     summary="Получение данных организации для редактирования",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID организации",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Данные организации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="sort",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="photo",
     *                     ref="#/components/schemas/PhotoElement"
     *                 ),
     *             )
     *         )
     *     )
     * )
     */
    public function edit($id, Request $request)
    {
        $result = $this->organizationRepository->getItem($id);
        return ApiResponse::success($result->toArray());
    }

    /**
     * @OA\Post(
     *     path="/api/organization/update",
     *     tags={"Организации"},
     *     summary="Редактирование организации",
     *     @OA\RequestBody(
     *         request="form",
     *         description="Новые атрибуты для организации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="sort", type="integer"),
     *             @OA\Property(property="photo_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Редактирование организации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             )
     *         )
     *     )
     * )
     */
    public function update($id, OrganizationUpdateRequest $request)
    {
        $data = $request->all();

        $res = Organization::find($data['id']);

        if ($res) {
            $res->fill($data)->save();

            return ApiResponse::success($res->toArray(), 'Элемент обновлен', 200);
        }

        return ApiResponse::error('Элемент не найден', 404);
    }

    /**
     * @OA\Delete(
     *     path="/api/organization/{id}",
     *     tags={"Организации"},
     *     summary="Удаление организации",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID удаляемого организации",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Удаление организации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             )
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $result = $this->organizationRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result);
        }

        return ApiResponse::error('Delete forbidden', 409);
    }

    /**
     * @OA\Post(
     *     path="/api/organization/{id}/restore",
     *     tags={"Организации"},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID организации",
     *         required=true,
     *         @OA\Schema(
     *         type="integer",
     *         example="1"
     *        ),
     *      ),
     *     @OA\Response(response="200", description="Восстановление организации")
     * )
     */
    public function restore($id)
    {
        $result = $this->organizationRepository->restore($id);
        if ($result) {
            return ApiResponse::success($result, 'Успешно восстановлено');
        }

        return ApiResponse::error('Restore forbidden', 409);
    }
    
    public function toggleActive($id, Request $request)
    {
        $result = $this->organizationRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }

        return ApiResponse::error('Action forbidden', 409);
    }

}
