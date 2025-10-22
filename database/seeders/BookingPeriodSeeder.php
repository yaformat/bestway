<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\BookingPeriod;
use Carbon\Carbon;

class BookingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::all();
        
        foreach ($hotels as $hotel) {
            // Создаем периоды бронирования на 2024-2025 годы
            $periods = [
                [
                    'name' => 'Зимний сезон',
                    'start_date' => Carbon::create(2024, 12, 1),
                    'end_date' => Carbon::create(2025, 2, 28),
                ],
                [
                    'name' => 'Весенний сезон',
                    'start_date' => Carbon::create(2025, 3, 1),
                    'end_date' => Carbon::create(2025, 5, 31),
                ],
                [
                    'name' => 'Летний сезон',
                    'start_date' => Carbon::create(2025, 6, 1),
                    'end_date' => Carbon::create(2025, 8, 31),
                ],
                [
                    'name' => 'Осенний сезон',
                    'start_date' => Carbon::create(2025, 9, 1),
                    'end_date' => Carbon::create(2025, 11, 30),
                ],
            ];

            foreach ($periods as $periodData) {
                BookingPeriod::create(array_merge($periodData, ['hotel_id' => $hotel->id]));
            }
        }
    }
}