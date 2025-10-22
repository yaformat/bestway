<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
* @OA\Schema(
*     schema="SortItemsRequest",
*     title="Сортировка списка элементов",
*     description="Передача массива ID. Например: [1,3,2]",
*     type="array",
*     @OA\Items(type="integer", example="1")
* )
*/
class SortItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            '*' => 'integer',
        ];
    }
}
