<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;
use App\Traits\HasPhotos;

class Tour extends BaseModel
{
    use HasFactory, SoftDeletes, HasPhotos, SortOrder;

    protected $fillable = [
        'name',
        'description',
        'direction_id',
        'duration_days',
        'price',
        'currency',
        'difficulty_level',
        'group_size_min',
        'group_size_max',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_days' => 'integer',
        'price' => 'decimal:2',
        'group_size_min' => 'integer',
        'group_size_max' => 'integer',
    ];

    protected $hidden = [
        'laravel_through_key',
    ];

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function program()
    {
        return $this->hasMany(TourProgram::class)->orderBy('day')->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}