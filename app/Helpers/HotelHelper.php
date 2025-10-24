<?php

namespace App\Helpers;

class HotelHelper
{
    /**
     * Получает типы отелей
     */
    public static function getHotelTypes()
    {
        return collect(config('hotels.types'))->map(function($type) {
            return (object) $type;
        });
    }

    /**
     * Получает типы отдыха
     */
    public static function getRestTypes()
    {
        return collect(config('hotels.rest_types'))->map(function($type) {
            return (object) $type;
        });
    }

    /**
     * Получает информационные блоки
     */
    public static function getInfoBlocks()
    {
        return collect(config('hotels.info_blocks'))->map(function($block) {
            return (object) $block;
        });
    }

    /**
     * Получает типы отелей для select
     */
    public static function getHotelTypesForSelect()
    {
        return self::getHotelTypes()->pluck('name', 'id')->toArray();
    }

    /**
     * Получает типы отдыха для select
     */
    public static function getRestTypesForSelect()
    {
        return self::getRestTypes()->pluck('name', 'id')->toArray();
    }

    /**
     * Получает информационные блоки для формы
     */
    public static function getInfoBlocksForForm()
    {
        return collect(config('hotels.info_blocks'))->mapWithKeys(function($block, $key) {
            return [$key => [
                'title' => $block['title'] ?? '',
                'icon' => $block['icon'] ?? '',
                'placeholder' => $block['placeholder'] ?? '',
            ]];
        })->toArray();
    }
    
    /**
     * Получает список валют
     */
    public static function getCurrencies()
    {
        return [
            'KGS' => 'Кыргызский сом',
            'USD' => 'Доллар США',
            'EUR' => 'Евро',
            'RUB' => 'Российский рубль'
        ];
    }

    /**
     * Получает типы ценообразования для услуг
     */
    public static function getPricingTypes()
    {
        return [
            'per_unit' => 'За единицу',
            'per_person' => 'За человека',
            'per_day' => 'За день',
            'per_stay' => 'За все проживание'
        ];
    }

    /**
     * Получает типы ценообразования для услуг для select
     */
    public static function getPricingTypesForSelect()
    {
        return self::getPricingTypes();
    }

    /**
     * Форматирует цену с учетом валюты
     */
    public static function formatPrice($price, $currency = 'KGS')
    {
        $symbols = [
            'KGS' => 'сом',
            'USD' => '$',
            'EUR' => '€',
            'RUB' => '₽'
        ];

        return number_format($price, 0, '.', ' ') . ' ' . ($symbols[$currency] ?? $currency);
    }

    /**
     * Получает иконку для типа отеля
     */
    public static function getHotelTypeIcon($type)
    {
        return config('hotels.types.' . $type . '.icon', 'mdi-hotel');
    }

    /**
     * Получает иконку для типа отдыха
     */
    public static function getRestTypeIcon($type)
    {
        return config('hotels.rest_types.' . $type . '.icon', 'mdi-star');
    }
}