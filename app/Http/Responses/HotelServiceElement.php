<?php
namespace App\Http\Responses;

/**
* @OA\Schema(
*     schema="HotelServiceElement",
*     title="Элемент услуги отеля",
*     type="object",
*     @OA\Property(property="id", type="integer", description="ID услуги"),
*     @OA\Property(property="name", type="string", description="Название"),
*     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
*     @OA\Property(property="is_active", type="boolean", description="Активность"),
*     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
*     @OA\Property(property="prices", type="array", @OA\Items(type="object"), description="Цены")
* )
*/
class HotelServiceElement
{
    protected $service;
    
    public function __construct($service)
    {
        $this->service = $service;
    }
    
    public function toArray()
    {
        return [
            'id' => $this->service->id,
            'name' => $this->service->name,
            'description' => $this->service->description,
            'is_active' => $this->service->is_active,
            'sort_order' => $this->service->sort_order,
            'prices' => $this->service->prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'price' => $price->price,
                    'currency' => $price->currency,
                    'pricing_type' => $price->pricing_type,
                    'is_active' => $price->is_active,
                    'booking_period' => $price->bookingPeriod ? [
                        'id' => $price->bookingPeriod->id,
                        'start_date' => $price->bookingPeriod->start_date,
                        'end_date' => $price->bookingPeriod->end_date
                    ] : null
                ];
            })->toArray()
        ];
    }
}