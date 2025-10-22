<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domain;
use App\Models\DomainSetting;

class TestDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем тестовый домен для локальной разработки
        $domain = Domain::create([
            'name' => 'Bestway (Local)',
            'domain' => 'bestway.test',
            'description' => 'Тестовый домен для локальной разработки',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 999,
        ]);

        // Создаем базовые настройки для тестового домена
        $testSettings = [
            'seo_title' => 'Bestway - Тестовый домен',
            'default_currency' => 'KGS',
            'contact_phone' => '+996 (312) 000-000',
            'contact_email' => 'test@bestway.test',
            'theme' => 'bestway-kg',
            'language' => 'ru',
            'timezone' => 'Asia/Bishkek',
            'maintenance_mode' => false,
        ];

        foreach ($testSettings as $key => $value) {
            $domain->settings()->create([
                'key' => $key,
                'value' => $value,
                'type' => is_bool($value) ? 'boolean' : 'string',
                'description' => DomainSetting::getKeyDescription($key),
                'is_public' => true,
            ]);
        }
    }
}