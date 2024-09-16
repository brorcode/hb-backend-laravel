<?php

namespace App\Services\Tag;

use App\Models\Tag;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;
use Illuminate\Database\Eloquent\Builder;

class TagListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Tag::class;

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
