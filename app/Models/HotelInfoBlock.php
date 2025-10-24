<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelInfoBlock extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'block_key',
        'content',
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

    public function getBlockInfoAttribute()
    {
        return config('hotels.info_blocks.' . $this->block_key);
    }
}