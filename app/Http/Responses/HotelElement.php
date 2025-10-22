<?php

namespace App\Http\Responses;

/**
 * @OA\Schema(
 *     schema="HotelElement",
 *     title="Элемент отеля",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID отеля"),
 *     @OA\Property(property="name", type="string", description="Название"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
 *     @OA\Property(property="direction", ref="#/components/schemas/DirectionElement", description="Направление"),
 *     @OA\Property(property="resort", ref="#/components/schemas/ResortElement", nullable=true, description="Курорт"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, description="Широта"),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, description="Долгота"),
 *     @OA\Property(property="rating", type="number", format="float", description="Рейтинг"),
 *     @OA\Property(property="stars", type="integer", description="Звездность"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="rest_types", type="array", @OA\Items(ref="#/components/schemas/RestTypeElement"), description="Виды отдыха"),
 *     @OA\Property(property="rooms_count", type="integer", description="Количество номеров"),
 *     @OA\Property(property="min_price", type="number", format="float", nullable=true, description="Минимальная цена"),
 *     @OA\Property(property="photo", ref="#/components/schemas/PhotoElement", nullable=true, description="Основное фото"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Дата удаления")
 * )
 */
class HotelElement
{
    protected $hotel;

    public function __construct($hotel)
    {
        $this->hotel = $hotel;
    }

    public function toArray()
    {
        return [
            'id' => $this->hotel->id,
            'name' => $this->hotel->name,
            'description' => $this->hotel->description,
            'direction' => $this->hotel->direction ? (new DirectionElement($this->hotel->direction))->toArray() : null,
            'resort' => $this->hotel->resort ? (new ResortElement($this->hotel->resort))->toArray() : null,
            'latitude' => $this->hotel->latitude,
            'longitude' => $this->hotel->longitude,
            'rating' => $this->hotel->rating,
            'stars' => $this->hotel->stars,
            'is_active' => $this->hotel->is_active,
            'sort_order' => $this->hotel->sort_order,
            'rest_types' => $this->hotel->restTypes->map(function ($type) {
                return (new RestTypeElement($type))->toArray();
            })->toArray(),
            'rooms_count' => $this->hotel->rooms->count(),
            'min_price' => $this->getMinPrice(),
            'photo' => $this->hotel->photo ? (new PhotoElement($this->hotel->photo))->toArray() : null,
            'created_at' => $this->hotel->created_at,
            'updated_at' => $this->hotel->updated_at,
            'deleted_at' => $this->hotel->deleted_at,
        ];
    }

    private function getMinPrice()
    {
        $prices = collect();
        foreach ($this->hotel->rooms as $room) {
            foreach ($room->prices as $price) {
                $prices->push($price->price);
            }
        }
        return $prices->isEmpty() ? null : $prices->min();
    }
}