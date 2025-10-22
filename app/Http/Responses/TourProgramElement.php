<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="TourProgramElement",
 *     title="Элемент программы тура",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID программы"),
 *     @OA\Property(property="day", type="integer", description="День"),
 *     @OA\Property(property="title", type="string", description="Заголовок"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="time", type="string", format="time", nullable=true, description="Время"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки")
 * )
 */
class TourProgramElement
{
    protected $program;

    public function __construct($program)
    {
        $this->program = $program;
    }

    public function toArray()
    {
        return [
            'id' => $this->program->id,
            'day' => $this->program->day,
            'title' => $this->program->title,
            'description' => $this->program->description,
            'time' => $this->program->time,
            'sort_order' => $this->program->sort_order,
        ];
    }
}