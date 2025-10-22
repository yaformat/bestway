<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;

class RestType extends BaseModel
{
    use HasFactory, SoftDeletes, SortOrder;

    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'laravel_through_key',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}