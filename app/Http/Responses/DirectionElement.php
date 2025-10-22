<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="DirectionElement",
 *     title="Элемент направления",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID направления"),
 *     @OA\Property(property="name", type="string", description="Название"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, description="ID родителя"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, description="Широта"),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, description="Долгота"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="full_path", type="string", description="Полный путь иерархии"),
 *     @OA\Property(property="children", type="array", @OA\Items(ref="#/components/schemas/DirectionElement"), description="Дочерние направления"),
 *     @OA\Property(property="photo", ref="#/components/schemas/PhotoElement", nullable=true, description="Основное фото"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class DirectionElement
{
    protected $direction;

    public function __construct($direction)
    {
        $this->direction = $direction;
    }

    public function toArray()
    {
        return [
            'id' => $this->direction->id,
            'name' => $this->direction->name,
            'description' => $this->direction->description,
            'parent_id' => $this->direction->parent_id,
            'latitude' => $this->direction->latitude,
            'longitude' => $this->direction->longitude,
            'is_active' => $this->direction->is_active,
            'sort_order' => $this->direction->sort_order,
            'full_path' => $this->direction->full_path,
            'children' => $this->direction->children->map(function ($child) {
                return (new self($child))->toArray();
            })->toArray(),
            'photo' => $this->direction->photo ? (new PhotoElement($this->direction->photo))->toArray() : null,
            'created_at' => $this->direction->created_at,
            'updated_at' => $this->direction->updated_at,
        ];
    }
}