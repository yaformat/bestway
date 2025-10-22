<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;

class DataController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/global-data",
     *     tags={"Общие данные"},
     *     @OA\Response(response="200", description="")
     * )
     */
    public function index()
    {
        // $users = Cache::remember('users', 60 * 60, function () {
        //     return \App\Models\User::all();
        // });
        $users = [];

        $rest_types = Cache::remember('rest_types', 60 * 60, function () {
            return \App\Models\RestType::select('id', 'name')->get();
        });

        $preparedData = compact(
            'users',
            'rest_types',
            // другие сущности...
        );

        return ApiResponse::success($preparedData);
    }

    /**
     * @OA\Get(
     *     path="/api/navigation",
     *     tags={"Общие данные"},
     *     @OA\Response(response="200", description="")
     * )
     */
    public function navigation()
    {
        return ApiResponse::success([]);
    }
}
