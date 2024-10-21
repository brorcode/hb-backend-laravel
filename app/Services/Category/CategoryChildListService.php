<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class CategoryChildListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Category::class;

    protected function getBuilder(): Builder
    {
        $builder = parent::getBuilder();

        $builder
            ->whereNotNull('parent_id')
            ->with([
                'parentCategory',
                'transactionsDebit' => function (HasMany $query) {
                    $query
                        ->select([
                            'category_id',
                            DB::raw('count(category_id) as count'),
                            DB::raw('sum(amount) as amount'),
                        ])
                        ->groupBy([
                            'category_id',
                        ]);
                },
                'transactionsCredit' => function (HasMany $query) {
                    $query
                        ->select([
                            'category_id',
                            DB::raw('count(category_id) as count'),
                            DB::raw('sum(amount) as amount'),
                        ])
                        ->groupBy([
                            'category_id',
                        ]);
                },
                'transactionsTransfer' => function (HasMany $query) {
                    $query
                        ->select([
                            'category_id',
                            DB::raw('count(category_id) as count'),
                            DB::raw('sum(amount) as amount'),
                        ])
                        ->groupBy([
                            'category_id',
                        ]);
                }
            ]);

        return $builder;
    }

    protected function applySpecificFilters(Builder $builder): void
    {
        if (isset($this->request->filters['name'])) {
            $builder->where('name', 'like', "%{$this->request->filters['name']['value']}%");
        }

        if (isset($this->request->filters['parent_categories'])) {
            $builder->whereIn('parent_id', array_column($this->request->filters['parent_categories']['value'], 'id'));
        }
    }
}
