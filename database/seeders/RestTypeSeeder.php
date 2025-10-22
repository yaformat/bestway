<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RestType;

class RestTypeSeeder extends Seeder
{
    public function run(): void
    {
        $restTypes = [
            ['name' => 'Лечебный', 'description' => 'Оздоровительный отдых с лечением'],
            ['name' => 'Семейный', 'description' => 'Отдых для всей семьи'],
            ['name' => 'Развлечения', 'description' => 'Активный отдых с развлечениями'],
            ['name' => 'Экстрим', 'description' => 'Экстремальный отдых и приключения'],
            ['name' => 'Экскурсионный', 'description' => 'Познавательные экскурсии'],
            ['name' => 'Пляжный', 'description' => 'Отдых на пляже'],
            ['name' => 'Горнолыжный', 'description' => 'Горнолыжный отдых'],
            ['name' => 'Романтический', 'description' => 'Отдух для пар'],
        ];

        foreach ($restTypes as $type) {
            RestType::create($type);
        }
    }
}