<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Excursion;
use App\Models\Direction;

class ExcursionSeeder extends Seeder
{
    public function run(): void
    {
        $excursions = [
            [
                'name' => 'Ташкент + Самарканд + Бухара',
                'description' => 'Комплексная экскурсия по древним городам Узбекистана с посещением всех главных достопримечательностей',
                'direction_id' => 2, // Узбекистан
                'duration_hours' => 72.00, // 3 дня
                'price' => 450.00,
                'currency' => 'USD',
                'group_size_min' => 6,
                'group_size_max' => 25,
                'difficulty_level' => 'easy',
            ],
            [
                'name' => 'Горнолыжные базы Бишкека и Иссык-Куля',
                'description' => 'Экскурсия по горнолыжным курортам Кыргызстана с возможностью катания',
                'direction_id' => 1, // Кыргызстан
                'duration_hours' => 48.00, // 2 дня
                'price' => 200.00,
                'currency' => 'USD',
                'group_size_min' => 4,
                'group_size_max' => 15,
                'difficulty_level' => 'medium',
            ],
            [
                'name' => 'Бишкек - городская экскурсия',
                'description' => 'Обзорная экскурсия по столице Кыргызстана: центральная площадь, музеи, парки',
                'direction_id' => 4, // Бишкек
                'duration_hours' => 4.00,
                'price' => 40.00,
                'currency' => 'USD',
                'group_size_min' => 2,
                'group_size_max' => 20,
                'difficulty_level' => 'easy',
            ],
            [
                'name' => 'Озеро Иссык-Куль - полный день',
                'description' => 'Экскурсия по северному побережью озера Иссык-Куль с посещением достопримечательностей',
                'direction_id' => 2, // Иссык-Куль
                'duration_hours' => 10.00,
                'price' => 80.00,
                'currency' => 'USD',
                'group_size_min' => 3,
                'group_size_max' => 30,
                'difficulty_level' => 'easy',
            ],
            [
                'name' => 'Самарканд - жемчужина Востока',
                'description' => 'Полноценная экскурсия по Самарканду: Регистан, Шахи-Зинда, Биби-Ханум',
                'direction_id' => 7, // Самарканд
                'duration_hours' => 8.00,
                'price' => 60.00,
                'currency' => 'USD',
                'group_size_min' => 4,
                'group_size_max' => 20,
                'difficulty_level' => 'easy',
            ],
            [
                'name' => 'Бухара - древний город',
                'description' => 'Экскурсия по историческому центру Бухары: минарет Калян, торговые купола',
                'direction_id' => 8, // Бухара
                'duration_hours' => 8.00,
                'price' => 55.00,
                'currency' => 'USD',
                'group_size_min' => 4,
                'group_size_max' => 20,
                'difficulty_level' => 'easy',
            ],
            [
                'name' => 'Алматы - город яблок',
                'description' => 'Обзорная экскурсия по Алматы: парк 28 гвардейцев, зеленый базар, холм Кок-Тобе',
                'direction_id' => 12, // Алматы
                'duration_hours' => 6.00,
                'price' => 70.00,
                'currency' => 'USD',
                'group_size_min' => 2,
                'group_size_max' => 15,
                'difficulty_level' => 'easy',
            ],
        ];

        foreach ($excursions as $excursionData) {
            Excursion::create($excursionData);
        }
    }
}