<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key_name',
    ];

    protected $with = [
        'keys'
    ];

    public function keys()
    {
        return $this->hasMany(TranslationKey::class, 'group_key_name', 'key_name')->orderBy('key_name', 'ASC');
    }  

}
