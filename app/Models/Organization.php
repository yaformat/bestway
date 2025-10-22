<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\BaseModel;

use App\Traits\HasPhotos;

class Organization extends BaseModel
{
    use HasPhotos;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',     
        'owner_id',
        'is_active',
    ];

    protected $casts = [
        'available_locales' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'fallback_locale' => 'ru',
    ];
    protected $hidden = [
        'photos',
    ];

    protected $appends = [
        'photo'
    ];
    protected $with = [
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'organization_id');
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function settings()
    {
        return $this->hasOne(OrganizationSetting::class);
    }
}
