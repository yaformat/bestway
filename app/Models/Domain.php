<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Domain",
 *     title="Домен",
 *     description="Модель домена сайта",
 *     @OA\Property(property="id", type="integer", description="ID домена"),
 *     @OA\Property(property="name", type="string", description="Название домена"),
 *     @OA\Property(property="domain", type="string", description="Доменное имя"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Описание домена"),
 *     @OA\Property(property="is_default", type="boolean", description="Домен по умолчанию"),
 *     @OA\Property(property="is_active", type="boolean", description="Активность домена"),
 *     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 * )
 */
class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'domain',
        'description',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Получает настройки домена
     */
    public function settings(): HasMany
    {
        return $this->hasMany(DomainSetting::class);
    }

    /**
     * Получает все настройки как ключ-значение
     */
    public function getAllSettingsAttribute()
    {
        return $this->settings->pluck('value', 'key')->toArray();
    }

    /**
     * Получает значение настройки по ключу
     */
    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Устанавливает значение настройки
     */
    public function setSetting(string $key, $value, string $type = DomainSetting::TYPE_STRING, string $description = null): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Удаляет настройку
     */
    public function removeSetting(string $key): void
    {
        $this->settings()->where('key', $key)->delete();
    }

    /**
     * Получает домен по умолчанию
     */
    public static function getDefault()
    {
        return static::where('is_default', true)
                   ->where('is_active', true)
                   ->first();
    }

    /**
     * Получает домен по имени
     */
    public static function getByDomain(string $domain)
    {
        return static::where('domain', $domain)
                   ->where('is_active', true)
                   ->first();
    }

    /**
     * Получает все активные домены
     */
    public static function getActive()
    {
        return static::where('is_active', true)
                   ->orderBy('sort_order')
                   ->get();
    }

    /**
     * Проверяет, является ли домен текущим
     */
    public function isCurrent(): bool
    {
        return request()->getHost() === $this->domain;
    }

    /**
     * Scope для активных доменов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для доменов по умолчанию
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Boot метод для событий
     */
    protected static function boot()
    {
        parent::boot();

        // При создании нового домена по умолчанию, убираем флаг с других
        static::saving(function ($domain) {
            if ($domain->is_default && $domain->isDirty('is_default')) {
                static::where('id', '!=', $domain->id)
                      ->update(['is_default' => false]);
            }
        });
    }
}