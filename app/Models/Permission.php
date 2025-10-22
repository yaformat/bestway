<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{  
    public function permissionables()
    {
        return $this->morphTo(); // это вернет все модели связанные с данной
    }
    
    // public function stocks()
    // {
    //     return $this->belongsToMany(Stock::class, 'user_stock_permissions')
    //                 ->withPivot('user_id');
    // }
}