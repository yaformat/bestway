<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="ExcursionProgramElement",
 *     title="Элемент программы экскурсии",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID программы"),
 *     @OA\Property(property="title", type="string", description="Заголовок"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="duration_minutes", type="integer", nullable=true, description="Длительность в минутах"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки")
 * )
 */
class ExcursionProgramElement
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
            'title' => $this->program->title,
            'description' => $this->program->description,
            'duration_minutes' => $this->program->duration_minutes,
            'sort_order' => $this->program->sort_order,
        ];
    }
}