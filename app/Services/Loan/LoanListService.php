<?php

namespace App\Services\Loan;

use App\Models\Account;
use App\Models\Loan;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Illuminate\Database\Eloquent\Builder;
use function PHPUnit\Framework\matches;

class LoanListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Loan::class;

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
    }
}
