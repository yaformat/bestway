<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

use App\Scopes\UserOrganizationScope;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization()
    {
        static::addGlobalScope(new UserOrganizationScope);

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->organization_id = Auth::user()->organization_id;
            } else {
                $userOrganizationId = -1;
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo('App\Models\Organization', 'organization_id');
    }

}
