<?php

namespace App\Services;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CategoryService extends AbstractListService
{
    use ServiceInstance;

    protected string $model = Category::class;

    protected function applySpecificFilters($builder): void
    {
        if (isset($this->request->filters['name'])) {
            $builder->where('name', 'like', "%{$this->request->filters['name']['value']}%");
        }

        if (isset($this->request->filters['description'])) {
            $builder->where('email', 'like', "%{$this->request->filters['description']['value']}%");
        }
    }
}
