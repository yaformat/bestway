<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="KitchenCreateRequest",
 *     title="Создание кухни",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *     ),
 * )
 */
class KitchenCreateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'photo_id' => 'nullable|integer|exists:photos,id',
        ];
    }
}