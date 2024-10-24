<?php

namespace App\Services\Account;

use App\Models\Account;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Illuminate\Database\Eloquent\Builder;

class AccountListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Account::class;

    protected function getBuilder(): Builder
    {
        $builder = parent::getBuilder();

        $builder->with(['transactions']);

        return $builder;
    }

    protected function applySpecificFilters(Builder $builder): void
    {
        if (isset($this->request->filters['name'])) {
            $builder->where('name', 'like', "%{$this->request->filters['name']['value']}%");
        }

        if (!isset($this->request->filters['show_archived'])) {
            $builder->where('is_archived', false);
        }
    }
}
