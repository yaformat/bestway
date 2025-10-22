<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;
use App\Traits\HasPhotos;

class Transfer extends BaseModel
{
    use HasFactory, SoftDeletes, HasPhotos, SortOrder;

    protected $fillable = [
        'name',
        'description',
        'type', // airport, hotel, city, etc.
        'from_location',
        'to_location',
        'price',
        'currency',
        'capacity',
        'duration_minutes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'duration_minutes' => 'integer',
    ];
    protected $hidden = [
        'laravel_through_key',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}