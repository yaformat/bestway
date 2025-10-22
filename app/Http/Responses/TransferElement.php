<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="TransferElement",
 *     title="Элемент трансфера",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID трансфера"),
 *     @OA\Property(property="name", type="string", description="Название"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="type", type="string", description="Тип трансфера"),
 *     @OA\Property(property="from_location", type="string", description="Откуда"),
 *     @OA\Property(property="to_location", type="string", description="Куда"),
 *     @OA\Property(property="price", type="number", format="float", description="Цена"),
 *     @OA\Property(property="currency", type="string", description="Валюта"),
 *     @OA\Property(property="capacity", type="integer", description="Вместимость"),
 *     @OA\Property(property="duration_minutes", type="integer", nullable=true, description="Длительность в минутах"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="photo", ref="#/components/schemas/PhotoElement", nullable=true, description="Основное фото"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class TransferElement
{
    protected $transfer;

    public function __construct($transfer)
    {
        $this->transfer = $transfer;
    }

    public function toArray()
    {
        return [
            'id' => $this->transfer->id,
            'name' => $this->transfer->name,
            'description' => $this->transfer->description,
            'type' => $this->transfer->type,
            'from_location' => $this->transfer->from_location,
            'to_location' => $this->transfer->to_location,
            'price' => $this->transfer->price,
            'currency' => $this->transfer->currency,
            'capacity' => $this->transfer->capacity,
            'duration_minutes' => $this->transfer->duration_minutes,
            'is_active' => $this->transfer->is_active,
            'sort_order' => $this->transfer->sort_order,
            'photo' => $this->transfer->photo ? (new PhotoElement($this->transfer->photo))->toArray() : null,
            'created_at' => $this->transfer->created_at,
            'updated_at' => $this->transfer->updated_at,
        ];
    }
}