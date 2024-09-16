<?php

namespace App\Models\Scopes;

use App\Services\OwnerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OwnerScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = OwnerService::make()->getUser();
        $builder->whereHas('users', function (Builder $query) use ($user) {
            $query->where('user_id', $user->getKey());
        });
    }
}
