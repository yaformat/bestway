<?php
namespace App\Http\Responses;

/**
* @OA\Schema(
*     schema="HotelElementDetails",
*     title="Детальная информация об отеле",
*     type="object",
*     @OA\Property(property="id", type="integer", description="ID отеля"),
*     @OA\Property(property="name", type="string", description="Название"),
*     @OA\Property(property="description", type="string", nullable=true, description="Описание"),
*     @OA\Property(property="direction", ref="#/components/schemas/DirectionElement", description="Направление"),
*     @OA\Property(property="resort", ref="#/components/schemas/ResortElement", nullable=true, description="Курорт"),
*     @OA\Property(property="currency", type="string", description="Валюта отеля"),
*     @OA\Property(property="hotel_type", type="object", description="Тип отеля"),
*     @OA\Property(property="latitude", type="number", format="float", nullable=true, description="Широта"),
*     @OA\Property(property="longitude", type="number", format="float", nullable=true, description="Долгота"),
*     @OA\Property(property="rating", type="number", format="float", description="Рейтинг"),
*     @OA\Property(property="is_active", type="boolean", description="Активность"),
*     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
*     @OA\Property(property="rest_types", type="array", @OA\Items(type="object"), description="Виды отдыха"),
*     @OA\Property(property="info_blocks", type="array", @OA\Items(type="object"), description="Информационные блоки"),
*     @OA\Property(property="buildings", type="array", @OA\Items(ref="#/components/schemas/HotelBuildingElement"), description="Строения"),
*     @OA\Property(property="rooms_without_building", type="array", @OA\Items(ref="#/components/schemas/HotelRoomElement"), description="Номера без строений"),
*     @OA\Property(property="services", type="array", @OA\Items(ref="#/components/schemas/HotelServiceElement"), description="Дополнительные услуги"),
*     @OA\Property(property="booking_periods", type="array", @OA\Items(ref="#/components/schemas/BookingPeriodElement"), description="Периоды бронирования"),
*     @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/PhotoElement"), description="Фотографии"),
*     @OA\Property(property="price_ranges", type="array", @OA\Items(type="object"), description="Диапазоны цен по периодам"),
*     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
*     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления"),
*     @OA\Property(property="deleted_at", type="string", format="date-time", description="Дата удаления")
* )
*/
class HotelElementDetails
{
    protected $hotel;
    
    public function __construct($hotel)
    {
        $this->hotel = $hotel;
    }
    
    public function toArray()
    {
        //return $this->hotel->toArray();
        return [
            'id' => $this->hotel->id,
            'name' => $this->hotel->name,
            'description' => $this->hotel->description,
            'direction' => $this->hotel->direction ? (new DirectionElement($this->hotel->direction))->toArray() : null,
            'resort' => $this->hotel->resort ? (new ResortElement($this->hotel->resort))->toArray() : null,
            'currency' => $this->hotel->currency,
            'hotel_type' => $this->hotel->hotelTypeInfo ? [
                'id' => $this->hotel->hotelTypeInfo['id'],
                'name' => $this->hotel->hotelTypeInfo['name'],
                'icon' => $this->hotel->hotelTypeInfo['icon']
            ] : null,
            'latitude' => $this->hotel->latitude,
            'longitude' => $this->hotel->longitude,
            'rating' => $this->hotel->rating,
            'is_active' => $this->hotel->is_active,
            'sort_order' => $this->hotel->sort_order,
            'rest_types' => collect($this->hotel->restTypesInfo)->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'icon' => $type->icon
                ];
            })->toArray(),
            'info_blocks' => $this->hotel->infoBlocks->map(function ($block) {
                return [
                    'id' => $block->id,
                    'key' => $block->block_key,
                    'title' => $block->blockInfo->title ?? $block->block_key,
                    'icon' => $block->blockInfo->icon ?? 'mdi-information',
                    'content' => $block->content,
                    'is_active' => $block->is_active,
                    'sort_order' => $block->sort_order
                ];
            })->toArray(),
            'buildings' => $this->hotel->buildings->map(function ($building) {
                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'description' => $building->description,
                    'rooms' => $building->rooms->map(function ($room) {
                        return (new HotelRoomElement($room))->toArray();
                    })->toArray(),
                    'is_active' => $building->is_active,
                    'sort_order' => $building->sort_order
                ];
            })->toArray(),
            'rooms' => $this->hotel->rooms->filter(function ($room) {
                return !is_null($room->hotel_building_id);
            })->map(function ($room) {
                return (new HotelRoomElement($room))->toArray();
            })->toArray(),
            'rooms_without_building' => $this->hotel->rooms->filter(function ($room) {
                return is_null($room->hotel_building_id);
            })->map(function ($room) {
                return (new HotelRoomElement($room))->toArray();
            })->toArray(),
            'services' => $this->hotel->services->map(function ($service) {
                return (new HotelServiceElement($service))->toArray();
            })->toArray(),
            'booking_periods' => $this->hotel->bookingPeriods->map(function ($period) {
                return (new BookingPeriodElement($period))->toArray();
            })->toArray(),
            'photos' => $this->hotel->photos ? $this->hotel->photos->map(function ($photo) {
                return (new PhotoElement($photo))->toArray();
            })->toArray() : null,
            'price_ranges' => $this->getPriceRanges(),
            'created_at' => $this->hotel->created_at,
            'updated_at' => $this->hotel->updated_at,
            'deleted_at' => $this->hotel->deleted_at,
        ];
    }
    
    private function getPriceRanges()
    {
        $priceRanges = [];
        
        foreach ($this->hotel->bookingPeriods as $period) {
            $prices = collect();
            
            // Цены номеров за период
            foreach ($this->hotel->rooms as $room) {
                $roomPrice = $room->prices->where('booking_period_id', $period->id)->first();
                if ($roomPrice) {
                    $prices->push($roomPrice->price);
                }
            }
            
            // Цены услуг за период
            foreach ($this->hotel->services as $service) {
                $servicePrice = $service->prices->where('booking_period_id', $period->id)->first();
                if ($servicePrice) {
                    $prices->push($servicePrice->price);
                }
            }
            
            $priceRanges[] = [
                'period' => [
                    'id' => $period->id,
                    'start_date' => $period->start_date,
                    'end_date' => $period->end_date,
                    'is_active' => $period->is_active
                ],
                'min_price' => $prices->isEmpty() ? null : [
                    'amount' => $prices->min(),
                    'currency' => $this->hotel->currency
                ],
                'max_price' => $prices->isEmpty() ? null : [
                    'amount' => $prices->max(),
                    'currency' => $this->hotel->currency
                ]
            ];
        }
        
        return $priceRanges;
    }
}