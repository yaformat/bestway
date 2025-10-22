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
        'sort_order',
        'is_active',
        'latitude',
        'longitude',
        'rating',
        'stars',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'stars' => 'integer',
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

    public function bookingPeriods()
    {
        return $this->hasMany(BookingPeriod::class);
    }

    public function prices()
    {
        return $this->hasManyThrough(HotelPrice::class, HotelRoom::class);
    }

    // Виды отдыха (через полиморфную связь)
    public function restTypes()
    {
        return $this->morphToMany(RestType::class, 'entity', 'entity_rest_types');
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
}