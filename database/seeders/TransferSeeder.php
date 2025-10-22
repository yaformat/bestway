<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transfer;

class TransferSeeder extends Seeder
{
    public function run(): void
    {
        $transfers = [
            [
                'name' => 'Аэропорт Манас - Бишкек',
                'description' => 'Трансфер из международного аэропорта Манас в центр Бишкека',
                'type' => 'airport',
                'from_location' => 'Аэропорт Манас',
                'to_location' => 'Бишкек',
                'price' => 25.00,
                'currency' => 'USD',
                'capacity' => 4,
                'duration_minutes' => 45,
            ],
            [
                'name' => 'Бишкек - Иссык-Куль',
                'description' => 'Трансфер из Бишкека на Иссык-Куль',
                'type' => 'city',
                'from_location' => 'Бишкек',
                'to_location' => 'Чолпон-Ата',
                'price' => 80.00,
                'currency' => 'USD',
                'capacity' => 4,
                'duration_minutes' => 240,
            ],
            [
                'name' => 'Аэропорт Ташкента - центр',
                'description' => 'Трансфер из аэропорта Ташкента в центр города',
                'type' => 'airport',
                'from_location' => 'Аэропорт Ташкента',
                'to_location' => 'Ташкент, центр',
                'price' => 30.00,
                'currency' => 'USD',
                'capacity' => 4,
                'duration_minutes' => 30,
            ],
            [
                'name' => 'Алматы - Медеу',
                'description' => 'Трансфер из Алматы в горнолыжный курорт Медеу',
                'type' => 'resort',
                'from_location' => 'Алматы',
                'to_location' => 'Медеу',
                'price' => 40.00,
                'currency' => 'USD',
                'capacity' => 4,
                'duration_minutes' => 60,
            ],
            [
                'name' => 'Бишкек - Ала-Арча',
                'description' => 'Трансфер из Бишкека в ущелье Ала-Арча',
                'type' => 'nature',
                'from_location' => 'Бишкек',
                'to_location' => 'Ущелье Ала-Арча',
                'price' => 35.00,
                'currency' => 'USD',
                'capacity' => 4,
                'duration_minutes' => 50,
            ],
            [
                'name' => 'Каракол - Джеты-Огуз',
                'description' => 'Трансфер из Каракола в долину Джеты-Огуз',
                'type' => 'nature',
                'from_location' => 'Каракол',
                'to_location' => 'Джеты-Огуз',
                'price' => 50.00,
                'currency' => 'USD',
                'capacity' => 4,
                'duration_minutes' => 90,
            ],
        ];

        foreach ($transfers as $transferData) {
            Transfer::create($transferData);
        }
    }
}