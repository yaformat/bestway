<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="DomainSetting",
 *     title="Настройка домена",
 *     description="Настройка для конкретного домена",
 *     @OA\Property(property="id", type="integer", description="ID настройки"),
 *     @OA\Property(property="domain_id", type="integer", description="ID домена"),
 *     @OA\Property(property="key", type="string", description="Ключ настройки"),
 *     @OA\Property(property="value", type="string", description="Значение настройки"),
 *     @OA\Property(property="type", type="string", description="Тип значения (string, integer, boolean, json)"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание настройки"),
 *     @OA\Property(property="is_public", type="boolean", description="Доступна для фронтенда"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class DomainSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Типы значений настроек
     */
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ARRAY = 'array';

    /**
     * Предустановленные ключи настроек
     */
    const KEYS = [
        // SEO
        'seo_title' => 'SEO заголовок по умолчанию',
        'seo_description' => 'SEO описание по умолчанию',
        'seo_keywords' => 'SEO ключевые слова',
        
        // Коды для вставки
        'head_code' => 'Код для вставки в <head>',
        'body_open_code' => 'Код после открытия <body>',
        'body_close_code' => 'Код перед закрытием </body>',
        
        // Контактная информация
        'default_currency' => 'Валюта по умолчанию',
        'contact_phone' => 'Контактный телефон',
        'contact_email' => 'Контактный email',
        
        // Брендинг
        'logo' => 'URL логотипа',
        'favicon' => 'URL фавикона',
        'theme' => 'Тема оформления',
        
        // Локализация
        'language' => 'Язык по умолчанию',
        'timezone' => 'Часовой пояс',
        
        // Аналитика
        'google_analytics' => 'Google Analytics ID',
        'yandex_metrica' => 'Яндекс.Метрика ID',
        
        // Режим обслуживания
        'maintenance_mode' => 'Режим обслуживания',
        'maintenance_message' => 'Сообщение об обслуживании',
    ];

    /**
     * Получает домен настройки
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Получает приведенное значение согласно типу
     */
    public function getTypedValueAttribute()
    {
        if (is_null($this->value)) {
            return null;
        }

        return match($this->type) {
            self::TYPE_INTEGER => (int) $this->value,
            self::TYPE_FLOAT => (float) $this->value,
            self::TYPE_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON, self::TYPE_ARRAY => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Устанавливает значение с приведением типа
     */
    public function setTypedValueAttribute($value)
    {
        $this->attributes['value'] = match($this->type) {
            self::TYPE_BOOLEAN => $value ? '1' : '0',
            self::TYPE_JSON, self::TYPE_ARRAY => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Scope для публичных настроек
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope для поиска по ключу
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Получает описание ключа
     */
    public static function getKeyDescription(string $key): ?string
    {
        return self::KEYS[$key] ?? null;
    }
}