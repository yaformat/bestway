<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Direction;

class DirectionSeeder extends Seeder
{
    public function run(): void
    {
        $directions = [
            // Страны
            ['name' => 'Кыргызстан', 'parent_id' => null, 'latitude' => 41.2044, 'longitude' => 74.7661],
            ['name' => 'Узбекистан', 'parent_id' => null, 'latitude' => 41.3775, 'longitude' => 64.5853],
            ['name' => 'Казахстан', 'parent_id' => null, 'latitude' => 48.0196, 'longitude' => 66.9237],
            
            // Города/регионы Кыргызстана
            ['name' => 'Бишкек', 'parent_id' => 1, 'latitude' => 42.8746, 'longitude' => 74.5698],
            ['name' => 'Иссык-Куль', 'parent_id' => 1, 'latitude' => 42.6495, 'longitude' => 77.0445],
            ['name' => 'Ош', 'parent_id' => 1, 'latitude' => 40.5283, 'longitude' => 72.7831],
            ['name' => 'Джалал-Абад', 'parent_id' => 1, 'latitude' => 40.9333, 'longitude' => 72.9833],
            ['name' => 'Нарын', 'parent_id' => 1, 'latitude' => 41.4286, 'longitude' => 76.0167],
            
            // Города Узбекистана
            ['name' => 'Ташкент', 'parent_id' => 2, 'latitude' => 41.2995, 'longitude' => 69.2401],
            ['name' => 'Самарканд', 'parent_id' => 2, 'latitude' => 39.6542, 'longitude' => 66.9597],
            ['name' => 'Бухара', 'parent_id' => 2, 'latitude' => 39.7747, 'longitude' => 64.4186],
            ['name' => 'Хива', 'parent_id' => 2, 'latitude' => 41.3783, 'longitude' => 60.3633],
            
            // Города Казахстана
            ['name' => 'Алматы', 'parent_id' => 3, 'latitude' => 43.2220, 'longitude' => 76.8512],
            ['name' => 'Астана', 'parent_id' => 3, 'latitude' => 51.1605, 'longitude' => 71.4704],
            ['name' => 'Шымкент', 'parent_id' => 3, 'latitude' => 42.3000, 'longitude' => 69.6000],
        ];

        foreach ($directions as $direction) {
            Direction::create($direction);
        }
    }
}