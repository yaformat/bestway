<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class BookingPeriod extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomPrices()
    {
        return $this->hasMany(HotelPrice::class);
    }

    public function servicePrices()
    {
        return $this->hasMany(HotelServicePrice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('start_date');
    }
}