<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ValidatorHelper
{

    public static function checkNumericArray($array = [], $key = null) {

        $array = (!$key) ? $array : (isset($array[$key]) ? $array[$key] : []);
        if (empty($array)) return false;

        
        $filteredValues = array_filter($array, 'is_numeric');
        if(!empty($filteredValues)){
            return $filteredValues;
        }
    
        return false;
        return self::checkArray($array, 'numeric');
    }


    /**
     * Проверяет и фильтрует массив по типу значений с уникализацией и приведением типов.
     *
     * @param array       $array - Входной массив
     * @param string|null $type - 'numeric', 'string' или null (оба типа)
     * @return array|false - Уникализированный и приведённый массив или false
     */
    public static function checkArray(array $array, ?string $type = null)
    {
        if (empty($array)) {
            return false;
        }

        $filtered = match ($type) {
            'numeric' => array_map(
                fn($val) => is_numeric($val) ? (strpos($val, '.') !== false ? (float) $val : (int) $val) : null,
                array_filter($array, 'is_numeric')
            ),
            'string' => array_map(
                fn($val) => (string) $val,
                array_filter($array, 'is_string')
            ),
            default => array_filter($array, fn($item) => is_string($item) || is_numeric($item)),
        };

        // Удаляем null'ы и дубли
        $filtered = array_filter($filtered, fn($val) => $val !== null);
        $unique = array_values(array_unique($filtered));

        return empty($unique) ? false : $unique;
    }

}

