<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserOrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check() && !empty(Auth::user()->organization_id)) {
            $builder->where('organization_id',  Auth::user()->organization_id);
        }
    }
}
