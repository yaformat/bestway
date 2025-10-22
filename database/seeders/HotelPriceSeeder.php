<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HotelRoom;
use App\Models\BookingPeriod;
use App\Models\HotelPrice;

class HotelPriceSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = HotelRoom::with('hotel.bookingPeriods')->get();
        
        foreach ($rooms as $room) {
            foreach ($room->hotel->bookingPeriods as $period) {
                // Базовая цена в зависимости от типа номера
                $basePrice = match(true) {
                    str_contains($room->name, 'Single') => 50,
                    str_contains($room->name, 'Standard') => 80,
                    str_contains($room->name, 'Deluxe') => 120,
                    str_contains($room->name, 'Family') => 150,
                    str_contains($room->name, 'Suite') => 200,
                    str_contains($room->name, 'Presidential') => 350,
                    default => 100,
                };
                
                // Модификатор сезона
                $seasonModifier = match(true) {
                    str_contains($period->name, 'Зимний') => 1.2,
                    str_contains($period->name, 'Летний') => 1.5,
                    str_contains($period->name, 'Весенний') => 1.1,
                    str_contains($period->name, 'Осенний') => 0.9,
                    default => 1.0,
                };
                
                // Модификатор звездности отеля
                $starsModifier = 1 + ($room->hotel->stars * 0.1);
                
                $finalPrice = $basePrice * $seasonModifier * $starsModifier;
                
                HotelPrice::create([
                    'hotel_room_id' => $room->id,
                    'booking_period_id' => $period->id,
                    'price' => round($finalPrice, 2),
                    'currency' => 'USD',
                    'min_nights' => 1,
                    'max_nights' => 30,
                ]);
            }
        }
    }
}