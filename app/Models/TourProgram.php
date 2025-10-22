<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class TourProgram extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tour_id',
        'day',
        'title',
        'description',
        'time',
        'sort_order',
    ];

    protected $casts = [
        'day' => 'integer',
        'time' => 'datetime:H:i',
    ];

    protected $hidden = [
        'laravel_through_key',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}