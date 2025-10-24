<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelServicePrice extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_service_id',
        'booking_period_id',
        'price',
        'currency',
        'pricing_type',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function service()
    {
        return $this->belongsTo(HotelService::class, 'hotel_service_id');
    }

    public function bookingPeriod()
    {
        return $this->belongsTo(BookingPeriod::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}