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
*     @OA\Property(property="currency", type="string", description="Валюта отеля"),
*     @OA\Property(property="hotel_type", type="object", description="Тип отеля"),
*     @OA\Property(property="hotel_type.id", type="string", description="ID типа"),
*     @OA\Property(property="hotel_type.name", type="string", description="Название типа"),
*     @OA\Property(property="hotel_type.icon", type="string", description="Иконка"),
*     @OA\Property(property="latitude", type="number", format="float", nullable=true, description="Широта"),
*     @OA\Property(property="longitude", type="number", format="float", nullable=true, description="Долгота"),
*     @OA\Property(property="rating", type="number", format="float", description="Рейтинг"),
*     @OA\Property(property="is_active", type="boolean", description="Активность"),
*     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
*     @OA\Property(property="rest_types", type="array", @OA\Items(type="object"), description="Виды отдыха"),
*     @OA\Property(property="rooms_count", type="integer", description="Количество номеров"),
*     @OA\Property(property="buildings_count", type="integer", description="Количество строений"),
*     @OA\Property(property="services_count", type="integer", description="Количество услуг"),
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
            'rooms_count' => $this->hotel->rooms->count(),
            'buildings_count' => $this->hotel->buildings->count(),
            'services_count' => $this->hotel->services->count(),
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
        
        // Цены номеров
        foreach ($this->hotel->rooms as $room) {
            foreach ($room->prices as $price) {
                $prices->push([
                    'price' => $price->price,
                    'currency' => $price->currency ?? $this->hotel->currency
                ]);
            }
        }
        
        // Цены услуг
        foreach ($this->hotel->services as $service) {
            foreach ($service->prices as $price) {
                $prices->push([
                    'price' => $price->price,
                    'currency' => $price->currency ?? $this->hotel->currency
                ]);
            }
        }
        
        if ($prices->isEmpty()) {
            return null;
        }
        
        // Конвертируем все цены в валюту отеля для сравнения
        $convertedPrices = $prices->map(function ($item) {
            if ($item['currency'] === $this->hotel->currency) {
                return $item['price'];
            }
            // Здесь можно добавить конвертацию валют
            return $item['price'];
        });
        
        return [
            'amount' => $convertedPrices->min(),
            'currency' => $this->hotel->currency
        ];
    }
}