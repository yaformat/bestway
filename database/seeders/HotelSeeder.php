<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\HotelInfoBlock;
use App\Models\HotelBuilding;
use App\Models\HotelService;
use Illuminate\Support\Facades\DB;

class HotelSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');


        DB::table('hotels')->truncate();
        DB::table('hotel_info_blocks')->truncate();
        DB::table('hotel_buildings')->truncate();
        DB::table('hotel_services')->truncate();
        DB::table('hotel_rooms')->truncate();
        DB::table('hotel_prices')->truncate();
        DB::table('hotel_service_prices')->truncate();
        DB::table('booking_periods')->truncate();

        $hotels = [
            [
                'name' => 'Vzmorie Resort hotel',
                'description' => '<p>Современный отель на берегу Иссык-Куля</p>',
                'direction_id' => 1,
                'resort_id' => 1,
                'currency' => 'KGS',
                'hotel_type' => 'hotel',
                'rest_types' => json_encode(['family', 'recreational']),
                'latitude' => 42.6489,
                'longitude' => 77.0186,
                'rating' => 4.5,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Кыргызское взморье Санаторий',
                'description' => '<p>Крупнейший лечебно-оздоровительный комплекс на северном берегу Иссык-Куля</p>',
                'direction_id' => 1,
                'resort_id' => 1,
                'currency' => 'KGS',
                'hotel_type' => 'sanatorium',
                'rest_types' => json_encode(['medical', 'recreational', 'family']),
                'latitude' => 42.6495,
                'longitude' => 77.0196,
                'rating' => 4.2,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Солнечный Детский Лагерь',
                'description' => '<p>Современный оздоровительный лагерь для детей и подростков</p>',
                'direction_id' => 1,
                'resort_id' => 2,
                'currency' => 'KGS',
                'hotel_type' => 'children_camp',
                'rest_types' => json_encode(['family', 'sport']),
                'latitude' => 42.6348,
                'longitude' => 77.0153,
                'rating' => 4.0,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Гостевой дом Бермет',
                'description' => '<p>Уютный гостевой дом с домашней атмосферой</p>',
                'direction_id' => 1,
                'resort_id' => 3,
                'currency' => 'KGS',
                'hotel_type' => 'guest_house',
                'rest_types' => json_encode(['family', 'excursion']),
                'latitude' => 42.6456,
                'longitude' => 77.0234,
                'rating' => 3.8,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Чон-Кызыл Пансионат',
                'description' => '<p>Пансионат с полным пансионом и развлекательной программой</p>',
                'direction_id' => 2,
                'resort_id' => 4,
                'currency' => 'KGS',
                'hotel_type' => 'pension',
                'rest_types' => json_encode(['family', 'recreational', 'entertainment']),
                'latitude' => 41.4325,
                'longitude' => 75.7844,
                'rating' => 4.1,
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);

            // Создаем информационные блоки
            $this->createInfoBlocks($hotel);

            // Создаем строения
            $this->createBuildings($hotel);

            // Создаем дополнительные услуги
            $this->createServices($hotel);
        }
    }

    private function createInfoBlocks(Hotel $hotel)
    {
        $blocksConfig = [
            'location' => [
                'content' => 'Vzmorie Resort hotel (Взморье Резорт отель) на Иссык-куле расположен на территории санатория Кыргызское взморье: Северное побережье, с.Бостери, Иссык-Кульская область, Кыргызская Республика.',
                'active' => true
            ],
            'accommodation' => [
                'content' => 'Комфортные кровати<br>Телевизор<br>Большой шкаф и тумба для хранения одежды<br>Удобный сан.узел<br>Всегда белоснежное постельное белье и полотенца',
                'active' => true
            ],
            'food' => [
                'content' => '3-разовое питание в ресторане отеля<br>Шведский стол на завтрак<br>Возможность заказа диетического меню',
                'active' => true
            ],
            'infrastructure' => [
                'content' => 'Собственный пляж<br>Бассейн с подогревом<br>Спортплощадка<br>Детская площадка<br>Парковка',
                'active' => true
            ],
            'beach' => [
                'content' => 'Песчаный пляж<br>Шезлонги и зонты<br>Пляжный бар<br>Спасательная служба',
                'active' => true
            ],
            'entertainment' => [
                'content' => 'Вечерние шоу-программы<br>Дискотека<br>Бильярд<br>Настольные игры',
                'active' => false
            ]
        ];

        foreach ($blocksConfig as $key => $block) {
            HotelInfoBlock::create([
                'hotel_id' => $hotel->id,
                'block_key' => $key,
                'content' => $block['content'],
                'is_active' => $block['active'],
                'sort_order' => 0
            ]);
        }
    }

    private function createBuildings(Hotel $hotel)
    {
        // Создаем строения только для отелей с типом hotel и sanatorium
        if (!in_array($hotel->hotel_type, ['hotel', 'sanatorium'])) {
            return;
        }

        $buildings = [
            ['name' => 'Коттедж Салкын', 'description' => 'Уютный коттедж для семейного отдыха'],
            ['name' => 'Коттедж Бермет', 'description' => 'Просторный коттедж с видом на озеро'],
            ['name' => 'Основной корпус', 'description' => 'Центральный корпус с номерами класса люкс']
        ];

        foreach ($buildings as $index => $buildingData) {
            $building = HotelBuilding::create([
                'hotel_id' => $hotel->id,
                'name' => $buildingData['name'],
                'description' => $buildingData['description'],
                'is_active' => true,
                'sort_order' => $index
            ]);

            // Создаем комнаты в строении
            $this->createRoomsInBuilding($building, $hotel);
        }
    }

    private function createRoomsInBuilding(HotelBuilding $building, Hotel $hotel)
    {
        $rooms = [
            [
                'name' => '2-комнатный 4-местный',
                'description' => 'Просторный двухкомнатный номер для семьи',
                'capacity' => 4,
                'beds_count' => 3,
                'hotel_id' => $hotel->id,
                'hotel_building_id' => $building->id
            ],
            [
                'name' => '1-комнатный 2-местный тип1',
                'description' => 'Комфортный одноместный номер',
                'capacity' => 2,
                'beds_count' => 2,
                'hotel_id' => $hotel->id,
                'hotel_building_id' => $building->id
            ],
            [
                'name' => 'Люкс',
                'description' => 'Номер класса люкс с отдельной гостиной',
                'capacity' => 3,
                'beds_count' => 2,
                'hotel_id' => $hotel->id,
                'hotel_building_id' => $building->id
            ]
        ];

        foreach ($rooms as $index => $roomData) {
            $room = \App\Models\HotelRoom::create(array_merge($roomData, [
                'is_active' => true,
                'sort_order' => $index
            ]));

            // Создаем цены для номеров
            $this->createRoomPrices($room, $hotel);
        }
    }

    private function createRoomPrices(\App\Models\HotelRoom $room, Hotel $hotel)
    {
        $periods = \App\Models\BookingPeriod::where('hotel_id', $hotel->id)->get();
        
        if ($periods->isEmpty()) {
            $periods = $this->createBookingPeriods($hotel);
        }

        foreach ($periods as $index => $period) {
            \App\Models\HotelPrice::create([
                'hotel_room_id' => $room->id,
                'booking_period_id' => $period->id,
                'price' => $room->capacity * 1500, // Цена зависит от вместимости
                'currency' => $hotel->currency,
                'min_nights' => 1,
                'max_nights' => 30
            ]);
        }
    }

    private function createServices(Hotel $hotel)
    {
        $services = [
            [
                'name' => 'Трансфер из аэропорта',
                'description' => 'Встреча в аэропорту Манас и трансфер до отеля',
                'pricing_type' => 'per_person'
            ],
            [
                'name' => 'Экскурсионное обслуживание',
                'description' => 'Организация экскурсий по Иссык-Кулю с гидом',
                'pricing_type' => 'per_person'
            ],
            [
                'name' => 'SPA-услуги',
                'description' => 'Массаж, сауна, хаммам',
                'pricing_type' => 'per_unit'
            ],
            [
                'name' => 'Прокат оборудования',
                'description' => 'Велосипеды, водные лыжи, катамараны',
                'pricing_type' => 'per_day'
            ],
            [
                'name' => 'Детская анимация',
                'description' => 'Развлекательная программа для детей',
                'pricing_type' => 'per_day'
            ],
            [
                'name' => 'Прачечная',
                'description' => 'Услуги стирки и глажки',
                'pricing_type' => 'per_unit'
            ],
            [
                'name' => 'Бизнес-центр',
                'description' => 'Конференц-зал, интернет, ксерокс',
                'pricing_type' => 'per_day'
            ],
            [
                'name' => 'Аренда авто',
                'description' => 'Аренда автомобиля с водителем',
                'pricing_type' => 'per_day'
            ]
        ];

        foreach ($services as $index => $serviceData) {
            $service = HotelService::create([
                'hotel_id' => $hotel->id,
                'name' => $serviceData['name'],
                'description' => $serviceData['description'],
                'is_active' => true,
                'sort_order' => $index
            ]);

            // Создаем цены для услуг
            $this->createServicePrices($service, $hotel, $serviceData['pricing_type']);
        }
    }

    private function createServicePrices(HotelService $service, Hotel $hotel, $pricingType)
    {
        $periods = \App\Models\BookingPeriod::where('hotel_id', $hotel->id)->get();
        
        if ($periods->isEmpty()) {
            $periods = $this->createBookingPeriods($hotel);
        }

        foreach ($periods as $period) {
            $basePrice = match($service->name) {
                'Трансфер из аэропорта' => 2000,
                'Экскурсионное обслуживание' => 1500,
                'SPA-услуги' => 3000,
                'Прокат оборудования' => 800,
                'Детская анимация' => 500,
                'Прачечная' => 1000,
                'Бизнес-центр' => 2500,
                'Аренда авто' => 5000,
                default => 1000
            };

            \App\Models\HotelServicePrice::create([
                'hotel_service_id' => $service->id,
                'booking_period_id' => $period->id,
                'price' => $basePrice,
                'currency' => $hotel->currency,
                'pricing_type' => $pricingType,
                'is_active' => true
            ]);
        }
    }

    private function createBookingPeriods(Hotel $hotel)
    {
        $createdPeriods = [];

        $periodCount = rand(3, 5);
        // Начинаем с текущей или ближайшей прошлой даты
        $startDate = now()->subMonths(rand(0, 2))->startOfMonth();

        for ($i = 0; $i < $periodCount; $i++) {

            $durationMonths = rand(1, 2);
            $endDate = (clone $startDate)->addMonths($durationMonths)->subDay();
            $periodData = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => true,
                    'sort_order' => $i
            ];

            $createdPeriods[] = \App\Models\BookingPeriod::create(array_merge($periodData, [
                'hotel_id' => $hotel->id
            ]));

            // Следующий период начинается на следующий день после окончания предыдущего
            $startDate = (clone $endDate)->addDay();
        }
        return $createdPeriods;
    }
}