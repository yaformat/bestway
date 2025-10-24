<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;
use App\Traits\HasPhotos;

class HotelRoom extends BaseModel
{
    use HasFactory, SoftDeletes, SortOrder, HasPhotos;

    protected $fillable = [
        'hotel_id',
        'hotel_building_id',
        'name',
        'description',
        'capacity',
        'beds_count',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'beds_count' => 'integer'
    ];

    protected $hidden = [
        'laravel_through_key',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function building()
    {
        return $this->belongsTo(HotelBuilding::class, 'hotel_building_id');
    }

    public function prices()
    {
        return $this->hasMany(HotelPrice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}