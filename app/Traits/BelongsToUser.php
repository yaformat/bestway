<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToUser
{
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Boot the BelongsToUser trait.
     *
     */
    protected static function bootBelongsToUser()
    {
        static::addGlobalScope('withUser', function ($builder) {
            $builder->with('user');
        });

    }
    
}
