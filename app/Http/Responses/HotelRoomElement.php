<?php
namespace App\Http\Responses;

/**
* @OA\Schema(
*     schema="HotelRoomElement",
*     title="Элемент номера отеля",
*     type="object",
*     @OA\Property(property="id", type="integer", description="ID номера"),
*     @OA\Property(property="name", type="string", description="Название"),
*     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
*     @OA\Property(property="capacity", type="integer", description="Вместимость"),
*     @OA\Property(property="beds_count", type="integer", description="Количество кроватей"),
*     @OA\Property(property="is_active", type="boolean", description="Активность"),
*     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
*     @OA\Property(property="building", type="object", nullable=true, description="Строение"),
*     @OA\Property(property="prices", type="array", @OA\Items(type="object"), description="Цены"),
*     @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/PhotoElement"), description="Фотографии")
* )
*/
class HotelRoomElement
{
    protected $room;
    
    public function __construct($room)
    {
        $this->room = $room;
    }
    
    public function toArray()
    {
        return [
            'id' => $this->room->id,
            'name' => $this->room->name,
            'description' => $this->room->description,
            'capacity' => $this->room->capacity,
            'beds_count' => $this->room->beds_count,
            'is_active' => $this->room->is_active,
            'sort_order' => $this->room->sort_order,
            'building' => $this->room->building ? [
                'id' => $this->room->building->id,
                'name' => $this->room->building->name
            ] : null,
            'prices' => $this->room->prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'price' => $price->price,
                    'currency' => $price->currency,
                    'min_nights' => $price->min_nights,
                    'max_nights' => $price->max_nights,
                    'booking_period' => $price->bookingPeriod ? [
                        'id' => $price->bookingPeriod->id,
                        'start_date' => $price->bookingPeriod->start_date,
                        'end_date' => $price->bookingPeriod->end_date
                    ] : null
                ];
            })->toArray(),
            'photos' => $this->room->photos ? $this->room->photos->map(function ($photo) {
                return (new PhotoElement($photo))->toArray();
            })->toArray() : null
        ];
    }
}