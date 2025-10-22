<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\RestType;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = [
            [
                'name' => 'Алтын-Булак',
                'description' => 'Современный отель с прекрасным видом на озеро Иссык-Куль',
                'direction_id' => 2,
                'resort_id' => 1,
                'latitude' => 42.6486,
                'longitude' => 77.0478,
                'rating' => 4.5,
                'stars' => 4,
            ],
            [
                'name' => 'Rixon Hotel',
                'description' => 'Роскошный отель в центре Бишкека с отличным сервисом',
                'direction_id' => 4,
                'latitude' => 42.8746,
                'longitude' => 74.5698,
                'rating' => 4.8,
                'stars' => 5,
            ],
            [
                'name' => 'Футуро',
                'description' => 'Современный отель с футуристическим дизайном',
                'direction_id' => 4,
                'latitude' => 42.8700,
                'longitude' => 74.5700,
                'rating' => 4.3,
                'stars' => 4,
            ],
            [
                'name' => 'Novotel',
                'description' => 'Международный отель с высоким стандартом обслуживания',
                'direction_id' => 4,
                'latitude' => 42.8750,
                'longitude' => 74.5680,
                'rating' => 4.6,
                'stars' => 4,
            ],
            [
                'name' => 'Issyk-Kul Resort',
                'description' => 'Пляжный отель на берегу Иссык-Куля',
                'direction_id' => 2,
                'resort_id' => 2,
                'latitude' => 42.4931,
                'longitude' => 78.3892,
                'rating' => 4.2,
                'stars' => 3,
            ],
            [
                'name' => 'Almaty Towers',
                'description' => 'Бизнес-отель в центре Алматы',
                'direction_id' => 12,
                'latitude' => 43.2220,
                'longitude' => 76.8512,
                'rating' => 4.7,
                'stars' => 5,
            ],
        ];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);
            
            // Привязываем виды отдыха
            $restTypes = RestType::inRandomOrder()->limit(rand(2, 4))->pluck('id');
            $hotel->restTypes()->attach($restTypes);
        }
    }
}