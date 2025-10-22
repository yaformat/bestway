<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="KitchenUpdateRequest",
 *     title="Обновление кухни",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *     ),
 * )
 */
class KitchenUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'name' => 'required|string',
            'photo_id' => 'nullable|integer|exists:photos,id',
            'photoMarkedForDeletion' => 'sometimes|boolean',
        ];
    }
}