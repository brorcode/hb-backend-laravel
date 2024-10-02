<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class CategoryParentListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Category::class;

    protected function getBuilder(): Builder
    {
        $builder = parent::getBuilder();

        $builder
            ->whereNull('parent_id')
            ->with([
                'parentCategory',
                'subTransactionsDebit' => function(HasManyThrough $query) {
                    $query
                        ->select([
                            DB::raw('count(category_id) as count'),
                            DB::raw('sum(amount) as amount'),
                        ])
                        ->groupBy([
                            'laravel_through_key',
                        ])
                    ;
                },
                'subTransactionsCredit' => function(HasManyThrough $query) {
                    $query
                        ->select([
                            DB::raw('count(category_id) as count'),
                            DB::raw('sum(amount) as amount'),
                        ])
                        ->groupBy([
                            'laravel_through_key',
                        ])
                    ;
                },
                'subTransactionsTransfer' => function(HasManyThrough $query) {
                    $query
                        ->select([
                            DB::raw('count(category_id) as count'),
                            DB::raw('sum(amount) as amount'),
                        ])
                        ->groupBy([
                            'laravel_through_key',
                        ])
                    ;
                }
            ])
        ;

        return $builder;
    }

    protected function applySpecificFilters($builder): void
    {
        if (isset($this->request->filters['name'])) {
            $builder->where('name', 'like', "%{$this->request->filters['name']['value']}%");
        }
    }
}
