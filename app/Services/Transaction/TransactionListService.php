<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TransactionListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Transaction::class;

    protected function getBuilder(): Builder
    {
        $builder = parent::getBuilder();

        $builder->with(['category', 'account', 'tags']);

        return $builder;
    }


    protected function applySpecificFilters(Builder $builder): void
    {
        if (isset($this->request->filters['amount'])) {
            $builder->where('amount', $this->request->filters['amount']['value']);
        }

        if (isset($this->request->filters['categories'])) {
            $builder->whereIn('category_id', array_column($this->request->filters['categories']['value'], 'id'));
        }

        if (isset($this->request->filters['accounts'])) {
            $builder->whereIn('account_id', array_column($this->request->filters['accounts']['value'], 'id'));
        }

        // @todo check how is better when or if
        // $builder->when($this->request->filters['tags'], function (Builder $query, array $tags) {
        //     $query->whereHas('tags', function (Builder $query2) use ($tags) {
        //         $query2->whereIn('tags.id', array_column($tags['value'], 'id'));
        //     });
        // });

        if (isset($this->request->filters['tags'])) {
            $builder->whereHas('tags', function(Builder $query) {
                $query->whereIn('tags.id', array_column($this->request->filters['tags']['value'], 'id'));
            });
        }

        if (isset($this->request->filters['created_at_after'])) {
            $dateAfter = Carbon::parse($this->request->filters['created_at_after']['value'])->format('Y-m-d 00:00:00');
            $builder->where('created_at', '>=', $dateAfter);
        }

        if (isset($this->request->filters['created_at_before'])) {
            $dateBefore = Carbon::parse($this->request->filters['created_at_before']['value'])->format('Y-m-d 23:59:59');
            $builder->where('created_at', '<=', $dateBefore);
        }
    }
}
