<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="TourElement",
 *     title="Элемент тура",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID тура"),
 *     @OA\Property(property="name", type="string", description="Название"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="direction", ref="#/components/schemas/DirectionElement", description="Направление"),
 *     @OA\Property(property="duration_days", type="integer", description="Длительность в днях"),
 *     @OA\Property(property="price", type="number", format="float", description="Цена"),
 *     @OA\Property(property="currency", type="string", description="Валюта"),
 *     @OA\Property(property="difficulty_level", type="string", description="Уровень сложности"),
 *     @OA\Property(property="group_size_min", type="integer", description="Мин размер группы"),
 *     @OA\Property(property="group_size_max", type="integer", description="Макс размер группы"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="program", type="array", @OA\Items(ref="#/components/schemas/TourProgramElement"), description="Программа тура"),
 *     @OA\Property(property="photo", ref="#/components/schemas/PhotoElement", nullable=true, description="Основное фото"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class TourElement
{
    protected $tour;

    public function __construct($tour)
    {
        $this->tour = $tour;
    }

    public function toArray()
    {
        return [
            'id' => $this->tour->id,
            'name' => $this->tour->name,
            'description' => $this->tour->description,
            'direction' => $this->tour->direction ? (new DirectionElement($this->tour->direction))->toArray() : null,
            'duration_days' => $this->tour->duration_days,
            'price' => $this->tour->price,
            'currency' => $this->tour->currency,
            'difficulty_level' => $this->tour->difficulty_level,
            'group_size_min' => $this->tour->group_size_min,
            'group_size_max' => $this->tour->group_size_max,
            'is_active' => $this->tour->is_active,
            'sort_order' => $this->tour->sort_order,
            'program' => $this->tour->program->map(function ($program) {
                return (new TourProgramElement($program))->toArray();
            })->toArray(),
            'photo' => $this->tour->photo ? (new PhotoElement($this->tour->photo))->toArray() : null,
            'created_at' => $this->tour->created_at,
            'updated_at' => $this->tour->updated_at,
        ];
    }
}