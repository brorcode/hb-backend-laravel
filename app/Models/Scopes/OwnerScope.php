<?php

namespace App\Models\Scopes;

use App\Exceptions\SystemException;
use App\Services\OwnerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OwnerScope implements Scope
{
    /**
     * @throws SystemException
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = OwnerService::make()->getUser();

        if ($this->hasDirectUserIdColumn($model)) {
            $builder->where('user_id', $user->getKey());

            return;
        }

        $builder->whereHas('users', function (Builder $query) use ($user) {
            $query->where('user_id', $user->getKey());
        });
    }

    private function hasDirectUserIdColumn(Model $model): bool
    {
        return $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'user_id');
    }
}
