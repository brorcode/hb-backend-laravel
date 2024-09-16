<?php

namespace App\Services;

use App\Http\Requests\Api\v1\ListRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class AbstractListService
{
    protected ListRequest $request;

    /**
     * @var class-string<Model|TModel>
     */
    protected string $model;

    public function setRequest(ListRequest $request): void
    {
        $this->request = $request;
    }

    protected function getBuilder(): Builder
    {
        $TModel = resolve($this->model);

        return $TModel->query();
    }

    public function getListBuilder(): Builder
    {
        $builder = $this->getBuilder();
        $this->applyFilters($builder);
        $this->applySorting($builder);

        return $builder;
    }

    private function applySorting($builder): void
    {
        if (!$this->request->sorting) {
            return;
        }

        $builder->orderBy($this->request->getSortingColumn(), $this->request->getSortingDirection());
    }

    protected function applyFilters($builder): void
    {
        if (!$this->request->filters) {
            return;
        }

        if (isset($this->request->filters['id'])) {
            $builder->where('id', $this->request->filters['id']['value']);
        }

        $this->applySpecificFilters($builder);
    }

    abstract protected function applySpecificFilters(Builder $builder): void;
}
