<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // DirectionSeeder::class,
            // RestTypeSeeder::class,
            // ResortSeeder::class,
            HotelSeeder::class,
            // HotelRoomSeeder::class,
            // BookingPeriodSeeder::class,
            // HotelPriceSeeder::class,
            // TourSeeder::class,
            // TourProgramSeeder::class,
            // TransferSeeder::class,
            // ExcursionSeeder::class,
            // ExcursionProgramSeeder::class,
            // DomainSeeder::class,
            // TestDomainSeeder::class, // Раскомментировать для локальной разработки
        ]);
    }
}