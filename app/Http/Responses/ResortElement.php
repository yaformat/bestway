<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="ResortElement",
 *     title="Элемент курорта",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID курорта"),
 *     @OA\Property(property="name", type="string", description="Название"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="direction", ref="#/components/schemas/DirectionElement", description="Направление"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, description="Широта"),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, description="Долгота"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="hotels_count", type="integer", description="Количество отелей"),
 *     @OA\Property(property="photo", ref="#/components/schemas/PhotoElement", nullable=true, description="Основное фото"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class ResortElement
{
    protected $resort;

    public function __construct($resort)
    {
        $this->resort = $resort;
    }

    public function toArray()
    {
        return [
            'id' => $this->resort->id,
            'name' => $this->resort->name,
            'description' => $this->resort->description,
            'direction' => $this->resort->direction ? (new DirectionElement($this->resort->direction))->toArray() : null,
            'latitude' => $this->resort->latitude,
            'longitude' => $this->resort->longitude,
            'is_active' => $this->resort->is_active,
            'sort_order' => $this->resort->sort_order,
            'hotels_count' => $this->resort->hotels->count(),
            'photo' => $this->resort->photo ? (new PhotoElement($this->resort->photo))->toArray() : null,
            'created_at' => $this->resort->created_at,
            'updated_at' => $this->resort->updated_at,
        ];
    }
}