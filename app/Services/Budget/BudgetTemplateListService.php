<?php

namespace App\Services\Budget;

use App\Models\BudgetTemplate;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Illuminate\Database\Eloquent\Builder;

class BudgetTemplateListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = BudgetTemplate::class;

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
    }
}
