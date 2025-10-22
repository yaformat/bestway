<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;

use App\Http\Requests\ItemsListBaseRequest;
use App\Http\Responses\ItemsListBaseResponse;

use App\Models\User;

use App\Repositories\UserRepository;

class UserController extends BaseController
{
    private $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

/**
* @OA\Post(
*     path="/api/users",
*     tags={"Пользователи"},
*     @OA\RequestBody(
*         description="Данные запроса для списка пользователей",
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
*                                       @OA\Items(ref="#/components/schemas/UserInfoElement")
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
* )
*/
    public function index(ItemsListBaseRequest $request)
    {
        $parameters = new \App\Utils\ItemsListSearchParameters($request);
        
        $result = $this->userRepository->getItems($parameters);

        $items = $result->getCollection()->map(function ($item) {
            return (new \App\Http\Responses\UserInfoElement($item))->toArray();
        });
    
        $response = new ItemsListBaseResponse(
            \App\Models\User::class, 
            \App\Presets\Sortings\DefaultSortings::class,
            $items, 
            $parameters, 
            $result
        );
    
        return ApiResponse::success($response);
    }

    /**
     * @OA\Post(
     *     path="/api/users/create",
     *     tags={"Пользователи"},
     *      @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Имя нового пользователя",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         example="John"
     *        ),
     *      ),
     *     @OA\Response(response="200", description="Отправка данных для создания нового пользователя")
     * )
     */
    public function create(UserCreateRequest $request)
    {
        $userData = $request->input('data', []);
        //
        $data = [
            'name' => $userData['name'],
            'notes' => $userData['notes'],
        ];
        
        $user = User::create($data);
        
        return ApiResource::success($user->toArray(), '', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Пользователи"},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID пользователя",
     *         required=true,
     *         @OA\Schema(
     *         type="integer",
     *         example="1"
     *        ),
     *      ),
     *     @OA\Response(response="200", description="Получение данных пользователя для просмотра")
     * )
     */
    public function show($id, Request $request)
    {
        $result = $this->userRepository->getItem($id);

        $response = new \App\Http\Responses\UserInfoElementDetails($result);

        return ApiResponse::success($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, UserUpdateRequest $request)
    {
        //
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Пользователи"},
     *     summary="Удаление пользователя",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID удаляемого пользователя",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Удаление пользователя",
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
        $result = $this->userRepository->delete($id);
        if ($result) {
            return ApiResponse::success($result);
        }

        return ApiResponse::error('Delete forbidden', 409);
    }
    
    public function toggleActive($id, Request $request)
    {
        $result = $this->userRepository->toggleActive($id);
        if ($result) {
            return ApiResponse::success($result, 'Активность элемента изменена');
        }

        return ApiResponse::error('Action forbidden', 409);
    }
    
}
