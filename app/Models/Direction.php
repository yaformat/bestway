<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\SortOrder;
use App\Traits\HasPhotos;

class Direction extends BaseModel
{
    use HasFactory, SoftDeletes, HasPhotos, SortOrder;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
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

    // Иерархия
    public function parent()
    {
        return $this->belongsTo(Direction::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Direction::class, 'parent_id')
            ->orderBy('sort_order');
    }

    // Связи с другими моделями
    public function resorts()
    {
        return $this->hasMany(Resort::class);
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    public function excursions()
    {
        return $this->hasMany(Excursion::class);
    }

    // Получение полного пути иерархии
    public function getFullPathAttribute()
    {
        $path = collect([$this]);
        $parent = $this->parent;
        
        while ($parent) {
            $path->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $path->pluck('name')->implode(' → ');
    }

    // Scope для получения корневых направлений
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope для получения активных
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}