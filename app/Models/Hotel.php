<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;
use App\Traits\HasPhotos;

class Hotel extends BaseModel
{
    use HasFactory, SoftDeletes, HasPhotos, SortOrder;

    protected $fillable = [
        'name',
        'description',
        'direction_id',
        'resort_id',
        'currency',
        'hotel_type',
        'rest_types', // Добавляем поле для хранения JSON массива типов отдыха
        'sort_order',
        'is_active',
        'latitude',
        'longitude',
        'rating'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'rest_types' => 'array' // Преобразуем JSON в массив
    ];

    protected $hidden = [
        'laravel_through_key',
    ];

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function resort()
    {
        return $this->belongsTo(Resort::class);
    }

    public function rooms()
    {
        return $this->hasMany(HotelRoom::class);
    }

    public function buildings()
    {
        return $this->hasMany(HotelBuilding::class);
    }

    public function infoBlocks()
    {
        return $this->hasMany(HotelInfoBlock::class);
    }

    public function services()
    {
        return $this->hasMany(HotelService::class);
    }

    public function bookingPeriods()
    {
        return $this->hasMany(BookingPeriod::class);
    }

    public function prices()
    {
        return $this->hasManyThrough(HotelPrice::class, HotelRoom::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Получение основного направления (если resort не указан)
    public function getMainDirectionAttribute()
    {
        return $this->resort?->direction ?? $this->direction;
    }

    // Получение типа отеля из конфига
    public function getHotelTypeInfoAttribute()
    {
        return config('hotels.types.' . $this->hotel_type);
    }

    // Получение типов отдыха из конфига
    public function getRestTypesInfoAttribute()
    {
        if (!$this->rest_types) return collect();
        
        return collect($this->rest_types)->map(function($typeId) {
            return config('hotels.rest_types.' . $typeId);
        })->filter();
    }

    // Получение всех цен (номера + услуги)
    public function getAllPrices()
    {
        $roomPrices = $this->prices()->with(['hotelRoom', 'bookingPeriod'])->get();
        $servicePrices = collect();
        
        foreach ($this->services as $service) {
            $servicePrices = $servicePrices->merge($service->prices()->with(['service', 'bookingPeriod'])->get());
        }
        
        return $roomPrices->merge($servicePrices);
    }

    // Методы для работы со строениями
    public function getBuildingsWithRooms()
    {
        return $this->buildings()->with(['rooms' => function($query) {
            $query->active()->with('prices.bookingPeriod');
        }])->orderBy('sort_order')->get();
    }

    // Методы для работы с дополнительными услугами
    public function getServicesWithPrices()
    {
        return $this->services()->with(['prices.bookingPeriod'])->orderBy('sort_order')->get();
    }

    // Методы для работы с информационными блоками
    public function getInfoBlocksOrdered()
    {
        return $this->infoBlocks()->orderBy('sort_order')->get();
    }
}