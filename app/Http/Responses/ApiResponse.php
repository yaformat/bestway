<?php

namespace App\Http\Responses;

/**
* @OA\Response(
*     response="SuccessResult",
*     description="Успешный ответ",
*     @OA\JsonContent(ref="#/components/schemas/SuccessResult")
* ),
* @OA\Schema(
*     schema="SuccessResult",
*     @OA\Property(property="success", type="boolean", default=true, description="This will always be true"),
*     @OA\Property(property="message", type="string", example="Success message"),
*     @OA\Property(property="data", type="object")
* ),
* @OA\Response(
*     response="ErrorResult",
*     description="Ошибочный ответ",
*     @OA\JsonContent(ref="#/components/schemas/ErrorResult")
* ),
* @OA\Schema(
*     schema="ErrorResult",
*     @OA\Property(property="success", type="boolean", default=false, description="This will always be false"),
*     @OA\Property(property="message", type="string", example="Error message"),
* )
*/
class ApiResponse
{
    /**
     * Send a successful JSON response.
     *
     * @param  mixed        $data
     * @param  string|null  $message
     * @param  int          $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data, string $message = null, int $status = 200)
    {
        $data = is_object($data) ? $data->toArray() : $data;
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Send an error JSON response.
     *
     * @param  string  $message
     * @param  int     $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message, int $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}