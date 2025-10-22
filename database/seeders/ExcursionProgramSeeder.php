<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Excursion;
use App\Models\ExcursionProgram;

class ExcursionProgramSeeder extends Seeder
{
    public function run(): void
    {
        $excursions = Excursion::all();
        
        foreach ($excursions as $excursion) {
            $programs = match($excursion->name) {
                'Ташкент + Самарканд + Бухара' => [
                    [
                        'title' => 'Ташкент - площадь Независимости',
                        'description' => 'Посещение главной площади Ташкента, монумента Независимости',
                        'duration_minutes' => 60,
                    ],
                    [
                        'title' => 'Старый город Ташкента',
                        'description' => 'Прогулка по старому городу, visitation мадраса Кукельдаш',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Самарканд - площадь Регистан',
                        'description' => 'Посещение трех мадраса на площади Регистан',
                        'duration_minutes' => 120,
                    ],
                    [
                        'title' => 'Мавзолей Гур-Эмир',
                        'description' => 'Посещение усыпальницы Тамерлана',
                        'duration_minutes' => 60,
                    ],
                    [
                        'title' => 'Бухара - минарет Калян',
                        'description' => 'Осмотр минарета и мечети Калян',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Торговые купола Бухары',
                        'description' => 'Посещение древних торговых куполов',
                        'duration_minutes' => 120,
                    ],
                ],
                'Горнолыжные базы Бишкека и Иссык-Куля' => [
                    [
                        'title' => 'База Орловка',
                        'description' => 'Посещение ближайшей к Бишкеку горнолыжной базы',
                        'duration_minutes' => 240,
                    ],
                    [
                        'title' => 'База Тогуз-Булак',
                        'description' => 'Катание на базе Тогуз-Булак',
                        'duration_minutes' => 180,
                    ],
                    [
                        'title' => 'Курорт Кара-Балта',
                        'description' => 'Посещение курорта Кара-Балта',
                        'duration_minutes' => 200,
                    ],
                ],
                'Бишкек - городская экскурсия' => [
                    [
                        'title' => 'Площадь Ала-Тоо',
                        'description' => 'Центральная площадь с памятником Манасу',
                        'duration_minutes' => 45,
                    ],
                    [
                        'title' => 'Исторический музей',
                        'description' => 'Экскурсия по Национальному музею Кыргызстана',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Парк Ата-Бейит',
                        'description' => 'Посещение мемориального комплекса',
                        'duration_minutes' => 60,
                    ],
                    [
                        'title' => 'Центральная мечеть',
                        'description' => 'Осмотр центральной мечети Бишкека',
                        'duration_minutes' => 45,
                    ],
                ],
                'Озеро Иссык-Куль - полный день' => [
                    [
                        'title' => 'Петроглифы Чолпон-Аты',
                        'description' => 'Посещение древних наскальных рисунков',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Музей золота и кочевников',
                        'description' => 'Экскурсия в музей с знаменитыми золотыми находками',
                        'duration_minutes' => 60,
                    ],
                    [
                        'title' => 'Рыбный рынок',
                        'description' => 'Посещение рыбного рынка, дегустация',
                        'duration_minutes' => 45,
                    ],
                    [
                        'title' => 'Пляжный отдых',
                        'description' => 'Свободное время на пляже',
                        'duration_minutes' => 120,
                    ],
                ],
                'Самарканд - жемчужина Востока' => [
                    [
                        'title' => 'Площадь Регистан',
                        'description' => 'Три мадраса: Улугбека, Шер-Дор, Тилля-Кори',
                        'duration_minutes' => 120,
                    ],
                    [
                        'title' => 'Некрополь Шахи-Зинда',
                        'description' => 'Посещение комплекса мавзолеев',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Мечеть Биби-Ханум',
                        'description' => 'Осмотр мечети и рынка Сиаб',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Обсерватория Улугбека',
                        'description' => 'Посещение древней обсерватории',
                        'duration_minutes' => 60,
                    ],
                ],
                'Бухара - древний город' => [
                    [
                        'title' => 'Минарет Калян',
                        'description' => 'Осмотр минарета и соборной мечети',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Арка цитадели',
                        'description' => 'Посещение древней цитадели',
                        'duration_minutes' => 60,
                    ],
                    [
                        'title' => 'Торговые купола',
                        'description' => 'Токи Саррафон, Тогул, Таки-Загарон',
                        'duration_minutes' => 120,
                    ],
                    [
                        'title' => 'Мавзолей Саманидов',
                        'description' => 'Посещение мавзолея династии Саманидов',
                        'duration_minutes' => 45,
                    ],
                ],
                'Алматы - город яблок' => [
                    [
                        'title' => 'Парк 28 гвардейцев',
                        'description' => 'Посещение мемориального комплекса',
                        'duration_minutes' => 60,
                    ],
                    [
                        'title' => 'Зеленый базар',
                        'description' => 'Экскурсия по центральному рынку',
                        'duration_minutes' => 90,
                    ],
                    [
                        'title' => 'Холм Кок-Тобе',
                        'description' => 'Подъем на фуникулере, панорама города',
                        'duration_minutes' => 120,
                    ],
                    [
                        'title' => 'Медеу и Шымбулак',
                        'description' => 'Посещение высокогорного катка и горнолыжного курорта',
                        'duration_minutes' => 180,
                    ],
                ],
                default => []
            };

            foreach ($programs as $programData) {
                ExcursionProgram::create(array_merge($programData, ['excursion_id' => $excursion->id]));
            }
        }
    }
}