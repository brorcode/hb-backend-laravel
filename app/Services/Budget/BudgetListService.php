<?php

namespace App\Services\Budget;

use App\Models\Budget;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BudgetListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Budget::class;

    protected function getBuilder(): Builder
    {
        $builder = parent::getBuilder();

        $builder
            ->selectRaw('SUM(amount) as total, period_on')
            ->groupBy('period_on')
        ;

        return $builder;
    }

    protected function applySpecificFilters(Builder $builder): void
    {
        if (isset($this->request->filters['period_on'])) {
            $periodOn = BudgetService::getPeriodOnFromArray($this->request->filters['period_on']['value']);

            $builder->where('period_on', $periodOn->toDateString());
        }
    }
}
