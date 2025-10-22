<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TranslatableModel;

class TranslationKey extends TranslatableModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [  
        'name', 
        'group_id',     
        'key_name',  
    ];    

    public $translatedAttributes = ['key_value'];
    public $translationModel = 'App\Models\Translation\TranslationKeyTranslation';
    
}
