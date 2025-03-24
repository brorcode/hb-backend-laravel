<?php

namespace App\Services\Budget;

use App\Models\Budget;
use App\Models\BudgetTemplate;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BudgetItemListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Budget::class;

    protected function getBuilder(): Builder
    {
        $builder = parent::getBuilder();

        $builder->with(['category']);

        return $builder;
    }

    protected function applySpecificFilters(Builder $builder): void
    {
        if (isset($this->request->filters['amount'])) {
            $builder->where('amount', $this->request->filters['amount']['value'] * 100);
        }

        if (isset($this->request->filters['categories'])) {
            $builder->whereIn('category_id', array_column($this->request->filters['categories']['value'], 'id'));
        }

        if (isset($this->request->filters['period_on'])) {
            $periodOn = BudgetService::getPeriodOnFromArray($this->request->filters['period_on']['value']);

            $builder->where('period_on', $periodOn->toDateString());
        }
    }

    public function getBudgetSum(): float
    {
        $builder = $this->getBuilder();
        $this->applyFilters($builder);

        return $builder->sum('amount') / 100;
    }
}
