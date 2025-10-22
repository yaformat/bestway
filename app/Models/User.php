<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Traits\BelongsToOrganization;

use App\Traits\HasPhotos;

use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPhotos, BelongsToOrganization;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', 
        'last_name', 
        'middle_name',
        'email',
        'password',
        'locale',
        'is_active',
        'activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'organization_id',
        'password',
        'remember_token',
        'photos',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    protected $with = [
        'organization',
    ];

    protected $appends = [
        'full_name',
        'initial_name',
        'locale',
        'fallback_locale',
        'all_permissions',
        'is_online',
    ];

    // Флаг для предотвращения вызова savePhotosFromRequest
    public $preventSavePhotos = false;

    //описываем возможные сортировки
    public static function sortings()
    {
        return \App\Presets\Sortings\DefaultSortings::sortings();
    }

    public static function filters()
    {
        return \App\Presets\Filters\NoFilters::filters();
    }

    public function getFallbackLocaleAttribute() {
        return $this->organization->fallback_locale;
    }

    public function getLocaleAttribute() {
        $locale = $this->attributes['locale'] ?? null;
        $organization = $this->organization;
    
        if ($organization === null) {
            return $locale;
        }
    
        if (empty($organization->available_locales)) {
            return $organization->fallback_locale;
        }
    
        if (!in_array($locale, $organization->available_locales)) {
            $locale = $organization->available_locales[0];
        }
    
        return $locale;
    }
    public function isOnline()
    {
        // Проверяем, была ли активность пользователя в последние 5 минут
        if (!$this->activity_at) {
            return false;
        }
        
        return Carbon::parse($this->activity_at)->diffInMinutes(now()) <= 5;
    }

    // Аксессор для удобного доступа к статусу
    public function getIsOnlineAttribute()
    {
        return $this->isOnline();
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }
    
    public function permissions()
    {
        return $this->morphMany(Permission::class, 'permissionable');
    }

    public function getAllPermissionsAttribute()
    {
        // Получаем permissions пользователя
        $userPermissions = $this->permissions;
    
        if ($userPermissions->isEmpty()) {
            return null;
        }
    
        // Если у пользователя есть свои permissions, отдаем их
        return $userPermissions->unique('name');
    }

    public function getFullNameAttribute()
    {
        $full_name = [];
        $full_name[] = $this->first_name; 
        if ($this->middle_name) $full_name[] = $this->middle_name; 
        if ($this->last_name) $full_name[] = $this->last_name; 

        return trim(implode(' ', $full_name));
    }

    public function getInitialNameAttribute()
    {
        $initial_name = [];
        $initial_name[] = $this->first_name; 
        if ($this->last_name) $initial_name[] = mb_substr($this->last_name, 0, 1) . ".";

        return trim(implode(' ', $initial_name));
    }

}
