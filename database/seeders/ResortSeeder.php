<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resort;

class ResortSeeder extends Seeder
{
    public function run(): void
    {
        $resorts = [
            // Курорты Иссык-Куля
            ['name' => 'Чолпон-Ата', 'direction_id' => 2, 'latitude' => 42.6486, 'longitude' => 77.0478],
            ['name' => 'Каракол', 'direction_id' => 2, 'latitude' => 42.4931, 'longitude' => 78.3892],
            ['name' => 'Тамга', 'direction_id' => 2, 'latitude' => 42.4167, 'longitude' => 76.6833],
            ['name' => 'Бостери', 'direction_id' => 2, 'latitude' => 42.5500, 'longitude' => 77.0833],
            
            // Курорты Бишкека
            ['name' => 'Ала-Арча', 'direction_id' => 4, 'latitude' => 42.6833, 'longitude' => 74.5000],
            ['name' => 'Аламедин', 'direction_id' => 4, 'latitude' => 42.6833, 'longitude' => 74.4167],
        ];

        foreach ($resorts as $resort) {
            Resort::create($resort);
        }
    }
}