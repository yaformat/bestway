<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class HotelPrice extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_room_id',
        'booking_period_id',
        'price',
        'currency',
        'min_nights',
        'max_nights',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_nights' => 'integer',
        'max_nights' => 'integer',
    ];

    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function bookingPeriod()
    {
        return $this->belongsTo(BookingPeriod::class);
    }
}