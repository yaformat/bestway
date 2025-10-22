<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\HotelRoom;

class HotelRoomSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::all();
        
        $roomTypes = [
            ['name' => 'Standard Single', 'capacity' => 1, 'beds_count' => 1],
            ['name' => 'Standard Double', 'capacity' => 2, 'beds_count' => 1],
            ['name' => 'Standard Twin', 'capacity' => 2, 'beds_count' => 2],
            ['name' => 'Deluxe Double', 'capacity' => 2, 'beds_count' => 1],
            ['name' => 'Family Room', 'capacity' => 4, 'beds_count' => 2],
            ['name' => 'Suite', 'capacity' => 2, 'beds_count' => 1],
            ['name' => 'Presidential Suite', 'capacity' => 2, 'beds_count' => 1],
        ];

        foreach ($hotels as $hotel) {
            foreach ($roomTypes as $roomType) {
                // Создаем от 1 до 3 комнат каждого типа для каждого отеля
                for ($i = 0; $i < rand(1, 3); $i++) {
                    HotelRoom::create([
                        'hotel_id' => $hotel->id,
                        'name' => $roomType['name'] . ($i > 0 ? ' ' . ($i + 1) : ''),
                        'description' => 'Комфортный номер с современным дизайном',
                        'capacity' => $roomType['capacity'],
                        'beds_count' => $roomType['beds_count'],
                    ]);
                }
            }
        }
    }
}