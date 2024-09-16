<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;

class UserListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = User::class;

    protected function applySpecificFilters($builder): void
    {
        if (isset($this->request->filters['name'])) {
            $builder->where('name', 'like', "%{$this->request->filters['name']['value']}%");
        }

        if (isset($this->request->filters['email'])) {
            $builder->where('email', 'like', "%{$this->request->filters['email']['value']}%");
        }
    }
}
