<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="RestTypeElement",
 *     title="Элемент вида отдыха",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID вида отдыха"),
 *     @OA\Property(property="name", type="string", description="Название"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class RestTypeElement
{
    protected $restType;

    public function __construct($restType)
    {
        $this->restType = $restType;
    }

    public function toArray()
    {
        return [
            'id' => $this->restType->id,
            'name' => $this->restType->name,
            'description' => $this->restType->description,
            'is_active' => $this->restType->is_active,
            'sort_order' => $this->restType->sort_order,
            'created_at' => $this->restType->created_at,
            'updated_at' => $this->restType->updated_at,
        ];
    }
}