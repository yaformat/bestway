<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    // Время жизни кеша по умолчанию (1 час)
    private const DEFAULT_TTL = 3600;

    /**
     * Генерирует ключ кеша на основе префикса, метода и параметров
     *
     * @param string $prefix Префикс для группировки кеша
     * @param string $method Название метода
     * @param array $params Параметры для генерации уникального ключа
     * @return string
     */
    public static function generateKey(string $prefix, string $method, array $params = []): string
    {
        $key = $prefix . '_' . $method;
        
        if (!empty($params)) {
            $paramString = implode('_', array_map(function ($param) {
                if (is_object($param)) {
                    return get_class($param) . '_' . (method_exists($param, 'value') ? $param->value : spl_object_hash($param));
                }
                return (string) $param;
            }, $params));
            
            $key .= '_' . md5($paramString);
        }
        
        return $key;
    }

    /**
     * Кеширует результат выполнения функции
     *
     * @param string $key Ключ кеша
     * @param callable $callback Функция для выполнения
     * @param int|null $ttl Время жизни кеша в секундах
     * @return mixed
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? self::DEFAULT_TTL;
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Кеширует результат с автоматической генерацией ключа
     *
     * @param string $prefix Префикс
     * @param string $method Метод
     * @param callable $callback Функция для выполнения
     * @param array $params Параметры для ключа
     * @param int|null $ttl Время жизни кеша
     * @return mixed
     */
    public static function rememberByKey(string $prefix, string $method, callable $callback, array $params = [], ?int $ttl = null): mixed
    {
        $key = self::generateKey($prefix, $method, $params);
        return self::remember($key, $callback, $ttl);
    }

    /**
     * Очищает кеш по ключу
     *
     * @param string $key Ключ кеша
     * @return bool
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Очищает кеш по паттерну
     *
     * @param string $pattern Паттерн для поиска ключей
     * @return void
     */
    public static function forgetByPattern(string $pattern): void
    {
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } else {
            // Для других драйверов кеша - ограниченная поддержка
            // Можно расширить логику для конкретных драйверов
        }
    }

    /**
     * Очищает весь кеш по префиксу
     *
     * @param string $prefix Префикс для очистки
     * @return void
     */
    public static function clearByPrefix(string $prefix): void
    {
        self::forgetByPattern($prefix . '_*');
    }

    /**
     * Очищает кеш для конкретного метода с параметрами
     *
     * @param string $prefix Префикс
     * @param string $method Название метода
     * @param array $params Параметры метода
     * @return bool
     */
    public static function forgetMethod(string $prefix, string $method, array $params = []): bool
    {
        $key = self::generateKey($prefix, $method, $params);
        return self::forget($key);
    }

    /**
     * Получает все ключи кеша по паттерну
     *
     * @param string $pattern Паттерн для поиска
     * @return array
     */
    public static function getKeysByPattern(string $pattern): array
    {
        if (config('cache.default') === 'redis') {
            return Cache::getRedis()->keys($pattern);
        }
        
        return [];
    }

    /**
     * Проверяет существование ключа в кеше
     *
     * @param string $key Ключ кеша
     * @return bool
     */
    public static function has(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * Устанавливает значение в кеш
     *
     * @param string $key Ключ кеша
     * @param mixed $value Значение
     * @param int|null $ttl Время жизни в секундах
     * @return bool
     */
    public static function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? self::DEFAULT_TTL;
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Получает значение из кеша
     *
     * @param string $key Ключ кеша
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }
}
