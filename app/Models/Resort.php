<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;
use App\Traits\HasPhotos;

class Resort extends BaseModel
{
    use HasFactory, SoftDeletes, HasPhotos, SortOrder;

    protected $fillable = [
        'name',
        'description',
        'direction_id',
        'sort_order',
        'is_active',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $hidden = [
        'laravel_through_key',
    ];

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}