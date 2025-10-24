<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SortOrder;

class HotelBuilding extends BaseModel
{
    use HasFactory, SoftDeletes, SortOrder;

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rooms()
    {
        return $this->hasMany(HotelRoom::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}