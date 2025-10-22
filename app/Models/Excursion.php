<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;
use App\Traits\HasPhotos;

class Excursion extends BaseModel
{
    use HasFactory, SoftDeletes, HasPhotos, SortOrder;

    protected $fillable = [
        'name',
        'description',
        'direction_id',
        'duration_hours',
        'price',
        'currency',
        'group_size_min',
        'group_size_max',
        'difficulty_level',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_hours' => 'decimal:2',
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
        return $this->hasMany(ExcursionProgram::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}