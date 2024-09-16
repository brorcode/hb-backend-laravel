<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Services\AbstractListService;
use App\Services\ServiceInstance;

class CategoryListService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Category::class;

    protected function applySpecificFilters($builder): void
    {
        if (isset($this->request->filters['name'])) {
            $builder->where('name', 'like', "%{$this->request->filters['name']['value']}%");
        }
    }
}
