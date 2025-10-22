<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\Organization;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Аутентификация"},
     *     summary="Регистрация нового пользователя",
     *     description="Берет имя, email и пароль пользователя, регистрирует нового пользователя и возвращает токен доступа.",
     *     operationId="auth.register",
     *     @OA\RequestBody(
     *         description="Регистрационные данные пользователя",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", format="text", description="Имя пользователя"),
     *             @OA\Property(property="organization_name", type="string", format="text", description="Название организации"),
     *             @OA\Property(property="email", type="string", format="email", description="Email пользователя"),
     *             @OA\Property(property="password", type="string", format="password", description="Пароль пользователя"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", description="Повтор пароля")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Успешное создание пользователя"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="Произошла ошибка"
     *     )
     * )
     */
    public function register(UserRegisterRequest $request)
    {
        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $organization = Organization::create([
            'name'  => $request->organization_name,
            'owner_id' => $user->id,
        ]);

        $user->organization_id = $organization->id;
        $user->save();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return ApiResponse::success($user->toArray(), 'Пользователь успешно создан', 201);
    }
    
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Аутентификация"},
     *     summary="Вход пользователя",
     *     description="Берет email и пароль пользователя, если успешно авторизованы, возвращает токен доступа и данные пользователя.",
     *     operationId="auth.login",
     *     @OA\RequestBody(
     *         description="Данные для входа пользователя",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", description="Email пользователя"),
     *             @OA\Property(property="password", type="string", format="password", description="Пароль пользователя")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный вход в систему"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="Произошла ошибка"
     *     )
     * )
     */
    public function login(UserLoginRequest $request)
    {
        $credentials = $request->only(['email','password']);
        if (!Auth::attempt($credentials))
        {
            return ApiResponse::error('Неверный логин или пароль');
        }
    
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return ApiResponse::success([
            'accessToken' => $token,
            'userData' => $user,
            'userAbilities' => [
                ["action" => "manage", "subject" => "all"],
            ],
        ]);
    }  

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     tags={"Аутентификация"},
     *     summary="Обновить токен пользователя",
     *     description="Обновить токен аутентификации для текущего аутентифицированного пользователя",
     *     operationId="auth.refresh",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="accessToken", type="string", description="Новый токен аутентификации"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не аутентифицирован",
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('Ошибка авторизации', 401);
        }
    
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return ApiResponse::success([
            'accessToken' => $token
        ]);
    } 
}