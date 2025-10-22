<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tour;
use App\Models\Direction;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $tours = [
            [
                'name' => 'Озеро Кол-Когур',
                'description' => 'Увлекательное путешествие к высокогорному озеру Кол-Когур с потрясающими видами',
                'direction_id' => 4, // Бишкек
                'duration_days' => 2,
                'price' => 150.00,
                'currency' => 'USD',
                'difficulty_level' => 'medium',
                'group_size_min' => 4,
                'group_size_max' => 15,
            ],
            [
                'name' => 'Каньон Конорчек',
                'description' => 'Исследование красочных каньонов Конорчек с ночевкой в палатках',
                'direction_id' => 4, // Бишкек
                'duration_days' => 3,
                'price' => 200.00,
                'currency' => 'USD',
                'difficulty_level' => 'medium',
                'group_size_min' => 3,
                'group_size_max' => 12,
            ],
            [
                'name' => 'Ущелье Ала-Арча',
                'description' => 'Однодневный поход по живописному ущелью Ала-Арча',
                'direction_id' => 4, // Бишкек
                'duration_days' => 1,
                'price' => 50.00,
                'currency' => 'USD',
                'difficulty_level' => 'easy',
                'group_size_min' => 2,
                'group_size_max' => 20,
            ],
            [
                'name' => 'Иссык-Куль - Северное побережье',
                'description' => 'Тур по северному побережью озера Иссык-Куль с посещением курортов',
                'direction_id' => 2, // Иссык-Куль
                'duration_days' => 4,
                'price' => 350.00,
                'currency' => 'USD',
                'difficulty_level' => 'easy',
                'group_size_min' => 4,
                'group_size_max' => 25,
            ],
            [
                'name' => 'Самарканд и Бухара',
                'description' => 'Культурный тур по древним городам Узбекистана',
                'direction_id' => 2, // Узбекистан
                'duration_days' => 5,
                'price' => 500.00,
                'currency' => 'USD',
                'difficulty_level' => 'easy',
                'group_size_min' => 6,
                'group_size_max' => 30,
            ],
        ];

        foreach ($tours as $tourData) {
            Tour::create($tourData);
        }
    }
}