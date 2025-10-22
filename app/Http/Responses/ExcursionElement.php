<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="ExcursionElement",
 *     title="Элемент экскурсии",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID экскурсии"),
 *     @OA\Property(property="name", type="string", description="Название"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="direction", ref="#/components/schemas/DirectionElement", description="Направление"),
 *     @OA\Property(property="duration_hours", type="number", format="float", description="Длительность в часах"),
 *     @OA\Property(property="price", type="number", format="float", description="Цена"),
 *     @OA\Property(property="currency", type="string", description="Валюта"),
 *     @OA\Property(property="difficulty_level", type="string", description="Уровень сложности"),
 *     @OA\Property(property="group_size_min", type="integer", description="Мин размер группы"),
 *     @OA\Property(property="group_size_max", type="integer", description="Макс размер группы"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="program", type="array", @OA\Items(ref="#/components/schemas/ExcursionProgramElement"), description="Программа экскурсии"),
 *     @OA\Property(property="photo", ref="#/components/schemas/PhotoElement", nullable=true, description="Основное фото"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class ExcursionElement
{
    protected $excursion;

    public function __construct($excursion)
    {
        $this->excursion = $excursion;
    }

    public function toArray()
    {
        return [
            'id' => $this->excursion->id,
            'name' => $this->excursion->name,
            'description' => $this->excursion->description,
            'direction' => $this->excursion->direction ? (new DirectionElement($this->excursion->direction))->toArray() : null,
            'duration_hours' => $this->excursion->duration_hours,
            'price' => $this->excursion->price,
            'currency' => $this->excursion->currency,
            'difficulty_level' => $this->excursion->difficulty_level,
            'group_size_min' => $this->excursion->group_size_min,
            'group_size_max' => $this->excursion->group_size_max,
            'is_active' => $this->excursion->is_active,
            'sort_order' => $this->excursion->sort_order,
            'program' => $this->excursion->program->map(function ($program) {
                return (new ExcursionProgramElement($program))->toArray();
            })->toArray(),
            'photo' => $this->excursion->photo ? (new PhotoElement($this->excursion->photo))->toArray() : null,
            'created_at' => $this->excursion->created_at,
            'updated_at' => $this->excursion->updated_at,
        ];
    }
}