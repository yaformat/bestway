<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class ExcursionProgram extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'excursion_id',
        'title',
        'description',
        'duration_minutes',
        'sort_order',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
    ];

    protected $hidden = [
        'laravel_through_key',
    ];

    public function excursion()
    {
        return $this->belongsTo(Excursion::class);
    }
}